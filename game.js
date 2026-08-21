// show how many words others have in those categories/letters


// Begrenzung Spieler, Einladung Spieler
// Wörterbuch: Wort, Sprache, Deklination/Eigenname? Stimmen pro, Stimmen contra
// Auswertung: Tabelle mit Haken für Deklination/Eigenname und ja/nein
// Auswertung: sortieren nach Spielern, Punkten, Häufigkeit, Länge, Alphabet
// Auswertung: langsam anzeigen
// Auswertung: weiter mögliche Wörter aus dem Wörterbuch anzeigen

function select(button)
  {
	var buttons = button.parentNode.children;
	for (var i=0; i<buttons.length; i++)
	  {
		buttons[i].classList.toggle('selected', buttons[i] == button);
	  }
  }

function playerbutton()
  {
	var button = document.createElement('div');
	button.textContent = "nach Spielern";
	button.onclick = function() { select(button); sortmode = 'player'; sortwords(); };
	document.getElementById("sortbuttons").appendChild(button);
  }

function letterclicked(obj)
  {
	obj.style.display = "none";
	document.getElementById("input").value += obj.innerHTML;
  }
  
function submitword()
  {
	var word = document.getElementById("input").value;
	if (word.length >2)
	{
		document.getElementById("input").value = '';
		for (i=0; i<document.getElementById("letters").children.length; i++)
		  {
			document.getElementById("letters").children[i].style.display = 'inline';
		  }
		if (words.indexOf(word) == -1)
		{
			post({action: "newword", game: game, word: word}, receivedata);
			words[words.length] = word;
			sortword(word);
		}
		else
		{
			colortransition(word+"word");
		}
	}
  }

function removeword(wordspan)
  {
	var word = wordspan.id.substr(0, wordspan.id.indexOf("word"));
	post({action: "removeword", game: game, word: word}, receivedata);
	words.splice(words.indexOf(word), 1);
	wordspan.parentNode.removeChild(wordspan);
  }

function pointspan(points)
  {
	var span = document.createElement('span');
	span.className = 'pointspan';
	span.textContent = points;
	return span;
  }

function sortword(word)
  {
	var sortcontainer = document.getElementById("words");
	var found = lookFor(word, wordpoints, 'word');
	switch (sortmode)
	{
		case 'standard': 
			sortcontainer = document.getElementById(word.charAt(0)+"words");
		break;
		case 'chrono': 
		break;
		case 'alpha': 
			sortcontainer = document.getElementById(word.charAt(0)+"words");
		break;
		case 'length': 
			if (!document.getElementById(word.length+"words")) 
			{
				var lengthbox = document.createElement('div');
				lengthbox.id = word.length+"words";
				var number = document.createElement('span');
				number.className = 'startnumber';
				number.innerHTML = word.length;
				lengthbox.appendChild(number);
				
				var wordbox = document.getElementById("words");
				var inserted = false;
				var child = wordbox.children[0];
				while (child && !inserted)
				{
					if (!isNaN(child.children[0].innerHTML) && child.children[0].innerHTML > word.length) { wordbox.insertBefore(lengthbox, child); inserted = true; }
					child = child.nextSibling;
				}
				if (!inserted)
				{
					wordbox.appendChild(lengthbox);
				}
			}
			sortcontainer = document.getElementById(word.length+"words");
		break;
		case 'points':
			var points = (found == -1) ? 0 : wordpoints[found].points;
			if (!document.getElementById(points+"words"))
			{
				var pointsbox = document.createElement('div');
				pointsbox.id = points+"words";
				var number = document.createElement('span');
				number.className = 'startnumber';
				number.innerHTML = points;
				pointsbox.appendChild(number);
				
				var wordbox = document.getElementById("words");
				var inserted = false;
				var child = wordbox.children[0];
				while (child && !inserted)
				{
					if (!isNaN(child.children[0].innerHTML) && parseInt(child.children[0].innerHTML) > parseInt(points)) { wordbox.insertBefore(pointsbox, child); inserted = true; }
					child = child.nextSibling;
				}
				if (!inserted)
				{
					wordbox.appendChild(pointsbox);
				}
			}
			sortcontainer = document.getElementById(points+"words");
		break;
		case 'player': 
			break;
	}
	var wordspan = document.createElement('span');
	wordspan.className = 'wordspan';
	wordspan.id = word+'word';
	wordspan.innerHTML = word;
	if (mystatus != 2) {wordspan.onclick = function() { removeword(wordspan); }; wordspan.className += ' deletable';}
	if (found != -1)
	{
		var span = pointspan(wordpoints[found].points);
		span.id = word+'points';
		wordspan.appendChild(span);
	}
	sortcontainer.appendChild(wordspan);
	sortcontainer.appendChild(document.createTextNode(" "));
  }

