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
	var inputfield = document.getElementById("input");
	var word = inputfield.value;
	if (word.length >2 && !inputfield.disabled)
	{
		inputfield.value = '';
		resyncletters();
		if (words.indexOf(word) == -1)
		{
			// the word is placed by receivedata, not here: only the server knows its points, which points sorting needs
			post({action: "newword", game: game, word: word}, function(data) {
				receivedata(data);
				// the rejected word comes back into the field, so waiting it out costs no retyping
				if (data.blocked) { inputfield.value = word; resyncletters(); countdown(data.blocked); }
			});
		}
		else
		{
			colortransition(word+"word");
		}
	}
  }

function countdown(seconds)
  {
	var line = document.getElementById("gamemessage");
	document.getElementById("input").disabled = seconds > 0;
	line.textContent = seconds > 0 ? "Zu viele nicht erlaubte Wörter probiert, warte "+seconds+" Sekunden!" : "";
	line.className = seconds > 0 ? "warning" : "hide";
	if (seconds > 0) { setTimeout(function() { countdown(seconds-1); }, 1000); }
  }

function removeword(word)
  {
	post({action: "removeword", game: game, word: word}, mystatus == 3 ? finishdata : receivedata);
	words.splice(words.indexOf(word), 1);
	// a finished game redraws every word from the response, an active one only ever adds to what is there
	if (mystatus != 3) { document.getElementById(word+"word").remove(); }
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
		var box = reactionbox(word, true);
		wordspan.appendChild(box);
		wordspan.onclick = function(e) { togglepopout(e, box); };
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
		// the own row wins the merged entry's user_id, so a shared word still counts as this player's
		if (rows[i].user_id == selfid) { byword[rows[i].word].user_id = selfid; }
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
	}
	else
	{
		var finders = uniquewords[lookFor(word["word"], uniquewords, 'word')].finders;
		if (finders.length == 1) { wordspan.className += ' unique'; }
		if (words.indexOf(word["word"]) == -1) { wordspan.className += ' others'; }
		else if (finders.length > 1) { wordspan.className += ' shared'; }
		wordspan.appendChild(pointspan(word["points"]));
	}
	var box = reactionbox(word["word"], word["user_id"] == selfid);
	if (box)
	{
		wordspan.appendChild(box);
		wordspan.onclick = function(e) { togglepopout(e, box); };
	}
	sortcontainer.appendChild(wordspan);
	sortcontainer.appendChild(document.createTextNode(" "));
}

var reactionemoji = ['💪', '👍', '🤦', '😭', '🤯', '😂', '✨', '❓', '🚫']; // keep in sync with $reactionemoji in receiver.php

document.addEventListener('click', function(e) {
	closepopouts();
	if (!e.target.closest("#lookup")) { closelookup(); }
});

// a tap never reaches the listener above, since the word stops the click to keep its own panel open
function togglepopout(e, box)
{
	e.stopPropagation();
	closepopouts(box);
	closelookup();
	box.classList.toggle('open');
}

function closepopouts(keep)
{
	document.querySelectorAll('.popout.open').forEach(function(box) { if (box != keep) { box.classList.remove('open'); } });
}

function closelookup()
{
	var panel = document.getElementById("lookup");
	if (panel) { panel.remove(); }
}

function reactionsFor(word)
{
	return reactions.filter(function(r) { return r.word == word; });
}

