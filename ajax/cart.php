<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$conn = getDBConnection();
$user_id = getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_to_cart':
            $product_id = intval($_POST['product_id']);
            $quantity = max(1, intval($_POST['quantity']));
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Check if item already exists in cart
                $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Update quantity if item exists
                    $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
                } else {
                    // Insert new item if it doesn't exist
                    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                }
                
                $success = $stmt->execute();
                
                if ($success) {
                    // Get updated cart count
                    $stmt = $conn->prepare("
                        SELECT SUM(quantity) as count
                        FROM cart_items
                        WHERE user_id = ?
                    ");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $cart_count = $result->fetch_assoc()['count'] ?? 0;
                    
                    $conn->commit();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product added to cart successfully!',
                        'cart_count' => $cart_count
                    ]);
                } else {
                    throw new Exception("Failed to add item to cart");
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
        case 'update_quantity':
            $product_id = intval($_POST['product_id']);
            $quantity = max(1, intval($_POST['quantity']));
            
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $success = $stmt->execute();
            
            if ($success) {
                // Get updated cart total
                $stmt = $conn->prepare("
                    SELECT SUM(p.price * c.quantity) as total
                    FROM cart_items c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.user_id = ?
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $total = $result->fetch_assoc()['total'] ?? 0;
                
                echo json_encode([
                    'success' => true,
                    'total' => number_format($total, 2)
                ]);
            } else {
                echo json_encode(['error' => 'Failed to update quantity']);
            }
            break;
            
        case 'remove_item':
            $product_id = intval($_POST['product_id']);
            
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $success = $stmt->execute();
            
            if ($success) {
                // Get updated cart total
                $stmt = $conn->prepare("
                    SELECT SUM(p.price * c.quantity) as total
                    FROM cart_items c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.user_id = ?
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $total = $result->fetch_assoc()['total'] ?? 0;
                
                echo json_encode([
                    'success' => true,
                    'total' => number_format($total, 2)
                ]);
            } else {
                echo json_encode(['error' => 'Failed to remove item']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
    $stmt->close();
    $conn->close();
    exit;
} 