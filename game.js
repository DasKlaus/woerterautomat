// show how many words others have in those categories/letters


// Begrenzung Spieler, Einladung Spieler
// Wörterbuch: Wort, Sprache, Deklination/Eigenname? Stimmen pro, Stimmen contra
// Auswertung: Tabelle mit Haken für Deklination/Eigenname und ja/nein
// Auswertung: sortieren nach Spielern, Punkten, Häufigkeit, Länge, Alphabet
// Auswertung: langsam anzeigen
// Auswertung: weiter mögliche Wörter aus dem Wörterbuch anzeigen

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
			jQuery.get( "receiver.php?action=newword&game="+game+"&player="+player+"&word="+word+"&gameword="+originalword);
			words[words.length] = word;
			sortword(word);
		}
		else
		{
			colortransition("#"+word+"word");
		}
	}
  }

function removeword(wordspan)
  {
	var word = wordspan.id.substr(0, wordspan.id.indexOf("word"));
	jQuery.get( "receiver.php?action=removeword&game="+game+"&player="+player+"&word="+word );
	words.splice(words.indexOf(word), 1);
	wordspan.parentNode.removeChild(wordspan);
  }

function sortword(word)
  {
	var sortcontainer = document.getElementById("words");
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
			// look in pointdump for points, otherwise treat as 0
			var points = 0;
			if (domGet(word+"points", pointdump)) { points = domGet(word+"points", pointdump).innerHTML; }
			
			if (!document.getElementById(points+"words")) 
			{
				var pointsbox = document.createElement('div');
				pointsbox.id = points+"words";
				var number = document.createElement('div');
				number.className = 'startnumber';
				number.style.display = 'none';
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
	if (status != 2) {$( wordspan ).attr( "onClick", "removeword(this);" );}
	sortcontainer.appendChild(wordspan);
	sortcontainer.innerHTML += " ";
  }

function sortallwordsdump(a,b)
{
	return ((a["word"] < b["word"]) ? -1 : ((a["word"] > b["word"]) ? 1 : 0));
}

function sortwords()
  {
	var tosort = words;
	if (status == 2) {tosort = allwordsdump;}
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
			if (status == 2) {tosort.sort(sortallwordsdump);}
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
		if (status == 2) {sortfinishedword(tosort[i]);}
		else {sortword(tosort[i]);}
	}
  }

function makeplayerboxes() 
{
	var players = document.getElementById("players").children;
	for (var i=0; i<players.length; i++)
	{
		var playername = players[i].id.substr(0, players[i].id.length-6);
		var playerbox = document.createElement('div');
		playerbox.id = playername+"words";
		document.getElementById("words").appendChild(playerbox);
		var playerboxhead = document.createElement('span');
		playerboxhead.className = 'startletter';
		playerboxhead.innerHTML = playername;
		playerbox.appendChild(playerboxhead);
	}
}
  
function sortallwords(allwords, allplayers)
{
	sortwords();
	// was, wenn ein Wort entfernt wurde?
	// getSortmode
	// put words as usual in words, not wordbox
	
	// assign colors to players
	
	/* switch (sortmode)
	{
		case 'standard':
			makeletterboxes(originalword);
		break;
		case 'chrono':
		break;
		case 'alpha':
			var sortedword = sortstring(originalword);
			makeletterboxes(sortedword);
			tosort.sort();
		break;
		case 'length':
		break;
		case 'points':
		break;
		case 'player': 
		break;
	}*/
	
	
	// 
			// if (!document.getElementById(word+"words")
			// sortcontainer = 
	
	  /*allwords.sort((function(index){
		    return function(a, b){
			return (a[index] === b[index] ? 0 : (a[index] < b[index] ? -1 : 1));
		    };
		})("'word'"));*/
	/*var wordbox = document.getElementById("wordbox")
	wordbox.innerHTML = '';
	var wordtable = document.createElement('table');
	wordtable.id = 'wordtable';
	var head = document.createElement('tr');
	head.className = 'mainrow';
	for (var i=0; i<allplayers.length; i++)
	{
		var playercell = document.createElement('td');
		playercell.innerHTML = allplayers[i].player;
		head.appendChild(playercell);
	}
	var pointcell = document.createElement('td');
	pointcell.innerHTML = 'Punkte';
	head.appendChild(pointcell);
	wordtable.appendChild(head);
	wordbox.appendChild(wordtable);*/
	
	/*for (var i=0; i<allwords.length; i++)
	{
		sortword(wordtable, allwords, i);
	}*/
	
	//var i = allwords.length;
	/*
	(function theLoop (i) {
	  setTimeout(function () {
	    sortfinishedword(wordtable, allwords, allplayers, i);
	    if (--i) {          // If i > 0, keep going
	      theLoop(i);       // Call the loop again, and pass it the current value of i
	    } else {addpointrow(wordtable, allplayers);}
	  }, 0);
	})(allwords.length-1);*/
}

