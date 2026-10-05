<?php
# Definitions only: index.php calls canonical() before any output and metatags() in the head,
# receiver.php uses gameurl() to build links for the client.

const DAILY = '2026-09-23'; // the daily's day 0
const DAILYLENGTH = [7, 8, 9, 10, 11, 12, 6]; // the daily's word length by weekday from Sunday

# with the setting on, a word the dictionary objected to scores nothing until the same second
# finder or 👍 that withdraws the verdict vouches for it; with lives, the verdict is final
const UNSCORED = "g.dictionary and exists (select 1 from reaction d
		where d.game_id = w.game_id and d.word = w.word and d.reactor_id = -1)
	and (g.lives or (select count(*) from word f where f.game_id = w.game_id and f.word = w.word) < 2
		and not exists (select 1 from reaction t where t.game_id = w.game_id and t.word = w.word and t.emoji = '👍'))";

function playercount($mysql, $game)
{
	return (int)$mysql->execute_query("select count(*) from player where game_id = ?", [$game])->fetch_column();
}

function recompute($mysql, $game)
{
	$mysql->execute_query("update player p set p.points = coalesce((
			select sum(if(".UNSCORED.", 0, char_length(w.word) * (? - (select count(*) from word f
					where f.game_id = w.game_id and f.word = w.word))))
			from word w join game g on g.id = w.game_id where w.game_id = p.game_id and w.user_id = p.user_id
		), 0)
		where p.game_id = ?", [playercount($mysql, $game), $game]);
}

function slug($word)
{
	return rawurlencode(mb_strtoupper(preg_replace('/[^\p{L}\p{N}]/u', '',
		Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($word))));
}

function gameurl($game, $word)
{
	return 'spiel-'.(int)$game.'-'.slug($word);
}

function daynumber($day)
{
	return (int)round((strtotime($day) - strtotime(DAILY)) / 86400);
}

function dailyurl($day, $language)
{
	return 'tag-'.daynumber($day).'-'.$language;
}

# Today's daily is created by its first visitor, from the candidates of the weekday's length, or else the nearest
# length left, shorter first: a language runs out of dailies as a whole, not day by day. Of those, the lowest day
# number goes first, any of several alike, and an unmarked one only when none is marked. A visitor racing the first
# waits on the unique key and is ignored.
function createdaily($mysql, $language)
{
	if ($mysql->execute_query("select 1 from game where daily = ? and language = ?", [date('Y-m-d'), $language])->fetch_column()) { return; }
	$candidate = $mysql->execute_query("select word, flexion, umlauts from candidate where language = ?
		order by abs(char_length(word) - ?), char_length(word), day is null, day, rand() limit 1", [$language, DAILYLENGTH[date('w')]])->fetch_assoc();
	if (!$candidate) { return; }
	$word = $candidate['word'];
	require_once("../dictionary.php");
	$words = solutionwords($dictionary, $language, $candidate['umlauts'], $candidate['flexion'], $word);
	$mysql->begin_transaction();
	$mysql->execute_query("insert ignore into game (source_word, language, umlauts, flexion, dictionary, lives, maxplayers, timelimit, solutions, daily,
			created_by, created_by_name, created_at, last_activity_at)
		values (?, ?, ?, ?, 1, 1, 0, 0, ?, ?, 0, '', now(), now())",
		[$word, $language, $candidate['umlauts'], $candidate['flexion'], count(array_filter($words, fn($rarity) => $rarity < 2)), date('Y-m-d')]);
	if ($mysql->affected_rows)
	{
		savesolution($mysql, $mysql->insert_id, $words);
		# the solution's words of the full length are exactly the word's anagrams, which would be the same field
		$anagrams = [$word, ...array_filter(array_keys($words), fn($found) => mb_strlen($found) == mb_strlen($word))];
		$mysql->execute_query("delete from candidate where language = ? and word in (".implode(",", array_fill(0, count($anagrams), "?")).")",
			[$language, ...$anagrams]);
		plandailies($mysql, $language);
	}
	$mysql->commit();
}

