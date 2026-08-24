<?php
if ($message) { echo '<p class="warning">'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</p>'; }
?>
<p>Das Wort ist für alle sichtbar. Beleidigendes, Privates oder Anstößiges ist nicht zulässig, Verstöße können über das Impressum gemeldet werden.
   Unzulässige Spiele werden ohne Ankündigung gelöscht.</p>
<p>Wörter länger als 16 Buchstaben werden womöglich nicht korrekt angezeigt.</p>
<form method="post">Gib ein Wort ein, mit dem du ein Spiel starten willst.<br>
	<input type="text" name="word" id="newwordinput" value="<?php echo htmlspecialchars($_POST['word'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
	<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
	<div id="controlgrid">
		<div class="controls"><label for="languageinput">Sprache:</label><select name="language" id="languageinput">
			<option value="de" <?php echo ((($_POST['language'] ?? 'de') == 'de') ? ' selected' : '') ?>>Deutsch</option>
			<option value="en" <?php echo ((($_POST['language'] ?? 'de') == 'en') ? ' selected' : '') ?>>Englisch</option>
		</select></div>
		<div class="controls"><label title="SS statt ß, AE statt Ä etc." for="umlautcheckbox">Umlaute substituieren:</label><input type="checkbox" onchange="toggleUmlauts(this);" name="umlauts" value="true" id="umlautcheckbox"<?php echo (isset($_POST['umlauts']) ? ' checked' : '')?>></div>
		<div class="controls"><label title="gebeugte und abgeleitete Formen von Wörtern wie Mehrzahlen, Deklinationen, Konjugationen, zum Beispiel Häuser, gelaufen, fragte, dessen, mir, Notarin, Fuchses" for="flexioncheckbox">Flexionsformen erlaubt:</label> <input type="checkbox" name="flexion" value="true" id="flexioncheckbox"<?php echo (isset($_POST['flexion']) ? ' checked' : '') ?>></div>
		<div class="controls"><label for="privatecheckbox">Privates Spiel:</label><input type="checkbox" name="private" value="true" id="privatecheckbox" <?php echo (isset($_POST['private']) ? ' checked' : '') ?>></div>
		<div class="controls"><label for="playersinput">Spieler begrenzen:</label><input type="number" name="players" id="playersinput" value="<?php echo $_POST['word'] ?>"></div>
		<div class="controls"><button type="submit" name="new" id="newgamesubmit">Spiel starten</button></div>
	</div>
</form>
<script src="newgame.js"></script>