<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

include "../DB_connection.php";
include "Model/Message.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['id'];
    $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if ($receiver_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid receiver']);
        exit();
    }
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
        exit();
    }
    
    if (strlen($message) > 1000) {
        echo json_encode(['success' => false, 'error' => 'Message too long']);
        exit();
    }
    
    try {
        // Check if messages table exists first
        $check_table = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($check_table->rowCount() == 0) {
            echo json_encode(['success' => false, 'error' => 'Messages table does not exist']);
            exit();
        }
        
        $result = send_message($conn, $sender_id, $receiver_id, $message);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Message sent']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
