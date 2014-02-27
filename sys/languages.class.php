<?php

class lang
{
	private $data;
	const LANGUAGES = 'ROOT/languages/';

	public function __construct()
	{
		$api = api::getInstance();
		$lang = array();
		$language = $this->get_language();
		include($api->filefmt(self::LANGUAGES) . 'lang.' . $language . '.php');
		$this->data = $lang ? $lang : array();
	}

	public function __destruct()
	{
		unset($this->data);
	}

	public function get_language()
	{
		$api = api::getInstance();
		return $GLOBALS['user']->userlang; //preg_match('#^fr#i', $_SERVER['HTTP_ACCEPT_LANGUAGE']) && file_exists($api->filefmt(self::LANGUAGES) . 'lang.it.php') ? 'it' : 'en';
	}

	public function get($key)
	{
		return $key && isset($this->data[$key]) ? $this->data[$key] : (string) $key;
	}
}

?>