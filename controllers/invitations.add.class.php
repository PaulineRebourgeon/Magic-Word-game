<?php

class invitations_add
{
	private $mode = '';

	private $fromuserid = 0;
	private $touserid = 0;
	private $invittime = 0;

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
		$db = db::getInstance();
		$user = user::getInstance();

		$this->fromuserid = intval($user->id);
		$this->touserid = isset ($_GET['touserid']) ? intval($_GET['touserid']) : false;
		$this->invittime = time();

		$sql = 'INSERT INTO invitations
					(fromuserid, touserid, invittime)
					VALUES (' . intval($this->fromuserid) . ' , ' .
						intval($this->touserid) . ', ' .
						intval($this->invittime) . ')';
		$db->query($sql);
		return false;
	}

	private function display()
	{
        redirect('');
        return true;
	}
}

?>