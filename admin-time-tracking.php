<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    $em = "Зөвхөн админ хандах боломжтой";
    header("Location: login.php?error=$em");
    exit();
}

include "DB_connection.php";
include "app/Model/TimeTracking.php";
include "app/Model/User.php";

$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$user_filter = $_GET['user_filter'] ?? '';

// Бүх ажилчдын цагийн тайлан авах
$time_reports = get_all_users_time_report($conn, $date_from, $date_to);

// Хэрэв хэрэглэгчийн шүүлтүүр байгаа бол
if ($user_filter) {
    $time_reports = array_filter($time_reports, function($report) use ($user_filter) {
        return stripos($report['full_name'], $user_filter) !== false;
    });
}

// Бүх ажилчдын жагсаалт dropdown-д ашиглах
$all_employees = get_all_users($conn);
$employees = array_filter($all_employees, function($user) {
    return $user['role'] == 'employee';
});

?>
<!DOCTYPE html>
<html>
<head>
    <title>Цагийн удирдлага - Админ</title>
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
        
        .admin-time-container {
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
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filter-btn {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .export-btn {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
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
        
        .stat-card.total {
            border-left-color: #4CAF50;
        }
        
        .stat-card.average {
            border-left-color: #2196F3;
        }
        
        .stat-card.overtime {
            border-left-color: #FF9800;
        }
        
        .stat-card.top-performer {
            border-left-color: #9C27B0;
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
        
        .time-report-table {
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
        
        .table-content {
            overflow-x: auto;
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
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .performance-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .performance-excellent {
            background-color: #d4edda;
            color: #155724;
        }
        
        .performance-good {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .performance-average {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .performance-poor {
            background-color: #f8d7da;
            color: #721c24;
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
        
        .btn-edit {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ccc;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
    </style>
</head>
<body class="body">
    <?php include "inc/nav.php"; ?>
    
    <div class="main-content">
        <div class="admin-time-container">
        <div class="admin-header">
            <h1><i class="fa fa-clock-o"></i> Цагийн удирдлага</h1>
            <p>Ажилчдын өдөр тутмын цагийн мэдээллийг эндээс хянах боломжтой. Та огноо эсвэл ажилтны нэрээр хайж, тухайн ажилтны ажилласан цаг, ирцийн статусыг хянах боломжтой.</p>
        </div>
        
        <!-- Шүүлтүүр хэсэг -->
        <div class="filter-section">
            <h3><i class="fa fa-filter"></i> Шүүлтүүр</h3>
            <form class="filter-form" method="GET">
                <div class="form-group">
                    <label for="date_from">Эхлэх огноо:</label>
                    <input type="date" id="date_from" name="date_from" value="<?= $date_from ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">Дуусах огноо:</label>
                    <input type="date" id="date_to" name="date_to" value="<?= $date_to ?>">
                </div>
                
                <div class="form-group">
                    <label for="user_filter">Ажилтан:</label>
                    <input type="text" id="user_filter" name="user_filter" placeholder="Ажилтны нэр..." value="<?= htmlspecialchars($user_filter) ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="filter-btn">
                        <i class="fa fa-search"></i> Хайх
                    </button>
                </div>
                
                <div class="form-group">
                    <a href="app/export-time-report.php?date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&user_filter=<?= urlencode($user_filter) ?>&type=excel" class="export-btn">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <a href="app/export-time-report.php?date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&user_filter=<?= urlencode($user_filter) ?>&type=pdf" class="export-btn">
                        <i class="fa fa-file-pdf-o"></i> PDF
                    </a>
                </div>
            </form>
        </div>
        
        <?php if (!empty($time_reports)): ?>
            <?php
            // Статистик тооцох
            $total_employees = count($time_reports);
            $total_hours = array_sum(array_column($time_reports, 'total_hours'));
            $total_overtime = array_sum(array_column($time_reports, 'total_overtime'));
            $avg_hours = $total_hours / max($total_employees, 1);
            
            // Хамгийн их ажилласан ажилчин
            $top_performer = array_reduce($time_reports, function($carry, $item) {
                return ($item['total_hours'] > ($carry['total_hours'] ?? 0)) ? $item : $carry;
            }, []);
            ?>
            
            <!-- Статистикийн хэсэг -->
            <div class="stats-overview">
                <div class="stat-card total">
                    <div class="stat-value"><?= number_format($total_hours, 1) ?></div>
                    <div class="stat-label">Нийт ажилласан цаг</div>
                </div>
                
                <div class="stat-card average">
                    <div class="stat-value"><?= number_format($avg_hours, 1) ?></div>
                    <div class="stat-label">Ажилтны дундаж цаг</div>
                </div>
                
                <div class="stat-card overtime">
                    <div class="stat-value"><?= number_format($total_overtime, 1) ?></div>
                    <div class="stat-label">Нийт илүү цаг</div>
                </div>
                
                <div class="stat-card top-performer">
                    <div class="stat-value"><?= $top_performer['full_name'] ?? 'Байхгүй' ?></div>
                    <div class="stat-label">Хамгийн их ажилласан</div>
                </div>
            </div>
            
            <!-- Ажилчдын цагийн тайлан -->
            <div class="time-report-table">
                <div class="table-header">
                    <h3><i class="fa fa-users"></i> Ажилчдын цагийн мэдээлэл (<?= $total_employees ?> ажилчин)</h3>
                </div>
                
                <div class="table-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ажилчин</th>
                                <th>Хэрэглэгчийн нэр</th>
                                <th>Ажилласан өдөр</th>
                                <th>Нийт цаг</th>
                                <th>Дундаж өдөр</th>
                                <th>Илүү цаг</th>
                                <th>Максимум</th>
                                <th>Минимум</th>
                                <th>Гүйцэтгэл</th>
                                <th>Үйлдэл</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($time_reports as $index => $report): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($report['full_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($report['username']) ?></td>
                                    <td><?= $report['days_worked'] ?: '0' ?> өдөр</td>
                                    <td><?= number_format($report['total_hours'], 1) ?> цаг</td>
                                    <td><?= number_format($report['avg_hours_per_day'], 1) ?> цаг</td>
                                    <td>
                                        <?php if ($report['total_overtime'] > 0): ?>
                                            <span style="color: #FF9800; font-weight: bold;">
                                                <?= number_format($report['total_overtime'], 1) ?> цаг
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $report['max_hours'] ? number_format($report['max_hours'], 1) . ' цаг' : '-' ?></td>
                                    <td><?= $report['min_hours'] ? number_format($report['min_hours'], 1) . ' цаг' : '-' ?></td>
                                    <td>
                                        <?php
                                        $performance = '';
                                        $avg_daily = $report['avg_hours_per_day'];
                                        if ($avg_daily >= 8) {
                                            $performance = '<span class="performance-badge performance-excellent">Маш сайн</span>';
                                        } elseif ($avg_daily >= 7) {
                                            $performance = '<span class="performance-badge performance-good">Сайн</span>';
                                        } elseif ($avg_daily >= 6) {
                                            $performance = '<span class="performance-badge performance-average">Дундаж</span>';
                                        } else {
                                            $performance = '<span class="performance-badge performance-poor">Хангалтгүй</span>';
                                        }
                                        echo $performance;
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="time-tracking-detail.php?user_id=<?= $report['id'] ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" 
                                               class="btn-sm btn-edit" title="Дэлгэрэнгүй харах">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <button onclick="editUser(<?= $report['id'] ?>)" 
                                                    class="btn-sm btn-edit" title="Засах">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-clock-o"></i>
                <h3>Цагийн бүртгэл олдсонгүй</h3>
                <p>Сонгосон хугацаанд цагийн бүртгэл байхгүй байна.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Цагийн бүртгэл засах</h3>
            <div id="editForm">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
        </div>
    </div>
    
    <script>
        function editUser(userId) {
            // TODO: Хэрэглэгчийн цагийн бүртгэлийг засах modal харуулах
            alert('Цагийн бүртгэл засах функц удахгүй нэмэгдэнэ.');
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Modal-г гадуур дарахад хаах
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Хуудас ачааллагдахад огнооны утгыг сонгох
        document.addEventListener('DOMContentLoaded', function() {
            // Хэрэв огноо оруулаагүй бол сүүлийн 30 хоногийг харуулах
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            if (!dateFrom.value) {
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                dateFrom.value = thirtyDaysAgo.toISOString().split('T')[0];
            }
            
            if (!dateTo.value) {
                const today = new Date();
                dateTo.value = today.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
