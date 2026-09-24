<?php
header('Content-Type: text/plain');
while (ob_get_level()) { ob_end_flush(); }
ob_implicit_flush(true);
require_once("config.php");
require_once("../dictionary.php");
require_once("meta.php");

// Refills the daily's candidate words, rerun after a dictionary import; the words already marked for a day stay, and the
// next four weeks are marked. A candidate is an uninflected, non-rare entry with a pronunciation whose solution, under the
// settings its length gets (6 and 7 letters with inflected forms, 8 to 12 without), has 25 to 150 words and exactly one of
// its longest length, the target, which is kept beside it for review. On weekends the target is of the full length, and it
// is never spelled out in the candidate's own order of letters. A daily's word and its anagrams are never candidates again.
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
	foreach ([[1, 6, 7], [0, 8, 12]] as [$flexion, $shortest, $longest])
	{
		$filed = []; // space-separated, as the leanest way to hold this many
		foreach ($dictionary->execute_query("select distinct `match` from `$language` where strict = 0 and inflected <= ? and char_length(`match`) between 3 and ?",
			[$flexion, $longest]) as $row)
		{
			$key = sortletters($row['match']);
			$filed[$key] = isset($filed[$key]) ? $filed[$key].' '.$row['match'] : $row['match'];
		}
		$candidates = [];
		foreach ($dictionary->execute_query("select `match` from `$language` where strict = 0 and inflected = 0 and sounds <> '[]'
			and char_length(`match`) between ? and ?", [$shortest, $longest]) as $row)
		{
			$word = $row['match'];
			if (in_array(sortletters($word), $used, true)) { continue; }
			$subsets = ['' => 0];
			foreach (array_count_values(mb_str_split(sortletters($word))) as $letter => $count)
			{
				$next = [];
				foreach ($subsets as $subset => $length)
				{
					for ($i = 0; $i <= $count; $i++) { $next[$subset.str_repeat($letter, $i)] = $length + $i; }
				}
				$subsets = $next;
			}
			$bylength = [];
			foreach ($subsets as $subset => $length)
			{
				if ($length > 2 and isset($filed[$subset])) { $bylength[$length] = ($bylength[$length] ?? 0) + substr_count($filed[$subset], ' ') + 1; }
			}
			$bylength[mb_strlen($word)]--; // the word itself
			$bylength = array_filter($bylength);
			krsort($bylength);
			$top = array_key_first($bylength);
			$total = array_sum($bylength);
			if ($total >= 25 and $total <= 150 and $bylength[$top] == 1 and ($flexion == 0 or $top == mb_strlen($word)))
			{
				$longestwords = [];
				foreach ($subsets as $subset => $length)
				{
					if ($length == $top and isset($filed[$subset])) { array_push($longestwords, ...explode(' ', $filed[$subset])); }
				}
				$target = implode(array_diff($longestwords, [$word]));
				// read off rather than found, like läufer in läuferin or vorzeichen in vorzeichnen
				if (!preg_match('/'.implode('.*', mb_str_split($target)).'/u', $word)) { $candidates[] = [$word, $target]; }
			}
		}
		foreach (array_chunk($candidates, 500) as $chunk)
		{
			$values = [];
			foreach ($chunk as [$word, $target]) { $values[] = $language; $values[] = $word; $values[] = $target; }
			$mysql->execute_query("insert ignore into candidate (language, word, target) values ".implode(",", array_fill(0, count($chunk), "(?,?,?)")), $values);
		}
		echo $language." ".$shortest."-".$longest." letters: ".count($candidates)." candidates".PHP_EOL;
	}
	plandailies($mysql, $language);
}

echo "Done!".PHP_EOL;
?>
