<?php
# The interface language. $ui holds labels and messages and is handed to the client by index.php, so
# it stays plain text; $text holds prose and markup and never leaves the server. identity.php and
# legal.php carry their own texts, being shared with projects that know nothing of this file.

function browserlanguage()
{
	foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') as $entry)
	{
		$tag = strtolower(trim(strtok($entry, ';')));
		if (str_starts_with($tag, 'de')) { return 'de'; }
		if (str_starts_with($tag, 'en')) { return 'en'; }
	}
	return 'en';
}

$ui = [
'de' => [
	'guest' => 'Gast',
	'dictionary' => 'Wörterbuch',
	'profile' => 'Profil',
	'newgame' => 'Neues Spiel',
	'gamelist' => 'Spieleübersicht',
	'instructions' => 'Anleitung',
	'legal' => 'Impressum',
	'shuffle' => 'mischen',
	'anagramsort' => 'sortieren',
	'alphabetical' => 'alphabetisch',
	'original' => 'original',
	'leave' => 'Spiel verlassen',
	'finish' => 'Spiel abschließen',
	'back' => 'zurück',
	'submit' => 'absenden',
	'sortstandard' => 'standard',
	'sortchrono' => 'chronologisch',
	'sortlength' => 'nach Länge',
	'sortpoints' => 'nach Punkten',
	'sortplayer' => 'nach Spielern',
	'ascending' => 'aufsteigend',
	'descending' => 'absteigend',
	'modestandard' => 'standard',
	'modestandardtitle' => 'laufende und eigene Spiele',
	'modeown' => 'eigene',
	'modeall' => 'alle',
	'sortactivity' => 'letzte Aktivität',
	'sortactivitytitle' => 'zuletzt aktive Spiele zuerst',
	'sortcreated' => 'Startzeitpunkt',
	'statusnew' => 'neu',
	'statusactive' => 'laufend',
	'statusfull' => 'Spielerzahl erreicht',
	'statusfinished' => 'abgeschlossen',

	'justnow' => 'gerade eben',
	'ago' => 'vor %1 %2',
	'minute' => ['Minute', 'Minuten'],
	'hour' => ['Stunde', 'Stunden'],
	'day' => ['Tag', 'Tagen'],
	'month' => ['Monat', 'Monaten'],
	'year' => ['Jahr', 'Jahren'],
	'lastactive' => 'zuletzt aktiv %1',
	'activenow' => 'gerade eben aktiv',
	'lastaction' => 'letzte Aktion %1',
	'limittitle' => 'kann nach %1 %2 Inaktivität einseitig geschlossen werden',

	'languagede' => 'Deutsch',
	'languageen' => 'Englisch',
	'dictionarylanguage' => 'Wörterbuch: %1',
	'setumlauts' => 'Umlaute substituieren',
	'setflexion' => 'Flexionsformen erlauben',
	'setdictionary' => 'nur Wörterbuch-Wörter werten',
	'setprivate' => 'privates Spiel',
	'setmaxplayers' => 'Spielerzahl begrenzen',
	'maxplayerstitle' => 'bis zu %1 Spieler',
	'settimelimit' => 'Zeitlimit für Inaktivität',
	'timelimitin' => 'Zeitlimit in %1',
	'possiblewords' => '%1 mögliche Wörter',

	'blocked' => 'Zu viele nicht erlaubte Wörter probiert, warte %1 Sekunden!',
	'notfound' => 'Nicht gefunden',
	'foundby' => 'Gefunden von %1',
	'noentry' => 'kein Eintrag',
	'pronunciation' => 'Aussprache',
	'meanings' => 'Bedeutungen',
	'formof' => 'Form von',
	'forcefinish' => 'abschließen',
	'forcefinishconfirm' => '%1 war zuletzt %2 aktiv. Das Spiel für %1 abschließen?',
	'leaveconfirm' => 'Wenn du dieses Spiel verlässt, gehen alle deine gefundenen Wörter verloren. Fortfahren?',

	'checkgame' => 'Spiel prüfen',
	'startgame' => 'Spiel starten',
	'checking' => 'wird geprüft …',
	'starting' => 'wird gestartet …',
	'noanswer' => 'Der Server hat nicht geantwortet.',
	'creategamefailed' => 'Das Spiel konnte nicht erstellt werden.',
	'creategameblocked' => 'Das Erstellen von Spielen wurde gesperrt.',
	'wordtooshort' => 'Gib ein Wort mit mindestens drei Buchstaben ein.',
	'wordtoolong' => 'Das Wort ist zu lang. Mehr als 64 Buchstaben sind nicht möglich.',
	'nodictionaryscoring' => 'Für diese Sprache gibt es noch kein Wörterbuch. Ohne Wörterbuch ist die Wörterbuch-Wertung nicht möglich.',
	'nodictionary' => 'Für diese Sprache gibt es noch kein Wörterbuch. Das Spiel kann trotzdem gestartet werden.',
	'toomany' => '%1 mögliche Wörter. Die Begrenzung liegt bei 5000.',
	'ratelimitminute' => 'Du hast gerade eben ein Spiel gestartet. Warte eine Minute, bevor du das nächste startest.',
	'ratelimithour' => 'Du hast in der letzten Stunde zehn Spiele gestartet. Versuch es später noch einmal.',

	'metadescription' => 'Bilde aus den Buchstaben eines Wortes möglichst viele andere Wörter.',
	'descquestion' => 'Wie viele Wörter findest du in %1?',
	'descfull' => 'Voll.',
	'descfinished' => 'Abgeschlossen.',

	'daily' => 'Spiel des Tages',
	'dailystatus' => '%1 Buchstaben · Zielwort: %2 Buchstaben',
	'resultletters' => '%1 / %2 Buchstaben',
	'resultwords' => '%1 / %2 Wörter',
	'target' => 'Zielwort',
	'streak' => '🔥 %1 Tage in Folge',
	'copy' => 'Kopieren',
],
'en' => [
	'guest' => 'Guest',
	'dictionary' => 'Dictionary',
	'profile' => 'Profile',
	'newgame' => 'New game',
	'gamelist' => 'Game list',
	'instructions' => 'Instructions',
	'legal' => 'Legal notice',
	'shuffle' => 'shuffle',
	'anagramsort' => 'sort',
	'alphabetical' => 'alphabetical',
	'original' => 'original',
	'leave' => 'Leave game',
	'finish' => 'Finish game',
	'back' => 'back',
	'submit' => 'submit',
	'sortstandard' => 'standard',
	'sortchrono' => 'chronological',
	'sortlength' => 'by length',
	'sortpoints' => 'by points',
	'sortplayer' => 'by player',
	'ascending' => 'ascending',
	'descending' => 'descending',
	'modestandard' => 'standard',
	'modestandardtitle' => 'ongoing and own games',
	'modeown' => 'own',
	'modeall' => 'all',
	'sortactivity' => 'last activity',
	'sortactivitytitle' => 'most recently active first',
	'sortcreated' => 'start time',
	'statusnew' => 'new',
	'statusactive' => 'ongoing',
	'statusfull' => 'player limit reached',
	'statusfinished' => 'finished',

	'justnow' => 'just now',
	'ago' => '%1 %2 ago',
	'minute' => ['minute', 'minutes'],
	'hour' => ['hour', 'hours'],
	'day' => ['day', 'days'],
	'month' => ['month', 'months'],
	'year' => ['year', 'years'],
	'lastactive' => 'last active %1',
	'activenow' => 'active just now',
	'lastaction' => 'last action %1',
	'limittitle' => 'can be closed unilaterally after %1 %2 of inactivity',

	'languagede' => 'German',
	'languageen' => 'English',
	'dictionarylanguage' => 'Dictionary: %1',
	'setumlauts' => 'substitute umlauts',
	'setflexion' => 'allow inflected forms',
	'setdictionary' => 'score dictionary words only',
	'setprivate' => 'private game',
	'setmaxplayers' => 'limit the number of players',
	'maxplayerstitle' => 'up to %1 players',
	'settimelimit' => 'inactivity time limit',
	'timelimitin' => 'time limit in %1',
	'possiblewords' => '%1 possible words',

	'blocked' => 'Too many words the dictionary rejected, wait %1 seconds!',
	'notfound' => 'Not found',
	'foundby' => 'Found by %1',
	'noentry' => 'no entry',
	'pronunciation' => 'Pronunciation',
	'meanings' => 'Meanings',
	'formof' => 'Form of',
	'forcefinish' => 'finish',
	'forcefinishconfirm' => '%1 was last active %2. Finish the game for %1?',
	'leaveconfirm' => 'If you leave this game, every word you found is lost. Continue?',

	'checkgame' => 'Check game',
	'startgame' => 'Start game',
	'checking' => 'checking …',
	'starting' => 'starting …',
	'noanswer' => 'The server did not answer.',
	'creategamefailed' => 'The game could not be created.',
	'creategameblocked' => 'Creating games has been blocked.',
	'wordtooshort' => 'Enter a word of at least three letters.',
	'wordtoolong' => 'The word is too long. More than 64 letters are not possible.',
	'nodictionaryscoring' => 'There is no dictionary for this language yet. Without one, dictionary scoring is not possible.',
	'nodictionary' => 'There is no dictionary for this language yet. The game can be started anyway.',
	'toomany' => '%1 possible words. The limit is 5000.',
	'ratelimitminute' => 'You started a game a moment ago. Wait a minute before starting the next one.',
	'ratelimithour' => 'You have started ten games in the last hour. Try again later.',

	'metadescription' => 'Find as many words as you can in the letters of a single word.',
	'descquestion' => 'How many words can you find in %1?',
	'descfull' => 'Full.',
	'descfinished' => 'Finished.',

	'daily' => 'Game of the day',
	'dailystatus' => '%1 letters · target word: %2 letters',
	'resultletters' => '%1 / %2 letters',
	'resultwords' => '%1 / %2 words',
	'target' => 'Target word',
	'streak' => '🔥 %1 days in a row',
	'copy' => 'Copy',
],
];

