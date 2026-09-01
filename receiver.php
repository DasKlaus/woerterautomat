<?php
header('Content-Type: application/json');
ob_start();
require_once("config.php");
require_once("identity.php");

$return = null;
$reactionemoji = ['💪', '👍', '🤦', '😭', '🤯', '😂', '✨', '❓', '🚫']; // keep in sync with reactionemoji in game.js

// the dictionary's verdict is withheld once a second finder or a 👍 vouches for the word
const VOUCHED = "(r.reactor_id <> -1
	or ((select count(*) from word f where f.game_id = r.game_id and f.word = r.word) < 2
		and not exists (select 1 from reaction t where t.game_id = r.game_id and t.word = r.word and t.emoji = '👍')))";

// with the setting on, a word the dictionary objected to scores nothing until the same second
// finder or 👍 that withdraws the verdict vouches for it
const UNSCORED = "g.dictionary and exists (select 1 from reaction d
		where d.game_id = w.game_id and d.word = w.word and d.reactor_id = -1)
	and (select count(*) from word f where f.game_id = w.game_id and f.word = w.word) < 2
	and not exists (select 1 from reaction t where t.game_id = w.game_id and t.word = w.word and t.emoji = '👍')";

if ($_SERVER['REQUEST_METHOD'] === 'POST' and $_SESSION['user_id'])
{
	if (identityRestriction() !== false) { $_SESSION['display_name'] = ""; }
	$user = $_SESSION['user_id'];
	$game = (int)($_POST['game'] ?? 0);

	switch($_POST["action"] ?? "") {
		// both halves of the two-stage button: identical validation and the same solution query,
		// so creation never has to trust that a check ran, let alone what it found
		case "checkgame":
		case "creategame":
			if (($_POST["website"] ?? "") != "") { $return = ["message" => "Das Spiel konnte nicht erstellt werden.", "style" => "warning"]; break; }
			$sourceword = preg_replace('/[^\p{L}]/u', '', mb_strtolower($_POST['word'] ?? ''));
			$reason = identityRestriction();
			if ($reason !== false)
				$return = ["message" => "Das Erstellen von Spielen wurde gesperrt. ".$reason, "style" => "warning"];
			elseif (mb_strlen($sourceword) < 3)
				$return = ["message" => "Gib ein Wort mit mindestens drei Buchstaben ein.", "style" => "warning"];
			elseif (mb_strlen($sourceword) > 64)
				$return = ["message" => "Das Wort ist zu lang. Mehr als 64 Buchstaben sind nicht möglich.", "style" => "warning"];
			else
			{
				$flexion = (isset($_POST['flexion'])) ? 1 : 0;
				$umlauts = (isset($_POST['umlauts'])) ? 1 : 0;
				$dictionaryonly = (isset($_POST['dictionary'])) ? 1 : 0;
				$language = in_array($_POST['language'] ?? '', ['de', 'en'], true) ? $_POST['language'] : 'de';
				require_once("dictionary.php");
				$words = solutionwords($dictionary, $language, $umlauts, $flexion, $sourceword);
				$count = is_null($words) ? -1 : count($words);
				if ($dictionaryonly and $count < 0)
					$return = ["message" => "Für diese Sprache gibt es noch kein Wörterbuch. Ohne Wörterbuch ist die Wörterbuch-Wertung nicht möglich.", "style" => "warning"];
				elseif ($count > 5000)
					$return = ["message" => $count." mögliche Wörter. Die Begrenzung liegt bei 5000.", "style" => "warning"];
				elseif ($_POST["action"] == "checkgame")
					$return = ["message" => ($count < 0) ? "Für diese Sprache gibt es noch kein Wörterbuch. Das Spiel kann trotzdem gestartet werden."
							: $count." mögliche Wörter.",
						"style" => ($count < 50 or $count > 500) ? "caution" : "", "ok" => true, "solutions" => $count];
				else
				{
					$recent = $mysql->execute_query("select sum(created_at > now() - interval 1 minute) as lastminute, count(*) as lasthour
						from game where created_by = ? and created_at > now() - interval 1 hour", [$user])->fetch_assoc();
					if ($recent['lastminute'] > 0)
						$return = ["message" => "Du hast gerade eben ein Spiel gestartet. Warte eine Minute, bevor du das nächste startest.", "style" => "warning"];
					elseif ($recent['lasthour'] >= 10)
						$return = ["message" => "Du hast in der letzten Stunde zehn Spiele gestartet. Versuch es später noch einmal.", "style" => "warning"];
					else
					{
						$private = (isset($_POST['private'])) ? 1 : 0;
						$players = (int)($_POST['players'] ?? 0);
						$unit = in_array($_POST['unit'] ?? '', ['1', '60', '1440', '43200'], true) ? (int)$_POST['unit'] : 1440;
						$timelimit = (int)($_POST['timelimit'] ?? 0) * $unit;
						$mysql->execute_query("insert into game (source_word, language, flexion, umlauts, dictionary, private, maxplayers, timelimit, solutions, created_by, created_by_name, created_at, last_activity_at)
							values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now())",
							[$sourceword, $language, $flexion, $umlauts, $dictionaryonly, $private, $players, $timelimit, $count, $user, $_SESSION['display_name']]);
						$id = $mysql->insert_id;
						$mysql->execute_query("insert into player (game_id, user_id, display_name, joined_at, activity) values (?, ?, ?, now(), now())",
							[$id, $user, $_SESSION['display_name']]);
						if ($words) { savesolution($mysql, $id, $words); }
						$return["game"] = $id;
					}
				}
			}
			break;
		case "newword":
			$word = mb_strtolower(trim($_POST['word'] ?? ''));
			// three words the dictionary rejected within a minute stop the guessing; the block lifts as the oldest leaves the window
			$_SESSION['wrongwords'] = array_values(array_filter($_SESSION['wrongwords'] ?? [], fn($t) => $t > time() - 60));
			if (count($_SESSION['wrongwords']) >= 3)
			{
				$return = gamestate($mysql, $game, $user);
				$return['blocked'] = 60 - (time() - $_SESSION['wrongwords'][0]);
				break;
			}
			$currentgame = $mysql->execute_query("select g.source_word, g.language, g.umlauts, g.flexion from game g
					join player p on p.game_id = g.id
					where g.id = ? and p.user_id = ? and p.status <> 2", [$game, $user])->fetch_assoc();
			if (mb_strlen($word) > 2 and $currentgame and possible($word, $currentgame['source_word']))
			{
				$mysql->begin_transaction();
				$mysql->execute_query("insert ignore into word (game_id, user_id, word, created_at) values (?, ?, ?, now())", [$game, $user, $word]);
				if (!$mysql->execute_query("select 1 from solution where game_id = ? and word = ?", [$game, $word])->fetch_column())
				{
					require_once("dictionary.php");
					$emoji = wordcheck($dictionary, $currentgame['language'], $word, $currentgame['umlauts'], $currentgame['flexion']);
					if ($emoji)
					{
						$mysql->execute_query("insert ignore into reaction (game_id, word, reactor_id, emoji, display_name, created_at)
							values (?, ?, -1, ?, 'Wörterbuch', now())", [$game, $word, $emoji]);
						$_SESSION['wrongwords'][] = time();
					}
				}
				recompute($mysql, $game);
				touchplayer($mysql, $game, $user);
				touchgame($mysql, $game);
				$mysql->commit();
			}
			$return = gamestate($mysql, $game, $user);
			break;
		case "removeword":
			$word = mb_strtolower(trim($_POST['word'] ?? ''));
			$mysql->begin_transaction();
			$mysql->execute_query("delete from word where game_id = ? and user_id = ? and word = ?", [$game, $user, $word]);
			recompute($mysql, $game);
			touchplayer($mysql, $game, $user);
			touchgame($mysql, $game);
			$mysql->commit();
			$return = playerstate($mysql, $game, $user);
			break;
		case "joingame":
			$mysql->begin_transaction();
			$mysql->execute_query("insert ignore into player (game_id, user_id, display_name, joined_at, activity)
					select ?, ?, ?, now(), now() from game where id = ? and status < 2", [$game, $user, $_SESSION['display_name'], $game]);
			// every page load posts this, so only a row that was really inserted counts as an action:
			// reopening a game refreshes the name it shows and nothing else
			$joined = $mysql->affected_rows > 0;
			$mysql->execute_query("update player set display_name = ? where game_id = ? and user_id = ?", [$_SESSION['display_name'], $game, $user]);
			if ($joined)
			{
				$mysql->execute_query("update game set status = 1
						where id = ? and status = 0 and (select count(*) from player where game_id = ?) > 1", [$game, $game]);
				$mysql->execute_query("update game set status = 2
						where id = ? and (select count(*) from player where game_id = ?) = maxplayers", [$game, $game]);
				recompute($mysql, $game);
				touchgame($mysql, $game);
			}
			$mysql->commit();
			$return = gamestate($mysql, $game, $user);
			break;
		case "leavegame":
			$mysql->begin_transaction();
			$mysql->execute_query("delete from word where game_id = ? and user_id = ?", [$game, $user]);
			$mysql->execute_query("delete from player where game_id = ? and user_id = ?", [$game, $user]);
			if (playercount($mysql, $game) == 0)
			{
				$mysql->execute_query("delete from solution where game_id = ?", [$game]);
				$mysql->execute_query("delete from game where id = ?", [$game]);
			}
			else
			{
				recompute($mysql, $game);
				touchgame($mysql, $game);
			}
			$mysql->commit();
			break;
		case "finishgame":
			$mysql->execute_query("update player set status = 3, activity = now() where game_id = ? and user_id = ?", [$game, $user]);
			$mysql->execute_query("update game set status = 3
					where id = ? and not exists (select 1 from player where game_id = ? and status <> 3)", [$game, $game]);
			touchgame($mysql, $game);
			$return = finishstate($mysql, $game);
			break;
		// the whole permission check is the where clause: an actor who has finished, a target who has not,
		// and a limit the target's last action has outlived
		case "forcefinish":
			$target = (int)($_POST['player'] ?? 0);
			$mysql->execute_query("update player t
					join game g on g.id = t.game_id
					join player a on a.game_id = t.game_id and a.user_id = ? and a.status = 3
				set t.status = 3
				where t.game_id = ? and t.user_id = ? and t.status <> 3
					and g.timelimit > 0 and t.activity < now() - interval g.timelimit minute", [$user, $game, $target]);
			$mysql->execute_query("update game set status = 3
					where id = ? and not exists (select 1 from player where game_id = ? and status <> 3)", [$game, $game]);
			touchgame($mysql, $game);
			$return = finishstate($mysql, $game);
			break;
		case "react":
			$word = mb_strtolower(trim($_POST['word'] ?? ''));
			$emoji = $_POST['emoji'] ?? '';
			$mysql->begin_transaction();
			// reacting requires having personally finished, same as the picker only being shown then
			$exists = $mysql->execute_query("select 1 from player p where p.game_id = ? and p.user_id = ? and p.status = 3
					and (exists (select 1 from word w where w.game_id = p.game_id and w.word = ?)
						or exists (select 1 from solution s where s.game_id = p.game_id and s.word = ?))",
				[$game, $user, $word, $word])->fetch_column();
			if ($exists and in_array($emoji, $reactionemoji, true))
			{
				$mysql->execute_query("insert into reaction (game_id, word, reactor_id, emoji, display_name, created_at) values (?, ?, ?, ?, ?, now())
						on duplicate key update emoji = values(emoji), display_name = values(display_name), created_at = values(created_at)",
					[$game, $word, $user, $emoji, $_SESSION['display_name']]);
				recompute($mysql, $game);
				touchgame($mysql, $game);
			}
			$mysql->commit();
			$return = playerstate($mysql, $game, $user);
			break;
		case "unreact":
			$word = mb_strtolower(trim($_POST['word'] ?? ''));
			$mysql->begin_transaction();
			$mysql->execute_query("delete from reaction where game_id = ? and word = ? and reactor_id = ?", [$game, $word, $user]);
			recompute($mysql, $game);
			touchgame($mysql, $game);
			$mysql->commit();
			$return = playerstate($mysql, $game, $user);
			break;
		default: break;
	}
}
else
{
	$user = $_SESSION['user_id'];
	$game = (int)($_GET['game'] ?? 0);
	$version = (int)($_GET['version'] ?? -1);

	switch($_GET["action"] ?? "") {
		case "datarequest":
			if (version($mysql, $game) === $version) { ob_end_clean(); http_response_code(304); exit; }
			$return = gamestate($mysql, $game, $user);
			break;
		case "finishrequest":
			if (version($mysql, $game) === $version) { ob_end_clean(); http_response_code(304); exit; }
			$return = finishstate($mysql, $game);
			break;
		case "lookup":
			require_once("dictionary.php");
			$currentgame = $mysql->execute_query("select language, umlauts from game where id = ?", [$game])->fetch_assoc() ?: ['language' => '', 'umlauts' => 0];
			$return = lookup($dictionary, $currentgame['language'], mb_strtolower(trim($_GET['word'] ?? '')), $currentgame['umlauts']);
			break;
		case "showgames":
			$where = "";
			$params = [];
			switch($_GET['mode'] ?? 'relevant')
			{
				case 'relevant':
					$where = "where ((game.status < 2 and not game.private) or exists (select 1 from player m where m.game_id = game.id and m.user_id = ?)) and (game.status < 3 or game.last_activity_at > now() - interval 1 day)";
					$params = [$user];
					break;
				case 'own':
					$where = "where game.created_by = ? or exists (select 1 from player m where m.game_id = game.id and m.user_id = ?)";
					$params = [$user, $user];
					break;
				case 'all':
					$where = "where not game.private or exists (select 1 from player m where m.game_id = game.id and m.user_id = ?)";
					$params = [$user];
					break;
				case 'new':
					$where = "where game.status = 0 and (not game.private or exists (select 1 from player m where m.game_id = game.id and m.user_id = ?))";
					$params = [$user];
					break;
				case 'active':
					$where = "where game.status = 1 and (not game.private or exists (select 1 from player m where m.game_id = game.id and m.user_id = ?))";
					$params = [$user];
					break;
				case 'finished':
					$where = "where game.status = 3 and (not game.private or exists (select 1 from player m where m.game_id = game.id and m.user_id = ?))";
					$params = [$user];
					break;
			}
			$sortcolumn = ['activity' => 'last_activity_at', 'created' => 'created_at', 'alpha' => 'source_word', 'length' => 'char_length(source_word)']
				[$_GET['sort'] ?? ''] ?? 'last_activity_at';
			$sortdir = ($_GET['dir'] ?? '') === 'asc' ? 'asc' : 'desc';
			$window = " order by ".$sortcolumn." ".$sortdir.", id ".$sortdir." limit 20 offset ".(20 * (max(1, (int)($_GET['page'] ?? 1)) - 1));
			$return["pages"] = (int)ceil($mysql->execute_query("select count(*) from game ".$where, $params)->fetch_column() / 20);
			$return["games"] = $mysql->execute_query("select id, source_word as word, status, language, umlauts, flexion, dictionary, private, maxplayers, timelimit, solutions,
					created_by_name as starter, timestampdiff(minute, last_activity_at, now()) as activitytime
				from game ".$where.$window, $params)->fetch_all(MYSQLI_ASSOC);
			$players = $mysql->execute_query("select p.game_id, p.display_name as player, p.points, p.status,
					timestampdiff(minute, p.activity, now()) as last_activity
				from player p join (select id from game ".$where.$window.") g on g.id = p.game_id
				order by p.joined_at", $params)->fetch_all(MYSQLI_ASSOC);
			$bygame = [];
			foreach ($players as $playerrow)
			{
				$bygame[$playerrow['game_id']][] = $playerrow;
			}
			foreach ($return["games"] as &$currentgame)
			{
				$currentgame['players'] = $bygame[$currentgame['id']] ?? [];
			}
			unset($currentgame);
			break;
		default: break;
	}
}

ob_end_clean();
echo json_encode($return);

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

function touchplayer($mysql, $game, $user)
{
	$mysql->execute_query("update player set display_name = ?, activity = now() where game_id = ? and user_id = ?", [$_SESSION['display_name'], $game, $user]);
}

function touchgame($mysql, $game)
{
	$mysql->execute_query("update game set version = version + 1, last_activity_at = now() where id = ?", [$game]);
}

function version($mysql, $game)
{
	return (int)$mysql->execute_query("select version from game where id = ?", [$game])->fetch_column();
}

function gamestate($mysql, $game, $user)
{
	$return = [];
	$return["version"] = version($mysql, $game);
	$return["players"] = $mysql->execute_query("select display_name as player, status, user_id = ? as self,
			timestampdiff(minute, activity, now()) as last_activity
		from player where game_id = ?", [$user, $game])->fetch_all(MYSQLI_ASSOC);
	$return["words"] = $mysql->execute_query("select w.word,
			if(".UNSCORED.", -1, char_length(w.word) * (? - (select count(*) from word f
					where f.game_id = w.game_id and f.word = w.word))) as points
		from word w join game g on g.id = w.game_id
		where w.game_id = ? and w.user_id = ?
		order by w.created_at, w.word", [playercount($mysql, $game), $game, $user])->fetch_all(MYSQLI_ASSOC);
	$return["reactions"] = $mysql->execute_query("select r.word, r.reactor_id, r.emoji, r.display_name
		from reaction r join word w on w.game_id = r.game_id and w.word = r.word
		where r.game_id = ? and w.user_id = ? and ".VOUCHED, [$game, $user])->fetch_all(MYSQLI_ASSOC);
	return $return;
}

function playerstate($mysql, $game, $user)
{
	$status = (int)$mysql->execute_query("select status from player where game_id = ? and user_id = ?", [$game, $user])->fetch_column();
	return ($status == 3) ? finishstate($mysql, $game) : gamestate($mysql, $game, $user);
}

function finishstate($mysql, $game)
{
	$return = [];
	$return["version"] = version($mysql, $game);
	$return["players"] = $mysql->execute_query("select display_name as player, user_id, status, points,
			timestampdiff(minute, activity, now()) as last_activity
		from player where game_id = ?", [$game])->fetch_all(MYSQLI_ASSOC);
	$return["gamestatus"] = (int)$mysql->execute_query("select status from game where id = ?", [$game])->fetch_column();
	$return["words"] = $mysql->execute_query("select w.word, w.user_id, p.display_name as player,
			if(".UNSCORED.", -1, char_length(w.word) * (? - (select count(*) from word f
					where f.game_id = w.game_id and f.word = w.word))) as points
		from word w join player p on p.game_id = w.game_id and p.user_id = w.user_id
			join game g on g.id = w.game_id
		where w.game_id = ?
		order by w.created_at, w.word", [playercount($mysql, $game), $game])->fetch_all(MYSQLI_ASSOC);
	$return["reactions"] = $mysql->execute_query("select r.word, r.reactor_id, r.emoji, r.display_name
		from reaction r where r.game_id = ? and ".VOUCHED." order by r.created_at", [$game])->fetch_all(MYSQLI_ASSOC);
	$return["solution"] = array_column($mysql->execute_query("select word from solution where game_id = ?", [$game])->fetch_all(MYSQLI_ASSOC), 'word');
	return $return;
}

function possible($word, $sourceword)
{
	if ($word === $sourceword) return false;
	for ($i = 0; $i < mb_strlen($word); $i++)
	{
		$at = mb_strpos($sourceword, mb_substr($word, $i, 1));
		if ($at === false) return false;
		$sourceword = mb_substr($sourceword, 0, $at).mb_substr($sourceword, $at + 1);
	}
	return true;
}
?>
