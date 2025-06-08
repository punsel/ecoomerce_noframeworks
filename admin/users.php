<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Ensure only admins can access
requireAdmin();

$conn = getDBConnection();

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    
    // Don't allow deleting the last admin
    $stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admin_count = $stmt->get_result()->fetch_assoc()['admin_count'];
    
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_role = $stmt->get_result()->fetch_assoc()['role'];
    
    if ($user_role === 'admin' && $admin_count <= 1) {
        $_SESSION['error'] = "Cannot delete the last admin user";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete cart items first
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Delete order items
            $stmt = $conn->prepare("DELETE oi FROM order_items oi INNER JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Delete orders
            $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Finally delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = "User deleted successfully";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = "Failed to delete user: " . $e->getMessage();
        }
    }
    
    header('Location: users.php');
    exit;
}

// Get user statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as user_count,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users
    FROM users
");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get all users with their order counts
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(DISTINCT o.id) as order_count,
           COALESCE(SUM(oi.quantity * oi.price), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Beauty Shop Admin</title>
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
        .user-card {
            @apply glass-effect rounded-xl p-6 transition-all duration-300;
        }
        .user-card:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            @apply glass-effect rounded-xl p-6 transition-all duration-300;
        }
        .stat-card:hover {
            transform: translateY(-2px);
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
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="users.php" class="sidebar-link active">
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

                <!-- User Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-primary-500 bg-opacity-20">
                                <i class="fas fa-users text-primary-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Total Users</p>
                                <p class="text-2xl font-semibold text-primary-300"><?php echo $stats['total_users']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                                <i class="fas fa-user-shield text-purple-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Admins</p>
                                <p class="text-2xl font-semibold text-purple-300"><?php echo $stats['admin_count']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                                <i class="fas fa-user text-blue-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">Regular Users</p>
                                <p class="text-2xl font-semibold text-blue-300"><?php echo $stats['user_count']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-user-plus text-green-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-400">New Users (30d)</p>
                                <p class="text-2xl font-semibold text-green-300"><?php echo $stats['new_users']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-primary-300">User Management</h2>
                    <a href="add-user.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-plus mr-2"></i>
                        Add New User
                    </a>
                </div>

                <!-- User Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($users as $user): ?>
                        <div class="user-card">
                            <div class="flex items-center mb-4">
                                <div class="h-16 w-16 rounded-full bg-primary-500 bg-opacity-20 flex items-center justify-center">
                                    <i class="fas fa-user text-primary-400 text-2xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-primary-300">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-400">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="text-center p-3 rounded-lg bg-gray-800">
                                    <p class="text-sm text-gray-400">Orders</p>
                                    <p class="text-lg font-semibold text-primary-300"><?php echo $user['order_count']; ?></p>
                                </div>
                                <div class="text-center p-3 rounded-lg bg-gray-800">
                                    <p class="text-sm text-gray-400">Total Spent</p>
                                    <p class="text-lg font-semibold text-primary-300">$<?php echo number_format($user['total_spent'], 2); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                                <div class="flex space-x-2">
                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" 
                                       class="text-primary-400 hover:text-primary-300 p-2 rounded-full hover-glow">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] !== getCurrentUserId()): ?>
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all their cart items, orders, and order items.');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" 
                                                    class="text-red-400 hover:text-red-300 p-2 rounded-full hover-glow">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-sm text-gray-400">
                                Joined <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 