<?php 
session_start();

// Test session үүсгэх
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 17;
    $_SESSION['role'] = 'employee';
    $_SESSION['full_name'] = 'Test User';
}

include "DB_connection.php";
include "app/Model/TimeTracking.php";

$user_id = $_SESSION['id'];
$today_status = get_today_time_status($conn, $user_id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Цагийн бүртгэл - Тест</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .status-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        .time-display {
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
        }
        .btn {
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            margin: 10px;
        }
        .btn-start {
            background: #4CAF50;
            color: white;
        }
        .btn-end {
            background: #f44336;
            color: white;
        }
        .btn-disabled {
            background: #cccccc;
            color: #666666;
            cursor: not-allowed;
        }
        .status-text {
            font-size: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🕐 Цагийн бүртгэл</h1>
        
        <div class="status-card">
            <div class="time-display" id="currentTime"></div>
            
            <?php if ($today_status['status'] == 'not_started'): ?>
                <div class="status-text">
                    ⏰ Өнөөдөр ажил эхлүүлээгүй байна
                </div>
                <p>Ажил эхлэх товчийг дарснаар таны цаг бүртгэгдэнэ.</p>
                
                <form method="POST" action="app/time-action.php" style="display:inline;">
                    <input type="hidden" name="action" value="start">
                    <button type="submit" class="btn btn-start">
                        🟢 Цаг эхлүүлэх
                    </button>
                </form>
                
            <?php elseif ($today_status['status'] == 'in_progress'): ?>
                <div class="status-text">
                    ▶️ Ажил явж байна
                </div>
                <p>Эхэлсэн цаг: <strong><?= $today_status['entry']['start_time'] ?></strong></p>
                <p>Ажлаа дуусгасны дараа дуусгах товчийг дарна уу.</p>
                
                <button class="btn btn-disabled" disabled>
                    🟢 Цаг эхлүүлэх
                </button>
                <form method="POST" action="app/time-action.php" style="display:inline;">
                    <input type="hidden" name="action" value="end">
                    <button type="submit" class="btn btn-end">
                        🔴 Цаг дуусгах
                    </button>
                </form>
                
            <?php else: ?>
                <div class="status-text">
                    ✅ Өнөөдрийн ажил дууссан
                </div>
                <p>Эхэлсэн: <strong><?= $today_status['entry']['start_time'] ?></strong> | 
                   Дууссан: <strong><?= $today_status['entry']['end_time'] ?></strong></p>
                <p>Нийт ажилласан цаг: <strong><?= $today_status['entry']['total_hours'] ?> цаг</strong></p>
                
                <form method="POST" action="app/time-action.php" style="display:inline;">
                    <input type="hidden" name="action" value="start">
                    <button type="submit" class="btn btn-start">
                        🟢 Дахин эхлүүлэх
                    </button>
                </form>
                <button class="btn btn-disabled" disabled>
                    🔴 Цаг дуусгах
                </button>
            <?php endif; ?>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px; margin-top: 20px;">
            <h3>📊 Мэдээлэл</h3>
            <p><strong>Хэрэглэгч:</strong> <?= $_SESSION['full_name'] ?> (ID: <?= $_SESSION['id'] ?>)</p>
            <p><strong>Өнөөдрийн төлөв:</strong> <?= $today_status['status'] ?></p>
            <p><strong>Огноо:</strong> <?= date('Y-m-d H:i:s') ?></p>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="time-tracking.php" style="color: #667eea;">← Үндсэн хуудас руу буцах</a> |
            <a href="admin-time-tracking.php" style="color: #667eea;">Админ хуудас →</a>
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
            const element = document.getElementById('currentTime');
            if (element) {
                element.textContent = timeString;
            }
        }
        
        // Цагийг секунд бүр шинэчлэх
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
    </script>
</body>
</html>
