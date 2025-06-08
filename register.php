<?php
require_once 'includes/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
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

    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email or username already exists
    if (empty($errors)) {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email or username already exists";
        } else {
            // Hash password and create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, username, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $email, $username, $hashed_password);
            
            if ($stmt->execute()) {
                $success = true;
                // Redirect to login page after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
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
    <title>Sign Up</title>
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
            <h2 class="text-3xl font-bold custom-text">Create Account</h2>
            <p class="mt-2 text-sm text-gray-500">
                Already have an account? 
                <a href="login.php" class="font-medium custom-accent hover:opacity-80 transition-colors duration-200">
                    Sign in
                </a>
            </p>
        </div>

        <?php if ($success): ?>
            <div class="bg-[#FAF3DD] border-l-4 border-[#FFADC6] p-4 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle custom-accent mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium custom-text">Registration successful!</h3>
                        <p class="text-sm text-gray-600 mt-1">Redirecting to login page...</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

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

        <form class="space-y-4" action="register.php" method="POST">
            <div class="space-y-3">
                <div>
                    <label for="full_name" class="block text-sm font-medium custom-text">Full Name</label>
                    <div class="mt-1 relative">
                        <input id="full_name" name="full_name" type="text" required 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg custom-text placeholder-gray-400 focus:outline-none input-focus" 
                               placeholder="Enter your full name"
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        <i class="fas fa-user absolute right-3 top-1/2 transform -translate-y-1/2 text-[#FF69B4]"></i>
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium custom-text">Email</label>
                    <div class="mt-1 relative">
                        <input id="email" name="email" type="email" required 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg custom-text placeholder-gray-400 focus:outline-none input-focus" 
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <i class="fas fa-envelope absolute right-3 top-1/2 transform -translate-y-1/2 text-[#FF69B4]"></i>
                    </div>
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium custom-text">Username</label>
                    <div class="mt-1 relative">
                        <input id="username" name="username" type="text" required 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg custom-text placeholder-gray-400 focus:outline-none input-focus" 
                               placeholder="Choose a username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        <i class="fas fa-at absolute right-3 top-1/2 transform -translate-y-1/2 text-[#FF69B4]"></i>
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium custom-text">Password</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" required 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg custom-text placeholder-gray-400 focus:outline-none input-focus" 
                               placeholder="Create a password">
                        <i class="fas fa-lock absolute right-3 top-1/2 transform -translate-y-1/2 text-[#FF69B4]"></i>
                    </div>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium custom-text">Confirm Password</label>
                    <div class="mt-1 relative">
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg custom-text placeholder-gray-400 focus:outline-none input-focus" 
                               placeholder="Confirm your password">
                        <i class="fas fa-lock absolute right-3 top-1/2 transform -translate-y-1/2 text-[#FF69B4]"></i>
                    </div>
                </div>
            </div>

            <button type="submit" 
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg text-white bg-[#FF69B4] hover:bg-[#FF69B4]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF69B4] btn-hover">
                <i class="fas fa-user-plus mr-2"></i> Create Account
            </button>
        </form>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?> 