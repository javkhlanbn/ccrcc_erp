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

$entry = get_time_entry_by_id($conn, $entry_id);

if ($entry) {
    echo json_encode(['success' => true, 'entry' => $entry]);
} else {
    echo json_encode(['success' => false, 'message' => 'Цагийн бүртгэл олдсонгүй']);
}
?>
