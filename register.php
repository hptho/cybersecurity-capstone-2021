<!-- Register a new user. -->
<?php
include ('config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="<?php echo $design; ?>/style.css" rel="stylesheet" title="Style" />
		<title>Register</title>
	</head>
	<body>
		<div class="header">
			<h1>Cybersecurity Capstone Project - October 2021</h1>
		</div>
<?php
//We check if the form has been sent
if (isset($_POST['username'], $_POST['password'], $_POST['passverif'], $_POST['email'], $_POST['maidenname'], $_POST['maidennamerepeat'], $_POST['elemschool'], $_POST['elemschoolrepeat'], $_POST['road'], $_POST['roadrepeat']) and $_POST['username'] != '')
{
    //We check if the two passwords are identical
    $errors = [];
    if ($_POST['maidenname'] != '' and $_POST['maidennamerepeat'] != '' and $_POST['elemschool'] != '' and $_POST['elemschoolrepeat'] != '' and $_POST['road'] != '' and $_POST['roadrepeat'] != '')
    {
        // We check if the username only letter and numberic.
        if (preg_match('^[a-zA-Z0-9]*$', $_POST['username']))
        {
            if ($_POST['password'] == $_POST['passverif'])
            {
                //We check if the choosen password is strong enough.
                if (checkPassword($_POST['password'], $errors))
                {
                    //We check if the email form is valid
                    if (preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i', $_POST['email']))
                    {
                        $username_input = $_POST['username'];
                        $password_input = $_POST['password'];
                        $email_input = $_POST['email'];
                        $maidenname_input = $_POST['maidenname'];
                        $elemschool_input = $_POST['elemschool'];
                        $road_input = $_POST['road'];

                        //Generate a five digit salt.
                        $salt = (string)rand(10000, 99999);

                        //Compute the hashes of salt concatenated to user data for sensitive information.
                        $password_input = hash("sha512", $salt . $password_input);
                        $maidenname_input = hash("sha512", $salt . $maidenname_input);
                        $elemschool_input = hash("sha512", $salt . $elemschool_input);
                        $road_input = hash("sha512", $salt . $road_input);

                        // Check if user exists already in database
                        $stmt = $link->prepare("SELECT id FROM users WHERE username=?"); // prepare sql statement for execution
                        $stmt->bind_param("s", $username_input); // bind variables to the parameter markers of the prepared statement
                        $stmt->execute(); // executed prepared statement
                        $result = $stmt->get_result(); // get result of executed statement
                        $stmt->close();

                        if ($result != false)
                        {
                            /* determine number of rows result set */
                            $row_cnt = mysqli_num_rows($result);
                            mysqli_free_result($result);
                        }
                        else
                        {
                            echo "<script type=\"text/javascript\">alert(\"Last SQL query error: " . $link->error . "\")</script>";
                        }

                        if ($row_cnt == 0)
                        {
                            //We count the number of users to give an ID to this one
                            if ($result = $link->query('SELECT id FROM users'))
                            {
                                /* determine number of rows result set */
                                $dn2 = mysqli_num_rows($result);
                                /* close result set */
                                mysqli_free_result($result);
                                $id = $dn2 + 1;
                            }
                            else
                            {
                                // show last error
                                echo "<script type=\"text/javascript\">alert(\"Last SQL query error: " . $link->error . "\")</script>";
                            }

                            //Check if the password recovery questions are valid
                            $password_recovery_valid = false;
                            if (($_POST['maidenname'] == $_POST['maidennamerepeat']) and ($_POST['elemschool'] == $_POST['elemschoolrepeat']) and ($_POST['road'] == $_POST['roadrepeat']))
                            {
                                $password_recovery_valid = true;
                            }
                            else
                            {
                                //Repeated answers to the password recovery questions are not always the same
                                $form = true;
                                $message = 'The repeated answers to the password recovery questions are not always the same. Please try again and ensure the repeated answers are the same.';
                            }

                            if ($password_recovery_valid == true)
                            {
                                // store user data in database
                                $stmt = $link->prepare("INSERT INTO users(id, username, password, email, maidenname, elemschool, road, signup_date, salt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"); // prepare sql statement for execution
                                $stmt->bind_param("isssssssi", $id, $username_input, $password_input, $email_input, $maidenname_input, $elemschool_input, $road_input, $time, $salt); // bind variables to the parameter markers of the prepared statement
                                $time = time();
                                $result = $stmt->execute(); // executed prepared statement
                                $stmt->close();
                                if ($result)
                                {
                                    //We dont display the form
                                    $form = false;

                                    echo "<div class=\"message\">You have successfuly been registered. You can log in now.<br />";
                                    echo "<a href=\"access.php\">Login</a></div>";
                                }
                                else
                                {
                                    //Otherwise, we say that an error occured
                                    $form = true;
                                    $message = 'An error occurred while signing up.';
                                    echo "<script type=\"text/javascript\">alert(\"Last SQL query error: " . $link->error . "\")</script>";
                                }
                            }
                        }
                        else
                        {
                            //Otherwise, we say the username is not available
                            $form = true;
                            $message = 'The username you want to use is not available, please choose another one.';
                        }
                    }
                    else
                    {
                        //Otherwise, we say the email is not valid
                        $form = true;
                        $message = 'The email you entered is not valid.';
                    }
                }
                else
                {
                    //Otherwise, we say the password is too weak
                    $form = true;
                    $message = '';
                    foreach ($errors as $item) $message = $message . $item . "<BR>";
                }
            }
            else
            {
                //Otherwise, we say the passwords are not identical
                $form = true;
                $message = 'The passwords you entered are not identical.';
            }
        }
        else
        {
            //Otherwise, we say the username are not identical
            $form = true;
            $message = 'The username you entered are not identical. Only letter and numberic';

        }
    }
    else
    {
        //Security questions are empty
        $form = true;
        $message = 'One or more of the security questions are not answered, please answer them with words for security reasons.';
    }
}
else
{
    $form = true;
}

if ($form)
{
    //We display the form again
    //We display a message if necessary
    if (isset($message))
    {
        echo '<br><div class="message">' . $message . '</div>';
    }
?>
		<div class="content">
			<form action="register.php" method="post">
				Please fill the following form to sign up:<br />
				<p style="font-size:14px;color:Red;">(Password requires 8 characters minimum and must include at least one number, one lowercase letter, one uppercase letter and one symbol)</p><br />
				<div class="center">
					<p style="text-align:left;text-decoration: underline;">Enter user data:</p><br />
					<label for="username" style="text-align:left;">Username</label><input type="text" name="username" value="<?php if (isset($_POST['username']))
    {
        echo htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8');
    } ?>" required/><br />
					<label for="email" style="text-align:left;">Email</label><input type="text" name="email" value="<?php if (isset($_POST['email']))
    {
        echo htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8');
    } ?>" required /><br />
					<label for="password" style="text-align:left;">Password</label><input type="password" name="password" required/><br />					
					<label for="passverif" style="text-align:left;">Repeat Password</label><input type="password" name="passverif" required/><br /><br />
					<p style="text-align:left;text-decoration: underline;">Answer the following questions, they are asked for in case you forget your password to setup a new one:</p><br />
					<label for="maidenname" style="width: 400px;text-align:left;">Your mother's maiden name?</label><input type="password" name="maidenname" required/><br />
					<label for="maidennamerepeat" style="width: 400px;text-align:left;">Repeat: your mother's maiden name?</label><input type="password" name="maidennamerepeat" required /><br /><br />
					<label for="elemschool" style="width: 400px;text-align:left;">What elementary school did you attend?</label><input type="password" name="elemschool" required/><br />
					<label for="elemschoolrepeat" style="width: 400px;text-align:left;">Repeat: what elementary school did you attend?</label><input type="password" name="elemschoolrepeat" required/><br /><br />
					<label for="road" style="width: 400px;text-align:left;">What is the name of the road you grew up on?</label><input type="password" name="road" required/><br />
					<label for="roadrepeat" style="width: 400px;text-align:left;">Repeat: what is the name of the road you grew up on?</label><input type="password" name="roadrepeat" required/><br /><br />					
					<input type="submit" value="Register" />
				</div>
			</form>
		</div>
<?php
}
?>
		<div class="foot"><a href="<?php echo $url_home; ?>">Go to start page</a></div>
	</body>
</html>
