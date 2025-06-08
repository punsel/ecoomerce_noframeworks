<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Ensure only admins can access
requireAdmin();

$conn = getDBConnection();

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = sanitizeInput($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Order status updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update order status";
    }
    
    header('Location: orders.php');
    exit;
}

// Get all orders with user and product details
$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email,
           COUNT(oi.id) as item_count,
           SUM(oi.quantity * oi.price) as total_amount
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Beauty Shop Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            200: '#fbcfe8',
                            300: '#f9a8d4',
                            400: '#f472b6',
                            500: '#ec4899',
                            600: '#db2777',
                            700: '#be185d',
                            800: '#9d174d',
                            900: '#831843',
                        }
                    }
                }
            }
        }
    </script>
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
</head>
<body class="min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 glass-effect">
            <div class="p-6 border-b border-gray-700">
                <a href="dashboard.php" class="flex items-center space-x-3 text-2xl font-bold text-primary-400 hover:text-primary-300 transition-colors">
                    <i class="fas fa-store text-3xl"></i>
                    <span>Admin Panel</span>
                </a>
            </div>
            <nav class="mt-6 flex flex-col space-y-1">
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
                <div class="flex flex-col space-y-1">
                    <a href="../logout.php" class="sidebar-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-6">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-4 rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    <?php 
                                    echo $_SESSION['success'];
                                    unset($_SESSION['success']);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    <?php 
                                    echo $_SESSION['error'];
                                    unset($_SESSION['error']);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="glass-effect rounded-lg p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-primary-300">Order Management</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Items</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-300">
                                                #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                                            </div>
                                            <div class="text-sm text-gray-400">
                                                <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-300">
                                                <?php echo htmlspecialchars($order['username']); ?>
                                            </div>
                                            <div class="text-sm text-gray-400">
                                                <?php echo htmlspecialchars($order['email']); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                            <?php echo $order['item_count']; ?> items
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                            $<?php echo number_format($order['total_amount'], 2); ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
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
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="view-order.php?id=<?php echo $order['id']; ?>" 
                                                   class="text-primary-400 hover:text-primary-300 p-2 rounded-full hover-glow">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()" 
                                                            class="text-sm rounded-md border-gray-700 bg-gray-800 text-gray-300 focus:ring-primary-500 focus:border-primary-500">
                                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 