<?php
	$user = user::getInstance();
	$userlang = $user->get_lang();
	if ( $userlang )
	{
		include 'languages/lang.' . $userlang . '.php';
	}
?>