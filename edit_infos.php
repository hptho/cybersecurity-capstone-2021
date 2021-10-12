<!-- Alter data of a registered user. -->
<?php
include('config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="<?php echo $design; ?>/style.css" rel="stylesheet" title="Style" />
		<title>Edit my personal information</title>
	</head>
	<body>
		<div class="header">
			<h1>Cybersecurity Capstone Project - October 2021</h1>
		</div>

<?php
//We check if the user is logged
if (isset($_SESSION['username'])) 
{
	//We check if the form has been sent
	if (isset($_POST['username'], $_POST['password'], $_POST['passverif'], $_POST['email'], $_POST['maidenname'], $_POST['maidennamerepeat'], $_POST['elemschool'], $_POST['elemschoolrepeat'], $_POST['road'], $_POST['roadrepeat']))
	{
		$errors = [];
		if ($_POST['maidenname'] != '' and $_POST['maidennamerepeat'] != '' and $_POST['elemschool'] != '' and $_POST['elemschoolrepeat'] != '' and  $_POST['road'] != '' and $_POST['roadrepeat'] != '')
		{	
			//We check if the two passwords are identical
			if ($_POST['password'] == $_POST['passverif']) 
			{
				//We check if the choosen password is strong enough.
				if (checkPassword($_POST['password'], $errors)) 
				{
					//We check if the email form is valid
					if (preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i',$_POST['email'])) 
					{
						$username_input     = $_POST['username'];
						$password_input     = $_POST['password'];
						$email_input	    = $_POST['email'];
						$maidenname_input	= $_POST['maidenname'];
						$elemschool_input	= $_POST['elemschool'];
						$road_input	        = $_POST['road'];
						$confirm_input      = $_POST['confirm'];
						
						//We check if there is no other user using the same username
						$stmt = $link->prepare("SELECT count(*) as nb FROM users WHERE username=?"); // prepare sql statement for execution
						$stmt->bind_param("s", $username_input); // bind variables to the parameter markers of the prepared statement
						$stmt->execute(); // executed prepared statement	
						$req = $stmt->get_result(); // get result of executed statement
						$dn = $req->fetch_array();
						$stmt->close();
						
						//We check if the username changed and if it is available
						if ($dn['nb'] == 0 or $_POST['username'] == $_SESSION['username']) 
						{
							$stmt = $link->prepare("SELECT password,id,salt FROM users WHERE username=?"); // prepare sql statement for execution
							$stmt->bind_param("s", $username_input); // bind variables to the parameter markers of the prepared statement
							$stmt->execute(); // executed prepared statement	
							$req = $stmt->get_result(); // get result of executed statement
							$dn = $req->fetch_array();
							$stmt->close();
							
							$password_input = hash("sha512", $dn['salt'].$password_input); // Hash password with the salt to update database.
							$oldpassw = hash("sha512", $dn['salt'].$confirm_input);  // Hash confirm with the salt to match database.
							
							//We edit the user informations
							if ($dn['password'] == $oldpassw) 
							{
								//Check if the password recovery questions are valid
								$password_recovery_valid = false;
								if (($_POST['maidenname'] == $_POST['maidennamerepeat']) and ($_POST['elemschool'] == $_POST['elemschoolrepeat']) and  ($_POST['road'] == $_POST['roadrepeat']))
								{
									$password_recovery_valid = true;
									$maidenname_input	= hash("sha512", $dn['salt'].$maidenname_input);
									$elemschool_input	= hash("sha512", $dn['salt'].$elemschool_input);
									$road_input	        = hash("sha512", $dn['salt'].$road_input);
								}
								else
								{
									//Repeated answers to the password recovery questions are not always the same
									$form	= true;
									$message = 'The repeated answers to the password recovery questions are not always the same.';
								}
								
								if ($password_recovery_valid == true)
								{
									$stmt = $link->prepare('UPDATE users SET username=?, password=?, email=?, maidenname=?, elemschool=?, road=? WHERE id="'.$_SESSION['userid'].'"'); // prepare sql statement for execution
									$stmt->bind_param("ssssss", $username_input, $password_input, $email_input, $maidenname_input, $elemschool_input, $road_input); // bind variables to the parameter markers of the prepared statement
									$result = $stmt->execute(); // executed prepared statement	
									$stmt->close();
									
									if ($result)
									{ 
										//We dont display the form
										$form = false;
										//We delete the old session, so the user needs to login again
										unset($_SESSION['username'], $_SESSION['userid']);
	 ?>
			 <div class="message">Your informations have successfuly been updated. You need to login again.<br />
	 <?php
									}
									else 
									{
										//Otherwise, we say that an error occured
										$form	= true;
										$message = 'An error occurred while trying to update your informations in the database.';
									}
								}
							}
							else 
							{
								//Otherwise, we say the password is incorrect.
								$form	= true;
								$message = 'The username or password is incorrect.';
							}
						}
						else 
						{
							//Otherwise, we say the username is not available
							$form	= true;
							$message = 'The username you want to use is not available, please choose another one.';
						}
					}
					else 
					{
						//Otherwise, we say the email is not valid
						$form	= true;
						$message = 'The email you entered is not valid.';
					}
				}
				else 
				{
					//Otherwise, we say the password is too weak
					$form	= true;
					$message = '';
					foreach ($errors as $item) $message = $message.$item."<BR>";
				}
			}
			else
			{
				//Otherwise, we say the passwords are not identical
				$form	 = true;
				$message = 'The passwords you entered are not identical.';
			}
		}
		else
		{
			//Security questions are empty
			$form	 = true;
			$message = 'One or more of the security questions are not answered, please answer them with words for security reasons.';		
		}
	}
	else $form = true;

	if ($form) 
	{
		//If the form has already been sent, we display the same values
		if (isset($_POST['username'],$_POST['password'],$_POST['email'])) 
		{
			$username_input  = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8');
			$password_input  = '';
			$passverif_input = '';
			$email_input	 = htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8');
		}
		else 
		{
			//otherwise, we display the values of the database
			$stmt = $link->prepare("SELECT username,password,email FROM users WHERE username=?"); // prepare sql statement for execution
			$stmt->bind_param("s", $username_session); // bind variables to the parameter markers of the prepared statement
			$username_session = $_SESSION['username'];
			$stmt->execute(); // executed prepared statement	
			$req = $stmt->get_result(); // get result of executed statement		
			$dnn = $req->fetch_array();
			$stmt->close();
						
			$username_input  = htmlentities($dnn['username'], ENT_QUOTES, 'UTF-8');
			$password_input  = '';
			$passverif_input = '';
			$email_input	 = htmlentities($dnn['email'], ENT_QUOTES, 'UTF-8');
		}
		//We display the form
?>
		<div class="content">
			<form action="edit_infos.php" method="post">
				You can change your stored user data here:<br />
				<p style="font-size:14px;color:Red;">(Password requires 8 characters minimum and must include at least one number, one lowercase letter, one uppercase letter and one symbol)</p><br />
				<div class="center">
					<label for="username">Username</label><input type="text" name="username" id="username" value="<?php echo $username_input; ?>" readonly/><br />
					<label for="confirm">Old Password<span class="small"></span></label><input type="password" name="confirm" id="confirm" value="" /><br />
					<label for="password">New Password</label><input type="password" name="password" id="password" value="" /><br />
					<label for="passverif">Repeat New Password</label><input type="password" name="passverif" id="passverif" value="" /><br />
					<label for="email">Email</label><input type="text" name="email" id="email" value="<?php echo $email_input; ?>" /><br />
					<p style="text-align:left;text-decoration: underline;">Password recovery questions:</p><br />
					<label for="maidenname" style="width: 400px;text-align:left;">Your mother's maiden name?</label><input type="password" name="maidenname" /><br />
					<label for="maidennamerepeat" style="width: 400px;text-align:left;">Repeat: your mother's maiden name?</label><input type="password" name="maidennamerepeat" /><br /><br />
					<label for="elemschool" style="width: 400px;text-align:left;">What elementary school did you attend?</label><input type="password" name="elemschool" /><br />
					<label for="elemschoolrepeat" style="width: 400px;text-align:left;">Repeat: what elementary school did you attend?</label><input type="password" name="elemschoolrepeat" /><br /><br />
					<label for="road" style="width: 400px;text-align:left;">What is the name of the road you grew up on?</label><input type="password" name="road" /><br />
					<label for="roadrepeat" style="width: 400px;text-align:left;">Repeat: what is the name of the road you grew up on?</label><input type="password" name="roadrepeat" /><br /><br />
					<input type="submit" value="Send" />
				</div>
			</form>
		</div>

<?php
		//We display a message if necessary
		if(isset($message)) echo '<br><div class="message">'.$message.'</div>';
	}
}
else {
?>
		<div class="message">To access this page, you must login.<br />
		<a href="login.php">Login</a></div>

<?php
}
?>
		<div class="foot"><a href="<?php echo $url_home; ?>">Go to start page</a></div>
	</body>
</html>
