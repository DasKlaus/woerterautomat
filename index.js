document.onclick = function(event)
  {
	if (event.target.classList.contains('collapsible')) { event.target.classList.toggle('open'); }
  };

var umlauts = {"ä":"ae", "ö":"oe", "ü":"ue", "ß":"ss", "å":"aa", "æ":"ae", "ø":"oe", "ё":"e"};
var substitute = false;

// sort letters

var originalword = "woerterautomat";
var mystatus = 2;

function select(button)
  {
	var buttons = button.closest('#lettersortbuttons, #sortbuttons, #orderbuttons').getElementsByTagName('button');
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

// find anagrams

const target = "woerterautomat";
const raw_words = ["o","wm","er","am","um","au","wo","ur","tau","tor","tut","rat","arm","rau","tot","out","tat","mut","art","war","oma","rum","met","rom","rar","wem","tue","rot","wut","ute","ort","wer","turm","waer","rare","atem","wate","wert","warm","term","etat","otto","euer","trat","arme","raum","etwa","wort","maat","trau","rote","matt","atom","auto","amte","wurm","taue","amor","euro","teer","reue","atme","tore","taet","raue","raet","aera","wort","tour","tret","moor","wart","more","taut","tute","eure","team","tuer","tote","rate","orte","ware","treu","rute","watt","meer","raete","turme","worte","motto","eurem","matte","tutor","atome","rarem","amour","mauer","motor","tuete","tumor","tuere","teuer","watte","meter","armee","totem","raute","raume","aroma","autor","water","rette","aorta","ortet","waere","ratet","traut","roete","warum","werte","meute","amtet","mater","rotem","roter","treue","wette","rotor","rotte","toter","watet","route","euter","taute","moewe","worum","taete","armer","armut","tatar","tutet","tatet","moore","wurmt","tower","traue","trott","torte","trete","otter","warte","warme","teert","murrt","wurme","motte","teure","ratte","atmet","toete","traum","toetet","tautet","meteor","wartet","raeumt","tortur","maurer","waermt","wertet","retter","tatort","rottet","reuter","erraet","wurmte","teurer","roeter","raeume","tomate","atomar","trauma","tratet","tratte","wermut","atmete","trauer","routet","retour","wettet","teurem","trotte","errate","roemer","traute","wuetet","aermer","artete","roetet","treter","mauert","wuerme","ratete","treuer","mutter","warmer","murrte","taeter","erwart","tutete","tuerme","wetter","aemter","ortete","tumore","rettet","waerme","matura","matter","tatare","wortarm","tatorte","wettert","trautet","muetter","wortart","tuermte","mauerte","rattere","torwart","trauter","woerter","rottete","waermte","martert","waerter","watetet","waermer","traeume","meutert","traeumt","ermatte","roterem","amateur","retorte","wartete","wurmtet","automat","armatur","rattert","trauert","torraum","erwarte","trauere","meteror","raeumte","wuermer","wermute","mattere","wartetet","waermtet","ratterte","amaretto","umwertet","erwartet","traeumte","armeerat","amateure","raeumtet","trauerte","automate","traeumer","ermattet","torwarte","matterer","erwaermt","torraeume","wortarmut","warteraum","tauwetter","traeumtet","ertraeumt","trauerte"];

const letters = w => [...w.normalize('NFC').toLowerCase()];

const count = w => letters(w).reduce((c, ch) => c.set(ch, (c.get(ch) || 0) + 1), new Map());

const fits = (c, rest) => { for (const [ch, n] of c) if (n > (rest.get(ch) ?? 0)) return false; return true; };

const spill = rest => [...rest].flatMap(([ch, n]) => Array(n).fill(ch));

const take = (c, rest, sign = 1) => c.forEach((n, ch) => rest.set(ch, rest.get(ch) - sign * n));

const prepare = words => [...new Set(words)].map(w => ({ w, c: count(w), len: letters(w).length }));

const graze = (target, dict) => {
  const rest = count(target);
  const pool = dict.filter(({ c }) => fits(c, rest));

  const path = [];
  for (;;) {
    const fitting = pool.filter(({ c }) => fits(c, rest));
    if (!fitting.length) break;
    const { w, c } = fitting[Math.random() * fitting.length | 0];
    take(c, rest);
    path.push(w);
  }

  const left = spill(rest);
  return path.join(" ")+left.join(''); // { left: left.length, words: path, rest: left.join('') };
};

const anagram = (target, dict, ms = 50) => {
  const rest = count(target);
  const pool = dict.filter(({ c }) => fits(c, rest));
  for (let i = pool.length - 1; i > 0; i--) {
    const j = Math.random() * (i + 1) | 0;
    [pool[i], pool[j]] = [pool[j], pool[i]];
  }

  const by = new Map([...rest.keys()].map(ch => [ch, []]));
  for (const word of pool) for (const ch of word.c.keys()) by.get(ch).push(word);

  const path = [];
  const dead = new Set();
  const deadline = performance.now() + ms;

  const search = left => {
    if (!left) return true;

    const key = [...rest.values()].join();
    if (dead.has(key)) return false;
    dead.add(key);

    let scarce;
    for (const [ch, n] of rest) if (n && (!scarce || by.get(ch).length < scarce.length)) scarce = by.get(ch);

    for (const { w, c, len } of scarce) {
      if (!fits(c, rest)) continue;
      take(c, rest);
      path.push(w);
      if (search(left - len)) return true;
      path.pop();
      take(c, rest, -1);
      if (performance.now() > deadline) return false;
    }
    return false;
  };

  return search(letters(target).length)
    ? path.join(" ") // { left: 0, words: path, rest: '' }
    : graze(target, pool);
};

prepared_words = prepare(raw_words);