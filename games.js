function filter(filtermode)
{
	mode = filtermode;
	jQuery.get( "receiver.php?action=showgames&player="+player+"&mode="+mode, gamedata);
}

/* TODO: make better */
function calcTimediff(timediff)
{
	var activity = "gerade eben";
	if (timediff > 0)
	{
		var activity = "vor ";
		var prettydiff;
		var unit;
		var plural;
		if (timediff > 525600) { prettydiff = Math.round(timediff/525600); unit =" Jahr"; plural = "en"}
		else if (timediff > 1440) { prettydiff = Math.round(timediff/1440); unit = " Tag"; plural = "en"}
		else if (timediff > 60) { prettydiff = Math.round(timediff/60); unit = " Stunde"; plural = "n"}
		else { prettydiff = timediff; unit = " Minute"; plural = "n"}
		activity += prettydiff + unit + (prettydiff === 1 ? "" : plural)
	}
	return activity;
}

function gamedata(json)
{
	var data = $.parseJSON(json);
	var content = document.getElementById("games");
	content.innerHTML = "";
	for (var i=0; i<data.length; i++)
	{
		var status = 'neu';
		if (data[i].status == 1) status = 'laufend';
		else if (data[i].status == 2) status = 'abgeschlossen';
		
		var timediff = data[i].starttime;
		var activity = calcTimediff(timediff);
		
		var language = "Deutsch";
		if (data[i].language=="en") { language = "Englisch"; }
		
		var flexion = "ohne";
		if (data[i].flexion==1) { flexion = "mit"; }
		
		var gamelink = document.createElement('a');
		gamelink.className = 'game';
		gamelink.href = '?go=game&game='+data[i].id;
		var headline = document.createElement('h2');
		headline.innerHTML = data[i].word;
		gamelink.appendChild(headline);
		gamelink.innerHTML += '('+status+') gestartet '+activity+' auf '+language+' von '+data[i].starter+' '+flexion+' Flexionsformen<br>mit';
		for (var j=0; j<data[i].players.length; j++)
		{
			var player = data[i].players[j].player;
			player = (player.substr(0, 4)=="Gast")?"Gast":player;
			gamelink.innerHTML += ' '+player;
			if (data[i].status == 2) { gamelink.innerHTML += ' ('+data[i].players[j].points+')'; }
		}
		content.appendChild(gamelink);
	}
}
