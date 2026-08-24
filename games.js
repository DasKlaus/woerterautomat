function pagebutton(label, target, enabled)
{
	var button = document.createElement(enabled ? 'a' : 'span');
	if (enabled) { button.href = '?go=games&mode='+mode+'&page='+target; }
	button.textContent = label;
	return button;
}

function pagination(pages)
{
	var nav = document.getElementById("pagination");
	nav.innerHTML = "";
	if (pages < 2) { return; }
	nav.appendChild(pagebutton("<<", 1, page > 1));
	nav.appendChild(pagebutton("<", page - 1, page > 1));
	nav.appendChild(pagebutton(page, page, false));
	nav.appendChild(pagebutton(">", page + 1, page < pages));
	nav.appendChild(pagebutton(">>", pages, page < pages));
}

function calcTimediff(timediff)
{
	var tshort = "⁕";
	var tlong = "gerade eben";
	if (timediff > 0)
	{
		var time;
		var unit;
		var plural;
		if (timediff > 525600) { time = Math.round(timediff/525600); u = "j"; unit =" Jahr"; plural = "en"}
		else if (timediff > 1440) { time = Math.round(timediff/1440); u = "d"; unit = " Tag"; plural = "en"}
		else if (timediff > 60) { time = Math.round(timediff/60); u = "h"; unit = " Stunde"; plural = "n"}
		else { time = timediff; u = "m"; unit = " Minute"; plural = "n"}
		var tshort = time + u;
		var tlong = "gestartet vor " + time + unit + (time === 1 ? "" : plural);
	}
	return [tshort,tlong];
}

function gamedata(response)
{
	pagination(response.pages);
	var data = response.games;
	var content = document.getElementById("games");
	content.innerHTML = "";
	for (var i=0; i<data.length; i++)
	{
		var status = 'neu';
		if (data[i].status == 1) status = 'laufend';
		else if (data[i].status == 2) status = 'voll';
		else if (data[i].status == 3) status = 'abgeschlossen';
		
		var timediff = data[i].starttime;
		var whenstarted = calcTimediff(timediff);
		
		var settings = document.createElement('div');
		settings.className = 'settings';
		
		var span = document.createElement('span');
		span.textContent = "○◐●✓"[data[i].status];
		span.setAttribute('title',["neu","laufend","Spielerzahl erreicht","abgeschlossen"][data[i].status]);
		settings.appendChild(span);
		
		span = document.createElement('span');
		span.textContent = whenstarted[0];
		span.setAttribute('title', whenstarted[1]);
		settings.appendChild(span);
		
		span = document.createElement('span');
		span.textContent = data[i].language.toUpperCase();
		span.setAttribute('title', "auf "+{de:"Deutsch",en:"Englisch"}[data[i].language]);
		settings.appendChild(span);
		
		if (data[i].umlauts) {
			span = document.createElement('span');
			span.textContent = "Ä→AE";
			span.setAttribute('title', 'Umlaute können substituiert werden');
			settings.appendChild(span);
		}
		if (data[i].flexion) {
			span = document.createElement('span');
			span.textContent = "⎇";
			span.setAttribute('title', 'Flexionsformen wie Mehrzahlen, Deklinationen, Konjugationen erlaubt');
			settings.appendChild(span);
		}
		if (data[i].private) {
			span = document.createElement('span');
			span.textContent = "⚿";
			span.setAttribute('title', 'privates Spiel, unsichtbar für andere, zum Einladen URL teilen');
			settings.appendChild(span);
		}
		if (data[i].maxplayers>0) {
			span = document.createElement('span');
			span.textContent = "☺"+data[i].maxplayers;
			span.setAttribute('title', 'bis zu '+data[i].maxplayers+' Spieler');
			settings.appendChild(span);
		}
		
		var gamelink = document.createElement('a');
		gamelink.className = 'game';
		gamelink.href = '?go=game&game='+data[i].id;
		var headline = document.createElement('h2');
		headline.textContent = data[i].word;
		gamelink.appendChild(headline);
		gamelink.appendChild(settings);
		gamelink.appendChild(document.createElement('br'));
		
		var players = document.createElement('span');
		for (var j=0; j<data[i].players.length; j++) {
			var player = document.createElement('span');
			player.className = 'wordspan';
			player.textContent = data[i].players[j].player || "Gast";
			if (data[i].status == 3) {
				var playerpoints = document.createElement('span');
				playerpoints.className = 'pointspan';
				playerpoints.textContent = data[i].players[j].points
				player.appendChild(playerpoints);
			}
			players.appendChild(player);
			players.appendChild(document.createTextNode(' '));
		}
		gamelink.appendChild(players);
		content.appendChild(gamelink);
	}
}

function listgames()
{
	fetch("receiver.php?action=showgames&mode="+mode+"&page="+page)
		.then(function(response) { return response.json(); })
		.then(gamedata);
}
