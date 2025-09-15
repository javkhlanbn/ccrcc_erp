<?php
// app/export-weekly-reports.php
// Export weekly reports to Excel or Word
require_once '../DB_connection.php';
require_once 'Model/WeeklyReport.php';

// Get export type (excel or word)
$type = isset($_GET['type']) ? $_GET['type'] : 'excel';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$week = isset($_GET['week']) ? $_GET['week'] : '';

// Get filtered reports
$reports = get_all_weekly_reports($conn, $status, $week);

// Group reports by week range
$grouped_reports = [];
foreach ($reports as $report) {
    // Normalize week_range to consistent format for filtering
    $week_range = $report['week_range'];
    if (!empty($week) && strpos($week_range, $week) === false) {
        // Skip reports not matching the exact week filter string
        continue;
    }
    if (!isset($grouped_reports[$week_range])) {
        $grouped_reports[$week_range] = [];
    }
    $grouped_reports[$week_range][] = $report;
}

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="weekly_reports.xls"');
    // Add BOM for UTF-8
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    echo "<table border='1'>";
    echo "<tr><th>Долоо хоног</th><th>Ажилчин</th><th>Статус</th><th>Илгээсэн огноо</th><th>Хянасан</th><th>Даваа гариг</th><th>Мягмар гариг</th><th>Лхагва гариг</th><th>Пүрэв гариг</th><th>Баасан гариг</th><th>Бямба гариг</th><th>Ням гариг</th><th>Хураангуй</th></tr>";

    foreach ($grouped_reports as $week_range => $week_reports) {
        foreach ($week_reports as $report) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($week_range) . "</td>";
            echo "<td>" . htmlspecialchars($report['employee_name']) . "</td>";
            echo "<td>" . htmlspecialchars(ucfirst($report['status'])) . "</td>";
            echo "<td>" . ($report['submitted_at'] ? date('Y-m-d H:i', strtotime($report['submitted_at'])) : '-') . "</td>";
            echo "<td>" . htmlspecialchars($report['reviewed_by_name'] ?? '-') . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['monday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['tuesday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['wednesday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['thursday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['friday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['saturday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['sunday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['summary'])) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    exit;
} elseif ($type === 'word') {
    header('Content-Type: application/vnd.ms-word; charset=UTF-8');
    header('Content-Disposition: attachment; filename="weekly_reports.doc"');
    // Add BOM for UTF-8
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);

    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<h1>7 хоногийн тайлангууд</h1>";

    foreach ($grouped_reports as $week_range => $week_reports) {
        echo "<h2>Долоо хоног: " . htmlspecialchars($week_range) . "</h2>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Ажилчин</th><th>Статус</th><th>Илгээсэн огноо</th><th>Хянасан</th><th>Даваа гариг</th><th>Мягмар гариг</th><th>Лхагва гариг</th><th>Пүрэв гариг</th><th>Баасан гариг</th><th>Бямба гариг</th><th>Ням гариг</th><th>Хураангуй</th></tr>";

        foreach ($week_reports as $report) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($report['employee_name']) . "</td>";
            echo "<td>" . htmlspecialchars(ucfirst($report['status'])) . "</td>";
            echo "<td>" . ($report['submitted_at'] ? date('Y-m-d H:i', strtotime($report['submitted_at'])) : '-') . "</td>";
            echo "<td>" . htmlspecialchars($report['reviewed_by_name'] ?? '-') . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['monday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['tuesday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['wednesday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['thursday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['friday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['saturday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['sunday_work'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($report['summary'])) . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    echo "</body></html>";
    exit;
} else {
    echo "Invalid export type.";
    exit;
}
