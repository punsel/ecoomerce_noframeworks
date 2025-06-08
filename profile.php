<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require login to access this page
requireLogin();

$success = false;
$errors = [];

// Get user data
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as total_items,
           SUM(oi.quantity) as total_quantity
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email is already taken by another user
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email is already taken";
        }
        $stmt->close();
    }

    // Password change validation
    if (!empty($current_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }

        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long";
        }

        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }

    if (empty($errors)) {
        // Update user information
        if (!empty($current_password)) {
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $full_name, $email, $hashed_password, $_SESSION['user_id']);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $full_name, $email, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            $success = true;
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    }
}

$conn->close();

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Beauty Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pink: {
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
                        },
                        neutral: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            900: '#0f172a',
                        }
                    },
                    boxShadow: {
                        'modern': '0 4px 15px rgba(236, 72, 153, 0.2)',
                        'modern-hover': '0 6px 20px rgba(236, 72, 153, 0.3)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #fdf2f8 0%, #f1f5f9 100%);
            font-family: 'Inter', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 1rem;
            transition: all 0.3s ease-in-out;
        }
        .glass-card:hover {
            box-shadow: 0 6px 20px rgba(236, 72, 153, 0.3);
        }
        .input-field {
            @apply block w-full rounded-lg border-pink-200 focus:ring-pink-400 focus:border-pink-400 transition-all duration-300 text-black;
        }
        .btn-primary {
            @apply inline-flex justify-center py-3 px-6 border border-transparent rounded-lg text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-400 transition-all duration-300;
        }
        .alert {
            @apply rounded-lg p-4 flex items-center space-x-3;
        }
        .alert-success {
            @apply bg-pink-50 text-pink-800;
        }
        .alert-error {
            @apply bg-red-50 text-red-800;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <div class="flex-grow">
        <div class="max-w-2xl mx-auto py-6 sm:py-8 md:py-12 px-4 sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="glass-card p-6 sm:p-8 mb-6 sm:mb-8">
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 rounded-full bg-pink-100 flex items-center justify-center">
                        <i class="fas fa-user text-4xl text-pink-600"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-pink-700"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                        <p class="text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Profile Settings -->
            <div class="glass-card p-4 sm:p-6 md:p-8">
                <h2 class="text-xl sm:text-2xl font-bold text-pink-700 mb-6">Profile Settings</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success mb-4 sm:mb-6">
                        <i class="fas fa-check-circle text-pink-600"></i>
                        <p class="text-sm font-medium">Profile updated successfully!</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error mb-4 sm:mb-6">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                        <div>
                            <h3 class="text-sm font-medium">There were errors with your submission:</h3>
                            <ul class="list-disc pl-5 mt-2 text-sm">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form class="space-y-6 sm:space-y-8" action="profile.php" method="POST">
                    <div class="space-y-4 sm:space-y-6">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-pink-900">Full Name</label>
                            <div class="mt-1">
                                <input type="text" name="full_name" id="full_name" 
                                       class="input-field text-black"
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-pink-900">Email</label>
                            <div class="mt-1">
                                <input type="email" name="email" id="email" 
                                       class="input-field text-black"
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-pink-900">Username</label>
                            <div class="mt-1">
                                <input type="text" id="username" 
                                       class="input-field bg-pink-50 cursor-not-allowed text-black"
                                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            </div>
                            <p class="mt-2 text-sm text-pink-600">Username cannot be changed</p>
                        </div>

                        <div class="border-t border-pink-200 pt-4 sm:pt-6">
                            <h3 class="text-base sm:text-lg font-medium text-pink-900">Change Password</h3>
                            <p class="mt-1 text-sm text-pink-600">Leave blank if you don't want to change your password</p>

                            <div class="mt-4 sm:mt-6 space-y-4 sm:space-y-6">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-pink-900">Current Password</label>
                                    <div class="mt-1">
                                        <input type="password" name="current_password" id="current_password" 
                                               class="input-field text-black">
                                    </div>
                                </div>

                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-pink-900">New Password</label>
                                    <div class="mt-1">
                                        <input type="password" name="new_password" id="new_password" 
                                               class="input-field text-black">
                                    </div>
                                </div>

                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-pink-900">Confirm New Password</label>
                                    <div class="mt-1">
                                        <input type="password" name="confirm_password" id="confirm_password" 
                                               class="input-field text-black">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 sm:pt-5 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <button type="submit" class="w-full sm:w-auto btn-primary">
                            Save Changes
                        </button>
                        <a href="logout.php" class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-red-300 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400 transition-all duration-300">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </a>
                    </div>
                </form>
            </div>

            <!-- Recent Orders Section -->
            <div class="glass-card p-4 sm:p-6 md:p-8 mt-6 sm:mt-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0 mb-4 sm:mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-pink-700">Recent Orders</h2>
                    <a href="orders.php" class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                        View All Orders <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <?php if (empty($recent_orders)): ?>
                    <div class="text-center py-6 sm:py-8">
                        <p class="text-gray-500">You haven't placed any orders yet.</p>
                        <a href="products.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700">
                            Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0">
                                    <div>
                                        <h3 class="text-base sm:text-lg font-medium text-gray-900">
                                            Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
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
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500">Items</p>
                                        <p class="font-medium text-gray-900"><?php echo $order['total_items']; ?> items</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Total</p>
                                        <p class="font-medium text-gray-900">$<?php echo number_format($order['total_amount'], 2); ?></p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                       class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                                        View Details <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 