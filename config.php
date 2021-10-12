<?php
session_start();

//Access to ClearDB DataBase. CLEARDB_DATABASE_URL is an environment variable set in Heroku project.
$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = substr($url["path"], 1);
$link = new mysqli($server, $username, $password, $database);

// check connection to database
if ($mysqli -> connect_errno)
{
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
  $db_access = "Failed to connect to MySQL: " . $link -> connect_error;
}
else
{
	$db_access = 'DB access successful';
}

//Home page file name
$url_home = 'index.php';

//Design Name
$design = 'default';

// Create tables in database if not existing yet
// code for development: comment out if a table needs to be deleted
// $delete_users_table = "DROP TABLE users";
// if ($link->query($delete_users_table) === TRUE) 
// {
	// echo '<script type="text/javascript">alert("Table users deleted successfully.")</script>';
// } 
// else 
// {
	// echo "<script type=\"text/javascript\">alert(\"Error deleting table users: " . $link->error . "\")</script>";
// }

// $delete_messages_table = "DROP TABLE messages";
// if ($link->query($delete_messages_table) === TRUE) 
// {
	// echo '<script type="text/javascript">alert("Table messages deleted successfully.")</script>';
// } 
// else 
// {
	// echo "<script type=\"text/javascript\">alert(\"Error deleting table messages: " . $link->error . "\")</script>";
// }

// $delete_messages_keys_table = "DROP TABLE messagekeys";
// if ($link->query($delete_messages_keys_table) === TRUE) 
// {
	// echo '<script type="text/javascript">alert("Table messagekeys deleted successfully.")</script>';
// } 
// else 
// {
	// echo "<script type=\"text/javascript\">alert(\"Error deleting table messagekeys: " . $link->error . "\")</script>";
// }

// users table
$users_table = "CREATE TABLE users (
  id bigint(20) NOT NULL,
  username varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  maidenname varchar(255) NOT NULL,
  elemschool varchar(255) NOT NULL,
  road varchar(255) NOT NULL,
  signup_date int(10) NOT NULL,
  salt varchar(255) NOT NULL
)";

$exists = $link->query("select 1 from users");
if($exists == FALSE)
{
	echo '<script type="text/javascript">alert("Table users does not exist in database, creating it.")</script>';

    if ($link->query($users_table) === TRUE) 
    {
		echo '<script type="text/javascript">alert("Table users created successfully.")</script>';
    } 
	else 
	{
		echo "<script type=\"text/javascript\">alert(\"Error creating table users: " . $link->error . "\")</script>";
	}
}

// messages table
$messages_table = "CREATE TABLE messages (
  id bigint(20) NOT NULL,
  id2 int(11) NOT NULL,
  title varchar(256) NOT NULL,
  user1 bigint(20) NOT NULL,
  user2 bigint(20) NOT NULL,
  message text NOT NULL,
  timestamp int(10) NOT NULL,
  user1read varchar(3) NOT NULL,
  user2read varchar(3) NOT NULL,
  tag varchar(255) NOT NULL
)";

$exists = $link->query("select 1 from messages");
if($exists == FALSE)
{
	echo '<script type="text/javascript">alert("Table messages does not exist in database, creating it.")</script>';

    if ($link->query($messages_table) === TRUE) 
    {
		echo '<script type="text/javascript">alert("Table messages created successfully.")</script>';
    } 
	else 
	{
		echo "<script type=\"text/javascript\">alert(\"Error creating table messages: " . $link->error . "\")</script>";
	}
}

// message keys table
$messages_keys_table = "CREATE TABLE messagekeys (
  user1 bigint(20) NOT NULL,
  user2 bigint(20) NOT NULL,
  mskey varchar(255) NOT NULL
)";

$exists = $link->query("select 1 from messagekeys");
if($exists == FALSE)
{
	echo '<script type="text/javascript">alert("Table messagekeys does not exist in database, creating it.")</script>';

    if ($link->query($messages_keys_table) === TRUE) 
    {
		echo '<script type="text/javascript">alert("Table messagekeys created successfully.")</script>';
    } 
	else 
	{
		echo "<script type=\"text/javascript\">alert(\"Error creating table messagekeys: " . $link->error . "\")</script>";
	}
}

// checkPassword: Check password strength. Returns true if it is Ok.
// $pwd receives the password to test.
// $errors returns the non-compliant items of the provided password

function checkPassword($pwd, &$errors) 
{
	$errors_init = $errors;

	if (strlen($pwd) < 8) $errors[] = "Password must have at least 8 characters!";
	if (!preg_match("#[0-9]+#", $pwd)) $errors[] = "Password must include at least one number!";
	if (!preg_match("#[a-zA-Z]+#", $pwd)) $errors[] = "Password must include at least one letter!";
	if (!preg_match("#[a-z]+#", $pwd)) $errors[] .= "Password must include at least one lowercase letter!";
	if (!preg_match("#[A-Z]+#", $pwd)) $errors[] .= "Password must include at least one uppercase letter!";
	if (!preg_match("#\W+#", $pwd)) $errors[] .= "Password must include at least one symbol!";

	return ($errors == $errors_init);
}

// getKey: Set and retrieve password for message database encryption.
// $user1 and $user2: users communicating each other.

function getKey($user1, $user2) 
{
	global $link;

	//Message DataBase. Access data cryptography hardcoded.
	$cipher = "aes-128-cbc";
	$ivlen  = openssl_cipher_iv_length($cipher);
	$iv		= base64_decode("rOlkVm2CPP7VOr47fNveCw=="); // A hardcoded random iv  of 128 bits.
	$dbkey  = base64_decode("+4TG74h6hbdMK8LaidUPjA=="); // A hardcoded random key of 128 bits.

	// Just to make $user1 < $user2. Swap if necessary. 
	if ($user1 > $user2) 
	{
		$tmp = $user1;
		$user1 = $user2;
		$user2 = $tmp;
	};

	$method = openssl_get_cipher_methods();
	if (in_array($cipher, $method)) 
	{
		$key = base64_encode(openssl_random_pseudo_bytes(24)); // A random key of 192 bits to be used in case of being the first message.
		$encrypted_key = openssl_encrypt($key, $cipher, $dbkey, 0, $iv);

		$req = mysqli_query($link, 'select * from messagekeys where user1="'.$user1.'" and user2="'.$user2.'"');
		$dn  = mysqli_num_rows($req);
		$dat = mysqli_fetch_array($req);

		// No key. First message. Create a new key.
		if ($dn == 0)
		{
			mysqli_query($link, 'insert into messagekeys(user1, user2, mskey) values ('.$user1.', "'.$user2.'", "'.$encrypted_key.'")');
		}
		else 
		{
			$cryp_key = $dat['mskey'];
			$key = openssl_decrypt($cryp_key, $cipher, $dbkey, 0, $iv);
		}
		return $key;
	}
	else return false;
}
?>
