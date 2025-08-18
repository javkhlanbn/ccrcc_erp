<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    $em = "Анх удаа нэвтэрч байна";
    header("Location: login.php?error=$em");
    exit();
}

include "DB_connection.php";
include "app/Model/TimeTracking.php";
include "app/Model/User.php";

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];

// Өнөөдрийн цагийн төлөвийг авах
$today_status = get_today_time_status($conn, $user_id);

// Хураангуй мэдээлэл авах
$today_summary = get_user_time_summary($conn, $user_id, 'today');
$week_summary = get_user_time_summary($conn, $user_id, 'week');
$month_summary = get_user_time_summary($conn, $user_id, 'month');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Цагийн бүртгэл</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .main-content {
            flex: 1; /* Flexbox child тохиргоо */
            padding: 20px;
            min-height: 100vh;
            overflow-x: auto;
        }
        
        .time-tracking-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .time-status-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .time-button {
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            margin: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .start-btn {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
        }
        
        .end-btn {
            background: linear-gradient(45deg, #f44336, #da190b);
            color: white;
        }
        
        .disabled-btn {
            background: #cccccc;
            color: #666666;
            cursor: not-allowed;
        }
        
        .time-button:not(.disabled-btn):hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .current-time {
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .status-text {
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 5px solid;
        }
        
        .summary-card.today {
            border-left-color: #4CAF50;
        }
        
        .summary-card.week {
            border-left-color: #2196F3;
        }
        
        .summary-card.month {
            border-left-color: #FF9800;
        }
        
        .summary-card h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 5px 0;
        }
        
        .summary-label {
            color: #666;
            font-size: 14px;
        }
        
        .time-history {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .history-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .history-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .overtime-badge {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 11px;
            margin-left: 5px;
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
        
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body class="body">
    <?php include "inc/nav.php"; ?>
    
    <div class="main-content">
        <div class="time-tracking-container">
        <div class="time-status-card">
            <div class="current-time" id="currentTime"></div>
            
            <?php if ($today_status['status'] == 'not_started'): ?>
                <div class="status-text">
                    <i class="fa fa-clock-o"></i> Өнөөдөр ажил эхлүүлээгүй байна
                </div>
                <p>Өнөөдөр ажил эхлэх товчийг дарснаар таны цаг бүртгэгдэнэ.</p>
                <form method="POST" action="app/time-action.php" style="display:inline;">
                    <input type="hidden" name="action" value="start">
                    <button type="submit" class="time-button start-btn">
                        🟢 Цаг эхлүүлэх
                    </button>
                </form>
                
            <?php elseif ($today_status['status'] == 'in_progress'): ?>
                <div class="status-text">
                    <i class="fa fa-play-circle text-success"></i> Ажил явж байна
                </div>
                <p>Эхэлсэн цаг: <strong><?= $today_status['entry']['start_time'] ?></strong></p>
                <p>Ажлаа дуусгасны дараа 'Цаг дуусгах' товчийг дарж цагийн бүртгэлээ баталгаажуулна уу.</p>
                
                <button class="time-button disabled-btn" disabled>
                    🟢 Цаг эхлүүлэх
                </button>
                <form method="POST" action="app/time-action.php" style="display:inline;">
                    <input type="hidden" name="action" value="end">
                    <button type="submit" class="time-button end-btn">
                        🔴 Цаг дуусгах
                    </button>
                </form>
                
            <?php else: ?>
                <div class="status-text">
                    <i class="fa fa-check-circle text-success"></i> Өнөөдрийн ажил дууссан
                </div>
                <p>Эхэлсэн: <strong><?= $today_status['entry']['start_time'] ?></strong> | 
                   Дууссан: <strong><?= $today_status['entry']['end_time'] ?></strong></p>
                <p>Нийт ажилласан цаг: <strong><?= $today_status['entry']['total_hours'] ?> цаг</strong>
                   <?php if ($today_status['entry']['overtime_hours'] > 0): ?>
                       <span class="overtime-badge">+<?= $today_status['entry']['overtime_hours'] ?> илүү цаг</span>
                   <?php endif; ?>
                </p>
                
                <form method="POST" action="app/time-action.php" style="display:inline;">
                    <input type="hidden" name="action" value="start">
                    <button type="submit" class="time-button start-btn">
                        🟢 Дахин эхлүүлэх
                    </button>
                </form>
                <button class="time-button disabled-btn" disabled>
                    🔴 Цаг дуусгах
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Хураангуй картууд -->
        <div class="summary-cards">
            <div class="summary-card today">
                <h3><i class="fa fa-calendar"></i> Өнөөдөр</h3>
                <div class="summary-value"><?= $today_summary['summary']['total_hours'] ?: '0' ?> цаг</div>
                <div class="summary-label">Ажилласан цаг</div>
                <?php if ($today_summary['summary']['total_overtime'] > 0): ?>
                    <div class="summary-value"><?= $today_summary['summary']['total_overtime'] ?> цаг</div>
                    <div class="summary-label">Илүү цаг</div>
                <?php endif; ?>
            </div>
            
            <div class="summary-card week">
                <h3><i class="fa fa-calendar-week"></i> 7 хоног</h3>
                <div class="summary-value"><?= round($week_summary['summary']['total_hours'], 1) ?: '0' ?> цаг</div>
                <div class="summary-label">Нийт ажилласан</div>
                <div class="summary-value"><?= $week_summary['summary']['days_worked'] ?: '0' ?> өдөр</div>
                <div class="summary-label">Ажилласан өдөр</div>
            </div>
            
            <div class="summary-card month">
                <h3><i class="fa fa-calendar-month"></i> 30 хоног</h3>
                <div class="summary-value"><?= round($month_summary['summary']['total_hours'], 1) ?: '0' ?> цаг</div>
                <div class="summary-label">Нийт ажилласан</div>
                <div class="summary-value"><?= round($month_summary['summary']['avg_hours_per_day'], 1) ?: '0' ?> цаг</div>
                <div class="summary-label">Өдрийн дундаж</div>
            </div>
        </div>
        
        <!-- Өмнөх өдрүүдийн түүх -->
        <div class="time-history">
            <h3><i class="fa fa-history"></i> Сүүлийн 30 хоногийн түүх</h3>
            
            <?php if (!empty($month_summary['details'])): ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Огноо</th>
                            <th>Эхлэх цаг</th>
                            <th>Дуусах цаг</th>
                            <th>Нийт цаг</th>
                            <th>Илүү цаг</th>
                            <th>Төлөв</th>
                            <th>Тэмдэглэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($month_summary['details'] as $detail): ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($detail['date'])) ?></td>
                                <td><?= $detail['start_time'] ?: '-' ?></td>
                                <td><?= $detail['end_time'] ?: '-' ?></td>
                                <td><?= $detail['total_hours'] ? round($detail['total_hours'], 2) . ' цаг' : '-' ?></td>
                                <td>
                                    <?php if ($detail['overtime_hours'] > 0): ?>
                                        <span class="overtime-badge"><?= round($detail['overtime_hours'], 2) ?> цаг</span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $detail['status'] ?>">
                                        <?php 
                                        switch($detail['status']) {
                                            case 'completed': echo 'Дууссан'; break;
                                            case 'in_progress': echo 'Явж байна'; break;
                                            default: echo $detail['status']; break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?= $detail['notes'] ?: '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-muted">Цагийн бүртгэл байхгүй байна.</p>
            <?php endif; ?>
        </div>
        </div>
    </div>
    
    <script>
        // Одоогийн цагийг харуулах
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('mn-MN', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        
        // Цагийг секунд бүр шинэчлэх
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
        
        // Автомат refresh - 5 минут тутам хуудсыг шинэчлэх
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 минут
    </script>
    
    <?php 
    // Success/Error мессеж харуулах
    if (isset($_GET['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success';
                alertDiv.innerHTML = '<i class="fa fa-check"></i> <?= htmlspecialchars($_GET['success']) ?>';
                document.querySelector('.time-tracking-container').insertBefore(alertDiv, document.querySelector('.time-status-card'));
                
                setTimeout(function() {
                    alertDiv.remove();
                }, 5000);
            });
        </script>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-error';
                alertDiv.innerHTML = '<i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error']) ?>';
                document.querySelector('.time-tracking-container').insertBefore(alertDiv, document.querySelector('.time-status-card'));
                
                setTimeout(function() {
                    alertDiv.remove();
                }, 5000);
            });
        </script>
    <?php endif; ?>
</body>
</html>
