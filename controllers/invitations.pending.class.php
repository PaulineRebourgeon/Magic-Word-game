<?php

class invitations_pending
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
		// clean invitations
	}

	private function validate()
	{
	}

	private function display()
	{
		$db = db::getInstance();
		$user = user::getInstance();

		// refuser l'invitation
		$deleteid = isset($_REQUEST['deleteid']) ? intval($_REQUEST['deleteid']) : false;
		$delete = isset($_REQUEST['delete']) ? intval($_REQUEST['delete']) : false;

		if ( $delete == 1 )
		{
			$sql = 'DELETE FROM invitations
						WHERE invitid = '. $deleteid;
			$db->query($sql);
		}

		// invitation reçues
		$awaitings = array();
		$sql = 'SELECT i.*, u.username
					FROM invitations i, users u
					WHERE u.userid = i.fromuserid
						AND i.touserid = ' . intval($user->id);
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$awaitings[] = $row;
		}

		// invitations envoyées
		$sents = array();
		$sql = 'SELECT i.*, u.username
					FROM invitations i, users u
					WHERE u.userid = i.touserid
						AND i.fromuserid = ' . intval($user->id);
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$sents[] = $row;
		}

		include('./views/invitations.pendings.html');
		return true;
	}
}


?>