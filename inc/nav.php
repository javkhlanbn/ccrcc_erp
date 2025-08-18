<nav class="side-bar">
			<div class="user-p">
				<img src="img/user.png">
<h4><?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '@'.$_SESSION['username']; ?></h4>
			</div>
			
			<?php 

			   if($_SESSION['role'] == "employee"){
			 ?>
			 <!-- Employee Navigation Bar -->
			<ul id="navList">
				<li>
					<a href="index.php">
						<i class="fa fa-tachometer" aria-hidden="true"></i>
						<span>Хянах самбар</span>
					</a>
				</li>
				<li>
					<a href="my_task.php">
						<i class="fa fa-tasks" aria-hidden="true"></i>
						<span>Миний даалгавар</span>
					</a>
				</li>
				<li>
					<a href="profile.php">
						<i class="fa fa-user" aria-hidden="true"></i>
						<span>Профайл</span>
					</a>
				</li>
				<li>
					<a href="notifications.php">
						<i class="fa fa-bell" aria-hidden="true"></i>
						<span>Мэдэгдэл</span>
					</a>
				</li>
			   <li>
				   <a href="chat.php" class="<?php if(basename($_SERVER['PHP_SELF'])=='chat.php') echo 'active'; ?>">
					   <i class="fa fa-comments" aria-hidden="true"></i>
					   <span>Чат</span>
					   <span id="chatNotification" class="notification-badge" style="display: none;"></span>
				   </a>
			   </li>
			   <li>
				   <a href="time-tracking.php" class="<?php if(basename($_SERVER['PHP_SELF'])=='time-tracking.php') echo 'active'; ?>">
					   <i class="fa fa-clock-o" aria-hidden="true"></i>
					   <span>Цагийн бүртгэл</span>
				   </a>
			   </li>
			   <li>
				   <a href="leave-request.php">
					   <i class="fa fa-calendar" aria-hidden="true"></i>
					   <span>Чөлөөний хүсэлт</span>
				   </a>
			   </li>
			   <li>
				   <a href="logout.php">
					   <i class="fa fa-sign-out" aria-hidden="true"></i>
					   <span>Гарах</span>
				   </a>
			   </li>
			</ul>
		<?php }else { ?>
			<!-- Admin Navigation Bar -->
			<ul id="navList">
				<li>
					<a href="index.php">
						<i class="fa fa-tachometer" aria-hidden="true"></i>
						<span>Хянах самбар</span>
					</a>
				</li>
				<li>
					<a href="user.php">
						<i class="fa fa-users" aria-hidden="true"></i>
						<span>Ажилтан удирдах</span>
					</a>
				</li>
				<li class="dropdown">
					<a href="javascript:void(0)" class="dropdown-toggle" onclick="toggleDropdown(this)">
						<i class="fa fa-tasks" aria-hidden="true"></i>
						<span>Даалгавар</span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="create_task.php">
								<i class="fa fa-plus" aria-hidden="true"></i>
								<span>Даалгавар үүсгэх</span>
							</a>
						</li>
						<li>
							<a href="tasks.php">
								<i class="fa fa-list" aria-hidden="true"></i>
								<span>Бүх даалгаврууд</span>
							</a>
						</li>
						<li>
							<a href="task-reports.php">
								<i class="fa fa-file-text" aria-hidden="true"></i>
								<span>Даалгаврын тайлан</span>
							</a>
						</li>
					</ul>
				</li>
			   <li>
				   <a href="chat.php">
					   <i class="fa fa-comments" aria-hidden="true"></i>
					   <span>Чат</span>
					   <span id="chatNotification" class="notification-badge" style="display: none;"></span>
				   </a>
			   </li>
			   <li>
				   <a href="admin-time-tracking.php" class="<?php if(basename($_SERVER['PHP_SELF'])=='admin-time-tracking.php') echo 'active'; ?>">
					   <i class="fa fa-clock-o" aria-hidden="true"></i>
					   <span>Цагийн удирдлага</span>
				   </a>
			   </li>
			   <li>
				   <a href="manage-leave-requests.php">
					   <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
					   <span>Чөлөөний хүсэлт</span>
				   </a>
			   </li>
			   <li>
				   <a href="logout.php">
					   <i class="fa fa-sign-out" aria-hidden="true"></i>
					   <span>Гарах</span>
				   </a>
			   </li>
			</ul>
		<?php } ?>
		</nav>

<script>
function toggleDropdown(element) {
    const dropdown = element.parentElement;
    const isOpen = dropdown.classList.contains('open');
    
    // Бусад бүх dropdown-г хаах
    document.querySelectorAll('.dropdown.open').forEach(function(openDropdown) {
        openDropdown.classList.remove('open');
    });
    
    // Одоогийн dropdown-г toggle хийх
    if (!isOpen) {
        dropdown.classList.add('open');
        // Dropdown нээгдэх үед active style нэмэх
        element.style.background = '#127b8e';
        element.style.color = '#fff';
    } else {
        // Dropdown хаагдах үед style арилгах
        element.style.background = '';
        element.style.color = '';
    }
}

// Dropdown-ээс гадаа дарвал хаах
document.addEventListener('click', function(event) {
    if (!event.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown.open').forEach(function(openDropdown) {
            openDropdown.classList.remove('open');
            // Style-г арилгах
            const toggle = openDropdown.querySelector('.dropdown-toggle');
            if (toggle) {
                toggle.style.background = '';
                toggle.style.color = '';
            }
        });
    }
});

// Хуудас ачаалагдахад одоогийн хуудасны dropdown-г идэвхжүүлэх
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const taskPages = ['create_task.php', 'tasks.php', 'task-reports.php'];
    
    if (taskPages.includes(currentPage)) {
        const taskDropdown = document.querySelector('.dropdown');
        if (taskDropdown) {
            taskDropdown.classList.add('open');
            const toggle = taskDropdown.querySelector('.dropdown-toggle');
            if (toggle) {
                toggle.style.background = '#127b8e';
                toggle.style.color = '#fff';
            }
            
            // Тухайн хуудасны link-г active болгох
            const activeLink = taskDropdown.querySelector(`a[href="${currentPage}"]`);
            if (activeLink) {
                activeLink.style.background = '#127b8e';
                activeLink.style.color = '#fff';
                activeLink.style.fontWeight = 'bold';
            }
        }
    }
});
</script>