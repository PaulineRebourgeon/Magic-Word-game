<?php

class game_word
{
	private $mode = '';

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		include('./models/grid.words.class.php');

		$gameid = isset($_GET['gameid']) ? intval($_GET['gameid']) : false;
		$gridid = isset($_GET['gridid']) ? intval($_GET['gridid']) : false;
		$word = isset($_GET['word']) ? $_GET['word'] : '';
		$wordexists = isset($_GET['wordexists']) ? intval($_GET['wordexists']) : false;
		$wordpoints = isset($_GET['wordpoints']) ? intval($_GET['wordpoints']) : false;

		$grid_words = new grid_words();
		$grid_words->create($user->id, $gameid, $gridid, $word, $wordexists, $wordpoints);

		die();
	}
}

?>