<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

include "../DB_connection.php";
include "Model/Message.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['id'];
    $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
    $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if ($receiver_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid receiver']);
        exit();
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
        exit();
    }
    
    $file = $_FILES['file'];
    $original_filename = $file['name'];
    $file_size = $file['size'];
    $file_type = $file['type'];
    $tmp_name = $file['tmp_name'];
    
    // File size limit (10MB)
    $max_size = 10 * 1024 * 1024;
    if ($file_size > $max_size) {
        echo json_encode(['success' => false, 'error' => 'File too large (max 10MB)']);
        exit();
    }
    
    // Allowed file types
    $allowed_image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_file_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
    $all_allowed_types = array_merge($allowed_image_types, $allowed_file_types);
    
    if (!in_array($file_type, $all_allowed_types)) {
        echo json_encode(['success' => false, 'error' => 'File type not allowed']);
        exit();
    }
    
    // Generate unique filename
    $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
    $upload_path = '../uploads/chat_files/' . $unique_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($tmp_name, $upload_path)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save file']);
        exit();
    }
    
    try {
        // Determine message type
        $message_type = in_array($file_type, $allowed_image_types) ? 'image' : 'file';
        
        // Prepare message text
        if (empty($message_text)) {
            $message_text = $message_type === 'image' ? '[Зураг илгээсэн]' : '[Файл илгээсэн: ' . $original_filename . ']';
        }
        
        // Save to database
        $result = send_file_message($conn, $sender_id, $receiver_id, $message_text, $unique_filename, $file_type, $file_size, $original_filename, $message_type);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'File sent successfully',
                'file_info' => [
                    'filename' => $unique_filename,
                    'original_name' => $original_filename,
                    'type' => $message_type,
                    'size' => $file_size
                ]
            ]);
        } else {
            // Delete uploaded file if database insert failed
            if (file_exists($upload_path)) {
                unlink($upload_path);
            }
            echo json_encode(['success' => false, 'error' => 'Failed to save message']);
        }
    } catch (Exception $e) {
        // Delete uploaded file if error occurred
        if (file_exists($upload_path)) {
            unlink($upload_path);
        }
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
