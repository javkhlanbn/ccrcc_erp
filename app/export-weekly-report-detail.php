<?php
// app/export-weekly-report-detail.php
// Export single weekly report detail to Excel or Word
require_once '../DB_connection.php';
require_once 'Model/WeeklyReport.php';

$report_id = $_GET['id'] ?? '';
$type = $_GET['type'] ?? 'excel';

if (!$report_id) {
    echo "Тайлан сонгогдоогүй";
    exit;
}

$report = get_weekly_report_by_id($conn, $report_id);
if (!$report) {
    echo "Тайлан олдсонгүй";
    exit;
}
// week_range field fallback
if (!isset($report['week_range'])) {
    $start = isset($report['week_start_date']) ? $report['week_start_date'] : '';
    $end = isset($report['week_end_date']) ? $report['week_end_date'] : '';
    $report['week_range'] = ($start && $end) ? (date('Y-m-d', strtotime($start)) . ' - ' . date('Y-m-d', strtotime($end))) : '';
}

$days = [
    'monday_work' => 'Даваа гариг',
    'tuesday_work' => 'Мягмар гариг',
    'wednesday_work' => 'Лхагва гариг',
    'thursday_work' => 'Пүрэв гариг',
    'friday_work' => 'Баасан гариг',
    'saturday_work' => 'Бямба гариг',
    'sunday_work' => 'Ням гариг'
];

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="weekly_report_detail_'. $report_id .'.xls"');
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    echo "<table border='1'>";
    echo "<tr><th colspan='2'>Ажилчин</th></tr>";
    echo "<tr><td>Нэр</td><td>" . htmlspecialchars($report['employee_name']) . " (@" . htmlspecialchars($report['employee_username']) . ")</td></tr>";
    echo "<tr><td>Долоо хоног</td><td>" . $report['week_range'] . "</td></tr>";
    echo "<tr><td>Статус</td><td>" . $report['status'] . "</td></tr>";
    echo "<tr><td>Илгээсэн огноо</td><td>" . ($report['submitted_at'] ? date('Y-m-d H:i', strtotime($report['submitted_at'])) : '-') . "</td></tr>";
    echo "<tr><td>Хянасан</td><td>" . ($report['reviewed_by_name'] ?: '-') . "</td></tr>";
    foreach ($days as $field => $label) {
        echo "<tr><td>" . $label . "</td><td>" . ($report[$field] ? htmlspecialchars($report[$field]) : '-') . "</td></tr>";
    }
    echo "<tr><td>Хураангуй</td><td>" . ($report['summary'] ? htmlspecialchars($report['summary']) : '-') . "</td></tr>";
    echo "<tr><td>Админы санал хүсэлт</td><td>" . ($report['admin_feedback'] ? htmlspecialchars($report['admin_feedback']) : '-') . "</td></tr>";
    echo "</table>";
    exit;
} elseif ($type === 'word') {
    header('Content-Type: application/vnd.ms-word; charset=UTF-8');
    header('Content-Disposition: attachment; filename="weekly_report_detail_'. $report_id .'.doc"');
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    echo "<table border='1'>";
    echo "<tr><th colspan='2'>Ажилчин</th></tr>";
    echo "<tr><td>Нэр</td><td>" . htmlspecialchars($report['employee_name']) . " (@" . htmlspecialchars($report['employee_username']) . ")</td></tr>";
    echo "<tr><td>Долоо хоног</td><td>" . $report['week_range'] . "</td></tr>";
    echo "<tr><td>Статус</td><td>" . $report['status'] . "</td></tr>";
    echo "<tr><td>Илгээсэн огноо</td><td>" . ($report['submitted_at'] ? date('Y-m-d H:i', strtotime($report['submitted_at'])) : '-') . "</td></tr>";
    echo "<tr><td>Хянасан</td><td>" . ($report['reviewed_by_name'] ?: '-') . "</td></tr>";
    foreach ($days as $field => $label) {
        echo "<tr><td>" . $label . "</td><td>" . ($report[$field] ? htmlspecialchars($report[$field]) : '-') . "</td></tr>";
    }
    echo "<tr><td>Хураангуй</td><td>" . ($report['summary'] ? htmlspecialchars($report['summary']) : '-') . "</td></tr>";
    echo "<tr><td>Админы санал хүсэлт</td><td>" . ($report['admin_feedback'] ? htmlspecialchars($report['admin_feedback']) : '-') . "</td></tr>";
    echo "</table>";
    exit;
} else {
    echo "Invalid export type.";
    exit;
}
