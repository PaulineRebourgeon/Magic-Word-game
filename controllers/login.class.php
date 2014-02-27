<?php

class login
{
    private $submit = false;
    private $username='';
    private $password = '';
    private $errors = array();
    private $userid = 0;

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
        $this->submit = isset($_POST['submit_form']);
        if ( $this->submit )
        {
            $this->username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $this->password = isset($_POST['userpasswd']) ? trim($_POST['userpasswd']) : '';
        }
        return true;
    }

    private function check()
    {
        $db = db::getInstance();

        if ( !$this->submit )
        {
            return false;
        }

        if ( empty($this->username) )
        {
            $this->errors[] = 'You must enter a user name';
        }
        if ( empty($this->password) )
        {
            $this->errors[] = 'You must enter a password';
        }
        if ( !$this->errors )
        {
            $sql = 'SELECT *
                        FROM users
                        WHERE username = ' . $db->escape((string) $this->username);
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            $result->free();

            if ( !$row || (md5($this->password) != $row['userpasswd']) )
            {
                $this->errors[] = 'The user name or the password are not a match.';
            }
        }
        if ( !$this->errors )
        {
            $this->userid = intval($row['userid']);
        }
    }

    private function validate()
    {
        if ( !$this->submit || $this->errors )
        {
            return false;
        }

        $user = user::getInstance();
		$user->set_login($this->userid);
        redirect('');
    }

    private function display()
    {
        include('./views/login.form.html');
        return true;
    }
}

?>
