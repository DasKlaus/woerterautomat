<?php
header('Content-Type: text/html; charset=ISO-8859-1');
/*
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
*/
require_once("config.php");

switch($_GET["action"]) {
	/*case "settime": 
		if (isset($_GET["time"]) and is_numeric($_GET["time"])) 
		{
			$query = mysql_query("update playerstatus set timeleft=".$_GET['time']." where game=".$_GET['game']." and player='".$_GET['player']."'");
		}
		echo 'settime';
		break;*/
	case "reset": 
		allpointsnew();
		break;
	case "newword": 
		// validate again because hackers
		if (possible($_GET['word'], $_GET['originalword']))
		{
		$query = $mysql->query("insert into game".$mysql->real_escape_string($_GET['game'])." (word, player) values ('".$mysql->real_escape_string($_GET['word'])."', '".$mysql->real_escape_string($_GET['player'])."')") or die ('Error: '.$mysql->error());
		$points = 0;
		$query = $mysql->query("select * from game".$mysql->real_escape_string($_GET['game'])." left join(
				select word, 
				char_length(word) * (
					(select count(*) as playercount 
					from playerstatus where game=".$mysql->real_escape_string($_GET['game'])." 
					group by game)
				- count(*)) as current_points 
				from game".$mysql->real_escape_string($_GET['game'])." 
				group by word) 
			as stuff on game".$mysql->real_escape_string($_GET['game']).".word = stuff.word  
			where player='".$mysql->real_escape_string($_GET['player'])."'
			and game".$mysql->real_escape_string($_GET['game']).".word='".$mysql->real_escape_string($_GET['word'])."'"
			) or die ('Error in points calculation: '.$mysql->error());
		while ($result = $query->fetch_array())
		{
			$points = $result['current_points'];
		}
		$query = $mysql->query("update game".$mysql->real_escape_string($_GET['game'])." set points=".$points." 
						where word='".$mysql->real_escape_string($_GET['word'])."'") or die ('Error: '.$mysql->error());
		$query = $mysql->query("update playerstatus set points=(points+".$points.") 
						where player = '".$mysql->real_escape_string($_GET['player'])."' 
						and game = '".$mysql->real_escape_string($_GET['game'])."'") or die ('Error recalculating player\'s points: '.$mysql->error());
		$query = $mysql->query("update playerstatus set points=(points-".strlen($_GET['word']).") 
						where player != '".$mysql->real_escape_string($_GET['player'])."' 
						and game = '".$mysql->real_escape_string($_GET['game'])."'
						and exists(select player from game".$mysql->real_escape_string($_GET['game'])." 
							where word = '".$mysql->real_escape_string($_GET['word'])."' 
							and game".$mysql->real_escape_string($_GET['game']).".player = playerstatus.player)") or die ('Error recalculating other player\'s points: '.$mysql->error());
		}
		break;
	case "removeword":
		$query = $mysql->query("update playerstatus set points=points-(select points 
							from game".$mysql->real_escape_string($_GET['game'])." 
							where word = '".$mysql->real_escape_string($_GET['word'])."' limit 1)
						where player = '".$mysql->real_escape_string($_GET['player'])."' 
						and game = '".$mysql->real_escape_string($_GET['game'])."'") or die ('Error recalculating player\'s points: '.$mysql->error());
		$query = $mysql->query("delete from game".$mysql->real_escape_string($_GET['game'])." where player = '".$mysql->real_escape_string($_GET['player'])."' and word = '".$mysql->real_escape_string($_GET['word'])."'");
		$query = $mysql->query("update game".$mysql->real_escape_string($_GET['game'])." set points=points+".strlen($_GET['word'])." 
						where word='".$mysql->real_escape_string($_GET['word'])."'") or die ('Error: '.$mysql->error());
		$query = $mysql->query("update playerstatus set points=points+".strlen($_GET['word'])." 
						where player != '".$mysql->real_escape_string($_GET['player'])."' 
						and game = '".$mysql->real_escape_string($_GET['game'])."' 
						and exists(select player from game".$mysql->real_escape_string($_GET['game'])." 
							where word = '".$mysql->real_escape_string($_GET['word'])."' 
							and game".$mysql->real_escape_string($_GET['game']).".player = playerstatus.player)") or die ('Error recalculating other player\'s points: '.$mysql->error());
		break;
	case "showgames":
		$return = [];
		$where = "";
		switch($_GET['mode'])
		{
			case 'all':
				break;
			case 'own':
				$where = "where starter='".$mysql->real_escape_string($_GET['player'])."' or '".$mysql->real_escape_string($_GET['player'])."' in (select player from playerstatus where game=games.id)";
				break;
			case 'new':
				$where = "where status=0";
				break;
			case 'active':
				$where = "where status=1";
				break;
			case 'finished':
				$where = "where status=2";
				break;
		}
		$query = $mysql->query("select *, TIMESTAMPDIFF(MINUTE,date,CURRENT_TIMESTAMP()) as starttime from games ".$where." order by date desc") or die ('Error: '.$mysql->error());
		while ($result = $query->fetch_array())
		{
			$currentgame=$result;
			$currentgame['starter'] = umlaute($result['starter']);
			$currentgame['players'] = [];
			
			$playerquery = $mysql->query("select player, points from playerstatus where game=".$result['id']) or die ('Error: '.$mysql->error());
			while ($playerresult = $playerquery->fetch_array())
			{
				$playerresult['player'] = umlaute($playerresult['player']);
				$currentgame['players'][] = $playerresult;
			}
			$return[]=$currentgame;
		}
		echo json_encode($return);
		break;
	case "finishrequest":
		$return = [];
		$return["players"] = [];
		$query = $mysql->query("select player, status, points, TIMESTAMPDIFF(MINUTE,activity,CURRENT_TIMESTAMP()) as last_activity from playerstatus where game = ".$mysql->real_escape_string($_GET['game'])." order by last_activity") or die ('Error: '.$mysql->error());
		while ($result = $query->fetch_array())
		{
			$result['player'] = umlaute($result['player']);
			$return["players"][] = $result;
		}
		// gamestatus
		$return["gamestatus"] = 0;
		$query = $mysql->query("select status from games where id=".$mysql->real_escape_string($_GET['game']));
		while ($result = $query->fetch_array())
		{
			$return["gamestatus"] = $result['status'];
		}
		// all words and points
		$return["words"] = [];
		$query = $mysql->query("select * from game".$mysql->real_escape_string($_GET['game'])) or die ('Error: '.$mysql->error());
		while ($result = $query->fetch_array())
		{
			$result['player'] = umlaute($result['player']);
			$return["words"][] = $result;
		}
		echo json_encode($return);
		break;
	case "datarequest": 
		$query = $mysql->query("update playerstatus set activity = CURRENT_TIMESTAMP() where game=".$mysql->real_escape_string($_GET['game'])." and player='".$mysql->real_escape_string($_GET['player'])."'");
		$return = [];
		$return["players"] = [];
		$query = $mysql->query("select player, status, TIMESTAMPDIFF(MINUTE,activity,CURRENT_TIMESTAMP()) as last_activity from playerstatus where game = ".$mysql->real_escape_string($_GET['game'])." order by last_activity") or die ('Error: '.$mysql->error());
		while ($result = $query->fetch_array())
		{
			$result['player'] = umlaute($result['player']);
			$return["players"][] = $result;
		}
		$return["words"] = [];
		$query = $mysql->query
			("select word, points from game".$mysql->real_escape_string($_GET['game'])." where player='".$mysql->real_escape_string($_GET['player'])."'"
			) or die ('Error: '.$mysql->error());
		while ($result = $query->fetch_array())
		{
			$return["words"][] = $result;
		}
		echo json_encode($return); 
		break;
	case "joingame":
		$new = true;
		$query = $mysql->query("select player from playerstatus where player='".$mysql->real_escape_string($_GET['player'])."' and game=".$mysql->real_escape_string($_GET['game']));
		while ($result = $query->fetch_array())
		{
			$new = false;
		}
		if ($new)
		{
			$query = $mysql->query("insert into playerstatus (game, player, status, activity) values ('".$mysql->real_escape_string($_GET['game'])."', '".$mysql->real_escape_string($_GET['player'])."', '0', CURRENT_TIMESTAMP())") or die ('Error: '.$mysql->error());
			// calculate all words new
			// all words get their lettercount additionally
			$query = $mysql->query("update game".$mysql->real_escape_string($_GET['game'])." set points = points+char_length(word)") or die('Error: '.$mysql->error());
		}
		calcpoints($mysql->real_escape_string($_GET['game']));
		$query = $mysql->query("select player from playerstatus where game=".$mysql->real_escape_string($_GET['game']));
		$players = $mysql->num_rows($query);
		if ($players == 2)
		{
			$query = $mysql->query("update games set status=1 where id=".$mysql->real_escape_string($_GET['game']));
		}
		break;
	case "leavegame":
		$query = $mysql->query("delete from playerstatus where player='".$mysql->real_escape_string($_GET['player'])."' and game=".$mysql->real_escape_string($_GET['game'])) or die('Error: '.$mysql->error());
		if ($_GET['players'] == '1')
		{
			$query = $mysql->query("delete from games where id=".$mysql->real_escape_string($_GET['game'])) or die('Error: '.$mysql->error());
			$query = $mysql->query("drop table game".$mysql->real_escape_string($_GET['game'])) or die('Error: '.$mysql->error());
		}
		else 
		{ 
			$query = $mysql->query("update game".$mysql->real_escape_string($_GET['game'])." set points = points-char_length(word) 
							where exists(select word from (select * from game".$mysql->real_escape_string($_GET['game']).") as checktbl 
							where player = '".$mysql->real_escape_string($_GET['player'])."' and checktbl.word = game".$mysql->real_escape_string($_GET['game']).".word)") or die('Error: '.$mysql->error());
			$query = $mysql->query("delete from game".$mysql->real_escape_string($_GET['game'])." where player='".$mysql->real_escape_string($_GET['player'])."'") or die ('Error: '.$mysql->error());
			calcpoints($mysql->real_escape_string($_GET['game']));
		}
		break;
	case "finishgame":
		$query = $mysql->query("update playerstatus set status = 2 where game=".$mysql->real_escape_string($_GET['game'])." and player='".$mysql->real_escape_string($_GET['player'])."'") or die('Error: '.$mysql->error());
		// if all players finished, set game status to two
		$query = $mysql->query("select distinct(status) from playerstatus where game=".$mysql->real_escape_string($_GET['game']));
		if ($mysql->num_rows($query)==1)
		{
			$query = $mysql->query("update games set status = 2 where id=".$mysql->real_escape_string($_GET['game']));
		}
		break;
	default: break;
}

function calcpoints($game)
{
	$query = $mysql->query("update playerstatus set points =
			(select sum(game".$game.".points) as fullpoints from game".$game." 
			where game".$game.".player=playerstatus.player group by game".$game.".player)
		where game = ".$game) or die ('Error: '.$mysql->error());
}

function allpointsnew()
{
	$games = [];
	$query = $mysql->query("select distinct(game) from playerstatus") or die ('Error: '.$mysql->error());
	while ($result = $query->fetch_array())
	{
		$games[] = $result['game'];
	}
	
	foreach($games as $game)
	{
		$query = $mysql->query("update game".$game." set points = 
		(
		    select char_length(word) * 
		    (
			(
				select count(*) as playercount
				from playerstatus where game=".$game."
				group by game
			)
			- count(*)
		    ) 
		    as current_points
		    from (select * from game".$game.") as hlp
		    where hlp.word = game".$game.".word
		    group by word
		)") or die ('Error calculating points for game '.$game.': '.$mysql->error());
		calcpoints($game);
	}
}

function umlaute($string)
{
	$upas = Array("ä" => "&auml;", "ü" => "&uuml;", "ö" => "&ouml;", "Ä" => "&Auml;", "Ü" => "&Uuml;", "Ö" => "&Ouml;", "ß" => "&szlig;"); 
	return strtr($string, $upas);
}

function possible($word, $originalword)
{
	if ($word == $originalword) return false;
	for ($i=0; $i<strlen($word); $i++)
	{
		if (strpos($originalword, substr($word, $i, 1)) === false)
		{
			$originalword = substr_replace ($originalword, "", strpos($originalword, substr($word, $i, 1)), 1);
		}
		else return false;
	}
	return true;
}
?>
