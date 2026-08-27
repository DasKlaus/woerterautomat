<?php
header('Content-Type: text/plain');
while (ob_get_level()) { ob_end_flush(); }
ob_implicit_flush(true);
require_once("config.php");
require_once("dictionary.php");

foreach ($mysql->execute_query("select id, source_word, language, umlauts, flexion from game where solutions < 0")->fetch_all(MYSQLI_ASSOC) as $game)
{
	echo $game['id']." ".$game['source_word']." (".$game['language'].") ";
	$words = solutionwords($dictionary, $game['language'], $game['umlauts'], $game['flexion'], $game['source_word']);
	$mysql->execute_query("delete from solution where game_id = ?", [$game['id']]);
	if ($words) { savesolution($mysql, $game['id'], $words); }
	$mysql->execute_query("update game set solutions = ? where id = ?", [is_null($words) ? -1 : count($words), $game['id']]);
	echo (is_null($words) ? "no dictionary" : count($words)." words").PHP_EOL;
}

echo "Done!".PHP_EOL;
?>
