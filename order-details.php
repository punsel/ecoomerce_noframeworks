<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require login to access this page
requireLogin();

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Get order details
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT o.*, u.full_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

include 'includes/header.php';
?>

<div class="bg-white">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-extrabold text-gray-900">Order Details</h1>
                <a href="orders.php" class="text-sm font-medium text-pink-600 hover:text-pink-500">
                    &larr; Back to Orders
                </a>
            </div>

            <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Order #<?php echo htmlspecialchars($order['id']); ?>
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                        ($order['status'] === 'processing' ? 'bg-yellow-100 text-yellow-800' : 
                                        'bg-gray-100 text-gray-800'); ?>">
                                <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Customer</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo htmlspecialchars($order['full_name']); ?><br>
                                <?php echo htmlspecialchars($order['email']); ?>
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Shipping Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-medium text-gray-900">Order Items</h2>
                <div class="mt-4 space-y-4">
                    <?php foreach ($items as $item): ?>
                        <div class="flex items-center space-x-4 bg-white p-4 rounded-lg shadow">
                            <div class="flex-shrink-0 h-16 w-16">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     class="h-16 w-16 object-cover rounded-md">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    Quantity: <?php echo htmlspecialchars($item['quantity']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">
                                    $<?php echo number_format($item['price'], 2); ?> each
                                </p>
                                <p class="text-sm text-gray-500">
                                    Total: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 bg-white p-4 rounded-lg shadow">
                    <div class="flex justify-between text-base font-medium text-gray-900">
                        <p>Subtotal</p>
                        <p>$<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                    <div class="flex justify-between text-base font-medium text-gray-900 mt-4 pt-4 border-t border-gray-200">
                        <p>Total</p>
                        <p>$<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 