<?php
header('Content-Type: text/html; charset=UTF-8');
require_once("config.php");
require_once("../identity.php");
require_once("lang.php");
require_once("meta.php");

if (($_POST['do'] ?? '') == 'renameall' and $_SESSION['user_id'])
{
	$mysql->execute_query("update player set display_name = ? where user_id = ?", [$_SESSION['display_name'], $_SESSION['user_id']]);
	$mysql->execute_query("update reaction set display_name = ? where reactor_id = ?", [$_SESSION['display_name'], $_SESSION['user_id']]);
	$mysql->execute_query("update game set created_by_name = ? where created_by = ?", [$_SESSION['display_name'], $_SESSION['user_id']]);
}

canonical(); // redirects, so it belongs above the first output like any header

$go = $_GET['go'] ?? 'anleitung';
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php metatags(); ?>
		<link href="favicon.svg" type="image/svg+xml" rel="icon">
		<link href="style.css" type="text/css" rel="stylesheet" media="screen">
	</head>
	<body>
	<div id="wrapper">
		<h1 id="letters">woerterautomat</h1>
		<div id="lettersortbuttons" class="collapsible shuffle">
			<div class="modes">
			<button type="button" onClick="select(this); writeletters(randomstring(originalword));"><?php echo t('shuffle'); ?></button>
			<button id="anagram" type="button" onClick="select(this); writeletters(anagram(target, prepared_words));"><?php echo t('anagramsort'); ?></button>
			</div>
			<div class="modes">
			<button type="button" onClick="select(this); writeletters(sortstring(originalword));"><?php echo t('alphabetical'); ?></button>
			<button type="button" class="selected" onClick="select(this); writeletters(originalword);"><?php echo t('original'); ?></button>
			</div>
		</div>
		<script>var txt = <?php echo json_encode($ui[$language]); ?>;</script>
		<script src="index.js"></script>
		<div id="menu">
			<div class="collapsible nav">
				<a href="profil"<?php if ($go=='user') echo ' class="selected"'; ?>><?php echo t('profile'); if ($_SESSION['user_id']) echo ': '.htmlspecialchars($_SESSION['display_name'] ?: t('guest'), ENT_QUOTES, 'UTF-8'); ?></a>
				<a href="neu"<?php if ($go=='neu') echo ' class="selected"'; ?>><?php echo t('newgame'); ?></a>
				<a href="spiele"<?php if ($go=='games') echo ' class="selected"'; ?>><?php echo t('gamelist'); ?></a>
				<a href="."<?php if ($go=='anleitung') echo ' class="selected"'; ?>><?php echo t('instructions'); ?></a>
				<a href="impressum"<?php if ($go=='impressum') echo ' class="selected"'; ?>><?php echo t('legal'); ?></a>
			</div>
			<?php // not gated on user_id: guests can view finished games too, and game.php expects these elements to exist
				if (isset($_GET["go"]) and $_GET["go"]=="game") { ?>
				<div class="collapsible actions">
					<div id="settings" class="settings"></div>
					<a id="leave" style="display: none;" onClick="leave();"><?php echo t('leave'); ?></a>
					<a id="finish" style="display: none;" onClick="finish();"><?php echo t('finish'); ?></a>
				</div>
				<div id="players" class="collapsible players"></div>
			<?php } ?>
		</div>
		<div id="content">
			<?php
			identityMessage($language);
			if (isset($_GET["go"]) and $_GET["go"]=="impressum")
			{
				require_once("../legal.php");
				legalNotice($language);
				echo t('dictionarylicense');
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="user")
			{
				echo '<h2>'.t('profile').'</h2>';
				identityLanguageForm($language, ['de' => 'Deutsch', 'en' => 'English']);
				identityForm($language);
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="neu" and !$_SESSION['user_id'])
			{
				echo t('guestcreate');
				identityForm($language);
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="neu")
				include_once("newgame.php");
			elseif (isset($_GET["go"]) and $_GET["go"]=="game")
				include_once("game.php");
			elseif (isset($_GET["go"]) and $_GET["go"]=="games")
				include_once("games.php");
			else # $_GET["go"]=="anleitung" doubles as start page
			{
				echo '<h2>'.t('instructions').'</h2>'.t('instructionstext').'<h3>'.t('settingstitle').'</h3>';
				legend(true);
				echo '<h3>'.t('reactionstitle').'</h3>'.t('reactionstext').'<h3>'.t('othertitle').'</h3>'.t('othertext');
			}
			?>
		</div>
	</div>
	</body>
</html>
