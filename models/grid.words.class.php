<?php

class grid_words
{
	public function create($userid, $gameid, $gridid, $word, $wordexists, $wordpoints)
	{
		$db = db::getInstance();

		$sql = 'INSERT INTO gamesuserswords
					(userid, gameid, gridid, word, wordexists, wordpoints)
					VALUES (' .
						intval($userid) . ', ' .
						intval($gameid) . ', ' .
						intval($gridid) . ', ' .
						$db->escape((string) $word) . ', ' .
						intval($wordexists) . ', ' .
						intval($wordpoints) . '
					)';
		$db->query($sql);

		return true;
	}
}

?>