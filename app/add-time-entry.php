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

$user_id = $_POST['user_id'] ?? '';
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$notes = $_POST['notes'] ?? '';

if (!$user_id || !$date || !$start_time || !$end_time) {
    echo json_encode(['success' => false, 'message' => 'Бүх шаардлагатай талбаруудыг бөглөнө үү']);
    exit();
}

// Check if entry already exists for this date and user
$sql = "SELECT id FROM time_entries WHERE user_id = ? AND date = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id, $date]);
$existing_entry = $stmt->fetch();

if ($existing_entry) {
    echo json_encode(['success' => false, 'message' => 'Энэ өдөр цагийн бүртгэл аль хэдийнэ байгаа']);
    exit();
}

// Calculate total hours and overtime
$start_datetime = new DateTime($date . ' ' . $start_time);
$end_datetime = new DateTime($date . ' ' . $end_time);
$interval = $start_datetime->diff($end_datetime);
$total_hours = $interval->h + ($interval->i / 60);

// Standard working hours (9 hours)
$standard_hours = 9;
$overtime_hours = max(0, $total_hours - $standard_hours);

// Insert new time entry
$sql = "INSERT INTO time_entries (user_id, date, start_time, end_time, total_hours, overtime_hours, status, notes, is_manual, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'completed', ?, TRUE, NOW())";

$stmt = $conn->prepare($sql);
$result = $stmt->execute([
    $user_id,
    $date,
    $start_time,
    $end_time,
    $total_hours,
    $overtime_hours,
    $notes
]);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Цагийн бүртгэл амжилттай нэмэгдлээ']);
} else {
    echo json_encode(['success' => false, 'message' => 'Алдаа гарлаа. Дахин оролдоно уу']);
}
?>
