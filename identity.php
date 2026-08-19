<?php
$host = "localhost";
$dbname = "CHANGEME";
$dbuser = "CHANGEME";
$dbpass = "CHANGEME";

session_start();
$identity = null;
$namemessage = "";
if (!isset($_SESSION['user_id']))
{
	$_SESSION['user_id'] = 0;
	$_SESSION['player'] = "Gast";
}
if (isset($_POST['name']))
{
	identify();
	$name = mb_substr(trim($_POST['name']), 0, 32);
	if ($name == "")
	{
		$namemessage = "Der Name darf nicht leer sein.";
		$name = "Gast".$_SESSION['user_id'];
	}
	elseif (preg_match('/^gast\d/i', $name) and $name != "Gast".$_SESSION['user_id'])
	{
		$namemessage = "Namen aus Gast und einer Nummer sind reserviert.";
		$name = "Gast".$_SESSION['user_id'];
	}
	elseif ($name != trim($_POST['name']))
		$namemessage = "Der Name wurde auf 32 Zeichen gekürzt.";
	$identity->execute_query("update user set display_name = ?, last_seen_at = now() where id = ?", [$name, $_SESSION['user_id']]);
	$_SESSION['player'] = $name;
}
$guest = (substr($_SESSION['player'], 0, 4) == "Gast");
$writeplayer = ($guest)?"Gast":$_SESSION['player'];

function identify()
{
	global $host, $dbname, $dbuser, $dbpass, $identity;
	if (!$identity)
	{
		$identity = new mysqli($host, $dbuser, $dbpass, $dbname);
		$identity->set_charset('utf8mb4');
	}
	if (!$_SESSION['user_id'])
	{
		$identity->execute_query("insert into user (display_name, created_at, last_seen_at) values ('', now(), now())");
		$_SESSION['user_id'] = $identity->insert_id;
		$_SESSION['player'] = "Gast".$_SESSION['user_id'];
		$identity->execute_query("update user set display_name = ? where id = ?", [$_SESSION['player'], $_SESSION['user_id']]);
	}
}

function identityForm()
{
	global $writeplayer, $namemessage;
	if ($namemessage) { echo '<p class="warning">'.$namemessage.'</p>'; }
	echo '<form method="post"><input style="width: 150px; margin-bottom: 10px;" type="text" name="name" value="'.htmlspecialchars($writeplayer, ENT_QUOTES, 'UTF-8').'"><br>
			<input  style="width: 170px; margin-bottom: 10px;" type="submit" value="Name &auml;ndern"></form>';
}
