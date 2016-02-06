document.getElementById('newwordinput').onkeypress = keyhandle;

function keyhandle(e) {
  evt = e || event;
  var chrTyped, chrCode = 0;
  if (evt.charCode!=null)     chrCode = evt.charCode;
  else if (evt.which!=null)   chrCode = evt.which;
  else if (evt.keyCode!=null) chrCode = evt.keyCode;
  if (chrCode==0) chrTyped = ' ';
  else chrTyped = String.fromCharCode(chrCode);
  
  if ("ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜßäöü".indexOf(chrTyped) != -1) 
	{
		evt.preventDefault();
		var charpressed = String.fromCharCode(e.charCode).toLowerCase();
		if ("äöüß".indexOf(chrTyped) != -1)
		{
			charpressed = (charpressed=="ä")?"ae":charpressed;
			charpressed = (charpressed=="ö")?"oe":charpressed;
			charpressed = (charpressed=="ü")?"ue":charpressed;
			charpressed = (charpressed=="ß")?"ss":charpressed;
		}
		document.getElementById("newwordinput").value += charpressed;
	}
	
  return true;
}