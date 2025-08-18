<?php 

function submit_leave_request($conn, $data){
    $sql = "INSERT INTO leave_requests (employee_id, start_date, end_date, reason) VALUES(?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function submit_leave_request_extended($conn, $data){
    $sql = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, start_time, end_time, reason) VALUES(?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function get_all_leave_requests($conn){
    $sql = "SELECT lr.*, u.full_name as employee_name FROM leave_requests lr 
            JOIN users u ON lr.employee_id = u.id 
            ORDER BY lr.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);

    if($stmt->rowCount() > 0){
        $requests = $stmt->fetchAll();
    }else $requests = 0;

    return $requests;
}

function get_employee_leave_requests($conn, $employee_id){
    $sql = "SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$employee_id]);

    if($stmt->rowCount() > 0){
        $requests = $stmt->fetchAll();
    }else $requests = 0;

    return $requests;
}

function update_leave_request_status($conn, $id, $status, $admin_comment = null){
    $sql = "UPDATE leave_requests SET status = ?, admin_comment = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$status, $admin_comment, $id]);
}

function update_leave_request_with_reason($conn, $id, $status, $admin_reason){
    $sql = "UPDATE leave_requests SET status = ?, admin_reason = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$status, $admin_reason, $id]);
}

function get_leave_request_by_id($conn, $id){
    $sql = "SELECT lr.*, u.full_name as employee_name FROM leave_requests lr 
            JOIN users u ON lr.employee_id = u.id 
            WHERE lr.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    if($stmt->rowCount() > 0){
        $request = $stmt->fetch();
    }else $request = 0;

    return $request;
}

function count_pending_leave_requests($conn){
    $sql = "SELECT id FROM leave_requests WHERE status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);

    return $stmt->rowCount();
}
