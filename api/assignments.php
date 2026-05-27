<?php
// api/assignments.php
include_once 'config/database.php';
include_once 'config/core.php';

$user = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $where = "";
    if ($user['role'] !== 'superadmin') {
        $where = "WHERE p.entity_id = :entity_id";
    }

    $query = "SELECT a.id, a.location, a.assigned_at, a.returned_at, a.notes,
                     pat.name as patient_name, pr.internal_code as product_code, pr.brand, pr.model,
                     u.name as assigned_by_name
              FROM assignments a
              JOIN products pr ON a.product_id = pr.id
              LEFT JOIN patients pat ON a.patient_id = pat.id
              JOIN users u ON a.assigned_by = u.id
              JOIN entities e ON pr.entity_id = e.id
              $where
              ORDER BY a.assigned_at DESC";

    $stmt = $db->prepare($query);

    if ($user['role'] !== 'superadmin') {
        $stmt->bindParam(':entity_id', $user['entity_id']);
    }

    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonResponse(200, $assignments);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user['role'] === 'viewer') {
        sendJsonResponse(403, ['message' => 'Forbidden']);
    }

    $data = json_decode(file_get_contents("php://input"));

    if(empty($data->product_id) || empty($data->location)) {
         sendJsonResponse(400, ['message' => 'Missing data']);
    }

    $db->beginTransaction();

    try {
        // Insert assignment
        $query = "INSERT INTO assignments (product_id, patient_id, assigned_by, location, notes)
                  VALUES (:product_id, :patient_id, :assigned_by, :location, :notes)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':product_id', $data->product_id);
        $patient_id = !empty($data->patient_id) ? $data->patient_id : null;
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':assigned_by', $user['user_id']);
        $stmt->bindParam(':location', $data->location);
        $notes = !empty($data->notes) ? $data->notes : null;
        $stmt->bindParam(':notes', $notes);

        $stmt->execute();

        // Update product status
        $status = $patient_id ? 'assigned' : 'in_stock';
        $updateQuery = "UPDATE products SET status = :status WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':id', $data->product_id);
        $updateStmt->execute();

        $db->commit();
        sendJsonResponse(201, ['message' => 'Assignment created']);
    } catch (Exception $e) {
        $db->rollBack();
        sendJsonResponse(500, ['message' => 'Failed to create assignment', 'error' => $e->getMessage()]);
    }
}
?>