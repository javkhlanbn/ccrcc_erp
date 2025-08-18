<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";
    
    $users = get_all_users($conn);
    $selected_user = isset($_GET['user_id']) ? $_GET['user_id'] : '';
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    
    $report_data = [];
    $total_tasks = 0;
    $completed_tasks = 0;
    $overdue_tasks = 0;
    $pending_tasks = 0;
    $in_progress_tasks = 0;
    
    if ($selected_user) {
        $report_data = get_user_task_report($conn, $selected_user, $date_from, $date_to);
        
        if ($report_data && is_array($report_data)) {
            $total_tasks = count($report_data);
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
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
<title>Даалгаврын тайлан</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .report-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .total { color: #2196F3; }
        .completed { color: #4CAF50; }
        .overdue { color: #F44336; }
        .pending { color: #FF9800; }
        .in-progress { color: #9C27B0; }
        .export-buttons {
            margin: 20px 0;
        }
        .export-btn {
            background: #43aa8b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .export-btn:hover {
            background: #2d6a4f;
        }
        .filter-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .status-overdue { background-color: #ffebee; color: #c62828; }
        .status-completed { background-color: #e8f5e8; color: #2e7d32; }
        .status-pending { background-color: #fff3e0; color: #ef6c00; }
        .status-in-progress { background-color: #f3e5f5; color: #7b1fa2; }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1">
            <h4 class="title">Даалгаврын тайлан</h4>
            
            <!-- Filter Form -->
            <div class="filter-form">
                <form method="GET" action="">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Ажилтан сонгох:</label>
                            <select name="user_id" class="input-1" required>
                                <option value="">Ажилтан сонгоно уу</option>
                                <?php if ($users && is_array($users)) {
                                    foreach ($users as $user) { ?>
                                        <option value="<?=$user['id']?>" <?=$selected_user == $user['id'] ? 'selected' : ''?>>
                                            <?=$user['full_name']?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Эхлэх огноо:</label>
                            <input type="date" name="date_from" class="input-1" value="<?=$date_from?>">
                        </div>
                        <div class="filter-group">
                            <label>Дуусах огноо:</label>
                            <input type="date" name="date_to" class="input-1" value="<?=$date_to?>">
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="edit-btn">Тайлан үүсгэх</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($selected_user && $report_data !== false) { ?>
                <!-- Statistics Cards -->
                <div class="report-stats">
                    <div class="stat-card">
                        <div class="stat-number total"><?=$total_tasks?></div>
                        <div class="stat-label">Нийт даалгавар</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number completed"><?=$completed_tasks?></div>
                        <div class="stat-label">Дууссан</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number overdue"><?=$overdue_tasks?></div>
                        <div class="stat-label">Хугацаа хэтэрсэн</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number pending"><?=$pending_tasks?></div>
                        <div class="stat-label">Хүлээгдэж байгаа</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number in-progress"><?=$in_progress_tasks?></div>
                        <div class="stat-label">Хийгдэж байгаа</div>
                    </div>
                </div>

                <!-- Export Buttons -->
                <div class="export-buttons">
                    <a href="app/export-report.php?type=pdf&user_id=<?=$selected_user?>&date_from=<?=$date_from?>&date_to=<?=$date_to?>" class="export-btn">
                        <i class="fa fa-file-pdf-o"></i> PDF татах
                    </a>
                    <a href="app/export-report.php?type=excel&user_id=<?=$selected_user?>&date_from=<?=$date_from?>&date_to=<?=$date_to?>" class="export-btn">
                        <i class="fa fa-file-excel-o"></i> Excel татах
                    </a>
                    <a href="app/export-report.php?type=word&user_id=<?=$selected_user?>&date_from=<?=$date_from?>&date_to=<?=$date_to?>" class="export-btn">
                        <i class="fa fa-file-word-o"></i> Word татах
                    </a>
                </div>

                <!-- Tasks Table -->
                <?php if ($report_data && is_array($report_data) && count($report_data) > 0) { ?>
                <table class="main-table">
                    <tr>
                        <th>#</th>
                        <th>Гарчиг</th>
                        <th>Тайлбар</th>
                        <th>Дуусах огноо</th>
                        <th>Төлөв</th>
                        <th>Үүсгэсэн огноо</th>
                        <th>Статус</th>
                    </tr>
                    <?php $i = 0; foreach ($report_data as $task) { 
                        $status_class = "";
                        $status_text = "";
                        
                        if ($task['status'] == 'completed') {
                            $status_class = "status-completed";
                            $status_text = "Хугацаанд дууссан";
                        } elseif (!empty($task['due_date'])) {
                            $today = new DateTime();
                            $due_date = new DateTime($task['due_date']);
                            if ($due_date < $today) {
                                $status_class = "status-overdue";
                                $status_text = "Хугацаа хэтэрсэн";
                            } elseif ($task['status'] == 'pending') {
                                $status_class = "status-pending";
                                $status_text = "Хүлээгдэж байгаа";
                            } elseif ($task['status'] == 'in_progress') {
                                $status_class = "status-in-progress";
                                $status_text = "Хийгдэж байгаа";
                            }
                        } else {
                            if ($task['status'] == 'pending') {
                                $status_class = "status-pending";
                                $status_text = "Хүлээгдэж байгаа";
                            } elseif ($task['status'] == 'in_progress') {
                                $status_class = "status-in-progress";
                                $status_text = "Хийгдэж байгаа";
                            }
                        }
                    ?>
                    <tr class="<?=$status_class?>">
                        <td><?=++$i?></td>
                        <td><?=$task['title']?></td>
                        <td><?=$task['description']?></td>
                        <td><?=$task['due_date'] ? $task['due_date'] : 'Хугацаагүй'?></td>
                        <td><?=$task['status']?></td>
                        <td><?=date('Y-m-d', strtotime($task['created_at']))?></td>
                        <td><?=$status_text?></td>
                    </tr>
                    <?php } ?>
                </table>
                <?php } else { ?>
                    <p>Сонгосон хугацаанд ямар нэгэн даалгавар олдсонгүй.</p>
                <?php } ?>
            <?php } ?>
            
        </section>
    </div>

<script type="text/javascript">
    var active = document.querySelector("#navList li:nth-child(7)"); // Task Reports is 7th item in admin nav
    if(active) active.classList.add("active");
</script>
</body>
</html>
<?php }else{ 
   $em = "Анх удаа нэвтэрч байна";
   header("Location: login.php?error=$em");
   exit();
}
?>
