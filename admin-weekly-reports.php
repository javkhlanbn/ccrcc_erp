<?php 
session_start();
// Монголын цагийн бүс тохируулах
date_default_timezone_set('Asia/Ulaanbaatar');

if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    $em = "Зөвхөн админ хандах боломжтой";
    header("Location: login.php?error=$em");
    exit();
}

include "DB_connection.php";
include "app/Model/WeeklyReport.php";
include "app/Model/User.php";

$status_filter = $_GET['status'] ?? 'submitted';
$reports = get_all_weekly_reports($conn, $status_filter);

?>
<!DOCTYPE html>
<html>
<head>
    <title>7 хоногийн тайлангууд - Админ</title>
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
        
        .admin-reports-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .filter-tab.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .filter-tab:hover {
            background: #555;
            color: white;
        }
        
        .reports-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .data-table tr:hover {
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
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-view {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-review {
            background-color: #28a745;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 5px solid;
        }
        
        .stat-card.submitted {
            border-left-color: #2196F3;
        }
        
        .stat-card.reviewed {
            border-left-color: #4CAF50;
        }
        
        .stat-card.pending {
            border-left-color: #FF9800;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body class="body">
    <?php include "inc/nav.php"; ?>
    
    <div class="main-content">
        <div class="admin-reports-container">
            
            <div class="admin-header">
                <h1><i class="fa fa-clipboard-list"></i> 7 хоногийн тайлангууд</h1>
                <p>Ажилчдын 7 хоногийн тайлангуудыг энд хянаж, санал хүсэлт өгөх боломжтой</p>
            </div>
            
            <!-- Статистик -->
            <div class="stats-overview">
                <?php 
                $all_reports = get_all_weekly_reports($conn);
                $submitted_count = count(array_filter($all_reports, function($r) { return $r['status'] == 'submitted'; }));
                $reviewed_count = count(array_filter($all_reports, function($r) { return $r['status'] == 'reviewed'; }));
                $total_count = count($all_reports);
                ?>
                
                <div class="stat-card submitted">
                    <div class="stat-value"><?= $submitted_count ?></div>
                    <div class="stat-label">Шинэ тайлан</div>
                </div>
                
                <div class="stat-card reviewed">
                    <div class="stat-value"><?= $reviewed_count ?></div>
                    <div class="stat-label">Хянагдсан</div>
                </div>
                
                <div class="stat-card pending">
                    <div class="stat-value"><?= $total_count ?></div>
                    <div class="stat-label">Нийт тайлан</div>
                </div>
            </div>
            
            <!-- Шүүлтүүр -->
            <div class="filter-section">
                <h3><i class="fa fa-filter"></i> Шүүлтүүр</h3>
                <div class="filter-tabs">
                    <a href="?status=submitted" class="filter-tab <?= $status_filter == 'submitted' ? 'active' : '' ?>">
                        <i class="fa fa-paper-plane"></i> Шинэ тайлангууд
                    </a>
                    <a href="?status=reviewed" class="filter-tab <?= $status_filter == 'reviewed' ? 'active' : '' ?>">
                        <i class="fa fa-check"></i> Хянагдсан
                    </a>
                    <a href="?" class="filter-tab <?= empty($status_filter) || $status_filter == 'all' ? 'active' : '' ?>">
                        <i class="fa fa-list"></i> Бүгд
                    </a>
                </div>
            </div>
            
            <!-- Тайлангууд -->
            <?php if (!empty($reports)): ?>
                <div class="reports-table">
                    <div class="table-header">
                        <h3><i class="fa fa-table"></i> Тайлангуудын жагсаалт (<?= count($reports) ?> тайлан)</h3>
                        <div style="margin-top:10px;">
                            <a href="app/export-weekly-reports.php?type=excel<?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?>" class="btn-sm" style="background:#2196F3;color:white;">
                                <i class="fa fa-file-excel"></i> Excel татах
                            </a>
                            <a href="app/export-weekly-reports.php?type=word<?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?>" class="btn-sm" style="background:#4CAF50;color:white;">
                                <i class="fa fa-file-word"></i> Word татах
                            </a>
                        </div>
                    </div>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ажилчин</th>
                                <th>Долоо хоног</th>
                                <th>Статус</th>
                                <th>Илгээсэн огноо</th>
                                <th>Хянасан</th>
                                <th>Үйлдэл</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $index => $report): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($report['employee_name']) ?></strong>
                                        <br>
                                        <small>@<?= htmlspecialchars($report['employee_username']) ?></small>
                                    </td>
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
                                        <div class="action-buttons">
                                            <a href="weekly-report-detail.php?id=<?= $report['id'] ?>" 
                                               class="btn-sm btn-view" title="Дэлгэрэнгүй харах">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <?php if ($report['status'] == 'submitted'): ?>
                                                <a href="weekly-report-review.php?id=<?= $report['id'] ?>" 
                                                   class="btn-sm btn-review" title="Хянах">
                                                    <i class="fa fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa fa-clipboard-list"></i>
                    <h3>Тайлан олдсонгүй</h3>
                    <p>Сонгосон шүүлтэнд тайлан байхгүй байна.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
