<?php

class edit
{
    private $submit = false;
    private $userid = 0;
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
        $db = db::getInstance();
        $user = user::getInstance();

		$this->userlang = $user->get_lang();

        // initialisation
        $this->submit = isset($_POST['submit_form']);

        $this->userid = ($this->mode == 'profile') ? intval($_SESSION['userid']) : (isset($_REQUEST['userid']) && intval($_REQUEST['userid']) ? intval($_REQUEST['userid']) : 0 );

        // recherche dans la BDD
        $sql = 'SELECT *
                    FROM users
                    WHERE userid = ' . intval($this->userid);
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $result->free();
        if ( !$row )
        {
            die('Game over.');
        }
        $this->username = $row['username'];
        $this->useremail = $row['useremail'];

        // recherche sur le formulaire
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
            $this->errors[] = 'Vous devez entrer un nom d\'utilisateur';
        }
        if ( empty($this->useremail) )
        {
            $this->errors[] = 'Vous devez entrer une adresse e-mail';
        }
        if ( empty($this->userlang) )
        {
            $this->errors[] = 'Vous devez choisir une langue';
        }
        // Vérification de l'unicité
        if ( !$this->errors )
        {
            $mailvalid = true;
            if ( !filter_var($this->useremail, FILTER_VALIDATE_EMAIL) )
            {
                $this->errors[] = 'e-mail non valide';
                $mailvalid = false;
            }
            if ( $this->password )
            {
                if ( $this->password != $this->password_confirm )
                {
                    $this->errors[] = 'Les mots de passe ne correspondent pas';
                }
            }
            $sql = 'SELECT *
                        FROM users
                        WHERE (username = ' . $db->escape((string) $this->username) . (!$mailvalid ? '' : '
                            OR useremail = ' . $db->escape((string) $this->useremail) ) . ')
                            AND userid <> ' . intval($this->userid);
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            $result->free();
            if ($row)
            {
                $this->errors[] = 'Cet utilisateur existe déjà';
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
        $sql = 'UPDATE users
                    SET
                        username = ' . $db->escape((string) $this->username) . ',
                        useremail = ' . $db->escape((string) $this->useremail) . (!$this->password ? '' : ',
                        userpasswd = ' . $db->escape((string) md5($this->password))) . ',
                        userlang = ' . $db->escape((string) $this->userlang) . '
                    WHERE userid = ' . intval($this->userid);
        $db->query($sql);
        // Mise à jour de la bdd
        redirect('');
    }

    private function display()
    {
        include('./views/edit.form.html');
        return true;
    }
}

?>