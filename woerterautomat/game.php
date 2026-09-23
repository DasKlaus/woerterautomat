<?php
$game = (int)($_GET['game'] ?? 0);
if (!$currentgame) # queried by canonical() in meta.php, which needs the word for the URL before any output
{
	echo '<p class="warning">'.t('gamegone').'</p>';
	return;
}

$mystatus = -1;
$playing = [];
	foreach ($mysql->execute_query("select user_id, display_name, status from player where game_id = ? order by joined_at", [$game])->fetch_all(MYSQLI_ASSOC) as $row)
	{
		$playing[] = $row['display_name'] ?: t('guest');
		if ($row["user_id"] == $_SESSION['user_id']) { $mystatus = $row["status"]; }
	}
function timeago($minutes)
{
	$count = $minutes; $unit = "minute";
	if ($minutes >= 525600) { $count = (int)round($minutes/525600); $unit = "year"; }
	elseif ($minutes >= 1440) { $count = (int)round($minutes/1440); $unit = "day"; }
	elseif ($minutes >= 60) { $count = (int)round($minutes/60); $unit = "hour"; }
	if ($count < 1) { return t('justnow'); }
	return t('ago', $count, plural($unit, $count));
}

function gameheader()
{
	global $currentgame, $playing;
	echo '<h2>'.htmlspecialchars($currentgame['source_word'], ENT_QUOTES, 'UTF-8').'</h2>
		<p>'.t('gamestarted', timeago($currentgame['starttime']), t('language'.$currentgame['language']),
			htmlspecialchars($currentgame['created_by_name'] ?: t('guest'), ENT_QUOTES, 'UTF-8'),
			t($currentgame['flexion'] ? 'withflexion' : 'withoutflexion'),
			htmlspecialchars(implode(', ', $playing), ENT_QUOTES, 'UTF-8')).'</p>';
}

if ($mystatus<0 and $currentgame['status'] == 2)
{
	gameheader();
	echo '<p class="warning">'.t('gamefull').'</p>';
	return;
}

if (!$_SESSION['user_id'] and $currentgame['status'] != 3)
{
	gameheader();
	echo t('gameintro').t('guestjoin');
	identityForm($language);
	return;
}

$words = $mysql->execute_query("select word from word where game_id = ? and user_id = ? order by created_at", [$game, $_SESSION['user_id']])->fetch_all(MYSQLI_ASSOC);
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
  var touch = matchMedia('(pointer: coarse)').matches; // focusing the input there opens the on-screen keyboard

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
  document.getElementById('input').oninput = function(e) { if (!e.isComposing) { resyncletters(); } };
  document.getElementById('input').oncompositionend = resyncletters;

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

// the same badges the game list draws, from the settings under the names this page gave them
settingsbadges(document.getElementById("settings"), {language: language, umlauts: substitute, flexion: flexion,
	dictionary: dictionaryonly, private: isprivate, maxplayers: maxplayers, timelimit: timelimit});
</script>

<p id="gamemessage" class="hide"></p>
<div id="inputline">
	<div id="back" onClick="backspace();"><?php echo t('back'); ?></div>
	<input type="text" id="input" value="" autofocus autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
	<div id="submit" onClick="submitword();"><?php echo t('submit'); ?></div>
</div>
<div id="wordbox">
	<div class="collapsible sort">
		<div class="modegroup">
		<div id="sortbuttons" class="modes">
		<button type="button" class="selected" onClick="select(this); sortmode = 'standard'; sortwords();"><?php echo t('sortstandard'); ?></button>
		<button type="button" onClick="select(this); sortmode = 'chrono'; sortwords();"><?php echo t('sortchrono'); ?></button>
		<button type="button" onClick="select(this); sortmode = 'alpha'; sortwords();"><?php echo t('alphabetical'); ?></button>
		<button type="button" onClick="select(this); sortmode = 'length'; sortwords();"><?php echo t('sortlength'); ?></button>
		<button type="button" onClick="select(this); sortmode = 'points'; sortwords();"><?php echo t('sortpoints'); ?></button>
		</div>
		<div id="orderbuttons" class="modes">
		<button type="button" class="selected" title="<?php echo t('ascending'); ?>" onClick="select(this); direction = 1; sortwords();">&darr;</button>
		<button type="button" title="<?php echo t('descending'); ?>" onClick="select(this); direction = -1; sortwords();">&uarr;</button>
		</div>
		</div>
	</div>
	<div id="words"></div>
</div>
