<?php
// api/incidents.php
include_once 'config/database.php';
include_once 'config/core.php';

$user = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Show incidents for products belonging to the user's entity
    $where = "";
    if ($user['role'] !== 'superadmin') {
        $where = "WHERE p.entity_id = :entity_id";
    }

    $query = "SELECT i.id, i.title, i.description, i.status, i.created_at, p.internal_code, p.brand, p.model, u.name as reported_by_name
              FROM incidents i
              JOIN products p ON i.product_id = p.id
              JOIN users u ON i.reported_by = u.id
              $where";

    $stmt = $db->prepare($query);

    if ($user['role'] !== 'superadmin') {
        $stmt->bindParam(':entity_id', $user['entity_id']);
    }

    $stmt->execute();
    $incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonResponse(200, $incidents);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user['role'] === 'viewer') {
        sendJsonResponse(403, ['message' => 'Forbidden']);
    }

    $data = json_decode(file_get_contents("php://input"));

    if(empty($data->product_id) || empty($data->title) || empty($data->description)) {
         sendJsonResponse(400, ['message' => 'Missing data']);
    }

    $query = "INSERT INTO incidents (product_id, reported_by, title, description, status)
              VALUES (:product_id, :reported_by, :title, :description, 'open')";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':product_id', $data->product_id);
    $stmt->bindParam(':reported_by', $user['user_id']);
    $stmt->bindParam(':title', $data->title);
    $stmt->bindParam(':description', $data->description);

    if ($stmt->execute()) {
        sendJsonResponse(201, ['message' => 'Incident created', 'id' => $db->lastInsertId()]);
    } else {
        sendJsonResponse(500, ['message' => 'Failed to create incident']);
    }
}
?>