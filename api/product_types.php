<?php
// api/product_types.php
include_once 'config/database.php';
include_once 'config/core.php';

$user = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If not superadmin, only show entity's types
    $where = "";
    if ($user['role'] !== 'superadmin') {
        $where = "WHERE entity_id = :entity_id";
    }

    $query = "SELECT id, name FROM product_types $where";

    $stmt = $db->prepare($query);

    if ($user['role'] !== 'superadmin') {
        $stmt->bindParam(':entity_id', $user['entity_id']);
    }

    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonResponse(200, $types);
}
?>