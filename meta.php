<?php
# Function definitions only: index.php calls canonical() before any output and metatags() in the head,
# receiver.php uses gameurl() to build links for the client.

function slug($word)
{
	return rawurlencode(mb_strtoupper(preg_replace('/[^\p{L}\p{N}]/u', '',
		Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($word))));
}

function gameurl($game, $word)
{
	return 'spiel-'.(int)$game.'-'.slug($word);
}

# The canonical path of the current request, and a redirect to it when the request came in some other
# shape: an old ?go= link, a stale word in a game URL, a spelling the rewrite accepts but we don't hand out.
function canonical()
{
	global $mysql, $currentgame, $canonicalurl;
	$go = $_GET['go'] ?? 'anleitung';
	$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\').'/';
	$origin = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'];
	$path = ['neu' => 'neu', 'user' => 'profil', 'games' => 'spiele', 'impressum' => 'impressum'][$go] ?? '';
	# the request as it arrived: mod_rewrite leaves REQUEST_URI alone but rewrites QUERY_STRING
	parse_str(ltrim((string)strstr($_SERVER['REQUEST_URI'], '?'), '?'), $query);
	$oldstyle = (isset($query['go']) or isset($query['game']));
	unset($query['go'], $query['game']);
	if ($go == 'game')
	{
		$currentgame = $mysql->execute_query("select source_word, status, language, umlauts, flexion, dictionary, maxplayers, timelimit, private, created_by_name,
				timestampdiff(minute, created_at, now()) as starttime from game where id = ?", [(int)($_GET['game'] ?? 0)])->fetch_assoc();
		if (!$currentgame) # leave the URL alone, game.php says the game is gone
		{
			$canonicalurl = $origin.$_SERVER['REQUEST_URI'];
			return;
		}
		$path = gameurl($_GET['game'] ?? 0, $currentgame['source_word']);
	}
	$target = $base.$path.($query ? '?'.http_build_query($query) : '');
	$canonicalurl = $origin.$target;
	if ($oldstyle or strtok($_SERVER['REQUEST_URI'], '?') != $base.$path)
	{
		header('Location: '.$canonicalurl, true, 301);
		exit;
	}
}

function gamedescription($word)
{
	global $currentgame;
	$english = $currentgame['language'] == 'en';
	$settings = [$english ? ['de' => 'German', 'en' => 'English'][$currentgame['language']] : ['de' => 'Deutsch', 'en' => 'Englisch'][$currentgame['language']]];
	if ($currentgame['umlauts']) { $settings[] = $english ? 'umlauts substituted' : 'Umlaute substituiert'; }
	if ($currentgame['flexion']) { $settings[] = $english ? 'with inflected forms' : 'mit Flexionsformen'; }
	if ($currentgame['dictionary']) { $settings[] = $english ? 'dictionary words only' : 'nur W&ouml;rterbuchw&ouml;rter'; }
	if ($currentgame['private']) { $settings[] = $english ? 'private' : 'privat'; }
	if ($currentgame['maxplayers']) { $settings[] = ($english ? 'up to '.$currentgame['maxplayers'].' players' : 'bis '.$currentgame['maxplayers'].' Spieler'); }

	if ($currentgame['status'] == 3) { $lead = $english ? 'Finished.' : 'Abgeschlossen.'; }
	elseif ($currentgame['status'] == 2) { $lead = $english ? 'Full.' : 'Voll.'; }
	else { $lead = $english ? 'How many words can you find in '.$word.'?' : 'Wie viele W&ouml;rter findest du in '.$word.'?'; }
	return $lead.' '.implode(', ', $settings).'.';
}

function metatags()
{
	global $currentgame, $canonicalurl;
	$title = 'W&ouml;rterautomat';
	$description = 'Bilde aus den Buchstaben eines Wortes m&ouml;glichst viele andere W&ouml;rter.';
	$image = 'vorschau.png';
	$go = $_GET['go'] ?? 'anleitung';
	$page = ['neu' => 'Neues Spiel', 'user' => 'Profil', 'games' => 'Spiele&uuml;bersicht', 'impressum' => 'Impressum'][$go] ?? '';
	if ($page) { $title = $page.' &middot; W&ouml;rterautomat'; }
	if ($go == 'game' and $currentgame)
	{
		$word = htmlspecialchars(mb_strtoupper($currentgame['source_word']), ENT_QUOTES, 'UTF-8');
		$title = $word.' &middot; W&ouml;rterautomat';
		$description = gamedescription($word);
		$image = 'vorschau-'.(int)$_GET['game'].'.png';
	}
	$imageurl = substr($canonicalurl, 0, strrpos($canonicalurl, '/') + 1).$image;
	echo '<title>'.$title.'</title>
		<meta name="description" content="'.$description.'">
		<meta name="robots" content="'.($go == 'game' ? 'noindex,nofollow' : 'index,nofollow').'">
		<link rel="canonical" href="'.htmlspecialchars($canonicalurl, ENT_QUOTES, 'UTF-8').'">
		<meta property="og:type" content="website">
		<meta property="og:site_name" content="W&ouml;rterautomat">
		<meta property="og:locale" content="'.(($currentgame['language'] ?? 'de') == 'en' ? 'en_GB' : 'de_DE').'">
		<meta property="og:title" content="'.$title.'">
		<meta property="og:description" content="'.$description.'">
		<meta property="og:url" content="'.htmlspecialchars($canonicalurl, ENT_QUOTES, 'UTF-8').'">
		<meta property="og:image" content="'.htmlspecialchars($imageurl, ENT_QUOTES, 'UTF-8').'">
		<meta property="og:image:width" content="1200">
		<meta property="og:image:height" content="628">
		';
}
