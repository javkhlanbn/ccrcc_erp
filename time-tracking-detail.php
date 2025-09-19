<?php 
session_start();
// Монголын цагийн бүс тохируулах
date_default_timezone_set('Asia/Ulaanbaatar');

include "DB_connection.php";
include "app/Model/TimeTracking.php";
include "app/Model/User.php";

// Get current user permissions first
$current_user = get_user_by_id($conn, $_SESSION['id']);
$can_view_all_time = $current_user && ($current_user['can_view_all_time'] ?? 0);

// Check access permissions
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    $em = "Нэвтрэх шаардлагатай";
    header("Location: login.php?error=$em");
    exit();
}

if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager' && !$can_view_all_time) {
    $em = "Зөвхөн админ, менежер эсвэл зөвшөөрөлтэй хэрэглэгч хандах боломжтой";
    header("Location: login.php?error=$em");
    exit();
}

$user_id = $_GET['user_id'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

if (!$user_id) {
    header("Location: admin-time-tracking.php?error=Ажилтан сонгогдоогүй");
    exit();
}

// Ажилтны мэдээлэл авах
$user_info = get_user_by_id($conn, $user_id);
if (!$user_info) {
    header("Location: admin-time-tracking.php?error=Ажилтан олдсонгүй");
    exit();
}

// Тухайн ажилтны цагийн хураангуй
$user_summary = get_user_time_summary($conn, $user_id, 'custom');

// Цагийн дэлгэрэнгүй бүртгэл авах
$sql = "SELECT * FROM time_entries 
        WHERE user_id = ? AND date BETWEEN ? AND ?
        ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id, $date_from, $date_to]);
