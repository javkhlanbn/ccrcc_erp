<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    echo json_encode(['count' => 0]);
    exit();
}

include "../DB_connection.php";
include "Model/Message.php";

try {
    $user_id = $_SESSION['id'];
    $unread_count = get_unread_message_count($conn, $user_id);
    echo json_encode(['count' => $unread_count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
}
?>
