<?php

class members {

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

    }

    private function validate()
    {

    }

    private function display()
    {
        $db = db::getInstance();
		$user = user::getInstance();

        $data = array();
        $sql = 'SELECT *
                    FROM users
                    ORDER BY username';
        $result = $db->query($sql);
        while ( ($row = $result->fetch_assoc()) )
        {
			$row['_is_online'] = $row['useronline'] >= intval(time() - user::SESSION_DURATION);
            $data[] = $row;
        }
        $result->free();

        include('./views/members.html');
        return true;
    }

}

?>