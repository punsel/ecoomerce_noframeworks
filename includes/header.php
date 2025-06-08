<?php
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Shop</title>
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
                        },
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
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 25px rgba(236, 72, 153, 0.3)',
                        'glow-hover': '0 0 35px rgba(236, 72, 153, 0.5)',
                    },
                    backgroundImage: {
                        'gradient-nav': 'linear-gradient(135deg, #1a1a1a 0%, #2d1b3a 50%, #3b1a4a 100%)',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(-10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                    }
                }
            }
        }
    </script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #1a1a1a;
            color: #f3f4f6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .nav-link {
            @apply relative px-6 py-3 text-xl font-medium transition-all duration-300 ease-in-out;
        }
        .nav-link::after {
            content: '';
            @apply absolute bottom-0 left-0 w-0 h-0.5 bg-primary-400 transition-all duration-300 ease-in-out;
        }
        .nav-link:hover::after {
            @apply w-full;
        }
        .nav-link.active {
            @apply text-primary-200 font-semibold;
        }
        .nav-link.active::after {
            @apply w-full bg-primary-200;
        }
        .cart-badge {
            @apply absolute -top-3 -right-3 bg-primary-500 text-white text-sm rounded-full h-6 w-6 flex items-center justify-center transform scale-100 transition-transform duration-300;
        }
        .cart-badge:hover {
            @apply scale-110;
        }
        .dropdown-menu {
            @apply absolute mt-4 bg-gray-800 rounded-xl shadow-glow opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top;
        }
        .profile-dropdown {
            @apply hidden bg-gray-800 rounded-xl shadow-glow;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
        }
        .profile-dropdown.show {
            @apply block;
        }
        .profile-dropdown a {
            @apply block px-5 py-3 text-base text-gray-200 hover:bg-primary-900 hover:text-primary-100 transition-colors;
        }
        .profile-dropdown a:not(.show) {
            display: none;
        }
        main {
            flex: 1 0 auto;
            width: 100%;
        }
        .nav-container {
            animation: fade-in 0.5s ease-out;
        }
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        .mobile-menu.active {
            transform: translateX(0);
        }
        .profile-dropdown {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }
        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        /* Add page transition effect */
        .page-content {
            opacity: 0;
            animation: fadeIn 0.3s ease-in forwards;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-900">
    <header class="fixed top-0 left-0 right-0 bg-gray-900 shadow-lg z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo - Left -->
                <div class="flex-shrink-0">
                    <a href="index.php" class="text-3xl font-extrabold text-primary-400 tracking-tight hover:text-primary-300 transition-colors">Wilk Cosmetics</a>
                </div>

                <!-- Navigation - Center -->
                <div class="hidden md:flex items-center justify-center flex-1">
                    <div class="flex items-center space-x-8">
                        <a href="products.php" class="px-3 py-2 text-gray-300 hover:text-primary-400 transition-colors font-semibold text-lg">Products</a>
                        <a href="about.php" class="px-3 py-2 text-gray-300 hover:text-primary-400 transition-colors font-semibold text-lg">About</a>
                        <a href="contact.php" class="px-3 py-2 text-gray-300 hover:text-primary-400 transition-colors font-semibold text-lg">Contact</a>
                    </div>
                </div>

                <!-- User Actions - Right -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if (isLoggedIn()): ?>
                        <a href="cart.php" class="relative px-3 py-2 text-gray-300 hover:text-primary-400 transition-colors flex items-center font-semibold text-lg">
                            <div class="relative">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                <?php
                                $cart_count = 0;
                                if (isLoggedIn()) {
                                    $conn = getDBConnection();
                                    $user_id = getCurrentUserId();
                                    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?");
                                    $stmt->bind_param("i", $user_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $cart_count = $result->fetch_assoc()['count'] ?? 0;
                                    $stmt->close();
                                    $conn->close();
                                }
                                if ($cart_count > 0):
                                ?>
                                <span class="absolute -top-3 -right-3 bg-primary-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold transform scale-100 transition-transform duration-300 hover:scale-110">
                                    <?php echo $cart_count; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <span>Cart</span>
                        </a>
                        <a href="orders.php" class="px-3 py-2 text-gray-300 hover:text-primary-400 transition-colors flex items-center font-semibold text-lg">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            <span>Orders</span>
                        </a>
                        <a href="profile.php" class="px-3 py-2 text-gray-300 hover:text-primary-400 transition-colors flex items-center font-semibold text-lg">
                            <i class="fas fa-user mr-2"></i>
                            <span>Profile</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 text-gray-300 hover:text-primary-400 transition-colors font-semibold text-lg">Login</a>
                        <a href="register.php" class="px-4 py-2 text-gray-300 hover:text-primary-400 transition-colors font-semibold text-lg">Sign Up</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="cart.php" class="relative px-4 py-2 text-gray-400 hover:text-primary-400 transition-colors flex items-center">
                            <div class="relative">
                                <i class="fas fa-shopping-cart"></i>
                                <?php
                                $cart_count = 0;
                                if (isLoggedIn()) {
                                    $conn = getDBConnection();
                                    $user_id = getCurrentUserId();
                                    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?");
                                    $stmt->bind_param("i", $user_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $cart_count = $result->fetch_assoc()['count'] ?? 0;
                                    $stmt->close();
                                    $conn->close();
                                }
                                if ($cart_count > 0):
                                ?>
                                <span class="absolute -top-3 -right-3 bg-primary-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold transform scale-100 transition-transform duration-300 hover:scale-110">
                                    <?php echo $cart_count; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <a href="orders.php" class="px-4 py-2 text-gray-400 hover:text-primary-400 transition-colors flex items-center">
                            <i class="fas fa-shopping-bag"></i>
                        </a>
                        <a href="profile.php" class="px-4 py-2 text-gray-400 hover:text-primary-400 transition-colors flex items-center">
                            <i class="fas fa-user"></i>
                        </a>
                    <?php endif; ?>
                    <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-primary-400 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-400" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile menu -->
        <div class="mobile-menu md:hidden fixed inset-0 z-50 bg-gray-900" id="mobile-menu">
            <div class="pt-5 pb-6 px-5">
                <div class="flex items-center justify-between">
                    <div>
                        <a href="index.php" class="text-3xl font-extrabold text-primary-400 tracking-tight">Wilk Cosmetics</a>
                    </div>
                    <div class="-mr-2">
                        <button type="button" class="mobile-menu-close inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-primary-400 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-400">
                            <span class="sr-only">Close menu</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-6">
                    <nav class="grid gap-y-8">
                        <a href="products.php" class="flex items-center px-3 py-5 text-base font-semibold text-gray-300 hover:text-primary-400 hover:bg-gray-800 rounded-md">
                            <i class="fas fa-shopping-bag mr-3 text-primary-400"></i>
                            Products
                        </a>
                        <a href="about.php" class="flex items-center px-3 py-5 text-base font-semibold text-gray-300 hover:text-primary-400 hover:bg-gray-800 rounded-md">
                            <i class="fas fa-info-circle mr-3 text-primary-400"></i>
                            About
                        </a>
                        <a href="contact.php" class="flex items-center px-3 py-5 text-base font-semibold text-gray-300 hover:text-primary-400 hover:bg-gray-800 rounded-md">
                            <i class="fas fa-envelope mr-3 text-primary-400"></i>
                            Contact
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Add padding to main content to prevent overlap with fixed header -->
    <div class="flex-1 pt-16">
    <main class="page-content flex-1 pt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="w-full px-4 sm:px-6 lg:px-8 py-4">
                <div class="rounded-md bg-green-50 p-4">
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
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="w-full px-4 sm:px-6 lg:px-8 py-4">
                <div class="rounded-md bg-red-50 p-4">
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
            </div>
        <?php endif; ?> 
        </main>
    </div>

    <script>
        // Mobile menu functionality
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenuClose = document.querySelector('.mobile-menu-close');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.add('active');
        });

        mobileMenuClose.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                mobileMenu.classList.remove('active');
            }
        });
    </script>
</body>
</html>