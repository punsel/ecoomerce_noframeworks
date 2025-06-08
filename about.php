<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

include 'includes/header.php';
?>

<main class="flex-1 pt-4">
    <div class="bg-[#7851A9] py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">About Us</h1>
                <p class="mt-4 text-lg text-[#FFADC6]">Your trusted partner in trading excellence</p>
            </div>

            <!-- Mission Section -->
            <div class="mt-16 bg-[#FFE4EC] rounded-2xl shadow-xl p-8">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-3xl font-bold text-[#7851A9]">Our Mission</h2>
                    <p class="mt-4 text-lg text-gray-600">
                        We are dedicated to providing exceptional trading services and empowering our clients 
                        with the tools and knowledge they need to succeed in the financial markets.
                    </p>
                </div>
            </div>

            <!-- Values Section -->
            <div class="mt-16">
                <h2 class="text-3xl font-bold text-center text-white mb-12">Our Core Values</h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Integrity -->
                    <div class="bg-[#FFE4EC] rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-200">
                        <div class="text-center">
                            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-[#FF69B4]">
                                <i class="fas fa-shield-alt text-white text-xl"></i>
                            </div>
                            <h3 class="mt-4 text-xl font-bold text-[#7851A9]">Integrity</h3>
                            <p class="mt-2 text-gray-600">
                                We conduct our business with the highest standards of ethics and transparency.
                            </p>
                        </div>
                    </div>

                    <!-- Innovation -->
                    <div class="bg-[#FFE4EC] rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-200">
                        <div class="text-center">
                            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-[#FF69B4]">
                                <i class="fas fa-lightbulb text-white text-xl"></i>
                            </div>
                            <h3 class="mt-4 text-xl font-bold text-[#7851A9]">Innovation</h3>
                            <p class="mt-2 text-gray-600">
                                We continuously evolve and adapt to bring you the latest trading technologies.
                            </p>
                        </div>
                    </div>

                    <!-- Excellence -->
                    <div class="bg-[#FFE4EC] rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-200">
                        <div class="text-center">
                            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-[#FF69B4]">
                                <i class="fas fa-star text-white text-xl"></i>
                            </div>
                            <h3 class="mt-4 text-xl font-bold text-[#7851A9]">Excellence</h3>
                            <p class="mt-2 text-gray-600">
                                We strive for excellence in every aspect of our service delivery.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Section -->
            <div class="mt-16">
                <h2 class="text-3xl font-bold text-center text-white mb-12">Our Leadership Team</h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Team Member 1 -->
                    <div class="bg-[#FFE4EC] rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition-all duration-200">
                        <div class="p-6">
                            <div class="text-center">
                                <div class="mx-auto h-24 w-24 rounded-full bg-[#FF69B4] flex items-center justify-center">
                                    <i class="fas fa-user text-white text-4xl"></i>
                                </div>
                                <h3 class="mt-4 text-xl font-bold text-[#7851A9]">John Doe</h3>
                                <p class="text-[#FF69B4]">Chief Executive Officer</p>
                                <p class="mt-2 text-gray-600">
                                    With over 15 years of experience in financial markets.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 2 -->
                    <div class="bg-[#FFE4EC] rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition-all duration-200">
                        <div class="p-6">
                            <div class="text-center">
                                <div class="mx-auto h-24 w-24 rounded-full bg-[#FF69B4] flex items-center justify-center">
                                    <i class="fas fa-user text-white text-4xl"></i>
                                </div>
                                <h3 class="mt-4 text-xl font-bold text-[#7851A9]">Jane Smith</h3>
                                <p class="text-[#FF69B4]">Chief Operations Officer</p>
                                <p class="mt-2 text-gray-600">
                                    Expert in operational excellence and process optimization.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 3 -->
                    <div class="bg-[#FFE4EC] rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition-all duration-200">
                        <div class="p-6">
                            <div class="text-center">
                                <div class="mx-auto h-24 w-24 rounded-full bg-[#FF69B4] flex items-center justify-center">
                                    <i class="fas fa-user text-white text-4xl"></i>
                                </div>
                                <h3 class="mt-4 text-xl font-bold text-[#7851A9]">Mike Johnson</h3>
                                <p class="text-[#FF69B4]">Chief Technology Officer</p>
                                <p class="mt-2 text-gray-600">
                                    Leading our technological innovation and digital transformation.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="mt-16 text-center">
                <h2 class="text-3xl font-bold text-white">Ready to Start Trading?</h2>
                <p class="mt-4 text-lg text-[#FFADC6]">Join thousands of successful traders who trust us</p>
                <div class="mt-8">
                    <a href="register.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-[#FF69B4] hover:bg-[#FF69B4]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF69B4] transition-all duration-200">
                        Get Started
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?> 