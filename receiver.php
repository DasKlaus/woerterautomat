<?php
header('Content-Type: application/json');
set_exception_handler(function($e) {
	ob_end_clean();
	error_log($e);
	echo json_encode(["error" => "server error"]);
});
ob_start();
require_once("config.php");
require_once("identity.php");

$return = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' and $_SESSION['user_id'])
{
	if (identityRestriction() !== false) { $_SESSION['display_name'] = ""; }
	$user = $_SESSION['user_id'];
	$game = (int)($_POST['game'] ?? 0);

	switch($_POST["action"] ?? "") {
		case "newword":
			$word = mb_strtolower(trim($_POST['word'] ?? ''));
			$sourceword = (string)$mysql->execute_query("select g.source_word from game g
					join player p on p.game_id = g.id
					where g.id = ? and p.user_id = ? and p.status <> 2", [$game, $user])->fetch_column();
			if (mb_strlen($word) > 2 and possible($word, $sourceword))
			{
				$mysql->begin_transaction();
				$mysql->execute_query("insert ignore into word (game_id, user_id, word, created_at) values (?, ?, ?, now())", [$game, $user, $word]);
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
			$return = gamestate($mysql, $game, $user);
			break;
		case "joingame":
			$mysql->begin_transaction();
			$mysql->execute_query("insert ignore into player (game_id, user_id, display_name, joined_at, activity)
					select ?, ?, ?, now(), now() from game where id = ? and status < 2", [$game, $user, $_SESSION['display_name'], $game]);
			touchplayer($mysql, $game, $user);
			$mysql->execute_query("update game set status = 1
					where id = ? and status = 0 and (select count(*) from player where game_id = ?) > 1", [$game, $game]);
			$mysql->execute_query("update game set status = 2
					where id = ? and (select count(*) from player where game_id = ?) = maxplayers", [$game, $game]);
			recompute($mysql, $game);
			touchgame($mysql, $game);
			$mysql->commit();
			$return = gamestate($mysql, $game, $user);
			break;
		case "leavegame":
			$mysql->begin_transaction();
			$mysql->execute_query("delete from word where game_id = ? and user_id = ?", [$game, $user]);
			$mysql->execute_query("delete from player where game_id = ? and user_id = ?", [$game, $user]);
			if (playercount($mysql, $game) == 0)
			{
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
		case "showgames":
			$where = "";
			$params = [];
			switch($_GET['mode'] ?? 'relevant')
			{
				case 'relevant':
					$where = "where (game.status < 2 and not game.private) or exists (select 1 from player m where m.game_id = game.id and m.user_id = ?)";
					$params = [$user];
					break;
				case 'own':
					$where = "where game.created_by = ? or exists (select 1 from player m where m.game_id = game.id and m.user_id = ?)";
					$params = [$user, $user];
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
			$window = " order by created_at desc, id desc limit 20 offset ".(20 * (max(1, (int)($_GET['page'] ?? 1)) - 1));
			$return["pages"] = (int)ceil($mysql->execute_query("select count(*) from game ".$where, $params)->fetch_column() / 20);
			$return["games"] = $mysql->execute_query("select id, source_word as word, status, language, umlauts, flexion, private, maxplayers,
					created_by_name as starter, timestampdiff(minute, created_at, now()) as starttime
				from game ".$where.$window, $params)->fetch_all(MYSQLI_ASSOC);
			$players = $mysql->execute_query("select p.game_id, p.display_name as player, p.points
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
			select sum(char_length(w.word) * (? - (select count(*) from word f
					where f.game_id = w.game_id and f.word = w.word)))
			from word w where w.game_id = p.game_id and w.user_id = p.user_id
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
		from player where game_id = ? order by last_activity", [$user, $game])->fetch_all(MYSQLI_ASSOC);
	$return["words"] = $mysql->execute_query("select w.word,
			char_length(w.word) * (? - (select count(*) from word f
					where f.game_id = w.game_id and f.word = w.word)) as points
		from word w where w.game_id = ? and w.user_id = ?", [playercount($mysql, $game), $game, $user])->fetch_all(MYSQLI_ASSOC);
	return $return;
}

function finishstate($mysql, $game)
{
	$return = [];
	$return["version"] = version($mysql, $game);
	$return["players"] = $mysql->execute_query("select display_name as player, user_id, status, points,
			timestampdiff(minute, activity, now()) as last_activity
		from player where game_id = ? order by last_activity", [$game])->fetch_all(MYSQLI_ASSOC);
	$return["gamestatus"] = (int)$mysql->execute_query("select status from game where id = ?", [$game])->fetch_column();
	$return["words"] = $mysql->execute_query("select w.word, w.user_id, p.display_name as player,
			char_length(w.word) * (? - (select count(*) from word f
					where f.game_id = w.game_id and f.word = w.word)) as points
		from word w join player p on p.game_id = w.game_id and p.user_id = w.user_id
		where w.game_id = ?", [playercount($mysql, $game), $game])->fetch_all(MYSQLI_ASSOC);
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
