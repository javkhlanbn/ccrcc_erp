<?php 
session_start();

// Test session
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
            margin-left: 250px; /* Navigation sidebar width */
            padding: 20px;
            min-height: 100vh;
        }
        
        .time-tracking-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
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
        
        .current-time {
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .time-button {
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            margin: 10px;
            transition: all 0.3s ease;
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
        
        .status-text {
            font-size: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php include "inc/nav.php"; ?>
    
    <div class="main-content">
        <div class="time-tracking-container">
            <h1 style="text-align: center; margin-bottom: 30px;">
                🕐 Цагийн бүртгэл
            </h1>
            
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
                        <i class="fa fa-play-circle"></i> Ажил явж байна
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
                        <i class="fa fa-check-circle"></i> Өнөөдрийн ажил дууссан
                    </div>
                    <p>Эхэлсэн: <strong><?= $today_status['entry']['start_time'] ?></strong> | 
                       Дууссан: <strong><?= $today_status['entry']['end_time'] ?></strong></p>
                    <p>Нийт ажилласан цаг: <strong><?= $today_status['entry']['total_hours'] ?> цаг</strong></p>
                    
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
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px;">
                <h3>📊 Debug мэдээлэл</h3>
                <p><strong>Хэрэглэгч:</strong> <?= $_SESSION['full_name'] ?> (ID: <?= $_SESSION['id'] ?>)</p>
                <p><strong>Өнөөдрийн төлөв:</strong> <?= $today_status['status'] ?></p>
                <p><strong>Огноо:</strong> <?= date('Y-m-d H:i:s') ?></p>
                <?php if (isset($today_status['entry'])): ?>
                    <p><strong>Entry мэдээлэл:</strong> <?= json_encode($today_status['entry']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
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
        
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
    </script>
</body>
</html>
