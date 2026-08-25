<?php
header('Content-Type: text/html; charset=UTF-8');
require_once("config.php");
require_once("identity.php");

$go = $_GET['go'] ?? 'anleitung';
$message = "";
if (($_GET["go"] ?? "") == "neu" and isset($_POST["new"]) and ($_POST["website"] ?? "") == "" and $_SESSION['user_id'])
{
	$sourceword = preg_replace('/[^\p{L}]/u', '', strtolower($_POST['word'] ?? ''));
	$reason = identityRestriction();
	if ($reason !== false)
		$message = "Das Erstellen von Spielen wurde gesperrt. ".$reason;
	elseif (strlen($sourceword) < 3)
		$message = "Gib ein Wort mit mindestens drei Buchstaben ein.";
	elseif (strlen($sourceword) > 64)
		$message = "Das Wort ist zu lang. Mehr als 64 Buchstaben sind nicht möglich.";
	else
	{
		$recent = $mysql->execute_query("select sum(created_at > now() - interval 1 minute) as lastminute, count(*) as lasthour
			from game where created_by = ? and created_at > now() - interval 1 hour", [$_SESSION['user_id']])->fetch_assoc();
		if ($recent['lastminute'] > 0)
			$message = "Du hast gerade eben ein Spiel gestartet. Warte eine Minute, bevor du das nächste startest.";
		elseif ($recent['lasthour'] >= 10)
			$message = "Du hast in der letzten Stunde zehn Spiele gestartet. Versuch es später noch einmal.";
		else
		{
			$flexion = (isset($_POST['flexion'])) ? 1 : 0;
			$umlauts = (isset($_POST['umlauts'])) ? 1 : 0;
			$private = (isset($_POST['private'])) ? 1 : 0;
			$players = ($_POST['players'] ?? 0);
			$language = ($_POST['language'] ?? 'de');
			$mysql->execute_query("insert into game (source_word, language, flexion, umlauts, private, maxplayers, created_by, created_by_name, created_at, last_activity_at)
				values (?, ?, ?, ?, ?, ?, ?, ?, now(), now())", [$sourceword, $language, $flexion, $umlauts, $private, $players, $_SESSION['user_id'], $_SESSION['display_name']]);
			$id = $mysql->insert_id;
			$mysql->execute_query("insert into player (game_id, user_id, display_name, joined_at, activity) values (?, ?, ?, now(), now())",
				[$id, $_SESSION['user_id'], $_SESSION['display_name']]);
			header("Location: ?go=game&game=".$id);
			exit();
		}
	}
}
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<title>W&ouml;rterautomat</title>
		<meta name="robots" content="index,nofollow">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="style.css" type="text/css" rel="stylesheet" media="screen">
	</head>
	<body>
	<div id="wrapper">
		<h1 id="letters">woerterautomat</h1>
		<div id="lettersortbuttons" class="collapsible sort">
			<div class="modes">
			<button type="button" onClick="select(this); writeletters(randomstring(originalword));">mischen</button>
			<button id="anagram" type="button" onClick="select(this); writeletters(anagram(target, prepared_words));">sortieren</button>
			</div>
			<div class="modes">
			<button type="button" onClick="select(this); writeletters(sortstring(originalword));">alphabetisch</button>
			<button type="button" class="selected" onClick="select(this); writeletters(originalword);">original</button>
			</div>
		</div>
		<script src="index.js"></script>
		<div id="menu">
			<div class="collapsible nav">
				<a href="?go=user"<?php if ($go=='user') echo ' class="selected"'; ?>>Profil<?php if ($_SESSION['user_id']) echo ': '.htmlspecialchars($_SESSION['display_name'] ?: "Gast", ENT_QUOTES, 'UTF-8'); ?></a>
				<a href="?go=neu"<?php if ($go=='neu') echo ' class="selected"'; ?>>Neues Spiel</a>
				<a href="?go=games"<?php if ($go=='games') echo ' class="selected"'; ?>>Spiele&uuml;bersicht</a>
				<a href="?go=anleitung"<?php if ($go=='anleitung') echo ' class="selected"'; ?>>Anleitung</a>
				<a href="?go=impressum"<?php if ($go=='impressum') echo ' class="selected"'; ?>>Impressum</a>
			</div>
			<?php // not gated on user_id: guests can view finished games too, and game.php expects these elements to exist
				if (isset($_GET["go"]) and $_GET["go"]=="game") { ?>
				<div class="collapsible actions">
					<div id="settings" class="settings"></div>
					<a id="leave" style="display: none;" onClick="leave();">Spiel verlassen</a>
					<a id="finish" style="display: none;" onClick="finish();">Spiel abschlie&szlig;en</a>
				</div>
				<div id="players" class="collapsible players"></div>
			<?php } ?>
		</div>
		<div id="content">
			<?php
			identityMessage();
			if (isset($_GET["go"]) and $_GET["go"]=="impressum")
			{
				require_once("legal.php");
				legalNotice();
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="user")
			{
				echo '<h2>profil</h2>';
				identityForm();
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="neu" and !$_SESSION['user_id'])
			{
				echo '<p>Vergib einen Namen, um ein Spiel zu erstellen, oder lass das Feld frei, um anonym zu spielen. Wenn du einen Authentifizierungs-Code hast, kannst du dich mit diesem anmelden.</p>';
				identityForm();
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="neu")
				include_once("newgame.php");
			elseif (isset($_GET["go"]) and $_GET["go"]=="game")
				include_once("game.php");
			elseif (isset($_GET["go"]) and $_GET["go"]=="games")
				include_once("games.php");
			else # $_GET["go"]=="anleitung" doubles as start page
			{
				echo '<h2>Anleitung</h2>
					<p>Bilde aus den Buchstaben des Wortes m&ouml;glichst viele andere W&ouml;rter.
					Jedes Wort bringt so viele Punkte, wie es Buchstaben hat, multipliziert mit der Anzahl der Mitspieler, die es nicht gefunden haben.</p>
					<p>Die Auswertung wird angezeigt, wenn du das Spiel abschließt. Es ist erst dann wirklich beendet, wenn alle Spieler abgeschlossen haben.</p>
					<p>Du kannst Mitspieler einladen, indem du die Spiel-URL mit ihnen teilst.</p>';
			}
			?>
		</div>
	</div>
	</body>
</html>
