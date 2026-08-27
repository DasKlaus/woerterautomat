function playerbutton()
  {
	var button = document.createElement('button');
	button.type = "button";
	button.textContent = "nach Spielern";
	button.onclick = function() { select(button); sortmode = 'player'; sortwords(); };
	document.getElementById("sortbuttons").appendChild(button);
  }

function letterclicked(obj)
  {
	if (mystatus != 3) {
	  obj.style.display = "none";
	  document.getElementById("input").value += obj.innerHTML;
	}
  }

function resyncletters()
  {
	var inputfield = document.getElementById("input");
	var spans = document.getElementById("letters").children;
	for (var i=0; i<spans.length; i++) { spans[i].style.display = 'inline'; }
	var value = inputfield.value.normalize('NFC').toLowerCase();
	var accepted = '';
	for (var i=0; i<value.length; i++)
	{
		var needed = (substitute && value[i] in umlauts) ? umlauts[value[i]] : value[i];
		var used = [];
		var ok = true;
		for (var j=0; j<needed.length; j++)
		{
			var span = findletterspan(needed[j], true);
			if (!span) { ok = false; break; }
			span.style.display = 'none';
			used.push(span);
		}
		if (ok) { accepted += needed; }
		else { used.forEach(function(span) { span.style.display = 'inline'; }); }
	}
	if (inputfield.value != accepted) { inputfield.value = accepted; }
  }

