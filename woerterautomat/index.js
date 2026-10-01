document.onclick = function(event)
  {
	if (event.target.classList.contains('collapsible')) { event.target.classList.toggle('open'); }
  };

var umlauts = {"ä":"ae", "ö":"oe", "ü":"ue", "ß":"ss", "å":"aa", "æ":"ae", "ø":"oe", "ё":"e"};
var substitute = false;

// the time limit is stored as minutes and is always a count times one of these factors, so the
// largest one that divides it evenly is the unit it was entered in — unlike an elapsed span, which
// timesplit measures by magnitude instead.
function limittext(minutes)
{
	var count = minutes, symbol = "⏳︎", unit = "minute";
	if (minutes % 43200 == 0) { count = minutes/43200; symbol = "📅︎"; unit = "month"; }
	else if (minutes % 1440 == 0) { count = minutes/1440; symbol = "🗓︎"; unit = "day"; }
	else if (minutes % 60 == 0) { count = minutes/60; symbol = "🕐︎"; unit = "hour"; }
	return [count+symbol, fmt(txt.limittitle, count, plural(unit, count))];
}

// text: txt is written into the page by index.php, in the language it resolved for this visitor.
// One pass, so a value carrying a placeholder of its own is not read back as one.
function fmt(string)
{
	var args = arguments;
	return string.replace(/%(\d)/g, function(match, n) { return args[n]; });
}

function plural(key, count)
{
	return txt[key][count == 1 ? 0 : 1];
}

// an elapsed span as a count and the unit that best expresses it: a key for the word, a letter for a badge
function timesplit(minutes)
{
	if (minutes >= 525600) { return [Math.round(minutes/525600), 'year', 'y']; }
	if (minutes >= 1440) { return [Math.round(minutes/1440), 'day', 'd']; }
	if (minutes >= 60) { return [Math.round(minutes/60), 'hour', 'h']; }
	return [minutes, 'minute', 'm'];
}

function timeago(minutes)
{
	var split = timesplit(minutes);
	return split[0] < 1 ? txt.justnow : fmt(txt.ago, split[0], plural(split[1], split[0]));
}

function difficulty(solutions)
{
	if (solutions < 25) { return "·"; }
	if (solutions < 50) { return "⁎"; }
	if (solutions < 100) { return "⁑"; }
	if (solutions < 250) { return "⁂"; }
	return "✳";
}

var statuskeys = ["statusnew", "statusactive", "statusfull", "statusfinished"];

function badge(target, text, title)
{
	var span = document.createElement('span');
	span.textContent = text;
	span.setAttribute('title', title);
	target.appendChild(span);
}

