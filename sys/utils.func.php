<?php

function redirect($mode)
{
    header('Location: ./index.php' . ($mode ? '?mode=' . $mode : ''));
    exit();
}

function _dump()
{
	$args = func_get_args();
	$message = count($args) == 1 ? $args[0] : $args;
	unset($args);

	$lang = array(
		'dbg_title' => '_dump() in <strong>%s</strong> on line <strong>%s</strong>',
		'dbg_empty' => 'Empty %s',
	);
	$title = false;
	unset($dbg);

	echo '<pre style="margin-top: 80px; background-color: #ffffff; color: #000000; border: 1px; border-style: outset; padding: 5px; text-align: left; overflow: auto; font: Arial; font-size: 12px;">' . ($title ? $title . '<br />' : '');
	if ( is_null($message) )
	{
		echo 'NULL';
	}
	else if ( is_bool($message) )
	{
		echo $message ? 'TRUE' : 'FALSE';
	}
	else if ( is_numeric($message) )
	{
		echo $message;
	}
	else if ( empty($message) )
	{
		echo sprintf($lang['dbg_empty'], gettype($message));
	}
	else if ( is_array($message) || is_object($message) || is_resource($message) )
	{
		ob_start();
		print_r($message);
		$content = ob_get_contents();
		ob_end_clean();
		echo htmlspecialchars($content);
	}
	else
	{
		echo str_replace("\t", '&nbsp; &nbsp;', htmlspecialchars($message));
	}
	echo '</pre>';
}

?>