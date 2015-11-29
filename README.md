Magic Word
================

Magic Word was created as part of the GAMER work package in the **[Innovalangues project](http://innovalangues.fr/)** at the Stendhal University in Grenoble, France. Innovalangues is a language learning centered IDEFI project (Initiatives in Excellence for Innovative Education) and will run from 2012 to 2018.

Based on a request by Innovalangue, this first version of Magic Word was designed and developed by two students of linguistics in the Language Industries master’s program, Pauline Rebourgeon and Christine Lutian, for their end-of-study professional project. Initially conceived for learners of English or Italian, the Innovalanges project hopes to adapt Magic Word for additional languages.

Rules
-------
Magic Word is a word search game, based on Boggle, and developed as a learning aid for foreign language learners. 

The game has two modes, solo and versus.
* In the solo mode, players can play as many rounds independently as they wish;
* In the versus mode, players go head-to-head and play three rounds.

At the end of a round, and to add to the learning experience, players can view definitions of all the possible words in the grid, as well as see suggestions for words that they played that were not found in the dictionary. They can then choose to review words later by adding them to their own personal dictionary, the Wordbox. To encourage vocabulary acquisition, a word from a player’s Wordbox is selected at random to appear on the player’s homepage for further review.

Requirements
-------------
* Apache Server with PHP 5.3+
* MySQL

Installation
-------------
* rename ./sys/config.db.sample.php to ./sys/config.db.php and include YOUR db connect info
* run initializeDB.php to verify your config works and to create the necessary tables
* the dB won't have a table for the dictionnaries, all those are included in the language files pointed to by "load_dictionnary.php"). To use them :
  * Select the dictionnaries you want in 'load_dictionnary.php' by un/commenting them ;
  * comment the "include_once('load_dictionary.php');" if you don't want to load the dictionaries… (it might be safer to connect to the server and use the MySQL cli)
  * The content of the LANGUAGE RESOURCES can be erased from the server after that operation.
  * It's also possible to manually run the sql files on the server.

## Contributors
### [First functional version of the project](https://github.com/PaulineRebourgeon/Magic-Word-game), developped for Innovalangues solely by :
* Pauline Rebourgeon
* Christine Lutian

### Later versions
* Maryam Nejat
* David Graceffa
 
### Other implementations
The *Università di Bologna* has proposed another version of the game more oriented towards finding particular words in the grid. It contains an algorithm to create grids containing certain words, it would allow to devise some pretty neat functionalities in terms of personalization (especially for language learning). [Do not hesitate to have a look at their instance of the game](https://github.com/giacomo-mambelli/magicword)
