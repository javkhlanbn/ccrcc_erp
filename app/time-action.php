<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php?error=Анх удаа нэвтэрч байна");
    exit();
}

include "../DB_connection.php";
include "Model/TimeTracking.php";

$user_id = $_SESSION['id'];
$action = $_POST['action'] ?? '';

if ($action == 'start') {
    $result = start_work_time($conn, $user_id);
    
    if ($result['success']) {
        header("Location: ../time-tracking.php?success=" . urlencode($result['message']));
    } else {
        header("Location: ../time-tracking.php?error=" . urlencode($result['message']));
    }
    
} elseif ($action == 'end') {
    $result = end_work_time($conn, $user_id);
    
    if ($result['success']) {
        $message = $result['message'] . " (Нийт: " . $result['total_hours'] . " цаг";
        if ($result['overtime'] > 0) {
            $message .= ", Илүү: " . $result['overtime'] . " цаг";
        }
        $message .= ")";
        
        header("Location: ../time-tracking.php?success=" . urlencode($message));
    } else {
        header("Location: ../time-tracking.php?error=" . urlencode($result['message']));
    }
    
} else {
    header("Location: ../time-tracking.php?error=Буруу хүсэлт");
}

exit();
?>
