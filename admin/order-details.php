<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require admin access
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = (int)$_GET['id'];

// Get order details with user information
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT o.*, u.full_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: orders.php');
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

include '../includes/header.php';
?>

<style>
    body {
        background-color: #1a1a1a;
        color: #f3f4f6;
    }
    .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .hover-glow:hover {
        box-shadow: 0 0 15px rgba(236, 72, 153, 0.5);
        transition: all 0.3s ease;
    }
    .sidebar-link {
        @apply flex items-center px-8 py-6 text-gray-300 hover:text-primary-300 transition-all duration-300 rounded-lg mx-4 my-2 text-xl;
    }
    .sidebar-link:hover {
        background: rgba(236, 72, 153, 0.1);
        transform: translateX(5px);
    }
    .sidebar-link.active {
        @apply text-primary-400 bg-primary-900 bg-opacity-20;
        border-left: 4px solid #ec4899;
    }
    .sidebar-link i {
        @apply w-8 text-2xl;
    }
    .sidebar-link span {
        @apply ml-4 font-semibold;
    }
    .sidebar-divider {
        @apply border-t border-gray-700 my-6 mx-4;
    }
</style>

<div class="flex h-screen">
    <!-- Sidebar -->
    <aside class="w-80 glass-effect">
        <div class="p-8 border-b border-gray-700">
            <a href="dashboard.php" class="flex items-center space-x-4 text-3xl font-bold text-primary-400 hover:text-primary-300 transition-colors">
                <i class="fas fa-store text-4xl"></i>
                <span>Admin Panel</span>
            </a>
        </div>
        <nav class="mt-10 flex flex-col space-y-2">
            <a href="dashboard.php" class="sidebar-link">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="users.php" class="sidebar-link">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="products.php" class="sidebar-link">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <a href="orders.php" class="sidebar-link active">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
            <div class="sidebar-divider"></div>
            <div class="flex flex-col space-y-2">
                <a href="../index.php" class="sidebar-link">
                    <i class="fas fa-home"></i>
                    <span>Back to Shop</span>
                </a>
                <a href="../logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>

    <div class="flex-1 p-8">
        <div class="bg-white">
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Page header -->
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Order #<?php echo $order['id']; ?>
                        </h2>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        <a href="orders.php" 
                           class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                            Back to Orders
                        </a>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Order Information -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Order Information</h3>
                        </div>
                        <div class="border-t border-gray-200">
                            <dl>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Order Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch ($order['status']) {
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'processing':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'shipped':
                                                    echo 'bg-purple-100 text-purple-800';
                                                    break;
                                                case 'delivered':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'cancelled':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                            }
                                            ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Order Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        $<?php echo number_format($order['total_amount'], 2); ?>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Information</h3>
                        </div>
                        <div class="border-t border-gray-200">
                            <dl>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo htmlspecialchars($order['full_name']); ?>
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo htmlspecialchars($order['email']); ?>
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
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mt-8">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Order Items</h3>
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($items as $item): ?>
                                <li class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 h-16 w-16">
                                            <img class="h-16 w-16 rounded-lg object-cover" 
                                                 src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Quantity: <?php echo $item['quantity']; ?>
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 text-sm text-gray-500">
                                            $<?php echo number_format($item['price'], 2); ?> each
                                        </div>
                                        <div class="flex-shrink-0 text-sm font-medium text-gray-900">
                                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 