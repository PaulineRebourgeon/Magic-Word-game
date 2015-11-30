<?php

class register
{
    private $submit = false;
    private $username = '';
    private $useremail = '';
    private $password = '';
    private $password_confirm = '';
    private $userlang = '';
    private $errors = array();

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
            $this->useremail = isset($_POST['useremail']) ? trim($_POST['useremail']) : '';
            $this->password = isset($_POST['userpasswd']) ? trim($_POST['userpasswd']) : '';
            $this->password_confirm = isset($_POST['password_confirm']) ? trim($_POST['password_confirm']) : '';
            $this->userlang = isset($_POST['userlang']) ? trim($_POST['userlang']) : '';
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
            $this->errors[] = 'Please enter a user name';
        }
        if ( empty($this->useremail) )
        {
            $this->errors[] = 'Please enter an email address';
        }
        if ( empty($this->password) )
        {
            $this->errors[] = 'Please enter a password';
        }
        if ( empty($this->password_confirm) )
        {
            $this->errors[] = 'Please confirm your password';
        }
        if ( empty($this->userlang) )
        {
            $this->errors[] = 'Choose a language';
        }
        // Vérification de l'unicité
        if ( !$this->errors )
        {
            $mailvalid = true;
            if ( !filter_var($this->useremail, FILTER_VALIDATE_EMAIL) )
            {
                $this->errors[] = 'invalid email';
                $mailvalid = false;
            }
            if ( $this->password != $this->password_confirm )
            {
                $this->errors[] = 'The passwords do not match';
            }
            $sql = 'SELECT *
                        FROM users
                        WHERE username = ' . $db->escape((string) $this->username) . (!$mailvalid ? '' : '
                            OR useremail = ' . $db->escape((string) $this->useremail) );
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            $result->free();
            if ($row)
            {
                $this->errors[] = 'This username (or e-mail address) already exists';
            }
        }
    }

    private function validate()
    {
        $db = db::getInstance();

        if ( !$this->submit || $this->errors )
        {
            return false;
        }
        $sql = 'INSERT INTO users
                    (username, useremail, userpasswd, userlang)
					VALUES(' .
						$db->escape((string) $this->username) . ', ' .
						$db->escape((string) $this->useremail) . ', ' .
						$db->escape((string) md5($this->password)) . ', ' .
						$db->escape((string) $this->userlang) . ')';
        $db->query($sql);
        // Mise à jour de la bdd
        redirect('');
    }

    private function display()
    {
        include('./views/register.form.html');
        return true;
    }
}

?>
