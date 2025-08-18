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
include "app/Model/User.php";

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];

// Одоогийн долоо хоногийн огноо авах
$current_week = get_current_week_dates();
$week_start = $current_week['start'];
$week_end = $current_week['end'];

// Одоогийн долоо хоногийн тайлан авах
$current_report = get_week_report($conn, $user_id, $week_start);

// Өмнөх тайлангууд авах
$previous_reports = get_user_weekly_reports($conn, $user_id, 5);

?>
<!DOCTYPE html>
<html>
<head>
    <title>7 хоногийн тайлан</title>
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
        
        .weekly-report-container {
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
            text-align: center;
        }
        
        .week-selector {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .week-info {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .report-form {
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
        
        .day-textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
        }
        
        .summary-section {
            margin-top: 30px;
            padding: 20px;
            background: #e8f4fd;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }
        
        .summary-textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-save {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
        }
        
        .btn-submit {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            color: white;
        }
        
        .btn-disabled {
            background: #cccccc;
            color: #666666;
            cursor: not-allowed;
        }
        
        .btn:not(.btn-disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
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
        
        .previous-reports {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .reports-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .reports-table th,
        .reports-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .reports-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .reports-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        
        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }
    </style>
</head>
<body class="body">
    <?php include "inc/nav.php"; ?>
    
    <div class="main-content">
        <div class="weekly-report-container">
            
            <div class="report-header">
                <h1><i class="fa fa-calendar-week"></i> 7 хоногийн тайлан</h1>
                <p>Долоо хоногийн турш хийсэн ажлын тайланг бөглөж админд илгээнэ үү</p>
            </div>
            
            <div class="week-selector">
                <div class="week-info">
                    <i class="fa fa-calendar"></i> 
                    <strong><?= $current_week['week_number'] ?>-р долоо хоног</strong> 
                    (<?= date('Y-m-d', strtotime($week_start)) ?> - <?= date('Y-m-d', strtotime($week_end)) ?>)
                </div>
                <?php if ($current_report): ?>
                    <span class="status-badge status-<?= $current_report['status'] ?>">
                        <?php 
                        switch($current_report['status']) {
                            case 'draft': echo 'Ноорог'; break;
                            case 'submitted': echo 'Илгээсэн'; break;
                            case 'reviewed': echo 'Хянасан'; break;
                        }
                        ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($current_report && $current_report['status'] == 'reviewed' && $current_report['admin_feedback']): ?>
                <div class="alert alert-info">
                    <h4><i class="fa fa-comment"></i> Админы санал хүсэлт:</h4>
                    <p><?= nl2br(htmlspecialchars($current_report['admin_feedback'])) ?></p>
                    <small>Хянасан: <?= $current_report['reviewed_by_name'] ?? 'Админ' ?> - <?= date('Y-m-d H:i', strtotime($current_report['reviewed_at'])) ?></small>
                </div>
            <?php endif; ?>
            
            <form class="report-form" method="POST" action="app/save-weekly-report.php">
                <input type="hidden" name="week_start_date" value="<?= $week_start ?>">
                <input type="hidden" name="week_end_date" value="<?= $week_end ?>">
                
                <?php 
                $days = [
                    'monday' => ['Даваа гариг', 'fa-calendar-day'],
                    'tuesday' => ['Мягмар гариг', 'fa-calendar-day'],
                    'wednesday' => ['Лхагва гариг', 'fa-calendar-day'],
                    'thursday' => ['Пүрэв гариг', 'fa-calendar-day'],
                    'friday' => ['Баасан гариг', 'fa-calendar-day'],
                    'saturday' => ['Бямба гариг', 'fa-calendar-day'],
                    'sunday' => ['Ням гариг', 'fa-calendar-day']
                ];
                
                foreach ($days as $day_key => $day_info):
                    $field_name = $day_key . '_work';
                    $current_value = $current_report[$field_name] ?? '';
                ?>
                    <div class="day-section">
                        <div class="day-header">
                            <i class="fa <?= $day_info[1] ?> day-icon"></i>
                            <span><?= $day_info[0] ?></span>
                            <small style="margin-left: auto; color: #666;">
                                <?= date('Y-m-d', strtotime($week_start . ' +' . (array_search($day_key, array_keys($days))) . ' days')) ?>
                            </small>
                        </div>
                        <textarea 
                            name="<?= $day_key ?>" 
                            class="day-textarea" 
                            placeholder="<?= $day_info[0] ?>-т хийсэн ажлуудаа дэлгэрэнгүй бичнэ үү..."
                            <?= ($current_report && $current_report['status'] != 'draft') ? 'readonly' : '' ?>
                        ><?= htmlspecialchars($current_value) ?></textarea>
                    </div>
                <?php endforeach; ?>
                
                <div class="summary-section">
                    <h4><i class="fa fa-clipboard-list"></i> Долоо хоногийн хураангуй</h4>
                    <textarea 
                        name="summary" 
                        class="summary-textarea" 
                        placeholder="Энэ долоо хоногт хийсэн ажлын хураангуй, ололт амжилт, бэрхшээл зэргийг бичнэ үү..."
                        <?= ($current_report && $current_report['status'] != 'draft') ? 'readonly' : '' ?>
                    ><?= htmlspecialchars($current_report['summary'] ?? '') ?></textarea>
                </div>
                
                <?php if (!$current_report || $current_report['status'] == 'draft'): ?>
                    <div class="action-buttons">
                        <button type="submit" name="action" value="save" class="btn btn-save">
                            <i class="fa fa-save"></i> Хадгалах
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-submit">
                            <i class="fa fa-paper-plane"></i> Админд илгээх
                        </button>
                    </div>
                <?php else: ?>
                    <div class="action-buttons">
                        <span class="btn btn-disabled">
                            <i class="fa fa-lock"></i> 
                            <?= $current_report['status'] == 'submitted' ? 'Илгээгдсэн' : 'Хянагдсан' ?>
                        </span>
                    </div>
                <?php endif; ?>
            </form>
            
            <!-- Өмнөх тайлангууд -->
            <?php if (!empty($previous_reports)): ?>
                <div class="previous-reports">
                    <h3><i class="fa fa-history"></i> Өмнөх тайлангууд</h3>
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Долоо хоног</th>
                                <th>Статус</th>
                                <th>Илгээсэн огноо</th>
                                <th>Хянасан</th>
                                <th>Үйлдэл</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($previous_reports as $report): ?>
                                <tr>
                                    <td><?= $report['week_range'] ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $report['status'] ?>">
                                            <?php 
                                            switch($report['status']) {
                                                case 'draft': echo 'Ноорог'; break;
                                                case 'submitted': echo 'Илгээсэн'; break;
                                                case 'reviewed': echo 'Хянасан'; break;
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td><?= $report['submitted_at'] ? date('Y-m-d H:i', strtotime($report['submitted_at'])) : '-' ?></td>
                                    <td><?= $report['reviewed_by_name'] ?: '-' ?></td>
                                    <td>
                                        <a href="weekly-report-view.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-edit">
                                            <i class="fa fa-eye"></i> Харах
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
