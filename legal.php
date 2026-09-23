<?php
$legaltexts = [
'de' => '<h2>Impressum</h2>
	<p>Verantwortlich f&uuml;r diese Webseite, ihre Inhalte und die Datenverarbeitung:</p>
	<p>Wollmilchmedien<br>
		CHANGEME Vorname Nachname<br>
		CHANGEME Stra&szlig;e Hausnummer<br>
		CHANGEME PLZ Ort<br>
		E-Mail: CHANGEME</p>
	<p>F&uuml;r die Nutzung der Webseite werden ein frei gew&auml;hlter Anzeigename, eingegebene Inhalte, Zeitstempel von Aktivit&auml;ten sowie ein zuf&auml;llig erzeugter pers&ouml;nlicher Code gespeichert. Der pers&ouml;nliche Code dient der Wiedererkennung und kann zur erneuten Zuordnung der Daten verwendet werden, ohne ihn ist eine Zuordnung nicht m&ouml;glich. Anzeigename und eingegebene Inhalte k&ouml;nnen anderen Nutzern angezeigt werden; sie sollten daher keine pers&ouml;nlichen Angaben enthalten. Die Verarbeitung erfolgt zum Betrieb und zur Bereitstellung der Webseite auf Grundlage eines berechtigten Interesses.</p>
	<p>F&uuml;r die Nutzung werden ein technisch notwendiges Sitzungs-Cookie sowie optional nach ausdr&uuml;cklicher, jederzeit widerruflicher Einwilligung ein Cookie zur Speicherung des pers&ouml;nlichen Codes verwendet. Das Sitzungs-Cookie verf&auml;llt mit dem Ende der Sitzung, das Cookie mit dem pers&ouml;nlichen Code nach einem Jahr Inaktivit&auml;t und kann &uuml;ber dieselbe Funktion jederzeit gel&ouml;scht werden.</p>
	<p>Die Webseite und die dabei verarbeiteten Daten werden bei ALL-INKL.COM &ndash; Neue Medien M&uuml;nnich gehostet. ALL-INKL.COM verarbeitet die Daten als Auftragsverarbeiter. IP-Adressen werden zur Auslieferung der Webseite verarbeitet und nicht gespeichert. Eine &Uuml;bermittlung der Daten an Dritte findet nicht statt.</p>
	<p>Die Daten werden gespeichert, solange sie f&uuml;r den Betrieb der Webseite ben&ouml;tigt werden. Betroffenenrechte wie Auskunft, Berichtigung, L&ouml;schung, Einschr&auml;nkung der Verarbeitung, Daten&uuml;bertragbarkeit und Widerspruch k&ouml;nnen &uuml;ber die oben genannte E-Mail-Adresse geltend gemacht werden. Bei einer L&ouml;schung wird die Zuordnung eines Beitrags zum Anzeigenamen entfernt; eingegebene Inhalte k&ouml;nnen als Teil eines gemeinsamen Verlaufs mit anderen Nutzern erhalten bleiben.</p>
	<p>Hinweise auf rechtswidrige, beleidigende, private oder sonstwie problematische Inhalte k&ouml;nnen an die oben genannte E-Mail-Adresse gerichtet werden. Beschwerden &uuml;ber die Verarbeitung personenbezogener Daten k&ouml;nnen bei der zust&auml;ndigen Datenschutzaufsichtsbeh&ouml;rde eingereicht werden (Landesbeauftragter f&uuml;r den Datenschutz Sachsen-Anhalt).</p>',
'en' => '<h2>Legal notice</h2>
	<p>Responsible for this website, its contents and the processing of data:</p>
	<p>Wollmilchmedien<br>
		CHANGEME Vorname Nachname<br>
		CHANGEME Stra&szlig;e Hausnummer<br>
		CHANGEME PLZ Ort<br>
		E-mail: CHANGEME</p>
	<p>The site stores a freely chosen display name, the entries made, timestamps of activity and a randomly generated personal code. The personal code is what makes recognition possible: with it the data can be found again, without it there is nothing to connect the data to. Display name and entries may be shown to other users and should therefore contain nothing personal. Everything stored serves one purpose, running the site, and that is the legitimate interest behind the processing.</p>
	<p>One cookie is technically necessary and holds the session; it expires when the session ends. A second cookie stores the personal code and is only set on explicit request; that consent can be withdrawn and the cookie deleted at any time through the same function. Otherwise it expires after a year without activity.</p>
	<p>Site and data are hosted at ALL-INKL.COM &ndash; Neue Medien M&uuml;nnich, acting strictly on instruction and for no purposes of its own. IP addresses are used to deliver the pages and are not stored. No data is passed on to third parties.</p>
	<p>Data is kept as long as running the site requires. The e-mail address above is the place to ask what is stored, to have it corrected, deleted or handed over, to restrict its processing or to object to it altogether. Deletion removes the link between a contribution and the display name; entries themselves may remain where they are part of a history shared with other users.</p>
	<p>Content that is unlawful, insulting, private or otherwise problematic can be reported to the same address. Complaints about the handling of personal data can be made to the supervisory authority, the Landesbeauftragter f&uuml;r den Datenschutz Sachsen-Anhalt.</p>',
];

function legalNotice($language)
{
	global $legaltexts;
	echo $legaltexts[$language];
}
