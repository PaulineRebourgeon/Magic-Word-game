<?php

class wordbox
{
	public $userids = 0;

	public $userid = 0;
	public $wordboxword = '';
	public $wordboxstatus = 0;
	public $wordboxlang = '';

	public $wordbox_added_msg = '';

	public function create($userids, $wordboxword, $wordboxstatus)
	{
		$db = db::getInstance();
		$user = user::getInstance();

		$this->userids = $userids;
		$this->wordboxlang = $user->get_lang();

		$this->wordboxword = $wordboxword;
		$word_exists = true;

		$this->wordboxstatus = $wordboxstatus;

		$word_exists = $this->check_wordboxword();

		if ( $word_exists === false )
		{

			// ajout du mot dans la table
			foreach ( $this->userids as $userid )
			{
				$sql = 'INSERT INTO wordbox
							(userid, wordboxword, wordboxstatus, wordboxlang)
							VALUES (' .
									intval($userid) . ', ' .
									$db->escape((string) $this->wordboxword) . ', ' .
									intval($this->wordboxstatus) . ', '.
									$db->escape((string) $this->wordboxlang) .')';
				$db->query($sql);
			}
			if ( $user->get_lang() == 'en' )
			{
				$msg = ' has been added to your wordbox.';
			}
			if ( $user->get_lang() == 'it' )
			{
				$msg = ' é stata aggiunta nel tuo Wordbox';
			}
		}
		if ( $word_exists === true )
		{
			if ( $user->get_lang() == 'en' )
			{
				$msg = ' is already in your Wordbox!';
			}
			if ( $user->get_lang() == 'it' )
			{
				$msg = ' é già presente nel tuo Wordbox!';
			}
		}
		$this->wordbox_added_msg = $this->wordboxword . $msg;
	}

	private function check_wordboxword()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		$sql = 'SELECT *
					FROM wordbox
					WHERE userid = ' .  intval($user->id) . '
						AND wordboxword = ' . $db->escape((string) $this->wordboxword) . '
					LIMIT 1';
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		$result->free();

		if( empty($row['wordboxword']) )
		{
			return false;
		}
		return true;

	}

		public function read()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		$sql = 'SELECT * FROM wordbox
					ORDER BY wordboxword
					WHERE userid = ' . intval($user->id) . '
						AND wordboxlang = ' . $db->escape((string) $this->wordboxlang);
		$db->query($sql);
	}
}
?>
