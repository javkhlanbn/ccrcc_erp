<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "employee") {
	include "DB_connection.php";
	include "app/Model/User.php";
	$user = get_user_by_id($conn, $_SESSION['id']);
	
 ?>
<!DOCTYPE html>
<html>
<head>
<title>Профайл</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">

</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
<section class="section-1">
<h4 class="title">Профайл <a href="edit_profile.php">Засах</a></h4>
		 <table class="main-table" style="max-width: 300px;">
				<tr>
<td colspan="2" style="text-align:center;">
	<?php if (!empty($user['profile_image'])): ?>
		<img src="<?=$user['profile_image']?>" alt="Profile Image" style="max-width:120px; border-radius:50%;">
	<?php else: ?>
		<img src="img/erp logo.png" alt="Profile Image" style="max-width:120px; border-radius:50%;">
	<?php endif; ?>
</td>
				</tr>
				<tr>
<td>Бүтэн нэр</td>
					<td><?=$user['full_name']?></td>
				</tr>
				<tr>
<td>Хэрэглэгчийн нэр</td>
					<td><?=$user['username']?></td>
				</tr>
				<tr>
<td>Бүртгүүлсэн огноо</td>
					<td><?=$user['created_at']?></td>
				</tr>
			</table>

		</section>
	</div>

<script type="text/javascript">
	var active = document.querySelector("#navList li:nth-child(3)");
	active.classList.add("active");
</script>
</body>
</html>
<?php }else{ 
   $em = "Анх удаа нэвтэрч байна";
   header("Location: login.php?error=$em");
   exit();
}
 ?>