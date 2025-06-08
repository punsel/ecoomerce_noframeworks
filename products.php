<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$brand = $_GET['brand'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;

// Build query
$where_conditions = ['stock > 0'];
$params = [];
$types = '';

if ($search) {
    $where_conditions[] = '(name LIKE ? OR description LIKE ?)';
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($category) {
    $where_conditions[] = 'category = ?';
    $params[] = $category;
    $types .= 's';
}

if ($brand) {
    $where_conditions[] = 'brand = ?';
    $params[] = $brand;
    $types .= 's';
}

if ($min_price !== '') {
    $where_conditions[] = 'price >= ?';
    $params[] = floatval($min_price);
    $types .= 'd';
}

if ($max_price !== '') {
    $where_conditions[] = 'price <= ?';
    $params[] = floatval($max_price);
    $types .= 'd';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$conn = getDBConnection();
$count_query = "SELECT COUNT(*) as total FROM products $where_clause";
if (!empty($params)) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;

// Get sort parameters
$sort_parts = explode('_', $sort);
$sort_field = $sort_parts[0];
$sort_direction = $sort_parts[1] ?? 'asc';

$valid_sort_fields = ['name', 'price', 'created_at'];
$valid_sort_directions = ['asc', 'desc'];

if (!in_array($sort_field, $valid_sort_fields)) {
    $sort_field = 'name';
}
if (!in_array($sort_direction, $valid_sort_directions)) {
    $sort_direction = 'asc';
}

// Get products
$query = "SELECT * FROM products $where_clause ORDER BY $sort_field $sort_direction LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $types .= 'ii';
    $params[] = $per_page;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}

$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get unique categories and brands for filters
$categories = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category")->fetch_all(MYSQLI_ASSOC);
$brands = $conn->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL ORDER BY brand")->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

include 'includes/header.php';
?>

<div class="flex flex-col min-h-screen">
    <!-- Main Content -->
    <main class="flex-1 bg-white relative">
        <div class="w-full px-0">
            <!-- Search and Filters Bar -->
            <div class="sticky top-16 z-40 bg-white border-b border-gray-200">
                <div class="w-full px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                        <!-- Search Bar -->
                        <div class="w-full sm:w-64">
                            <form action="products.php" method="GET" class="w-full">
                                <div class="relative">
                                    <input type="text" 
                                           name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="Search products..."
                                           class="w-full rounded-lg border-gray-300 pl-10 pr-4 py-2 focus:border-pink-500 focus:ring-pink-500 shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Filter Spinners -->
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Category Filter -->
                            <div class="relative">
                                <select name="category" onchange="this.form.submit()" class="block w-full rounded-lg border-gray-300 pl-3 pr-10 py-2 text-base focus:border-pink-500 focus:ring-pink-500 sm:text-sm text-gray-900">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['category']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Brand Filter -->
                            <div class="relative">
                                <select name="brand" onchange="this.form.submit()" class="block w-full rounded-lg border-gray-300 pl-3 pr-10 py-2 text-base focus:border-pink-500 focus:ring-pink-500 sm:text-sm text-gray-900">
                                    <option value="">All Brands</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?php echo htmlspecialchars($b['brand']); ?>" <?php echo $brand === $b['brand'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($b['brand']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="flex items-center space-x-2">
                                <input type="number" 
                                       name="min_price" 
                                       placeholder="Min" 
                                       value="<?php echo htmlspecialchars($min_price); ?>"
                                       class="block w-24 rounded-lg border-gray-300 pl-3 pr-3 py-2 text-base focus:border-pink-500 focus:ring-pink-500 sm:text-sm text-gray-900">
                                <span class="text-gray-500">-</span>
                                <input type="number" 
                                       name="max_price" 
                                       placeholder="Max" 
                                       value="<?php echo htmlspecialchars($max_price); ?>"
                                       class="block w-24 rounded-lg border-gray-300 pl-3 pr-3 py-2 text-base focus:border-pink-500 focus:ring-pink-500 sm:text-sm text-gray-900">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                    Apply
                                </button>
                            </div>

                            <!-- Sort -->
                            <div class="relative">
                                <select name="sort" onchange="this.form.submit()" class="block w-full rounded-lg border-gray-300 pl-3 pr-10 py-2 text-base focus:border-pink-500 focus:ring-pink-500 sm:text-sm text-gray-900">
                                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                    <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['added'])): ?>
                <div class="mt-4 px-4 sm:px-6 lg:px-8">
                    <div class="rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    Product added to cart successfully!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Product grid -->
            <div class="px-4 sm:px-6 lg:px-8 py-8 pb-24">
                <div class="grid grid-cols-2 gap-y-4 gap-x-2 sm:gap-y-6 sm:gap-x-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
                    <?php foreach ($products as $product): ?>
                        <div class="group relative bg-pink-50 rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-t-xl bg-white">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="block">
                                    <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'assets/images/default-product.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                                </a>
                            </div>
                            <div class="p-3 sm:p-4">
                                <div class="mb-2 sm:mb-3">
                                    <h3 class="text-xs sm:text-sm font-medium text-gray-900 line-clamp-2">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="hover:text-pink-600 transition-colors">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>
                                    <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm text-gray-500"><?php echo htmlspecialchars($product['brand']); ?></p>
                                </div>
                                <div class="flex items-center justify-between mb-2 sm:mb-3">
                                    <p class="text-xs sm:text-sm font-bold text-pink-600">$<?php echo number_format($product['price'], 2); ?></p>
                                    <?php if ($product['stock'] > 0): ?>
                                        <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">In Stock</span>
                                    <?php else: ?>
                                        <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-full">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-shopping-bag mr-1"></i>
                                        <?php 
                                        // Get number of sold items from order_items table
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

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&sort=<?php echo urlencode($sort); ?>" 
                                   class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&sort=<?php echo urlencode($sort); ?>" 
                                   class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium"><?php echo ($page - 1) * $per_page + 1; ?></span>
                                    to
                                    <span class="font-medium"><?php echo min($page * $per_page, $total); ?></span>
                                    of
                                    <span class="font-medium"><?php echo $total; ?></span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&sort=<?php echo urlencode($sort); ?>" 
                                           class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Previous</span>
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&sort=<?php echo urlencode($sort); ?>" 
                                           class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?php echo $i === $page ? 'bg-pink-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0'; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&sort=<?php echo urlencode($sort); ?>" 
                                           class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Next</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-gray-900 text-white relative bottom-0">
        <div class="w-full">
            <?php include 'includes/footer.php'; ?>
        </div>
    </footer>
</div>

<style>
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    main {
        flex: 1 0 auto;
    }
    footer {
        flex-shrink: 0;
    }
</style>

<script>
function updateSort(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', value);
    window.location.href = url.toString();
}

function clearFilters() {
    window.location.href = 'products.php';
}
</script>