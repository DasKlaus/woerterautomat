<?php
return [
'woerterautomat' => [
	'de' => 'Wörterautomat',
	'en' => 'Wörterautomat',
	'badges' => [
		'wa_anagram' => [
			'levels' => [6, 9, 12],
			'de' => 'Anagramm aus mindestens %s Buchstaben gefunden',
			'en' => 'Anagram of at least %s-letter word found',
		],
		'wa_long' => [
			'levels' => [8, 10, 12],
			'de' => 'Wort mit mindestens %s Buchstaben gefunden, das nicht im Ausgangswort steckt',
			'en' => 'Word of at least %s letters found that is not part of the source word',
		],
		'wa_words0_de' => [
			'levels' => [500, 1000, 2000],
			'de' => '%s verschiedene häufige deutsche Wörter gefunden',
			'en' => '%s distinct frequent German words found',
		],
		'wa_words1_de' => [
			'levels' => [500, 1000, 2000],
			'de' => '%s verschiedene gebräuchliche deutsche Wörter gefunden',
			'en' => '%s distinct common German words found',
		],
		'wa_words2_de' => [
			'levels' => [250, 500, 1000],
			'de' => '%s verschiedene ungewöhnliche oder seltene deutsche Wörter gefunden',
			'en' => '%s distinct uncommon or rare German words found',
		],
		'wa_words0_en' => [
			'levels' => [500, 1000, 2000],
			'de' => '%s verschiedene häufige englische Wörter gefunden',
			'en' => '%s distinct frequent English words found',
		],
		'wa_words1_en' => [
			'levels' => [500, 1000, 2000],
			'de' => '%s verschiedene gebräuchliche englische Wörter gefunden',
			'en' => '%s distinct common English words found',
		],
		'wa_words2_en' => [
			'levels' => [250, 500, 1000],
			'de' => '%s verschiedene ungewöhnliche oder seltene englische Wörter gefunden',
			'en' => '%s distinct uncommon or rare English words found',
		],
		'wa_burst' => [
			'levels' => [10, 20, 30],
			'de' => '%s Wörter in 60 Sekunden gefunden',
			'en' => '%s words found in 60 seconds',
		],
		'wa_share0' => [
			'levels' => [50, 67, 80],
			'de' => '%s%% der häufigen Wörter eines Spiels gefunden',
			'en' => 'Found %s%% of frequent words in a game',
		],
		'wa_share1' => [
			'levels' => [33, 50, 75],
			'de' => '%s%% der gebräuchlichen Wörter eines Spiels gefunden',
			'en' => 'Found %s%% of common words in a game',
		],
		'wa_share2' => [
			'levels' => [10, 25, 33],
			'de' => '%s%% der ungewöhnlichen Wörter eines Spiels gefunden',
			'en' => 'Found %s%% of uncommon words in a game',
		],
		'wa_klaus' => [
			'levels' => [1],
			'de' => 'Klaus in einem Spiel geschlagen',
			'en' => 'Beat Klaus in a game',
		],
		'wa_streak' => [
			'levels' => [3, 5, 7],
			'de' => 'Spiel des Tages an %s Tagen in Folge gespielt',
			'en' => 'Game of the day played %s days in a row',
		],
		'wa_daily' => [
			'levels' => [1, 2, 3],
			'labels' => ['de' => ['häufig', 'gebräuchlich', 'ungewöhnlich'], 'en' => ['frequent', 'common', 'uncommon']],
			'de' => 'Ziel der %sen Wörter im Spiel des Tages erreicht',
			'en' => 'Reached the %s goal in the game of the day',
		],
	],
],
];
