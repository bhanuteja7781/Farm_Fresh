<?php
$host = getenv('DB_HOST') ?: 'db';
$db = getenv('DB_DATABASE') ?: 'farmfresh';
$user = getenv('DB_USERNAME') ?: 'farmfresh';
$pass = getenv('DB_PASSWORD') ?: 'farmfreshpass';

$conn = @mysqli_connect($host, $user, $pass, $db);
if ($conn) {
    echo "OK";
    mysqli_close($conn);
    exit(0);
}

echo mysqli_connect_error();
exit(1);
