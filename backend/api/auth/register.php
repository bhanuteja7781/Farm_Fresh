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

require __DIR__ . '/../../middlewares/JWT.php';
require __DIR__ . '/../../config/Database.php';

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role = $data['role'] ?? 'customer';

if (!$name || !$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Please fill in all required fields"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address"]);
    exit;
}

// Check if email already exists
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email is already registered. Please login or use a different email."]);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $jwtInstance = new JWT();
        $payload = [
            "email" => $email,
            "role" => $role,
        ];
        $token = $jwtInstance->createJWT($payload);
        echo json_encode([
            "status" => "success",
            "message" => "Account registered successfully!",
            "token" => $token,
            "user" => [
                "id" => $user_id,
                "name" => $name,
                "email" => $email,
                "role" => $role
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed. Please try again."]);
    }
    $stmt->close();
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo json_encode(["status" => "error", "message" => "Email is already registered. Please login or use a different email."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed. Please try again."]);
    }
}

$conn->close();
?>
