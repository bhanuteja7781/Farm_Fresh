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
if (isset($decoded['error']) || $decoded['role'] !== 'farmer') {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$email = $decoded['email'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();
$farmer_id = $farmer['id'];
$stmt->close();

$query = "
    SELECT 
        orders.id AS order_id,
        COALESCE(orders.status, 'pending') AS order_status,
        orders.payment_method,
        orders.created_at,
        orders.address,
        orders.mobile,
        users.name AS customer_name,
        products.name AS product_name,
        order_items.id AS item_id,
        order_items.product_id,
        order_items.quantity,
        order_items.price,
        COALESCE(order_items.status, orders.status, 'pending') AS item_status
    FROM order_items
    INNER JOIN orders ON order_items.order_id = orders.id
    INNER JOIN users ON orders.user_id = users.id
    INNER JOIN products ON order_items.product_id = products.id
    WHERE products.farmer_id = ?
    ORDER BY orders.created_at DESC, order_items.id ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];
    $itemStatus = $row['item_status'];

    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            "order_id" => $order_id,
            "status" => $itemStatus, // Start with first item status
            "payment_method" => $row['payment_method'] ?? 'cod',
            "created_at" => $row['created_at'],
            "customer_name" => $row['customer_name'],
            "address" => $row['address'],
            "mobile" => $row['mobile'],
            "items" => []
        ];
    } else {
        // Upgrade overall order status if any item is further along
        $statusOrder = ['delivered' => 4, 'out_for_delivery' => 3, 'confirmed' => 2, 'pending' => 1];
        if (($statusOrder[$itemStatus] ?? 0) > ($statusOrder[$orders[$order_id]["status"]] ?? 0)) {
            $orders[$order_id]["status"] = $itemStatus;
        }
    }

    $orders[$order_id]["items"][] = [
        "item_id" => $row['item_id'],
        "product_id" => $row['product_id'],
        "product_name" => $row['product_name'],
        "quantity" => $row['quantity'],
        "price" => $row['price'],
        "item_status" => $itemStatus
    ];
}

echo json_encode([
    "status" => "success",
    "orders" => array_values($orders)
]);

$stmt->close();
$conn->close();
?>
