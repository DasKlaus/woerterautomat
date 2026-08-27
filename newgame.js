document.getElementById('newwordinput').onkeypress = keyhandle;

function toggleUmlauts(selector) {
	substitute = selector.checked;
}

function creategame(form) {
	var data = new URLSearchParams(new FormData(form));
	data.append("action", "creategame");
	fetch("receiver.php", {method: "POST", body: data})
		.then(function(response) { return response.json(); })
		.then(function(answer) {
			if (answer.game) { window.location = "?go=game&game=" + answer.game; }
			else
			{
				document.getElementById("newgameerror").textContent = answer.error;
				document.getElementById("newgameerror").classList.remove("hide");
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
	}
	return true;
}