function pagebutton(label, target, enabled)
{
	var button = document.createElement(enabled ? 'a' : 'span');
	if (enabled) { button.href = 'spiele?mode='+mode+'&sort='+sort+'&dir='+dir+'&page='+target; }
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

function gamedata(response)
{
	pagination(response.pages);
	var data = response.games;
	var content = document.getElementById("games");
	content.innerHTML = "";
	for (var i=0; i<data.length; i++)
	{
		var settings = document.createElement('div');
		settings.className = 'settings';
		settingsbadges(settings, data[i]);
		
		var gamelink = document.createElement('a');
		gamelink.className = 'game';
		gamelink.href = data[i].url;
		var headline = document.createElement('h2');
		headline.textContent = data[i].word;
		gamelink.appendChild(headline);
		gamelink.appendChild(settings);
		gamelink.appendChild(document.createElement('br'));
		
		var players = document.createElement('span');
		for (var j=0; j<data[i].players.length; j++) {
			var player = document.createElement('span');
			player.className = 'wordspan';
			player.textContent = data[i].players[j].player || txt.guest;
			if (data[i].players[j].status == 3) { player.appendChild(document.createTextNode(" ✓")); }
			else if (data[i].timelimit > 0 && data[i].players[j].last_activity >= data[i].timelimit) { player.appendChild(document.createTextNode(" ⏰︎")); }
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
	fetch("receiver.php?action=showgames&mode="+mode+"&sort="+sort+"&dir="+dir+"&page="+page)
		.then(function(response) { return response.json(); })
		.then(gamedata);
}
