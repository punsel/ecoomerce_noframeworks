<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Get featured products
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM products WHERE stock > 0 ORDER BY RAND() LIMIT 8");
$stmt->execute();
$featured_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="relative bg-gradient-to-r from-pink-50 to-purple-50 pt-24 pb-24">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-16 sm:pb-20 md:pb-24 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
            <main class="mx-auto max-w-7xl px-4 sm:px-6 md:px-8 lg:px-8">
                <div class="sm:text-center lg:text-left pt-12 sm:pt-16 md:pt-20 lg:pt-24">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                        <span class="block animate-slide-up opacity-0" style="animation-delay: 0.2s; animation-fill-mode: forwards;">Discover Your</span>
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-pink-600 to-purple-600 animate-slide-up opacity-0" style="animation-delay: 0.4s; animation-fill-mode: forwards;">Beauty Potential</span>
                    </h1>
                    <p class="mt-6 text-lg text-gray-600 sm:text-xl md:text-2xl animate-slide-up opacity-0" style="animation-delay: 0.6s; animation-fill-mode: forwards;">
                        Explore our curated collection of premium beauty products. From skincare to makeup, find everything you need to enhance your natural beauty.
                    </p>
                    <div class="mt-8 sm:mt-10 sm:flex sm:justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                        <div class="rounded-full shadow-lg hover:shadow-xl transition-all duration-300 animate-slide-up opacity-0" style="animation-delay: 0.8s; animation-fill-mode: forwards;">
                            <a href="products.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-gradient-to-r from-pink-600 to-purple-600 hover:from-pink-700 hover:to-purple-700">
                                Shop Now
                                <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                        <div class="rounded-full shadow-lg hover:shadow-xl transition-all duration-300 animate-slide-up opacity-0" style="animation-delay: 1s; animation-fill-mode: forwards;">
                            <a href="about.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-pink-700 bg-white hover:bg-pink-50">
                                Learn More
                                <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
        <div class="relative h-48 w-full sm:h-64 md:h-80 lg:w-full lg:h-full animate-fade-in opacity-0" style="animation-delay: 0.4s; animation-fill-mode: forwards;">
            <div class="absolute inset-0 bg-gradient-to-r from-pink-600/10 to-purple-600/10"></div>
            <img class="h-full w-full object-cover object-center rounded-l-3xl shadow-xl" 
                 src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                 alt="Beauty products collection">
        </div>
    </div>
</div>