// one game's settings as symbols, for the game view and for a card in the game list: whatever the
// row it is handed carries, in one order. A game being viewed knows nothing of its own difficulty,
// last activity or status, and leaves those out by not carrying them.
function settingsbadges(target, g)
{
	target.innerHTML = '';
	if (g.private) { badge(target, "⚿", txt.setprivate); }
	if (g.maxplayers > 0) { badge(target, g.maxplayers+"☺", fmt(txt.maxplayerstitle, g.maxplayers)); }
	if (g.timelimit > 0) { var limit = limittext(g.timelimit); badge(target, limit[0], limit[1]); }
	if (g.flexion) { badge(target, "⎇", txt.setflexion); }
	if (g.umlauts) { badge(target, "Ä→AE", txt.setumlauts); }
	if (g.dictionary) { badge(target, "🕮", txt.setdictionary); }
	if (g.solutions >= 0) { badge(target, difficulty(g.solutions), fmt(txt.possiblewords, g.solutions)); }
	if (g.activitytime !== undefined)
	{
		var split = timesplit(g.activitytime);
		badge(target, split[0]+split[2], g.activitytime > 0 ? fmt(txt.lastactive, timeago(g.activitytime)) : txt.activenow);
	}
	badge(target, g.language.toUpperCase(), fmt(txt.dictionarylanguage, txt['language'+g.language]));
	if (g.status !== undefined) { badge(target, "○◐●✓"[g.status], txt[statuskeys[g.status]]); }
}

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
	if (document.getElementById("input")) { resyncletters(); }
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
const raw_words_de = ["o","wm","er","am","um","au","wo","ur","tau","tor","tut","rat","arm","rau","tot","out","tat","mut","art","war","oma","rum","met","rom","rar","wem","tue","rot","wut","ute","ort","wer","turm","waer","rare","atem","wate","wert","warm","term","etat","otto","euer","trat","arme","raum","etwa","wort","maat","trau","rote","matt","atom","auto","amte","wurm","taue","amor","euro","teer","reue","atme","tore","taet","raue","raet","aera","wort","tour","tret","moor","wart","more","taut","tute","eure","team","tuer","tote","rate","orte","ware","treu","rute","watt","meer","raete","turme","worte","motto","eurem","matte","tutor","atome","rarem","amour","mauer","motor","tuete","tumor","tuere","teuer","watte","meter","armee","totem","raute","raume","aroma","autor","water","rette","aorta","ortet","waere","ratet","traut","roete","warum","werte","meute","amtet","mater","rotem","roter","treue","wette","rotor","rotte","toter","watet","route","euter","taute","moewe","worum","taete","armer","armut","tatar","tutet","tatet","moore","wurmt","tower","traue","trott","torte","trete","otter","warte","warme","teert","murrt","wurme","motte","teure","ratte","atmet","toete","traum","toetet","tautet","meteor","wartet","raeumt","tortur","maurer","waermt","wertet","retter","tatort","rottet","reuter","erraet","wurmte","teurer","roeter","raeume","tomate","atomar","trauma","tratet","tratte","wermut","atmete","trauer","routet","retour","wettet","teurem","trotte","errate","roemer","traute","wuetet","aermer","artete","roetet","treter","mauert","wuerme","ratete","treuer","mutter","warmer","murrte","taeter","erwart","tutete","tuerme","wetter","aemter","ortete","tumore","rettet","waerme","matura","matter","tatare","wortarm","tatorte","wettert","trautet","muetter","wortart","tuermte","mauerte","rattere","torwart","trauter","woerter","rottete","waermte","martert","waerter","watetet","waermer","traeume","meutert","traeumt","ermatte","roterem","amateur","retorte","wartete","wurmtet","automat","armatur","rattert","trauert","torraum","erwarte","trauere","meteror","raeumte","wuermer","wermute","mattere","wartetet","waermtet","ratterte","amaretto","umwertet","erwartet","traeumte","armeerat","amateure","raeumtet","trauerte","automate","traeumer","ermattet","torwarte","matterer","erwaermt","torraeume","wortarmut","warteraum","tauwetter","traeumtet","ertraeumt","trauerte"];
const raw_words_en = ["outerwear", "maturate", "amaretto", "totterer", "automate", "armature", "mutterer", "amateur", "torture", "trotter", "maturer", "rewrote", "erratum", "aerator", "retreat", "aurorae", "reroute", "tearoom", "roomer", "ureter", "wearer", "roamer", "rewarm", "marrow", "tattoo", "mature", "turret", "warmer", "aurora", "tartar", "aerate", "mutter", "rooter", "meteor", "tremor", "router", "orator", "trauma", "wetter", "reamer", "tarter", "retort", "remote", "morrow", "totter", "aortae", "tatter", "mutate", "matter", "ratter", "tomato", "errata", "rotate", "tooter", "mortar", "armour", "tauter", "orate", "otter", "treat", "tumor", "totem", "metre", "eater", "aroma", "emote", "tweet", "aware", "amour", "motto", "rawer", "muter", "wrote", "tutor", "aorta", "tater", "terra", "armor", "aurae", "tarot", "tower", "retro", "tamer", "mower", "outre", "metro", "rumor", "meter", "water", "rater", "rearm", "route", "outer", "arrow", "rotor", "truer", "rower", "wooer", "motor", "trout", "utter", "matte", "moor", "room", "root", "tame", "more", "roam", "team", "wore", "ream", "mere", "rote", "teat", "true", "tart", "tout", "urea", "rear", "wear", "wort", "teem", "moat", "wart", "worm", "tour", "rate", "mutt", "rare", "taut", "meat", "ware", "moot", "tort", "area", "mart", "tree", "tome", "tram", "tare", "taro", "trot", "tote", "tear", "toot", "trow", "mare", "mate", "mute", "meet", "were", "atom", "roar", "mote", "rout", "watt", "auto", "tore", "aura", "meow", "warm", "mete", "term", "mow", "tar", "rat", "era", "arm", "tom", "roe", "met", "woo", "ear", "tea", "owe", "maw", "rot", "tee", "our", "two", "mat", "woe", "toe", "wet", "mar", "oar", "wee", "ewe", "rut", "row", "mew", "err", "ere", "emu", "war", "raw", "rum", "eat", "rue", "awe", "oat", "tot", "tut", "too", "ram", "ore", "moo", "are", "tat", "ate", "out", "art", "tow", "to", "ow", "at", "or", "me", "ma", "aw", "we", "am", "a"]

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

prepared_words = prepare(document.documentElement.lang == 'en' ? raw_words_en : raw_words_de);