$text = [
'de' => [
	'gamegone' => 'Dieses Spiel gibt es nicht mehr.',
	'gamefull' => 'Dieses Spiel nimmt keine weiteren Spieler an.',
	'gamestarted' => 'gestartet %1 auf %2 von %3 %4<br>mit %5',
	'withflexion' => 'mit Flexionsformen',
	'withoutflexion' => 'ohne Flexionsformen',
	'gameintro' => '<p>Bilde aus den Buchstaben des Wortes m&ouml;glichst viele andere W&ouml;rter. Jedes Wort bringt so viele Punkte, wie es Buchstaben hat, multipliziert mit der Anzahl der Mitspieler, die es nicht gefunden haben.</p>',
	'guestjoin' => '<p>Vergib einen Namen, um mitzuspielen, oder lass das Feld frei, um anonym zu spielen. Wenn du einen Authentifizierungs-Code hast, kannst du dich mit diesem anmelden.</p>',
	'guestcreate' => '<p>Vergib einen Namen, um ein Spiel zu erstellen, oder lass das Feld frei, um anonym zu spielen. Wenn du einen Authentifizierungs-Code hast, kannst du dich mit diesem anmelden.</p>',
	'dictionarylicense' => '<p>Das W&ouml;rterbuch basiert auf Daten aus <a href="https://www.wiktionary.org">Wiktionary</a>, extrahiert von <a href="https://kaikki.org/dictionary/">kaikki.org</a>, und steht unter der Lizenz <a href="https://creativecommons.org/licenses/by-sa/4.0/deed.de">CC BY-SA 4.0</a>.</p>',

	'instructionstext' => '<p>Bilde aus den Buchstaben des Ausgangswortes m&ouml;glichst viele andere W&ouml;rter.
		Jedes Wort bringt so viele Punkte, wie es Buchstaben hat, multipliziert mit der Anzahl der Mitspieler, die es nicht gefunden haben.</p>
		<p>Die Auswertung wird nach Abschlie&szlig;en angezeigt. Es ist erst dann wirklich beendet, wenn alle Spieler abgeschlossen haben.</p>
		<p>Du kannst Mitspieler einladen, indem du die Spiel-URL mit ihnen teilst.</p>',
	'settingstitle' => 'Spieleinstellungen',
	'reactionstitle' => 'Reaktionen',
	'reactionstext' => '<p>Nach Spielabschluss können Spieler die Wörter anderer Spieler und mögliche Lösungen aus dem Wörterbuch sehen. Auf diese können sie mit Emoji reagieren: 💪👍🤦😭🤯😂✨❓🚫</p>
		<p>Einige Reaktionen werden automatisch vom Wörterbuch hinzugefügt:</p>
		<dl class="legend">
			<dt>❗</dt><dd>Wort nicht gefunden.</dd>
			<dt>❕</dt><dd>Wort als selten, regional oder dergleichen gekennzeichnet.</dd>
			<dt>Ä→AE</dt><dd>Wort wäre erlaubt, wenn Umlautsubstitution aktiviert wäre.</dd>
			<dt>⎇</dt><dd>Wort wäre erlaubt, wenn Flexionsformen erlaubt wären.</dd>
		</dl>
		<p>Das Wörterbuch ist ein Import von wiktionary.org und kann stellenweise unvollständig, falsch oder inkorrekt (nicht) als Flexionsform/selten gekennzeichnet sein. Daher verschwinden Wörterbuch-Reaktionen, wenn ein weiterer Spieler das Wort gefunden hat oder mit 👍 reagiert.</p>',
	'othertitle' => 'Sonstige',
	'othertext' => '<dl class="legend">
			<dt>🔍</dt><dd>Wort nachschlagen (nur nach Spielabschluss möglich)</dd>
			<dt>❌</dt><dd>Wort löschen</dd>
			<dt>&#9200;&#65038;</dt><dd>Spieler war über das Zeitlimit hinaus nicht aktiv; das Spiel kann durch andere für ihn beendet werden.</dd>
		</dl>',

	'legendlanguage' => 'Sprache des W&ouml;rterbuchs',
	'legendumlauts' => 'Umlaute substituieren: SS statt &szlig;, AE statt &Auml; etc.',
	'legendflexion' => 'Flexionsformen erlauben: gebeugte und abgeleitete Formen von Wörtern wie Mehrzahlen, Deklinationen, Konjugationen<br>
		zum Beispiel Häuser, gelaufen, fragte, dessen, mir, Notarin, Fuchses',
	'legenddictionary' => 'W&ouml;rter, die das W&ouml;rterbuch nicht kennt, bringen keine Punkte<br>
		Fund durch einen zweiten Spieler oder ein 👍 nehmen ein Wort in die Wertung auf.',
	'legendprivate' => 'privates Spiel: unsichtbar f&uuml;r andere (zum Einladen URL teilen)',
	'legendmaxplayers' => 'Spielerzahl begrenzen; ohne Angabe unbegrenzt',
	'legendtimelimit' => 'Zeitlimit f&uuml;r Inaktivit&auml;t, ab der das Spiel durch Mitspieler beendet werden kann<br>
		&#9203;&#65038; Minuten &nbsp;&nbsp; &#128336;&#65038; Stunden &nbsp;&nbsp; &#128467;&#65038; Tage &nbsp;&nbsp; &#128197;&#65038; Monate, &nbsp;&nbsp; ohne Angabe unbegrenzt',
	'legenddifficulty' => 'Anzahl der m&ouml;glichen W&ouml;rter (wird automatisch ermittelt)<br>
		&middot; <50, &nbsp;&nbsp; &#8270; 50-199, &nbsp;&nbsp; &#8273; 200-499, &nbsp;&nbsp; &#8258; 500-999, &nbsp;&nbsp; &#10035;>=1000',
	'legendstatus' => 'Spielstatus<br>
		○ neu (noch kein Beitritt) &nbsp;&nbsp; ◐ laufend (mehrere Spieler beigetreten) &nbsp;&nbsp; ● voll (keine weiteren Beitritte möglich) &nbsp;&nbsp; ✓ abgeschlossen',

	'newgamenotice' => '<p>Das Wort ist in nicht-privaten Spielen für alle sichtbar. Beleidigendes, Privates oder Anstößiges ist nicht zulässig,
		Verstöße können über das Impressum gemeldet werden.
		Unzulässige Spiele werden ohne Ankündigung gelöscht.</p>
		<p>Wörter länger als 16 Buchstaben werden womöglich nicht korrekt angezeigt.</p>',
	'enterword' => 'Gib ein Wort ein, mit dem du ein Spiel starten willst.',

	'nodaily' => 'Für diesen Tag gibt es kein Spiel des Tages.',
	'dailybox' => 'Ein Wort für alle, jeden Tag ein neues.',
	'dailyintro' => '<p>Ein Wort für alle, jeden Tag ein neues: Bilde aus seinen Buchstaben möglichst viele andere Wörter.
		Jedes Wort aus dem Wörterbuch bringt so viele Punkte, wie es Buchstaben hat, seltene Wörter als Bonus. Das Zielwort ist das längste mögliche Wort.</p>
		<p>Um Mitternacht endet das Spiel. Nach dem Abschließen zeigt es die Lösung und ein Ergebnis zum Teilen.</p>',
],
'en' => [
	'gamegone' => 'This game no longer exists.',
	'gamefull' => 'This game does not take any more players.',
	'gamestarted' => 'started %1 in %2 by %3 %4<br>with %5',
	'withflexion' => 'with inflected forms',
	'withoutflexion' => 'without inflected forms',
	'gameintro' => '<p>Find as many words as you can in the letters of this word. Each word scores as many points as it has letters, multiplied by the number of fellow players who did not find it.</p>',
	'guestjoin' => '<p>Pick a name to join in, or leave the field empty to play anonymously. If you have an authentication code, you can sign in with it.</p>',
	'guestcreate' => '<p>Pick a name to create a game, or leave the field empty to play anonymously. If you have an authentication code, you can sign in with it.</p>',
	'dictionarylicense' => '<p>The dictionary is based on data from <a href="https://www.wiktionary.org">Wiktionary</a>, extracted by <a href="https://kaikki.org/dictionary/">kaikki.org</a>, and is licensed under <a href="https://creativecommons.org/licenses/by-sa/4.0/">CC BY-SA 4.0</a>.</p>',

	'instructionstext' => '<p>Find as many words as you can in the letters of the source word.
		Each word scores as many points as it has letters, multiplied by the number of fellow players who did not find it.</p>
		<p>The result is shown once you finish. The game is only really over when every player has finished.</p>
		<p>You can invite others by sharing the game&rsquo;s URL with them.</p>',
	'settingstitle' => 'Game settings',
	'reactionstitle' => 'Reactions',
	'reactionstext' => '<p>Once the game is finished, players can see the other players&rsquo; words and the possible solutions from the dictionary. They can react to those with emoji: 💪👍🤦😭🤯😂✨❓🚫</p>
		<p>Some reactions are added by the dictionary itself:</p>
		<dl class="legend">
			<dt>❗</dt><dd>Word not found.</dd>
			<dt>❕</dt><dd>Word marked as rare, regional or the like.</dd>
			<dt>Ä→AE</dt><dd>Word would be allowed with umlaut substitution switched on.</dd>
			<dt>⎇</dt><dd>Word would be allowed with inflected forms allowed.</dd>
		</dl>
		<p>The dictionary is an import from wiktionary.org and can be incomplete, wrong, or incorrectly marked (or not marked) as an inflected or rare form. Dictionary reactions therefore disappear once another player has found the word or reacted with 👍.</p>',
	'othertitle' => 'Other',
	'othertext' => '<dl class="legend">
			<dt>🔍</dt><dd>look the word up (only once the game is finished)</dd>
			<dt>❌</dt><dd>delete the word</dd>
			<dt>&#9200;&#65038;</dt><dd>player has been inactive beyond the time limit; others can finish the game on their behalf.</dd>
		</dl>',

	'legendlanguage' => 'dictionary language',
	'legendumlauts' => 'substitute umlauts: SS for &szlig;, AE for &Auml; etc.',
	'legendflexion' => 'allow inflected forms: declined and derived forms of words such as plurals, cases, conjugations<br>
		for example houses, walked, asked, whose, running, teacher&rsquo;s',
	'legenddictionary' => 'words the dictionary does not know score no points<br>
		A second player finding the word, or a 👍, takes it into the scoring.',
	'legendprivate' => 'private game: invisible to others (share the URL to invite)',
	'legendmaxplayers' => 'limit the number of players; unlimited if left empty',
	'legendtimelimit' => 'inactivity time limit, after which fellow players can finish the game<br>
		&#9203;&#65038; minutes &nbsp;&nbsp; &#128336;&#65038; hours &nbsp;&nbsp; &#128467;&#65038; days &nbsp;&nbsp; &#128197;&#65038; months, &nbsp;&nbsp; unlimited if left empty',
	'legenddifficulty' => 'number of possible words (determined automatically)<br>
		&middot; <50, &nbsp;&nbsp; &#8270; 50-199, &nbsp;&nbsp; &#8273; 200-499, &nbsp;&nbsp; &#8258; 500-999, &nbsp;&nbsp; &#10035;>=1000',
	'legendstatus' => 'game status<br>
		○ new (nobody has joined) &nbsp;&nbsp; ◐ ongoing (several players have joined) &nbsp;&nbsp; ● full (no further players can join) &nbsp;&nbsp; ✓ finished',

	'newgamenotice' => '<p>In games that are not private, the word is visible to everyone. Insulting, private or offensive words are not allowed;
		violations can be reported through the legal notice.
		Games that are not allowed are deleted without notice.</p>
		<p>Words longer than 16 letters may not be displayed correctly.</p>',
	'enterword' => 'Enter a word to start a game with.',

	'nodaily' => 'There is no game of the day for this day.',
	'dailybox' => 'One word for everyone, a new one every day.',
	'dailyintro' => '<p>One word for everyone, a new one every day: find as many words as you can in its letters.
		Each word from the dictionary scores as many points as it has letters, rare words as a bonus. The target word is the longest possible word.</p>
		<p>The game ends at midnight. Once you finish, it shows the solution and a result to share.</p>',
],
];

