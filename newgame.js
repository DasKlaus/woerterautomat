document.getElementById('newwordinput').onkeypress = keyhandle;
document.getElementById('newwordinput').oninput = invalidate;

var checked = false;

function toggleUmlauts(selector) {
	substitute = selector.checked;
	invalidate();
}

// only the four fields the solution depends on invalidate a check; private and the player limit do not
function invalidate() {
	checked = false;
	document.getElementById("newgamesubmit").textContent = "Spiel prüfen";
	say("", "hide");
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