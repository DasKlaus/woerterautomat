<script src="games.js"></script>
<script>
var mode = 'all';
$( document ).ready(function() {
	jQuery.get( "receiver.php?action=showgames&mode="+mode, gamedata);
	var gamerequest = setInterval(function(){jQuery.get( "receiver.php?action=showgames&mode="+mode, gamedata);}, 60000);
});
</script>
<div id="games"></div>
