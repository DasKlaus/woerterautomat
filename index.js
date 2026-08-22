document.onclick = function(event)
  {
	if (event.target.classList.contains('collapsible')) { event.target.classList.toggle('open'); }
  };
  
var originalword = "woerterautomat";
var mystatus = 2;

function select(button)
  {
	var buttons = button.closest('#lettersortbuttons, #sortbuttons').getElementsByTagName('button');
	for (var i=0; i<buttons.length; i++)
	  {
		buttons[i].classList.toggle('selected', buttons[i] == button);
	  }
  }

function writeletters(lettersinorder)
  {
	var letterbox = document.getElementById("letters");
	document.getElementById("wrapper").style.setProperty('--letters', lettersinorder.length);
	letterbox.innerHTML = '';
	for (i=0; i<lettersinorder.length; i++)
	  {
		letterbox.innerHTML += (mystatus == 2 ? '<span>' : '<span onClick="letterclicked(this);">')+lettersinorder.charAt(i)+'</span>';
	  }
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