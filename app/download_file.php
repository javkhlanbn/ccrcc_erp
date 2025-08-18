<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    http_response_code(403);
    exit('Access denied');
}

$filename = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($filename)) {
    http_response_code(400);
    exit('No file specified');
}

// Security check - prevent directory traversal
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
    http_response_code(400);
    exit('Invalid filename');
}

$file_path = '../uploads/chat_files/' . $filename;

if (!file_exists($file_path)) {
    http_response_code(404);
    exit('File not found');
}

// Get file info from database
include "../DB_connection.php";

try {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE file_name = ? AND (sender_id = ? OR receiver_id = ?)");
    $stmt->execute([$filename, $_SESSION['id'], $_SESSION['id']]);
    $message = $stmt->fetch();
    
    if (!$message) {
        http_response_code(403);
        exit('Access denied to this file');
    }
    
    $file_type = $message['file_type'];
    $original_filename = $message['original_filename'] ?? $filename;
    
    // Set appropriate headers
    header('Content-Type: ' . $file_type);
    header('Content-Length: ' . filesize($file_path));
    
    // For images, display inline; for files, force download
    if (strpos($file_type, 'image/') === 0) {
        header('Content-Disposition: inline; filename="' . $original_filename . '"');
    } else {
        header('Content-Disposition: attachment; filename="' . $original_filename . '"');
    }
    
    // Output file
    readfile($file_path);
    
} catch (Exception $e) {
    http_response_code(500);
    exit('Database error');
}
?>
