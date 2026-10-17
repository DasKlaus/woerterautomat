<?php
header('Content-Type: text/plain');
while (ob_get_level()) { ob_end_flush(); }
ob_implicit_flush(true);
require_once("config.php");
require_once("../identity.php");
$catalogue = require "../achievements.php";

// Sets every player's level of each of this project's achievements from history, as receiver.php grants them live.
// Each query yields events as user_id, n and time, in time order: an event reaches the levels whose threshold n meets (a share's n is a percentage),
// a player holds the highest any of their events reaches, granted when it was first reached. A row this
// changes is marked seen; one it leaves alone keeps its seen.

// a found word in its game's solution, not nonstandard or disallowed
const VALID = "(select w.game_id, w.user_id, w.word, w.created_at, s.rarity, g.language, g.source_word, g.daily
	from word w join game g on g.id = w.game_id join solution s on s.game_id = w.game_id and s.word = w.word
	where s.rarity < 4 and w.user_id > 0)";

$events = [
	'wa_anagram' => ["select user_id, char_length(word), created_at from ".VALID." v
		where char_length(word) = char_length(source_word)", []],
	'wa_long' => ["select user_id, char_length(word), created_at from ".VALID." v
		where locate(word, source_word collate utf8mb4_bin) = 0", []],
	// words of the same second count as one second apart, as now() - interval 60 second does live; legacy imports
	// carry their game's creation time, so only words from 2026 on count
	'wa_burst' => ["select v.user_id, count(*), v.created_at from ".VALID." v
		join ".VALID." u on u.game_id = v.game_id and u.user_id = v.user_id and u.created_at between v.created_at - interval 59 second and v.created_at
		where v.created_at >= '2026-01-01' and u.created_at >= '2026-01-01' group by v.game_id, v.user_id, v.word, v.created_at", []],
	// at the last finish, on today's points
	'wa_klaus' => ["select p.user_id, 1, (select max(a.activity) from player a where a.game_id = p.game_id)
		from player p join player k on k.game_id = p.game_id and k.user_id = 1 join game g on g.id = p.game_id
		where g.status = 3 and g.daily is null and p.user_id not in (0, 1) and p.points > k.points", []],
	'wa_streak' => ["select p.user_id, p.streak, p.joined_at from player p join game g on g.id = p.game_id
		where g.daily is not null and p.user_id > 0", []],
	// as dailystate() and game.js count a daily: L is the tenth-longest frequent word's length, a goal the number of
	// the solution's frequent, then common and uncommon words of at least L letters, and every scored word counts
	// towards each
	'wa_daily' => ["with lengths as (select g.id, coalesce((select char_length(s.word) from solution s where s.game_id = g.id and s.rarity = 0
				order by char_length(s.word) desc limit 1 offset 9), 3) as l from game g where g.daily is not null),
			goals as (select lengths.id, lengths.l, sum(s.rarity = 0) as g0, sum(s.rarity < 2) as g1, sum(s.rarity < 3) as g2
				from lengths join solution s on s.game_id = lengths.id and char_length(s.word) >= lengths.l group by lengths.id, lengths.l)
		select user_id, (n >= g0) + (n >= g1) + (n >= g2), created_at from (select v.user_id, v.created_at, g0, g1, g2,
				row_number() over (partition by v.game_id, v.user_id order by v.created_at, v.word) as n
			from goals join ".VALID." v on v.game_id = goals.id and char_length(v.word) >= goals.l) x", []],
];
foreach ([0, 1, 2] as $tier)
{
	// non-daily games only, a game's tier words counted as found among at least 20; the uncommon tier divides by its
	// uncommon words only
	$events['wa_share'.$tier] = ["select v.user_id, row_number() over (partition by v.game_id, v.user_id order by v.created_at, v.word) * 100 / t.total, v.created_at
		from ".VALID." v join (select game_id, sum(rarity < 3) as total from solution where rarity < 4 and least(rarity, 2) = ?
			group by game_id having total >= 20) t on t.game_id = v.game_id
		where v.daily is null and least(v.rarity, 2) = ?", [$tier, $tier]];
	// distinct words, each counted when first found
	foreach (['de', 'en'] as $language)
		$events['wa_words'.$tier.'_'.$language] = ["select user_id, row_number() over (partition by user_id order by min(created_at), word), min(created_at)
			from ".VALID." v where language = ? and least(rarity, 2) = ? group by user_id, word", [$language, $tier]];
}

identityConnect();
foreach ($catalogue['woerterautomat']['badges'] as $key => $entry)
{
	[$sql, $params] = $events[$key];
	$held = [];
	foreach ($mysql->execute_query($sql." order by 3", $params)->fetch_all() as [$user, $n, $at])
	{
		$level = 0;
		foreach ($entry['levels'] as $threshold)
			if ($n >= (int)$threshold) { $level++; }
		if ($level > ($held[$user][0] ?? 0)) { $held[$user] = [$level, $at]; }
	}
	$current = array_column($identity->execute_query("select user_id, level from achievement where badge = ?", [$key])->fetch_all(), 1, 0);
	$changed = 0;
	foreach ($current as $user => $level)
		if (!isset($held[$user]))
		{
			$identity->execute_query("delete from achievement where user_id = ? and badge = ?", [$user, $key]);
			$changed++;
		}
	foreach ($held as $user => [$level, $at])
		if (($current[$user] ?? 0) != $level)
		{
			$identity->execute_query("insert into achievement (user_id, badge, level, granted_at, seen) values (?, ?, ?, ?, 1)
				on duplicate key update level = values(level), granted_at = values(granted_at), seen = 1", [$user, $key, $level, $at]);
			$changed++;
		}
	echo $key.": ".count($held)." players, ".$changed." changed".PHP_EOL;
}

echo "Done!".PHP_EOL;
?>
