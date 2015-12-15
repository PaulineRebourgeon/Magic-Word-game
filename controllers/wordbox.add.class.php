<?php

class wordbox_add
{
	private $mode = '';

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		$user = user::getInstance();

		include('./models/wordbox.class.php');
		$userids = array($user->id);

		if ( isset($_GET["word"]) ) {
			$wordboxword = $_GET["word"];
			$wordboxstatus = $_GET["status"];
			$wordbox = new wordbox();
			$wordbox->create($userids, $wordboxword, $wordboxstatus);
		}

		$msg = $wordbox->wordbox_added_msg;
		echo $msg;
		return true;
	}
}

?>