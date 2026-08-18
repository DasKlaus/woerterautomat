<?php
$mode = $_GET['mode'] ?? 'relevant';
$page = max(1, (int)($_GET['page'] ?? 1));
?>
<script src="games.js"></script>
<script>
var mode = <?php echo json_encode($mode); ?>;
var page = <?php echo json_encode($page); ?>;
$( document ).ready(function() {
	jQuery.get( "receiver.php?action=showgames&mode="+mode+"&page="+page, gamedata);
	var gamerequest = setInterval(function(){jQuery.get( "receiver.php?action=showgames&mode="+mode+"&page="+page, gamedata);}, 60000);
});
</script>
<div id="games"></div>
