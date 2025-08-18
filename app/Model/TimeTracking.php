<?php

// Time Tracking Model Functions

function start_work_time($conn, $user_id) {
    $today = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Өнөөдөр аль хэдийн эхлүүлсэн эсэхийг шалгах
    $check_sql = "SELECT id, status FROM time_entries WHERE user_id = ? AND date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$user_id, $today]);
    $existing = $check_stmt->fetch();
    
    if ($existing) {
        if ($existing['status'] == 'in_progress') {
            return ['success' => false, 'message' => 'Таны ажлын цаг аль хэдийн эхэлсэн байна!'];
        } else {
            // Дахин эхлүүлэх
            $update_sql = "UPDATE time_entries SET start_time = ?, status = 'in_progress', updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt->execute([$current_time, $existing['id']])) {
                return ['success' => true, 'message' => 'Ажлын цаг амжилттай эхэллээ!'];
            }
        }
    } else {
        // Шинэ бүртгэл үүсгэх
        $insert_sql = "INSERT INTO time_entries (user_id, date, start_time, status) VALUES (?, ?, ?, 'in_progress')";
        $insert_stmt = $conn->prepare($insert_sql);
        if ($insert_stmt->execute([$user_id, $today, $current_time])) {
            return ['success' => true, 'message' => 'Ажлын цаг амжилттай эхэллээ!'];
        }
    }
    
    return ['success' => false, 'message' => 'Алдаа гарлаа. Дахин оролдоно уу.'];
}

function end_work_time($conn, $user_id) {
    $today = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Өнөөдрийн ажлын цагийг олох
    $sql = "SELECT id, start_time, status FROM time_entries WHERE user_id = ? AND date = ? AND status = 'in_progress'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $today]);
    $entry = $stmt->fetch();
    
    if (!$entry) {
        return ['success' => false, 'message' => 'Та өнөөдөр ажлын цаг эхлүүлээгүй байна!'];
    }
    
    // Нийт ажилласан цагийг тооцох
    $start_datetime = new DateTime($today . ' ' . $entry['start_time']);
    $end_datetime = new DateTime($today . ' ' . $current_time);
    $interval = $start_datetime->diff($end_datetime);
    $total_hours = $interval->h + ($interval->i / 60);
    
    // Илүү цагийг тооцох (8 цагаас дээш)
    $overtime = max(0, $total_hours - 8);
    
    // Бүртгэлийг шинэчлэх
    $update_sql = "UPDATE time_entries SET end_time = ?, total_hours = ?, overtime_hours = ?, status = 'completed', updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    if ($update_stmt->execute([$current_time, $total_hours, $overtime, $entry['id']])) {
        return [
            'success' => true, 
            'message' => 'Ажлын цаг амжилттай дууслаа!',
            'total_hours' => round($total_hours, 2),
            'overtime' => round($overtime, 2)
        ];
    }
    
    return ['success' => false, 'message' => 'Алдаа гарлаа. Дахин оролдоно уу.'];
}

function get_today_time_status($conn, $user_id) {
    $today = date('Y-m-d');
    
    $sql = "SELECT * FROM time_entries WHERE user_id = ? AND date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $today]);
    $entry = $stmt->fetch();
    
    if (!$entry) {
        return ['status' => 'not_started', 'entry' => null];
    }
    
    return ['status' => $entry['status'], 'entry' => $entry];
}

function get_user_time_summary($conn, $user_id, $period = 'today') {
    $today = date('Y-m-d');
    $conditions = [];
    $params = [$user_id];
    
    switch ($period) {
        case 'today':
            $conditions[] = "date = ?";
            $params[] = $today;
            break;
        case 'week':
            $conditions[] = "date >= DATE_SUB(?, INTERVAL 7 DAY)";
            $params[] = $today;
            break;
        case 'month':
            $conditions[] = "date >= DATE_SUB(?, INTERVAL 30 DAY)";
            $params[] = $today;
            break;
    }
    
    $where_clause = !empty($conditions) ? "AND " . implode(" AND ", $conditions) : "";
    
    $sql = "SELECT 
                COUNT(*) as days_worked,
                SUM(total_hours) as total_hours,
                SUM(overtime_hours) as total_overtime,
                AVG(total_hours) as avg_hours_per_day,
                MAX(total_hours) as max_hours,
                MIN(total_hours) as min_hours
            FROM time_entries 
            WHERE user_id = ? AND status = 'completed' $where_clause";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $summary = $stmt->fetch();
    
    // Өдөр тутмын дэлгэрэнгүй мэдээлэл
    $detail_sql = "SELECT date, start_time, end_time, total_hours, overtime_hours, status, notes 
                   FROM time_entries 
                   WHERE user_id = ? $where_clause 
                   ORDER BY date DESC";
    
    $detail_stmt = $conn->prepare($detail_sql);
    $detail_stmt->execute($params);
    $details = $detail_stmt->fetchAll();
    
    return ['summary' => $summary, 'details' => $details];
}

function get_all_users_time_report($conn, $date_from = null, $date_to = null) {
    $today = date('Y-m-d');
    $conditions = [];
    $params = [];
    
    if ($date_from) {
        $conditions[] = "te.date >= ?";
        $params[] = $date_from;
    }
    if ($date_to) {
        $conditions[] = "te.date <= ?";
        $params[] = $date_to;
    }
    
    // Add role condition
    $conditions[] = "u.role = 'employee'";
    
    $where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "WHERE u.role = 'employee'";
    
    $sql = "SELECT 
                u.id,
                u.full_name,
                u.username,
                COUNT(te.id) as days_worked,
                SUM(te.total_hours) as total_hours,
                SUM(te.overtime_hours) as total_overtime,
                AVG(te.total_hours) as avg_hours_per_day,
                MAX(te.total_hours) as max_hours,
                MIN(te.total_hours) as min_hours
            FROM users u
            LEFT JOIN time_entries te ON u.id = te.user_id AND te.status = 'completed'
            $where_clause
            GROUP BY u.id, u.full_name, u.username
            ORDER BY total_hours DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

function update_time_entry_manual($conn, $entry_id, $start_time, $end_time, $notes, $edited_by) {
    // Нийт цагийг тооцох
    $start_datetime = new DateTime($start_time);
    $end_datetime = new DateTime($end_time);
    $interval = $start_datetime->diff($end_datetime);
    $total_hours = $interval->h + ($interval->i / 60);
    $overtime = max(0, $total_hours - 8);
    
    $sql = "UPDATE time_entries 
            SET start_time = TIME(?), 
                end_time = TIME(?), 
                total_hours = ?, 
                overtime_hours = ?,
                notes = ?,
                is_manual = TRUE,
                edited_by = ?,
                updated_at = NOW()
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    return $stmt->execute([
        $start_time, $end_time, $total_hours, $overtime, 
        $notes, $edited_by, $entry_id
    ]);
}

function get_time_entry_by_id($conn, $entry_id) {
    $sql = "SELECT te.*, u.full_name as user_name 
            FROM time_entries te
            JOIN users u ON te.user_id = u.id
            WHERE te.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$entry_id]);
    
    return $stmt->fetch();
}

function delete_time_entry($conn, $entry_id) {
    $sql = "DELETE FROM time_entries WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$entry_id]);
}

?>
