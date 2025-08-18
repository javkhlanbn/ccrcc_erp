<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    $em = "Анх удаа нэвтэрч байна";
    header("Location: login.php?error=$em");
    exit();
}
include "DB_connection.php";
include "app/Model/User.php";
include "app/Model/Message.php";

$users = get_all_users_for_chat($conn);
$current_id = $_SESSION['id'];
$current_role = $_SESSION['role'];

// Get chat participants for the current user
$participants = get_chat_participants($conn, $current_id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Чат</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-container {
            display: flex;
            height: 80vh;
            max-width: 1200px;
            margin: 20px auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .chat-sidebar {
            width: 300px;
            border-right: 1px solid #ddd;
            background: #f8f9fa;
        }
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 15px;
            background: #43aa8b;
            color: #fff;
            border-bottom: 1px solid #ddd;
        }
        .user-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .user-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .user-item:hover {
            background-color: #e9ecef;
        }
        .user-item.active {
            background-color: #43aa8b;
            color: #fff;
        }
        .user-name {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .user-role {
            font-size: 12px;
            color: #6c757d;
        }
        .user-item.active .user-role {
            color: #fff;
        }
        .unread-count {
            float: right;
            background: #dc3545;
            color: #fff;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 11px;
            min-width: 18px;
            text-align: center;
        }
        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f8f9fa;
        }
        .chat-input-area {
            padding: 15px;
            background: #fff;
            border-top: 1px solid #ddd;
        }
        .chat-input-form {
            display: flex;
            gap: 10px;
            align-items: end;
        }
        .chat-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
            font-family: inherit;
            min-height: 40px;
        }
        .file-input-container {
            position: relative;
        }
        .file-input {
            display: none;
        }
        .file-btn {
            padding: 10px;
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .file-btn:hover {
            background: #5a6268;
        }
        .send-btn {
            padding: 10px 20px;
            background: #43aa8b;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .send-btn:hover {
            background: #2d6a4f;
        }
        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .message {
            margin-bottom: 15px;
            max-width: 70%;
        }
        .message.sent {
            margin-left: auto;
            text-align: right;
        }
        .message-bubble {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        .message.received .message-bubble {
            background: #e9ecef;
            color: #333;
        }
        .message.sent .message-bubble {
            background: #43aa8b;
            color: #fff;
        }
        .message-time {
            font-size: 11px;
            color: #6c757d;
            margin-top: 5px;
        }
        .message.sent .message-time {
            color: #a8dadc;
        }
        .no-chat-selected {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
            font-size: 18px;
        }
        .message-image {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .message-image:hover {
            transform: scale(1.02);
        }
        .file-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 300px;
        }
        .file-icon {
            font-size: 24px;
            color: #6c757d;
        }
        .file-details {
            flex: 1;
        }
        .file-name {
            font-weight: 500;
            margin-bottom: 2px;
        }
        .file-size {
            font-size: 12px;
            color: #6c757d;
        }
        .download-btn {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
        }
        .download-btn:hover {
            background: #0056b3;
        }
        .uploading {
            opacity: 0.6;
        }
        .empty-chat {
            text-align: center;
            color: #6c757d;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1">
            <h4 class="title">Чат</h4>
            
            <div class="chat-container">
                <!-- Chat Sidebar -->
                <div class="chat-sidebar">
                    <div class="chat-header">
                        <h5 style="margin: 0;">Хэрэглэгчид</h5>
                    </div>
                    <ul class="user-list" id="userList">
                        <?php
                        if ($users && is_array($users)) {
                            foreach ($users as $user) {
                                if ($user['id'] != $current_id) {
                                    $unread_count = 0;
                                    // Find unread count for this user
                                    foreach ($participants as $participant) {
                                        if ($participant['participant_id'] == $user['id']) {
                                            $unread_count = $participant['unread_count'];
                                            break;
                                        }
                                    }
                                    
                                    echo '<li class="user-item" data-user-id="'.$user['id'].'" data-user-name="'.$user['full_name'].'">';
                                    echo '<div class="user-name">'.$user['full_name'];
                                    if ($unread_count > 0) {
                                        echo '<span class="unread-count">'.$unread_count.'</span>';
                                    }
                                    echo '</div>';
                                    echo '<div class="user-role">'.($user['role'] == 'admin' ? 'Админ' : 'Ажилтан').'</div>';
                                    echo '</li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
                
                <!-- Chat Main Area -->
                <div class="chat-main">
                    <div class="chat-header" id="chatHeader">
                        <div class="no-chat-selected">Хэрэглэгч сонгоно уу</div>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <div class="no-chat-selected">
                            <div>
                                <i class="fa fa-comments" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                                <p>Хэрэглэгч сонгоод чат эхлүүлээрэй</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-input-area" id="chatInputArea" style="display: none;">
                        <form class="chat-input-form" id="chatForm" autocomplete="off" enctype="multipart/form-data">
                            <textarea class="chat-input" id="messageInput" rows="2" placeholder="Зурвас бичих..." maxlength="1000"></textarea>
                            <div class="file-input-container">
                                <input type="file" id="fileInput" class="file-input" accept="image/*,.pdf,.doc,.docx,.txt">
                                <button type="button" class="file-btn" onclick="document.getElementById('fileInput').click()">
                                    <i class="fa fa-paperclip"></i>
                                </button>
                            </div>
                            <button type="submit" class="send-btn" id="sendBtn">
                                <i class="fa fa-paper-plane"></i> Илгээх
                            </button>
                        </form>
                        <div id="filePreview" style="margin-top: 10px; display: none;">
                            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; display: flex; align-items: center; gap: 10px;">
                                <span id="fileName"></span>
                                <button type="button" onclick="clearFileSelection()" style="background: #dc3545; color: white; border: none; border-radius: 3px; padding: 2px 6px; cursor: pointer;">✕</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

<script>
const currentId = <?=$_SESSION['id']?>;
let selectedUserId = null;
let selectedUserName = '';
let messageInterval = null;

console.log('Current user ID:', currentId); // Debug log

// User selection
document.querySelectorAll('.user-item').forEach(item => {
    item.addEventListener('click', function() {
        // Remove active class from all items
        document.querySelectorAll('.user-item').forEach(i => i.classList.remove('active'));
        // Add active class to clicked item
        this.classList.add('active');
        
        selectedUserId = this.dataset.userId;
        selectedUserName = this.dataset.userName;
        
        // Update chat header
        document.getElementById('chatHeader').innerHTML = '<h5 style="margin: 0;">' + selectedUserName + '</h5>';
        
        // Show input area
        document.getElementById('chatInputArea').style.display = 'block';
        
        // Load messages
        loadMessages();
        
        // Start auto-refresh
        if (messageInterval) clearInterval(messageInterval);
        messageInterval = setInterval(loadMessages, 3000);
        
        // Remove unread count
        const unreadBadge = this.querySelector('.unread-count');
        if (unreadBadge) unreadBadge.remove();
    });
});

function loadMessages() {
    if (!selectedUserId) return;
    
    fetch('app/get_messages.php?receiver_id=' + selectedUserId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(messages => {
            console.log('Received messages:', messages); // Debug log
            
            if (messages.error) {
                console.error('Server error:', messages.error);
                document.getElementById('chatMessages').innerHTML = 
                    '<div class="empty-chat"><i class="fa fa-exclamation-triangle" style="font-size: 36px; color: #dc3545; margin-bottom: 10px;"></i><p>Алдаа: ' + messages.error + '</p></div>';
                return;
            }
            
            const chatArea = document.getElementById('chatMessages');
            
            if (!Array.isArray(messages) || messages.length === 0) {
                chatArea.innerHTML = '<div class="empty-chat"><i class="fa fa-comments" style="font-size: 36px; opacity: 0.3; margin-bottom: 10px;"></i><p>Хараахан зурвас байхгүй байна</p></div>';
                return;
            }
            
            let html = '';
            messages.forEach(msg => {
                const isOwn = msg.sender_id == currentId;
                const messageClass = isOwn ? 'sent' : 'received';
                const time = new Date(msg.created_at).toLocaleString('mn-MN', {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += `<div class="message ${messageClass}">`;
                html += `<div class="message-bubble">`;
                
                // Handle different message types
                if (msg.message_type === 'image' && msg.file_name) {
                    html += `<img src="app/download_file.php?file=${msg.file_name}" class="message-image" onclick="openImage('${msg.file_name}')" alt="Image">`;
                    if (msg.message && msg.message !== '[Зураг илгээсэн]') {
                        html += `<p style="margin-top: 8px;">${msg.message}</p>`;
                    }
                } else if (msg.message_type === 'file' && msg.file_name) {
                    html += `<div class="file-info">
                        <i class="fa fa-file file-icon"></i>
                        <div class="file-details">
                            <div class="file-name">${msg.original_filename || msg.file_name}</div>
                            <div class="file-size">${formatFileSize(msg.file_size)}</div>
                        </div>
                        <button class="download-btn" onclick="downloadFile('${msg.file_name}', '${msg.original_filename}')">
                            <i class="fa fa-download"></i>
                        </button>
                    </div>`;
                    if (msg.message && !msg.message.startsWith('[Файл илгээсэн:')) {
                        html += `<p style="margin-top: 8px;">${msg.message}</p>`;
                    }
                } else {
                    html += msg.message;
                }
                
                html += `</div>`;
                html += `<div class="message-time">${time}</div>`;
                html += `</div>`;
            });
            
            chatArea.innerHTML = html;
            chatArea.scrollTop = chatArea.scrollHeight;
        })
        .catch(error => {
            console.error('Error loading messages:', error);
            document.getElementById('chatMessages').innerHTML = 
                '<div class="empty-chat"><i class="fa fa-exclamation-triangle" style="font-size: 36px; color: #dc3545; margin-bottom: 10px;"></i><p>Зурвас ачаалахад алдаа гарлаа</p></div>';
        });
}

// Send message
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    const sendBtn = document.getElementById('sendBtn');
    const message = messageInput.value.trim();
    const file = fileInput.files[0];
    
    if (!selectedUserId) return;
    
    // Check if we have either message or file
    if (!message && !file) {
        alert('Зурвас эсвэл файл сонгоно уу');
        return;
    }
    
    // Disable send button during upload
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Илгээж байна...';
    
    const formData = new FormData();
    formData.append('receiver_id', selectedUserId);
    
    if (file) {
        // Send file
        formData.append('file', file);
        formData.append('message', message);
        
        fetch('app/send_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Send file response:', data);
            if (data.success) {
                messageInput.value = '';
                clearFileSelection();
                loadMessages();
            } else {
                alert('Файл илгээхэд алдаа гарлаа: ' + (data.error || 'Тодорхойгүй алдаа'));
            }
        })
        .catch(error => {
            console.error('Error sending file:', error);
            alert('Файл илгээхэд алдаа гарлаа: ' + error.message);
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fa fa-paper-plane"></i> Илгээх';
        });
    } else {
        // Send text message
        formData.append('message', message);
        
        fetch('app/send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Send message response:', data);
            if (data.success) {
                messageInput.value = '';
                loadMessages();
            } else {
                alert('Зурвас илгээхэд алдаа гарлаа: ' + (data.error || 'Тодорхойгүй алдаа'));
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Зурвас илгээхэд алдаа гарлаа: ' + error.message);
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fa fa-paper-plane"></i> Илгээх';
        });
    }
});

// Handle Enter key in textarea
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
});

// File input handling
document.getElementById('fileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (10MB limit)
        if (file.size > 10 * 1024 * 1024) {
            alert('Файлын хэмжээ 10MB-аас их байж болохгүй');
            e.target.value = '';
            return;
        }
        
        // Show file preview
        document.getElementById('fileName').textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        document.getElementById('filePreview').style.display = 'block';
    }
});

// Clear file selection
function clearFileSelection() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Download file
function downloadFile(filename, originalName) {
    const link = document.createElement('a');
    link.href = 'app/download_file.php?file=' + encodeURIComponent(filename);
    link.download = originalName || filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Open image in new window
function openImage(filename) {
    window.open('app/download_file.php?file=' + encodeURIComponent(filename), '_blank');
}

// Set active navigation
var active = document.querySelector("#navList li:nth-child(5)");
if(active) active.classList.add("active");
</script>
</body>
</html>
