<?php
include "DB_connection.php";

try {
    // Add permission columns to users table
    $sql = "ALTER TABLE users
            ADD COLUMN can_view_all_time TINYINT(1) NOT NULL DEFAULT 0,
            ADD COLUMN can_download_reports TINYINT(1) NOT NULL DEFAULT 0";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Update existing admin users to have all permissions by default
    $sql2 = "UPDATE users SET can_view_all_time = 1, can_download_reports = 1 WHERE role = 'admin'";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute();

    echo "Migration completed successfully!";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
