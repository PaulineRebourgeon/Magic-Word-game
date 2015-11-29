<?php

class result
{
	public $userids = array();
	public $resultid = 0;
	public $resultwords = 0;
	public $resultpoints = 0;

	public function create($userids, $resultwords, $resultpoints)
	{
		$db = db::getInstance();

		$this->resultwords = intval($resultwords);
		$this->resultpoints = intval($resultpoints);

		$sql = 'INSERT INTO results
					(resultwords, resultpoints)
					VALUES (' . intval($this->resultwords) . ', ' . intval($this->resultpoints) . ')';
		$db->query($sql);
		$this->resultid = $db->next_id();

		$this->userids = $userids;
		foreach ( $this->userids as $userid )
		{
			$sql = 'INSERT INTO resultsusers
						(resultid, userid)
						VALUES (' . intval($this->resultid) . ', ' . intval($userid) . ')';
			$db->query($sql);
		}


		return true;
	}

	public function read()
	{
		$db = db::getInstance();

		$sql = 'SELECT * FROM results
					ORDER BY resultid
					DESC LIMIT 1';
		$db->query($sql);
	}

}

?>