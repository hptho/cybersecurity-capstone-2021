<?php
include('config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="<?php echo $design; ?>/style.css" rel="stylesheet" title="Style" />
		<title>Profile of an user</title>
	</head>
	<body>
		<div class="header">
			<h1>Cybersecurity Capstone Project - October 2021</h1>
		</div>
		<div class="content">

<?php
//We check if the users ID is defined
if (isset($_GET['id'])) {
	$id = intval($_GET['id']);
	//We check if the user exists
	$dn = mysqli_query($link, 'select username, email, signup_date from users where id="'.$id.'"');
	if (mysqli_num_rows($dn)>0) {
		$dnn = mysqli_fetch_array($dn);
		//We display the user datas
?>
This is the profile of "<?php echo htmlentities($dnn['username']); ?>" :
	<table style="width:500px;">
		<tr>
			<td class="left"><h1><?php echo htmlentities($dnn['username'], ENT_QUOTES, 'UTF-8'); ?></h1>
Email: <?php echo htmlentities($dnn['email'], ENT_QUOTES, 'UTF-8'); ?><br />
This user joined the website on <?php echo date('Y/m/d',$dnn['signup_date']); ?></td>
		</tr>
	</table>
<?php
//We add a link to send a pm to the user
		if (isset($_SESSION['username']))
?>
<br /><a href="new_pm.php?recip=<?php echo urlencode($dnn['username']); ?>" class="big">Send a private message to "<?php echo htmlentities($dnn['username'], ENT_QUOTES, 'UTF-8'); ?>"</a>
<?php
	}
	else echo 'This user dont exists.';
}
else echo 'The user ID is not defined.';
?>
		</div>
		<div class="foot"><a href="users.php">Go to the users list</a></div>
	</body>
</html>