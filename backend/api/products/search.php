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

require_once(__DIR__ . '/../../config/Database.php');

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$search = $data['search'] ?? '';
$min_price = $data['min_price'] ?? 0;
$max_price = $data['max_price'] ?? 100000;
$farmer_id = $data['farmer_id'] ?? null;
$lat = isset($data['lat']) && is_numeric($data['lat']) ? floatval($data['lat']) : null;
$lon = isset($data['lon']) && is_numeric($data['lon']) ? floatval($data['lon']) : null;

$hasGps = ($lat !== null && $lon !== null && $lat != 0 && $lon != 0);

if ($hasGps) {
    // Haversine formula calculation in kilometers
    $distanceSql = ", ROUND((6371 * acos(
        LEAST(1.0, GREATEST(-1.0, 
            cos(radians($lat)) * cos(radians(COALESCE(p.latitude, 31.25176))) * 
            cos(radians(COALESCE(p.longitude, 75.70421)) - radians($lon)) + 
            sin(radians($lat)) * sin(radians(COALESCE(p.latitude, 31.25176)))
        ))
    )), 1) AS distance_km";
} else {
    $distanceSql = ", NULL AS distance_km";
}

$baseQuery = "
    SELECT 
        p.id,
        p.name,
        p.description,
        p.price,
        p.quantity,
        p.image_url,
        COALESCE(p.latitude, 31.25176) AS latitude,
        COALESCE(p.longitude, 75.70421) AS longitude,
        COALESCE(p.delivery_radius_km, 20) AS delivery_radius_km,
        u.name AS farmer_name,
        ROUND(COALESCE(AVG(r.rating), 0), 1) AS avg_rating,
        COUNT(r.id) AS review_count
        {$distanceSql}
    FROM products p
    JOIN users u ON p.farmer_id = u.id
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE 
        (p.name LIKE ? OR p.description LIKE ?)
        AND p.price BETWEEN ? AND ?
        AND p.quantity > 0
";

$params = ["%$search%", "%$search%", $min_price, $max_price];
$types = "ssdd";

if ($farmer_id) {
    $baseQuery .= " AND p.farmer_id = ?";
    $params[] = $farmer_id;
    $types .= "i";
}

$baseQuery .= " GROUP BY p.id, p.name, p.description, p.price, p.quantity, p.image_url, p.latitude, p.longitude, p.delivery_radius_km, u.name";

$products = [];

if ($hasGps) {
    // 1. Try strict delivery radius filter first
    $strictQuery = $baseQuery . " HAVING (distance_km <= p.delivery_radius_km OR distance_km IS NULL) ORDER BY distance_km ASC";
    $stmt = $conn->prepare($strictQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

    // 2. Fallback: If no products within strict radius, return all products ordered by distance
    if (empty($products)) {
        $fallbackQuery = $baseQuery . " ORDER BY distance_km ASC";
        $stmt = $conn->prepare($fallbackQuery);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
    }
} else {
    $allQuery = $baseQuery . " ORDER BY p.id DESC";
    $stmt = $conn->prepare($allQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}

echo json_encode([
    "status" => "success",
    "user_location_active" => $hasGps,
    "data" => $products
]);

$conn->close();
?>
