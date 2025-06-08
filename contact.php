<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Here you would typically send the email or store the message in a database
        // For now, we'll just show a success message
        $success = true;
    }
}

include 'includes/header.php';
?>

<main class="flex-1 pt-4">
    <div class="bg-[#7851A9] py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">Contact Us</h1>
                <p class="mt-4 text-lg text-[#FFADC6]">We'd love to hear from you</p>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Contact Information -->
                <div class="bg-[#FFE4EC] p-8 rounded-2xl shadow-xl space-y-8">
                    <div>
                        <h2 class="text-2xl font-bold text-[#7851A9]">Get in Touch</h2>
                        <p class="mt-4 text-gray-600">
                            Have questions about our products or services? We're here to help. 
                            Fill out the form and we'll get back to you as soon as possible.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-[#FF69B4] text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-[#7851A9]">Address</h3>
                                <p class="mt-1 text-gray-600">123 Trading Street, Business District, City, Country</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-phone text-[#FF69B4] text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-[#7851A9]">Phone</h3>
                                <p class="mt-1 text-gray-600">+1 (555) 123-4567</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope text-[#FF69B4] text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-[#7851A9]">Email</h3>
                                <p class="mt-1 text-gray-600">contact@vrtradingshop.com</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8">
                        <h3 class="text-lg font-medium text-[#7851A9]">Business Hours</h3>
                        <dl class="mt-4 space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Monday - Friday</dt>
                                <dd class="text-[#7851A9] font-medium">9:00 AM - 6:00 PM</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Saturday</dt>
                                <dd class="text-[#7851A9] font-medium">10:00 AM - 4:00 PM</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Sunday</dt>
                                <dd class="text-[#7851A9] font-medium">Closed</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-[#FFE4EC] p-8 rounded-2xl shadow-xl">
                    <?php if ($success): ?>
                        <div class="rounded-lg bg-[#FAF3DD] border-l-4 border-[#FF69B4] p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-[#FF69B4]"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-[#7851A9]">
                                        Thank you for your message. We'll get back to you soon!
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="rounded-lg bg-[#FAF3DD] border-l-4 border-[#FF69B4] p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-[#FF69B4]"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-[#7851A9]">
                                        <?php echo htmlspecialchars($error); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-[#7851A9]">Name</label>
                            <input type="text" name="name" id="name" required
                                   class="mt-1 block w-full rounded-lg border-gray-200 shadow-sm focus:border-[#FF69B4] focus:ring-[#FF69B4] text-[#7851A9]">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-[#7851A9]">Email</label>
                            <input type="email" name="email" id="email" required
                                   class="mt-1 block w-full rounded-lg border-gray-200 shadow-sm focus:border-[#FF69B4] focus:ring-[#FF69B4] text-[#7851A9]">
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-[#7851A9]">Subject</label>
                            <input type="text" name="subject" id="subject" required
                                   class="mt-1 block w-full rounded-lg border-gray-200 shadow-sm focus:border-[#FF69B4] focus:ring-[#FF69B4] text-[#7851A9]">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-[#7851A9]">Message</label>
                            <textarea name="message" id="message" rows="4" required
                                      class="mt-1 block w-full rounded-lg border-gray-200 shadow-sm focus:border-[#FF69B4] focus:ring-[#FF69B4] text-[#7851A9]"></textarea>
                        </div>

                        <div>
                            <button type="submit"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[#FF69B4] hover:bg-[#FF69B4]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF69B4] transition-all duration-200">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?> 