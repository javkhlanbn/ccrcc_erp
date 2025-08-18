<?php
session_start();

// Debug purpose - employee session үүсгэх
$_SESSION['id'] = 17;
$_SESSION['role'] = 'employee';
$_SESSION['username'] = 'anujin';
$_SESSION['full_name'] = 'Anujin';

header("Location: time-tracking.php");
exit();
?>
