<?php
if ($dailyday) # set by canonical() for any day's page, with a daily or without
	echo '<div id="dailyhead">
			<div id="dailylanguage" class="modes">
				<a href="'.dailyurl($dailyday, 'de').'" title="'.t('dictionarylanguage', t('languagede')).'"'.($dailylanguage == 'de' ? ' class="selected"' : '').'>DE</a>
				<a href="'.dailyurl($dailyday, 'en').'" title="'.t('dictionarylanguage', t('languageen')).'"'.($dailylanguage == 'en' ? ' class="selected"' : '').'>EN</a>
			</div>
			<h3>'.t('daily').' #'.daynumber($dailyday).'</h3>
			<span id="dailycount"></span>
		</div>';
if (!$currentgame) # queried by canonical() in meta.php, which needs the word for the URL before any output
{
	echo '<p class="warning">'.t($dailyday ? 'nodaily' : 'gamegone').'</p>';
	return;
}
$game = (int)$currentgame['id'];
$over = ($currentgame['daily'] and $currentgame['daily'] < date('Y-m-d')); // only today's daily takes words

$mystatus = -1;
$playing = [];
	// a daily has a row for everyone who played it, and only this player's matters
	foreach ($mysql->execute_query("select user_id, display_name, status from player where game_id = ? and (user_id = ? or ? is null) order by joined_at",
		[$game, $_SESSION['user_id'], $currentgame['daily']])->fetch_all(MYSQLI_ASSOC) as $row)
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

if (!$_SESSION['user_id'] and $currentgame['status'] != 3 and !$currentgame['daily']) # a daily's first word creates the player
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
  var gamestatus = <?php echo json_encode($over ? 3 : (int)$currentgame['status']); ?>; // a daily is over for everyone at midnight
  var daily = <?php echo json_encode($currentgame['daily'] ? ['number' => daynumber($currentgame['daily'])] : null); ?>; // an object, since #0 is a number too
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
anagrambutton.onclick = function() { select(this); writeletters(anagram(originalword, prepare(solution.map(function(row) { return row.word; })))); };
if (mystatus != 3) { anagrambutton.remove(); }

document.addEventListener('DOMContentLoaded', function() {
  // leaving a daily would delete the day's game along with its only player
  if (!daily && (isplayer || mystatus != 3)) { document.getElementById("leave").style.display = 'block'; }
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
  else if (daily) // opening it is not playing it: the first word makes the player
  {
	sortwords();
	get("receiver.php?action=datarequest&game="+game, receivedata);
  }
  else
  {
	sortwords();
	document.getElementById("finish").style.display = 'block';
	post({action: "joingame", game: game}, receivedata);
  }
  // played alone, a daily has nothing changing underneath it
  if (!daily)
  {
	gamedata = setTimeout(poll, pollwait);
	document.addEventListener('visibilitychange', repoll);
  }
});

// the same badges the game list draws, from the settings under the names this page gave them
settingsbadges(document.getElementById("settings"), {language: language, umlauts: substitute, flexion: flexion,
	dictionary: dictionaryonly, private: isprivate, maxplayers: maxplayers, timelimit: timelimit});
</script>

<?php
if ($currentgame['daily'] and !$over and !$_SESSION['user_id'])
	echo '<div id="dailyintro">'.t('dailyintro').'</div>';
?>
<p id="gamemessage" class="hide"></p>
<div id="inputline">
	<div id="back" onClick="backspace();"><?php echo t('back'); ?></div>
	<input type="text" id="input" value="" autofocus autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
	<div id="submit" onClick="submitword();"><?php echo t('submit'); ?></div>
</div>
<?php
if ($currentgame['daily'])
{
	echo '<div id="result" class="hide">
			<div class="share"></div>
			<button type="button" class="copy" title="'.t('copy').'" onclick="getSelection().selectAllChildren(this.previousElementSibling); navigator.clipboard.writeText(this.previousElementSibling.textContent);">⧉</button>
		</div>';
}
?>
<div id="wordbox">
	<div class="collapsible sort">
		<div class="modegroup">
		<div id="sortbuttons" class="modes">
		<button type="button" class="selected" onClick="select(this); sortmode = 'standard'; sortwords();"><?php echo t('sortstandard'); ?></button>
		<button type="button" onClick="select(this); sortmode = 'chrono'; sortwords();"><?php echo t('sortchrono'); ?></button>
		<button type="button" onClick="select(this); sortmode = 'alpha'; sortwords();"><?php echo t('alphabetical'); ?></button>
		<button type="button" onClick="select(this); sortmode = 'length'; sortwords();"><?php echo t('sortlength'); ?></button>
		<?php if (!$currentgame['daily']) echo '<button type="button" onClick="select(this); sortmode = \'points\'; sortwords();">'.t('sortpoints').'</button>'; ?>
		</div>
		<div id="orderbuttons" class="modes">
		<button type="button" class="selected" title="<?php echo t('ascending'); ?>" onClick="select(this); direction = 1; sortwords();">&darr;</button>
		<button type="button" title="<?php echo t('descending'); ?>" onClick="select(this); direction = -1; sortwords();">&uarr;</button>
		</div>
		</div>
	</div>
	<div id="words"></div>
</div>
