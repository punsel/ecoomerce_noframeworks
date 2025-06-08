<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

// Get cart items
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.image_url, p.stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 5.99;
$tax = $subtotal * 0.1; // 10% tax
$total = $subtotal + $shipping + $tax;

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $required_fields = ['full_name', 'email', 'address', 'city', 'state', 'zip', 'card_number', 'expiry', 'cvv'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = "This field is required";
        }
    }

    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    // Validate card number (simple validation)
    if (!preg_match('/^\d{16}$/', str_replace(' ', '', $_POST['card_number']))) {
        $errors['card_number'] = "Invalid card number";
    }

    // Validate expiry date
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $_POST['expiry'])) {
        $errors['expiry'] = "Invalid expiry date (MM/YY)";
    }

    // Validate CVV
    if (!preg_match('/^\d{3,4}$/', $_POST['cvv'])) {
        $errors['cvv'] = "Invalid CVV";
    }

    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO orders (user_id, total_amount, status, shipping_address, shipping_city, shipping_state, shipping_zip)
                VALUES (?, ?, 'pending', ?, ?, ?, ?)
            ");
            $stmt->bind_param("idssss", $user_id, $total, $_POST['address'], $_POST['city'], $_POST['state'], $_POST['zip']);
            $stmt->execute();
            $order_id = $conn->insert_id;

            // Add order items
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($cart_items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();

                // Update product stock
                $new_stock = $item['stock'] - $item['quantity'];
                $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $update_stock->bind_param("ii", $new_stock, $item['product_id']);
                $update_stock->execute();
            }

            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $conn->commit();
            $success = true;

            // Redirect to order confirmation
            header("Location: order-confirmation.php?id=" . $order_id);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors['general'] = "An error occurred while processing your order. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Checkout</h1>

            <?php if (!empty($errors['general'])): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo htmlspecialchars($errors['general']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Order Summary -->
                        <div class="border-b border-gray-200 pb-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
                            <div class="space-y-4">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="flex items-center">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             class="w-16 h-16 object-cover rounded-md">
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h3>
                                            <p class="text-sm text-gray-500">Quantity: <?php echo $item['quantity']; ?></p>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-6 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <p class="text-gray-600">Subtotal</p>
                                    <p class="text-gray-900">$<?php echo number_format($subtotal, 2); ?></p>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <p class="text-gray-600">Shipping</p>
                                    <p class="text-gray-900">$<?php echo number_format($shipping, 2); ?></p>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <p class="text-gray-600">Tax</p>
                                    <p class="text-gray-900">$<?php echo number_format($tax, 2); ?></p>
                                </div>
                                <div class="flex justify-between text-base font-medium">
                                    <p class="text-gray-900">Total</p>
                                    <p class="text-gray-900">$<?php echo number_format($total, 2); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Checkout Form -->
                        <form method="POST" class="space-y-6">
                            <!-- Shipping Information -->
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping Information</h2>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                        <input type="text" name="full_name" id="full_name" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                        <?php if (isset($errors['full_name'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['full_name']; ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                        <?php if (isset($errors['email'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['email']; ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                        <input type="text" name="address" id="address" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                                        <?php if (isset($errors['address'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['address']; ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                        <input type="text" name="city" id="city" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                                        <?php if (isset($errors['city'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['city']; ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                        <input type="text" name="state" id="state" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
                                        <?php if (isset($errors['state'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['state']; ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <label for="zip" class="block text-sm font-medium text-gray-700">ZIP Code</label>
                                        <input type="text" name="zip" id="zip" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               value="<?php echo isset($_POST['zip']) ? htmlspecialchars($_POST['zip']) : ''; ?>">
                                        <?php if (isset($errors['zip'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['zip']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h2>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
                                        <input type="text" name="card_number" id="card_number" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               placeholder="1234 5678 9012 3456"
                                               value="<?php echo isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : ''; ?>">
                                        <?php if (isset($errors['card_number'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['card_number']; ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <label for="expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                        <input type="text" name="expiry" id="expiry" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               placeholder="MM/YY"
                                               value="<?php echo isset($_POST['expiry']) ? htmlspecialchars($_POST['expiry']) : ''; ?>">
                                        <?php if (isset($errors['expiry'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['expiry']; ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <label for="cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                                        <input type="text" name="cvv" id="cvv" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                               placeholder="123"
                                               value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>">
                                        <?php if (isset($errors['cvv'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['cvv']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-6">
                                <button type="submit" 
                                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-pink-600 to-purple-600 hover:from-pink-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                    Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Format card number with spaces
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = '';
    for(let i = 0; i < value.length; i++) {
        if(i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    e.target.value = formattedValue;
});

// Format expiry date
document.getElementById('expiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    if (value.length >= 2) {
        value = value.slice(0,2) + '/' + value.slice(2,4);
    }
    e.target.value = value;
});

// Format CVV (numbers only)
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/gi, '').slice(0,4);
});
</script>

<?php include 'includes/footer.php'; ?> 