function sortallwordsdump(a,b)
{
	return ((a["word"] < b["word"]) ? -1 : ((a["word"] > b["word"]) ? 1 : 0));
}

function sortwords()
  {
	var tosort = words;
	if (mystatus == 2) {tosort = (sortmode == 'player') ? allwordsdump : uniquewords;}
	var worddiv = document.getElementById("words");
	worddiv.innerHTML = '';
	switch (sortmode)
	{
		case 'standard':
			makeletterboxes(originalword);
		break;
		case 'chrono':
		break;
		case 'alpha':
			var sortedword = sortstring(originalword);
			makeletterboxes(sortedword);
			if (mystatus == 2) {tosort.sort(sortallwordsdump);}
			else {tosort.sort();}
		break;
		case 'length':
		break;
		case 'points':
		break;
		case 'player': 
			makeplayerboxes();
		break;
	}
	for (var i=0; i<tosort.length; i++)
	{
		if (mystatus == 2) {sortfinishedword(tosort[i]);}
		else {sortword(tosort[i]);}
	}
  }

function makeplayerboxes()
{
	for (var i=0; i<allplayersdump.length; i++)
	{
		var playerbox = document.createElement('div');
		playerbox.id = "player"+i+"words";
		document.getElementById("words").appendChild(playerbox);
		var playerboxhead = document.createElement('span');
		playerboxhead.className = 'startletter';
		playerboxhead.textContent = allplayersdump[i].player || "Gast";
		playerbox.appendChild(playerboxhead);
	}
}
  
function dedupe(rows)
{
	var byword = Object.create(null);
	var list = [];
	for (var i=0; i<rows.length; i++)
	{
		if (!byword[rows[i].word])
		{
			byword[rows[i].word] = {word: rows[i].word, points: rows[i].points, finders: []};
			list.push(byword[rows[i].word]);
		}
		byword[rows[i].word].finders.push(rows[i].player || "Gast");
	}
	return list;
}

function sortfinishedword(word)
{
	var sortcontainer = document.getElementById("words");
	switch (sortmode)
	{
		case 'standard': 
			sortcontainer = document.getElementById(word["word"].charAt(0)+"words");
		break;
		case 'chrono': 
		break;
		case 'alpha': 
			sortcontainer = document.getElementById(word["word"].charAt(0)+"words");
		break;
		case 'length': 
			if (!document.getElementById(word["word"].length+"words")) 
			{
				var lengthbox = document.createElement('div');
				lengthbox.id = word["word"].length+"words";
				var number = document.createElement('span');
				number.className = 'startnumber';
				number.innerHTML = word["word"].length;
				lengthbox.appendChild(number);
				
				var wordbox = document.getElementById("words");
				var inserted = false;
				var child = wordbox.children[0];
				while (child && !inserted)
				{
					if (!isNaN(child.children[0].innerHTML) && child.children[0].innerHTML > word["word"].length) { wordbox.insertBefore(lengthbox, child); inserted = true; }
					child = child.nextSibling;
				}
				if (!inserted)
				{
					wordbox.appendChild(lengthbox);
				}
			}
			sortcontainer = document.getElementById(word["word"].length+"words");
		break;
		case 'points':
			var points = word["points"];
			if (!document.getElementById(points+"words"))
			{
				var pointsbox = document.createElement('div');
				pointsbox.id = points+"words";
				var number = document.createElement('span');
				number.className = 'startnumber';
				number.innerHTML = points;
				pointsbox.appendChild(number);
				
				var wordbox = document.getElementById("words");
				var inserted = false;
				var child = wordbox.children[0];
				while (child && !inserted)
				{
					if (!isNaN(child.children[0].innerHTML) && parseInt(child.children[0].innerHTML) > parseInt(points)) { wordbox.insertBefore(pointsbox, child); inserted = true; }
					child = child.nextSibling;
				}
				if (!inserted)
				{
					wordbox.appendChild(pointsbox);
				}
			}
			sortcontainer = document.getElementById(points+"words");
		break;
		case 'player':
			sortcontainer = document.getElementById("player"+lookFor(word["user_id"], allplayersdump, 'user_id')+"words");
		break;
	}
	var finders = uniquewords[lookFor(word["word"], uniquewords, 'word')].finders;
	var wordspan = document.createElement('span');
	wordspan.className = 'wordspan';
	if (finders.length == 1) { wordspan.className += ' unique'; }
	if (words.indexOf(word["word"]) == -1) { wordspan.className += ' others'; }
	else if (finders.length > 1) { wordspan.className += ' shared'; }
	if (allplayersdump.length > 2) { wordspan.title = finders.join(', '); }
	wordspan.textContent = word["word"];
	wordspan.appendChild(pointspan(word["points"]));
	sortcontainer.appendChild(wordspan);
	sortcontainer.appendChild(document.createTextNode(" "));
}
  
