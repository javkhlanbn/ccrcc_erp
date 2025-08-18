<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "../DB_connection.php";
    include "Model/Task.php";
    include "Model/User.php";
    
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    
    if (!$type || !$user_id) {
        die("Invalid parameters");
    }
    
    $user_info = get_user_by_id($conn, $user_id);
    $report_data = get_user_task_report($conn, $user_id, $date_from, $date_to);
    
    if (!$user_info || !$report_data) {
        die("No data found");
    }
    
    // Calculate statistics
    $total_tasks = count($report_data);
    $completed_tasks = 0;
    $overdue_tasks = 0;
    $pending_tasks = 0;
    $in_progress_tasks = 0;
    
    $today = new DateTime();
    foreach ($report_data as $task) {
        if ($task['status'] == 'completed') $completed_tasks++;
        elseif ($task['status'] == 'pending') $pending_tasks++;
        elseif ($task['status'] == 'in_progress') $in_progress_tasks++;
        
        if (!empty($task['due_date']) && $task['status'] != 'completed') {
            $due_date = new DateTime($task['due_date']);
            if ($due_date < $today) {
                $overdue_tasks++;
            }
        }
    }
    
    if ($type == 'pdf') {
        // PDF Export using TCPDF with Composer autoload
        if (file_exists('../vendor/autoload.php')) {
            require_once('../vendor/autoload.php');
            
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            $pdf->SetCreator('Task Management System');
            $pdf->SetAuthor('Admin');
            $pdf->SetTitle('Даалгаврын тайлан - ' . $user_info['full_name']);
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            $pdf->AddPage();
            
            // Add Mongolian font support
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->Cell(0, 10, 'Даалгаврын тайлан', 0, 1, 'C');
            
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->Cell(0, 8, 'Ажилтан: ' . $user_info['full_name'], 0, 1);
            $pdf->Cell(0, 8, 'Огноо: ' . date('Y-m-d'), 0, 1);
            if ($date_from) $pdf->Cell(0, 8, 'Эхлэх огноо: ' . $date_from, 0, 1);
            if ($date_to) $pdf->Cell(0, 8, 'Дуусах огноо: ' . $date_to, 0, 1);
            $pdf->Ln(5);
            
            // Statistics
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->Cell(0, 8, 'Статистик:', 0, 1);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell(0, 6, 'Нийт даалгавар: ' . $total_tasks, 0, 1);
            $pdf->Cell(0, 6, 'Дууссан: ' . $completed_tasks, 0, 1);
            $pdf->Cell(0, 6, 'Хугацаа хэтэрсэн: ' . $overdue_tasks, 0, 1);
            $pdf->Cell(0, 6, 'Хүлээгдэж байгаа: ' . $pending_tasks, 0, 1);
            $pdf->Cell(0, 6, 'Хийгдэж байгаа: ' . $in_progress_tasks, 0, 1);
            $pdf->Ln(5);
            
            // Table header
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(15, 8, '#', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Гарчиг', 1, 0, 'C');
            $pdf->Cell(50, 8, 'Тайлбар', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Дуусах огноо', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Төлөв', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Статус', 1, 1, 'C');
            
            // Table data
            $pdf->SetFont('dejavusans', '', 8);
            $i = 1;
            foreach ($report_data as $task) {
                $status_text = "";
                if ($task['status'] == 'completed') {
                    $status_text = "Дууссан";
                } elseif (!empty($task['due_date'])) {
                    $due_date = new DateTime($task['due_date']);
                    if ($due_date < $today && $task['status'] != 'completed') {
                        $status_text = "Хугацаа хэтэрсэн";
                    } else {
                        $status_text = $task['status'] == 'pending' ? 'Хүлээгдэж байгаа' : 'Хийгдэж байгаа';
                    }
                } else {
                    $status_text = $task['status'] == 'pending' ? 'Хүлээгдэж байгаа' : 'Хийгдэж байгаа';
                }
                
                $pdf->Cell(15, 6, $i++, 1, 0, 'C');
                $pdf->Cell(40, 6, substr($task['title'], 0, 20), 1, 0);
                $pdf->Cell(50, 6, substr($task['description'], 0, 30), 1, 0);
                $pdf->Cell(25, 6, $task['due_date'] ?: 'Хугацаагүй', 1, 0, 'C');
                $pdf->Cell(25, 6, $task['status'], 1, 0, 'C');
                $pdf->Cell(25, 6, $status_text, 1, 1, 'C');
            }
            
            $filename = 'task_report_' . $user_info['full_name'] . '_' . date('Y-m-d') . '.pdf';
            $pdf->Output($filename, 'D');
            
        } elseif (file_exists('../vendor/tecnickcom/tcpdf/tcpdf.php')) {
            // Fallback to direct include
            require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
            
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            $pdf->SetCreator('Task Management System');
            $pdf->SetAuthor('Admin');
            $pdf->SetTitle('Даалгаврын тайлан - ' . $user_info['full_name']);
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            $pdf->AddPage();
            
            // Add Mongolian font support
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->Cell(0, 10, 'Даалгаврын тайлан', 0, 1, 'C');
            
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->Cell(0, 8, 'Ажилтан: ' . $user_info['full_name'], 0, 1);
            $pdf->Cell(0, 8, 'Огноо: ' . date('Y-m-d'), 0, 1);
            if ($date_from) $pdf->Cell(0, 8, 'Эхлэх огноо: ' . $date_from, 0, 1);
            if ($date_to) $pdf->Cell(0, 8, 'Дуусах огноо: ' . $date_to, 0, 1);
            $pdf->Ln(5);
            
            // Statistics
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->Cell(0, 8, 'Статистик:', 0, 1);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell(0, 6, 'Нийт даалгавар: ' . $total_tasks, 0, 1);
            $pdf->Cell(0, 6, 'Дууссан: ' . $completed_tasks, 0, 1);
            $pdf->Cell(0, 6, 'Хугацаа хэтэрсэн: ' . $overdue_tasks, 0, 1);
            $pdf->Cell(0, 6, 'Хүлээгдэж байгаа: ' . $pending_tasks, 0, 1);
            $pdf->Cell(0, 6, 'Хийгдэж байгаа: ' . $in_progress_tasks, 0, 1);
            $pdf->Ln(5);
            
            // Table header
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(15, 8, '#', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Гарчиг', 1, 0, 'C');
            $pdf->Cell(50, 8, 'Тайлбар', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Дуусах огноо', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Төлөв', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Статус', 1, 1, 'C');
            
            // Table data
            $pdf->SetFont('dejavusans', '', 8);
            $i = 1;
            foreach ($report_data as $task) {
                $status_text = "";
                if ($task['status'] == 'completed') {
                    $status_text = "Дууссан";
                } elseif (!empty($task['due_date'])) {
                    $due_date = new DateTime($task['due_date']);
                    if ($due_date < $today && $task['status'] != 'completed') {
                        $status_text = "Хугацаа хэтэрсэн";
                    } else {
                        $status_text = $task['status'] == 'pending' ? 'Хүлээгдэж байгаа' : 'Хийгдэж байгаа';
                    }
                } else {
                    $status_text = $task['status'] == 'pending' ? 'Хүлээгдэж байгаа' : 'Хийгдэж байгаа';
                }
                
                $pdf->Cell(15, 6, $i++, 1, 0, 'C');
                $pdf->Cell(40, 6, substr($task['title'], 0, 20), 1, 0);
                $pdf->Cell(50, 6, substr($task['description'], 0, 30), 1, 0);
                $pdf->Cell(25, 6, $task['due_date'] ?: 'Хугацаагүй', 1, 0, 'C');
                $pdf->Cell(25, 6, $task['status'], 1, 0, 'C');
                $pdf->Cell(25, 6, $status_text, 1, 1, 'C');
            }
            
            $filename = 'task_report_' . $user_info['full_name'] . '_' . date('Y-m-d') . '.pdf';
            $pdf->Output($filename, 'D');
            
        } else {
            // No TCPDF found
            header('Content-Type: text/html');
            echo "<h1>PDF Library татах шаардлагатай</h1>";
            echo "<p>TCPDF library суулгагдаагүй байна. Composer ашиглан суулгана уу:</p>";
            echo "<pre>composer require tecnickcom/tcpdf</pre>";
            echo "<p>Эсвэл Excel/Word format ашиглана уу.</p>";
        }
        
    } elseif ($type == 'excel') {
        // Excel Export
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="task_report_' . $user_info['full_name'] . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        echo "<html><head><meta charset='utf-8'></head><body>";
        echo "<h2>Даалгаврын тайлан</h2>";
        echo "<p><strong>Ажилтан:</strong> " . $user_info['full_name'] . "</p>";
        echo "<p><strong>Огноо:</strong> " . date('Y-m-d') . "</p>";
        if ($date_from) echo "<p><strong>Эхлэх огноо:</strong> " . $date_from . "</p>";
        if ($date_to) echo "<p><strong>Дуусах огноо:</strong> " . $date_to . "</p>";
        
        echo "<h3>Статистик</h3>";
        echo "<table border='1'>";
        echo "<tr><td>Нийт даалгавар</td><td>" . $total_tasks . "</td></tr>";
        echo "<tr><td>Дууссан</td><td>" . $completed_tasks . "</td></tr>";
        echo "<tr><td>Хугацаа хэтэрсэн</td><td>" . $overdue_tasks . "</td></tr>";
        echo "<tr><td>Хүлээгдэж байгаа</td><td>" . $pending_tasks . "</td></tr>";
        echo "<tr><td>Хийгдэж байгаа</td><td>" . $in_progress_tasks . "</td></tr>";
        echo "</table><br>";
        
        echo "<h3>Даалгаврын жагсаалт</h3>";
        echo "<table border='1'>";
        echo "<tr><th>#</th><th>Гарчиг</th><th>Тайлбар</th><th>Дуусах огноо</th><th>Төлөв</th><th>Үүсгэсэн огноо</th><th>Статус</th></tr>";
        
        $i = 1;
        foreach ($report_data as $task) {
            $status_text = "";
            if ($task['status'] == 'completed') {
                $status_text = "Дууссан";
            } elseif (!empty($task['due_date'])) {
                $due_date = new DateTime($task['due_date']);
                if ($due_date < $today && $task['status'] != 'completed') {
                    $status_text = "Хугацаа хэтэрсэн";
                } else {
                    $status_text = $task['status'] == 'pending' ? 'Хүлээгдэж байгаа' : 'Хийгдэж байгаа';
                }
            } else {
                $status_text = $task['status'] == 'pending' ? 'Хүлээгдэж байгаа' : 'Хийгдэж байгаа';
            }
            
            echo "<tr>";
            echo "<td>" . $i++ . "</td>";
            echo "<td>" . htmlspecialchars($task['title']) . "</td>";
            echo "<td>" . htmlspecialchars($task['description']) . "</td>";
            echo "<td>" . ($task['due_date'] ?: 'Хугацаагүй') . "</td>";
            echo "<td>" . $task['status'] . "</td>";
            echo "<td>" . date('Y-m-d', strtotime($task['created_at'])) . "</td>";
            echo "<td>" . $status_text . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</body></html>";
        
    } elseif ($type == 'word') {
        // Word Export
        header('Content-Type: application/vnd.ms-word');
        header('Content-Disposition: attachment;filename="task_report_' . $user_info['full_name'] . '_' . date('Y-m-d') . '.doc"');
        header('Cache-Control: max-age=0');
        
        echo "<html><head><meta charset='utf-8'></head><body>";
        echo "<h1 style='text-align:center;'>Даалгаврын тайлан</h1>";
        echo "<p><strong>Ажилтан:</strong> " . $user_info['full_name'] . "</p>";
        echo "<p><strong>Огноо:</strong> " . date('Y-m-d') . "</p>";
        if ($date_from) echo "<p><strong>Эхлэх огноо:</strong> " . $date_from . "</p>";
        if ($date_to) echo "<p><strong>Дуусах огноо:</strong> " . $date_to . "</p>";
        
        echo "<h2>Статистик</h2>";
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><td><strong>Нийт даалгавар</strong></td><td>" . $total_tasks . "</td></tr>";
        echo "<tr><td><strong>Дууссан</strong></td><td>" . $completed_tasks . "</td></tr>";
        echo "<tr><td><strong>Хугацаа хэтэрсэн</strong></td><td>" . $overdue_tasks . "</td></tr>";
        echo "<tr><td><strong>Хүлээгдэж байгаа</strong></td><td>" . $pending_tasks . "</td></tr>";
        echo "<tr><td><strong>Хийгдэж байгаа</strong></td><td>" . $in_progress_tasks . "</td></tr>";
        echo "</table><br>";
        
        echo "<h2>Даалгаврын дэлгэрэнгүй жагсаалт</h2>";
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr style='background-color:#f0f0f0;'><th>#</th><th>Гарчиг</th><th>Тайлбар</th><th>Дуусах огноо</th><th>Төлөв</th><th>Үүсгэсэн огноо</th><th>Статус</th></tr>";
        
        $i = 1;
        foreach ($report_data as $task) {
            $status_text = "";
            $row_color = "";
            
            if ($task['status'] == 'completed') {
                $status_text = "Дууссан";
                $row_color = "background-color:#e8f5e8;";
            } elseif (!empty($task['due_date'])) {
                $due_date = new DateTime($task['due_date']);
                if ($due_date < $today && $task['status'] != 'completed') {
                    $status_text = "Хугацаа хэтэрсэн";
                    $row_color = "background-color:#ffebee;";
                } else {
                    $status_text = $task['status'] == 'pending' ? 'Хүлээгдэж байгаа' : 'Хийгдэж байгаа';
                    $row_color = $task['status'] == 'pending' ? "background-color:#fff3e0;" : "background-color:#f3e5f5;";
                }
            } else {
                $status_text = $task['status'] == 'pending' ? 'Хүлээгдэж байгаа' : 'Хийгдэж байгаа';
                $row_color = $task['status'] == 'pending' ? "background-color:#fff3e0;" : "background-color:#f3e5f5;";
            }
            
            echo "<tr style='" . $row_color . "'>";
            echo "<td>" . $i++ . "</td>";
            echo "<td>" . htmlspecialchars($task['title']) . "</td>";
            echo "<td>" . htmlspecialchars($task['description']) . "</td>";
            echo "<td>" . ($task['due_date'] ?: 'Хугацаагүй') . "</td>";
            echo "<td>" . $task['status'] . "</td>";
            echo "<td>" . date('Y-m-d', strtotime($task['created_at'])) . "</td>";
            echo "<td><strong>" . $status_text . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</body></html>";
    }
    
} else {
    die("Access denied");
}
?>
