<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {

if (isset($_GET['id'])) {
    include "../DB_connection.php";
    include "Model/LeaveRequest.php";

    $request_id = $_GET['id'];
    
    // Update the request status to approved
    update_leave_request_status($conn, $request_id, 'approved', 'Зөвшөөрөгдлөө');

    $em = "Чөлөөний хүсэлт зөвшөөрөгдлөө";
    header("Location: ../manage-leave-requests.php?success=$em");
    exit();
    
}else {
   $em = "Алдаа гарлаа";
   header("Location: ../manage-leave-requests.php?error=$em");
   exit();
}

}else{ 
   $em = "Эрх хүрэхгүй";
   header("Location: ../manage-leave-requests.php?error=$em");
   exit();
}
?>
