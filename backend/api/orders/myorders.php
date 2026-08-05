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
if (!$token) {
    echo json_encode(["status" => "error", "message" => "Token is required"]);
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
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();

$orders = [];
while ($order = $ordersResult->fetch_assoc()) {
    $order_id = $order['id'];
    $stmtItems = $conn->prepare("
        SELECT 
            order_items.id AS item_id,
            order_items.product_id,
            products.name,
            order_items.quantity,
            order_items.price,
            COALESCE(order_items.status, o.status, 'pending') AS item_status,
            (SELECT rating FROM reviews WHERE user_id = ? AND product_id = order_items.product_id ORDER BY id DESC LIMIT 1) AS user_rating
        FROM order_items
        INNER JOIN products ON products.id = order_items.product_id
        INNER JOIN orders o ON order_items.order_id = o.id
        WHERE order_items.order_id = ?
    ");
    $stmtItems->bind_param("ii", $user_id, $order_id);
    $stmtItems->execute();
    $itemsResult = $stmtItems->get_result();
    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }
    $stmtItems->close();

    $delivery_code = sprintf("%06d", ($order_id * 739211) % 900000 + 100000);

    $orders[] = [
        "id" => $order['id'],
        "total_price" => $order['total_price'],
        "status" => $order['status'],
        "payment_method" => $order['payment_method'] ?? 'cod',
        "delivery_code" => $delivery_code,
        "created_at" => $order['created_at'],
        "items" => $items
    ];
}

echo json_encode([
    "status" => "success",
    "orders" => $orders
]);

$conn->close();
?>
