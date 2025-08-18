<?php
// Script to create messages table if it doesn't exist
include "DB_connection.php";

try {
    // Check if messages table exists
    $check_table = $conn->query("SHOW TABLES LIKE 'messages'");
    
    if ($check_table->rowCount() == 0) {
        echo "Messages table does not exist. Creating...\n";
        
        // Create messages table
        $sql = "CREATE TABLE `messages` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sender_id` int(11) NOT NULL,
          `receiver_id` int(11) NOT NULL,
          `message` text NOT NULL,
          `is_read` tinyint(1) DEFAULT 0,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `sender_id` (`sender_id`),
          KEY `receiver_id` (`receiver_id`),
          CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
          CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "Messages table created successfully!\n";
        
        // Insert a test message
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (1, 2, 'Сайн байна уу? Чат систем ажиллаж байна.')");
        $stmt->execute();
        echo "Test message inserted!\n";
        
    } else {
        echo "Messages table already exists.\n";
        
        // Check messages count
        $count_result = $conn->query("SELECT COUNT(*) as count FROM messages");
        $count = $count_result->fetch()['count'];
        echo "Current messages count: " . $count . "\n";
    }
    
    // Show table structure
    echo "\nMessages table structure:\n";
    $structure = $conn->query("DESCRIBE messages");
    while ($row = $structure->fetch()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
