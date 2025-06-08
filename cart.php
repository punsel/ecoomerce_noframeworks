<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$cart_items = [];
$total = 0;

if (isLoggedIn()) {
    $conn = getDBConnection();
    $user_id = getCurrentUserId();
    
    // Get cart items from database
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image_url, p.stock 
        FROM cart_items c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($item = $result->fetch_assoc()) {
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
        
        $cart_items[] = [
            'id' => $item['product_id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'image_url' => $item['image_url'],
            'quantity' => $item['quantity'],
            'subtotal' => $subtotal,
            'stock' => $item['stock']
        ];
    }
    
    // Handle quantity updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_quantity'])) {
            $product_id = intval($_POST['product_id']);
            $quantity = max(1, intval($_POST['quantity']));
            
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $stmt->execute();
            
            header('Location: cart.php');
            exit;
        }
        
        if (isset($_POST['remove_item'])) {
            $product_id = intval($_POST['product_id']);
            
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            
            header('Location: cart.php');
            exit;
        }
    }
    
    $stmt->close();
    $conn->close();
}

include 'includes/header.php';
?>

<div class="bg-white">
    <div class="max-w-2xl mx-auto py-8 px-4 sm:py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
            <div class="mt-8 text-center">
                <h2 class="text-lg font-medium text-gray-900">Your cart is empty</h2>
                <p class="mt-2 text-sm text-gray-500">Start shopping to add items to your cart.</p>
                <div class="mt-4">
                    <a href="products.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="mt-8">
                <div class="flow-root">
                    <ul role="list" class="-my-4 divide-y divide-gray-200" id="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="py-4 flex" id="cart-item-<?php echo $item['id']; ?>">
                                <div class="flex-shrink-0 w-20 h-20 border border-gray-200 rounded-md overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'assets/images/default-product.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         class="h-20 w-20 rounded-md object-cover object-center">
                                </div>

                                <div class="ml-4 flex-1 flex flex-col">
                                    <div>
                                        <div class="flex justify-between text-base font-medium text-gray-900">
                                            <h3>
                                                <a href="product.php?id=<?php echo $item['id']; ?>">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                            </h3>
                                            <p class="ml-4">$<?php echo number_format($item['subtotal'], 2); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex-1 flex items-end justify-between text-sm">
                                        <div class="flex items-center">
                                            <label for="quantity-<?php echo $item['id']; ?>" class="mr-2 text-gray-500">Qty</label>
                                            <select id="quantity-<?php echo $item['id']; ?>" 
                                                    class="max-w-full rounded-md border border-gray-300 py-1.5 text-base leading-5 font-medium text-gray-700 text-left shadow-sm focus:outline-none focus:ring-1 focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                                    onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                                <?php for ($i = 1; $i <= min(10, $item['stock']); $i++): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo $i === $item['quantity'] ? 'selected' : ''; ?>>
                                                        <?php echo $i; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="flex">
                                            <button type="button" 
                                                    onclick="removeItem(<?php echo $item['id']; ?>)"
                                                    class="font-medium text-pink-600 hover:text-pink-500">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 py-4 px-4 sm:px-6">
                <div class="flex justify-between text-base font-medium text-gray-900">
                    <p>Subtotal</p>
                    <p id="cart-total">$<?php echo number_format($total, 2); ?></p>
                </div>
                <p class="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
                <div class="mt-4">
                    <a href="checkout.php" class="flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-pink-600 hover:bg-pink-700">
                        Checkout
                    </a>
                </div>
                <div class="mt-4 flex justify-center text-sm text-center text-gray-500">
                    <p>
                        or
                        <a href="products.php" class="text-pink-600 font-medium hover:text-pink-500">
                            Continue Shopping<span aria-hidden="true"> &rarr;</span>
                        </a>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateQuantity(productId, quantity) {
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_quantity&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cart-total').textContent = `$${data.total}`;
        } else {
            alert('Failed to update quantity');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating quantity');
    });
}

function removeItem(productId) {
    if (!confirm('Are you sure you want to remove this item?')) {
        return;
    }

    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove_item&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.getElementById(`cart-item-${productId}`);
            item.style.opacity = '0';
            item.style.transition = 'opacity 0.3s ease-out';
            
            setTimeout(() => {
                item.remove();
                document.getElementById('cart-total').textContent = `$${data.total}`;
                
                // If no items left, reload page to show empty cart message
                if (document.querySelectorAll('#cart-items li').length === 0) {
                    window.location.reload();
                }
            }, 300);
        } else {
            alert('Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while removing item');
    });
}
</script>

<?php include 'includes/footer.php'; ?> 