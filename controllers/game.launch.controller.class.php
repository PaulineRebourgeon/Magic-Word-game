<?php

class game_launch_controller
{
	private $mode = '';

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		switch ( $this->mode )
		{
			// display practice menu
			case 'game.launch.practice':
				include('controllers/game.launch.practice.class.php');
				$controller = new game_launch_practice();
				$controller->set_mode($this->mode);
				return $controller->process();
			break;

			// start game per type
			case 'game.launch.practice.allwords':
			case 'game.launch.practice.longest':
			case 'game.launch.practice.constraints':
			case 'game.launch.practice.full':
				include('controllers/game.launch.practice.main.class.php');
				$controller = new game_launch_main();
				$controller->set_mode($this->mode);
				return $controller->process();
			break;

			// create grid for each game type
			case 'game.launch.practice.full.newgrid':
				include('controllers/game.launch.practice.full.class.php');
				$controller = new game_launch_full();
				$controller->set_mode($this->mode);
				return $controller->process();
			break;
			case 'game.launch.practice.allwords.newgrid':
				include('controllers/game.launch.practice.allwords.class.php');
				$controller = new game_launch_allwords();
				$controller->set_mode($this->mode);
				return $controller->process();
			break;
			case 'game.launch.practice.longest.newgrid':
				include('controllers/game.launch.practice.longest.class.php');
				$controller = new game_launch_longest();
				$controller->set_mode($this->mode);
				return $controller->process();
			break;
			case 'game.launch.practice.constraints.newgrid':
				include('controllers/game.launch.practice.constraints.class.php');
				$controller = new game_launch_constraints();
				$controller->set_mode($this->mode);
				return $controller->process();
			break;

			// start battle
			case 'game.launch.battle':
				include('controllers/game.launch.battle.main.class.php');
				$controller = new game_launch_battle();
				$controller->set_mode($this->mode);
				return $controller->process();
			break;

			// create battle grid
			case 'game.launch.battle.newgrid':
				include('controllers/game.launch.battle.full.class.php');
				$controller = new game_launch_battle_full();
				$controller->set_mode($this->mode);
				return $controller->process();
			break;
		}
		return false;
	}
}

?>