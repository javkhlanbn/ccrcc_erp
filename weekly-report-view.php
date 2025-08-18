<?php 
session_start();
// Монголын цагийн бүс тохируулах
date_default_timezone_set('Asia/Ulaanbaatar');

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    $em = "Анх удаа нэвтэрч байна";
    header("Location: login.php?error=$em");
    exit();
}

include "DB_connection.php";
include "app/Model/WeeklyReport.php";

$user_id = $_SESSION['id'];
$report_id = $_GET['id'] ?? '';

if (!$report_id) {
    header("Location: weekly-report.php?error=Тайлан сонгогдоогүй");
    exit();
}

$report = get_weekly_report_by_id($conn, $report_id);
if (!$report || ($report['user_id'] != $user_id && $_SESSION['role'] != 'admin')) {
    header("Location: weekly-report.php?error=Тайлан олдсонгүй эсвэл хандах эрх байхгүй");
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Тайлан харах</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            min-height: 100vh;
            overflow-x: auto;
        }
        
        .report-view-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-button {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-submitted {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-reviewed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .report-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .day-section {
            margin-bottom: 25px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .day-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-weight: bold;
            color: #333;
        }
        
        .day-icon {
            margin-right: 10px;
            font-size: 18px;
            color: #667eea;
        }
        
        .day-content {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
            min-height: 50px;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        
        .summary-section {
            margin-top: 30px;
            padding: 20px;
            background: #e8f4fd;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }
        
        .summary-content {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        
        .feedback-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #28a745;
            margin-top: 20px;
        }
    </style>
</head>
<body class="body">
    <?php include "inc/nav.php"; ?>
    
    <div class="main-content">
        <div class="report-view-container">
            
            <div class="report-header">
                <div>
                    <h1><i class="fa fa-calendar-week"></i> Тайлан харах</h1>
                    <p><i class="fa fa-calendar"></i> <?= date('Y-m-d', strtotime($report['week_start_date'])) ?> - <?= date('Y-m-d', strtotime($report['week_end_date'])) ?></p>
                </div>
                <a href="weekly-report.php" class="back-button">
                    <i class="fa fa-arrow-left"></i> Буцах
                </a>
            </div>
            
            <div class="status-info">
                <div>
                    <strong>Тайлангийн статус:</strong>
                    <span class="status-badge status-<?= $report['status'] ?>">
                        <?php 
                        switch($report['status']) {
                            case 'draft': echo 'Ноорог'; break;
                            case 'submitted': echo 'Илгээсэн'; break;
                            case 'reviewed': echo 'Хянасан'; break;
                        }
                        ?>
                    </span>
                </div>
                <div>
                    <?php if ($report['submitted_at']): ?>
                        <small><i class="fa fa-clock-o"></i> Илгээсэн: <?= date('Y-m-d H:i', strtotime($report['submitted_at'])) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="report-content">
                <h3><i class="fa fa-clipboard-list"></i> 7 хоногийн тайлан</h3>
                
                <?php 
                $days = [
                    'monday_work' => ['Даваа гариг', 'fa-calendar-day'],
                    'tuesday_work' => ['Мягмар гариг', 'fa-calendar-day'],
                    'wednesday_work' => ['Лхагва гариг', 'fa-calendar-day'],
                    'thursday_work' => ['Пүрэв гариг', 'fa-calendar-day'],
                    'friday_work' => ['Баасан гариг', 'fa-calendar-day'],
                    'saturday_work' => ['Бямба гариг', 'fa-calendar-day'],
                    'sunday_work' => ['Ням гариг', 'fa-calendar-day']
                ];
                
                foreach ($days as $field => $day_info):
                ?>
                    <div class="day-section">
                        <div class="day-header">
                            <i class="fa <?= $day_info[1] ?> day-icon"></i>
                            <span><?= $day_info[0] ?></span>
                        </div>
                        <div class="day-content">
                            <?= $report[$field] ? nl2br(htmlspecialchars($report[$field])) : '<em>Тайлан бичээгүй байна</em>' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="summary-section">
                    <h4><i class="fa fa-clipboard-list"></i> Долоо хоногийн хураангуй</h4>
                    <div class="summary-content">
                        <?= $report['summary'] ? nl2br(htmlspecialchars($report['summary'])) : '<em>Хураангуй бичээгүй байна</em>' ?>
                    </div>
                </div>
            </div>
            
            <!-- Админы санал хүсэлт -->
            <?php if ($report['admin_feedback']): ?>
                <div class="feedback-section">
                    <h4><i class="fa fa-comment"></i> Админы санал хүсэлт:</h4>
                    <p><?= nl2br(htmlspecialchars($report['admin_feedback'])) ?></p>
                    <small>Хянасан: <?= $report['reviewed_by_name'] ?: 'Админ' ?> - <?= date('Y-m-d H:i', strtotime($report['reviewed_at'])) ?></small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
