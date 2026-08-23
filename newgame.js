document.getElementById('newwordinput').onkeypress = keyhandle;

function toggleUmlautcontrols(selector) {
	if (selector.value == "de") {
		document.getElementById("umlautcontrols").classList.remove('hide');
	}
	else {
		document.getElementById("umlautcontrols").classList.add('hide');
	}
}

function toggleUmlauts(selector) {
	substitution = selector.clicked;
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