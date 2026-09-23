<?php
$host = "localhost";
$dbname = "CHANGEME";
$dbuser = "CHANGEME";
$dbpass = "CHANGEME";

# Every text this file shows, in every language it knows. A key it has no entry for is shown as it
# is, which is how a moderator's own wording reaches the page beside the boilerplate reasons.
$identitytexts = [
'de' => [
	'invalidcode' => 'Der gespeicherte Zugang ist ungültig. Möglicherweise wurde der Code in einer anderen Sitzung neu erzeugt.',
	'namelocked' => 'Die Namensänderung wurde gesperrt.',
	'nametruncated' => 'Der Name wurde auf 32 Zeichen gekürzt.',
	'unknowncode' => 'Der Code ist unbekannt.',
	'language' => 'Sprache',
	'name' => 'Name',
	'namenotice' => 'Der Name ist für alle sichtbar. Beleidigendes, Privates oder Anstößiges ist nicht zulässig, Verstöße können über das Impressum gemeldet werden. Unzulässige Namen werden ohne Ankündigung anonymisiert.',
	'cookienotice' => 'Ein Cookie h&auml;lt die Anmeldung auf diesem Ger&auml;t ein Jahr lang und verl&auml;ngert sich bei jedem Besuch. Ohne Cookie endet die Anmeldung mit der Sitzung.',
	'setname' => 'Name setzen',
	'setnamecookie' => 'Name und Cookie setzen',
	'changename' => 'Name &auml;ndern',
	'changenameall' => 'Name r&uuml;ckwirkend &auml;ndern',
	'staysignedin' => 'Angemeldet bleiben',
	'forgetcookie' => 'Cookie l&ouml;schen',
	'authcode' => 'Authentifizierungs-Code',
	'codenotice' => 'Mit dem Code ist ein Wiederanmelden in einem anderen Browser oder nach Ende der Sitzung möglich. Er sollte notiert und nicht weitergegeben werden.',
	'usecode' => 'Mit Code anmelden',
	'copycode' => 'Code kopieren',
	'newcodewarning' => 'Ein neuer Code macht den bisherigen ung&uuml;ltig und beendet den Zugang in allen anderen Browsern.',
	'newcode' => 'Neuen Code erzeugen',
	'signout' => 'Abmelden',
	'signoutwarning' => 'Ohne den Code ist der Zugang zu Name und Historie nach dem Abmelden dauerhaft verloren.',
	'signoutconfirm' => 'Ohne den Code ist der Zugang zu Name und Historie dauerhaft verloren. Wirklich abmelden?',
],
'en' => [
	'invalidcode' => 'The stored access is not valid. The code may have been regenerated in another session.',
	'namelocked' => 'Changing the name has been blocked.',
	'nametruncated' => 'The name was shortened to 32 characters.',
	'unknowncode' => 'The code is unknown.',
	'language' => 'Language',
	'name' => 'Name',
	'namenotice' => 'The name is visible to everyone. Insulting, private or offensive names are not allowed; violations can be reported through the legal notice. Names that are not allowed are anonymised without notice.',
	'cookienotice' => 'A cookie keeps you signed in on this device for a year and renews on every visit. Without the cookie, you are signed out on session end.',
	'setname' => 'Set name',
	'setnamecookie' => 'Set name and cookie',
	'changename' => 'Change name',
	'changenameall' => 'Change name retroactively',
	'staysignedin' => 'Stay signed in',
	'forgetcookie' => 'Delete cookie',
	'authcode' => 'Authentication code',
	'codenotice' => 'The code lets you sign in again in another browser or after the session has ended. Note it down and do not pass it on.',
	'usecode' => 'Sign in with code',
	'copycode' => 'Copy code',
	'newcodewarning' => 'A new code invalidates the previous one and ends access in every other browser.',
	'newcode' => 'Generate new code',
	'signout' => 'Sign out',
	'signoutwarning' => 'Without the code, access to the name and history is permanently lost after signing out.',
	'signoutconfirm' => 'Without the code, access to the name and history is permanently lost. Really sign out?',
],
];

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
	$row = $identity->execute_query("select id, display_name, language from user where code = ?", [$_COOKIE['code']])->fetch_assoc();
	if ($row)
	{
		session_regenerate_id(true);
		$_SESSION['user_id'] = $row['id'];
		$_SESSION['display_name'] = $row['display_name'];
		if ($row['language']) { $_SESSION['language'] = $row['language']; }
		$identity->execute_query("update user set last_seen_at = now() where id = ?", [$_SESSION['user_id']]);
		identityRemember($_COOKIE['code']);
	}
	else
	{
		identityForget();
		$identitymessage = "invalidcode";
	}
}
switch ($_POST['do'] ?? '')
{
	case "namecookie":
	case "renameall":
	case "name":
		identify();
		if (identityRestriction() !== false)
		{
			$identitymessage = "namelocked";
			break;
		}
		$name = mb_substr(trim($_POST['name'] ?? ''), 0, 32);
		if ($name != trim($_POST['name'] ?? ''))
			$identitymessage = "nametruncated";
		$identity->execute_query("update user set display_name = ?, last_seen_at = now() where id = ?", [$name, $_SESSION['user_id']]);
		$_SESSION['display_name'] = $name;
		if ($_POST['do'] == "namecookie") { identityRemember(identityCode()); }
		break;
	// the choice is the session's; only an account that exists already carries it over to the next one
	case "language":
		$_SESSION['language'] = substr($_POST['language'] ?? '', 0, 2);
		if ($_SESSION['user_id'])
		{
			identityConnect();
			$identity->execute_query("update user set language = ? where id = ?", [$_SESSION['language'], $_SESSION['user_id']]);
		}
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
		$row = $identity->execute_query("select id, display_name, language from user where code = ?", [$code])->fetch_assoc();
		if ($row)
		{
			session_regenerate_id(true);
			$_SESSION['user_id'] = $row['id'];
			$_SESSION['display_name'] = $row['display_name'];
			if ($row['language']) { $_SESSION['language'] = $row['language']; }
			$identity->execute_query("update user set last_seen_at = now() where id = ?", [$_SESSION['user_id']]);
			if (isset($_COOKIE['code'])) { identityRemember($code); }
		}
		else
			$identitymessage = "unknowncode";
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

function identityText($language, $key)
{
	global $identitytexts;
	return $identitytexts[$language][$key] ?? $key;
}

function identityMessage($language)
{
	global $identitymessage;
	if ($identitymessage) { echo '<p class="warning">'.htmlspecialchars(identityText($language, $identitymessage), ENT_QUOTES, 'UTF-8').'</p>'; }
}

# Only called by projects that have more than one language; $languages maps each code to its own name.
function identityLanguageForm($language, $languages)
{
	echo '<h3>'.identityText($language, 'language').'</h3>
		<form method="post" class="identity language">
		<input type="hidden" name="do" value="language">';
	foreach ($languages as $code => $name)
		echo '<label><input type="radio" name="language" value="'.htmlspecialchars($code, ENT_QUOTES, 'UTF-8').'"'.($code == $language ? ' checked' : '')
			.' onchange="this.form.submit();">'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'</label>';
	echo '</form>';
}

function identityForm($language)
{
	echo '<h3>'.identityText($language, 'name').'</h3>';
	$reason = identityRestriction();
	if ($reason !== false)
		echo '<p class="warning">'.identityText($language, 'namelocked').' '.htmlspecialchars(identityText($language, (string)$reason), ENT_QUOTES, 'UTF-8').'</p>';
	else
		echo '<p>'.identityText($language, 'namenotice').'</p>';
	if (!$_SESSION['user_id'])
		echo '<p>'.identityText($language, 'cookienotice').'</p>
			<form method="post" class="identity namechange">
				<input type="text" name="name" value="">
				<button type="submit" name="do" value="name">'.identityText($language, 'setname').'</button>
				<button type="submit" name="do" value="namecookie">'.identityText($language, 'setnamecookie').'</button>
			</form>';
	else
	{
		if ($reason === false)
			echo '<form method="post" class="identity namechange">
				<input type="text" name="name" value="'.htmlspecialchars($_SESSION['display_name'], ENT_QUOTES, 'UTF-8').'">
				<button type="submit" name="do" value="name">'.identityText($language, 'changename').'</button>
				<button type="submit" name="do" value="renameall">'.identityText($language, 'changenameall').'</button>
			</form>';
		echo '<h3>'.identityText($language, 'staysignedin').'</h3>
			<p>'.identityText($language, 'cookienotice').'</p>
			<form method="post" class="identity">';
		if (isset($_COOKIE['code']))
			echo '<button type="submit" name="do" value="forget">'.identityText($language, 'forgetcookie').'</button>';
		else
			echo '<button type="submit" name="do" value="remember">'.identityText($language, 'staysignedin').'</button>';
		echo '</form>';
	}
	echo '<h3>'.identityText($language, 'authcode').'</h3>
		<p>'.identityText($language, 'codenotice').'</p>';
	if (!$_SESSION['user_id'])
		echo '<form method="post" class="identity">
				<input type="text" name="code" value="">
				<button type="submit" name="do" value="usecode">'.identityText($language, 'usecode').'</button>
			</form>';
	if ($_SESSION['user_id'])
		echo '<p class="warning">'.identityText($language, 'newcodewarning').'</p>
			<form method="post" class="identity">
			  <div class="codebox">
			    <input type="text" class="code" value="'.htmlspecialchars(identityCode(), ENT_QUOTES, 'UTF-8').'" readonly>
			    <button type="button" class="copy" title="'.identityText($language, 'copycode').'" onclick="this.previousElementSibling.select(); navigator.clipboard.writeText(this.previousElementSibling.value);">⧉</button>
			  </div>
			  <button type="submit" name="do" value="newcode">'.identityText($language, 'newcode').'</button>
			</form>
			<h3>'.identityText($language, 'signout').'</h3>
			<p class="warning">'.identityText($language, 'signoutwarning').'</p>
			<form method="post" class="identity">
			<button type="submit" name="do" value="logout" onclick="return confirm(\''.identityText($language, 'signoutconfirm').'\');">'.identityText($language, 'signout').'</button>
			</form>';
}
