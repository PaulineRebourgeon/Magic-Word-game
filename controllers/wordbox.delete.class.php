<?php

$db = db::getInstance();

if (isset($_GET["w1"])) {
	$word = $db->escape((string) $_GET["w1"]);

	$sql = 'DELETE
				FROM wordbox
				WHERE wordboxword = ' . $word;
	$db->query($sql);
}
die();

?>