// deletable is set for this player's own words, whose panel keeps offering removal after finishing:
// it only ever costs the player points, and it is the one way to undo a mistyped word
function reactionbox(word, deletable)
{
	var existing = reactionsFor(word);
	// reacting and looking up are only offered once this player has personally finished
	var canreact = isplayer && mystatus == 3;
	if (existing.length == 0 && !canreact && !deletable) { return null; }
	var box = element('span', 'popout');
	var mine = null;
	var panel = element('span', 'popoutpanel');
	if (playerlist.length > 2 && mystatus == 3) {
		var finders = uniquewords[lookFor(word, uniquewords, 'word')].finders;
		panel.appendChild(element('span', 'reactionline', finders.includes("Wörterbuch") ? 'Nicht gefunden' : 'Gefunden von ' + finders.join(', ') )); 
	}
	existing.forEach(function(r) {
		var badge = element('span', 'reactionbadge', r.emoji);
		box.appendChild(badge);
		panel.appendChild(element('span', 'reactionline', r.emoji + ' ' + (r.display_name || 'Gast')));
		if (r.reactor_id == selfid) { mine = r.emoji; }
	});
	var row = element('span', 'popoutrow');
	if (canreact) {
		var picker = element('span', 'modes');
		reactionemoji.forEach(function(emoji) {
			var choice = element('span', emoji == mine ? 'selected' : '', emoji);
			choice.onclick = function(e) {
				e.stopPropagation();
				post({action: (emoji == mine ? 'unreact' : 'react'), game: game, word: word, emoji: emoji}, finishdata);
			};
			picker.appendChild(choice);
		});
		row.appendChild(picker);
	}
	var actions = element('span', 'modes');
	// the dictionary answers for every word it did not object to, and for its own unfound ones
	if (canreact && solution.length && lookFor('❗', existing, 'emoji') == -1) {
		var look = element('span', '', '🔍');
		look.onclick = function(e) { e.stopPropagation(); box.classList.remove('open'); lookuphistory = []; lookup(word); };
		actions.appendChild(look);
	}
	if (deletable) {
		var del = element('span', '', '❌');
		del.onclick = function(e) { e.stopPropagation(); removeword(word); };
		actions.appendChild(del);
	}
	if (actions.children.length) { row.appendChild(actions); }
	if (row.children.length) { panel.appendChild(row); }
	box.appendChild(panel);
	return box;
}

var lookuphistory = [];

function lookup(word)
{
	get("receiver.php?action=lookup&game="+game+"&word="+encodeURIComponent(word), function(entries) { writelookup(word, entries); });
}

function writelookup(word, entries)
{
	var panel = document.getElementById("lookup");
	if (!panel)
	{
		panel = element('div');
		panel.id = "lookup";
		document.body.appendChild(panel);
	}
	panel.innerHTML = "";
	var bar = element('div');
	bar.id = "lookupbar";
	if (lookuphistory.length)
	{
		var back = element('span', '', '←');
		back.onclick = function() { lookup(lookuphistory.pop()); };
		bar.appendChild(back);
	}
	bar.appendChild(element('h1', '', word));
	var close = element('span', '', '✕');
	close.onclick = closelookup;
	bar.appendChild(close);
	panel.appendChild(bar);

	var body = element('div');
	body.id = "lookupbody";
	panel.appendChild(body);
	if (!entries.length) { body.appendChild(element('div', 'tag', 'kein Eintrag')); }
	entries.forEach(function(entry) {
		// a game substituting umlauts can match two spellings at once, and each is an entry of its own
		if (entries.length > 1) { body.appendChild(element('h3', '', entry.word)); }
		var sounds = entry.sounds || [];
		var senses = entry.senses || [];
		// a spelling can be a form of several roots at once, and is one entry in the dictionary for each of them
		var roots = group((entry.forms || []).filter(function(form) { return form.root != word; }), function(form) { return form.root; });

		if (sounds.length) { body.appendChild(element('div', 'lookuphead', 'Aussprache')); }
		group(sounds, function(sound) { return labels(sound).join(); }).forEach(function(g) {
			var line = element('div', 'ipaline');
			labels(g.items[0]).forEach(function(label) { line.appendChild(element('span', 'wordspan', label)); });
			line.appendChild(document.createTextNode(g.items.map(function(sound) { return sound.ipa; }).join(' · ')));
			body.appendChild(line);
		});

		if (senses.length) { body.appendChild(element('div', 'lookuphead', 'Bedeutungen')); }
		// glosses are a path, not a list: senses sharing their first one are one meaning with several readings
		group(senses, function(sense) { return sense.glosses[0]; }).forEach(function(g, i) {
			body.appendChild(senseline(g.items[0].glosses.length == 1 ? g.items[0] : {}, (i+1)+".", g.key, ''));
			var letters = 0;
			g.items.forEach(function(sense) {
				if (sense.glosses.length > 1)
				{
					body.appendChild(senseline(sense, "abcdefghijklmnopqrstuvwxyz".charAt(letters++)+".",
						sense.glosses.slice(1).join(': '), ' subsense'));
				}
			});
		});

		if (roots.length) { body.appendChild(element('div', 'lookuphead', 'Form von')); }
		roots.forEach(function(g) {
			var line = element('div', 'rootline');
			line.appendChild(element('b', '', g.key));
			var button = element('span', 'modes');
			var look = element('span', '', '🔍');
			look.onclick = function() { lookuphistory.push(word); lookup(g.key); };
			button.appendChild(look);
			line.appendChild(button);
			body.appendChild(line);
			g.items.forEach(function(form) {
				var formline = element('div', 'formline', marker(form));
				formline.appendChild(element('span', 'tag', ' '+labels(form).join(', ')));
				body.appendChild(formline);
			});
		});
	});
}

