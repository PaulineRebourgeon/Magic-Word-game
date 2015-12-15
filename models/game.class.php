<?php

class game
{
	public $userids = array();
	public $gameid = 0;
	public $gametype = 0;
	public $gamelang = '';
	public $gamefinished = 0;

	public function create($userids, $gametype, $gamelang)
	{
		$db = db::getInstance();

		$this->gametype = intval($gametype);
		$this->gamelang = (string) $gamelang;
		$this->gamefinished = 0;

		$sql = 'INSERT INTO games
					(gamelang, gametype, gamefinished)
					VALUES (' . $db->escape((string) $this->gamelang) . ', ' . intval($this->gametype) . ', ' . intval($this->gamefinished) . ')';
		$db->query($sql);
		$this->gameid = $db->next_id();

		if ( $userids )
		{
			$sql = 'SELECT userid, username
						FROM users
						WHERE userid IN(' . implode(', ', array_map('intval', $userids)) . ')';
			$userids = array();
			$result = $db->query($sql);
			while ( ($row = $result->fetch_assoc()) )
			{
				$userids[ intval($row['userid']) ] = $row['username'];
			}
		}

		// créer la liste des utilisateurs affectés à la partie
		$this->userids = $userids;
		if ( $this->userids )
		{
			foreach ( $this->userids as $userid => $username )
			{
				$sql = 'INSERT INTO gamesusers
							(gameid, userid, request)
							VALUES (' .
								intval($this->gameid) . ', ' .
								intval($userid) . ',
								0)';
				$db->query($sql);
			}
		}

		return $this->gameid;
	}

	// affecter une grille à une partie pour tous les utilisateurs
	public function assign_grid($gridid)
	{
		$db = db::getInstance();

		foreach ( $this->userids as $userid => $username )
		{
			$sql = 'INSERT INTO gamesstatus
						(gameid, userid, gridid, gridstatus)
						VALUES (' . intval($this->gameid) . ', ' . intval($userid) . ', ' . intval($gridid) . ', ' . intval(GRIDSTATUS_ASSIGNED) . ')';
			$db->query($sql);
		}
		return true;
	}

	public function start_grid($gridid)
	{
		$db = db::getInstance();
		$user = user::getInstance();

		$sql = 'UPDATE gamesstatus
					SET gridstatus = ' . intval(GRIDSTATUS_STARTED) . '
					WHERE gameid = ' . intval($this->gameid) . '
						AND gridid = ' . intval($gridid) . '
						AND userid = ' . intval($user->id) . '
						AND gridstatus = ' . intval(GRIDSTATUS_ASSIGNED);
		$db->query($sql);
	}

	public function read($gameid)
	{
		$db = db::getInstance();

		$this->userids = array();
		$this->gameid = 0;
		$this->gametype = 0;
		$this->gamelang = '';
		$this->gamefinished = 0;

		// lecture de l'entête
		$sql = 'SELECT *
					FROM games
					WHERE gameid = ' . intval($gameid);
		$result = $db->query($sql);
		if ( !($row = $result->fetch_assoc()) )
		{
			return false;
		}
		$this->gameid = intval($row['gameid']);
		$this->gametype = intval($row['gametype']);
		$this->gamelang = intval($row['gamelang']);
		$this->gamefinished = intval($row['gamefinished']);

		// lecture des utilisateurs
		$sql = 'SELECT gu.*, u.username
					FROM gamesusers gu, users u
					WHERE gu.gameid = ' . intval($gameid) . '
						AND u.userid = gu.userid';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$this->userids[ intval($row['userid']) ] = $row['username'];
		}
		return true;
	}

}

?>