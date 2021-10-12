<!-- Authenticate a registered user. -->
<?php
include('config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="<?php echo $design; ?>/style.css" rel="stylesheet" title="Style" />
        <title>Registered user access</title>
    </head>
    <body>
    	<div class="header">
        	<h1>Cybersecurity Capstone Project - October 2021</h1>
	    </div>
<?php
//If the user is logged, we log him out
if(isset($_SESSION['username']))
{
	//We log him out by deleting the username and userid sessions
	unset($_SESSION['username'], $_SESSION['userid']);
?>
<div class="message">You have successfuly been logged out.<br />
<?php
}
else
{
	$ousername = '';
	//We check if the form has been sent
	if(isset($_POST['username'], $_POST['password']))
	{
		$username_input = $_POST['username'];
		$password_input = $_POST['password'];
		
		//We get the password of the user
		$stmt = $link->prepare("SELECT password,id,salt FROM users WHERE username=?"); // prepare sql statement for execution
		$stmt->bind_param("s", $username_input); // bind variables to the parameter markers of the prepared statement
		$stmt->execute(); // executed prepared statement	
		$req = $stmt->get_result(); // get result of executed statement
		$dn = $req->fetch_array();
		$stmt->close();
		$password_input = hash("sha512", $dn['salt'].$password_input); // Hash with the salt to match database.
		
		//We compare the submited password and the real one, and we check if the user exists
		if ($dn['password'] == $password_input and mysqli_num_rows($req)>0) 
		{
			//If the password is good, we dont show the form
			$form = false;
			//We save the user name in the session username and the user Id in the session userid
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['userid'] = $dn['id'];
			// go to start page
			header('Location: index.php');
		}
		else 
		{
			//Otherwise, we say the password is incorrect.
			$form = true;
			if (mysqli_num_rows($req) == 0)
			{
				$message = 'The entered username is not registered.';
			}
			else
			{
				$message = 'The entered password does not fit to the registered username. Click on link <a href="password_forgotten.php">Password forgotten?</a><br/> in case you forgot the password.';
			}
		}
	}
	else $form = true;
	
	if($form) 
	{
		//We display a message if necessary
		if(isset($message)) echo '<div class="message">'.$message.'</div>';

	//We display the form
?>
<div class="content">
    <form action="access.php" method="post">
        Please type your username and password to log in:<br />
		<br />
        <div class="center">
            <label for="username">Username</label><input type="text" name="username" id="username" value="<?php echo htmlentities($ousername, ENT_QUOTES, 'UTF-8'); ?>" /><br />
            <label for="password">Password</label><input type="password" name="password" id="password" /><br /><br />
            <input type="submit" value="Login" />
		</div>
    </form>
	
	<br/><a href="password_forgotten.php">Password forgotten?</a><br/>
</div>
<?php
	}
}
?>
		<div class="foot"><a href="<?php echo $url_home; ?>">Go to start page</a></div>
	</body>
</html>