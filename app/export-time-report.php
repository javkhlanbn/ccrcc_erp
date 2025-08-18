<?php
session_start();
// Монголын цагийн бүс тохируулах
date_default_timezone_set('Asia/Ulaanbaatar');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

include "../DB_connection.php";
include "Model/TimeTracking.php";
include "Model/User.php";

$type = $_GET['type'] ?? 'excel';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$user_filter = $_GET['user_filter'] ?? '';

$time_reports = get_all_users_time_report($conn, $date_from, $date_to);

if ($user_filter) {
    $time_reports = array_filter($time_reports, function($report) use ($user_filter) {
        return $report['id'] == $user_filter;
    });
}

if ($type == 'pdf') {
    // PDF Export
    if (file_exists('../vendor/autoload.php')) {
        require_once('../vendor/autoload.php');
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Task Management System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Цагийн тайлан - ' . $date_from . ' - ' . $date_to);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('dejavusans', 'B', 16);
        $pdf->Cell(0, 10, 'Цагийн удирдлагын тайлан', 0, 1, 'C');
        
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Cell(0, 8, 'Хугацаа: ' . $date_from . ' - ' . $date_to, 0, 1);
        $pdf->Cell(0, 8, 'Огноо: ' . date('Y-m-d H:i'), 0, 1);
        if ($user_filter) $pdf->Cell(0, 8, 'Ажилтан: ' . $user_filter, 0, 1);
        $pdf->Ln(5);
        
        // Statistics
        $total_employees = count($time_reports);
        $total_hours = array_sum(array_column($time_reports, 'total_hours'));
        $total_overtime = array_sum(array_column($time_reports, 'total_overtime'));
        
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 8, 'Ерөнхий статистик:', 0, 1);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 6, 'Нийт ажилчин: ' . $total_employees, 0, 1);
        $pdf->Cell(0, 6, 'Нийт ажилласан цаг: ' . number_format($total_hours, 1), 0, 1);
        $pdf->Cell(0, 6, 'Нийт илүү цаг: ' . number_format($total_overtime, 1), 0, 1);
        $pdf->Ln(5);
        
        // Table header
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->Cell(10, 8, '#', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Ажилчин', 1, 0, 'C');
        $pdf->Cell(15, 8, 'Өдөр', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Нийт цаг', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Дундаж', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Илүү цаг', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Гүйцэтгэл', 1, 1, 'C');
        
        // Table data
        $pdf->SetFont('dejavusans', '', 7);
        $i = 1;
        foreach ($time_reports as $report) {
            $performance = '';
            $avg_daily = $report['avg_hours_per_day'];
            if ($avg_daily >= 8) $performance = 'Маш сайн';
            elseif ($avg_daily >= 7) $performance = 'Сайн';
            elseif ($avg_daily >= 6) $performance = 'Дундаж';
            else $performance = 'Хангалтгүй';
            
            $pdf->Cell(10, 6, $i++, 1, 0, 'C');
            $pdf->Cell(40, 6, substr($report['full_name'], 0, 25), 1, 0);
            $pdf->Cell(15, 6, $report['days_worked'], 1, 0, 'C');
            $pdf->Cell(20, 6, number_format($report['total_hours'], 1), 1, 0, 'C');
            $pdf->Cell(20, 6, number_format($report['avg_hours_per_day'], 1), 1, 0, 'C');
            $pdf->Cell(20, 6, number_format($report['total_overtime'], 1), 1, 0, 'C');
            $pdf->Cell(25, 6, $performance, 1, 1, 'C');
        }
        
        $filename = 'time_report_' . $date_from . '_' . $date_to . '.pdf';
        $pdf->Output($filename, 'D');
        
    } else {
        die('PDF Library суулгагдаагүй байна.');
    }
    
} elseif ($type == 'excel') {
    // Excel Export
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="time_report_' . $date_from . '_' . $date_to . '.xls"');
    header('Cache-Control: max-age=0');
    
    echo "<html><head><meta charset='utf-8'></head><body>";
    echo "<h2>Цагийн удирдлагын тайлан</h2>";
    echo "<p><strong>Хугацаа:</strong> " . $date_from . " - " . $date_to . "</p>";
    echo "<p><strong>Огноо:</strong> " . date('Y-m-d H:i') . "</p>";
    if ($user_filter) echo "<p><strong>Ажилтан:</strong> " . htmlspecialchars($user_filter) . "</p>";
    
    // Statistics
    $total_employees = count($time_reports);
    $total_hours = array_sum(array_column($time_reports, 'total_hours'));
    $total_overtime = array_sum(array_column($time_reports, 'total_overtime'));
    
    echo "<h3>Ерөнхий статистик</h3>";
    echo "<table border='1'>";
    echo "<tr><td>Нийт ажилчин</td><td>" . $total_employees . "</td></tr>";
    echo "<tr><td>Нийт ажилласан цаг</td><td>" . number_format($total_hours, 1) . "</td></tr>";
    echo "<tr><td>Нийт илүү цаг</td><td>" . number_format($total_overtime, 1) . "</td></tr>";
    echo "</table><br>";
    
    echo "<h3>Дэлгэрэнгүй мэдээлэл</h3>";
    echo "<table border='1'>";
    echo "<tr><th>#</th><th>Ажилчин</th><th>Хэрэглэгчийн нэр</th><th>Ажилласан өдөр</th><th>Нийт цаг</th><th>Дундаж өдөр</th><th>Илүү цаг</th><th>Макс цаг</th><th>Мин цаг</th><th>Гүйцэтгэл</th></tr>";
    
    $i = 1;
    foreach ($time_reports as $report) {
        $performance = '';
        $avg_daily = $report['avg_hours_per_day'];
        if ($avg_daily >= 8) $performance = 'Маш сайн';
        elseif ($avg_daily >= 7) $performance = 'Сайн';
        elseif ($avg_daily >= 6) $performance = 'Дундаж';
        else $performance = 'Хангалтгүй';
        
        echo "<tr>";
        echo "<td>" . $i++ . "</td>";
        echo "<td>" . htmlspecialchars($report['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($report['username']) . "</td>";
        echo "<td>" . $report['days_worked'] . "</td>";
        echo "<td>" . number_format($report['total_hours'], 1) . "</td>";
        echo "<td>" . number_format($report['avg_hours_per_day'], 1) . "</td>";
        echo "<td>" . number_format($report['total_overtime'], 1) . "</td>";
        echo "<td>" . ($report['max_hours'] ? number_format($report['max_hours'], 1) : '-') . "</td>";
        echo "<td>" . ($report['min_hours'] ? number_format($report['min_hours'], 1) : '-') . "</td>";
        echo "<td>" . $performance . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</body></html>";
}
?>
