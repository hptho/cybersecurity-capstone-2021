<?php
	include('config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="<?php echo $design; ?>/style.css" rel="stylesheet" title="Style" />
		<title>Database Dump</title>
	</head>
	<body>

<?php
//We display all tables
//
//Users
//We get all rows of users

echo "user<BR>";
echo "id,username,password,e-mail,maidenname,elemschool,road,signup_date,salt<BR>";
$req = mysqli_query($link, 'select * from users');

while($dnn = mysqli_fetch_array($req))
{
	echo $dnn['id'].",";
	echo htmlentities($dnn['username'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['password'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['email'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['maidenname'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['elemschool'], ENT_QUOTES, 'UTF-8').",";	
	echo htmlentities($dnn['road'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities(date("Y-m-d H:i:s", $dnn['signup_date']), ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['salt'], ENT_QUOTES, 'UTF-8')."<BR>";
}

//Users
//We get all rows of users
echo "<BR>messages<BR>";
echo "id,id2,title,user1,user2,message,timestamp,user1read,user2read,tag<BR>";
$req = mysqli_query($link, 'select * from messages');

while($dnn = mysqli_fetch_array($req))
{
	echo $dnn['id'].",";
	echo $dnn['id2'].",";
	echo htmlentities($dnn['title'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['user1'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['user2'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['message'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities(date("Y-m-d H:i:s", $dnn['timestamp']), ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['user1read'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['user2read'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['tag'], ENT_QUOTES, 'UTF-8')."<BR>";
}

//Encryption keys for messages
//We get all rows of users
echo "<BR>messagekeys<BR>";
echo "user1,user2,mskey<BR>";
$req = mysqli_query($link, 'select * from messagekeys');

while($dnn = mysqli_fetch_array($req))
{
	echo htmlentities($dnn['user1'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['user2'], ENT_QUOTES, 'UTF-8').",";
	echo htmlentities($dnn['mskey'], ENT_QUOTES, 'UTF-8')."<BR>";
}
?>
		<br /><div class="foot"><a href="<?php echo $url_home; ?>">Go to start page</a></div>
	</body>
</html>