$language = in_array($_SESSION['language'] ?? '', ['de', 'en'], true) ? $_SESSION['language'] : browserlanguage();
$strings = $ui[$language] + $text[$language];

# One pass, so a value carrying a placeholder of its own is not read back as one
function fmt($string, $args)
{
	$pairs = [];
	foreach ($args as $i => $arg) { $pairs['%'.($i + 1)] = $arg; }
	return strtr($string, $pairs);
}

function t($key, ...$args)
{
	global $strings;
	return fmt($strings[$key], $args);
}

function plural($key, $count)
{
	global $strings;
	return $strings[$key][$count == 1 ? 0 : 1];
}

# The settings legend, shown whole beside the creation form and in the instructions, where the status
# symbols a game later carries belong with it.
function legend($status = false)
{
	echo '<dl class="legend">
		<dt>DE&nbsp;&nbsp;EN</dt><dd>'.t('legendlanguage').'</dd>
		<dt>&Auml;&rarr;AE</dt><dd>'.t('legendumlauts').'</dd>
		<dt>&#9095;</dt><dd>'.t('legendflexion').'</dd>
		<dt>&#128366;</dt><dd>'.t('legenddictionary').'</dd>
		<dt>&#9919;</dt><dd>'.t('legendprivate').'</dd>
		<dt>&#9786;</dt><dd>'.t('legendmaxplayers').'</dd>
		<dt>&#9203;&#65038; &#128336;&#65038; &#128467;&#65038; &#128197;&#65038;</dt><dd>'.t('legendtimelimit').'</dd>
		<dt>&middot; &#8270; &#8273; &#8258; &#10035;</dt><dd>'.t('legenddifficulty').'</dd>';
	if ($status) { echo '<dt>○◐●✓</dt><dd>'.t('legendstatus').'</dd>'; }
	echo '</dl>';
}
