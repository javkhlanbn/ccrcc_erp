<?php
session_start();
// Монголын цагийн бүс тохируулах
date_default_timezone_set('Asia/Ulaanbaatar');

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php?error=Анх удаа нэвтэрч байна");
    exit();
}

include "../DB_connection.php";
include "Model/WeeklyReport.php";

$user_id = $_SESSION['id'];
$action = $_POST['action'] ?? '';
$week_start_date = $_POST['week_start_date'] ?? '';
$week_end_date = $_POST['week_end_date'] ?? '';

if (!$week_start_date || !$week_end_date) {
    header("Location: ../weekly-report.php?error=Долоо хоногийн огноо буруу байна");
    exit();
}

// 7 хоногийн өгөгдөл цуглуулах
$report_data = [
    'monday' => $_POST['monday'] ?? '',
    'tuesday' => $_POST['tuesday'] ?? '',
    'wednesday' => $_POST['wednesday'] ?? '',
    'thursday' => $_POST['thursday'] ?? '',
    'friday' => $_POST['friday'] ?? '',
    'saturday' => $_POST['saturday'] ?? '',
    'sunday' => $_POST['sunday'] ?? ''
];

$summary = $_POST['summary'] ?? '';

// Хадгалах
$save_result = save_weekly_report($conn, $user_id, $week_start_date, $week_end_date, $report_data, $summary);

if (!$save_result) {
    header("Location: ../weekly-report.php?error=Тайлан хадгалахад алдаа гарлаа");
    exit();
}

if ($action == 'submit') {
    // Админд илгээх
    $submit_result = submit_weekly_report($conn, $user_id, $week_start_date);
    
    if ($submit_result) {
        header("Location: ../weekly-report.php?success=Тайлан амжилттай админд илгээгдлээ!");
    } else {
        header("Location: ../weekly-report.php?error=Тайлан илгээхэд алдаа гарлаа");
    }
} else {
    // Зөвхөн хадгалах
    header("Location: ../weekly-report.php?success=Тайлан амжилттай хадгалагдлаа!");
}
exit();
?>
