<?php
// ini_set('display_errors', 1);

$host = "localhost";
$dbname = "DasKlaussql12";
$dbuser = "DasKlaussql12";
$dbpass = "hNzljODYwM";

mysql_connect($host, $dbuser, $dbpass) or die ('Verbindung mit Datenbank konnte nicht hergestellt werden: '.mysql_error());
mysql_select_db($dbname) or die ('Datenbank konnte nicht ausgewählt werden: '.mysql_error());

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
		$query = mysql_query("insert into game".mysql_real_escape_string($_GET['game'])." (word, player) values ('".mysql_real_escape_string($_GET['word'])."', '".mysql_real_escape_string($_GET['player'])."')") or die ('Error: '.mysql_error());
		$points = 0;
		$query = mysql_query("select * from game".mysql_real_escape_string($_GET['game'])." left join(
				select word, 
				char_length(word) * (
					(select count(*) as playercount 
					from playerstatus where game=".mysql_real_escape_string($_GET['game'])." 
					group by game)
				- count(*)) as current_points 
				from game".mysql_real_escape_string($_GET['game'])." 
				group by word) 
			as stuff on game".mysql_real_escape_string($_GET['game']).".word = stuff.word  
			where player='".mysql_real_escape_string($_GET['player'])."'
			and game".mysql_real_escape_string($_GET['game']).".word='".mysql_real_escape_string($_GET['word'])."'"
			) or die ('Error in points calculation: '.mysql_error());
		while ($result = mysql_fetch_array($query))
		{
			$points = $result['current_points'];
		}
		$query = mysql_query("update game".mysql_real_escape_string($_GET['game'])." set points=".$points." 
						where word='".mysql_real_escape_string($_GET['word'])."'") or die ('Error: '.mysql_error());
		$query = mysql_query("update playerstatus set points=(points+".$points.") 
						where player = '".mysql_real_escape_string($_GET['player'])."' 
						and game = '".mysql_real_escape_string($_GET['game'])."'") or die ('Error recalculating player\'s points: '.mysql_error());
		$query = mysql_query("update playerstatus set points=(points-".strlen($_GET['word']).") 
						where player != '".mysql_real_escape_string($_GET['player'])."' 
						and game = '".mysql_real_escape_string($_GET['game'])."'
						and exists(select player from game".mysql_real_escape_string($_GET['game'])." 
							where word = '".mysql_real_escape_string($_GET['word'])."' 
							and game".mysql_real_escape_string($_GET['game']).".player = playerstatus.player)") or die ('Error recalculating other player\'s points: '.mysql_error());
		}
		break;
	case "removeword":
		$query = mysql_query("update playerstatus set points=points-(select points 
							from game".mysql_real_escape_string($_GET['game'])." 
							where word = '".mysql_real_escape_string($_GET['word'])."' limit 1)
						where player = '".mysql_real_escape_string($_GET['player'])."' 
						and game = '".mysql_real_escape_string($_GET['game'])."'") or die ('Error recalculating player\'s points: '.mysql_error());
		$query = mysql_query("delete from game".mysql_real_escape_string($_GET['game'])." where player = '".mysql_real_escape_string($_GET['player'])."' and word = '".mysql_real_escape_string($_GET['word'])."'");
		$query = mysql_query("update game".mysql_real_escape_string($_GET['game'])." set points=points+".strlen($_GET['word'])." 
						where word='".mysql_real_escape_string($_GET['word'])."'") or die ('Error: '.mysql_error());
		$query = mysql_query("update playerstatus set points=points+".strlen($_GET['word'])." 
						where player != '".mysql_real_escape_string($_GET['player'])."' 
						and game = '".mysql_real_escape_string($_GET['game'])."' 
						and exists(select player from game".mysql_real_escape_string($_GET['game'])." 
							where word = '".mysql_real_escape_string($_GET['word'])."' 
							and game".mysql_real_escape_string($_GET['game']).".player = playerstatus.player)") or die ('Error recalculating other player\'s points: '.mysql_error());
		break;
	case "showgames":
		$return = [];
		$where = "";
		switch($_GET['mode'])
		{
			case 'all':
				break;
			case 'own':
				$where = "where starter='".mysql_real_escape_string($_GET['player'])."' or '".mysql_real_escape_string($_GET['player'])."' in (select player from playerstatus where game=games.id)";
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
		
		$query = mysql_query("select *, TIMESTAMPDIFF(MINUTE,date,CURRENT_TIMESTAMP()) as starttime from games ".$where." order by date desc") or die ('Error: '.mysql_error());
		while ($result = mysql_fetch_array($query))
		{
			$result['starter'] = umlaute($result['starter']);
			$return[]=$result;
			$return[sizeof($return)-1]['players'] = [];
			
			$playerquery = mysql_query("select player, points from playerstatus where game=".$result['id']) or die ('Error: '.mysql_error());
			while ($playerresult = mysql_fetch_array($playerquery))
			{
				$playerresult['player'] = umlaute($playerresult['player']);
				$return[sizeof($return)-1]['players'][] = $playerresult;
			}
		}
		echo json_encode($return);
		break;
	case "finishrequest":
		$return = [];
		$return["players"] = [];
		$query = mysql_query("select player, status, points, TIMESTAMPDIFF(MINUTE,activity,CURRENT_TIMESTAMP()) as last_activity from playerstatus where game = ".mysql_real_escape_string($_GET['game'])." order by last_activity") or die ('Error: '.mysql_error());
		while ($result = mysql_fetch_array($query))
		{
			$result['player'] = umlaute($result['player']);
			$return["players"][] = $result;
		}
		// gamestatus
		$return["gamestatus"] = 0;
		$query = mysql_query("select status from games where id=".mysql_real_escape_string($_GET['game']));
		while ($result = mysql_fetch_array($query))
		{
			$return["gamestatus"] = $result['status'];
		}
		// all words and points
		$return["words"] = [];
		$query = mysql_query("select * from game".mysql_real_escape_string($_GET['game'])) or die ('Error: '.mysql_error());
		while ($result = mysql_fetch_array($query))
		{
			$result['player'] = umlaute($result['player']);
			$return["words"][] = $result;
		}
		echo json_encode($return);
		break;
	case "datarequest": 
		$query = mysql_query("update playerstatus set activity = CURRENT_TIMESTAMP() where game=".mysql_real_escape_string($_GET['game'])." and player='".mysql_real_escape_string($_GET['player'])."'");
		$return = [];
		$return["players"] = [];
		$query = mysql_query("select player, status, TIMESTAMPDIFF(MINUTE,activity,CURRENT_TIMESTAMP()) as last_activity from playerstatus where game = ".mysql_real_escape_string($_GET['game'])." order by last_activity") or die ('Error: '.mysql_error());
		while ($result = mysql_fetch_array($query))
		{
			$result['player'] = umlaute($result['player']);
			$return["players"][] = $result;
		}
		$return["words"] = [];
		$query = mysql_query
			("select word, points from game".mysql_real_escape_string($_GET['game'])." where player='".mysql_real_escape_string($_GET['player'])."'"
			) or die ('Error: '.mysql_error());
		while ($result = mysql_fetch_array($query))
		{
			$return["words"][] = $result;
		}
		$query = mysql_query("select points from playerstatus where game = ".mysql_real_escape_string($_GET['game'])." and player='".mysql_real_escape_string($_GET['player'])."'") or die ('Error: '.mysql_error());
		while ($result = mysql_fetch_array($query))
		{
			$return["ownpoints"] = $result;
		}
		echo json_encode($return); 
		break;
	case "joingame":
		$new = true;
		$query = mysql_query("select player from playerstatus where player='".mysql_real_escape_string($_GET['player'])."' and game=".mysql_real_escape_string($_GET['game']));
		while ($result = mysql_fetch_array($query))
		{
			$new = false;
		}
		if ($new)
		{
			$query = mysql_query("insert into playerstatus (game, player, status, activity) values ('".mysql_real_escape_string($_GET['game'])."', '".mysql_real_escape_string($_GET['player'])."', '0', CURRENT_TIMESTAMP())") or die ('Error: '.mysql_error());
			// calculate all words new
			// all words get their lettercount additionally
			$query = mysql_query("update game".mysql_real_escape_string($_GET['game'])." set points = points+char_length(word)") or die('Error: '.mysql_error());
		}
		calcpoints(mysql_real_escape_string($_GET['game']));
		$query = mysql_query("select player from playerstatus where game=".mysql_real_escape_string($_GET['game']));
		$players = mysql_num_rows($query);
		if ($players == 2)
		{
			$query = mysql_query("update games set status=1 where id=".mysql_real_escape_string($_GET['game']));
		}
		break;
	case "leavegame":
		$query = mysql_query("delete from playerstatus where player='".mysql_real_escape_string($_GET['player'])."' and game=".mysql_real_escape_string($_GET['game'])) or die('Error: '.mysql_error());
		if ($_GET['players'] == '1')
		{
			$query = mysql_query("delete from games where id=".mysql_real_escape_string($_GET['game'])) or die('Error: '.mysql_error());
			$query = mysql_query("drop table game".mysql_real_escape_string($_GET['game'])) or die('Error: '.mysql_error());
		}
		else 
		{ 
			$query = mysql_query("update game".mysql_real_escape_string($_GET['game'])." set points = points-char_length(word) 
							where exists(select word from (select * from game".mysql_real_escape_string($_GET['game']).") as checktbl 
							where player = '".mysql_real_escape_string($_GET['player'])."' and checktbl.word = game".mysql_real_escape_string($_GET['game']).".word)") or die('Error: '.mysql_error());
			$query = mysql_query("delete from game".mysql_real_escape_string($_GET['game'])." where player='".mysql_real_escape_string($_GET['player'])."'") or die ('Error: '.mysql_error());
			calcpoints(mysql_real_escape_string($_GET['game']));
		}
		break;
	case "finishgame":
		$query = mysql_query("update playerstatus set status = 2 where game=".mysql_real_escape_string($_GET['game'])." and player='".mysql_real_escape_string($_GET['player'])."'") or die('Error: '.$mysql_error());
		// if all players finished, set game status to two
		$query = mysql_query("select distinct(status) from playerstatus where game=".mysql_real_escape_string($_GET['game']));
		if (mysql_num_rows($query)==1)
		{
			$query = mysql_query("update games set status = 2 where id=".mysql_real_escape_string($_GET['game']));
		}
		break;
	default: break;
}

function calcpoints($game)
{
	$query = mysql_query("update playerstatus set points =
			(select sum(game".$game.".points) as fullpoints from game".$game." 
			where game".$game.".player=playerstatus.player group by game".$game.".player)
		where game = ".$game) or die ('Error: '.mysql_error());
}

function allpointsnew()
{
	$games = [];
	$query = mysql_query("select distinct(game) from playerstatus") or die ('Error: '.mysql_error());
	while ($result = mysql_fetch_array($query))
	{
		$games[] = $result['game'];
	}
	
	foreach($games as $game)
	{
		$query = mysql_query("update game".$game." set points = 
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
		)") or die ('Error calculating points for game '.$game.': '.mysql_error());
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
