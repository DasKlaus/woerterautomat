<?php
$game = (int)($_GET['game'] ?? 0);
$currentgame = $mysql->execute_query("select source_word, status, language, flexion, created_by_name,
		timestampdiff(minute, created_at, now()) as starttime from game where id = ?", [$game])->fetch_assoc();
if (!$currentgame)
{
	echo 'Dieses Spiel gibt es nicht mehr.';
	return;
}

function timeago($minutes)
{
	$count = $minutes; $unit = "Minute"; $plural = "n";
	if ($minutes >= 1440) { $count = (int)round($minutes/1440); $unit = "Tag"; $plural = "en"; }
	elseif ($minutes >= 60) { $count = (int)round($minutes/60); $unit = "Stunde"; $plural = "n"; }
	if ($count < 1) { return "gerade eben"; }
	return "vor ".$count." ".$unit.($count == 1 ? "" : $plural);
}

if (!$_SESSION['user_id'] and $currentgame['status'] != 2)
{
	$playing = [];
	foreach ($mysql->execute_query("select display_name from player where game_id = ? order by joined_at", [$game])->fetch_all(MYSQLI_ASSOC) as $row)
	{
		$playing[] = $row['display_name'] ?: "Gast";
	}
	echo '<h2 id="letters">'.htmlspecialchars($currentgame['source_word'], ENT_QUOTES, 'UTF-8').'</h2>
		<p>gestartet '.timeago($currentgame['starttime']).' auf '.(($currentgame['language'] == 'en') ? 'Englisch' : 'Deutsch')
		.' von '.htmlspecialchars($currentgame['created_by_name'] ?: "Gast", ENT_QUOTES, 'UTF-8').' '.($currentgame['flexion'] ? 'mit' : 'ohne').' Flexionsformen<br>
		mit '.htmlspecialchars(implode(', ', $playing), ENT_QUOTES, 'UTF-8').'</p>
		<p>Bilde aus den Buchstaben des Wortes m&ouml;glichst viele andere W&ouml;rter. Jedes Wort bringt so viele Punkte, wie es Buchstaben hat, multipliziert mit der Anzahl der Mitspieler, die es nicht gefunden haben.</p>
		<p>Vergib einen Namen, um mitzuspielen, oder lass das Feld frei, um anonym zu spielen. Wenn du einen Authentifizierungs-Code hast, kannst du dich mit diesem anmelden.</p>';
	identityForm();
	return;
}

$mystatus = $mysql->execute_query("select status from player where game_id = ? and user_id = ?", [$game, $_SESSION['user_id']])->fetch_column();

$words = $mysql->execute_query("select word from word where game_id = ? and user_id = ?", [$game, $_SESSION['user_id']])->fetch_all(MYSQLI_ASSOC);
?>
<script src="game.js"></script>
<script>
  var game = <?php echo json_encode($game); ?>;
  var originalword = <?php echo json_encode($currentgame['source_word']); ?>;
  var mystatus = <?php echo json_encode((int)$mystatus); ?>;
  var isplayer = <?php echo json_encode($mystatus !== false); ?>;
  var gamestatus = <?php echo json_encode((int)$currentgame['status']); ?>;
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

document.addEventListener('DOMContentLoaded', function() {
  if (gamestatus == 2) { mystatus = 2; }
  if (isplayer || mystatus != 2) { document.getElementById("leave").style.display = 'block'; }
  writeletters(originalword);
  document.getElementById("input").value = '';
  document.getElementById('input').onkeypress = keyhandle;
  document.getElementById('input').onkeydown = keydownhandle;

  if (mystatus == 2)
  {
	document.getElementById('inputline').style.display = 'none';
	document.getElementById('lettersortbuttons').style.display = 'none';
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

<div id="letterline" style="">
	<div id="lettersortbuttons" class="modes">
	<div onClick="select(this); writeletters(randomstring(originalword));">mischen</div>
	<div onClick="select(this); writeletters(sortstring(originalword));">alphabetisch</div>
	<div class="selected" onClick="select(this); writeletters(originalword);">original</div>
	</div>

	<div id="letters"></div>
</div>
<div id="inputline" style="">
	<div id="back" onClick="backspace();">zur&uuml;ck</div>
	<input type="text" id="input" value="" autofocus>
	<div id="submit" onClick="submitword();">absenden</div>
</div>
<div id="wordbox" style="">
	<div id="sortbuttons" class="modes">
	<div class="selected" onClick="select(this); sortmode = 'standard'; sortwords();">standard</div>
	<div onClick="select(this); sortmode = 'chrono'; sortwords();">chronologisch</div>
	<div onClick="select(this); sortmode = 'alpha'; sortwords();">alphabetisch</div>
	<div onClick="select(this); sortmode = 'length'; sortwords();">nach L&auml;nge</div>
	<div onClick="select(this); sortmode = 'points'; sortwords();">nach Punkten</div>
	</div>
	<div id="words"></div>
</div>