function submitword()
  {
	var word = document.getElementById("input").value;
	if (word.length >2)
	{
		document.getElementById("input").value = '';
		resyncletters();
		if (words.indexOf(word) == -1)
		{
			post({action: "newword", game: game, word: word}, receivedata);
			words[words.length] = word;
			sortwords();
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
					if (!isNaN(child.children[0].innerHTML) && (child.children[0].innerHTML - word.length) * direction > 0) { wordbox.insertBefore(lengthbox, child); inserted = true; }
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
					if (!isNaN(child.children[0].innerHTML) && (child.children[0].innerHTML - points) * direction > 0) { wordbox.insertBefore(pointsbox, child); inserted = true; }
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
	if (found != -1)
	{
		var span = pointspan(wordpoints[found].points);
		span.id = word+'points';
		wordspan.appendChild(span);
	}
	if (mystatus != 3) {
		wordspan.className += ' deletable';
		var box = reactionbox(word, wordspan);
		wordspan.appendChild(box);
		wordspan.onclick = function(e) { e.stopPropagation(); box.classList.toggle('open'); };
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
	var tosort = words.slice();
	if (mystatus == 3) {tosort = ((sortmode == 'player') ? allwordsdump : uniquewords).slice();}
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
			if (mystatus == 3) {tosort.sort(sortallwordsdump);}
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
	if (direction == -1) { tosort = tosort.slice().reverse(); }
	for (var i=0; i<tosort.length; i++)
	{
		if (mystatus == 3) {sortfinishedword(tosort[i]);}
		else {sortword(tosort[i]);}
	}
  }

function makeplayerboxes()
{
	for (var i=0; i<allplayersdump.length; i++)
	{
		var playerbox = document.createElement('div');
		playerbox.id = "player"+i+"words";
		var wordbox = document.getElementById("words");
		wordbox.insertBefore(playerbox, direction == 1 ? null : wordbox.firstChild);
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
			byword[rows[i].word] = {word: rows[i].word, user_id: rows[i].user_id, points: rows[i].points, finders: []};
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
					if (!isNaN(child.children[0].innerHTML) && (child.children[0].innerHTML - word["word"].length) * direction > 0) { wordbox.insertBefore(lengthbox, child); inserted = true; }
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
			// an unfound word carries no points and always heads the order, so it skips the search for its place
			var points = (word["points"] === undefined) ? "∞" : word["points"];
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
				var child = (points === "∞") ? null : wordbox.children[0];
				while (child && !inserted)
				{
					if (!isNaN(child.children[0].innerHTML) && (child.children[0].innerHTML - points) * direction > 0) { wordbox.insertBefore(pointsbox, child); inserted = true; }
					child = child.nextSibling;
				}
				if (!inserted)
				{
					wordbox.insertBefore(pointsbox, (points === "∞" && direction == -1) ? wordbox.firstChild : null);
				}
			}
			sortcontainer = document.getElementById(points+"words");
		break;
		case 'player':
			sortcontainer = document.getElementById("player"+lookFor(word["user_id"], allplayersdump, 'user_id')+"words");
		break;
	}
	var wordspan = document.createElement('span');
	wordspan.className = 'wordspan';
	wordspan.textContent = word["word"];
	if (word["user_id"] == -1)
	{
		wordspan.className += ' unfound';
		wordspan.title = "Wörterbuch";
	}
	else
	{
		var finders = uniquewords[lookFor(word["word"], uniquewords, 'word')].finders;
		if (finders.length == 1) { wordspan.className += ' unique'; }
		if (words.indexOf(word["word"]) == -1) { wordspan.className += ' others'; }
		else if (finders.length > 1) { wordspan.className += ' shared'; }
		if (playerlist.length > 2) { wordspan.title = finders.join(', '); }
		wordspan.appendChild(pointspan(word["points"]));
	}
	var box = reactionbox(word["word"]);
	if (box) { wordspan.appendChild(box); }
	sortcontainer.appendChild(wordspan);
	sortcontainer.appendChild(document.createTextNode(" "));
}

var reactionemoji = ['💪', '👍', '🤦', '😭', '🤯', '😂', '✨', '❓', '🚫']; // keep in sync with $reactionemoji in receiver.php

document.addEventListener('click', function() {
	document.querySelectorAll('.popout.open').forEach(function(box) { box.classList.remove('open'); });
});

function reactionsFor(word)
{
	return reactions.filter(function(r) { return r.word == word; });
}

// wordspan is only passed for a still-deletable (active-game) word: it adds a delete choice
// and makes the whole word (not just the badges) open the popout, since there may be no badges yet
function reactionbox(word, wordspan)
{
	var existing = reactionsFor(word);
	// reacting is only offered once this player has personally finished, so seeing others'
	// reactions on your own still-active words is an incentive to finish, not an option yet
	var canreact = isplayer && mystatus == 3;
	if (existing.length == 0 && !canreact && !wordspan) { return null; }
	var box = document.createElement('span');
	box.className = 'popout';
	var mine = null;
	var panel = document.createElement('span');
	panel.className = 'popoutpanel';
	existing.forEach(function(r) {
		var badge = document.createElement('span');
		badge.className = 'reactionbadge';
		badge.textContent = r.emoji;
		badge.title = r.display_name || 'Gast';
		box.appendChild(badge);
		var line = document.createElement('span');
		line.className = 'reactionline';
		line.textContent = r.emoji + ' ' + (r.display_name || 'Gast');
		panel.appendChild(line);
		if (r.reactor_id == selfid) { mine = r.emoji; }
	});
	if (canreact || wordspan) {
		var picker = document.createElement('span');
		picker.className = 'reactionpicker';
		if (canreact) {
			reactionemoji.forEach(function(emoji) {
				var choice = document.createElement('span');
				choice.className = 'popoutchoice' + (emoji == mine ? ' active' : '');
				choice.textContent = emoji;
				choice.onclick = function(e) {
					e.stopPropagation();
					post({action: (emoji == mine ? 'unreact' : 'react'), game: game, word: word, emoji: emoji}, finishdata);
				};
				picker.appendChild(choice);
			});
		}
		if (wordspan) {
			var del = document.createElement('span');
			del.className = 'popoutchoice alert';
			del.textContent = 'löschen';
			del.onclick = function(e) { e.stopPropagation(); removeword(wordspan); };
			picker.appendChild(del);
		}
		panel.appendChild(picker);
	}
	box.appendChild(panel);
	box.onclick = function(e) { e.stopPropagation(); box.classList.toggle('open'); };
	return box;
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

function makeletterboxes(word)
  {
	for (i=0; i<word.length; i++)
	{
		if (!document.getElementById(word.charAt(i)+"words"))
		{
			var startletterbox = document.createElement('div');
			startletterbox.id = word.charAt(i)+"words";
			var wordbox = document.getElementById("words");
			wordbox.insertBefore(startletterbox, direction == 1 ? null : wordbox.firstChild);
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
	inputfield.value = inputfield.value.slice(0, -1);
	resyncletters();
  }

function keydownhandle(e)
  {
	evt = e || event;
	if (evt.keyCode == 13)
	{
		evt.preventDefault();
		submitword();
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
	mystatus=3;
	document.getElementById('finish').style.display = 'none';
	document.getElementById('inputline').style.display = 'none';
	document.getElementById('input').value = '';
	writeletters(document.getElementById("letters").textContent);
	playerbutton();
	document.querySelector("#lettersortbuttons .modes").appendChild(anagrambutton);
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
	gamedata = setTimeout(poll, pollwait);
	if (document.hidden) { return; }
	if (mystatus == 3)
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
		if (list[i].status == 3) { playerstatus = "abgeschlossen"; }
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
	solution = data.solution;
	var found = data.words.map(function(row) { return row.word; });
	allplayersdump = data.players.concat([{player: "Wörterbuch", user_id: -1}]);
	allwordsdump = data.words.concat(solution.filter(function(word) { return found.indexOf(word) == -1; })
		.map(function(word) { return {word: word, user_id: -1, player: "Wörterbuch"}; }));
	uniquewords = dedupe(allwordsdump);
	reactions = data.reactions;
	gamestatus = data.gamestatus;
	sortwords();
}

function receivedata(data)
  {
	pollwait = 5000;
	playerstamp = Date.now();
	version = data.version;
	wordpoints = data.words;
	reactions = data.reactions;
	var changed = false;
	ownpoints = 0;
	for (var i=0; i<data.words.length; i++)
	{
		ownpoints += data.words[i].points;
		var wordspan = document.getElementById(data.words[i].word+"word");
		var span = document.getElementById(data.words[i].word+'points');
		if (!span)
		{
			span = pointspan(data.words[i].points);
			span.id = data.words[i].word+'points';
			wordspan.appendChild(span);
		}
		else if (span.textContent != data.words[i].points)
		{
			span.textContent = data.words[i].points;
			colortransition(data.words[i].word+"word");
			changed = true;
		}
		var oldbox = wordspan.querySelector('.popout');
		var wasopen = oldbox && oldbox.classList.contains('open');
		if (oldbox) { wordspan.removeChild(oldbox); }
		var newbox = reactionbox(data.words[i].word, wordspan);
		if (wasopen) { newbox.classList.add('open'); }
		wordspan.appendChild(newbox);
		wordspan.onclick = function(box) { return function(e) { e.stopPropagation(); box.classList.toggle('open'); }; }(newbox);
	}
	writeplayers(data.players);
	if (changed && sortmode == 'points') { sortwords(); }
  }
