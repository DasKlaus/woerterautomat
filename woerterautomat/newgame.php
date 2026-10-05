<p id="newgamemessage" class="hide"></p>
<?php echo t('newgamenotice'); ?>
<form onsubmit="creategame(this); return false;"><?php echo t('enterword'); ?><br>
	<input type="text" name="word" id="newwordinput" value=""><br>
	<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
	<div id="gamesettings" class="settings">
		<label class="segment" title="<?php echo t('dictionarylanguage', t('languagede')); ?>"><input type="radio" name="language" value="de" onchange="invalidate();" checked>DE</label>
		<label class="segment" title="<?php echo t('dictionarylanguage', t('languageen')); ?>"><input type="radio" name="language" value="en" onchange="invalidate();">EN</label>
		<label title="<?php echo t('setumlauts'); ?>"><input type="checkbox" name="umlauts" value="true" onchange="toggleUmlauts(this);">&Auml;&rarr;AE</label>
		<label title="<?php echo t('setflexion'); ?>"><input type="checkbox" name="flexion" value="true" onchange="invalidate();">&#9095;</label>
		<label title="<?php echo t('setdictionary'); ?>"><input type="checkbox" name="dictionary" value="true">&#128366;</label>
		<label title="<?php echo t('setlives'); ?>"><input type="checkbox" name="lives" value="true" onchange="this.form.dictionary.disabled = this.checked; if (this.checked) { this.form.dictionary.checked = true; }">&#9825;</label>
		<label title="<?php echo t('setprivate'); ?>"><input type="checkbox" name="private" value="true">&#9919;</label>
		<label title="<?php echo t('setmaxplayers'); ?>">&#9786;<input type="number" name="players" id="playersinput" value="" min="0" max="99" placeholder=" "></label>
		<label class="segment" title="<?php echo t('settimelimit'); ?>"><input type="number" name="timelimit" id="timelimitinput" value="" min="0" max="99" placeholder=" " oninput="timeunit(this);"></label>
		<label class="segment" title="<?php echo t('timelimitin', plural('minute', 2)); ?>"><input type="radio" name="unit" value="1">&#9203;&#65038;</label>
		<label class="segment" title="<?php echo t('timelimitin', plural('hour', 2)); ?>"><input type="radio" name="unit" value="60">&#128336;&#65038;</label>
		<label class="segment" title="<?php echo t('timelimitin', plural('day', 2)); ?>"><input type="radio" name="unit" value="1440">&#128467;&#65038;</label>
		<label class="segment" title="<?php echo t('timelimitin', plural('month', 2)); ?>"><input type="radio" name="unit" value="43200">&#128197;&#65038;</label>
		<span id="difficulty" class="hide"></span>
		<button type="submit" name="new" id="newgamesubmit"><?php echo t('checkgame'); ?></button>
	</div>
</form>
<?php legend(); ?>
<script src="newgame.js"></script>
