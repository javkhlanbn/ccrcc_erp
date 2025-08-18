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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['id'];
    $receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;
    
    if ($receiver_id <= 0) {
        echo json_encode([]);
        exit();
    }
    
    try {
        // Check if messages table exists first
        $check_table = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($check_table->rowCount() == 0) {
            echo json_encode(['error' => 'Messages table does not exist']);
            exit();
        }
        
        // Get messages between current user and selected user
        $messages = get_messages($conn, $user_id, $receiver_id);
        
        // Mark messages from the other user as read
        mark_messages_as_read($conn, $receiver_id, $user_id);
        
        // Format messages for frontend
        $formatted_messages = [];
        foreach ($messages as $message) {
            $formatted_message = [
                'id' => $message['id'],
                'sender_id' => $message['sender_id'],
                'receiver_id' => $message['receiver_id'],
                'message' => htmlspecialchars($message['message']),
                'sender_name' => $message['sender_name'],
                'created_at' => $message['created_at'],
                'is_read' => $message['is_read'],
                'message_type' => isset($message['message_type']) ? $message['message_type'] : 'text'
            ];
            
            // Add file information if it's a file message
            if (isset($message['file_name']) && !empty($message['file_name'])) {
                $formatted_message['file_name'] = $message['file_name'];
                $formatted_message['file_type'] = $message['file_type'];
                $formatted_message['file_size'] = $message['file_size'];
                $formatted_message['original_filename'] = $message['original_filename'];
            }
            
            $formatted_messages[] = $formatted_message;
        }
        
        echo json_encode($formatted_messages);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
