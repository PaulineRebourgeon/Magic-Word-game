<?php

class game_launch_full
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
		$sql = 'SELECT COUNT(gridid) AS count_gridid
					FROM gamesstatus
					WHERE gameid = ' . intval($this->gameid) . '
						AND userid = ' . intval($user->id) . '
						AND gridstatus = ' . intval(GRIDSTATUS_FINISHED);
		$result = $db->query($sql);
		$grid_count = ($row = $result->fetch_assoc()) ? intval($row['count_gridid']) : 0;

		// déterminer le prochain type de grille
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
		}

		// création d'une nouvelle grille
		$grid = new grid();
		$gridid = $grid->create($gridtype);

		// ajout de la grille au game
		$game->assign_grid($gridid);
		$game->start_grid($gridid);

		$res = $grid->get();
		$res->gameid = $this->gameid;
		$res->gametype = GAMETYPE_PRACTICE_FULL;

		header('Content-Type: application/json');
		echo json_encode($res);
		die();
	}
}

?>