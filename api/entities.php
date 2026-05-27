<?php
// api/entities.php
include_once 'config/database.php';
include_once 'config/core.php';

$user = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($user['role'] !== 'superadmin') {
        sendJsonResponse(403, ['message' => 'Forbidden']);
    }

    $query = "SELECT id, name FROM entities";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonResponse(200, $entities);
}
?>