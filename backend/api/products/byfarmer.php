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
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Token is required"]);
    exit;
}

$jwt = new JWT();
$decoded = $jwt->decodeJWT($token);
if (isset($decoded['error'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid token"]);
    exit;
}

if ($decoded['role'] !== 'farmer') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Only farmers can access their products"]);
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
        p.id, p.name, p.description, p.price, p.quantity, p.image_url, p.created_at,
        COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
        COUNT(r.id) AS review_count
    FROM products p
    LEFT JOIN reviews r ON r.product_id = p.id
    WHERE p.farmer_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
echo json_encode([
    "status" => "success",
    "data" => $products
]);
$stmt->close();
$conn->close();
?>
