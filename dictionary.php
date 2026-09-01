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

// null when the language has no dictionary, game.solutions keeps that as -1
function solutionwords($dictionary, $language, $umlauts, $flexion, $sourceword)
{
	if (!dictionaryhas($dictionary, $language)) { return null; }
	$mode = $umlauts ? 'clean' : 'match';
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
	$conditions = ["strict = 0", "inflected <= ?", "char_length(`$mode`) between 3 and ?", "`$mode` <> ?", "char_length($wrongletters) = 0", ...$caps];
	$params = [$flexion, mb_strlen($sourceword), $sourceword, ...array_keys($letters), ...$capparams];
	return array_column($dictionary->execute_query("select distinct `$mode` as word from `$language` force index (`{$mode}_scan`)
		where ".implode(" and ", $conditions), $params)->fetch_all(MYSQLI_ASSOC), 'word');
}

function savesolution($mysql, $game, $words)
{
	foreach (array_chunk($words, 500) as $chunk)
	{
		$values = [];
		foreach ($chunk as $word) { $values[] = $game; $values[] = $word; }
		$mysql->execute_query("insert into solution (game_id, word) values ".implode(",", array_fill(0, count($chunk), "(?,?)")), $values);
	}
}

// the game's own spelling answers first; the other one is only tried when it has nothing to say, so
// a root taken from another entry's forms still resolves where the game substituted its umlauts away
function lookup($dictionary, $language, $word, $umlauts)
{
	if (!dictionaryhas($dictionary, $language)) { return []; }
	foreach ($umlauts ? ['clean', 'match'] : ['match', 'clean'] as $mode)
	{
		$rows = $dictionary->execute_query("select word, sounds, senses, forms from `$language` where `$mode` = ?", [$word])->fetch_all(MYSQLI_ASSOC);
		if ($rows)
		{
			return array_map(fn($row) => ['word' => $row['word'], 'sounds' => json_decode($row['sounds']),
				'senses' => json_decode($row['senses']), 'forms' => json_decode($row['forms'])], $rows);
		}
	}
	return [];
}

// one equality per column to allow using its index
function spelled($dictionary, $language, $word, $rest)
{
	foreach (['match', 'clean'] as $column)
	{
		if ($dictionary->execute_query("select 1 from `$language` where `$column` = ? and $rest", [$word])->fetch_column()) { return true; }
	}
	return false;
}

function wordcheck($dictionary, $language, $word, $umlauts, $flexion)
{
	if (!dictionaryhas($dictionary, $language)) { return ""; }
	$mode = $umlauts ? 'clean' : 'match';
	if ($dictionary->execute_query("select 1 from `$language` where `$mode` = ? and strict = 1 and inflected <= ?",
		[$word, $flexion])->fetch_column()) { return "❕"; }
	if (!$flexion and spelled($dictionary, $language, $word, "inflected = 1 and strict = 0")) { return "⎇"; }
	if (!$umlauts and $dictionary->execute_query("select 1 from `$language` where clean = ? and `match` <> ? and inflected <= ? and strict = 0",
		[$word, $word, $flexion])->fetch_column()) { return "Ä→AE"; }
	if (spelled($dictionary, $language, $word, "strict = 1")) { return "❕"; }
	return "❗";
}
