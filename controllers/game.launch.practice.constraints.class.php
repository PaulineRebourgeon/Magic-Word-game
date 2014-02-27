<?php

class game_launch_constraints
{
	private $mode = '';
	private $gameid = false;

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		$user = user::getInstance();
		$db = db::getInstance();

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

		$gametype = GAMETYPE_PRACTICE_CONSTRAINTS;

		// création d'une nouvelle grille
		$gridtype = GRIDTYPE_CONSTRAINTS;
		$grid = new grid();
		$gridid = $grid->create($gridtype, $gametype);

		// ajout de la grille au game
		$game->assign_grid($gridid);
		$game->start_grid($gridid);

		// enrichissement du retour json
		$res = $grid->get($gametype);
		$res->gameid = $this->gameid;

		header('Content-Type: application/json');
		echo json_encode($res);
		die();
	}
}

?>