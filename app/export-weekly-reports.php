<?php
// app/export-weekly-reports.php
// Export weekly reports to Excel or Word
require_once '../DB_connection.php';
require_once 'Model/WeeklyReport.php';

// Get export type (excel or word)
$type = isset($_GET['type']) ? $_GET['type'] : 'excel';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get filtered reports
$reports = get_all_weekly_reports($conn, $status);

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="weekly_reports.xls"');
    // Add BOM for UTF-8
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    echo "<table border='1'>";
    echo "<tr><th>#</th><th>Ажилчин</th><th>Долоо хоног</th><th>Статус</th><th>Илгээсэн огноо</th><th>Хянасан</th></tr>";
    foreach ($reports as $i => $r) {
        echo "<tr>";
        echo "<td>" . ($i+1) . "</td>";
        echo "<td>" . htmlspecialchars($r['employee_name']) . "<br><small>@" . htmlspecialchars($r['employee_username']) . "</small></td>";
        echo "<td>" . $r['week_range'] . "</td>";
        echo "<td>" . $r['status'] . "</td>";
        echo "<td>" . ($r['submitted_at'] ? date('Y-m-d H:i', strtotime($r['submitted_at'])) : '-') . "</td>";
        echo "<td>" . ($r['reviewed_by_name'] ?: '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
} elseif ($type === 'word') {
    header('Content-Type: application/vnd.ms-word; charset=UTF-8');
    header('Content-Disposition: attachment; filename="weekly_reports.doc"');
    // Add BOM for UTF-8
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    echo "<table border='1'>";
    echo "<tr><th>#</th><th>Ажилчин</th><th>Долоо хоног</th><th>Статус</th><th>Илгээсэн огноо</th><th>Хянасан</th></tr>";
    foreach ($reports as $i => $r) {
        echo "<tr>";
        echo "<td>" . ($i+1) . "</td>";
        echo "<td>" . htmlspecialchars($r['employee_name']) . "<br><small>@" . htmlspecialchars($r['employee_username']) . "</small></td>";
        echo "<td>" . $r['week_range'] . "</td>";
        echo "<td>" . $r['status'] . "</td>";
        echo "<td>" . ($r['submitted_at'] ? date('Y-m-d H:i', strtotime($r['submitted_at'])) : '-') . "</td>";
        echo "<td>" . ($r['reviewed_by_name'] ?: '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
} else {
    echo "Invalid export type.";
    exit;
}