<style>
@keyframes slide-up {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

.animate-slide-up {
    animation: slide-up 0.6s ease-out;
}

.animate-fade-in {
    animation: fade-in 0.8s ease-out;
}

@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

@keyframes fade-in-up {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.5s ease-out forwards;
}

.animation-delay-200 {
    animation-delay: 200ms;
}

.animation-delay-400 {
    animation-delay: 400ms;
}
</style>

<!-- Featured Products Section -->
<div class="bg-[#FFE4EC] py-8 relative">
    <!-- Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-pink-300/30 to-purple-300/30"></div>
    
    <!-- Pattern -->
    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"52\" height=\"26\" viewBox=\"0 0 52 26\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23FF69B4\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M10 10c0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6h2c0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4v2c-3.314 0-6-2.686-6-6 0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6zm25.464-1.95l8.486 8.486-1.414 1.414-8.486-8.486 1.414-1.414z\" /%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    
    <div class="max-w-2xl mx-auto py-4 px-0.5 sm:py-6 sm:px-0.5 lg:max-w-7xl lg:px-0.5 relative">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-4 sm:mb-6 px-0.5 text-center">
            <span class="bg-gradient-to-r from-pink-500 to-purple-600 text-transparent bg-clip-text">Featured Products</span>
            <p class="text-sm sm:text-base text-gray-500 mt-2 font-normal">Discover our handpicked selection of premium beauty essentials</p>
        </h2>
        <div class="grid grid-cols-2 gap-y-4 gap-x-2 sm:gap-y-6 sm:gap-x-4 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
            <?php foreach ($featured_products as $product): ?>
                <div class="group relative bg-white/90 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1 border-2 border-pink-300 hover:border-pink-500">
                    <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-t-xl bg-white">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="block relative">
                            <div class="absolute inset-0 bg-pink-500 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'assets/images/default-product.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="h-32 sm:h-48 w-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                        </a>
                    </div>
                    <div class="p-3 sm:p-4">
                        <div class="mb-2 sm:mb-3">
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 line-clamp-2 group-hover:text-pink-600 transition-colors duration-300">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="hover:text-pink-600 transition-colors">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <p class="mt-1 text-xs sm:text-sm text-gray-500 group-hover:text-gray-600 transition-colors"><?php echo htmlspecialchars($product['brand']); ?></p>
                        </div>
                        <div class="flex items-center justify-between mb-2 sm:mb-3">
                            <p class="text-sm sm:text-base font-bold text-pink-600 group-hover:text-pink-700 transition-colors">$<?php echo number_format($product['price'], 2); ?></p>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full group-hover:bg-green-100 transition-colors">In Stock</span>
                            <?php else: ?>
                                <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-full group-hover:bg-red-100 transition-colors">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 group-hover:text-gray-600 transition-colors">
                                <i class="fas fa-shopping-bag mr-1"></i>
                                <?php 
                                $conn = getDBConnection();
                                $stmt = $conn->prepare("
                                    SELECT SUM(quantity) as total_sold 
                                    FROM order_items oi 
                                    JOIN orders o ON oi.order_id = o.id 
                                    WHERE oi.product_id = ? AND o.status = 'completed'
                                ");
                                $stmt->bind_param("i", $product['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $total_sold = $result->fetch_assoc()['total_sold'] ?? 0;
                                $stmt->close();
                                $conn->close();
                                echo number_format($total_sold) . ' sold';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Categories Section -->
<div class="bg-pink-100 py-12">
    <div class="max-w-7xl mx-auto py-8 px-2 sm:py-12 sm:px-4 lg:px-6">
        <div class="sm:flex sm:items-baseline sm:justify-between mb-6">
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900">
                <span class="bg-gradient-to-r from-pink-500 to-purple-600 text-transparent bg-clip-text">Shop by Category</span>
            </h2>
            <a href="products.php" class="hidden text-sm font-semibold text-pink-600 hover:text-pink-500 sm:block">
                Browse all categories<span aria-hidden="true"> →</span>
            </a>
        </div>

        <!-- Carousel Container -->
        <div class="relative">
            <!-- Navigation Buttons -->
            <button class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 hover:bg-white text-pink-600 p-1.5 rounded-full shadow-lg hover:shadow-xl transition-all duration-300" id="prevCategory">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 hover:bg-white text-pink-600 p-1.5 rounded-full shadow-lg hover:shadow-xl transition-all duration-300" id="nextCategory">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Categories Carousel -->
            <div class="overflow-hidden">
                <div class="flex transition-transform duration-500 ease-in-out" id="categoryCarousel">
                    <div class="w-full sm:w-1/2 lg:w-1/4 flex-shrink-0 px-1">
                        <div class="group relative">
                            <div class="relative w-full h-40 sm:h-56 bg-white rounded-xl overflow-hidden group-hover:opacity-75 border-2 border-pink-300 hover:border-pink-500 transition-all duration-300 transform hover:-translate-y-1 shadow-sm hover:shadow-xl">
                                <img src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Skincare" class="w-full h-full object-center object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <h3 class="text-base font-semibold text-white">Skincare</h3>
                                    <p class="text-xs text-white/80">Discover your perfect routine</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full sm:w-1/2 lg:w-1/4 flex-shrink-0 px-1">
                        <div class="group relative">
                            <div class="relative w-full h-40 sm:h-56 bg-white rounded-xl overflow-hidden group-hover:opacity-75 border-2 border-pink-300 hover:border-pink-500 transition-all duration-300 transform hover:-translate-y-1 shadow-sm hover:shadow-xl">
                                <img src="https://images.pexels.com/photos/3373716/pexels-photo-3373716.jpeg" alt="Makeup" class="w-full h-full object-center object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <h3 class="text-base font-semibold text-white">Makeup</h3>
                                    <p class="text-xs text-white/80">Express your beauty</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full sm:w-1/2 lg:w-1/4 flex-shrink-0 px-1">
                        <div class="group relative">
                            <div class="relative w-full h-40 sm:h-56 bg-white rounded-xl overflow-hidden group-hover:opacity-75 border-2 border-pink-300 hover:border-pink-500 transition-all duration-300 transform hover:-translate-y-1 shadow-sm hover:shadow-xl">
                                <img src="https://images.pexels.com/photos/3685530/pexels-photo-3685530.jpeg" alt="Fragrance" class="w-full h-full object-center object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <h3 class="text-base font-semibold text-white">Fragrance</h3>
                                    <p class="text-xs text-white/80">Find your signature scent</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full sm:w-1/2 lg:w-1/4 flex-shrink-0 px-1">
                        <div class="group relative">
                            <div class="relative w-full h-40 sm:h-56 bg-white rounded-xl overflow-hidden group-hover:opacity-75 border-2 border-pink-300 hover:border-pink-500 transition-all duration-300 transform hover:-translate-y-1 shadow-sm hover:shadow-xl">
                                <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Haircare" class="w-full h-full object-center object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <h3 class="text-base font-semibold text-white">Haircare</h3>
                                    <p class="text-xs text-white/80">Nourish your locks</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 sm:hidden">
            <a href="products.php" class="block text-sm font-semibold text-pink-600 hover:text-pink-500">
                Browse all categories<span aria-hidden="true"> →</span>
            </a>
        </div>
    </div>
</div>

<!-- Newsletter Section -->
<div class="bg-pink-50 py-12">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:py-10 lg:px-8 lg:flex lg:items-center">
        <div class="lg:w-0 lg:flex-1">
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-gray-900">
                Sign up for our newsletter
            </h2>
            <p class="mt-1 max-w-3xl text-sm text-gray-500">
                Stay updated with our latest products and exclusive offers.
            </p>
        </div>
        <div class="mt-4 lg:mt-0 lg:ml-6">
            <form class="sm:flex">
                <label for="newsletter-email" class="sr-only">Email address</label>
                <input id="newsletter-email" name="email" type="email" required 
                       class="w-full px-3 py-2 border border-gray-300 shadow-sm placeholder-gray-400 focus:ring-pink-500 focus:border-pink-500 sm:max-w-xs rounded-md" 
                       placeholder="Enter your email">
                <div class="mt-2 sm:mt-0 sm:ml-3 sm:flex-shrink-0">
                    <button type="submit" 
                            class="w-full flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Subscribe
                    </button>
                </div>
            </form>
            <p class="mt-2 text-xs text-gray-500">
                We care about your data. Read our
                <a href="privacy.php" class="font-medium text-pink-600 hover:text-pink-500">
                    Privacy Policy
                </a>.
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Add this script at the end of the file, before </body> -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('categoryCarousel');
    const prevButton = document.getElementById('prevCategory');
    const nextButton = document.getElementById('nextCategory');
    let currentPosition = 0;
    const cardWidth = carousel.querySelector('.flex-shrink-0').offsetWidth;
    const totalCards = carousel.children.length;
    const visibleCards = window.innerWidth < 640 ? 1 : window.innerWidth < 1024 ? 2 : 4;
    const maxPosition = -(totalCards - visibleCards) * cardWidth;

    function updateCarousel() {
        carousel.style.transform = `translateX(${currentPosition}px)`;
    }

    prevButton.addEventListener('click', () => {
        currentPosition = Math.min(currentPosition + cardWidth, 0);
        updateCarousel();
    });

    nextButton.addEventListener('click', () => {
        currentPosition = Math.max(currentPosition - cardWidth, maxPosition);
        updateCarousel();
    });

    // Update on window resize
    window.addEventListener('resize', () => {
        const newCardWidth = carousel.querySelector('.flex-shrink-0').offsetWidth;
        const newVisibleCards = window.innerWidth < 640 ? 1 : window.innerWidth < 1024 ? 2 : 4;
        const newMaxPosition = -(totalCards - newVisibleCards) * newCardWidth;
        
        if (currentPosition < newMaxPosition) {
            currentPosition = newMaxPosition;
            updateCarousel();
        }
    });
});

function getCartCount() {
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_cart_count'
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('cart-count').innerText = data.cart_count;
    })
    .catch(error => console.error('Error:', error));
}
getCartCount();
</script> 