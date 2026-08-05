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
$token    = $data['token'] ?? '';
$item_id  = $data['item_id'] ?? null;
$order_id = $data['order_id'] ?? null;
$status   = $data['status'] ?? '';

if (!$token || (!$item_id && !$order_id) || !$status) {
    echo json_encode(["status" => "error", "message" => "Token, ID, and status are required"]);
    exit;
}

$allowedStatuses = ['pending', 'confirmed', 'out_for_delivery', 'delivered', 'cancelled'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(["status" => "error", "message" => "Invalid status requested"]);
    exit;
}

$jwt = new JWT();
$decoded = $jwt->decodeJWT($token);
if (isset($decoded['error'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$email = $decoded['email'];
$role  = $decoded['role'];

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$user_id = $user['id'];
$stmt->close();

$targetOrderId = $order_id;

if ($role === 'farmer') {
    if (!in_array($status, ['confirmed', 'out_for_delivery'])) {
        echo json_encode(["status" => "error", "message" => "Farmers can set status to 'confirmed' or 'out_for_delivery'."]);
        exit;
    }

    if ($item_id) {
        $stmt = $conn->prepare("UPDATE order_items oi JOIN products p ON p.id = oi.product_id SET oi.status = ? WHERE oi.id = ? AND p.farmer_id = ?");
        $stmt->bind_param("sii", $status, $item_id, $user_id);
        
        // Find order_id for sync
        $findStmt = $conn->prepare("SELECT order_id FROM order_items WHERE id = ?");
        $findStmt->bind_param("i", $item_id);
        $findStmt->execute();
        $findRes = $findStmt->get_result();
        if ($findRow = $findRes->fetch_assoc()) {
            $targetOrderId = $findRow['order_id'];
        }
        $findStmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE order_items oi JOIN products p ON p.id = oi.product_id SET oi.status = ? WHERE oi.order_id = ? AND p.farmer_id = ?");
        $stmt->bind_param("sii", $status, $order_id, $user_id);
    }
} else if ($role === 'customer') {
    if ($status !== 'delivered') {
        echo json_encode(["status" => "error", "message" => "Customers can only confirm delivery status ('delivered')"]);
        exit;
    }

    if ($item_id) {
        $stmt = $conn->prepare("UPDATE order_items oi JOIN orders o ON o.id = oi.order_id SET oi.status = 'delivered' WHERE oi.id = ? AND o.user_id = ?");
        $stmt->bind_param("ii", $item_id, $user_id);
        
        $findStmt = $conn->prepare("SELECT order_id FROM order_items WHERE id = ?");
        $findStmt->bind_param("i", $item_id);
        $findStmt->execute();
        $findRes = $findStmt->get_result();
        if ($findRow = $findRes->fetch_assoc()) {
            $targetOrderId = $findRow['order_id'];
        }
        $findStmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE order_items oi JOIN orders o ON o.id = oi.order_id SET oi.status = 'delivered' WHERE oi.order_id = ? AND o.user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
    }
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized role"]);
    exit;
}

if ($stmt->execute()) {
    // Sync parent orders.status with most progressive item status
    if ($targetOrderId) {
        $syncStmt = $conn->prepare("
            UPDATE orders o 
            SET o.status = COALESCE(
                (SELECT oi.status FROM order_items oi WHERE oi.order_id = ? ORDER BY FIELD(oi.status, 'delivered', 'out_for_delivery', 'confirmed', 'pending', 'cancelled') ASC LIMIT 1),
                'pending'
            )
            WHERE o.id = ?
        ");
        $syncStmt->bind_param("ii", $targetOrderId, $targetOrderId);
        $syncStmt->execute();
        $syncStmt->close();
    }

    echo json_encode(["status" => "success", "message" => "Status updated to '$status'"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update status"]);
}

$stmt->close();
$conn->close();
?>
