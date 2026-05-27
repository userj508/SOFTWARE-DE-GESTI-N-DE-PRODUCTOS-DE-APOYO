<?php
// api/dynamic_attributes.php
include_once 'config/database.php';
include_once 'config/core.php';

$user = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($_GET['product_type_id'])) {
        sendJsonResponse(400, ['message' => 'Missing product_type_id']);
    }

    $product_type_id = $_GET['product_type_id'];

    $query = "SELECT id, name, type FROM dynamic_attributes WHERE product_type_id = :product_type_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':product_type_id', $product_type_id);

    $stmt->execute();
    $attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonResponse(200, $attributes);
}
?>