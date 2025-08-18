<?php 

function send_message($conn, $sender_id, $receiver_id, $message){
    $sql = "INSERT INTO messages (sender_id, receiver_id, message, message_type) VALUES(?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$sender_id, $receiver_id, $message, 'text']);
    return $stmt->rowCount() > 0;
}

function send_file_message($conn, $sender_id, $receiver_id, $message, $filename, $file_type, $file_size, $original_filename, $message_type){
    $sql = "INSERT INTO messages (sender_id, receiver_id, message, file_name, file_type, file_size, original_filename, message_type) VALUES(?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$sender_id, $receiver_id, $message, $filename, $file_type, $file_size, $original_filename, $message_type]);
    return $stmt->rowCount() > 0;
}

function get_messages($conn, $user1_id, $user2_id, $limit = 50){
    $sql = "SELECT m.*, 
                   s.full_name as sender_name, 
                   r.full_name as receiver_name 
            FROM messages m 
            JOIN users s ON m.sender_id = s.id 
            JOIN users r ON m.receiver_id = r.id 
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?) 
            ORDER BY m.created_at ASC 
            LIMIT " . intval($limit);
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user1_id, $user2_id, $user2_id, $user1_id]);

    if($stmt->rowCount() > 0){
        $messages = $stmt->fetchAll();
    }else $messages = [];

    return $messages;
}

function mark_messages_as_read($conn, $sender_id, $receiver_id){
    $sql = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$sender_id, $receiver_id]);
    return $stmt->rowCount();
}

function get_unread_message_count($conn, $user_id){
    $sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    
    $result = $stmt->fetch();
    return $result ? $result['count'] : 0;
}

function get_chat_participants($conn, $user_id){
    $sql = "SELECT DISTINCT 
                CASE 
                    WHEN sender_id = ? THEN receiver_id 
                    ELSE sender_id 
                END as participant_id,
                u.full_name,
                u.role,
                COUNT(CASE WHEN receiver_id = ? AND is_read = 0 THEN 1 END) as unread_count,
                MAX(m.created_at) as last_message_time
            FROM messages m 
            JOIN users u ON (u.id = CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END)
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY participant_id, u.full_name, u.role
            ORDER BY last_message_time DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);

    if($stmt->rowCount() > 0){
        $participants = $stmt->fetchAll();
    }else $participants = [];

    return $participants;
}

function get_latest_message($conn, $user1_id, $user2_id){
    $sql = "SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?) 
            ORDER BY created_at DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user1_id, $user2_id, $user2_id, $user1_id]);

    if($stmt->rowCount() > 0){
        $message = $stmt->fetch();
    }else $message = null;

    return $message;
}
