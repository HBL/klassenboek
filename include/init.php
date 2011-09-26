<?  
require_once('config.php');
// info about session lifetimes from
// http://www.captain.at/howto-php-sessions.php
$timeout = 12600; // overbrugging Kerstvakantie
ini_set('session.gc_maxlifetime', $timeout + 600);
session_set_cookie_params(0, $cookie_path);
//$session_dir = ini_get('session.save_path').'/'.$session_subdir;
//if (!is_dir($session_dir)) mkdir($session_dir, 1777);
//ini_set('session.save_path', $session_dir);
session_start();
require_once('database.php');
require_once('sprint.php');
require_once('dom.php');
require_once('html.php');
require_once('status.php');
require_once('functions.php');
require_once('bbcode.php');
require_once('teletop_lib.php');
require_once('error.php');
set_exception_handler('exception_handler');
$schooljaar = substr($schooljaar_long, 2, 2).substr($schooljaar_long, 7, 2);
$vorig_schooljaar = substr($vorig_schooljaar_long, 2, 2).substr($vorig_schooljaar_long, 7, 2);
$aantal_lesweken = count($lesweken);
$aantal_weken_in_kalenderjaar = date('W',
	mktime(0, 0, 0, 12, 28, substr($schooljaar_long, 0, 4)));
$load_time = time();
$timeouts =  array(1 => '1 minuut', 5 => '5 minuten', 10 => '10 minuten',
	60 => '1 uur', 120 => '2 uur', 600 => '10 uur');
?>