# The next four weeks' words are marked ahead with their days' numbers, so they can be reviewed. Since a length's
# marked words go lowest number first, whatever their numbers, a length is only marked again for the days its
# marked words don't cover: a deleted mark is replaced, unless a word marked by hand already stands in for it.
function plandailies($mysql, $language)
{
	$marked = array_column($mysql->execute_query("select char_length(word), count(*) from candidate where language = ? and day is not null
		group by char_length(word)", [$language])->fetch_all(), 1, 0);
	$days = [];
	$today = $mysql->execute_query("select 1 from game where daily = ? and language = ?", [date('Y-m-d'), $language])->fetch_column();
	for ($i = $today ? 1 : 0; $i < 28; $i++)
	{
		$time = strtotime("+$i days");
		$days[DAILYLENGTH[date('w', $time)]][] = daynumber(date('Y-m-d', $time));
	}
	foreach ($days as $length => $numbers)
	{
		foreach (array_slice($numbers, $marked[$length] ?? 0) as $number)
		{
			$mysql->execute_query("update candidate set day = ? where language = ? and day is null and char_length(word) = ? order by rand() limit 1",
				[$number, $language, $length]);
		}
	}
}

# The canonical path of the current request, and a redirect to it when the request came in some other
# shape: an old ?go= link, a stale word in a game URL, a spelling the rewrite accepts but we don't hand out.
function canonical()
{
	global $mysql, $currentgame, $canonicalurl, $dailyday, $dailylanguage;
	$go = $_GET['go'] ?? 'anleitung';
	$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\').'/';
	$origin = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'];
	$path = ['neu' => 'neu', 'user' => 'profil', 'games' => 'spiele', 'impressum' => 'impressum'][$go] ?? '';
	# the request as it arrived: mod_rewrite leaves REQUEST_URI alone but rewrites QUERY_STRING
	parse_str(ltrim((string)strstr($_SERVER['REQUEST_URI'], '?'), '?'), $query);
	$oldstyle = (isset($query['go']) or isset($query['game']));
	unset($query['go'], $query['game'], $query['day'], $query['language']);
	if ($go == 'daily')
	{
		$dailyday = date('Y-m-d', strtotime(DAILY.' +'.(int)($_GET['day'] ?? 0).' days'));
		$dailylanguage = ($_GET['language'] ?? '') == 'en' ? 'en' : 'de';
		if ($dailyday == date('Y-m-d')) { createdaily($mysql, $dailylanguage); }
		$path = dailyurl($dailyday, $dailylanguage);
	}
	if ($go == 'game' or $go == 'daily')
	{
		# a game by its id, a daily by its day and language; a daily's own game URL leads to its day
		$currentgame = $mysql->execute_query("select id, source_word, status, language, umlauts, flexion, dictionary, lives, maxplayers, timelimit, private, created_by_name, daily,
				timestampdiff(minute, created_at, now()) as starttime from game where id = ? or daily = ? and language = ?",
			[(int)($_GET['game'] ?? 0), $dailyday, $dailylanguage])->fetch_assoc();
		if ($currentgame)
			$path = $currentgame['daily'] ? dailyurl($currentgame['daily'], $currentgame['language']) : gameurl($currentgame['id'], $currentgame['source_word']);
		elseif ($go == 'game') # leave the URL alone, game.php says the game is gone
		{
			$canonicalurl = $origin.$_SERVER['REQUEST_URI'];
			return;
		}
	}
	$target = $base.$path.($query ? '?'.http_build_query($query) : '');
	$canonicalurl = $origin.$target;
	if ($oldstyle or strtok($_SERVER['REQUEST_URI'], '?') != $base.$path)
	{
		header('Location: '.$canonicalurl, true, 301);
		exit;
	}
}

# In the game's own language, not the reader's: whoever this reaches is a possible player, and playing
# takes that language anyway.
function gamedescription($word)
{
	global $currentgame, $ui;
	$say = $ui[$currentgame['language']];
	$settings = [$say['language'.$currentgame['language']]];
	if ($currentgame['umlauts']) { $settings[] = $say['setumlauts']; }
	if ($currentgame['flexion']) { $settings[] = $say['setflexion']; }
	if ($currentgame['dictionary']) { $settings[] = $say['setdictionary']; }
	if ($currentgame['lives']) { $settings[] = $say['setlives']; }
	if ($currentgame['private']) { $settings[] = $say['setprivate']; }
	if ($currentgame['maxplayers']) { $settings[] = fmt($say['maxplayerstitle'], [$currentgame['maxplayers']]); }

	if ($currentgame['status'] == 3) { $lead = $say['descfinished']; }
	elseif ($currentgame['status'] == 2) { $lead = $say['descfull']; }
	else { $lead = fmt($say['descquestion'], [$word]); }
	return $lead.' '.implode(', ', $settings).'.';
}

function metatags()
{
	global $currentgame, $canonicalurl, $language, $dailyday, $dailylanguage, $ui;
	$title = 'W&ouml;rterautomat';
	$description = t('metadescription');
	$image = 'vorschau.png';
	$go = $_GET['go'] ?? 'anleitung';
	$page = ['neu' => t('newgame'), 'user' => t('profile'), 'games' => t('gamelist'), 'impressum' => t('legal')][$go] ?? '';
	if ($page) { $title = $page.' &middot; W&ouml;rterautomat'; }
	if ($currentgame)
	{
		$word = htmlspecialchars(mb_strtoupper($currentgame['source_word']), ENT_QUOTES, 'UTF-8');
		$title = $word.' &middot; W&ouml;rterautomat';
		$description = gamedescription($word);
		$image = 'vorschau-'.(int)$currentgame['id'].'.png';
	}
	# a daily goes by its day in its own language, with or without a game
	if ($go == 'daily') { $title = $ui[$dailylanguage]['daily'].' #'.daynumber($dailyday).' &middot; W&ouml;rterautomat'; }
	$imageurl = substr($canonicalurl, 0, strrpos($canonicalurl, '/') + 1).$image;
	echo '<title>'.$title.'</title>
		<meta name="description" content="'.$description.'">
		<meta name="robots" content="'.(($go == 'game' or $go == 'daily') ? 'noindex,nofollow' : 'index,nofollow').'">
		<link rel="canonical" href="'.htmlspecialchars($canonicalurl, ENT_QUOTES, 'UTF-8').'">
		<meta property="og:type" content="website">
		<meta property="og:site_name" content="W&ouml;rterautomat">
		<meta property="og:locale" content="'.(($currentgame['language'] ?? $dailylanguage ?? $language) == 'en' ? 'en_GB' : 'de_DE').'">
		<meta property="og:title" content="'.$title.'">
		<meta property="og:description" content="'.$description.'">
		<meta property="og:url" content="'.htmlspecialchars($canonicalurl, ENT_QUOTES, 'UTF-8').'">
		<meta property="og:image" content="'.htmlspecialchars($imageurl, ENT_QUOTES, 'UTF-8').'">
		<meta property="og:image:width" content="1200">
		<meta property="og:image:height" content="628">
		';
}