function senseline(sense, num, gloss, level)
{
	var line = element('div', 'sense'+level);
	line.appendChild(element('span', 'num', num));
	line.appendChild(document.createTextNode(marker(sense)));
	if (labels(sense).length) { line.appendChild(element('span', 'tag', ' '+labels(sense).join(', '))); }
	line.appendChild(document.createTextNode(' '+gloss));
	return line;
}

function labels(item)
{
	return (item.tags || []).concat(item.note || []);
}

// the same two symbols the Wörterbuch reactions use: ❕ for what no game accepts, ⎇ for a flexion
function marker(item)
{
	return (item.strict ? '❕' : '') + (item.inflected ? '⎇' : '');
}

function group(list, key)
{
	var groups = [];
	list.forEach(function(item) {
		var found = lookFor(key(item), groups, 'key');
		if (found == -1) { groups.push({key: key(item), items: [item]}); }
		else { groups[found].items.push(item); }
	});
	return groups;
}

function element(tag, className, text)
{
	var node = document.createElement(tag);
	if (className) { node.className = className; }
	if (text) { node.textContent = text; }
	return node;
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
		// only a player who has finished, and only against one who has outsat the limit; the payload
		// carrying a user_id at all is finishstate, which is the only one this player can be seeing
		if (isplayer && mystatus == 3 && list[i].status != 3
			&& timelimit > 0 && list[i].last_activity + elapsed >= timelimit)
		{
			var force = document.createElement('a');
			force.className = 'forcefinish';
			force.textContent = "⏰︎ abschließen";
			force.onclick = function(id, name, since) { return function() { forcefinish(id, name, since); }; }
				(list[i].user_id, playername, list[i].last_activity + elapsed);
			players.appendChild(force);
		}
	}
}

function forcefinish(id, name, since)
{
	if (confirm(name+" war zuletzt "+timeago(since)+" aktiv. Das Spiel für "+name+" abschließen?"))
	{
		post({action: "forcefinish", game: game, player: id}, finishdata);
	}
}

function finishdata(data)
{
	pollwait = 5000;
	playerstamp = Date.now();
	version = data.version;
	writeplayers(data.players);
	solution = data.solution;
	if (!solution.length) { anagrambutton.remove(); } // no dictionary for this language, or nothing in the word
	var found = data.words.map(function(row) { return row.word; });
	var unfound = solution.filter(function(word) { return found.indexOf(word) == -1; })
		.map(function(word) { return {word: word, user_id: -1, player: "Wörterbuch"}; });
	allplayersdump = data.players.concat(unfound.length ? [{player: "Wörterbuch", user_id: -1}] : []);
	allwordsdump = data.words.concat(unfound);
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
	var added = false;
	data.words.forEach(function(row) { if (words.indexOf(row.word) == -1) { words.push(row.word); added = true; } });
	if (added) { sortwords(); }
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
		var newbox = reactionbox(data.words[i].word, true);
		if (wasopen) { newbox.classList.add('open'); }
		wordspan.appendChild(newbox);
		wordspan.onclick = function(box) { return function(e) { togglepopout(e, box); }; }(newbox);
	}
	writeplayers(data.players);
	if (changed && sortmode == 'points') { sortwords(); }
  }
