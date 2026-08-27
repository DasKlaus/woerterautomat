<?php
$host = "localhost";
$dbname = "CHANGEME";
$dbuser = "CHANGEME";
$dbpass = "CHANGEME";

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => true]);
session_start();
$identity = null;
$identitymessage = "";
if (!isset($_SESSION['display_name']))
{
	$_SESSION['user_id'] = 0;
	$_SESSION['display_name'] = "";
}
if (!$_SESSION['user_id'] and strlen($_COOKIE['code'] ?? '') == 32)
{
	identityConnect();
	$row = $identity->execute_query("select id, display_name from user where code = ?", [$_COOKIE['code']])->fetch_assoc();
	if ($row)
	{
		session_regenerate_id(true);
		$_SESSION['user_id'] = $row['id'];
		$_SESSION['display_name'] = $row['display_name'];
		$identity->execute_query("update user set last_seen_at = now() where id = ?", [$_SESSION['user_id']]);
		identityRemember($_COOKIE['code']);
	}
	else
	{
		identityForget();
		$identitymessage = "Der gespeicherte Zugang ist ungültig. Möglicherweise wurde der Code in einer anderen Sitzung neu erzeugt.";
	}
}
switch ($_POST['do'] ?? '')
{
	case "name":
		identify();
		if (identityRestriction() !== false)
		{
			$identitymessage = "Die Namensänderung wurde gesperrt.";
			break;
		}
		$name = mb_substr(trim($_POST['name'] ?? ''), 0, 32);
		if ($name != trim($_POST['name'] ?? ''))
			$identitymessage = "Der Name wurde auf 32 Zeichen gekürzt.";
		$identity->execute_query("update user set display_name = ?, last_seen_at = now() where id = ?", [$name, $_SESSION['user_id']]);
		$_SESSION['display_name'] = $name;
		break;
	case "remember":
		identify();
		identityRemember(identityCode());
		break;
	case "forget":
		identityForget();
		break;
	case "newcode":
		identify();
		$code = bin2hex(random_bytes(16));
		$identity->execute_query("update user set code = ? where id = ?", [$code, $_SESSION['user_id']]);
		if (isset($_COOKIE['code'])) { identityRemember($code); }
		break;
	case "usecode":
		$code = trim($_POST['code'] ?? '');
		identityConnect();
		$row = $identity->execute_query("select id, display_name from user where code = ?", [$code])->fetch_assoc();
		if ($row)
		{
			session_regenerate_id(true);
			$_SESSION['user_id'] = $row['id'];
			$_SESSION['display_name'] = $row['display_name'];
			$identity->execute_query("update user set last_seen_at = now() where id = ?", [$_SESSION['user_id']]);
			if (isset($_COOKIE['code'])) { identityRemember($code); }
		}
		else
			$identitymessage = "Der Code ist unbekannt.";
		break;
	case "logout":
		identityForget();
		session_regenerate_id(true);
		$_SESSION['user_id'] = 0;
		$_SESSION['display_name'] = "";
		break;
}

function identityConnect()
{
	global $host, $dbname, $dbuser, $dbpass, $identity;
	if (!$identity)
	{
		$identity = new mysqli($host, $dbuser, $dbpass, $dbname);
		$identity->set_charset('utf8mb4');
	}
}

function identify()
{
	global $identity;
	identityConnect();
	if (!$_SESSION['user_id'])
	{
		$identity->execute_query("insert into user (display_name, code, created_at, last_seen_at) values ('', ?, now(), now())", [bin2hex(random_bytes(16))]);
		$_SESSION['user_id'] = $identity->insert_id;
	}
}

function identityCode()
{
	global $identity;
	identityConnect();
	return (string)$identity->execute_query("select code from user where id = ?", [$_SESSION['user_id']])->fetch_column();
}

function identityRestriction()
{
	global $identity;
	if (!$_SESSION['user_id']) { return false; }
	identityConnect();
	return $identity->execute_query("select restriction_reason from user where id = ? and restriction is not null", [$_SESSION['user_id']])->fetch_column();
}

function identityRemember($code)
{
	setcookie('code', $code, ['expires' => time() + 31536000, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
	$_COOKIE['code'] = $code;
}

function identityForget()
{
	setcookie('code', '', ['expires' => 1, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
	unset($_COOKIE['code']);
}

function identityMessage()
{
	global $identitymessage;
	if ($identitymessage) { echo '<p class="warning">'.htmlspecialchars($identitymessage, ENT_QUOTES, 'UTF-8').'</p>'; }
}

function identityForm()
{
	echo '<h3>Name</h3>';
	$reason = identityRestriction();
	if ($reason !== false)
		echo '<p class="warning">Die Namensänderung wurde gesperrt. '.htmlspecialchars((string)$reason, ENT_QUOTES, 'UTF-8').'</p>';
	else
		echo '
		<p>Der Name ist für alle sichtbar. Beleidigendes, Privates oder Anstößiges ist nicht zulässig, Verstöße können über das Impressum gemeldet werden. Unzulässige Namen werden ohne Ankündigung anonymisiert.</p>
		<form method="post" class="identity">
			<input type="text" name="name" value="'.htmlspecialchars($_SESSION['display_name'], ENT_QUOTES, 'UTF-8').'">
			<button type="submit" name="do" value="name">Name &auml;ndern</button>
		</form>';
	if ($_SESSION['user_id'])
	{
		echo '<h3>Angemeldet bleiben</h3>
			<p>Ein Cookie h&auml;lt die Anmeldung auf diesem Ger&auml;t ein Jahr lang und verl&auml;ngert sich bei jedem Besuch. Ohne Cookie endet die Anmeldung mit der Sitzung.</p>
			<form method="post" class="identity">';
		if (isset($_COOKIE['code']))
			echo '<button type="submit" name="do" value="forget">Cookie l&ouml;schen</button>';
		else
			echo '<button type="submit" name="do" value="remember">Angemeldet bleiben</button>';
		echo '</form>';
	}
	echo '<h3>Authentifizierungs-Code</h3>
		<p>Mit dem Code ist ein Wiederanmelden in einem anderen Browser oder nach Ende der Sitzung möglich. Er sollte notiert und nicht weitergegeben werden.</p>';
	if ($_SESSION['user_id'])
		echo '<p class="warning">Ein neuer Code macht den bisherigen ung&uuml;ltig und beendet den Zugang in allen anderen Browsern.</p>
			<form method="post" class="identity">
			  <div class="codebox">
			    <input type="text" class="code" value="'.htmlspecialchars(identityCode(), ENT_QUOTES, 'UTF-8').'" readonly>
			    <button type="button" class="copy" title="Code kopieren" onclick="this.previousElementSibling.select(); navigator.clipboard.writeText(this.previousElementSibling.value);">⧉</button>
			  </div>
			  <button type="submit" name="do" value="newcode">Neuen Code erzeugen</button>
			</form>';
	if (!$_SESSION['user_id']) 
		echo '<form method="post" class="identity">
				<input type="text" name="code" value="">
				<button type="submit" name="do" value="usecode">Mit Code anmelden</button>
			</form>';
	if ($_SESSION['user_id'])
		echo '<h3>Abmelden</h3>
			<p class="warning">Ohne den Code ist der Zugang zu Name und Historie nach dem Abmelden dauerhaft verloren.</p>
			<form method="post" class="identity">
			<button type="submit" name="do" value="logout" onclick="return confirm(\'Ohne den Code ist der Zugang zu Name und Historie dauerhaft verloren. Wirklich abmelden?\');">Abmelden</button>
			</form>';
}