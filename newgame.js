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
	button.textContent = "Spiel prüfen";
	document.getElementById("difficulty").className = "hide";
	say("", "hide");
}

function difficulty(solutions) {
	if (solutions < 50) { return "·"; }
	if (solutions < 200) { return "⁎"; }
	if (solutions < 500) { return "⁑"; }
	if (solutions < 1000) { return "⁂"; }
	return "✳";
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
	var release = function() { button.disabled = false; button.textContent = checked ? "Spiel starten" : "Spiel prüfen"; };
	var data = new URLSearchParams(new FormData(form));
	data.append("action", checked ? "creategame" : "checkgame");
	button.textContent = checked ? "wird gestartet …" : "wird geprüft …";
	button.disabled = true;
	fetch("receiver.php", {method: "POST", body: data})
		.then(function(response) { return response.json(); })
		.then(function(answer) {
			if (!button.disabled) { return; }
			if (answer.game) { window.location = "?go=game&game=" + answer.game; return; }
			say(answer.message, answer.style);
			if (answer.ok)
			{
				checked = true;
				if (answer.solutions >= 0) {
					var mark = document.getElementById("difficulty");
					mark.textContent = difficulty(answer.solutions);
					mark.setAttribute("title", answer.solutions + " mögliche Wörter");
					mark.className = "";
				}
			}
			release();
		})
		.catch(function() { say("Der Server hat nicht geantwortet.", "warning"); release(); });
}