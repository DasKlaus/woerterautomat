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
