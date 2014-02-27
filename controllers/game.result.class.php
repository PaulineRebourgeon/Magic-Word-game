<?php

class game_result
{
	private $mode = '';
	private $gameid = false;
	private $game = false;
	private $gridid = false;

	private $gridtypes = array();
	private $users_points = array();
	private $grids_points = array();
	private $grid_words = array();
	private $users_words = array();
	private $users_wordscount = 0;

	private $last_grid = false;

	private $in_battle = false;
	private $can_continue = false;

	private $quizz_results = 0;

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
		if ( !$this->gameid )
		{
			trigger_error('Sorry, no results available!', E_USER_ERROR);
		}

		// fermer la grille en cours pour cet utilisateur
		$sql = 'UPDATE gamesstatus
					SET gridstatus = ' . intval(GRIDSTATUS_FINISHED) . '
					WHERE gameid = ' . intval($this->gameid) . '
						AND userid = ' . intval($user->id) . '
						AND gridstatus = ' . intval(GRIDSTATUS_STARTED);
		$db->query($sql);

		// lecture de la partie
		$this->game = new game();
		$this->game->read($this->gameid);

		// faire de l'acteur le premier user du game
		if ( $this->game->userids && isset($this->game->userids[$user->id]) )
		{
			unset($this->game->userids[$user->id]);
			$this->game->userids = array($user->id => $user->username) + $this->game->userids;
		}

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

		if ( $this->game->gametype >= 2 )
		{
			// fermer le jeu en cours pour cet utilisateur si ce n'est pas un full game ou un battle
			$sql = 'UPDATE games
						SET gamefinished = ' . 1 . '
						WHERE gameid = ' . intval($this->gameid);
			$db->query($sql);
		}

		// obtenir le nombre de grilles terminées pour l'utilisateur
		$sql = 'SELECT COUNT(gridid) AS count_gridid
					FROM gamesstatus
					WHERE gameid = ' . intval($this->gameid) . '
						AND gridstatus = ' . intval(GRIDSTATUS_FINISHED) . '
						AND userid = ' . intval($user->id);
		$result = $db->query($sql);
		$count_gridid = ($row = $result->fetch_assoc()) ? intval($row['count_gridid']) : 0;

		// aucune grille terminée : théoriquement impossible
		if ( !$count_gridid )
		{
			trigger_error('You can not yet see results', E_USER_ERROR);
		}
		// n° de grille supérieur au nombre de grilles disponible pour le type de partie : théoriquement impossible
		if ( !isset($this->gridtypes[ ($count_gridid - 1) ]) )
		{
			trigger_error('Incoherent data', E_USER_ERROR);
		}

		// dernière partie : le nombre de grilles terminées pour le user est égal au nombre de grille du type de partie
		$this->last_grid = $count_gridid == count($this->gridtypes);

		// recherche du dernier gridid terminé en utilisant le gridtype
		$gridtype = $this->gridtypes[ ($count_gridid - 1) ];
		$sql = 'SELECT g.gridid
					FROM gamesstatus gs, grids g
					WHERE gs.gameid = ' . intval($this->gameid) . '
						AND g.gridid = gs.gridid
						AND g.gridtype = ' . intval($gridtype) . '
					LIMIT 1';
		$result = $db->query($sql);
		$this->gridid = ($row = $result->fetch_assoc()) ? intval($row['gridid']) : 0;

		// donnée in battle, continuer ou attendre
		$this->in_battle = $this->game->gametype == GAMETYPE_BATTLE;
		$this->can_continue = !$this->last_grid;
		$this->can_end = $this->last_grid;

		if ( $this->last_grid )
		{
			// fermer la grille en cours pour cet utilisateur
			$sql = 'UPDATE games
						SET gamefinished = ' . 1 . '
						WHERE gameid = ' . intval($this->gameid);
			$db->query($sql);
		}

		if ( $this->in_battle )
		{
			$sql = 'SELECT *
						FROM gamesstatus
						WHERE gameid = ' . intval($this->gameid) . '
							AND gridid = ' . intval($this->gridid) . '
							AND gridstatus <> ' . intval(GRIDSTATUS_FINISHED) . '
						LIMIT 1';
			$result = $db->query($sql);
			$exists = ($row = $result->fetch_assoc()) ? true : false;
			if ( $exists )
			{
				$this->can_continue = false;
				$this->can_end = false;

			}
		}

		// points par utilisateur et par grille
		$this->users_points = array();
		$this->quizz_results = isset($_GET['quizz']) ? intval($_GET['quizz']) : false;
		$sql = 'SELECT gs.gridid, gs.userid, g.gridtype, SUM(guw.wordpoints) AS sum_wordpoints
					FROM grids g, gamesstatus gs
						LEFT JOIN gamesuserswords guw
							ON guw.gameid = gs.gameid
								AND guw.userid = gs.userid
								AND guw.gridid = gs.gridid
					WHERE gs.gameid = ' . intval($this->gameid) . '
						AND gs.gridstatus = ' . intval(GRIDSTATUS_FINISHED) . '
						AND g.gridid = gs.gridid
					GROUP BY g.gridtype, gs.userid, gs.gridid';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			if ( !isset($this->users_points[ intval($row['gridtype']) ]) )
			{
				$this->users_points[ intval($row['gridtype']) ] = array();
			}
			$this->users_points[ intval($row['gridtype']) ][ intval($row['userid']) ] = array(
				'points' => intval($row['sum_wordpoints']),
				'gridid' => intval($row['gridid']),
			);

			if ( $this->quizz_results )
			{
				$this->users_points[ intval($row['gridtype']) ][ intval($row['userid']) ] ['points'] += $this->quizz_results;
			}
		}

		// nombre de mots de la grille en cours
		$this->grid_words = array();
		$sql = 'SELECT gridword, gridpoints
					FROM gridswords
					WHERE gridid = ' . intval($this->gridid) . '
					ORDER BY gridword, gridpoints DESC';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$this->grid_words[] = array(
				'word' => $row['gridword'],
				'points' => intval($row['gridpoints']),
			);
		}

		// mots trouvés par utilisateur
		$this->def_words = array();

		$this->users_words = array();
		$this->users_wordscount = array();
		$sql = 'SELECT userid, word, wordpoints, wordexists
					FROM gamesuserswords
					WHERE gameid = ' . intval($this->gameid) . '
						AND gridid = ' . intval($this->gridid) . '
					ORDER BY userid, wordpoints DESC, word';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			// liste des mots et nombre de mots trouvés
			if ( !isset($this->users_words[ intval($row['userid']) ]) )
			{
				$this->users_words[ intval($row['userid']) ] = array();
				$this->users_wordscount[ intval($row['userid']) ] = 0;
			}
			$this->users_words[ intval($row['userid']) ][] = array(
				'word' => $row['word'],
				'points' => intval($row['wordpoints']),
				'exists' => intval($row['wordexists']),
			);
			if ( $row['wordexists'] )
			{
				$this->users_wordscount[ intval($row['userid']) ]++;
			}
		}
		return $this->display();
	}

	private function display()
	{
		include('./views/game.result.html');
		return true;
	}
}

?>