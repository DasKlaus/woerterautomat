<p id="newgamemessage" class="hide"></p>
<p>Das Wort ist in nicht-privaten Spielen für alle sichtbar. Beleidigendes, Privates oder Anstößiges ist nicht zulässig,
	Verstöße können über das Impressum gemeldet werden.
	Unzulässige Spiele werden ohne Ankündigung gelöscht.</p>
<p>Wörter länger als 16 Buchstaben werden womöglich nicht korrekt angezeigt.</p>
<form onsubmit="creategame(this); return false;">Gib ein Wort ein, mit dem du ein Spiel starten willst.<br>
	<input type="text" name="word" id="newwordinput" value=""><br>
	<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
	<div id="gamesettings" class="settings">
		<label class="segment" title="deutsches W&ouml;rterbuch"><input type="radio" name="language" value="de" onchange="invalidate();" checked>DE</label>
		<label class="segment" title="englisches W&ouml;rterbuch"><input type="radio" name="language" value="en" onchange="invalidate();">EN</label>
		<label title="Umlaute substituieren"><input type="checkbox" name="umlauts" value="true" onchange="toggleUmlauts(this);">&Auml;&rarr;AE</label>
		<label title="Flexionsformen erlauben"><input type="checkbox" name="flexion" value="true" onchange="invalidate();">&#9095;</label>
		<label title="privates Spiel"><input type="checkbox" name="private" value="true">&#9919;</label>
		<label title="Spielerzahl begrenzen">&#9786;<input type="number" name="players" id="playersinput" value="" min="0" max="99" placeholder=" "></label>
		<label class="segment" title="Zeitlimit f&uuml;r Inaktivit&auml;t"><input type="number" name="timelimit" id="timelimitinput" value="" min="0" max="99" placeholder=" " oninput="timeunit(this);"></label>
		<label class="segment" title="Minuten"><input type="radio" name="unit" value="1">&#9203;&#65038;</label>
		<label class="segment" title="Stunden"><input type="radio" name="unit" value="60">&#128336;&#65038;</label>
		<label class="segment" title="Tage"><input type="radio" name="unit" value="1440">&#128467;&#65038;</label>
		<label class="segment" title="Monate"><input type="radio" name="unit" value="43200">&#128197;&#65038;</label>
		<span id="difficulty" class="hide"></span>
		<button type="submit" name="new" id="newgamesubmit">Spiel pr&uuml;fen</button>
	</div>
</form>
<dl class="legend">
	<dt>DE&nbsp;&nbsp;EN</dt><dd>Sprache des W&ouml;rterbuchs</dd>
	<dt>&Auml;&rarr;AE</dt><dd>Umlaute substituieren: SS statt &szlig;, AE statt &Auml; etc.</dd>
	<dt>&#9095;</dt><dd>Flexionsformen erlauben: gebeugte und abgeleitete Formen von Wörtern wie Mehrzahlen, Deklinationen, Konjugationen, zum Beispiel Häuser, gelaufen, fragte, dessen, mir, Notarin, Fuchses</dd>
	<dt>&#9919;</dt><dd>privates Spiel: unsichtbar f&uuml;r andere (zum Einladen URL teilen)</dd>
	<dt>&#9786;</dt><dd>Spielerzahl begrenzen, ohne Angabe unbegrenzt</dd>
	<dt>&#9203;&#65038; &#128336;&#65038; &#128467;&#65038; &#128197;&#65038;</dt><dd>Zeitlimit f&uuml;r Inaktivit&auml;t in Minuten, Stunden, Tagen oder Monaten, ohne Angabe unbegrenzt: wer l&auml;nger nicht gespielt hat, kann von den anderen abgeschlossen werden</dd>
	<dt>&middot; &#8270; &#8273; &#8258; &#10035;</dt><dd>Anzahl der m&ouml;glichen W&ouml;rter (wird beim Pr&uuml;fen ermittelt)</dd>
	<dt></dt><dd>&middot; <50, &nbsp;&nbsp;&#8270; 50-199, &nbsp;&nbsp;&#8273; 200-499, &nbsp;&nbsp;&#8258; 500-999, &nbsp;&nbsp;&#10035;>=1000</dd>
</dl>
<script src="newgame.js"></script>