<!DOCTYPE html>
<?php
session_start();
$guest = false;
if (isset($_POST['name'])) {$_SESSION['player'] = $_POST['name'];}
if ($_SESSION['player']=="" or !isset($_SESSION['player'])) {$_SESSION['player'] = "Gast".session_id();}
if (substr($_SESSION['player'], 0, 4) == "Gast") { $guest = true; }
$writeplayer = ($guest)?"Gast":umlaute($_SESSION['player']);
$host = "localhost";
$dbname = "DasKlaussql12";
$dbuser = "DasKlaussql12";
$dbpass = "hNzljODYwM";

mysql_connect($host, $dbuser, $dbpass) or die ('Verbindung mit Datenbank konnte nicht hergestellt werden: '.mysql_error());
mysql_select_db($dbname) or die ('Datenbank konnte nicht ausgew�hlt werden: '.mysql_error());

function umlaute($string){
  $upas = Array("�" => "ae", "�" => "ue", "�" => "oe", "�" => "Ae", "�" => "Ue", "�" => "Oe", "�" => "ss"); 
  return strtr($string, $upas);
  }
?>
<html>
	<head>
		<title>W�rterautomat</title>
		<meta name="robots" content="index,follow">
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
		<link href="style.css" type="text/css" rel="stylesheet" media="screen">
	</head>
	<body>
	<script src="jquery.js"></script>
	<div id="wrapper">
		<a id="headline" href="http://antiscrabble.de/woerterautomat"><h1>woerterautomat</h1></a>
		<div id="menu">
			<h2 style="text-transform: lowercase;"><?php echo $writeplayer; ?></h2>
			<?php if (isset($_GET['go']) and $_GET['go']!="game") { ?><form method="post"><input style="width: 150px; margin-bottom: 10px;" type="text" name="name" value="<?php echo $writeplayer; ?>"><br>
			<input  style="width: 170px; margin-bottom: 10px;" type="submit" value="Name �ndern"></form><?php } ?>
			<a href="?go=neu">Neues Spiel</a>
			<a href="?go=games">Spiele�bersicht</a>
			<a href="?go=anleitung">Anleitung</a>
			<a href="?go=impressum">Impressum</a>
			<?php if ((isset($_GET["go"]) and $_GET["go"]=="games") or !isset($_GET['go'])) { ?>
				<a id="all" onClick="filter('all');">alle Spiele</a>
				<a id="own" onClick="filter('own');">deine Spiele</a>
				<a id="new" onClick="filter('new');">neue Spiele</a>
				<a id="active" onClick="filter('active');">laufende Spiele</a>
				<a id="finished" onClick="filter('finished');">abgeschlossene Spiele</a>
			<?php } ?>
			<?php if (isset($_GET["go"]) and $_GET["go"]=="game") { ?>
				<a id="leave" onClick="leave();">Spiel verlassen</a>
				<a id="finish" onClick="finish();">Spiel abschlie�en</a>
				<div id="players"></div>
			<?php } ?>
		</div>
		<div id="content">
			<?php
			if (isset($_GET["go"]) and $_GET["go"]=="impressum")
			{
			echo '<h2>impressum</h2>
				<b>Angaben gem�� � 5 TMG:</b><br>
				Wollmilchmedien<br>
				Claudia R�ssel<br>
				Elisabethstra�e 6<br>
				18057 Rostock<br>
				E-Mail: info@antiscrabble.de<br><br>

				<h2>haftungsausschluss</h2>
				<b>Haftung f�r Inhalte</b><br>
				<p>Die Inhalte unserer Seiten wurden mit gr��ter Sorgfalt erstellt. F�r die Richtigkeit, Vollst�ndigkeit und Aktualit�t der Inhalte k�nnen wir jedoch keine Gew�hr �bernehmen. Als Diensteanbieter sind wir gem�� � 7 Abs.1 TMG f�r eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach �� 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, �bermittelte oder gespeicherte fremde Informationen zu �berwachen oder nach Umst�nden zu forschen, die auf eine rechtswidrige T�tigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unber�hrt. Eine diesbez�gliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung m�glich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.</p>
				<br><b>Haftung f�r Links</b><br>
				<p>Unser Angebot enth�lt Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb k�nnen wir f�r diese fremden Inhalte auch keine Gew�hr �bernehmen. F�r die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf m�gliche Rechtsverst��e �berpr�ft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.</p>
				<br><b>Urheberrecht</b><br>
				<p>Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielf�ltigung, Bearbeitung, Verbreitung und jede Art der Verwertung au�erhalb der Grenzen des Urheberrechtes bed�rfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur f�r den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>
				<h2>datenschutz</h2>
				<b>Erhebung, Verarbeitung und Nutzung Deiner Daten</b><br>
				<p>Du kannst diese Seite besuchen, ohne Angaben zu Deiner Person zu machen. Personenbezogene Daten werden nur erhoben, wenn Du uns diese freiwillig mitteilst. Wir verwenden die von Dir mitgeteilten Daten ohne Deine gesonderte Einwilligung ausschlie�lich zum bei der Erhebung angegebenen Zweck. </p>
				<b>Weitergabe personenbezogener Daten</b><br>
				<p>Eine Weitergabe deiner Daten an Dritte erfolgt nicht.</p>
				<b>Auskunftsrecht</b><br>
				<p>Nach dem Bundesdatenschutzgesetz hast Du ein Recht auf unentgeltliche Auskunft �ber Deine gespeicherten Daten. Au�er deiner Emailadresse werden keine weiteren personenbezogenen Daten erhoben. Du kannst jederzeit formlos anfragen, ob wir deine Emailadresse gespeichert haben, indem du uns eine Email an info@antiscrabble.de schickst.</p>
				<h2>sonstiges</h2>
				<p>Wir weisen darauf hin, dass die Daten�bertragung im Internet (z.B. bei der Kommunikation per E-Mail) Sicherheitsl�cken aufweisen kann. Ein l�ckenloser Schutz der Daten vor dem Zugriff durch Dritte ist nicht m�glich.</p>
				<p>Der Nutzung von im Rahmen der Impressumspflicht ver�ffentlichten Kontaktdaten durch Dritte zur �bersendung von nicht ausdr�cklich angeforderter Werbung und Informationsmaterialien wird hiermit ausdr�cklich widersprochen. Die Betreiber der Seiten behalten sich ausdr�cklich rechtliche Schritte im Falle der unverlangten Zusendung von Werbeinformationen, etwa durch Spam-Mails, vor.</p>';
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="anleitung")
			{
			echo 'Hier kommt irgendwann eine Anleitung hin, wenn ich daf�r nicht zu faul bin.<br><br>
				Wenn du Freude an Spielen hast, die sprachliches Improvisationsgeschick erfordern, dann solltest du dir au�erdem das <a href="http://rhetorisches-quartett.de" target="_blank">Rhetorische Quartett</a> ansehen.';
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="neu")
			{
				if (isset($_POST["new"])) 
				{
					$flexion = 0;
					if (isset($_POST['flexion'])) { $flexion = 1; }
					$query = mysql_query("insert into games (word, status, starter, language, flexion) values ('".strtolower(umlaute(mysql_real_escape_string($_POST['word'])))."', 0, '".mysql_real_escape_string($_SESSION['player'])."', '".mysql_real_escape_string($_POST['language'])."', ".$flexion.")");
					$id = mysql_insert_id();
					$query = mysql_query("insert into playerstatus (game, player, status) values ($id, '".mysql_real_escape_string($_SESSION['player'])."', 0)");
					$query = mysql_query("create table game".$id." (word varchar(255) CHARACTER SET latin1 COLLATE latin1_german1_ci, player varchar(255) CHARACTER SET latin1 COLLATE latin1_german1_ci, points int(2))") or die("Fehler beim Anlegen der Spieltabelle: ".mysql_error());
					header("Location: http://antiscrabble.de/woerterautomat/?go=game&game=".$id); /* Redirect browser */
					exit();
				}
				else echo '<form method="post">Gib ein Wort ein, mit dem du ein Spiel starten willst.<br>
					<input type="text" name="word" id="newwordinput"><br>
					Sprache: <select name="language" id="languageinput">
					<option value="de">Deutsch</option>
					<option value="en">Englisch</option>
					</select>
					<span title="gebeugte und abgeleitete Formen von W�rtern wie Mehrzahlen, Deklinationen, Konjugationen, zum Beispiel H�user, gelaufen, fragte, dessen, mir, Notarin, Fuchses">Flexionsformen</span> erlaubt: <input type="checkbox" name="flexion" value="true" id="flexioncheckbox">
					<input type="submit" name="new" value="Spiel starten" id="newgamesubmit">
					</form>
					<script src="newgame.js"></script>';
			}
			elseif (isset($_GET["go"]) and $_GET["go"]=="game")
				include_once("game.php");
			elseif (isset($_GET["go"]) and $_GET["go"]=="games")
				include_once("allGames.php");
			else
				include_once("allGames.php");
			?>
		</div>
	</div>
	</body>
</html>
