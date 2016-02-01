<?php
$status = 0;
$query = mysql_query("SELECT status FROM playerstatus WHERE player = '".$_SESSION['player']."' AND game = ".$_GET['game']) or die ('Error: '.mysql_error());
while ($result = mysql_fetch_array($query))
{
	$status = $result['status'];
}

$word = '';
$gamestatus = 0;
$query = mysql_query("select word, status from games where id = ".$_GET['game']) or die ('Error: '.mysql_error());
while ($result = mysql_fetch_array($query))
{
	$word = $result['word'];
	$gamestatus = $result['status'];
}

$words = [];
$query = mysql_query("select word from game".$_GET['game']." where player='".$_SESSION['player']."'") or die ('Error: '.mysql_error());
while ($result = mysql_fetch_array($query))
{
	$words[] = $result['word'];
}

?>
<script src="game.js"></script>
<script>
  var game = <?php echo $_GET['game']; ?>;
  var player = "<?php echo $_SESSION['player']; ?>";
  var originalword = '<?php echo $word; ?>';
  var status = <?php echo $status; ?>;
  var gamestatus = <?php echo $gamestatus; ?>;
  var sortmode = 'standard';
  var lettermode = 'original';
  var words = [originalword<?php foreach($words as $word) { echo ",'".$word."'"; } ?>];
  var pointdump = [];
  var allwordsdump = [];

$( document ).ready(function() {
  if (gamestatus != 2)
  {
	jQuery.get( "receiver.php?action=joingame&game="+game+"&player="+player);
  } else { status = 2; }
  writeletters(originalword); 
  document.getElementById("player").style.display = 'none';
  document.getElementById("input").value = '';
  document.getElementById('input').onkeypress = keyhandle;

  if (status == 2) 
  { 
	document.getElementById("finish").style.display = 'none';
	document.getElementById('inputline').style.display = 'none';
	document.getElementById('player').style.display = 'block';
	document.getElementById('lettersortbuttons').style.display = 'none';
	jQuery.get( "receiver.php?action=finishrequest&game="+game+"&player="+player, finishdata );
	var gamedata = setInterval(finisher, 5000);
  }
  else
  {
	sortwords();
	jQuery.get( "receiver.php?action=datarequest&game="+game+"&player="+player, receivedata );
	var gamedata = setInterval(function(){jQuery.get( "receiver.php?action=datarequest&game="+game+"&player="+player, receivedata );}, 5000);
  }
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
	<div id="points" onClick="sortmode = 'points'; pointdump=document.getElementById('words').cloneNode(true); sortwords();">nach Punkten</div>
	<div id="player" onClick="sortmode = 'player'; sortwords();"></div>
	<hr style="clear: both; margin-bottom: 5px;">
	<div id="words"></div>
	<div style="clear: both;"></div>
</div>
