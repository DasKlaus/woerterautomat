<?php
header('Content-Type: text/plain');
while (ob_get_level()) { ob_end_flush(); }
ob_implicit_flush(true);
require_once("config.php");
require_once("dictionary.php");

// ?all=1 rebuilds every game rather than only the ones no dictionary ever answered for, for when an
// import changes what the same settings would have found
$scope = isset($_GET['all']) ? "" : " where solutions < 0";

foreach ($mysql->execute_query("select id, source_word, language, umlauts, flexion from game".$scope)->fetch_all(MYSQLI_ASSOC) as $game)
{
	echo $game['id']." ".$game['source_word']." (".$game['language'].") ";
	$words = solutionwords($dictionary, $game['language'], $game['umlauts'], $game['flexion'], $game['source_word']);
	$mysql->execute_query("delete from solution where game_id = ?", [$game['id']]);
	if ($words) { savesolution($mysql, $game['id'], $words); }
	$mysql->execute_query("update game set solutions = ? where id = ?", [is_null($words) ? -1 : count($words), $game['id']]);

	// the dictionary's own reactions are a function of the solution, so they are rebuilt with it
	$found = array_column($mysql->execute_query("select distinct word from word where game_id = ?", [$game['id']])->fetch_all(MYSQLI_ASSOC), 'word');
	$mysql->execute_query("delete from reaction where game_id = ? and reactor_id = -1", [$game['id']]);
	$reactions = 0;
	foreach (array_diff($found, $words ?? []) as $unknown)
	{
		$emoji = wordcheck($dictionary, $game['language'], $unknown, $game['umlauts'], $game['flexion']);
		if ($emoji)
		{
			$mysql->execute_query("insert into reaction (game_id, word, reactor_id, emoji, display_name, created_at)
				values (?, ?, -1, ?, 'Wörterbuch', now())", [$game['id'], $unknown, $emoji]);
			$reactions++;
		}
	}
	echo (is_null($words) ? "no dictionary" : count($words)." words").", ".$reactions." reactions".PHP_EOL;
}

echo "Done!".PHP_EOL;
?>
