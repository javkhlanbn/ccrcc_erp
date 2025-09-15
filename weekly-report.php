<?php
session_start();
// Монголын цагийн бүс тохируулах
date_default_timezone_set('Asia/Ulaanbaatar');

if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'employee') {
    $em = "Зөвхөн ажилтан хандах боломжтой";
    header("Location: login.php?error=$em");
    exit();
}

include "DB_connection.php";
include "app/Model/WeeklyReport.php";

$user_id = $_SESSION['id'];
$current_week = get_current_week_dates();

// Одоогийн долоо хоногийн тайлан шалгах
$current_report = get_week_report($conn, $user_id, $current_week['start']);

// Өмнөх тайлангуудыг авах
$user_reports = get_user_weekly_reports($conn, $user_id, 10);

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
            max-width: 1200px;
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

        .current-week-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .week-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }

        .report-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .day-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            background: white;
        }

        .day-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        .day-icon {
            margin-right: 10px;
            font-size: 16px;
            color: #667eea;
        }

        .day-input {
            width: 100%;
            min-height: 80px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
            font-family: inherit;
        }

        .summary-section {
            margin-top: 20px;
            padding: 20px;
            background: #e8f4fd;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }

        .summary-input {
            width: 100%;
            min-height: 100px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
            font-family: inherit;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
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
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
        }

        .btn-submit {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .reports-list {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .reports-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .reports-table th,
        .reports-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .reports-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        .reports-table tr:hover {
            background-color: #f5f5f5;
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

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
    </style>
</head>
<body class="body">
    <?php include "inc/nav.php"; ?>

    <div class="main-content">
        <div class="weekly-report-container">

            <div class="report-header">
                <h1><i class="fa fa-calendar-week"></i> 7 хоногийн тайлан</h1>
                <p>Долоо хоног тутамд гүйцэтгэсэн ажлаа тайлагнана уу</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <!-- Одоогийн долоо хоногийн тайлан -->
            <div class="current-week-section">
                <div class="week-info">
                    <h3><i class="fa fa-calendar"></i> Энэ долоо хоног: <?= date('Y-m-d', strtotime($current_week['start'])) ?> - <?= date('Y-m-d', strtotime($current_week['end'])) ?></h3>
                    <?php if ($current_report): ?>
                        <p><strong>Статус:</strong>
                            <span class="status-badge status-<?= $current_report['status'] ?>">
                                <?php
                                switch($current_report['status']) {
                                    case 'draft': echo 'Ноорог'; break;
                                    case 'submitted': echo 'Илгээсэн'; break;
                                    case 'reviewed': echo 'Хянасан'; break;
                                }
                                ?>
                            </span>
                        </p>
                    <?php endif; ?>
                </div>

                <form method="POST" action="app/save-weekly-report.php" class="report-form">
                    <input type="hidden" name="week_start_date" value="<?= $current_week['start'] ?>">
                    <input type="hidden" name="week_end_date" value="<?= $current_week['end'] ?>">

                    <h3><i class="fa fa-clipboard-list"></i> Ажлын тайлан</h3>

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

                    foreach ($days as $field => $day_info):
                    ?>
                        <div class="day-section">
                            <div class="day-header">
                                <i class="fa <?= $day_info[1] ?> day-icon"></i>
                                <span><?= $day_info[0] ?></span>
                                <?php if (in_array($field, ['saturday', 'sunday'])): ?>
                                    <small style="color: #666; margin-left: 10px;">(Нэмэлтээр)</small>
                                <?php endif; ?>
                            </div>
                            <textarea name="<?= $field ?>" class="day-input" placeholder="Энэ өдөр гүйцэтгэсэн ажлаа бичнэ үү..."><?= $current_report[$field . '_work'] ?? '' ?></textarea>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-section">
                        <h4><i class="fa fa-clipboard-list"></i> Долоо хоногийн хураангуй</h4>
                        <textarea name="summary" class="summary-input" placeholder="Долоо хоногийн нийт ажлын хураангуй бичнэ үү..."><?= $current_report['summary'] ?? '' ?></textarea>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" name="action" value="save" class="btn btn-save">
                            <i class="fa fa-save"></i> Ноорог болгон хадгалах
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-submit">
                            <i class="fa fa-paper-plane"></i> Админд илгээх
                        </button>
                    </div>
                </form>
            </div>

            <!-- Өмнөх тайлангууд -->
            <div class="reports-list">
                <h3><i class="fa fa-history"></i> Миний тайлангууд</h3>

                <?php if (!empty($user_reports)): ?>
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
                            <?php foreach ($user_reports as $report): ?>
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
                                        <a href="weekly-report-view.php?id=<?= $report['id'] ?>" class="btn btn-save" style="padding: 5px 10px; font-size: 12px;">
                                            <i class="fa fa-eye"></i> Харах
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa fa-clipboard-list"></i>
                        <h4>Одоогоор тайлан байхгүй байна</h4>
                        <p>Дээрх маягтаар шинэ тайлан үүсгэнэ үү</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
