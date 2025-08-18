<?php 
// Debug mode - Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
echo "<h3>Debug Info:</h3>";
echo "<p>Session ID: " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'NOT SET') . "</p>";
echo "<p>Session Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET') . "</p>";

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    echo "<p style='color: red;'>Session хоосон байна - login хийх хэрэгтэй</p>";
    echo "<a href='login.php'>Login хуудас руу очих</a><br>";
    echo "<a href='test-employee-login.php'>Test employee session үүсгэх</a>";
    exit();
}

try {
    include "DB_connection.php";
    echo "<p style='color: green;'>Database холболт амжилттай</p>";
    
    include "app/Model/TimeTracking.php";
    echo "<p style='color: green;'>TimeTracking model амжилттай</p>";
    
    include "app/Model/User.php";
    echo "<p style='color: green;'>User model амжилттай</p>";

    $user_id = $_SESSION['id'];
    $user_role = $_SESSION['role'];
    
    echo "<p>User ID: $user_id, Role: $user_role</p>";

    // Өнөөдрийн цагийн төлөвийг авах
    $today_status = get_today_time_status($conn, $user_id);
    echo "<p style='color: blue;'>Today status: " . print_r($today_status, true) . "</p>";

    // Хураангуй мэдээлэл авах
    $today_summary = get_user_time_summary($conn, $user_id, 'today');
    echo "<p style='color: blue;'>Today summary: " . print_r($today_summary, true) . "</p>";
    
    $week_summary = get_user_time_summary($conn, $user_id, 'week');
    $month_summary = get_user_time_summary($conn, $user_id, 'month');
    
    echo "<h3>Бүгд ажиллаж байна! Үндсэн хуудас руу очино уу...</h3>";
    echo "<a href='time-tracking.php'>Time Tracking хуудас</a>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Алдаа: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}
?>
