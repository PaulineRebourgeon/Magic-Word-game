<?php

class wordbox_wordofday {

    private $mode = '';
	private $day = 0;
	private $current_day = 0;

	private $userlang = '';

	private $random_word = '';

    public function set_mode($mode)
    {
        $this->mode = $mode;
    }

    public function process()
    {
		$user = user::getInstance();

		$this->userlang = $user->get_lang();

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

    }

    private function validate()
    {

    }

    private function display()
    {
		$this->check_wordofday();

        include('./views/wordbox.wordofday.html');
        return true;
    }

	// appelle la fonction choose_random_word une fois par jour
	private function check_wordofday()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		$this->userlang = $user->get_lang();

		$sql = 'SELECT *
				FROM wordofday
				WHERE userid = ' . intval($user->id) . '
					AND wordofdaylang = ' . $db->escape((string) $this->userlang) . '
				ORDER BY wordofdaydate DESC
				LIMIT 1';
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		$this->day = intval($row['wordofdaydate']);
		$this->random_word = $row['wordofdayword'];
		$this->random_word = $this->random_word;

		$this->current_day = (int)date('Ymd');

		if ( $this->day != $this->current_day )
		{
			$this->choose_random_word();
			$this->day = $this->current_day;
		}
	}

	// choisis un mot au hasard parmi tous les mots de la wordbox de l'utilisateur
	private function choose_random_word()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		$sql = 'SELECT *
				FROM wordbox
				WHERE userid = ' . $user->id . '
					AND wordboxlang = ' . $db->escape((string) $this->userlang) . '
				ORDER BY RAND( )
				LIMIT 1';
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		$this->random_word = $row['wordboxword'];

		$this->insert_wordofday();
	}

	private function insert_wordofday()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		$sql= 'INSERT INTO wordofday
					(userid, wordofdayword, wordofdaydate, wordofdaylang)
					VALUES ( ' .
						intval($user->id) . ', ' .
						$db->escape((string) $this->random_word) . ', ' .
						intval($this->current_day)  . ', ' .
						$db->escape((string) $this->userlang) . ')';
		$db->query($sql);
	}
}

?>