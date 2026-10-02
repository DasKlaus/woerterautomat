# Wörterautomat

A multiplayer word game: find as many words as you can in the letters of a given word
* [Play](http://wollmilchmedien.com/woerterautomat/) it here

## Setup

* put everything on a server
* create the game database from `schema.sql` and the shared identity database from `identity.sql`
* the dictionary database holds one table per language (`de`, `en`), imported from [kaikki.org](https://kaikki.org/dictionary/)'s Wiktionary data; the import is not part of this repository
* enter the mysql credentials in `config.php`, `identity.php` and `dictionary.php`, and the operator's address in `legal.php`
* the addresses of games, the game of the day and the link previews need Apache's mod_rewrite (`woerterautomat/.htaccess`)
* run `woerterautomat/candidates.php` after a dictionary import to fill the game of the day's word list, and `woerterautomat/backfill.php` to update scoring
* put a `.user.ini` above the document root switching `display_errors` off and `log_errors` on, with
  `error_log` pointing outside the document root — `config.php` runs too late to catch a parse error

## Play

Pick a name on the profile page or play anonymously, then start a game with a word of your choice or join one from the game list. Sharing a game's URL invites others.
Each word scores as many points as it has letters, multiplied by the number of fellow players who did not find it. Settings decide the dictionary language, whether umlauts may be substituted (AE for Ä), whether inflected forms count, whether only words the dictionary knows score, and limits on players and inactivity.
The result is shown once you finish: everyone's words, the possible solutions from the dictionary, and emoji reactions to them, which can be added at this stage. The game is over when every player has finished.

## Game of the day

A new word every day and language at `tag-N-de` and `tag-N-en`, played alone. Words from a length on that makes ten frequent words count; the headline shows the next goal (frequent, then common, then uncommon words). Words that aren't allowed are misses, and the fourth one ends the game. 

## Notes

Each game's solution is taken from the dictionary once, when the game is created, and kept with it. Scores are recomputed from the words on every change. Players have no accounts: a code is the whole identity, kept in a cookie on request, and can be used to sign in elsewhere.
The dictionary is based on data from [Wiktionary](https://www.wiktionary.org), extracted by [kaikki.org](https://kaikki.org/dictionary/), and is licensed under [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/).
