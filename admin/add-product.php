<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require admin access
requireAdmin();

$success = false;
$errors = [];
$product = [
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'category' => '',
    'brand' => '',
    'image_url' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product['name'] = trim($_POST['name'] ?? '');
    $product['description'] = trim($_POST['description'] ?? '');
    $product['price'] = trim($_POST['price'] ?? '');
    $product['stock'] = trim($_POST['stock'] ?? '');
    $product['category'] = trim($_POST['category'] ?? '');
    $product['brand'] = trim($_POST['brand'] ?? '');

    // Validation
    if (empty($product['name'])) {
        $errors[] = "Product name is required";
    }

    if (empty($product['description'])) {
        $errors[] = "Product description is required";
    }

    if (empty($product['price'])) {
        $errors[] = "Product price is required";
    } elseif (!is_numeric($product['price']) || $product['price'] < 0) {
        $errors[] = "Product price must be a positive number";
    }

    if (empty($product['stock'])) {
        $errors[] = "Product stock is required";
    } elseif (!is_numeric($product['stock']) || $product['stock'] < 0) {
        $errors[] = "Product stock must be a positive number";
    }

    if (empty($product['category'])) {
        $errors[] = "Product category is required";
    }

    if (empty($product['brand'])) {
        $errors[] = "Product brand is required";
    }

    // Handle image upload
    $image_path = 'assets/images/default-product.jpg'; // Default image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF images are allowed.";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "File is too large. Maximum size is 5MB.";
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $upload_path = '../uploads/products/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $image_path = 'uploads/products/' . $filename;
            } else {
                $errors[] = "Failed to upload image. Please try again.";
            }
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Error uploading image. Please try again.";
    }

    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            INSERT INTO products (name, description, price, stock, category, brand, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssdiiss", 
            $product['name'], 
            $product['description'], 
            $product['price'], 
            $product['stock'], 
            $product['category'], 
            $product['brand'],
            $image_path
        );

        if ($stmt->execute()) {
            $success = true;
            // Redirect to edit page for the new product
            header('Location: edit-product.php?id=' . $stmt->insert_id);
            exit;
        } else {
            $errors[] = "Failed to save product. Please try again.";
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Beauty Shop Admin</title>
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

        <main class="flex-1 p-8 space-y-6">
            <div class="glass-effect rounded-lg">
                <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                    <!-- Page header -->
                    <div class="md:flex md:items-center md:justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-2xl font-bold leading-7 text-gray-100 sm:text-3xl sm:truncate">
                                Add New Product
                            </h2>
                        </div>
                        <div class="mt-4 flex md:mt-0 md:ml-4">
                            <a href="products.php" 
                               class="ml-3 inline-flex items-center px-4 py-2 border border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-300 bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                Back to Products
                            </a>
                        </div>
                    </div>

                    <?php if ($success): ?>
                        <div class="mt-4 rounded-md bg-green-900 bg-opacity-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-300">
                                        Product saved successfully!
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="mt-4 rounded-md bg-red-900 bg-opacity-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-300">There were errors with your submission:</h3>
                                    <div class="mt-2 text-sm text-red-200">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form class="mt-8 space-y-8 divide-y divide-gray-700" 
                          action="add-product.php" 
                          method="POST" 
                          enctype="multipart/form-data">
                        <div class="space-y-8 divide-y divide-gray-700">
                            <div>
                                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-4">
                                        <label for="name" class="block text-sm font-medium text-gray-300">Product Name</label>
                                        <div class="mt-1">
                                            <input type="text" name="name" id="name" 
                                                   class="shadow-sm focus:ring-pink-500 focus:border-pink-500 block w-full sm:text-sm border-gray-600 rounded-md bg-gray-700 text-gray-100"
                                                   value="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                                        <div class="mt-1">
                                            <textarea id="description" name="description" rows="3" 
                                                      class="shadow-sm focus:ring-pink-500 focus:border-pink-500 block w-full sm:text-sm border-gray-600 rounded-md bg-gray-700 text-gray-100"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="price" class="block text-sm font-medium text-gray-300">Price</label>
                                        <div class="mt-1">
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-400 sm:text-sm">$</span>
                                                </div>
                                                <input type="number" name="price" id="price" step="0.01" min="0"
                                                       class="focus:ring-pink-500 focus:border-pink-500 block w-full pl-7 pr-12 sm:text-sm border-gray-600 rounded-md bg-gray-700 text-gray-100"
                                                       value="<?php echo htmlspecialchars($product['price']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="stock" class="block text-sm font-medium text-gray-300">Stock</label>
                                        <div class="mt-1">
                                            <input type="number" name="stock" id="stock" min="0"
                                                   class="shadow-sm focus:ring-pink-500 focus:border-pink-500 block w-full sm:text-sm border-gray-600 rounded-md bg-gray-700 text-gray-100"
                                                   value="<?php echo htmlspecialchars($product['stock']); ?>">
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label for="category" class="block text-sm font-medium text-gray-300">Category</label>
                                        <div class="mt-1">
                                            <select id="category" name="category" 
                                                    class="shadow-sm focus:ring-pink-500 focus:border-pink-500 block w-full sm:text-sm border-gray-600 rounded-md bg-gray-700 text-gray-100">
                                                <option value="">Select a category</option>
                                                <option value="Skincare" <?php echo $product['category'] === 'Skincare' ? 'selected' : ''; ?>>Skincare</option>
                                                <option value="Makeup" <?php echo $product['category'] === 'Makeup' ? 'selected' : ''; ?>>Makeup</option>
                                                <option value="Haircare" <?php echo $product['category'] === 'Haircare' ? 'selected' : ''; ?>>Haircare</option>
                                                <option value="Fragrance" <?php echo $product['category'] === 'Fragrance' ? 'selected' : ''; ?>>Fragrance</option>
                                                <option value="Bath & Body" <?php echo $product['category'] === 'Bath & Body' ? 'selected' : ''; ?>>Bath & Body</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label for="brand" class="block text-sm font-medium text-gray-300">Brand</label>
                                        <div class="mt-1">
                                            <input type="text" name="brand" id="brand" 
                                                   class="shadow-sm focus:ring-pink-500 focus:border-pink-500 block w-full sm:text-sm border-gray-600 rounded-md bg-gray-700 text-gray-100"
                                                   value="<?php echo htmlspecialchars($product['brand']); ?>">
                                        </div>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <label for="image" class="block text-sm font-medium text-gray-300">Product Image</label>
                                        <div class="mt-1 flex items-center">
                                            <input type="file" name="image" id="image" accept="image/*"
                                                   class="shadow-sm focus:ring-pink-500 focus:border-pink-500 block w-full sm:text-sm border-gray-600 rounded-md bg-gray-700 text-gray-100">
                                        </div>
                                        <p class="mt-2 text-sm text-gray-400">
                                            JPG, PNG or GIF (max. 5MB)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-5">
                            <div class="flex justify-end">
                                <a href="products.php" 
                                   class="bg-gray-800 py-2 px-4 border border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 