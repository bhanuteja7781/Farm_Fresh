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
$token         = $data['token'] ?? '';
$order_id      = $data['order_id'] ?? null;
$delivery_code = trim($data['delivery_code'] ?? '');

if (!$token || !$order_id || !$delivery_code) {
    echo json_encode(["status" => "error", "message" => "Order ID and 6-digit delivery code are required"]);
    exit;
}

$jwt = new JWT();
$decoded = $jwt->decodeJWT($token);
if (isset($decoded['error']) || $decoded['role'] !== 'farmer') {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// Calculate expected 6-digit delivery code deterministically
$expectedCode = sprintf("%06d", ($order_id * 739211) % 900000 + 100000);

if ($delivery_code !== $expectedCode) {
    echo json_encode(["status" => "error", "message" => "Incorrect 6-digit delivery code. Please ask customer for the code on their screen."]);
    exit;
}

// Complete order status for order and all items
$stmt = $conn->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("UPDATE order_items SET status = 'delivered' WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    "status" => "success",
    "message" => "Delivery code verified! Order #$order_id marked as Delivered."
]);

$conn->close();
?>
