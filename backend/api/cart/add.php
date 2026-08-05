<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Max-Age: 86400");
    http_response_code(204);
    exit;
}
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once(__DIR__ . '/../../middlewares/JWT.php');
require_once(__DIR__ . '/../../config/Database.php');

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$token = $data['token'] ?? '';
$product_id = $data['product_id'] ?? '';
$quantity = (int)($data['quantity'] ?? 1);

if (!$token || !$product_id) {
    echo json_encode(["status" => "error", "message" => "Token and product ID are required"]);
    exit;
}

$jwt = new JWT();
$decoded = $jwt->decodeJWT($token);
if (isset($decoded['error']) || $decoded['role'] !== 'customer') {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$email = $decoded['email'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$userRes = $stmt->get_result();
$user = $userRes->fetch_assoc();
$user_id = $user['id'];
$stmt->close();

// Check available stock in products table
$stmt = $conn->prepare("SELECT name, quantity FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$prodRes = $stmt->get_result();
if ($prodRes->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Product not found"]);
    exit;
}
$product = $prodRes->fetch_assoc();
$available_stock = (int)$product['quantity'];
$stmt->close();

// Check existing cart quantity
$stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$cartRes = $stmt->get_result();
$existing_cart_qty = 0;
if ($cartRes->num_rows > 0) {
    $cartRow = $cartRes->fetch_assoc();
    $existing_cart_qty = (int)$cartRow['quantity'];
}
$stmt->close();

$total_requested = $existing_cart_qty + $quantity;

if ($total_requested > $available_stock) {
    echo json_encode([
        "status" => "error",
        "message" => "Only {$available_stock} kg of '{$product['name']}' available in stock!"
    ]);
    exit;
}

if ($existing_cart_qty > 0) {
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
} else {
    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Item added to cart"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add to cart"]);
}
$stmt->close();
$conn->close();
?>
