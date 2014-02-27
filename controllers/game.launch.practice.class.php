<?php

class game_launch_practice
{
	private $mode = '';

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		if ( $this->init() )
        {
            $this->check();
            $this->validate();
            return $this->display();
        }
        return false;
	}

	private function init()
	{
		return true;
	}

	private function check()
	{
		return false;
	}

	private function validate()
	{
		return false;
	}

	private function display()
	{
		include('./views/game.launch.practice.html');
        return true;
	}
}

?>