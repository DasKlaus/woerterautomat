<?php
$game = (int)($_GET['game'] ?? 0);
$currentgame = $mysql->execute_query("select source_word, status, language, umlauts, flexion, dictionary, maxplayers, timelimit, private, created_by_name,
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
  var selfid = <?php echo json_encode((int)$_SESSION['user_id']); ?>;
  var isplayer = <?php echo json_encode($mystatus>=0); ?>; // has a player row here, regardless of finish status
  var gamestatus = <?php echo json_encode((int)$currentgame['status']); ?>;
  var language = <?php echo json_encode($currentgame['language']); ?>;
  substitute = <?php echo json_encode($currentgame['umlauts']>0); ?>;
  var flexion = <?php echo json_encode($currentgame['flexion']>0); ?>;
  var dictionaryonly = <?php echo json_encode($currentgame['dictionary']>0); ?>;
  var isprivate = <?php echo json_encode($currentgame['private']>0); ?>;
  var maxplayers = <?php echo json_encode($currentgame['maxplayers']); ?>;
  var timelimit = <?php echo json_encode((int)$currentgame['timelimit']); ?>;
  var sortmode = 'standard';
  var direction = 1;
  var words = <?php echo json_encode(array_merge([$currentgame['source_word']], array_column($words, 'word'))); ?>;
  var wordpoints = [];
  var ownpoints = 0;
  var allwordsdump = [];
  var allplayersdump = [];
  var reactions = [];
  var solution = [];
  var uniquewords = [];
  var version = -1;
  var pollwait = 5000;
  var playerlist = [];
  var playerstamp = 0;
  var gamedata;

if (gamestatus == 3) { mystatus = 3; } // a guest who never joined still has mystatus -1 even once the game is finished

writeletters(originalword);
// kept out of the DOM rather than hidden while playing: gone, its neighbour rounds off as an only child
var anagrambutton = document.getElementById("anagram");
anagrambutton.onclick = function() { select(this); writeletters(anagram(originalword, prepare(solution))); };
if (mystatus != 3) { anagrambutton.remove(); }

document.addEventListener('DOMContentLoaded', function() {
  if (isplayer || mystatus != 3) { document.getElementById("leave").style.display = 'block'; }
  document.getElementById("input").value = '';
  document.getElementById('input').onkeydown = keydownhandle;
  document.getElementById('input').oninput = resyncletters;

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

/* append settings */ // TODO: do in php?
var settings = document.getElementById("settings");
var span = document.createElement('span');
span.textContent = language.toUpperCase();
settings.appendChild(span);
if (substitute) {
	span = document.createElement('span');
	span.textContent = "Ä→AE";
	span.setAttribute('title', 'Umlaute können substituiert werden');
	settings.appendChild(span);
}
if (flexion) {
	span = document.createElement('span');
	span.textContent = "⎇";
	span.setAttribute('title', 'Flexionsformen wie Mehrzahlen, Deklinationen, Konjugationen erlaubt');
	settings.appendChild(span);
}
if (dictionaryonly) {
	span = document.createElement('span');
	span.textContent = "🕮";
	span.setAttribute('title', 'nur Wörter aus dem Wörterbuch bringen Punkte');
	settings.appendChild(span);
}
if (isprivate) {
	span = document.createElement('span');
	span.textContent = "⚿";
	span.setAttribute('title', 'privates Spiel, unsichtbar für andere, zum Einladen URL teilen');
	settings.appendChild(span);
}
if (maxplayers>0) {
	span = document.createElement('span');
	span.textContent = maxplayers+"☺";
	span.setAttribute('title', 'bis zu '+maxplayers+' Spieler');
	settings.appendChild(span);
}
if (timelimit>0) {
	var limit = limittext(timelimit);
	span = document.createElement('span');
	span.textContent = limit[0];
	span.setAttribute('title', limit[1]);
	settings.appendChild(span);
}
</script>

<p id="gamemessage" class="hide"></p>
<div id="inputline">
	<div id="back" onClick="backspace();">zur&uuml;ck</div>
	<input type="text" id="input" value="" autofocus>
	<div id="submit" onClick="submitword();">absenden</div>
</div>
<div id="wordbox">
	<div class="collapsible sort">
		<div class="modegroup">
		<div id="sortbuttons" class="modes">
		<button type="button" class="selected" onClick="select(this); sortmode = 'standard'; sortwords();">standard</button>
		<button type="button" onClick="select(this); sortmode = 'chrono'; sortwords();">chronologisch</button>
		<button type="button" onClick="select(this); sortmode = 'alpha'; sortwords();">alphabetisch</button>
		<button type="button" onClick="select(this); sortmode = 'length'; sortwords();">nach L&auml;nge</button>
		<button type="button" onClick="select(this); sortmode = 'points'; sortwords();">nach Punkten</button>
		</div>
		<div id="orderbuttons" class="modes">
		<button type="button" class="selected" title="aufsteigend" onClick="select(this); direction = 1; sortwords();">&darr;</button>
		<button type="button" title="absteigend" onClick="select(this); direction = -1; sortwords();">&uarr;</button>
		</div>
		</div>
	</div>
	<div id="words"></div>
</div>
