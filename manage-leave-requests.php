<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/LeaveRequest.php";
    
    $leave_requests = get_all_leave_requests($conn);
?>
<!DOCTYPE html>
<html>
<head>
<title>Чөлөөний хүсэлтүүд удирдах</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .main-table td, .main-table th { padding: 16px 10px; }
        .approve-btn { background: #28a745; color: #fff; padding: 5px 12px; border-radius: 4px; text-decoration: none; margin-right: 5px; border: none; cursor: pointer; }
        .reject-btn { background: #dc3545; color: #fff; padding: 5px 12px; border-radius: 4px; text-decoration: none; border: none; cursor: pointer; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border-radius: 8px; width: 400px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: black; }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1">
            <h4 class="title">Чөлөөний хүсэлтүүд удирдах</h4>
            
            <?php if (isset($_GET['success'])) { ?>
                <div class="success" role="alert">
                    <?php echo stripcslashes($_GET['success']); ?>
                </div>
            <?php } ?>

            <?php if (isset($_GET['error'])) { ?>
                <div class="danger" role="alert">
                    <?php echo stripcslashes($_GET['error']); ?>
                </div>
            <?php } ?>
            
            <?php if ($leave_requests != 0 && is_array($leave_requests)) { ?>
            <table class="main-table">
                <tr>
                    <th>#</th>
                    <th>Ажилтан</th>
                    <th>Төрөл</th>
                    <th>Эхлэх огноо</th>
                    <th>Дуусах огноо</th>
                    <th>Цаг</th>
                    <th>Шалтгаан</th>
                    <th>Төлөв</th>
                    <th>Илгээсэн огноо</th>
                    <th>Үйлдэл</th>
                </tr>
                <?php $i = 0; foreach ($leave_requests as $request) { ?>
                <tr class="status-<?=$request['status']?>">
                    <td><?=++$i?></td>
                    <td><?=$request['employee_name']?></td>
                    <td>
                        <?php 
                        if($request['leave_type'] == 'full_day') echo 'Бүтэн өдөр';
                        elseif($request['leave_type'] == 'half_day') echo 'Хагас өдөр';
                        elseif($request['leave_type'] == 'hourly') echo 'Цагаар';
                        else echo 'Бүтэн өдөр'; // fallback for old records
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
                    <td><?=date('Y-m-d H:i', strtotime($request['created_at']))?></td>
                    <td>
                        <?php if($request['status'] == 'pending') { ?>
                            <button class="approve-btn" onclick="showApprovalModal(<?=$request['id']?>, 'approve')">Зөвшөөрөх</button>
                            <button class="reject-btn" onclick="showApprovalModal(<?=$request['id']?>, 'reject')">Татгалзах</button>
                        <?php } else { ?>
                            <span style="color: #666;">Шийдэгдсэн</span>
                            <?php if($request['admin_reason']) { ?>
                                <br><small style="color: #666;">Шалтгаан: <?=$request['admin_reason']?></small>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <?php } else { ?>
                <p>Одоогоор ямар нэгэн чөлөөний хүсэлт байхгүй байна.</p>
            <?php } ?>
            
        </section>
    </div>

<!-- Modal for approval/rejection with reason -->
<div id="approvalModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Шийдвэр</h3>
        <form id="approvalForm" method="POST" action="app/process-leave-request.php">
            <input type="hidden" id="requestId" name="request_id">
            <input type="hidden" id="actionType" name="action">
            
            <div class="input-holder">
                <label>Шалтгаан:</label>
                <textarea name="admin_reason" class="input-1" rows="3" placeholder="Яагаад зөвшөөрсөн/татгалзсан шалтгаанаа бичнэ үү..." required></textarea>
            </div>
            
            <button type="submit" class="edit-btn" id="submitBtn">Батлах</button>
            <button type="button" onclick="closeModal()" style="background: #6c757d; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; margin-left: 10px;">Цуцлах</button>
        </form>
    </div>
</div>

<script type="text/javascript">
    // Set active nav item for leave requests management
    var active = document.querySelector("#navList li:nth-child(5)"); // Adjust based on nav position
    if(active) active.classList.add("active");

    function showApprovalModal(requestId, action) {
        document.getElementById('requestId').value = requestId;
        document.getElementById('actionType').value = action;
        
        if(action === 'approve') {
            document.getElementById('modalTitle').textContent = 'Чөлөөний хүсэлт зөвшөөрөх';
            document.getElementById('submitBtn').textContent = 'Зөвшөөрөх';
            document.getElementById('submitBtn').style.background = '#28a745';
        } else {
            document.getElementById('modalTitle').textContent = 'Чөлөөний хүсэлт татгалзах';
            document.getElementById('submitBtn').textContent = 'Татгалзах';
            document.getElementById('submitBtn').style.background = '#dc3545';
        }
        
        document.getElementById('approvalModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('approvalModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('approvalModal');
        if (event.target == modal) {
            modal.style.display = 'none';
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