function lookFor(needle, haystack, param) 
{
	for (var i=0; i<haystack.length; i++)
	{
		if (haystack[i][param] == needle)
		{
			return i;
		}
	}
	return -1;
}

function sortstring(word)
  {
	var output = [];
	for (var i=0; i<word.length; i++)
	{
		output[output.length] = word.charAt(i);
	}
	output.sort();
	return output.join("");
  }

function randomstring(word)
  {
	var output = [];
	for (var i=0; i<word.length; i++)
	{
		output[output.length] = word.charAt(i);
	}
	output = shuffle(output);
	return output.join("");
  }

function shuffle(o)
{
    for(var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
}

function makeletterboxes(word)
  {
	for (i=0; i<word.length; i++)
	{
		if (!document.getElementById(word.charAt(i)+"words"))
		{
			var startletterbox = document.createElement('div');
			startletterbox.id = word.charAt(i)+"words";
			document.getElementById("words").appendChild(startletterbox);
			var letter = document.createElement('span');
			letter.className = 'startletter';
			letter.innerHTML = word.charAt(i);
			startletterbox.appendChild(letter);
		}
		else
		{
			document.getElementById(word.charAt(i)+"words").children[0].innerHTML += word.charAt(i);
		}
	}
  }

function writeletters(lettersinorder)
  {
	var letterbox = document.getElementById("letters");
	letterbox.innerHTML = '';
	for (i=0; i<lettersinorder.length; i++)
	  {
		letterbox.innerHTML += '<span onClick="letterclicked(this);">'+lettersinorder.charAt(i)+'</span>';
	  }
  }

function findletterspan(charpressed, none)
  {
	letterspan = document.getElementById("letters").children[0];
	while(letterspan)
	{
		if (letterspan.innerHTML == charpressed && ((letterspan.style.display != "none" && none) || (letterspan.style.display == "none" && !none)))
		{
			return letterspan;
		}
		letterspan = letterspan.nextSibling;
	}
  }

function backspace()
  {
	var inputfield = document.getElementById("input");
	if (inputfield.value.length > 0)
	{
		var chartrimmed = inputfield.value.substr(inputfield.value.length - 1, inputfield.value.length);
		inputfield.value = inputfield.value.substr(0, inputfield.value.length - 1);
		findletterspan(chartrimmed, false).style.display = "inline";
	}
  }

function keydownhandle(e)
  {
	evt = e || event;
	if (evt.keyCode == 8)
	{
		evt.preventDefault();
		backspace();
	}
  }

function keyhandle(e)
  {
	evt = e || event;
	evt.preventDefault();
	var chrTyped, chrCode = 0;
	if (evt.charCode!=null && evt.charCode!=0)       chrCode = evt.charCode;
	else if (evt.which!=null && evt.which!=0)     chrCode = evt.which;
	else if (evt.keyCode!=null && evt.keyCode!=0) chrCode = evt.keyCode;
	if (chrCode==0) chrTyped = ' ';
	  else chrTyped = String.fromCharCode(chrCode).toLowerCase();
	if (chrCode == 13) { submitword(); }
	if ("äöüß".indexOf(chrTyped) == -1)
	{
		if (findletterspan(chrTyped, true))
		{
			letterclicked(findletterspan(chrTyped, true));
		}
	}
	else
	{
		umlautletters = [];
		if (chrTyped == "ä") { umlautletters[0] = "a"; umlautletters[1] = "e"; }
		if (chrTyped == "ö") { umlautletters[0] = "o"; umlautletters[1] = "e"; }
		if (chrTyped == "ü") { umlautletters[0] = "u"; umlautletters[1] = "e"; }
		if (chrTyped == "ß") { umlautletters[0] = "s"; umlautletters[1] = "s"; }
		if (findletterspan(umlautletters[0], true))
		{
			letterclicked(findletterspan(umlautletters[0], true));
			if (findletterspan(umlautletters[1], true))
			{
				letterclicked(findletterspan(umlautletters[1], true));
			}
			else
			{
				backspace();
			}
		}
	}
  }

function colortransition(id, ini)
{
	var wordspan = document.getElementById(id);
	var fadein = wordspan.classList.contains("fadeIn");
	wordspan.classList.toggle("fadeIn", !fadein);
	wordspan.classList.toggle("fadeOut", fadein);
	if (ini === undefined)
	{
		setTimeout(function() { colortransition(id, 1); }, 250);
	}
}

function leave() {
	if (confirm("Wenn du dieses Spiel verlässt, gehen alle deine gefundenen Wörter verloren. Fortfahren?"))
	{
	post({action: "leavegame", game: game}, function(){ window.location = "."; });
	}
}

function finish() {
	mystatus=2;
	document.getElementById('finish').style.display = 'none';
	document.getElementById('inputline').style.display = 'none';
	document.getElementById('lettersortbuttons').style.display = 'none';
	playerbutton();
	post({action: "finishgame", game: game}, finishdata);
}

function get(url, callback)
{
	fetch(url).then(function(response) {
		if (response.status == 304) { pollwait = Math.min(pollwait + 5000, 30000); return; }
		response.json().then(callback);
	});
}

function post(data, callback)
{
	fetch("receiver.php", {method: "POST", body: new URLSearchParams(data)}).then(function(response) {
		response.json().then(callback);
	});
}

function poll()
{
	if (mystatus == 2 && gamestatus == 2) { return; }
	gamedata = setTimeout(poll, pollwait);
	if (document.hidden) { return; }
	if (mystatus == 2)
	{
		get("receiver.php?action=finishrequest&game="+game+"&version="+version, finishdata);
		return;
	}
	writeplayers(playerlist);
	get("receiver.php?action=datarequest&game="+game+"&version="+version, receivedata);
}

function repoll()
{
	if (document.hidden) { return; }
	clearTimeout(gamedata);
	pollwait = 5000;
	poll();
}

function timeago(minutes)
{
	var count = minutes, unit = "Minute", plural = "n";
	if (minutes >= 1440) { count = Math.round(minutes/1440); unit = "Tag"; plural = "en"; }
	else if (minutes >= 60) { count = Math.round(minutes/60); unit = "Stunde"; plural = "n"; }
	if (count < 1) { return "gerade eben"; }
	return "vor "+count+" "+unit+(count == 1 ? "" : plural);
}

function writeplayers(list)
{
	playerlist = list;
	var elapsed = Math.floor((Date.now() - playerstamp) / 60000);
	var players = document.getElementById("players");
	players.innerHTML = "";
	for (var i=0; i<list.length; i++)
	{
		var playername = list[i].player || "Gast";
		var points = list[i].self ? ownpoints : list[i].points;
		var playerstatus = "aktiv";
		if (list[i].status == 2) { playerstatus = "abgeschlossen"; }
		var activity = "letzte Aktion "+timeago(list[i].last_activity + elapsed);

		var playerdiv = document.createElement('div');
		playerdiv.className = 'playerdiv';
		playerdiv.appendChild(document.createTextNode(points === undefined ? playername : playername+' ('+points+')'));
		var meta = document.createElement('span');
		meta.style.fontSize = '10px';
		meta.appendChild(document.createTextNode(' - '+playerstatus));
		meta.appendChild(document.createElement('br'));
		meta.appendChild(document.createTextNode(activity));
		playerdiv.appendChild(meta);
		players.appendChild(playerdiv);
	}
}

function finishdata(data)
{
	pollwait = 5000;
	playerstamp = Date.now();
	version = data.version;
	writeplayers(data.players);
	allplayersdump = data.players;
	allwordsdump = data.words;
	uniquewords = dedupe(data.words);
	gamestatus = data.gamestatus;
	sortwords();
}

function receivedata(data)
  {
	pollwait = 5000;
	playerstamp = Date.now();
	version = data.version;
	wordpoints = data.words;
	var changed = false;
	ownpoints = 0;
	for (var i=0; i<data.words.length; i++)
	{
		ownpoints += data.words[i].points;
		var span = document.getElementById(data.words[i].word+'points');
		if (!span)
		{
			span = pointspan(data.words[i].points);
			span.id = data.words[i].word+'points';
			document.getElementById(data.words[i].word+"word").appendChild(span);
		}
		else if (span.textContent != data.words[i].points)
		{
			span.textContent = data.words[i].points;
			colortransition(data.words[i].word+"word");
			changed = true;
		}
	}
	writeplayers(data.players);
	if (changed && sortmode == 'points') { sortwords(); }
  }
