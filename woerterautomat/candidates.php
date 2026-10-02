<?php
header('Content-Type: text/plain');
while (ob_get_level()) { ob_end_flush(); }
ob_implicit_flush(true);
require_once("config.php");
require_once("../dictionary.php");
require_once("meta.php");

// Refills the daily's candidate words, rerun after a dictionary import; the words already marked for a day stay, and the
// next four weeks are marked. A source is an uninflected entry up to uncommon with a pronunciation, 6 to 12 letters as played.
// It qualifies under a combination of settings if, counting down from its solution's longest length, the frequent words reach
// exactly ten at some length L (those ten are kept beside it for review), and its frequent and common words number fewer than 100.
// Settings are tried fewest first: none, else inflected forms alone and umlaut substitution alone, else both; substitution only
// counts where it changes the frequent words' count by length. A daily's word and its anagrams are never candidates again.
// Rather than solutionwords() per word, every word is filed under its sorted letters, and a candidate's solution is what
// is filed under every sub-multiset of its own letters.
function sortletters($word)
{
	$letters = mb_str_split($word);
	sort($letters);
	return implode($letters);
}

foreach (['de', 'en'] as $language)
{
	if (!dictionaryhas($dictionary, $language)) { continue; }
	$used = array_map('sortletters', array_column($mysql->execute_query("select source_word from game where daily is not null and language = ?",
		[$language])->fetch_all(), 0));
	$mysql->execute_query("delete from candidate where language = ? and day is null", [$language]);
	$sources = array_column($dictionary->execute_query("select `match`, min(clean) as clean from `$language` where rarity < 3 and inflected = 0 and sounds <> '[]'
		and (char_length(`match`) between 6 and 12 or char_length(clean) between 6 and 12) group by `match`")->fetch_all(MYSQLI_ASSOC), 'clean', 'match');
	$settled = []; // source => the number of settings it qualified with, which rules out combinations of more
	$counts = []; // flexion => source => frequent words by length without substitution
	$candidates = [];
	foreach ([[0, 0], [1, 0], [0, 1], [1, 1]] as [$flexion, $umlauts])
	{
		$mode = $umlauts ? 'clean' : 'match';
		$frequent = []; // space-separated, as the leanest way to hold this many
		$common = [];
		foreach ($dictionary->execute_query("select `$mode` as word, min(rarity) as rarity from `$language` where rarity < 2 and inflected <= ?
			and char_length(`$mode`) between 3 and 12 group by `$mode`", [$flexion]) as $row)
		{
			$key = sortletters($row['word']);
			if ($row['rarity'] == 0) { $frequent[$key] = isset($frequent[$key]) ? $frequent[$key].' '.$row['word'] : $row['word']; }
			else { $common[$key] = isset($common[$key]) ? $common[$key].' '.$row['word'] : $row['word']; }
		}
		foreach ($sources as $source => $clean)
		{
			if (($settled[$source] ?? 3) < $flexion + $umlauts) { continue; }
			$word = $umlauts ? $clean : $source;
			$all = sortletters($word);
			if (mb_strlen($word) < 6 or mb_strlen($word) > 12 or in_array($all, $used, true)) { continue; }
			$subsets = ['' => 0];
			foreach (array_count_values(mb_str_split($all)) as $letter => $count)
			{
				$next = [];
				foreach ($subsets as $subset => $length)
				{
					for ($i = 0; $i <= $count; $i++) { $next[$subset.str_repeat($letter, $i)] = $length + $i; }
				}
				$subsets = $next;
			}
			$bylength = [];
			$total = 0;
			foreach ($subsets as $subset => $length)
			{
				if ($length < 3) { continue; }
				if (isset($frequent[$subset])) { $bylength[$length] = ($bylength[$length] ?? 0) + substr_count($frequent[$subset], ' ') + 1; }
				if (isset($common[$subset])) { $total += substr_count($common[$subset], ' ') + 1; }
			}
			// the word itself is filed under all its letters, but is no word of its own solution
			if (str_contains(' '.($frequent[$all] ?? '').' ', " $word ")) { $bylength[mb_strlen($word)]--; }
			if (str_contains(' '.($common[$all] ?? '').' ', " $word ")) { $total--; }
			$bylength = array_filter($bylength);
			krsort($bylength);
			if (!$umlauts) { $counts[$flexion][$source] = $bylength; }
			elseif ($bylength == ($counts[$flexion][$source] ?? null)) { continue; }
			$total += array_sum($bylength);
			$long = 0;
			foreach ($bylength as $shortest => $count)
			{
				$long += $count;
				if ($long >= 10) { break; }
			}
			if ($long != 10 or $total >= 100) { continue; }
			$settled[$source] = $flexion + $umlauts;
			$target = [];
			foreach ($subsets as $subset => $length)
			{
				if ($length >= $shortest and isset($frequent[$subset])) { array_push($target, ...explode(' ', $frequent[$subset])); }
			}
			$candidates[] = [$word, $flexion, $umlauts, implode(' ', array_diff($target, [$word]))];
		}
	}
	foreach (array_chunk($candidates, 500) as $chunk)
	{
		$values = [];
		foreach ($chunk as $candidate) { array_push($values, $language, ...$candidate); }
		$mysql->execute_query("insert ignore into candidate (language, word, flexion, umlauts, target) values ".implode(",", array_fill(0, count($chunk), "(?,?,?,?,?)")), $values);
	}
	foreach ($mysql->execute_query("select char_length(word) as length, count(*) as count from candidate where language = ? group by length", [$language]) as $row)
	{
		echo $language." ".$row['length']." letters: ".$row['count']." candidates".PHP_EOL;
	}
	plandailies($mysql, $language);
}

echo "Done!".PHP_EOL;
?>
