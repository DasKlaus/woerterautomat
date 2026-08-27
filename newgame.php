<p id="newgamemessage" class="hide"></p>
<p>Das Wort ist in nicht-privaten Spielen für alle sichtbar. Beleidigendes, Privates oder Anstößiges ist nicht zulässig,
	Verstöße können über das Impressum gemeldet werden.
	Unzulässige Spiele werden ohne Ankündigung gelöscht.</p>
<p>Wörter länger als 16 Buchstaben werden womöglich nicht korrekt angezeigt.</p>
<form onsubmit="creategame(this); return false;">Gib ein Wort ein, mit dem du ein Spiel starten willst.<br>
	<input type="text" name="word" id="newwordinput" value=""><br>
	<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
	<div id="controlgrid">
		<div class="controls"><label for="languageinput">Sprache:</label><select name="language" onchange="invalidate();" id="languageinput">
			<option value="de">Deutsch</option>
			<option value="en">Englisch</option>
		</select></div>
		<div class="controls"><label title="SS statt ß, AE statt Ä etc." for="umlautcheckbox">Umlaute substituieren:</label><input type="checkbox" onchange="toggleUmlauts(this);" name="umlauts" value="true" id="umlautcheckbox"></div>
		<div class="controls"><label title="gebeugte und abgeleitete Formen von Wörtern wie Mehrzahlen, Deklinationen, Konjugationen, zum Beispiel Häuser, gelaufen, fragte, dessen, mir, Notarin, Fuchses" for="flexioncheckbox">Flexionsformen erlaubt:</label> <input type="checkbox" onchange="invalidate();" name="flexion" value="true" id="flexioncheckbox"></div>
		<div class="controls"><label for="privatecheckbox">Privates Spiel:</label><input type="checkbox" name="private" value="true" id="privatecheckbox"></div>
		<div class="controls"><label for="playersinput">Spieler begrenzen:</label><input type="number" name="players" id="playersinput" value=""></div>
		<div class="controls"><button type="submit" name="new" id="newgamesubmit">Spiel pr&uuml;fen</button></div>
	</div>
</form>
<script src="newgame.js"></script>
