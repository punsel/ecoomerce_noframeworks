<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Ensure only admins can access
requireAdmin();

$conn = getDBConnection();

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    
    // Get product image before deletion
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    // Delete the product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Delete the product image if it exists and is not the default image
        if ($product && $product['image_url'] && $product['image_url'] !== 'assets/images/default-product.jpg') {
            $image_path = '../' . $product['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = "Product deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete product";
    }
    
    header('Location: products.php');
    exit;
}

// Get all products
$stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Beauty Shop Admin</title>
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
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="users.php" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="products.php" class="sidebar-link active">
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

                <div class="glass-effect rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-primary-300">Product Management</h2>
                        <a href="add-product.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-plus mr-2"></i>
                            Add New Product
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($products as $product): ?>
                            <div class="glass-effect rounded-lg overflow-hidden">
                                <div class="aspect-w-16 aspect-h-9">
                                    <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'assets/images/default-product.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="w-full h-48 object-cover">
                                </div>
                                <div class="p-4">
                                    <h3 class="text-lg font-medium text-primary-300 mb-2">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </h3>
                                    <p class="text-gray-400 text-sm mb-4">
                                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                    </p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-primary-400 font-medium">
                                            $<?php echo number_format($product['price'], 2); ?>
                                        </span>
                                        <div class="flex space-x-2">
                                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" 
                                               class="text-primary-400 hover:text-primary-300 p-2 rounded-full hover-glow">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" name="delete_product" 
                                                        class="text-red-400 hover:text-red-300 p-2 rounded-full hover-glow">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 