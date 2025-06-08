<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Ensure only admins can access
requireAdmin();

$conn = getDBConnection();

// Get total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total'];

// Get total products
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
$stmt->execute();
$total_products = $stmt->get_result()->fetch_assoc()['total'];

// Get total orders
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total'];

// Get recent orders
$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Beauty Shop</title>
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
        <aside class="w-80 glass-effect">
            <div class="p-8 border-b border-gray-700">
                <a href="dashboard.php" class="flex items-center space-x-4 text-3xl font-bold text-primary-400 hover:text-primary-300 transition-colors">
                    <i class="fas fa-store text-4xl"></i>
                    <span>Admin Panel</span>
                </a>
            </div>
            <nav class="mt-10 flex flex-col space-y-2">
                <a href="dashboard.php" class="sidebar-link active">
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
                <a href="orders.php" class="sidebar-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <div class="sidebar-divider"></div>
                <div class="flex flex-col space-y-2">
                    <a href="../logout.php" class="sidebar-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-8">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="glass-effect rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-primary-500 bg-opacity-20">
                                <i class="fas fa-users text-primary-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Total Users</p>
                                <p class="text-2xl font-semibold text-primary-300"><?php echo $total_users; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="glass-effect rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-primary-500 bg-opacity-20">
                                <i class="fas fa-box text-primary-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Total Products</p>
                                <p class="text-2xl font-semibold text-primary-300"><?php echo $total_products; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="glass-effect rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-primary-500 bg-opacity-20">
                                <i class="fas fa-shopping-cart text-primary-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Total Orders</p>
                                <p class="text-2xl font-semibold text-primary-300"><?php echo $total_orders; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="glass-effect rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-primary-300 mb-4">Recent Orders</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">#<?php echo $order['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            <?php echo htmlspecialchars($order['username']); ?><br>
                                            <span class="text-xs text-gray-400"><?php echo htmlspecialchars($order['email']); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php
                                                switch($order['status']) {
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
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            $<?php echo number_format($order['total_amount'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="text-primary-400 hover:text-primary-300">
                                                View Details
                                            </a>
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