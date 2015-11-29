<?php

define('GAMETYPE_BATTLE', 0);
define('GAMETYPE_PRACTICE_FULL', 1);
define('GAMETYPE_PRACTICE_ALLWORDS', 2);
define('GAMETYPE_PRACTICE_LONGEST', 3);
define('GAMETYPE_PRACTICE_CONSTRAINTS', 4);

define('GRIDTYPE_ALLWORDS', 0);
define('GRIDTYPE_LONGEST', 1);
define('GRIDTYPE_CONSTRAINTS', 2);

define('GRIDSTATUS_ASSIGNED', 0);
define('GRIDSTATUS_STARTED', 1);
define('GRIDSTATUS_FINISHED', 2);


session_start();
header('Content-Type: text/html; charset=UTF-8');
require('./sys/utils.func.php');
require('./sys/db.class.php');
require('./models/user.class.php');
require('./languages/language.php');

// Initialisation
if ( isset($_POST['cancel_form']) )
{
    redirect('');
}
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$user = user::getInstance();
if ( !$user->logged_in() && ($mode != 'register') )
{
    $mode = 'login';
}

$userlogged = $user->logged_in();

$modes = $mode ? explode('.', $mode) : array();
$modes = array_slice($modes, 0, 2);
$wmode = implode('.', $modes);

// Traitement des modes
$html = false;
switch ( $wmode )
{
    case 'login':
        include('controllers/login.class.php');
        $controller = new login();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
    case 'register':
        include('controllers/register.class.php');
        $controller = new register();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
    case 'logout':
		$user->set_logout();
        redirect('');
    break;
    case 'profile':
        include('controllers/edit.class.php');
        $controller = new edit();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
    case 'members':
        include('controllers/members.class.php');
        $controller = new members();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
	case 'infos':
        include('controllers/infos.class.php');
        $controller = new infos();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;

    case 'members.online':
        include('controllers/members.online.class.php');
        $controller = new members_online();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
    case 'members.online':
        include('controllers/members.online.class.php');
        $controller = new members_online();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;

    case 'game.launch':
        include('controllers/game.launch.controller.class.php');
        $controller = new game_launch_controller();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
	case 'game.result':
        include('controllers/game.result.class.php');
        $controller = new game_result();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
	case 'game.definitions':
        include('controllers/game.definitions.class.php');
        $controller = new game_definitions();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
	case 'game.word':
        include('controllers/game.word.class.php');
        $controller = new game_word();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;

	case 'wordbox.display':
        include('controllers/wordbox.display.class.php');
        $controller = new wordbox_display();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
	case 'wordbox.add':
        include('controllers/wordbox.add.class.php');
        $controller = new wordbox_add();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;
	case 'wordbox.definition':
        include('controllers/wordbox.definition.class.php');
    break;
	case 'wordbox.delete':
        include('controllers/wordbox.delete.class.php');
    break;

	case 'invitations.add':
        include('controllers/invitations.add.class.php');
        $controller = new invitations_add();
        $controller->set_mode($mode);
        $html = $controller->process();
    break;

    default:
        $html = true;
        include('./views/page.home.html');
        $mode = '';
}
// Affichage de la page
if ( !$html )
{
    include('./views/page.errors.html');
}

?>