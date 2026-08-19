<?php
$game = (int)($_GET['game'] ?? 0);
$currentgame = $mysql->execute_query("select source_word, status from game where id = ?", [$game])->fetch_assoc();
if (!$currentgame)
{
	echo 'Dieses Spiel gibt es nicht mehr.';
	return;
}

$mystatus = (int)$mysql->execute_query("select status from player where game_id = ? and user_id = ?", [$game, $_SESSION['user_id']])->fetch_column();

$words = $mysql->execute_query("select word from word where game_id = ? and user_id = ?", [$game, $_SESSION['user_id']])->fetch_all(MYSQLI_ASSOC);
?>
<script src="game.js"></script>
<script>
  var game = <?php echo json_encode($game); ?>;
  var originalword = <?php echo json_encode($currentgame['source_word']); ?>;
  var mystatus = <?php echo json_encode($mystatus); ?>;
  var gamestatus = <?php echo json_encode((int)$currentgame['status']); ?>;
  var sortmode = 'standard';
  var lettermode = 'original';
  var words = <?php echo json_encode(array_merge([$currentgame['source_word']], array_column($words, 'word'))); ?>;
  var wordpoints = [];
  var allwordsdump = [];
  var allplayersdump = [];
  var uniquewords = [];
  var version = -1;
  var pollwait = 5000;
  var playerlist = [];
  var playerstamp = 0;
  var gamedata;

$( document ).ready(function() {
  if (gamestatus == 2) { mystatus = 2; }
  writeletters(originalword);
  document.getElementById("player").style.display = 'none';
  document.getElementById("input").value = '';
  document.getElementById('input').onkeypress = keyhandle;
  document.getElementById('input').onkeydown = keydownhandle;

  if (mystatus == 2)
  {
	document.getElementById("finish").style.display = 'none';
	document.getElementById('inputline').style.display = 'none';
	document.getElementById('player').style.display = 'block';
	document.getElementById('lettersortbuttons').style.display = 'none';
	jQuery.get( "receiver.php?action=finishrequest&game="+game, finishdata );
  }
  else
  {
	sortwords();
	jQuery.post( "receiver.php", {action: "joingame", game: game}, receivedata );
  }
  gamedata = setTimeout(poll, pollwait);
  document.addEventListener('visibilitychange', repoll);
});
</script>

<div id="letterline" style="">
	<div id="lettersortbuttons">
	<div id="shuffle" onClick="writeletters(randomstring(originalword));" style="margin-left: 180px;">mischen</div>
	<div id="abc" onClick="writeletters(sortstring(originalword));">alphabetisch</div>
	<div id="orig" onClick="writeletters(originalword);">original</div>
	</div>

	<div id="letters"></div>
</div>
<div id="inputline" style="">
	<div id="back" onClick="backspace();">zur&uuml;ck</div>
	<input type="text" id="input" value="" autofocus>
	<div id="submit" onClick="submitword();">absenden</div>
	<br style="clear: both;">
</div>
<div id="wordbox" style="">
	<div id="standard" onClick="sortmode = 'standard'; sortwords();">standard</div>
	<div id="chrono" onClick="sortmode = 'chrono'; sortwords();">chronologisch</div>
	<div id="alpha" onClick="sortmode = 'alpha'; sortwords();">alphabetisch</div>
	<div id="length" onClick="sortmode = 'length'; sortwords();">nach L&auml;nge</div>
	<div id="points" onClick="sortmode = 'points'; sortwords();">nach Punkten</div>
	<div id="player" onClick="sortmode = 'player'; sortwords();">nach Spielern</div>
	<hr style="clear: both; margin-bottom: 5px;">
	<div id="words"></div>
	<div style="clear: both;"></div>
</div>
