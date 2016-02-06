<script src="games.js"></script>
<script>
var player = "<?php echo $_SESSION['player']; ?>";
var mode = 'all';
$( document ).ready(function() {
	jQuery.get( "receiver.php?action=showgames&player="+player+"&mode="+mode, gamedata);
	var gamerequest = setInterval(function(){jQuery.get( "receiver.php?action=showgames&player="+player+"&mode="+mode, gamedata);}, 60000);
});
</script>
<div id="games"></div>
<?php
/*
$query = mysql_query("select * from games order by status") or die ('Error: '.mysql_error());
while ($result = mysql_fetch_array($query))
{
	$status = "";
	switch ($result['status']) {
		case 0: $status = "neu"; break;
		case 1: $status = "laufend"; break;
		case 2: $status = "beendet"; break;
		default: break;
	}
	echo '<a class="game" href="?go=game&game='.$result['id'].'"><h2>'.$result['word'].'</h2>
		('.$status.') gestartet am '.$result['date'].' von '.$result['starter'].' mit ';
	$playerquery = mysql_query("select player from playerstatus where game=".$result['id']) or die ('Error: '.mysql_error());
	while ($playerresult = mysql_fetch_array($playerquery)) 
	{
		echo $playerresult['player']." ";
	}
	echo '</a>';
}*/
?>