function addpointrow(wordtable, allplayers)
{
	var pointrow = document.createElement('tr');
	pointrow.className = 'mainrow';
	for (var i=0; i<allplayers.length; i++)
	{
		var cell = document.createElement('td');
		cell.innerHTML = allplayers[i]['points'];
		pointrow.appendChild(cell);
	}
	wordtable.appendChild(pointrow);
}

function sortfinishedword(word)
{
	/*if (!document.getElementById(allwords[i].word+"row"))
	{
		var wordrow = document.createElement('tr');
		wordrow.id = allwords[i].word+"row";
		for (var j=0; j<allplayers.length; j++)
		{
			var cell = document.createElement('td');
			wordrow.appendChild(cell);
		}
		var pointcell = document.createElement('td');
		pointcell.innerHTML = allwords[i].points;
		wordrow.appendChild(pointcell);
		wordtable.appendChild(wordrow);
	}
	var wordrow = document.getElementById(allwords[i].word+"row");
	var playerindex = lookFor(allwords[i].player, allplayers, 'player');
	var cell = wordrow.children[playerindex];
	if(!cell) { wordbox.innerHTML += "<br>Fehler bei Wort "+allwords[i].word+" von "+allwords[i].player }
	cell.innerHTML = allwords[i].word;*/
	
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
			// look in pointdump for points, otherwise treat as 0
			var points = 0;
			points = word["points"];
			
			if (!document.getElementById(points+"words")) 
			{
				var pointsbox = document.createElement('div');
				pointsbox.id = points+"words";
				var number = document.createElement('div');
				number.className = 'startnumber';
				number.style.display = 'none';
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
	wordspan.id = word["word"]+'word';
	wordspan.innerHTML = word["word"];
	if (status != 2) {$( wordspan ).attr( "onClick", "removeword(this);" );}
	sortcontainer.appendChild(wordspan);
	sortcontainer.innerHTML += " ";
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
	if (chrCode == 8) { backspace(); }
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

 function colortransition( id , ini){
    if ( $( id ).hasClass("fadeIn") ){
     $( id ).removeClass( "fadeIn");
     $( id ).addClass( "fadeOut" );
    }else{
     $( id ).removeClass( "fadeOut");
     $( id ).addClass( "fadeIn" );
    }
    if (typeof(ini) === 'undefined'){
     setTimeout(function() {colortransition( id , 1);},250);
    }
   }

function leave() {
	if (confirm("Wenn du dieses Spiel verlässt, gehen alle deine gefundenen Wörter verloren. Fortfahren?"))
	{
	var playersnumber = document.getElementById("players").children.length;
	jQuery.get( "receiver.php?action=leavegame&game="+game+"&player="+player+"&players="+playersnumber);
	setTimeout(function(){ window.location = "http://antiscrabble.de/woerterautomat/"; }, 100);
	}
}

function finish() {
	jQuery.get( "receiver.php?action=finishgame&game="+game+"&player="+player+"");
	status=2;
	clearInterval(gamedata);
	document.getElementById('finish').style.display = 'none';
	document.getElementById('inputline').style.display = 'none';
	document.getElementById('lettersortbuttons').style.display = 'none';
	jQuery.get( "receiver.php?action=finishrequest&game="+game+"&player="+player, finishdata );
	var gamedata = setInterval(finisher, 3000);
}

function finisher() {
	if (gamestatus!=2)
	{
		jQuery.get( "receiver.php?action=finishrequest&game="+game+"&player="+player, finishdata );
		//var gamedata = setInterval(function(){jQuery.get( "receiver.php?action=finishrequest&game="+game+"&player="+player, finishdata );}, 5000);
	}
}

function finishdata(json) 
{
	var data = $.parseJSON(json);
	var players = document.getElementById("players");
	players.innerHTML = "";
	for (var i=0; i<data.players.length; i++)
	{
		var playerdiv = document.createElement('div');
		playerdiv.className = 'playerdiv';
		playerdiv.id = data.players[i].player+'player';
		var status = "aktiv";
		if (data.players[i].status == 2) { status = "abgeschlossen"; }
		var activity = "online";
		if (data.players[i].last_activity > 0)
		{
			var activity = "offline seit ";
			if (data.players[i].last_activity > 3600) { activity += Math.round(data.players[i].last_activity/3600)+" Tagen"; }
			else if (data.players[i].last_activity > 60) { activity += Math.round(data.players[i].last_activity/60)+" Stunden"; }
			else activity += data.players[i].last_activity + " Minuten";
		}
		var playername = data.players[i].player;
		playername = (playername.substr(0, 4)=="Gast")?"Gast":playername;
		playerdiv.innerHTML = playername+' ('+data.players[i].points+')<span style="font-size: 10px;"> - '+status+'<br>'+activity+'</span>';
		players.appendChild(playerdiv);
	}
	allwordsdump = data.words;
	gamestatus = data.gamestatus;
	sortallwords(data.words, data.players);
}

function receivedata(json) 
  {
	console.log("data received");
	var data = $.parseJSON(json);
	var players = document.getElementById("players");
	players.innerHTML = "";
	for (var i=0; i<data.players.length; i++)
	{
		var playerdiv = document.createElement('div');
		playerdiv.className = 'playerdiv';
		playerdiv.id = data.players[i].player+'player';
		var status = "aktiv";
		if (data.players[i].status == 2) { status = "abgeschlossen"; }
		var activity = "online";
		if (data.players[i].last_activity > 0)
		{
			var activity = "offline seit ";
			if (data.players[i].last_activity > 3600) { activity += Math.round(data.players[i].last_activity/3600)+" Tagen"; }
			else if (data.players[i].last_activity > 60) { activity += Math.round(data.players[i].last_activity/60)+" Stunden"; }
			else activity += data.players[i].last_activity + " Minuten";
		}
		var playername = data.players[i].player;
		playername = (playername.substr(0, 4)=="Gast")?"Gast":playername;
		playerdiv.innerHTML = playername+'<span style="font-size: 10px;"> - '+status+'<br>'+activity+'</span>';
		players.appendChild(playerdiv);
	}
	ownplayerdiv = document.getElementById(player+"player");
	ownplayerdiv.innerHTML = player+' ('+data.ownpoints.points+')<span style="font-size: 10px;"> - '+status+'<br>'+activity+'</span>';
	for (var i=0; i<data.words.length; i++)
	{
		var pointspan = document.getElementById(data.words[i].word+'points');
		if (!pointspan || pointspan.innerHTML != data.words[i].points)
		{
			// resort word if sortmode points
			if (sortmode == 'points')
			{
				// remove node and put word (and points) in pointdump
				pointspan = document.createElement('span');
				pointspan.className = 'pointspan';
				pointspan.id = data.words[i].word+'points';
				document.getElementById(data.words[i].word+"word").appendChild(pointspan);
				pointspan.innerHTML = data.words[i].points;
				pointdump.appendChild(document.getElementById(data.words[i].word+'word').parentNode.removeChild(document.getElementById(data.words[i].word+'word')));
				// sort word
				sortword(data.words[i].word);
				// get pointspan again
				pointspan = false;
			}
			if (!pointspan) 
			{
				pointspan = document.createElement('span');
				pointspan.className = 'pointspan';
				pointspan.id = data.words[i].word+'points';
				document.getElementById(data.words[i].word+"word").appendChild(pointspan);
			}
			else
			{
				if (pointspan.innerHTML != data.words[i].points)
				{
					colortransition("#"+data.words[i].word+"word");
				}
			}
		}
		pointspan.innerHTML = data.words[i].points;
	}
  }
  
  
  // thank you, stackoverflow
  function domGet( id , rootNode ) {
    if ( !id ) return null;

    if ( rootNode === undefined ) {

        // rel to doc base
        var o = document.getElementById( id );
        return o;

    } else {

        // rel to current node
        var nodes = [];
        nodes.push(rootNode);
        while ( nodes && nodes.length > 0 ) {
            var children = [];
            for ( var i = 0; i<nodes.length; i++ ) {
                var node = nodes[i];
                if ( node && node['id'] !== undefined ) {
                    if ( node.id == id ) {
                        return node; // found!
                    }
                }

                // else keep searching
                var childNodes = node.childNodes;
                if ( childNodes && childNodes.length > 0 ) {
                    for ( var j = 0 ; j < childNodes.length; j++ ) {
                        children.push( childNodes[j] );
                    }
                }
            }
            nodes = children;
        }

        // nothing found
        return null;
    }
}
