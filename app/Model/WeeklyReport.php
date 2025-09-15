<?php

// Монголын цагийн бүс тохируулах
date_default_timezone_set('Asia/Ulaanbaatar');

// Weekly Report Model Functions

function get_current_week_dates() {
    $today = new DateTime();
    $day_of_week = $today->format('N'); // 1=Monday, 7=Sunday
    
    // Даваа гаригаас эхлэх
    $monday = clone $today;
    $monday->modify('-' . ($day_of_week - 1) . ' days');
    
    $sunday = clone $monday;
    $sunday->modify('+6 days');
    
    return [
        'start' => $monday->format('Y-m-d'),
        'end' => $sunday->format('Y-m-d'),
        'week_number' => $monday->format('W')
    ];
}

function get_week_report($conn, $user_id, $week_start_date) {
    $sql = "SELECT * FROM weekly_reports 
            WHERE user_id = ? AND week_start_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $week_start_date]);
    return $stmt->fetch();
}

function save_weekly_report($conn, $user_id, $week_start_date, $week_end_date, $report_data, $summary = '') {
    // Өмнө нь байгаа эсэхийг шалгах
    $existing = get_week_report($conn, $user_id, $week_start_date);
    
    if ($existing) {
        // Шинэчлэх
        $sql = "UPDATE weekly_reports SET 
                week_end_date = ?,
                monday_work = ?,
                tuesday_work = ?,
                wednesday_work = ?,
                thursday_work = ?,
                friday_work = ?,
                saturday_work = ?,
                sunday_work = ?,
                summary = ?,
                status = 'draft',
                updated_at = NOW()
                WHERE user_id = ? AND week_start_date = ?";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            $week_end_date,
            $report_data['monday'] ?? '',
            $report_data['tuesday'] ?? '',
            $report_data['wednesday'] ?? '',
            $report_data['thursday'] ?? '',
            $report_data['friday'] ?? '',
            $report_data['saturday'] ?? '',
            $report_data['sunday'] ?? '',
            $summary,
            $user_id,
            $week_start_date
        ]);
    } else {
        // Шинээр үүсгэх
        $sql = "INSERT INTO weekly_reports 
                (user_id, week_start_date, week_end_date, monday_work, tuesday_work, 
                 wednesday_work, thursday_work, friday_work, saturday_work, sunday_work, 
                 summary, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            $user_id,
            $week_start_date,
            $week_end_date,
            $report_data['monday'] ?? '',
            $report_data['tuesday'] ?? '',
            $report_data['wednesday'] ?? '',
            $report_data['thursday'] ?? '',
            $report_data['friday'] ?? '',
            $report_data['saturday'] ?? '',
            $report_data['sunday'] ?? '',
            $summary
        ]);
    }
}

function submit_weekly_report($conn, $user_id, $week_start_date) {
    $sql = "UPDATE weekly_reports SET 
            status = 'submitted',
            submitted_at = NOW()
            WHERE user_id = ? AND week_start_date = ? AND status = 'draft'";
    
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$user_id, $week_start_date]);
}

function get_user_weekly_reports($conn, $user_id, $limit = 10) {
    $limit = (int)$limit; // Integer болгох
    $sql = "SELECT wr.*, 
                   CONCAT(DATE_FORMAT(week_start_date, '%Y-%m-%d'), ' - ', DATE_FORMAT(week_end_date, '%Y-%m-%d')) as week_range,
                   admin.full_name as reviewed_by_name
            FROM weekly_reports wr
            LEFT JOIN users admin ON wr.reviewed_by = admin.id
            WHERE wr.user_id = ?
            ORDER BY wr.week_start_date DESC
            LIMIT $limit";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_all_weekly_reports($conn, $status = null, $week_filter = null) {
    $where_clause = '';
    $params = [];
    $conditions = [];

    if ($status) {
        $conditions[] = "wr.status = ?";
        $params[] = $status;
    }

    if ($week_filter) {
        $conditions[] = "wr.week_start_date = ?";
        $params[] = $week_filter;
    }

    if (!empty($conditions)) {
        $where_clause = "WHERE " . implode(" AND ", $conditions);
    }

    $sql = "SELECT wr.*,
                   u.full_name as employee_name,
                   u.username as employee_username,
                   admin.full_name as reviewed_by_name,
                   CONCAT(DATE_FORMAT(week_start_date, '%Y-%m-%d'), ' - ', DATE_FORMAT(week_end_date, '%Y-%m-%d')) as week_range
            FROM weekly_reports wr
            JOIN users u ON wr.user_id = u.id
            LEFT JOIN users admin ON wr.reviewed_by = admin.id
            $where_clause
            ORDER BY wr.submitted_at DESC, wr.week_start_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function review_weekly_report($conn, $report_id, $admin_id, $feedback = '') {
    $sql = "UPDATE weekly_reports SET 
            status = 'reviewed',
            reviewed_by = ?,
            reviewed_at = NOW(),
            admin_feedback = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$admin_id, $feedback, $report_id]);
}

function get_weekly_report_by_id($conn, $report_id) {
    $sql = "SELECT wr.*, 
                   u.full_name as employee_name,
                   u.username as employee_username,
                   admin.full_name as reviewed_by_name
            FROM weekly_reports wr
            JOIN users u ON wr.user_id = u.id
            LEFT JOIN users admin ON wr.reviewed_by = admin.id
            WHERE wr.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$report_id]);
    return $stmt->fetch();
}

?>
