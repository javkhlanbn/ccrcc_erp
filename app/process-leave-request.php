<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {

if (isset($_POST['request_id']) && isset($_POST['action']) && isset($_POST['admin_reason'])) {
    include "../DB_connection.php";
    include "Model/LeaveRequest.php";

    function validate_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $request_id = validate_input($_POST['request_id']);
    $action = validate_input($_POST['action']);
    $admin_reason = validate_input($_POST['admin_reason']);
    
    if (empty($admin_reason)) {
        $em = "Шалтгаан оруулна уу";
        header("Location: ../manage-leave-requests.php?error=$em");
        exit();
    }
    
    if ($action === 'approve') {
        $status = 'approved';
        $message = "Чөлөөний хүсэлт зөвшөөрөгдлөө";
    } elseif ($action === 'reject') {
        $status = 'rejected';
        $message = "Чөлөөний хүсэлт татгалзлаа";
    } else {
        $em = "Буруу үйлдэл";
        header("Location: ../manage-leave-requests.php?error=$em");
        exit();
    }
    
    // Update the request status with admin reason
    update_leave_request_with_reason($conn, $request_id, $status, $admin_reason);

    header("Location: ../manage-leave-requests.php?success=$message");
    exit();
    
}else {
   $em = "Бүх талбаруудыг бөглөнө үү";
   header("Location: ../manage-leave-requests.php?error=$em");
   exit();
}

}else{ 
   $em = "Эрх хүрэхгүй";
   header("Location: ../manage-leave-requests.php?error=$em");
   exit();
}
?>
