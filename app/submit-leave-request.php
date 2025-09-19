<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "employee") {

if (isset($_POST['start_date']) && isset($_POST['reason']) && isset($_POST['leave_type'])) {
    include "../DB_connection.php";
    include "Model/LeaveRequest.php";



    function validate_input($data) {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
    }

    $leave_type = validate_input($_POST['leave_type']);
    $start_date = validate_input($_POST['start_date']);
    $end_date = !empty($_POST['end_date']) ? validate_input($_POST['end_date']) : null;
    $start_time = !empty($_POST['start_time']) ? validate_input($_POST['start_time']) : null;
    $end_time = !empty($_POST['end_time']) ? validate_input($_POST['end_time']) : null;
    $reason = validate_input($_POST['reason']);
    $employee_id = $_SESSION['id'];

    if (empty($start_date)) {
        $em = "Эхлэх огноо оруулна уу";
        header("Location: ../leave-request.php?error=$em");
        exit();
    }else if (empty($reason)) {
        $em = "Шалтгаан оруулна уу";
        header("Location: ../leave-request.php?error=$em");
        exit();
    }else if ($leave_type == 'hourly' && (empty($start_time) || empty($end_time))) {
        $em = "Цагийн мэдээлэл оруулна уу";
        header("Location: ../leave-request.php?error=$em");
        exit();
    }else if ($leave_type == 'full_day' && !empty($end_date) && $start_date > $end_date) {
        $em = "Эхлэх огноо дуусах огнооноос өмнө байх ёстой";
        header("Location: ../leave-request.php?error=$em");
        exit();
    }else {
        
        // Set end_date to start_date for half_day and hourly requests
        if ($leave_type == 'half_day' || $leave_type == 'hourly') {
            $end_date = $start_date;
        }
        
        $data = array($employee_id, $leave_type, $start_date, $end_date, $start_time, $end_time, $reason);
        try {
            submit_leave_request_extended($conn, $data);
            $em = "Чөлөөний хүсэлт амжилттай илгээгдлээ";
            header("Location: ../leave-request.php?success=$em");
            exit();
        } catch (Exception $e) {
            // Log the error
            error_log("Leave request submission error: " . $e->getMessage(), 3, "../logs/error.log");
            $em = "Алдаа гарлаа: " . $e->getMessage();
            header("Location: ../leave-request.php?error=$em");
            exit();
        }
    }
}else {
   $em = "Бүх талбаруудыг бөглөнө үү";
   header("Location: ../leave-request.php?error=$em");
   exit();
}

}else{ 
   $em = "Эрх хүрэхгүй";
   header("Location: ../leave-request.php?error=$em");
   exit();
}
?>
