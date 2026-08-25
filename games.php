<?php
$mode = in_array($_GET['mode'] ?? '', ['relevant', 'own', 'all', 'new', 'active', 'finished']) ? $_GET['mode'] : 'relevant';
$sort = in_array($_GET['sort'] ?? '', ['activity', 'created', 'alpha', 'length']) ? $_GET['sort'] : 'activity';
$dir = in_array($_GET['dir'] ?? '', ['asc', 'desc']) ? $_GET['dir'] : (in_array($sort, ['activity', 'created']) ? 'desc' : 'asc');
$page = max(1, (int)($_GET['page'] ?? 1));
?>
<script src="games.js"></script>
<script>
var mode = <?php echo json_encode($mode); ?>;
var sort = <?php echo json_encode($sort); ?>;
var dir = <?php echo json_encode($dir); ?>;
var page = <?php echo json_encode($page); ?>;
document.addEventListener('DOMContentLoaded', function() {
	listgames();
	setInterval(function(){ if (!document.hidden) { listgames(); } }, 60000);
	document.addEventListener('visibilitychange', function(){ if (!document.hidden) { listgames(); } });
});
</script>
<?php if ((isset($_GET["go"]) and $_GET["go"]=="games") or !isset($_GET['go'])) { ?>
	<div id="sortgames" class="collapsible sort">
	<div class="modes">
	<a href="?go=games&amp;mode=relevant&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>" title="laufende und eigene Spiele"<?php if ($mode=='relevant') echo ' class="selected"'; ?>>standard</a>
	<a href="?go=games&amp;mode=own&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='own') echo ' class="selected"'; ?>>eigene</a>
	<a href="?go=games&amp;mode=all&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='all') echo ' class="selected"'; ?>>alle</a>
	<a href="?go=games&amp;mode=new&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='new') echo ' class="selected"'; ?>>neu</a>
	<a href="?go=games&amp;mode=active&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='active') echo ' class="selected"'; ?>>laufend</a>
	<a href="?go=games&amp;mode=finished&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='finished') echo ' class="selected"'; ?>>abgeschlossen</a>
	</div>
	<div class="modegroup">
	<div class="modes">
	<a href="?go=games&amp;mode=<?php echo $mode; ?>&amp;sort=activity" title="zuletzt aktive Spiele zuerst"<?php if ($sort=='activity') echo ' class="selected"'; ?>>letzte Aktivit&auml;t</a>
	<a href="?go=games&amp;mode=<?php echo $mode; ?>&amp;sort=created"<?php if ($sort=='created') echo ' class="selected"'; ?>>Startzeitpunkt</a>
	<a href="?go=games&amp;mode=<?php echo $mode; ?>&amp;sort=alpha"<?php if ($sort=='alpha') echo ' class="selected"'; ?>>alphabetisch</a>
	<a href="?go=games&amp;mode=<?php echo $mode; ?>&amp;sort=length"<?php if ($sort=='length') echo ' class="selected"'; ?>>nach L&auml;nge</a>
	</div>
	<div id="orderbuttons" class="modes">
	<a href="?go=games&amp;mode=<?php echo $mode; ?>&amp;sort=<?php echo $sort; ?>&amp;dir=asc" title="aufsteigend"<?php if ($dir=='asc') echo ' class="selected"'; ?>>&darr;</a>
	<a href="?go=games&amp;mode=<?php echo $mode; ?>&amp;sort=<?php echo $sort; ?>&amp;dir=desc" title="absteigend"<?php if ($dir=='desc') echo ' class="selected"'; ?>>&uarr;</a>
	</div></div>
	</div>
	<div id="pagination"></div>
<?php } ?>
<div id="games"></div>
