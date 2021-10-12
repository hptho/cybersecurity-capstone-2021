<!-- Handle forgotten password. -->
<?php
include('config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="<?php echo $design; ?>/style.css" rel="stylesheet" title="Style" />
        <title>Reset Password</title>
    </head>
    <body>
    	<div class="header">
        	<h1>Cybersecurity Capstone Project - October 2021</h1>
	    </div>

<?php
//Check if the form has been sent
if(isset($_POST['username'], $_POST['password'], $_POST['passverif'], $_POST['maidenname'], $_POST['elemschool'], $_POST['road']))
{
	$errors = [];
	
	//Check if username is registered
	$username = $_POST['username'];
	
	$stmt = $link->prepare("SELECT id FROM users WHERE username=?"); // prepare sql statement for execution
	$stmt->bind_param("s", $username); // bind variables to the parameter markers of the prepared statement
	$stmt->execute(); // executed prepared statement		
	$result = $stmt->get_result(); // get result of executed statement
	$stmt->close();
	
	if ($result != FALSE) 
	{
		/* determine number of rows result set */
		$row_cnt = mysqli_num_rows($result);
		mysqli_free_result($result);
	}
	else
	{
		echo "<script type=\"text/javascript\">alert(\"Last SQL query error: " . $link->error . "\")</script>";
	}
	
	if($row_cnt == 0)
	{
		//Entered user does not exist in database
		$form	= true;
		$message = 'The entered username is not registered, no new password can be set.';
	}
	else
	{
		$maidenname = $_POST['maidenname'];
		$elemschool = $_POST['elemschool'];
		$road	    = $_POST['road'];

		$stmt = $link->prepare("SELECT maidenname,elemschool,road,salt FROM users WHERE username=?"); // prepare sql statement for execution
		$stmt->bind_param("s", $username); // bind variables to the parameter markers of the prepared statement
		$stmt->execute(); // executed prepared statement		
		$result = $stmt->get_result(); // get result of executed statement
		$stmt->close();
		
		$user_data  = mysqli_fetch_array($result);
		$maidenname = hash("sha512", $user_data['salt'].$maidenname); // Hash with the salt to match database.
		$elemschool = hash("sha512", $user_data['salt'].$elemschool); // Hash with the salt to match database.
		$road       = hash("sha512", $user_data['salt'].$road); // Hash with the salt to match database.
		
		//Check if security questions are answered correctly
		if ($user_data['maidenname'] == $maidenname and $user_data['elemschool'] == $elemschool and $user_data['road'] == $road)
		{	
			//Check if new password is repeated correctly
			if($_POST['password'] == $_POST['passverif'])
			{
				//We check if the choosen password is strong enough.
				if(checkPassword($_POST['password'], $errors))
				{
					$new_password = $_POST['password'];
					$new_password = hash("sha512", $user_data['salt'].$new_password);
					
					//Store new password in database
					$stmt = $link->prepare("UPDATE users SET password=? WHERE username=?"); // prepare sql statement for execution
					$stmt->bind_param("ss", $new_password, $username); // bind variables to the parameter markers of the prepared statement
					$result = $stmt->execute(); // executed prepared statement		
					$stmt->close();
					
					if($result)
					{
						//We dont display the form
						$form = false;
						
						echo "<div class=\"message\">Reset password successfully. You can login now using the new password.<br />";
						echo "<a href=\"access.php\">Login</a></div>";						
					}
					else
					{
						//Otherwise, we say that an error occured
						$form	= true;
						$message = 'An error occurred while trying to store the new password into the database.';
						echo "<script type=\"text/javascript\">alert(\"Last SQL query error: " . $link->error . "\")</script>";
					}
				}
				else
				{
					//Otherwise, we say the password is too weak
					$form	= true;
					$message = '';
					foreach ($errors as $item)
						$message = $message.$item."<BR>";
				}
			}
			else
			{
				//Otherwise, we say the passwords are not identical
				$form	 = true;
				$message = 'The new passwords you entered are not identical.';			
			}
		}
		else
		{
			//A security question is not answered correctly
			$form	= true;
			$message = 'One or more of the security questions have not been answered correctly.';
		}		
	}
}
else
{
	$form = true;
}

if ($form) 
{
	//We display the form again?>
		<div class="content">
			<form action="password_forgotten.php" method="post">
				Please enter the username and answer the security questions to set a new password for the user:<br />
				<br />
				<div class="center">
					<label for="username" style="width: 350px;">Username</label><input type="text" name="username" id="username" /><br />
					<label for="maidenname" style="width: 350px;">Your mother's maiden name?</label><input type="password" name="maidenname" /><br />
					<label for="elemschool" style="width: 350px;">What elementary school did you attend?</label><input type="password" name="elemschool" /><br />
					<label for="road" style="width: 350px;">What is the name of the road you grew up on?</label><input type="password" name="road" /><br />	
					<label for="password" style="width: 350px;">New Password</label><input type="password" name="password" id="password" /><br />
					<label for="passverif" style="width: 350px;">Repeat New Password</label><input type="password" name="passverif" /><br /><br />
					<input type="submit" value="Reset Password" />
				</div>
			</form>
		</div>
<?php
	//We display a message if necessary
	if(isset($message))
	{
		echo '<br><div class="message">'.$message.'</div>';
	}
}
?>
		<div class="foot"><a href="<?php echo $url_home; ?>">Go to start page</a></div>
	</body>
</html>