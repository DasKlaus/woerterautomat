<?php
$game = (int)($_GET['game'] ?? 0);
$currentgame = $mysql->execute_query("select source_word, status, language, umlauts, flexion created_by_name,
		timestampdiff(minute, created_at, now()) as starttime from game where id = ?", [$game])->fetch_assoc();
if (!$currentgame)
{
	echo '<p class="warning">Dieses Spiel gibt es nicht mehr.</p>';
	return;
}

$mystatus = -1;
$playing = [];
	foreach ($mysql->execute_query("select user_id, display_name, status from player where game_id = ? order by joined_at", [$game])->fetch_all(MYSQLI_ASSOC) as $row)
	{
		$playing[] = $row['display_name'] ?: "Gast";
		if ($row["user_id"] == $_SESSION['user_id']) { $mystatus = $row["status"]; }
	}
function timeago($minutes)
{
	$count = $minutes; $unit = "Minute"; $plural = "n";
	if ($minutes >= 1440) { $count = (int)round($minutes/1440); $unit = "Tag"; $plural = "en"; }
	elseif ($minutes >= 60) { $count = (int)round($minutes/60); $unit = "Stunde"; $plural = "n"; }
	if ($count < 1) { return "gerade eben"; }
	return "vor ".$count." ".$unit.($count == 1 ? "" : $plural);
}

if ($mystatus<0 and $currentgame['status'] == 2)
{
	$playing = [];
	foreach ($mysql->execute_query("select display_name from player where game_id = ? order by joined_at", [$game])->fetch_all(MYSQLI_ASSOC) as $row)
	{
		$playing[] = $row['display_name'] ?: "Gast";
	}
	echo '<h2>'.htmlspecialchars($currentgame['source_word'], ENT_QUOTES, 'UTF-8').'</h2>
		<p>gestartet '.timeago($currentgame['starttime']).' auf '.(($currentgame['language'] == 'en') ? 'Englisch' : 'Deutsch')
		.' von '.htmlspecialchars($currentgame['created_by_name'] ?: "Gast", ENT_QUOTES, 'UTF-8').' '.($currentgame['flexion'] ? 'mit' : 'ohne').' Flexionsformen<br>
		mit '.htmlspecialchars(implode(', ', $playing), ENT_QUOTES, 'UTF-8').'</p>';
	echo '<p class="warning">Dieses Spiel nimmt keine weiteren Spieler an.</p>';
	return;
}

if (!$_SESSION['user_id'] and $currentgame['status'] != 3)
{
	echo '<h2>'.htmlspecialchars($currentgame['source_word'], ENT_QUOTES, 'UTF-8').'</h2>
		<p>gestartet '.timeago($currentgame['starttime']).' auf '.(($currentgame['language'] == 'en') ? 'Englisch' : 'Deutsch')
		.' von '.htmlspecialchars($currentgame['created_by_name'] ?: "Gast", ENT_QUOTES, 'UTF-8').' '.($currentgame['flexion'] ? 'mit' : 'ohne').' Flexionsformen<br>
		mit '.htmlspecialchars(implode(', ', $playing), ENT_QUOTES, 'UTF-8').'</p>
		<p>Bilde aus den Buchstaben des Wortes m&ouml;glichst viele andere W&ouml;rter. Jedes Wort bringt so viele Punkte, wie es Buchstaben hat, multipliziert mit der Anzahl der Mitspieler, die es nicht gefunden haben.</p>
		<p>Vergib einen Namen, um mitzuspielen, oder lass das Feld frei, um anonym zu spielen. Wenn du einen Authentifizierungs-Code hast, kannst du dich mit diesem anmelden.</p>';
	identityForm();
	return;
}

$words = $mysql->execute_query("select word from word where game_id = ? and user_id = ?", [$game, $_SESSION['user_id']])->fetch_all(MYSQLI_ASSOC);
?>
<script src="game.js"></script>
<script>
  var game = <?php echo json_encode($game); ?>;
  var originalword = <?php echo json_encode($currentgame['source_word']); ?>;
  var mystatus = <?php echo json_encode((int)$mystatus); ?>;
  var isplayer = <?php echo json_encode($mystatus<0); ?>;
  var gamestatus = <?php echo json_encode((int)$currentgame['status']); ?>;
  var language = <?php echo json_encode($currentgame['language']); ?>;
  var umlauts = <?php echo json_encode($currentgame['umlauts']>0); ?>;
  var flexion = <?php echo json_encode($currentgame['flexion']>0); ?>;
  var sortmode = 'standard';
  var words = <?php echo json_encode(array_merge([$currentgame['source_word']], array_column($words, 'word'))); ?>;
  var wordpoints = [];
  var ownpoints = 0;
  var allwordsdump = [];
  var allplayersdump = [];
  var uniquewords = [];
  var version = -1;
  var pollwait = 5000;
  var playerlist = [];
  var playerstamp = 0;
  var gamedata;

if (gamestatus == 3) { mystatus = 3; } // TODO: why? should be already 3!
substitution = umlauts;

writeletters(originalword);
document.getElementById("anagram").remove(); // TODO: once disctionaries implemented, return function on finished games
substitute = game[""]

document.addEventListener('DOMContentLoaded', function() {
  if (isplayer || mystatus != 3) { document.getElementById("leave").style.display = 'block'; }
  document.getElementById("input").value = '';
  document.getElementById('input').onkeypress = keyhandle;
  document.getElementById('input').onkeydown = keydownhandle;

  if (mystatus == 3)
  {
	document.getElementById('inputline').style.display = 'none';
	playerbutton();
	get("receiver.php?action=finishrequest&game="+game, finishdata);
  }
  else
  {
	sortwords();
	document.getElementById("finish").style.display = 'block';
	post({action: "joingame", game: game}, receivedata);
  }
  gamedata = setTimeout(poll, pollwait);
  document.addEventListener('visibilitychange', repoll);
});
</script>

<div id="inputline" style="">
	<div id="back" onClick="backspace();">zur&uuml;ck</div>
	<input type="text" id="input" value="" autofocus>
	<div id="submit" onClick="submitword();">absenden</div>
</div>
<div id="wordbox" style="">
	<div id="sortbuttons" class="modes collapsible sort">
	<button type="button" class="selected" onClick="select(this); sortmode = 'standard'; sortwords();">standard</button>
	<button type="button" onClick="select(this); sortmode = 'chrono'; sortwords();">chronologisch</button>
	<button type="button" onClick="select(this); sortmode = 'alpha'; sortwords();">alphabetisch</button>
	<button type="button" onClick="select(this); sortmode = 'length'; sortwords();">nach L&auml;nge</button>
	<button type="button" onClick="select(this); sortmode = 'points'; sortwords();">nach Punkten</button>
	</div>
	<div id="words"></div>
</div>
