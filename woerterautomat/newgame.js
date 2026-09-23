document.getElementById('newwordinput').oninput = function(e) { if (!e.isComposing) { inputhandle(); } };
document.getElementById('newwordinput').oncompositionend = inputhandle;

function inputhandle() {
	var inputfield = document.getElementById("newwordinput");
	var value = inputfield.value.normalize('NFC')
	var lower = value.toLowerCase();
	var accepted = '';
	for (var i=0; i<lower.length; i++) {
		accepted += (substitute && lower[i] in umlauts) ? umlauts[lower[i]] : lower[i];
	}
	if (inputfield.value != accepted) {
		var pos = inputfield.selectionEnd + accepted.length - inputfield.value.length;
		inputfield.value = accepted;
		inputfield.setSelectionRange(pos, pos);
	}
	invalidate();
}

var checked = false;

function toggleUmlauts(selector) {
	substitute = selector.checked;
	inputhandle();
}

// a unit says nothing without a number and a number nothing without a unit, so the two are set and
// cleared together; assigning to the radio group as a whole is what unchecks all of it
function timeunit(input) {
	if (input.value == "") { input.form.unit.value = ""; }
	else if (input.form.unit.value == "") { input.form.unit.value = "1440"; }
}

// only the four fields the solution depends on invalidate a check;
function invalidate() {
	checked = false;
	var button = document.getElementById("newgamesubmit");
	button.disabled = false;
	button.textContent = txt.checkgame;
	document.getElementById("difficulty").className = "hide";
	say("", "hide");
}

function say(text, style) {
	var line = document.getElementById("newgamemessage");
	line.textContent = text;
	line.className = style;
}

// the dictionary query can take seconds, so the button waits, and its own disabled state doubles as
// the marker for that wait: an invalidation clears it, and a reply arriving after that is stale
function creategame(form) {
	var button = document.getElementById("newgamesubmit");
	var release = function() { button.disabled = false; button.textContent = checked ? txt.startgame : txt.checkgame; };
	var data = new URLSearchParams(new FormData(form));
	data.append("action", checked ? "creategame" : "checkgame");
	button.textContent = checked ? txt.starting : txt.checking;
	button.disabled = true;
	fetch("receiver.php", {method: "POST", body: data})
		.then(function(response) { return response.json(); })
		.then(function(answer) {
			if (!button.disabled) { return; }
			if (answer.url) { window.location = answer.url; return; }
			var message = fmt(txt[answer.message], answer.solutions);
			if (answer.detail) { message += " " + (txt[answer.detail] || answer.detail); }
			say(message, answer.style);
			if (answer.ok)
			{
				checked = true;
				if (answer.solutions >= 0) {
					var mark = document.getElementById("difficulty");
					mark.textContent = difficulty(answer.solutions);
					mark.setAttribute("title", fmt(txt.possiblewords, answer.solutions));
					mark.className = "";
				}
			}
			release();
		})
		.catch(function() { say(txt.noanswer, "warning"); release(); });
}
