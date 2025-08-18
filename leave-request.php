<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "employee") {
    include "DB_connection.php";
    include "app/Model/LeaveRequest.php";
    
    $employee_requests = get_employee_leave_requests($conn, $_SESSION['id']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Чөлөөний хүсэлт</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .main-table td, .main-table th { padding: 16px 10px; }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1">
            <h4 class="title">Чөлөөний хүсэлт илгээх</h4>
            
            <form class="form-1" method="POST" action="app/submit-leave-request.php">
                <?php if (isset($_GET['error'])) {?>
                <div class="danger" role="alert">
                  <?php echo stripcslashes($_GET['error']); ?>
                </div>
                <?php } ?>

                <?php if (isset($_GET['success'])) {?>
                <div class="success" role="alert">
                  <?php echo stripcslashes($_GET['success']); ?>
                </div>
                <?php } ?>
                
                <div class="input-holder">
                    <label>Чөлөөний төрөл</label>
                    <select name="leave_type" class="input-1" id="leaveType" onchange="toggleFields()" required>
                        <option value="full_day">Бүтэн өдөр</option>
                        <option value="half_day">Хагас өдөр</option>
                        <option value="hourly">Цагаар</option>
                    </select><br>
                </div>
                
                <div class="input-holder">
                    <label>Эхлэх огноо</label>
                    <input type="date" name="start_date" class="input-1" required><br>
                </div>
                
                <div class="input-holder" id="endDateField">
                    <label>Дуусах огноо</label>
                    <input type="date" name="end_date" class="input-1"><br>
                </div>
                
                <div class="input-holder" id="startTimeField" style="display: none;">
                    <label>Эхлэх цаг</label>
                    <input type="time" name="start_time" class="input-1"><br>
                </div>
                
                <div class="input-holder" id="endTimeField" style="display: none;">
                    <label>Дуусах цаг</label>
                    <input type="time" name="end_time" class="input-1"><br>
                </div>
                
                <div class="input-holder">
                    <label>Шалтгаан</label>
                    <textarea name="reason" class="input-1" placeholder="Чөлөөний шалтгаанаа бичнэ үү..." rows="4" required></textarea><br>
                </div>

                <button class="edit-btn">Хүсэлт илгээх</button>
            </form>

            <h4 class="title">Миний чөлөөний хүсэлтүүд</h4>
            
            <?php if ($employee_requests != 0 && is_array($employee_requests)) { ?>
            <table class="main-table">
                <tr>
                    <th>#</th>
                    <th>Төрөл</th>
                    <th>Эхлэх огноо</th>
                    <th>Дуусах огноо</th>
                    <th>Цаг</th>
                    <th>Шалтгаан</th>
                    <th>Төлөв</th>
                    <th>Админы тайлбар</th>
                    <th>Илгээсэн огноо</th>
                </tr>
                <?php $i = 0; foreach ($employee_requests as $request) { ?>
                <tr class="status-<?=$request['status']?>">
                    <td><?=++$i?></td>
                    <td>
                        <?php 
                        if($request['leave_type'] == 'full_day') echo 'Бүтэн өдөр';
                        elseif($request['leave_type'] == 'half_day') echo 'Хагас өдөр';
                        elseif($request['leave_type'] == 'hourly') echo 'Цагаар';
                        ?>
                    </td>
                    <td><?=$request['start_date']?></td>
                    <td><?=$request['end_date'] ?? '-'?></td>
                    <td>
                        <?php 
                        if($request['leave_type'] == 'hourly' && $request['start_time'] && $request['end_time']) {
                            echo $request['start_time'] . ' - ' . $request['end_time'];
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td><?=$request['reason']?></td>
                    <td>
                        <?php 
                        if($request['status'] == 'pending') echo 'Хүлээгдэж байна';
                        elseif($request['status'] == 'approved') echo 'Зөвшөөрөгдсөн';
                        elseif($request['status'] == 'rejected') echo 'Татгалзсан';
                        ?>
                    </td>
                    <td><?=$request['admin_comment'] ?? ($request['admin_reason'] ?? '-')?></td>
                    <td><?=date('Y-m-d H:i', strtotime($request['created_at']))?></td>
                </tr>
                <?php } ?>
            </table>
            <?php } else { ?>
                <p>Та одоогоор ямар нэгэн чөлөөний хүсэлт илгээгээгүй байна.</p>
            <?php } ?>
            
        </section>
    </div>

<script type="text/javascript">
    // Set active nav item
    var active = document.querySelector("#navList li:nth-child(5)"); // Assuming this will be 5th item
    if(active) active.classList.add("active");

    function toggleFields() {
        var leaveType = document.getElementById('leaveType').value;
        var endDateField = document.getElementById('endDateField');
        var startTimeField = document.getElementById('startTimeField');
        var endTimeField = document.getElementById('endTimeField');
        var endDateInput = document.querySelector('input[name="end_date"]');
        var startTimeInput = document.querySelector('input[name="start_time"]');
        var endTimeInput = document.querySelector('input[name="end_time"]');

        if (leaveType === 'hourly') {
            // Show time fields, hide end date
            endDateField.style.display = 'none';
            startTimeField.style.display = 'block';
            endTimeField.style.display = 'block';
            
            endDateInput.required = false;
            startTimeInput.required = true;
            endTimeInput.required = true;
        } else if (leaveType === 'half_day') {
            // Hide time fields, hide end date
            endDateField.style.display = 'none';
            startTimeField.style.display = 'none';
            endTimeField.style.display = 'none';
            
            endDateInput.required = false;
            startTimeInput.required = false;
            endTimeInput.required = false;
        } else {
            // Full day - show end date, hide time fields
            endDateField.style.display = 'block';
            startTimeField.style.display = 'none';
            endTimeField.style.display = 'none';
            
            endDateInput.required = false; // Can be same day
            startTimeInput.required = false;
            endTimeInput.required = false;
        }
    }
</script>
</body>
</html>
<?php }else{ 
   $em = "Анх удаа нэвтэрч байна";
   header("Location: login.php?error=$em");
   exit();
}
?>
