<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require login to access this page
requireLogin();

// Get user's orders
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as total_items,
           SUM(oi.quantity) as total_quantity
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'includes/header.php';
?>

<div class="bg-white">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-extrabold text-gray-900">My Orders</h1>

            <?php if (empty($orders)): ?>
                <div class="mt-8 text-center">
                    <p class="text-gray-500">You haven't placed any orders yet.</p>
                    <a href="products.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700">
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="mt-8 space-y-8">
                    <?php foreach ($orders as $order): ?>
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
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
                                        <dt class="text-sm font-medium text-gray-500">Total Items</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            <?php echo htmlspecialchars($order['total_items']); ?> items
                                            (<?php echo htmlspecialchars($order['total_quantity']); ?> units)
                                        </dd>
                                    </div>
                                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            $<?php echo number_format($order['total_amount'], 2); ?>
                                        </dd>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Shipping Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="bg-gray-50 px-4 py-4 sm:px-6">
                                <div class="text-sm">
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                       class="font-medium text-pink-600 hover:text-pink-500">
                                        View Order Details
                                        <span aria-hidden="true"> &rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 