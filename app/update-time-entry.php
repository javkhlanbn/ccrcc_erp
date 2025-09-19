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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Зөвхөн POST хүсэлт зөвшөөрөгдөнө']);
    exit();
}

$entry_id = $_POST['entry_id'] ?? '';
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$status = $_POST['status'] ?? '';
$notes = $_POST['notes'] ?? '';

if (!$entry_id || !$date || !$start_time || !$end_time) {
    echo json_encode(['success' => false, 'message' => 'Бүх талбарыг бөглөнө үү']);
    exit();
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Огнооны формат буруу байна']);
    exit();
}

// Validate time formats
if (!preg_match('/^\d{2}:\d{2}(:\d{2})?(?:\s?(?:AM|PM))?$/i', $start_time)
    || !preg_match('/^\d{2}:\d{2}(:\d{2})?(?:\s?(?:AM|PM))?$/i', $end_time)) {
    echo json_encode(['success' => false, 'message' => 'Цагийн формат буруу байна']);
    exit();
}


// Check if start time is before end time
if (strtotime($start_time) >= strtotime($end_time)) {
    echo json_encode(['success' => false, 'message' => 'Эхлэх цаг дуусах цагаас өмнө байх ёстой']);
    exit();
}

// Check if entry exists and belongs to admin's permission (no restriction for admin)
$entry = get_time_entry_by_id($conn, $entry_id);
if (!$entry) {
    echo json_encode(['success' => false, 'message' => 'Цагийн бүртгэл олдсонгүй']);
    exit();
}

// Update the time entry manually
$start_datetime = $date . ' ' . $start_time;
$end_datetime = $date . ' ' . $end_time;

$result = update_time_entry_manual($conn, $entry_id, $start_datetime, $end_datetime, $notes, $_SESSION['id']);

if ($result) {
    // Update status separately
    $update_status_sql = "UPDATE time_entries SET status = ? WHERE id = ?";
    $update_status_stmt = $conn->prepare($update_status_sql);
    $update_status_stmt->execute([$status, $entry_id]);

    echo json_encode(['success' => true, 'message' => 'Цагийн бүртгэл амжилттай шинэчлэгдлээ']);
} else {
    echo json_encode(['success' => false, 'message' => 'Алдаа гарлаа. Дахин оролдоно уу']);
}
?>
