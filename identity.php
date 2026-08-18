<?php
$host = "localhost";
$dbname = "CHANGEME";
$dbuser = "CHANGEME";
$dbpass = "CHANGEME";

session_start();
if (!isset($_SESSION['user_id']) or isset($_POST['name']))
{
	$identity = new mysqli($host, $dbuser, $dbpass, $dbname);
	$identity->set_charset('utf8mb4');
	if (!isset($_SESSION['user_id']))
	{
		$identity->query("insert into user (display_name, created_at, last_seen_at) values ('', now(), now())");
		$_SESSION['user_id'] = $identity->insert_id;
		$identity->query("update user set display_name = 'Gast".$_SESSION['user_id']."' where id = ".$_SESSION['user_id']);
	}
	if (isset($_POST['name']))
	{
		$identity->query("update user set display_name = '".$identity->real_escape_string($_POST['name'])."' where id = ".$_SESSION['user_id']);
	}
	$identity->query("update user set last_seen_at = now() where id = ".$_SESSION['user_id']);
	$_SESSION['player'] = $identity->query("select display_name from user where id = ".$_SESSION['user_id'])->fetch_array()['display_name'];
}
$guest = (substr($_SESSION['player'], 0, 4) == "Gast");
$writeplayer = ($guest)?"Gast":$_SESSION['player'];

function identityForm()
{
	global $writeplayer;
	echo '<form method="post"><input style="width: 150px; margin-bottom: 10px;" type="text" name="name" value="'.htmlspecialchars($writeplayer, ENT_QUOTES, 'UTF-8').'"><br>
			<input  style="width: 170px; margin-bottom: 10px;" type="submit" value="Name &auml;ndern"></form>';
}
