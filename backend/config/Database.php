<?php
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
$host = getenv('DB_HOST') ?: 'farmfresh-db';
$db_name = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: 'farmfresh');
$username = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: 'root');
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root');

$conn = null;

// Try Docker / Configured Database Host
$conn = @new mysqli($host, $username, $password, $db_name);

// Fallback to local 127.0.0.1 / XAMPP / WAMP if Docker host fails
if (!$conn || $conn->connect_error) {
    $conn = @new mysqli('127.0.0.1', 'root', '', $db_name);
}

// Second fallback to localhost
if (!$conn || $conn->connect_error) {
    $conn = @new mysqli('localhost', 'root', '', $db_name);
}

if (!$conn || $conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . ($conn ? $conn->connect_error : 'Unable to connect to database')]));
}
?>
