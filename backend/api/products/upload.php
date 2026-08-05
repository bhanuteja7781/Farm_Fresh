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
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST");

require_once(__DIR__ . '/../../middlewares/JWT.php');
require_once(__DIR__ . '/../../config/Database.php');

$token = $_POST['token'] ?? '';
if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Missing authentication token"]);
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
    echo json_encode(["status" => "error", "message" => "Only farmers can upload product images"]);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No image file uploaded or upload error"]);
    exit;
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed."]);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "File size exceeds 5MB limit."]);
    exit;
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFilename = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);

$uploadDir = __DIR__ . '/../../uploads/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}
@chmod($uploadDir, 0777);

$targetFilePath = $uploadDir . $newFilename;

if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
    echo json_encode([
        "status" => "success",
        "message" => "File uploaded successfully",
        "image_url" => "uploads/" . $newFilename
    ]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to save uploaded file."]);
}
?>
