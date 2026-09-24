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
		<div class="modegroup">
			<div class="modes">
				<a href="spiele?mode=relevant&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>" title="<?php echo t('modestandardtitle'); ?>"<?php if ($mode=='relevant') echo ' class="selected"'; ?>><?php echo t('modestandard'); ?></a>
				<a href="spiele?mode=own&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='own') echo ' class="selected"'; ?>><?php echo t('modeown'); ?></a>
				<a href="spiele?mode=all&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='all') echo ' class="selected"'; ?>><?php echo t('modeall'); ?></a>
				<a href="spiele?mode=new&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='new') echo ' class="selected"'; ?>><?php echo t('statusnew'); ?></a>
				<a href="spiele?mode=active&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='active') echo ' class="selected"'; ?>><?php echo t('statusactive'); ?></a>
				<a href="spiele?mode=finished&amp;sort=<?php echo $sort; ?>&amp;dir=<?php echo $dir; ?>"<?php if ($mode=='finished') echo ' class="selected"'; ?>><?php echo t('statusfinished'); ?></a>
			</div>
			<div id="pagination"></div>
		</div>
		<div class="modegroup">
			<div class="modes">
				<a href="spiele?mode=<?php echo $mode; ?>&amp;sort=activity" title="<?php echo t('sortactivitytitle'); ?>"<?php if ($sort=='activity') echo ' class="selected"'; ?>><?php echo t('sortactivity'); ?></a>
				<a href="spiele?mode=<?php echo $mode; ?>&amp;sort=created"<?php if ($sort=='created') echo ' class="selected"'; ?>><?php echo t('sortcreated'); ?></a>
				<a href="spiele?mode=<?php echo $mode; ?>&amp;sort=alpha"<?php if ($sort=='alpha') echo ' class="selected"'; ?>><?php echo t('alphabetical'); ?></a>
				<a href="spiele?mode=<?php echo $mode; ?>&amp;sort=length"<?php if ($sort=='length') echo ' class="selected"'; ?>><?php echo t('sortlength'); ?></a>
			</div>
			<div id="orderbuttons" class="modes">
				<a href="spiele?mode=<?php echo $mode; ?>&amp;sort=<?php echo $sort; ?>&amp;dir=asc" title="<?php echo t('ascending'); ?>"<?php if ($dir=='asc') echo ' class="selected"'; ?>>&darr;</a>
				<a href="spiele?mode=<?php echo $mode; ?>&amp;sort=<?php echo $sort; ?>&amp;dir=desc" title="<?php echo t('descending'); ?>"<?php if ($dir=='desc') echo ' class="selected"'; ?>>&uarr;</a>
			</div>
		</div>
	</div>
<?php }
if ($dailyopen)
	echo '<a id="dailybox" class="game" href="'.dailyurl(date('Y-m-d'), $language).'"><h2>'.t('daily').' #'.daynumber(date('Y-m-d')).'</h2><br>'.t('dailybox').'</a>';
?>
<div id="games"></div>
