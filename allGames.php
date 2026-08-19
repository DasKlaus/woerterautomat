<?php
$mode = $_GET['mode'] ?? 'relevant';
$page = max(1, (int)($_GET['page'] ?? 1));
?>
<script src="games.js"></script>
<script>
var mode = <?php echo json_encode($mode); ?>;
var page = <?php echo json_encode($page); ?>;
$( document ).ready(function() {
	listgames();
	setInterval(function(){ if (!document.hidden) { listgames(); } }, 60000);
	document.addEventListener('visibilitychange', function(){ if (!document.hidden) { listgames(); } });
});
</script>
<div id="games"></div>
