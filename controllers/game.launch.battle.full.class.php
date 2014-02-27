<?php

class game_launch_battle_full
{
	private $mode = '';
	private $gameid = false;

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		include('./models/game.class.php');
		include('./models/grid.class.php');

		// récupérer le gameid
		$this->gameid = isset($_REQUEST['gameid']) ? intval($_REQUEST['gameid']) : false;
		if ( $this->gameid === false )
		{
			trigger_error('Game not found', E_USER_ERROR);
		}

		// lecture de l'objet game
		$game = new game();
		$game->read($this->gameid);

		// lire les grilles terminées pour trouver le prochain type de grille
		$grid_counts = array();
		$last_grid = 0;
		$sql = 'SELECT userid, COUNT(gridid) AS count_gridid
					FROM gamesstatus
					WHERE gameid = ' . intval($this->gameid) . '
						AND gridstatus = ' . intval(GRIDSTATUS_FINISHED) . '
					GROUP BY userid';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$grid_counts[ intval($row['userid']) ] = intval($row['count_gridid']);
			if ( (intval($row['count_gridid']) > $last_grid) && (intval($row['userid']) != $user->id) )
			{
				$last_grid = intval($row['count_gridid']);
			}
		}
		if ( isset($grid_counts[$user->id]) )
		{
			if ( $grid_counts && ($last_grid < intval($grid_counts[$user->id])) )
			{
				trigger_error('Wait for the other!', E_USER_ERROR);
			}
			$grid_count = $grid_counts ? intval($grid_counts[$user->id]) : 0;
		}
		else
		{
			$grid_count = 0;
		}

		// déterminer le prochain type de grille
		$gridtype = false;
		switch ( $grid_count )
		{
			case 0:
				$gridtype = GRIDTYPE_ALLWORDS;
			break;
			case 1:
				$gridtype = GRIDTYPE_LONGEST;
			break;
			case 2:
				$gridtype = GRIDTYPE_CONSTRAINTS;
			break;
			default:
				trigger_error('Game over!', E_USER_ERROR);
			break;
		}

		// vérifier si l'autre adversaire est en train de créer une grille
		$sql = ' SELECT *
					FROM gamesusers
					WHERE gameid = ' . intval($this->gameid);
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		$request = $row['request'];

		// lire les grilles existantes pour ce type de grille
		$sql = 'SELECT *
					FROM gamesstatus gs, grids g
					WHERE gs.gameid = ' . intval($this->gameid) . '
						AND gs.userid = ' . intval($user->id) . '
						AND gs.gridstatus = ' . intval(GRIDSTATUS_ASSIGNED) . '
						AND g.gridid = gs.gridid
						AND g.gridtype = ' . intval($gridtype);
		$result = $db->query($sql);
		$gridid = ($row = $result->fetch_assoc()) ? intval($row['gridid']) : false;

		if ( $request == 1 )
		{
			// lire les grilles existantes pour ce type de grille
			$sql = 'SELECT *
						FROM gamesstatus gs, grids g
						WHERE gs.gameid = ' . intval($this->gameid) . '
							AND gs.userid = ' . intval($user->id) . '
							AND gs.gridstatus = ' . intval(GRIDSTATUS_ASSIGNED) . '
							AND g.gridid = gs.gridid
							AND g.gridtype = ' . intval($gridtype);
			$result = $db->query($sql);
			$gridid = ($row = $result->fetch_assoc()) ? intval($row['gridid']) : false;

			while( $gridid === false )
			{
				// lire les grilles existantes pour ce type de grille
				$sql = 'SELECT *
							FROM gamesstatus gs, grids g
							WHERE gs.gameid = ' . intval($this->gameid) . '
								AND gs.userid = ' . intval($user->id) . '
								AND gs.gridstatus = ' . intval(GRIDSTATUS_ASSIGNED) . '
								AND g.gridid = gs.gridid
								AND g.gridtype = ' . intval($gridtype);
				$result = $db->query($sql);
				$gridid = ($row = $result->fetch_assoc()) ? intval($row['gridid']) : false;
			}
		}

		if ( $gridid === false )
		{
			if ( $request == 0 )
			{
				$sql = 'UPDATE gamesusers
							SET request = 1
							WHERE gameid = ' . intval($this->gameid);
				$db->query($sql);

				// création d'une nouvelle grille
				$grid = new grid();
				$gridid = $grid->create($gridtype);

				// ajout de la grille au game
				$game->assign_grid($gridid);
				$game->start_grid($gridid);
			}
		}
		else
		{
			$grid = new grid();
			$grid->read($gridid, $game->gamelang, $gridtype);
			$game->start_grid($gridid);
			$sql = 'UPDATE gamesusers
						SET request = 0
						WHERE gameid = ' . intval($this->gameid);
			$db->query($sql);

		}
		$res = $grid->get();
		$res->gameid = $this->gameid;
		$res->gametype = GAMETYPE_BATTLE;

		header('Content-Type: application/json');
		echo json_encode($res);
		die();
	}
}

?>