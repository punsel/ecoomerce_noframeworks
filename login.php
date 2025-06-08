<?php
require_once 'includes/config.php';

$errors = [];

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username)) {
        $errors[] = "Username/Email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        $conn = getDBConnection();
        
        // Check if username is email or username
        $is_email = filter_var($username, FILTER_VALIDATE_EMAIL);
        $field = $is_email ? 'email' : 'username';
        
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE $field = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "User not found";
        }
        
        $stmt->close();
        $conn->close();
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #7851A9;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .input-focus {
            transition: all 0.3s ease;
        }
        .input-focus:focus {
            border-color: #FFADC6;
            box-shadow: 0 0 0 3px rgba(255, 173, 198, 0.1);
        }
        .btn-hover {
            transition: all 0.3s ease;
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(120, 81, 169, 0.1);
        }
        .custom-bg {
            background-color: #7851A9;
        }
        .custom-text {
            color: #7851A9;
        }
        .custom-accent {
            color: #FFADC6;
        }
        .custom-border {
            border-color: #FFADC6;
        }
        iframe {
            background-color: #7851A9 !important;
        }
    </style>
</head>
<body class="bg-[#7851A9] min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-[#FFE4EC] rounded-2xl shadow-xl p-10 space-y-8 fade-in mx-auto my-8 border border-[#FFADC6]/20">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold custom-text">Welcome Back</h2>
            <p class="mt-2 text-sm text-gray-500">
                Don't have an account? 
                <a href="register.php" class="font-medium custom-accent hover:opacity-80 transition-colors duration-200">
                    Sign up
                </a>
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-[#FAF3DD] border-l-4 border-[#FFADC6] p-4 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle custom-accent mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium custom-text">Please fix the following errors:</h3>
                        <ul class="mt-2 text-sm text-gray-600 list-disc pl-5">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form class="space-y-4" action="login.php" method="POST">
            <div class="space-y-3">
                <div>
                    <label for="username" class="block text-sm font-medium custom-text">Username or Email</label>
                    <div class="mt-1 relative">
                        <input id="username" name="username" type="text" required 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg custom-text placeholder-gray-400 focus:outline-none input-focus" 
                               placeholder="Enter username or email" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        <i class="fas fa-user absolute right-3 top-1/2 transform -translate-y-1/2 text-[#FF69B4]"></i>
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium custom-text">Password</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" required 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg custom-text placeholder-gray-400 focus:outline-none input-focus" 
                               placeholder="Enter password">
                        <i class="fas fa-lock absolute right-3 top-1/2 transform -translate-y-1/2 text-[#FF69B4]"></i>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox" 
                           class="h-4 w-4 text-[#FF69B4] focus:ring-[#FF69B4] border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm custom-text">Remember me</label>
                </div>
                <a href="forgot-password.php" class="text-sm font-medium custom-accent hover:opacity-80 transition-colors duration-200">
                    Forgot password?
                </a>
            </div>

            <button type="submit" 
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg text-white bg-[#FF69B4] hover:bg-[#FF69B4]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF69B4] btn-hover">
                <i class="fas fa-sign-in-alt mr-2"></i> Sign In
            </button>
        </form>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>