$time_entries = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($user_info['full_name']) ?> - Цагийн дэлгэрэнгүй</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .main-content {
            flex: 1; /* Flexbox child тохиргоо */
            padding: 20px;
            min-height: 100vh;
            overflow-x: auto;
        }
        
        .detail-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .user-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }
        
        .user-info h1 {
            margin: 0;
            font-size: 28px;
        }
        
        .user-info p {
            margin: 5px 0;
            opacity: 0.9;
        }
        
        .back-button {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }
        
        .period-selector {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .period-form {
            display: flex;
            gap: 15px;
            align-items: end;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 5px solid;
        }
        
        .summary-card.days {
            border-left-color: #4CAF50;
        }
        
        .summary-card.total {
            border-left-color: #2196F3;
        }
        
        .summary-card.average {
            border-left-color: #FF9800;
        }
        
        .summary-card.overtime {
            border-left-color: #f44336;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .summary-label {
            color: #666;
            font-size: 14px;
        }
        
        .entries-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .section-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .entries-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .entries-table th,
        .entries-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .entries-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .entries-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-break {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .manual-badge {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 10px;
            margin-left: 5px;
        }
        
        .overtime-highlight {
            background-color: #fff3cd;
            font-weight: bold;
            color: #856404;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .add-entry-btn {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Edit Modal Form Styling */
        #editModal .form-group {
            margin-bottom: 15px;
        }

        #editModal label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }

        #editModal input[type="date"],
        #editModal input[type="time"],
        #editModal select,
        #editModal textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        #editModal input:focus,
        #editModal select:focus,
        #editModal textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        #editModal select {
            background: white;
            cursor: pointer;
        }

        #editModal textarea {
            resize: vertical;
            min-height: 80px;
        }

        #editModal .filter-btn {
            background: #007bff;
            color: #fff;
            font-weight: 600;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
        }

        #editModal .filter-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body class="body">
    <?php include "inc/nav.php"; ?>
    
    <div class="main-content">
        <div class="detail-container">
        <!-- Ажилтны мэдээлэл -->
        <div class="user-header">
            <div class="user-avatar">
                <i class="fa fa-user"></i>
            </div>
            <div class="user-info">
                <h1><?= htmlspecialchars($user_info['full_name']) ?></h1>
                <p><i class="fa fa-user"></i> @<?= htmlspecialchars($user_info['username']) ?></p>
                <p><i class="fa fa-calendar"></i> Хугацаа: <?= $date_from ?> - <?= $date_to ?></p>
            </div>
            <a href="admin-time-tracking.php" class="back-button">
                <i class="fa fa-arrow-left"></i> Буцах
            </a>
        </div>
        
        <!-- Хугацаа сонгох -->
        <div class="period-selector">
            <form class="period-form" method="GET">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                
                <div class="form-group">
                    <label for="date_from">Эхлэх огноо:</label>
                    <input type="date" id="date_from" name="date_from" value="<?= $date_from ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">Дуусах огноо:</label>
                    <input type="date" id="date_to" name="date_to" value="<?= $date_to ?>">
                </div>
                
                <button type="submit" class="filter-btn">
                    <i class="fa fa-search"></i> Хайх
                </button>
            </form>
        </div>
        
        <?php if (!empty($time_entries)): ?>
            <?php
            // Статистик тооцох
            $total_days = count($time_entries);
            $total_hours = array_sum(array_column($time_entries, 'total_hours'));
            $total_overtime = array_sum(array_column($time_entries, 'overtime_hours'));
            $avg_hours = $total_days > 0 ? $total_hours / $total_days : 0;
            ?>
            
            <!-- Хураангуй -->
            <div class="summary-grid">
                <div class="summary-card days">
                    <div class="summary-value"><?= $total_days ?></div>
                    <div class="summary-label">Ажилласан өдөр</div>
                </div>
                
                <div class="summary-card total">
                    <div class="summary-value"><?= number_format($total_hours, 1) ?></div>
                    <div class="summary-label">Нийт ажилласан цаг</div>
                </div>
                
                <div class="summary-card average">
                    <div class="summary-value"><?= number_format($avg_hours, 1) ?></div>
                    <div class="summary-label">Өдрийн дундаж цаг</div>
                </div>
                
                <div class="summary-card overtime">
                    <div class="summary-value"><?= number_format($total_overtime, 1) ?></div>
                    <div class="summary-label">Нийт илүү цаг</div>
                </div>
            </div>
            
            <!-- Цагийн бүртгэлийн дэлгэрэнгүй -->
            <div class="entries-section">
                <div class="section-header">
                    <h3><i class="fa fa-clock-o"></i> Цагийн бүртгэлийн түүх (<?= $total_days ?> өдөр)</h3>
                    <?php if ($_SESSION['id'] != 42): ?>
                    <a href="#" class="add-entry-btn" onclick="addNewEntry()">
                        <i class="fa fa-plus"></i> Шинэ бүртгэл
                    </a>
                    <?php endif; ?>
                </div>
                
                <table class="entries-table">
                    <thead>
                        <tr>
                            <th>Огноо</th>
                            <th>Эхлэх цаг</th>
                            <th>Дуусах цаг</th>
                            <th>Нийт цаг</th>
                            <th>Илүү цаг</th>
                            <th>Төлөв</th>
                            <th>Тэмдэглэл</th>
                            <th>Засварласан</th>
                            <th>Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($time_entries as $entry): ?>
                            <tr class="<?= $entry['overtime_hours'] > 0 ? 'overtime-highlight' : '' ?>">
                                <td><?= date('Y-m-d (l)', strtotime($entry['date'])) ?></td>
                                <td><?= $entry['start_time'] ?: '-' ?></td>
                                <td><?= $entry['end_time'] ?: '-' ?></td>
                                <td><?= $entry['total_hours'] ? number_format($entry['total_hours'], 2) . ' цаг' : '-' ?></td>
                                <td>
                                    <?php if ($entry['overtime_hours'] > 0): ?>
                                        <strong style="color: #FF9800;">
                                            <?= number_format($entry['overtime_hours'], 2) ?> цаг
                                        </strong>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $entry['status'] ?>">
                                        <?php 
                                        switch($entry['status']) {
                                            case 'completed': echo 'Дууссан'; break;
                                            case 'in_progress': echo 'Явж байна'; break;
                                            case 'break': echo 'Завсарлага'; break;
                                            default: echo $entry['status']; break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($entry['notes']) ?: '-' ?>
                                </td>
                                <td>
                                    <?php if ($entry['is_manual']): ?>
                                        <span class="manual-badge">Гар</span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($_SESSION['id'] != 42): ?>
                                    <div class="action-buttons">
                                        <button onclick="editEntry(<?= $entry['id'] ?>)" 
                                                class="btn-sm btn-edit" title="Засах">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button onclick="deleteEntry(<?= $entry['id'] ?>)" 
                                                class="btn-sm btn-delete" title="Устгах">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-clock-o" style="font-size: 48px; margin-bottom: 20px; color: #ccc;"></i>
                <h3>Цагийн бүртгэл олдсонгүй</h3>
                <p>Сонгосон хугацаанд <?= htmlspecialchars($user_info['full_name']) ?> ажилтны цагийн бүртгэл байхгүй байна.</p>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 5% auto; padding: 20px; border-radius: 10px; width: 90%; max-width: 500px;">
            <span style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h3>Цагийн бүртгэл засах</h3>
            <div id="editForm">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Add New Entry Modal -->
    <div id="addModal">
  <div class="modal-content">
    <span class="close-btn" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
    <h3>Шинэ цагийн бүртгэл нэмэх</h3>
    <form id="addEntryForm">
      <input type="hidden" name="user_id" value="<?= $user_id ?>">
      <div class="form-group">
        <label for="add_date">Огноо:</label>
        <input type="date" id="add_date" name="date" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="form-group">
        <label for="add_start_time">Эхлэх цаг:</label>
        <input type="time" id="add_start_time" name="start_time" value="09:00" required>
      </div>
      <div class="form-group">
        <label for="add_end_time">Дуусах цаг:</label>
        <input type="time" id="add_end_time" name="end_time" value="18:00" required>
      </div>
      <div class="form-group">
        <label for="add_notes">Тэмдэглэл:</label>
        <textarea id="add_notes" name="notes"></textarea>
      </div>
      <button type="submit" class="filter-btn">Нэмэх</button>
    </form>
  </div>
</div>
<style>
    /* Modal background */
#addModal {
  display: none; /* эхлээд нуух */
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.6);
}

