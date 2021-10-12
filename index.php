<!-- Home page. App starts here. -->
<?php
include('config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="<?php echo $design; ?>/style.css" rel="stylesheet" title="Style" />
        <title>Members Area</title>
    </head>
    <body>
    	<div class="header">
			<h1>Cybersecurity Capstone Project - October 2021</h1>
	    </div>
        <div class="content">
<?php
//We display a welcome message, if the user is logged, we display it username
?>

<?php
if(isset($_SESSION['username'])) 
{
	echo 'Hello <b>' .htmlentities($_SESSION['username'], ENT_QUOTES, 'UTF-8') . '</b>, you are successfully logged in.';
}
?>

<br />
Welcome to the Coursera cyber security course capstone project.<br />
Registered users can exchange messages on this site.<br />

<?php
//If the user is logged, we display links to edit his infos, to see his pms and to log out
if (isset($_SESSION['username'])) 
{
	//We count the number of new messages the user has
	$nb_new_pm = mysqli_fetch_array(mysqli_query($link, 'select count(*) as nb_new_pm from messages where ((user1="'.$_SESSION['userid'].'" and user1read="no") or (user2="'.$_SESSION['userid'].'" and user2read="no")) and id2="1"'));
	//The number of new messages is in the variable $nb_new_pm
	$nb_new_pm = $nb_new_pm['nb_new_pm'];
	//We display the links
?>
<br />
<a href="list_pm.php">Read/Send Messages (<?php echo $nb_new_pm; ?> new messages)</a><br />
<a href="users.php">Show list of registered users</a><br />
<a href="edit_infos.php">Edit profile information</a><br />
<br />
<a href="access.php">Logout</a>
<?php
}
else 
{
//Otherwise, we display a link to log in and to Sign up
?>
<br/>
<a href="register.php">Register</a><br/>
<a href="access.php">Login</a>
<?php
}
?>
		</div>
	</body>
</html>