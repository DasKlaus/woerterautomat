document.getElementById('newwordinput').onkeypress = keyhandle;

function keyhandle(e) {
  evt = e || event;
  var chrTyped, chrCode = 0;
  if (evt.charCode!=null)     chrCode = evt.charCode;
  else if (evt.which!=null)   chrCode = evt.which;
  else if (evt.keyCode!=null) chrCode = evt.keyCode;
  if (chrCode==0) chrTyped = ' ';
  else chrTyped = String.fromCharCode(chrCode);
  
  if ("ABCDEFGHIJKLMNOPQRSTUVWXYZ�������".indexOf(chrTyped) != -1) 
	{
		evt.preventDefault();
		var charpressed = String.fromCharCode(e.charCode).toLowerCase();
		if ("����".indexOf(chrTyped) != -1)
		{
			charpressed = (charpressed=="�")?"ae":charpressed;
			charpressed = (charpressed=="�")?"oe":charpressed;
			charpressed = (charpressed=="�")?"ue":charpressed;
			charpressed = (charpressed=="�")?"ss":charpressed;
		}
		document.getElementById("newwordinput").value += charpressed;
	}
	
  return true;
}