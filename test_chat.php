<?php
// Test chat functionality
include "DB_connection.php";

try {
    // Insert some test messages
    $test_messages = [
        [1, 2, "Сайн байна уу? Энэ бол туршилтын зурвас."],
        [2, 1, "Сайн байна! Чат систем сайхан ажиллаж байна."],
        [1, 2, "Маш сайн! Өөр функцуудыг шалгая."]
    ];
    
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    
    foreach ($test_messages as $msg) {
        $stmt->execute($msg);
        echo "Inserted message: " . $msg[2] . "\n";
    }
    
    echo "\nTest messages inserted successfully!\n";
    
    // Check messages count
    $count_result = $conn->query("SELECT COUNT(*) as count FROM messages");
    $count = $count_result->fetch()['count'];
    echo "Total messages in database: " . $count . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
