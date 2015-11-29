<?php

class games_pending
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
	}

	private function validate()
	{
	}

	private function display()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		// on ferme les parties qui ne sont pas full game ou battle mode où l'utilisateur n'a pas terminé sa partie
		$pendings = array();
		$sql = 'SELECT gameid
					FROM gamesstatus
					WHERE gridstatus IN(' . intval(GRIDSTATUS_ASSIGNED) . ', ' . intval(GRIDSTATUS_STARTED) . ')
						AND gameid = gameid
						AND userid = ' . intval($user->id);
		$result = $db->query($sql);
		$result_grids = array();
		while ( ($row = $result->fetch_assoc()) )
		{
			$sql = 'UPDATE games g, gamesstatus gs
						SET g.gamefinished = 1,
						gs.gridstatus = ' .intval(GRIDSTATUS_FINISHED) .'
						WHERE g.gameid = ' . intval($row['gameid']) . '
						AND g.gametype > 1 ';
			$db->query($sql);
		}

		// on recherche toutes les parties assignées à l'utilisateur dont au moins un joueur n'a pas terminé une grille
		$pendings = array();
		$sql = 'SELECT gs1.gameid
					FROM gamesstatus gs1, gamesstatus gs2, games g
					WHERE gs2.gridstatus = ' . intval(GRIDSTATUS_STARTED) . '
						AND gs2.gameid = gs1.gameid
						AND gs2.gridid = gs1.gridid
						AND gs2.userid = ' . intval($user->id) . '
						AND g.gamelang = ' . $db->escape((string) $user->get_lang()) . '
					GROUP BY gs1.gameid';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$pendings[ (int) $row['gameid'] ] = true;
		}

		// on ajoute les parties de type full dont le nombre de grille n'est pas égale à 3
		$sql = 'SELECT gs.gameid, COUNT(gs.gridid) AS count_gridid
					FROM gamesstatus gs, games g
					WHERE g.gameid = gs.gameid
						AND g.gametype IN(' . intval(GAMETYPE_PRACTICE_FULL) . ', ' . intval(GAMETYPE_BATTLE) . ')
						AND g.gamelang = ' . $db->escape((string) $user->get_lang()) . '
						AND gs.userid = ' . intval($user->id) . '
						AND gs.gridstatus = ' . intval(GRIDSTATUS_FINISHED) . '
					GROUP BY gs.gameid
					HAVING count_gridid < 3';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$pendings[ (int) $row['gameid'] ] = true;
		}

		// lecture des infos de partie en cours à partir des pendings
		$battleids = array();
		if ( $pendings )
		{
			$sql = 'SELECT *
						FROM games
						WHERE gameid IN(' . implode(', ', array_map('intval', array_keys($pendings))) . ')
						ORDER BY gametype, gamelang, gameid DESC';
			$pendings = array();
			$result = $db->query($sql);
			while ( ($row = $result->fetch_assoc()) )
			{
				if ( !isset($pendings[ intval($row['gametype']) ]) )
				{
					$pendings[ intval($row['gametype']) ] = array();
				}
				if ( !isset($pendings[ intval($row['gametype']) ][ $row['gamelang'] ]) )
				{
					$pendings[ intval($row['gametype']) ][ $row['gamelang'] ] = array();
				}
				$pendings[ intval($row['gametype']) ][ $row['gamelang'] ][] = intval($row['gameid']);

				// stocker les id de battle pour rechercher le nom des adversaires
				if ( intval($row['gametype']) == GAMETYPE_BATTLE )
				{
					$battleids[ intval($row['gameid']) ] = true;
				}
			}
		}

		// get opponents
		$opponents = array();
		if ( $battleids )
		{
			$sql = 'SELECT gu.gameid, gu.userid, u.username
						FROM gamesusers gu, users u
						WHERE gu.gameid IN(' . implode(', ', array_map('intval', array_keys($battleids))) . ')
							AND gu.userid <> ' . intval($user->id) . '
							AND u.userid = gu.userid';
			$battleids = array();
			$result = $db->query($sql);
			while ( ($row = $result->fetch_assoc()) )
			{
				if ( !isset($opponents[ intval($row['gameid']) ]) )
				{
					$opponents[ intval($row['gameid']) ] = array();
				}
				$opponents[ intval($row['gameid']) ][ intval($row['userid']) ] = (string) $row['username'];
			};
		}

		include('./views/games.pendings.html');
		return true;
	}
}


?>