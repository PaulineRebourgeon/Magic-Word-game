<?php

class wordbox_display
{
	private $mode = '';

	private $wordboxwords = array();

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
		$user = user::getInstance();

		include('./models/wordbox.class.php');

		$wordbox = new wordbox();
		$wordbox->read($this->wordboxid = 9);
		return false;
	}

	private function validate()
	{
		return false;
	}

	private function display()
	{
		$db = db::getInstance();
		$user = user::getInstance();

        $data = array();
		$sql = 'SELECT *
					FROM wordbox
					WHERE userid = ' . intval($user->id) . '
						AND wordboxlang = ' . $db->escape((string) $user->get_lang()) . '
					ORDER BY wordboxword';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$data[] = $row;
		}
        $result->free();


		include('./views/wordbox.html');
        return true;
	}
}

?>