<?php

class db
{
    private static $_instance = null;
    private $handler;
    public static function getInstance()
    {
        if ( !self::$_instance )
        {
            $class = __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }
    private function __construct()
    {
        $this->connect();
    }
    private function __clone() {}

    public function connect()
    {
        $dbhost = $dbuser = $dbpasswd = $dbname = '';
        include(realpath(dirname(__FILE__) . '/db.config.php'));
        $this->handler = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);
		if ( $this->handler->connect_error )
		{
			die('Connect error::' . $this->handler->connect_errno . '::'. $this->handler->connect_error);
		}
        if ( $this->handler->query('SET NAMES \'utf8\'') === false )
		{
			die('Error::' . $this->handler->errno . '::' . $this->handler->error);
		}
    }

    public function query($sql)
    {
		return $this->handler->query($sql);
    }

    public function next_id()
    {
        return $this->handler->insert_id;
    }

    public function affected_rows()
    {
		return $this->handler->affected_rows;
    }

    public function escape($str)
    {
        return is_string($str) ? '\'' . $this->handler->real_escape_string($str) . '\'' : intval($str);
    }
}
?>