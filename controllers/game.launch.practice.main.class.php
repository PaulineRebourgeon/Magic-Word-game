<?php

class game_launch_main
{
	private $mode = '';
	private $gameid = 0;

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		include('./models/game.class.php');

		// récupérer le gameid si il existe
		$game = false;
		$this->gameid = isset($_REQUEST['gameid']) ? intval($_REQUEST['gameid']) : false;
		if ( $this->gameid !== false )
		{
			$game = new game();
			if ( !$game->read($this->gameid) )
			{
				$this->gameid = false;
				trigger_error('Game not found!', E_USER_ERROR);
			}
			if ( !isset($game->userids[$user->id]) )
			{
				$this->gameid = false;
				trigger_error('Not your game!', E_USER_ERROR);
			}
		}

		/*// fermer toute grille en cours pour cet utilisateur
		$sql = 'UPDATE gamesstatus
					SET gridstatus = ' . intval(GRIDSTATUS_FINISHED) . '
					WHERE userid = ' . intval($user->id) . '
						AND gridstatus = ' . intval(GRIDSTATUS_STARTED);
		$db->query($sql);*/

		// déterminer le type de partie à partir du mode
		$gametype = false;
		switch ( $this->mode )
		{
			case 'game.launch.practice.allwords':
				$gametype = GAMETYPE_PRACTICE_ALLWORDS;
			break;
			case 'game.launch.practice.longest':
				$gametype = GAMETYPE_PRACTICE_LONGEST;
			break;
			case 'game.launch.practice.constraints':
				$gametype = GAMETYPE_PRACTICE_CONSTRAINTS;
			break;
			case 'game.launch.practice.full':
				$gametype = GAMETYPE_PRACTICE_FULL;
			break;
		}

		// créer une nouvelle partie
		if ( !$this->gameid )
		{
			$userids = array($user->id);
			$game = new game();
			$this->gameid = $game->create($userids, $gametype, $user->get_lang());
		}

		return $this->display();
	}

	private function display()
	{
		include('./views/game.launch.html');
        return true;
	}
}

?>