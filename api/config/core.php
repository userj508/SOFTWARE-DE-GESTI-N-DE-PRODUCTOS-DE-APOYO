<?php
// api/config/core.php

// Show error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set default time zone
date_default_timezone_set('Europe/Madrid');

// Helper function to send JSON response
function sendJsonResponse($status, $data) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Very basic authentication helper (For demo purposes, normally you'd use JWT)
function authenticate() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(401, ['message' => 'Unauthorized']);
    }
    return [
        'user_id' => $_SESSION['user_id'],
        'entity_id' => $_SESSION['entity_id'],
        'role' => $_SESSION['role']
    ];
}
?>