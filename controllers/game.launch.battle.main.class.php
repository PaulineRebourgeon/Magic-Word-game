<?php

class game_launch_battle
{
	private $mode = '';
	private $gameid = 0;

	public function set_mode($mode)
	{
		$this->mode = $mode;
	}

	public function process()
	{
		$user = user::getInstance();
		$db = db::getInstance();

		include('./models/game.class.php');

		/*// fermer toute grille en cours pour cet utilisateur
		$sql = 'UPDATE gamesstatus
					SET gridstatus = ' . intval(GRIDSTATUS_FINISHED) . '
					WHERE userid = ' . intval($user->id) . '
						AND gridstatus = ' . intval(GRIDSTATUS_STARTED);
		$db->query($sql);*/

		// lire l'invitation
		$invitid = isset($_REQUEST['invitid']) ? intval($_REQUEST['invitid']) : 0;
		$gameid = isset($_REQUEST['gameid']) ? intval($_REQUEST['gameid']) : 0;

		// lire sur invitid
		if ( $invitid )
		{
			$sql = 'SELECT *
						FROM invitations
						WHERE invitid = ' . intval($invitid);
			$result = $db->query($sql);
			$data = ($row = $result->fetch_assoc()) ? $row : false;
			if ( $data === false )
			{
				trigger_error('No invitation', E_USER_ERROR);
			}

			// invitation reçue
			if ( intval($data['touserid']) === $user->id )
			{
				$gametype = GAMETYPE_BATTLE;

				$userids = array($user->id, intval($row['fromuserid']));
				$game = new game();
				$this->gameid = $game->create($userids, $gametype, $user->get_lang());

				$sql = 'UPDATE invitations
							SET gameid = ' . $this->gameid . '
							WHERE invitid = ' . intval($invitid);
				$db->query($sql);
			}
			else
			{
				$this->gameid = $row['gameid'];
				$sql = 'DELETE FROM invitations
							WHERE invitid = ' . intval($invitid);
				$db->query($sql);
			}
		}
		// lire la partie sur le gameid
		else
		{
			$this->gameid = intval($gameid);
			$game = new game();
			if ( !$game->read($this->gameid) )
			{
				trigger_error('No game!', E_USER_ERROR);
			}
		}

		// lire les grilles terminées pour trouver le prochain type de grille
		$grid_counts = array();
		$last_grid = 0;
		$sql = 'SELECT userid, COUNT(gridid) AS count_gridid
					FROM gamesstatus
					WHERE gameid = ' . intval($this->gameid) . '
						AND gridstatus = ' . intval(GRIDSTATUS_FINISHED) . '
					GROUP BY userid';
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$grid_counts[ intval($row['userid']) ] = intval($row['count_gridid']);
			if ( (intval($row['count_gridid']) > $last_grid) && (intval($row['userid']) != $user->id) )
			{
				$last_grid = intval($row['count_gridid']);
			}
		}
		if ( isset($grid_counts[$user->id]) )
		{
			if ( $grid_counts && ($last_grid < intval($grid_counts[$user->id])) )
			{
				redirect('game.result&gameid=' . intval($this->gameid));
				exit();
				trigger_error('Wait for the other!', E_USER_ERROR);
			}
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