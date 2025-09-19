<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
	include "DB_connection.php";
	include "app/Model/User.php";
	
	if (!isset($_GET['id'])) {
		 header("Location: user.php");
		 exit();
	}
	$id = $_GET['id'];
	$user = get_user_by_id($conn, $id);

	if ($user == 0) {
		 header("Location: user.php");
		 exit();
	}

 ?>
<!DOCTYPE html>
<html>
<head>
<title>Хэрэглэгч засах</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">

</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
<h4 class="title">Хэрэглэгч засах <a href="user.php">Хэрэглэгчид</a></h4>
			<form class="form-1"
				  method="POST"
				  action="app/update-user.php">
				  <?php if (isset($_GET['error'])) {?>
			<div class="danger" role="alert">
			  <?php echo stripcslashes($_GET['error']); ?>
			</div>
		  <?php } ?>

		  <?php if (isset($_GET['success'])) {?>
			<div class="success" role="alert">
			  <?php echo stripcslashes($_GET['success']); ?>
			</div>
		  <?php } ?>
				<div class="input-holder">
<lable>Бүтэн нэр</lable>
<input type="text" name="full_name" class="input-1" placeholder="Бүтэн нэр" value="<?php echo $user['full_name']; ?>"><br>
				</div>
				<div class="input-holder">
<lable>Хэрэглэгчийн нэр</lable>
<input type="text" name="user_name" value="<?=$user['username']?>" class="input-1" placeholder="Хэрэглэгчийн нэр"><br>
				</div>
				<div class="input-holder">
<lable>Нууц үг</lable>
<input type="text" value="**********" name="password" class="input-1" placeholder="Нууц үг"><br>
				</div>
				<div class="input-holder">
<label>Эрхүүд</label><br>
<input type="checkbox" name="can_view_all_time" value="1" <?php echo (($user['can_view_all_time'] ?? 0) ? 'checked' : ''); ?>> Бүх ажилчдын цагийг харах<br>
<input type="checkbox" name="can_download_reports" value="1" <?php echo (($user['can_download_reports'] ?? 0) ? 'checked' : ''); ?>> Цагийн тайлан татаж авах<br>
				</div>
				<input type="text" name="id" value="<?=$user['id']?>" hidden>

<button class="edit-btn">Шинэчлэх</button>
			</form>
			
		</section>
	</div>

<script type="text/javascript">
	var active = document.querySelector("#navList li:nth-child(2)");
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