/* Modal box */
#addModal .modal-content {
  background: #fff;
  margin: 5% auto;
  padding: 25px 20px;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.25);
  animation: fadeIn 0.3s ease;
}

/* Close button */
#addModal .close-btn {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
}
#addModal .close-btn:hover {
  color: #333;
}

/* Form styling */
#addModal .form-group {
  margin-bottom: 15px;
}

#addModal label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
  color: #333;
}

#addModal input[type="date"],
#addModal input[type="time"],
#addModal textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s;
}

#addModal input:focus,
#addModal textarea:focus {
  border-color: #007bff;
  outline: none;
}

/* Button */
#addModal .filter-btn {
  background: #007bff;
  color: #fff;
  font-weight: 600;
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
  transition: background 0.2s;
}

#addModal .filter-btn:hover {
  background: #0056b3;
}

/* Fade in animation */
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(-10px);}
  to {opacity: 1; transform: translateY(0);}
}

</style>

    <script>
        function editEntry(entryId) {
            // Fetch entry data via AJAX and show in modal form for editing
            fetch('app/get-time-entry.php?id=' + entryId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const entry = data.entry;
                        const modal = document.getElementById('editModal');
                        const editForm = document.getElementById('editForm');
                        editForm.innerHTML = `
                            <form id="editEntryForm">
                                <input type="hidden" name="entry_id" value="${entry.id}">
                                <div class="form-group">
                                    <label for="edit_date">Огноо:</label>
                                    <input type="date" id="edit_date" name="date" value="${entry.date}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_start_time">Эхлэх цаг:</label>
                                    <input type="time" id="edit_start_time" name="start_time" value="${entry.start_time}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_end_time">Дуусах цаг:</label>
                                    <input type="time" id="edit_end_time" name="end_time" value="${entry.end_time}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_status">Төлөв:</label>
                                    <select id="edit_status" name="status" required>
                                        <option value="completed" ${entry.status === 'completed' ? 'selected' : ''}>Дууссан</option>
                                        <option value="in_progress" ${entry.status === 'in_progress' ? 'selected' : ''}>Явж байна</option>
                                        <option value="break" ${entry.status === 'break' ? 'selected' : ''}>Завсарлага</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit_notes">Тэмдэглэл:</label>
                                    <textarea id="edit_notes" name="notes">${entry.notes || ''}</textarea>
                                </div>
                                <button type="submit" class="filter-btn">Хадгалах</button>
                            </form>
                        `;
                        modal.style.display = 'block';

                        document.getElementById('editEntryForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            const formData = new FormData(this);
                            fetch('app/update-time-entry.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(resData => {
                                alert(resData.message);
                                if (resData.success) {
                                    modal.style.display = 'none';
                                    location.reload();
                                }
                            })
                            .catch(() => alert('Алдаа гарлаа. Дахин оролдоно уу.'));
                        });
                    } else {
                        alert('Цагийн бүртгэл олдсонгүй.');
                    }
                })
                .catch(() => alert('Алдаа гарлаа. Дахин оролдоно уу.'));
        }
        
        function deleteEntry(entryId) {
            if (confirm('Энэ цагийн бүртгэлийг устгахдаа итгэлтэй байна уу?')) {
                fetch('app/delete-time-entry.php?id=' + entryId, {
                    method: 'DELETE'
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(() => alert('Алдаа гарлаа. Дахин оролдоно уу.'));
            }
        }

        function addNewEntry() {
            const modal = document.getElementById('addModal');
            modal.style.display = 'block';

            document.getElementById('addEntryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('app/add-time-entry.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(resData => {
                    alert(resData.message);
                    if (resData.success) {
                        modal.style.display = 'none';
                        location.reload();
                    }
                })
                .catch(() => alert('Алдаа гарлаа. Дахин оролдоно уу.'));
            });
        }
        
        // Хуудас ачааллагдахад огнооны утгыг сонгох
        document.addEventListener('DOMContentLoaded', function() {
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            if (!dateFrom.value) {
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                dateFrom.value = thirtyDaysAgo.toISOString().split('T')[0];
            }
            
            if (!dateTo.value) {
                const today = new Date();
                dateTo.value = today.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
