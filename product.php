<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$product_id = intval($_GET['id'] ?? 0);
if (!$product_id) {
    header('Location: products.php');
    exit;
}

$conn = getDBConnection();

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get product reviews
$stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate average rating
$avg_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = $total_rating / count($reviews);
}

// Handle review submission
$review_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $review_errors[] = "Please select a valid rating";
    }

    if (empty($comment)) {
        $review_errors[] = "Please enter a review comment";
    }

    if (empty($review_errors)) {
        $user_id = getCurrentUserId();
        
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
        
        if ($stmt->execute()) {
            header("Location: product.php?id=$product_id");
            exit;
        } else {
            $review_errors[] = "Failed to submit review. Please try again.";
        }
    }
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $user_id = getCurrentUserId();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if item already exists in cart
        $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity if item exists
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        } else {
            // Insert new item if it doesn't exist
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        }
        
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        header("Location: product.php?id=$product_id&added=1");
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Failed to add item to cart. Please try again.";
        header("Location: product.php?id=$product_id");
        exit;
    }
}

$stmt->close();
$conn->close();

include 'includes/header.php';
?>

<div class="bg-white">
    <div class="max-w-2xl mx-auto px-4 py-6 sm:px-6 sm:py-8 lg:max-w-7xl lg:px-8 lg:py-12">
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
            <!-- Image gallery -->
            <div class="flex flex-col-reverse">
                <div class="w-full max-w-xs mx-auto aspect-w-1 aspect-h-1 rounded-xl overflow-hidden bg-white">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'assets/images/default-product.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="w-full h-full object-center object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
            </div>

            <!-- Product info -->
            <div class="mt-6 px-0 sm:mt-8 lg:mt-0">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>

                <div class="mt-3">
                    <h2 class="sr-only">Product information</h2>
                    <p class="text-2xl sm:text-3xl text-gray-900">$<?php echo number_format($product['price'], 2); ?></p>
                </div>

                <!-- Reviews -->
                <div class="mt-3">
                    <h3 class="sr-only">Reviews</h3>
                    <div class="flex items-center">
                        <div class="flex items-center">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="ml-2 text-sm text-gray-500">
                            <?php echo count($reviews); ?> reviews
                        </p>
                    </div>
                </div>

                <div class="mt-4 sm:mt-6">
                    <h3 class="sr-only">Description</h3>
                    <div class="text-sm sm:text-base text-gray-700 space-y-4">
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                </div>

                <div class="mt-4 sm:mt-6">
                    <div class="flex items-center">
                        <h3 class="text-sm text-gray-600">Brand:</h3>
                        <p class="ml-2 text-sm text-gray-900"><?php echo htmlspecialchars($product['brand']); ?></p>
                    </div>
                    <div class="flex items-center mt-2">
                        <h3 class="text-sm text-gray-600">Category:</h3>
                        <p class="ml-2 text-sm text-gray-900"><?php echo htmlspecialchars($product['category']); ?></p>
                    </div>
                    <div class="flex items-center mt-2">
                        <h3 class="text-sm text-gray-600">Status:</h3>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="ml-2 text-sm text-green-600 bg-green-50 px-2 py-1 rounded-full">In Stock</span>
                        <?php else: ?>
                            <span class="ml-2 text-sm text-red-600 bg-red-50 px-2 py-1 rounded-full">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center mt-2">
                        <h3 class="text-sm text-gray-600">Available Stock:</h3>
                        <p class="ml-2 text-sm text-gray-900"><?php echo number_format($product['stock']); ?> units</p>
                    </div>
                    <div class="flex items-center mt-2">
                        <h3 class="text-sm text-gray-600">Total Sold:</h3>
                        <p class="ml-2 text-sm text-gray-900">
                            <?php 
                            $conn = getDBConnection();
                            $stmt = $conn->prepare("
                                SELECT SUM(quantity) as total_sold 
                                FROM order_items oi 
                                JOIN orders o ON oi.order_id = o.id 
                                WHERE oi.product_id = ? AND o.status = 'completed'
                            ");
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $total_sold = $result->fetch_assoc()['total_sold'] ?? 0;
                            $stmt->close();
                            $conn->close();
                            echo number_format($total_sold) . ' units';
                            ?>
                        </p>
                    </div>
                </div>

                <form class="mt-6 sm:mt-8" id="add-to-cart-form">
                    <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row gap-4">
                        <div class="w-full sm:w-32">
                            <label for="quantity" class="sr-only">Quantity</label>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   min="1" 
                                   max="<?php echo $product['stock']; ?>" 
                                   value="1"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 w-full">
                            <button type="button" 
                                    onclick="addToCart(<?php echo $product['id']; ?>)"
                                    class="flex-1 bg-white text-pink-600 border-2 border-pink-600 rounded-md py-2.5 sm:py-3 px-4 sm:px-8 flex items-center justify-center text-sm sm:text-base font-medium hover:bg-pink-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Add to Cart
                            </button>
                            <button type="submit" 
                                    name="buy_now"
                                    class="flex-1 bg-pink-600 text-white rounded-md py-2.5 sm:py-3 px-4 sm:px-8 flex items-center justify-center text-sm sm:text-base font-medium hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                                <i class="fas fa-bolt mr-2"></i>
                                Buy Now
                            </button>
                        </div>
                    </div>
                </form>

                <div id="cart-message" class="mt-4 hidden">
                    <div class="rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800" id="cart-message-text">
                                    Product added to cart successfully!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function addToCart(productId) {
                    const quantity = document.getElementById('quantity').value;
                    const messageDiv = document.getElementById('cart-message');
                    const messageText = document.getElementById('cart-message-text');
                    const cartBadge = document.querySelector('.cart-badge');
                    
                    fetch('ajax/cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=add_to_cart&product_id=${productId}&quantity=${quantity}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            messageText.textContent = data.message;
                            messageDiv.classList.remove('hidden');
                            
                            // Update cart badge
                            if (cartBadge) {
                                cartBadge.textContent = data.cart_count;
                                cartBadge.classList.remove('hidden');
                            } else {
                                // Create cart badge if it doesn't exist
                                const cartIcon = document.querySelector('a[href="cart.php"] .fa-shopping-cart');
                                if (cartIcon) {
                                    const badge = document.createElement('span');
                                    badge.className = 'absolute -top-3 -right-3 bg-primary-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold transform scale-100 transition-transform duration-300 hover:scale-110';
                                    badge.textContent = data.cart_count;
                                    cartIcon.parentElement.appendChild(badge);
                                }
                            }
                            
                            // Hide message after 3 seconds
                            setTimeout(() => {
                                messageDiv.classList.add('hidden');
                            }, 3000);
                        } else {
                            messageText.textContent = data.error || 'Failed to add item to cart';
                            messageDiv.classList.remove('hidden');
                            messageDiv.classList.remove('bg-green-50');
                            messageDiv.classList.add('bg-red-50');
                            messageText.classList.remove('text-green-800');
                            messageText.classList.add('text-red-800');
                            
                            setTimeout(() => {
                                messageDiv.classList.add('hidden');
                                messageDiv.classList.remove('bg-red-50');
                                messageDiv.classList.add('bg-green-50');
                                messageText.classList.remove('text-red-800');
                                messageText.classList.add('text-green-800');
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageText.textContent = 'An error occurred while adding to cart';
                        messageDiv.classList.remove('hidden');
                        messageDiv.classList.remove('bg-green-50');
                        messageDiv.classList.add('bg-red-50');
                        messageText.classList.remove('text-green-800');
                        messageText.classList.add('text-red-800');
                        
                        setTimeout(() => {
                            messageDiv.classList.add('hidden');
                            messageDiv.classList.remove('bg-red-50');
                            messageDiv.classList.add('bg-green-50');
                            messageText.classList.remove('text-red-800');
                            messageText.classList.add('text-green-800');
                        }, 3000);
                    });
                }
                </script>
            </div>
        </div>

        <!-- Related Products -->
        <div class="mt-16">
            <h2 class="text-lg font-medium text-gray-900 mb-6">Related Products</h2>
            <div class="mt-6 grid grid-cols-2 gap-y-4 gap-x-2 sm:gap-y-6 sm:gap-x-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
                <?php
                // Get related products (same category, excluding current product)
                $conn = getDBConnection();
                $stmt = $conn->prepare("
                    SELECT * FROM products 
                    WHERE category = ? AND id != ? AND stock > 0 
                    ORDER BY created_at DESC
                ");
                $stmt->bind_param("si", $product['category'], $product_id);
                $stmt->execute();
                $related_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                $conn->close();

                foreach ($related_products as $related): ?>
                    <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-t-xl bg-gray-100">
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="block">
                                <img src="<?php echo htmlspecialchars($related['image_url'] ?? 'assets/images/default-product.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>"
                                     class="h-48 w-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                            </a>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div class="mb-2 sm:mb-3">
                                <h3 class="text-xs sm:text-sm font-medium text-gray-900 line-clamp-2">
                                    <a href="product.php?id=<?php echo $related['id']; ?>" class="hover:text-pink-600 transition-colors">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </a>
                                </h3>
                                <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm text-gray-500"><?php echo htmlspecialchars($related['brand']); ?></p>
                            </div>
                            <div class="flex items-center justify-between mb-2 sm:mb-3">
                                <p class="text-xs sm:text-sm font-bold text-pink-600">$<?php echo number_format($related['price'], 2); ?></p>
                                <?php if ($related['stock'] > 0): ?>
                                    <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">In Stock</span>
                                <?php else: ?>
                                    <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-full">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-shopping-bag mr-1"></i>
                                    <?php 
                                    $conn = getDBConnection();
                                    $stmt = $conn->prepare("
                                        SELECT SUM(quantity) as total_sold 
                                        FROM order_items oi 
                                        JOIN orders o ON oi.order_id = o.id 
                                        WHERE oi.product_id = ? AND o.status = 'completed'
                                    ");
                                    $stmt->bind_param("i", $related['id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $total_sold = $result->fetch_assoc()['total_sold'] ?? 0;
                                    $stmt->close();
                                    $conn->close();
                                    echo number_format($total_sold) . ' sold';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 