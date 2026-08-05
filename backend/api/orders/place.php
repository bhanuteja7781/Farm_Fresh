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
$token          = $data['token'] ?? '';
$items          = $data['items'] ?? [];
$address        = $data['address'] ?? '';
$mobile         = $data['mobile'] ?? '';
$payment_method = $data['payment_method'] ?? 'cod';

if (!$token || empty($items) || !$address || !$mobile) {
    echo json_encode(["status" => "error", "message" => "Token, address, mobile, and items are required"]);
    exit;
}

$jwt = new JWT();
$decoded = $jwt->decodeJWT($token);
if (isset($decoded['error']) || $decoded['role'] !== 'customer') {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid or unauthorized token"]);
    exit;
}

$email = $decoded['email'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$stmt->close();

$total_price = 0;
// 1. Stock validation before placing order
foreach ($items as $item) {
    $product_id = $item['product_id'];
    $requested_qty = (int)$item['quantity'];

    $stmt = $conn->prepare("SELECT name, price, quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Product ID $product_id not found"]);
        exit;
    }
    $product = $res->fetch_assoc();
    $stmt->close();

    if ($product['quantity'] < $requested_qty) {
        echo json_encode([
            "status" => "error",
            "message" => "Only {$product['quantity']} kg of '{$product['name']}' available in stock!"
        ]);
        exit;
    }

    $total_price += $product['price'] * $requested_qty;
}

// 2. Insert Order
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, address, mobile, payment_method) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("idsss", $user_id, $total_price, $address, $mobile, $payment_method);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// 3. Insert Order Items & Deduct Inventory Stock
foreach ($items as $item) {
    $product_id = $item['product_id'];
    $quantity   = (int)$item['quantity'];

    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();
    $price = $product['price'];
    $stmt->close();

    // Insert order item
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    $stmt->execute();
    $stmt->close();

    // Deduct stock from products table
    $stmtStock = $conn->prepare("UPDATE products SET quantity = GREATEST(0, quantity - ?) WHERE id = ?");
    $stmtStock->bind_param("ii", $quantity, $product_id);
    $stmtStock->execute();
    $stmtStock->close();
}

// Clear cart
$stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    "status" => "success",
    "message" => "Order placed successfully!",
    "order_id" => $order_id,
    "total" => $total_price,
    "payment_method" => $payment_method
]);

$conn->close();
?>
