<?php
$mode = $_GET['mode'] ?? 'relevant';
$page = max(1, (int)($_GET['page'] ?? 1));
?>
<script src="games.js"></script>
<script>
var mode = <?php echo json_encode($mode); ?>;
var page = <?php echo json_encode($page); ?>;
document.addEventListener('DOMContentLoaded', function() {
	listgames();
	setInterval(function(){ if (!document.hidden) { listgames(); } }, 60000);
	document.addEventListener('visibilitychange', function(){ if (!document.hidden) { listgames(); } });
});
</script>
<?php if ((isset($_GET["go"]) and $_GET["go"]=="games") or !isset($_GET['go'])) { $mode = $_GET['mode'] ?? 'relevant'; ?>
	<div id="sortgames" class="collapsible sort"><div class="modes">
	<a href="?go=games&amp;mode=relevant" title="laufende und eigene Spiele"<?php if ($mode=='relevant') echo ' class="selected"'; ?>>standard</a>
	<a href="?go=games&amp;mode=own"<?php if ($mode=='own') echo ' class="selected"'; ?>>eigene</a>
	<a href="?go=games&amp;mode=all"<?php if ($mode=='all') echo ' class="selected"'; ?>>alle</a>
	<a href="?go=games&amp;mode=new"<?php if ($mode=='new') echo ' class="selected"'; ?>>neu</a>
	<a href="?go=games&amp;mode=active"<?php if ($mode=='active') echo ' class="selected"'; ?>>laufend</a>
	<a href="?go=games&amp;mode=finished"<?php if ($mode=='finished') echo ' class="selected"'; ?>>abgeschlossen</a>
	</div></div>
	<div id="pagination"></div>
<?php } ?>
<div id="games"></div>
