<?php

class game_definitions
{
	private $mode = '';
	private $gameid = false;
	private $gridid = false;

	private $gridtypes = array();
	private $users_words = array();
	private $users_wordscount = 0;

	private $words_found = array();
	private $words_not_known = array();
	private $right_words = array();
	private $def_word = '';
	private $def_words = array();
	private $def_test_words = array();
	private $def_rand_words = array();
	private $redirect = false;

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		include('./models/game.class.php');

		// récupérer les paramètres
		$this->gameid = isset($_GET['gameid']) ? intval($_GET['gameid']) : false;
		$this->gridid = isset($_GET['gridid']) ? intval($_GET['gridid']) : false;
		$this->right_words = isset($_GET['right_words']) ? $_GET['right_words'] : false;
		$this->send = isset($_GET['send']) ? $_GET['send'] : false;
		if ( !$this->gameid )
		{
			trigger_error('Sorry, no results available!', E_USER_ERROR);
		}

		if ( $this->right_words )
		{
			// mise à jour des statuts des mots de la wordbox que l'apprenant connait
			foreach ( $this->right_words as $word )
			{
				$sql = 'UPDATE wordbox
							SET wordboxstatus = 2
							WHERE userid = '. intval($user->id) . '
							AND wordboxword = \'' . $word . '\'';
				$db->query($sql);
				_dump($sql);
			}
			return false;
		}

		// s'il y a plus de 10 mots dans la wordbox, faire l'exercice
		$sql = 'SELECT COUNT(*)
					FROM wordbox
					WHERE userid = ' . intval($user->id);
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		$count_wordbox = $row['COUNT(*)'];
		if ( $count_wordbox < 10 )
		{
			$this->redirect = true;
			return $this->display();
		}

		// lecture de la partie
		$this->game = new game();
		$this->game->read($this->gameid);

		// liste des grilles
		$this->gridtypes = array();
		switch ( $this->game->gametype )
		{
			case GAMETYPE_BATTLE:
			case GAMETYPE_PRACTICE_FULL:
				$this->gridtypes = array(GRIDTYPE_ALLWORDS, GRIDTYPE_LONGEST, GRIDTYPE_CONSTRAINTS);
			break;
			case GAMETYPE_PRACTICE_ALLWORDS:
				$this->gridtypes = array(GRIDTYPE_ALLWORDS);
			break;
			case GAMETYPE_PRACTICE_LONGEST:
				$this->gridtypes = array(GRIDTYPE_LONGEST);
			break;
			case GAMETYPE_PRACTICE_CONSTRAINTS:
				$this->gridtypes = array(GRIDTYPE_CONSTRAINTS);
			break;
		}

		// obtenir le nombre de grilles terminées pour l'utilisateur
		$sql = 'SELECT COUNT(gridid) AS count_gridid
					FROM gamesstatus
					WHERE gameid = ' . intval($this->gameid) . '
						AND gridstatus = ' . intval(GRIDSTATUS_STARTED) . '
						AND userid = ' . intval($user->id);
		$result = $db->query($sql);

		$count_gridid = ($row = $result->fetch_assoc()) ? intval($row['count_gridid']) : 0;

		// dernière partie : le nombre de grilles terminées pour le user est égal au nombre de grille du type de partie
		$this->last_grid = $count_gridid == count($this->gridtypes);


		// mots trouvés par utilisateur
		$this->def_words = array();

		$this->users_words = array();
		$this->users_wordscount = array();
		$sql = 'SELECT userid, word, wordpoints, wordexists
					FROM gamesuserswords
					WHERE gameid = ' . intval($this->gameid) . '
						AND gridid = ' . intval($this->gridid) . '
						AND userid = ' . intval($user->id) . '
					ORDER BY userid, wordpoints DESC, word';
		$result = $db->query($sql);

		while ( ($row = $result->fetch_assoc()) )
		{
			array_push($this->words_found, $row['word']);
		}

		foreach ( $this->words_found as $word )
		{
			// recherche des mots que l'apprenant ne connait pas encore
			$sql = 'SELECT *
						FROM wordbox
						WHERE userid = ' . intval($user->id) . '
						AND wordboxword = \'' . $word . '\'
						AND (
							wordboxstatus = 0
							OR wordboxstatus = 1
							)
						AND wordboxlang = ' . $db->escape((string) $user->get_lang());
			$result = $db->query($sql);
			while ( ($row = $result->fetch_assoc()) )
			{
				array_push($this->words_not_known, $word);
			}
		}
		if ( !$this->words_not_known )
		{
			$this->redirect = true;
			return $this->display();
		}
		// ajouter les mots à l'exercice de définitions
		foreach ( $this->words_not_known as $word )
		{
			$this->def_words[ intval($user->id) ][] = array(
				'word' => $word,
			);
			array_push($this->def_test_words, $word);
		}

		// mise à jour des statuts des mots de la wordbox si l'apprenant l'a trouvé dans la grille
		$sql = 'UPDATE wordbox
					SET wordboxstatus = 1
					WHERE userid = ' . intval($user->id) . '
					AND wordboxword = \'' . $row['word'] . '\'
					AND wordboxstatus = 0
					AND wordboxlang = \'it\'';
		$db->query($sql);

		$found = false;
		while ( $found === false ) {
			$found = true;
			$this->def_rand_words = array();
			$this->def_test_words_temp = $this->def_test_words;
			$sql = 'SELECT wordboxword
						FROM wordbox
						ORDER BY RAND()
						LIMIT 2';
			$result = $db->query($sql);
			while ( ($row = $result->fetch_assoc()) )
			{
				array_push($this->def_rand_words, $row['wordboxword']);
				array_push($this->def_test_words_temp, $row['wordboxword']);
			}
			foreach ( $this->def_rand_words as $rand_word ) {
				foreach ( $this->def_words[$user->id] as $key => $word) {
					if ( $word['word'] == $rand_word )
					{
						$found = false;
					}
				}
			}
		}
		shuffle($this->def_test_words_temp);
		$this->def_test_words = $this->def_test_words_temp;

		return $this->display();
    }

	private function display()
	{
		if ( $this->redirect == true )
		{
			redirect('game.result&gameid=' . intval($this->gameid));
			return true;
		}
		if ( $this->redirect == false )
		{
			include('./views/game.definitions.html');
			return true;
		}
	}
}
?>