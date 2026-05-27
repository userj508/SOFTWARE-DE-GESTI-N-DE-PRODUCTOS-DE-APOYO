<?php
// api/login.php
include_once 'config/database.php';
include_once 'config/core.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->email) && !empty($data->password)) {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, entity_id, role, password_hash, name FROM users WHERE email = :email LIMIT 0,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $data->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Note: In production you should use password_hash and password_verify
            // For MVP simplicity and ease of testing, we will check raw string or hashed
            if (password_verify($data->password, $row['password_hash']) || $data->password === $row['password_hash']) {
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['entity_id'] = $row['entity_id'];
                $_SESSION['role'] = $row['role'];

                sendJsonResponse(200, [
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'role' => $row['role'],
                        'entity_id' => $row['entity_id']
                    ]
                ]);
            }
        }
    }
    sendJsonResponse(401, ['message' => 'Invalid credentials']);
}

// Handle GET for checking session / me
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = authenticate();

    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT id, name, role, entity_id FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user['user_id']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    sendJsonResponse(200, ['user' => $row]);
}

// Logout
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    session_start();
    session_destroy();
    sendJsonResponse(200, ['message' => 'Logged out']);
}
?>