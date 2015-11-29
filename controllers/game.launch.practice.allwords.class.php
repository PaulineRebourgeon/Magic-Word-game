<?php

class game_launch_allwords
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

		// création d'une nouvelle grille
		$gridtype = GRIDTYPE_ALLWORDS;
		$grid = new grid();
		$gridid = $grid->create($gridtype);

		// ajout de la grille au game
		$game->assign_grid($gridid);
		$game->start_grid($gridid);

		// enrichissement du retour json
		$res = $grid->get();
		$res->gameid = $this->gameid;
		$res->gametype = GAMETYPE_PRACTICE_ALLWORDS;

		header('Content-Type: application/json');
		echo json_encode($res);
		die();
	}
}

?>