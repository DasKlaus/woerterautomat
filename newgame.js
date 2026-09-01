document.getElementById('newwordinput').onkeypress = keyhandle;
document.getElementById('newwordinput').oninput = inputhandle;

function inputhandle() {
	var inputfield = document.getElementById("newwordinput");
	var value = inputfield.value.normalize('NFC')
	var lower = value.toLowerCase();
	var accepted = '';
	for (var i=0; i<lower.length; i++) {
		accepted += (substitute && lower[i] in umlauts) ? umlauts[lower[i]] : lower[i];
	}
	if (inputfield.value != accepted) { inputfield.value = accepted; }
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
	document.getElementById("newgamesubmit").textContent = "Spiel prüfen";
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

function creategame(form) {
	var data = new URLSearchParams(new FormData(form));
	data.append("action", checked ? "creategame" : "checkgame");
	fetch("receiver.php", {method: "POST", body: data})
		.then(function(response) { return response.json(); })
		.then(function(answer) {
			if (answer.game) { window.location = "?go=game&game=" + answer.game; return; }
			say(answer.message, answer.style);
			if (answer.ok)
			{
				checked = true;
				document.getElementById("newgamesubmit").textContent = "Spiel starten";
				if (answer.solutions >= 0) {
					var mark = document.getElementById("difficulty");
					mark.textContent = difficulty(answer.solutions);
					mark.setAttribute("title", answer.solutions + " mögliche Wörter");
					mark.className = "";
				}
			}
		});
}

function keyhandle(e) {
	evt = e || event;
	var chrTyped, chrCode = 0;
	if (evt.charCode!=null)     chrCode = evt.charCode;
	else if (evt.which!=null)   chrCode = evt.which;
	else if (evt.keyCode!=null) chrCode = evt.keyCode;
	if (chrCode==0) chrTyped = ' ';
		else chrTyped = String.fromCharCode(chrCode).toLowerCase();
	if (chrTyped.toUpperCase() != chrTyped.toLowerCase()) {
		evt.preventDefault();
		document.getElementById("newwordinput").value += (chrTyped in umlauts && substitute == true) ? umlauts[chrTyped] : chrTyped;
		invalidate(); // writing .value fires no input event, so oninput never sees a typed letter
	}
	return true;
}