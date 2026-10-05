function playerbutton()
  {
	var button = document.createElement('button');
	button.type = "button";
	button.textContent = txt.sortplayer;
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
	if (inputfield.value != accepted) {
		var pos = inputfield.selectionEnd + accepted.length - inputfield.value.length;
		inputfield.value = accepted;
		inputfield.setSelectionRange(pos, pos);
	}
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
	line.textContent = seconds > 0 ? fmt(txt.blocked, seconds) : "";
	line.className = seconds > 0 ? "warning" : "hide";
	if (seconds > 0) { setTimeout(function() { countdown(seconds-1); }, 1000); }
	else { document.getElementById("input").focus(); }
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
				number.innerHTML = (points < 0) ? "–" : points;
				pointsbox.appendChild(number);
				var wordbox = document.getElementById("words");
				var inserted = false;
				var child = wordbox.children[0];
				while (child && !inserted)
				{
					var boxpoints = parseInt(child.id);
					if (!isNaN(boxpoints) && (boxpoints - points) * direction > 0) { wordbox.insertBefore(pointsbox, child); inserted = true; }
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
		// -1 is the payload's way of saying the dictionary rules this word out; css hides its pointspan
		if (wordpoints[found].points < 0) { wordspan.className += ' unscored'; }
		if (isshort(wordpoints[found])) { wordspan.className += ' short'; }
		wordspan.className += ' ' + rarityclass(wordpoints[found]);
		if (!daily)
		{
			var span = pointspan(wordpoints[found].points);
			span.id = word+'points';
			wordspan.appendChild(span);
		}
	}
	if (mystatus != 3) {
		if (!daily) { wordspan.className += ' deletable'; }
		var box = reactionbox(word, !daily);
		if (box)
		{
			wordspan.appendChild(box);
			wordspan.onclick = function(e) { togglepopout(e, box); };
		}
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
		playerboxhead.textContent = allplayersdump[i].player || txt.guest;
		playerbox.appendChild(playerboxhead);
	}
}
// a word outside the solution is one no dictionary entry vouches for, as rare as it gets
// on a daily, a word that would score if it were long enough
function isshort(row)
{
	return daily && row.points < 0 && row.rarity != null && row.rarity < 4;
}
function rarityclass(row)
{
	return 'rarity' + (row.rarity == null ? 5 : row.rarity);
}
function dedupe(rows)
{
	var byword = Object.create(null);
	var list = [];
	for (var i=0; i<rows.length; i++)
	{
		if (!byword[rows[i].word])
		{
			byword[rows[i].word] = {word: rows[i].word, user_id: rows[i].user_id, points: rows[i].points, rarity: rows[i].rarity, finders: []};
			list.push(byword[rows[i].word]);
		}
		// the own row wins the merged entry's user_id, so a shared word still counts as this player's
		if (rows[i].user_id == selfid) { byword[rows[i].word].user_id = selfid; }
		byword[rows[i].word].finders.push(rows[i].player || txt.guest);
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
				number.innerHTML = (points < 0) ? "–" : points;
				pointsbox.appendChild(number);
				var wordbox = document.getElementById("words");
				var inserted = false;
				var child = (points === "∞") ? null : wordbox.children[0];
				while (child && !inserted)
				{
					var boxpoints = parseInt(child.id);
					if (!isNaN(boxpoints) && (boxpoints - points) * direction > 0) { wordbox.insertBefore(pointsbox, child); inserted = true; }
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
	wordspan.className += ' ' + rarityclass(word);
	if (word["user_id"] == -1)
	{
		wordspan.className += ' unfound';
	}
	else
	{
		var finders = uniquewords[lookFor(word["word"], uniquewords, 'word')].finders;
		// a daily is played alone, so every word would be unique
		if (finders.length == 1 && !daily) { wordspan.className += ' unique'; }
		if (words.indexOf(word["word"]) == -1) { wordspan.className += ' others'; }
		else if (finders.length > 1) { wordspan.className += ' shared'; }
		if (word["points"] < 0) { wordspan.className += ' unscored'; }
		if (isshort(word)) { wordspan.className += ' short'; }
		if (!daily) { wordspan.appendChild(pointspan(word["points"])); }
	}
	var box = reactionbox(word["word"], word["user_id"] == selfid && !daily);
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
	// index.js has toggled a collapsible before this runs, so one without .open was just closed
	if (!touch || e.target.matches('.collapsible:not(.open)')) { document.getElementById("input").focus(); }
});
// a tap never reaches the listener above, since the word stops the click to keep its own panel open
function togglepopout(e, box)
{
	e.stopPropagation();
	closepopouts(box);
	closelookup();
	if (!box.classList.toggle('open') && !touch) { document.getElementById("input").focus(); }
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
	// reacting and looking up are only offered once this player has personally finished, reacting not on a daily, played alone
	var finished = isplayer && mystatus == 3;
	var canreact = finished && !daily;
	if (existing.length == 0 && !finished && !deletable) { return null; }
	var box = element('span', 'popout');
	var mine = null;
	var panel = element('span', 'popoutpanel');
	if (playerlist.length > 2 && mystatus == 3) {
		var entry = uniquewords[lookFor(word, uniquewords, 'word')];
		panel.appendChild(element('span', 'reactionline', entry.user_id == -1 ? txt.notfound : fmt(txt.foundby, entry.finders.join(', '))));
	}
	existing.forEach(function(r) {
		var badge = element('span', 'reactionbadge', r.emoji);
		box.appendChild(badge);
		panel.appendChild(element('span', 'reactionline', r.emoji + ' ' + (r.reactor_id == -1 ? txt.dictionary : (r.display_name || txt.guest))));
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
	if (finished && solution.length && lookFor('❗', existing, 'emoji') == -1) {
		var look = element('span', '', '🔍');
		look.onclick = function(e) { e.stopPropagation(); box.classList.remove('open'); lookuphistory = []; lookup(word); };
		actions.appendChild(look);
	}
	if (deletable) {
		var del = element('span', '', '❌');
		del.onclick = function(e) { e.stopPropagation(); removeword(word); if (!touch) { document.getElementById("input").focus(); } };
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
	if (!entries.length) { body.appendChild(element('div', 'tag', txt.noentry)); }
	entries.forEach(function(entry) {
		// a game substituting umlauts can match two spellings at once, and each is an entry of its own
		if (entries.length > 1) { body.appendChild(element('h3', '', entry.word)); }
		body.appendChild(element('div', 'tag', txt['rarity'+entry.level]));
		var sounds = entry.sounds || [];
		var senses = entry.senses || [];
		// a spelling can be a form of several roots at once, and is one entry in the dictionary for each of them
		var roots = group((entry.forms || []).filter(function(form) { return form.root != word; }), function(form) { return form.root; });
		if (sounds.length) { body.appendChild(element('div', 'lookuphead', txt.pronunciation)); }
		group(sounds, function(sound) { return labels(sound).join(); }).forEach(function(g) {
			var line = element('div', 'ipaline');
			labels(g.items[0]).forEach(function(label) { line.appendChild(element('span', 'wordspan', label)); });
			line.appendChild(document.createTextNode(g.items.map(function(sound) { return sound.ipa; }).join(' · ')));
			body.appendChild(line);
		});
		if (senses.length) { body.appendChild(element('div', 'lookuphead', txt.meanings)); }
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
		if (roots.length) { body.appendChild(element('div', 'lookuphead', txt.formof)); }
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
// the same symbols the Wörterbuch reactions use: ❕ for nonstandard, ❗ for disallowed, ⎇ for a flexion
function marker(item)
{
	return (item.level == 4 ? '❕' : item.level == 5 ? '❗' : '') + (item.inflected ? '⎇' : '');
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
	if (confirm(txt.leaveconfirm))
	{
	post({action: "leavegame", game: game}, function(){ window.location = "."; });
	}
}
function finish() {
	// a daily's finished view is the page's own, with the name form for a player without one
	if (daily) { post({action: "finishgame", game: game}, function() { location.reload(); }); return; }
	mystatus=3;
	document.getElementById('finish').style.display = 'none';
	document.getElementById('inputline').style.display = 'none';
	var line = document.getElementById('lives');
	if (line) { line.remove(); }
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
function writeplayers(list)
{
	playerlist = list;
	var elapsed = Math.floor((Date.now() - playerstamp) / 60000);
	var players = document.getElementById("players");
	players.innerHTML = "";
	for (var i=0; i<list.length; i++)
	{
		var playername = list[i].player || txt.guest;
		var points = list[i].self ? ownpoints : list[i].points;
		var playerstatus = (list[i].status == 3) ? "✓ " : "";
		var activity = fmt(txt.lastaction, timeago(list[i].last_activity + elapsed));
		var playerdiv = document.createElement('div');
		playerdiv.className = 'playerdiv';
		playerdiv.appendChild(document.createTextNode(points === undefined ? playerstatus+playername : playerstatus+playername+' ('+points+')'));
		var meta = document.createElement('span');
		meta.style.fontSize = '10px';
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
			force.textContent = "⏰︎ " + txt.forcefinish;
			force.onclick = function(id, name, since) { return function() { forcefinish(id, name, since); }; }
				(list[i].user_id, playername, list[i].last_activity + elapsed);
			players.appendChild(force);
		}
	}
}
function forcefinish(id, name, since)
{
	if (confirm(fmt(txt.forcefinishconfirm, name, timeago(since))))
	{
		post({action: "forcefinish", game: game, player: id}, finishdata);
	}
}
// a daily while playing: finishing takes a word played, and the instructions go with it
function writestatus(data)
{
	document.getElementById("finish").style.display = data.players.length ? 'block' : 'none';
	var intro = document.getElementById("dailyintro");
	if (intro && data.players.length) { intro.remove(); }
	writedaily(data);
}
// a daily's headline: the first goal not yet reached and the misses left; once the first goal is reached or the game
// finished, the result as plain text to share, a mark per long length, the address carrying no result.
// Every scored word is long, and counts towards every goal whatever its rarity.
function writedaily(data)
{
	writehighscores(data);
	var me = data.players[0];
	if (!me && mystatus == 3) { return; } // a visitor who never played it sees the solution and the ranking alone
	var scored = data.words.filter(function(row) { return row.points == 0; });
	var counts = {};
	scored.forEach(function(row) { counts[row.word.length] = (counts[row.word.length] || 0) + 1; });
	var goals = [0, 0, 0]; // frequent, then common and uncommon added
	for (var length in data.lengths)
	{
		if (length < data.long) { continue; }
		var f = data.lengths[length][0], fc = f + data.lengths[length][1], fcu = fc + data.lengths[length][2];
		goals = [goals[0] + f, goals[1] + fc, goals[2] + fcu];
	}
	var goal = 0;
	while (goal < 2 && scored.length >= goals[goal]) { goal++; }
	document.getElementById("wordbox").className = "target" + goal;
	var hearts = "❤️".repeat(data.missesleft);
	document.getElementById("dailycount").textContent = fmt(txt['dailygoal'+goal], scored.length, goals[goal], data.long)
		+ (mystatus == 3 ? "" : " · " + livestext(data.missesleft));
	document.getElementById("result").classList.toggle("hide", !me || !(scored.length >= goals[0] || mystatus == 3));
	var last = [hearts, me && me.streak >= 2 ? fmt(txt.streak, me.streak) : ""].filter(Boolean).join(" · ");
	document.querySelector("#result .share").textContent = new URL("tag-"+daily.number+"-"+language, location.href).href+" #"+daily.number+" "+language.toUpperCase()
		+ "\n" + marks(data, counts) + " · " + fmt(txt.wordcount, scored.length) + (last ? "\n" + last : "");
}
// a mark per long length, from the number of scored words of that length
function marks(data, counts)
{
	var list = [];
	for (var length in data.lengths) // ascending, as integer keys always are
	{
		if (length < data.long) { continue; }
		var f = data.lengths[length][0], fc = f + data.lengths[length][1], fcu = fc + data.lengths[length][2];
		var n = counts[length] || 0;
		list.push(length + (n == 0 ? "–" : n >= fcu ? "♛" : n >= fc ? "★" : n >= f ? "✓" : "○"));
	}
	return list.join(" ");
}
// the ranking, sent with the solution: rank, name, marks and scored words
function writehighscores(data)
{
	var list = document.getElementById("highscores");
	list.textContent = "";
	(data.top || []).forEach(function(entry) {
		var row = element("div", entry.self ? "self" : "");
		var count = Object.values(entry.lengths).reduce(function(sum, n) { return sum + n; }, 0);
		row.append(element("span", "", entry.rank), element("span", "", entry.player || txt.guest),
			element("span", "", marks(data, entry.lengths)), element("span", "", String(count)));
		list.appendChild(row);
	});
	list.classList.toggle("hide", !list.childElementCount);
}
function livestext(missesleft)
{
	return missesleft ? fmt(txt.missesleft, "❤️".repeat(missesleft)) : txt.nomissesleft;
}
function finishdata(data)
{
	pollwait = 5000;
	playerstamp = Date.now();
	version = data.version;
	solution = data.solution;
	if (daily) { writedaily(data); } else { writeplayers(data.players); }
	if (!solution.length) { anagrambutton.remove(); } // no dictionary for this language, or nothing in the word
	var found = data.words.map(function(row) { return row.word; });
	// a daily's short words are no part of its goals
	var unfound = solution.filter(function(row) { return found.indexOf(row.word) == -1 && (!daily || row.word.length >= data.long); })
		.map(function(row) { return {word: row.word, rarity: row.rarity, user_id: -1, player: txt.dictionary}; });
	allplayersdump = data.players.concat(unfound.length ? [{player: txt.dictionary, user_id: -1}] : []);
	allwordsdump = data.words.concat(unfound);
	uniquewords = dedupe(allwordsdump);
	reactions = data.reactions;
	gamestatus = data.gamestatus;
	sortwords();
}
function receivedata(data)
  {
	var me = data.players.find(function(p) { return p.self; });
	if (isplayer && (!me || me.status == 3)) { location.reload(); return; }
	if (daily && data.solution.length) { location.reload(); return; } // midnight passed with the page open
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
		ownpoints += Math.max(0, data.words[i].points);
		var wordspan = document.getElementById(data.words[i].word+"word");
		wordspan.classList.toggle('unscored', data.words[i].points < 0);
		wordspan.className = wordspan.className.replace(/ rarity\d/, '') + ' ' + rarityclass(data.words[i]);
		var span = document.getElementById(data.words[i].word+'points');
		if (!span && !daily)
		{
			span = pointspan(data.words[i].points);
			span.id = data.words[i].word+'points';
			wordspan.appendChild(span);
		}
		else if (span && span.textContent != data.words[i].points)
		{
			span.textContent = data.words[i].points;
			colortransition(data.words[i].word+"word");
			changed = true;
		}
		var oldbox = wordspan.querySelector('.popout');
		var wasopen = oldbox && oldbox.classList.contains('open');
		if (oldbox) { wordspan.removeChild(oldbox); }
		var newbox = reactionbox(data.words[i].word, !daily);
		if (newbox)
		{
			if (wasopen) { newbox.classList.add('open'); }
			wordspan.appendChild(newbox);
			wordspan.onclick = function(box) { return function(e) { togglepopout(e, box); }; }(newbox);
		}
	}
	if (daily) { writestatus(data); } else { writeplayers(data.players); }
	var line = document.getElementById("lives");
	if (line) { line.textContent = livestext(data.missesleft); }
	if (changed && sortmode == 'points') { sortwords(); }
  }
