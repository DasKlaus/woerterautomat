<?php
$host = "localhost";
$dbname = "CHANGEME";
$dbuser = "CHANGEME";
$dbpass = "CHANGEME";

$dictionary = new mysqli($host, $dbuser, $dbpass, $dbname);
$dictionary->set_charset('utf8mb4');

// the language is a table name and cannot be bound: matching it against information_schema is what
// whitelists it, and doubles as the check for a language that has not been imported yet
function dictionaryhas($dictionary, $language)
{
	return (bool)$dictionary->execute_query("select 1 from information_schema.tables
		where table_schema = database() and table_name = ?", [$language])->fetch_column();
}

function generatesolution($mysql, $dictionary, $game, $language, $umlauts, $flexion, $sourceword)
{
	if (!dictionaryhas($dictionary, $language)) { return; }
	$mode = $umlauts ? 'clean' : 'word';
	$letters = array_count_values(mb_str_split($sourceword));
	$wrongletters = "`$mode`";
	$caps = [];
	$capparams = [];
	foreach ($letters as $letter => $count)
	{
		$wrongletters = "replace($wrongletters, ?, '')";
		$caps[] = "char_length(`$mode`) - char_length(replace(`$mode`, ?, '')) <= ?";
		$capparams[] = $letter;
		$capparams[] = $count;
	}
	$conditions = ["inflected <= ?", "char_length(`$mode`) between 3 and ?", "`$mode` <> ?", "char_length($wrongletters) = 0", ...$caps];
	$params = [$flexion, mb_strlen($sourceword), $sourceword, ...array_keys($letters), ...$capparams];
	$words = array_column($dictionary->execute_query("select distinct `$mode` as word from `$language`
		where ".implode(" and ", $conditions), $params)->fetch_all(MYSQLI_ASSOC), 'word');
	foreach (array_chunk($words, 500) as $chunk)
	{
		$values = [];
		foreach ($chunk as $word) { $values[] = $game; $values[] = $word; }
		$mysql->execute_query("insert into solution (game_id, word) values ".implode(",", array_fill(0, count($chunk), "(?,?)")), $values);
	}
}

// only asked for a word the solution does not hold: one query per relaxation the game withholds,
// flexion first because it takes precedence where both would let the word through. `word <> ?` is
// what makes the second query mean "only reachable through substitution" rather than "known at all"
function wordcheck($dictionary, $language, $word, $umlauts, $flexion)
{
	if (!dictionaryhas($dictionary, $language)) { return ""; }
	if (!$flexion and $dictionary->execute_query("select 1 from `$language` where (word = ? or clean = ?) and inflected = 1",
		[$word, $word])->fetch_column()) { return "⎇"; }
	if (!$umlauts and $dictionary->execute_query("select 1 from `$language` where clean = ? and word <> ? and inflected <= ?",
		[$word, $word, $flexion])->fetch_column()) { return "Ä→AE"; }
	return "❗";
}
