<?php
session_start();
date_default_timezone_set('Asia/Ulaanbaatar');

if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Зөвхөн админ хандах боломжтой']);
    exit();
}

include "../DB_connection.php";
include "Model/TimeTracking.php";

header('Content-Type: application/json');

$entry_id = $_GET['id'] ?? '';

if (!$entry_id) {
    echo json_encode(['success' => false, 'message' => 'Entry ID шаардлагатай']);
    exit();
}

$result = delete_time_entry($conn, $entry_id);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Цагийн бүртгэл амжилттай устгагдлаа']);
} else {
    echo json_encode(['success' => false, 'message' => 'Алдаа гарлаа. Дахин оролдоно уу']);
}
?>
