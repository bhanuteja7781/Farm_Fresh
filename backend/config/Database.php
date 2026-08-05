<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db_name = getenv('DB_DATABASE') ?: 'farmfresh';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$conn = null;
$maxAttempts = 20;
$delaySeconds = 2;

for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    $conn = @new mysqli($host, $username, $password, $db_name);
    if ($conn && !$conn->connect_error) {
        break;
    }

    if ($attempt < $maxAttempts) {
        sleep($delaySeconds);
    }
}

if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn ? $conn->connect_error : 'unknown error'));
}
?>
