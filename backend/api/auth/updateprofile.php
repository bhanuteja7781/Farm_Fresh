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
$token            = $data['token'] ?? '';
$name             = trim($data['name'] ?? '');
$mobile           = trim($data['mobile'] ?? '');
$current_password = $data['current_password'] ?? '';
$new_password     = $data['new_password'] ?? '';

if (!$token) {
    echo json_encode(["status" => "error", "message" => "Token is required"]);
    exit;
}

if (!$name) {
    echo json_encode(["status" => "error", "message" => "Name cannot be empty"]);
    exit;
}

$jwt = new JWT();
$decoded = $jwt->decodeJWT($token);
if (isset($decoded['error'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
    exit;
}

$email = $decoded['email'];
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$user = $res->fetch_assoc();
$user_id = $user['id'];
$current_hash = $user['password'];
$stmt->close();

// If user wants to change password
if ($new_password !== '') {
    if (!$current_password) {
        echo json_encode(["status" => "error", "message" => "Current password is required to set a new password"]);
        exit;
    }

    if (!password_verify($current_password, $current_hash)) {
        echo json_encode(["status" => "error", "message" => "Current password is incorrect"]);
        exit;
    }

    if (strlen($new_password) < 6) {
        echo json_encode(["status" => "error", "message" => "New password must be at least 6 characters long"]);
        exit;
    }

    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET name = ?, mobile = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $mobile, $new_hash, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET name = ?, mobile = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $mobile, $user_id);
}

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Profile updated successfully!"
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update profile"]);
}

$stmt->close();
$conn->close();
?>
