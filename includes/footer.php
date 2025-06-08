    </main>
    </div>
    <footer class="bg-gray-900 mt-auto">
        <div class="w-full py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-primary-400 mb-4">About Us</h3>
                    <p class="text-gray-400">
                        Beauty Shop is your one-stop destination for all your beauty needs. We offer high-quality products at competitive prices.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary-400 mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="index.php" class="text-gray-400 hover:text-primary-400 transition-colors">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="products.php" class="text-gray-400 hover:text-primary-400 transition-colors">
                                Products
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li>
                                <a href="profile.php" class="text-gray-400 hover:text-primary-400 transition-colors">
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a href="orders.php" class="text-gray-400 hover:text-primary-400 transition-colors">
                                    Orders
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <a href="login.php" class="text-gray-400 hover:text-primary-400 transition-colors">
                                    Login
                                </a>
                            </li>
                            <li>
                                <a href="register.php" class="text-gray-400 hover:text-primary-400 transition-colors">
                                    Register
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary-400 mb-4">Contact Us</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            <span>support@beautyshop.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <span>+1 (555) 123-4567</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span>123 Beauty Street, City, Country</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800">
                <p class="text-center text-gray-400">
                    &copy; <?php echo date('Y'); ?> Beauty Shop. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
    <script>
        // Toggle user menu
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');
        
        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', () => {
                userMenu.classList.toggle('hidden');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html> 