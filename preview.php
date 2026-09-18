<?php
require_once("config.php");

$word = "wörterautomat";
$language = 'de';
if ((int)($_GET['game'] ?? 0))
{
	$row = $mysql->execute_query("select source_word, language from game where id = ?", [(int)$_GET['game']])->fetch_assoc();
	if ($row) { $word = $row['source_word']; $language = $row['language']; }
}
$font = __DIR__.'/fonts/ScrambleMixed-'.(['de' => 'German', 'en' => 'English'][$language] ?? 'German').'.ttf';

# 1200x628 is the ratio link previews crop to; the word wraps into whichever number of rows gives the largest letters
$width = 1200;
$height = 628;
$letters = mb_str_split($word);
$box = imagettfbbox(100, 0, $font, $word);
$advance = ($box[2] - $box[0]) / count($letters);
$line = 1.1 * ($box[1] - $box[7]);
$size = 0;
for ($n = 1; $n <= count($letters); $n++)
{
	$chunks = array_chunk($letters, (int)ceil(count($letters) / $n));
	$fit = min(100 * ($width - 80) / (count($chunks[0]) * $advance), 100 * ($height - 80) / (count($chunks) * $line));
	if ($fit > $size) { $size = $fit; $rows = $chunks; }
}

$image = imagecreatetruecolor($width, $height);
imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
$top = ($height - (count($rows) * $line - 0.1 * ($box[1] - $box[7])) * $size / 100) / 2 - $box[7] * $size / 100;
foreach ($rows as $i => $row)
{
	$rowbox = imagettfbbox($size, 0, $font, implode($row));
	imagettftext($image, $size, 0, (int)(($width - ($rowbox[2] - $rowbox[0])) / 2 - $rowbox[0]), (int)($top + $i * $line * $size / 100),
		imagecolorallocate($image, 0, 0, 0), $font, implode($row));
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
imagepng($image);
