<? include('include/init.php');
check_login();

//header("Content-type: text/plain");
//print_r($_POST);

//if ($_SESSION['type'] != 'personeel') regular_error($http_server.'/', (array) NULL,
//	'De gevraagde pagina is alleen toegankelijk voor personeel');

$result = mysql_query_safe("SELECT * FROM ppl WHERE ppl_id = '{$_SESSION['ppl_id']}' AND password = PASSWORD('%s')",
	mysql_escape_safe($_POST['password']));

if (!mysql_numrows($result)) regular_error('teletop.php', (array) NULL, 'wachtwoord onjuist');
mysql_free_result($result);


if ($_POST['action'] != 'Opslaan' && $_POST['action'] != 'Verwijderen')
	regular_error('teletop.php', (array) NULL, 'onzinnige waarde van action');

if ($_POST['action'] == 'Verwijderen') {
	mysql_query_safe("DELETE FROM ppl2teletop WHERE ppl_id = {$_SESSION['ppl_id']}");
	$_SESSION['successmsg'] = "TeleTOP&reg; credentials gewist";
} else {
	if (!isset($_POST['ttusr']) || !$_POST['ttusr'] || $_POST['ttusr'] == '')
		regular_error('teletop.php', (array) NULL, 'TeleTOP&reg; username mag niet leeg zijn');

	$safe_username = mysql_escape_safe($_POST['ttusr']);

	if (isset($_POST['new_pw0']) && $_POST['new_pw0'] && $_POST['new_pw0'] != '') {
		$td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
		mcrypt_generic_init($td, $_POST['password'], 'TeleTop&reg;01234567890123456789');
		$plain_teletop_password =  $_POST['new_pw0'];
		if (get_magic_quotes_gpc())
			$plain_teletop_password = stripslashes($plain_teletop_password);

		$password = mcrypt_generic($td, $plain_teletop_password);
		$safe_password = mysql_real_escape_string($password);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		$_SESSION['teletop_password'] = $plain_teletop_password;

		mysql_query_safe("INSERT INTO ppl2teletop (ppl_id, teletop_username, ".
			"teletop_password ) VALUES ( '{$_SESSION['ppl_id']}', '%s', '%s' ) ".
 			"ON DUPLICATE KEY UPDATE teletop_username = '%s', ".
 			"teletop_password = '%s'",
 			$safe_username, $safe_password, $safe_username, $safe_password);
	} else { // update alleen de username
		mysql_query_safe("INSERT INTO ppl2teletop (ppl_id, teletop_username ) ".
			"VALUES ( '{$_SESSION['ppl_id']}', '%s' ) ".
 			"ON DUPLICATE KEY UPDATE teletop_username = '%s' ",
 			$safe_username, $safe_username);
	}
	$_SESSION['teletop_username'] = $_POST['ttusr'];

	if ($_SESSION['type'] == 'personeel') {

	//header("Content-type: text/plain");
	//print_r($_POST);
	for ($i = 0; $i < count($_POST['grp2vak_id']); $i++) {
		if (!verify_grp2vak($_POST['grp2vak_id'][$i])) continue;
		if (!$_POST['vaksite_id'][$i] || $_POST['vaksite_id'][$i] == '')
			mysql_query_safe("DELETE FROM grp2vak2vaksite WHERE grp2vak_id = '%s'", mysql_escape_safe($_POST['grp2vak_id'][$i]));
		else mysql_query_safe("INSERT INTO grp2vak2vaksite ( grp2vak_id, vaksite_id ) VALUES ( '%s', '%s' ) ON DUPLICATE KEY UPDATE vaksite_id = '%s'",
			mysql_escape_safe($_POST['grp2vak_id'][$i]), mysql_escape_safe($_POST['vaksite_id'][$i]), mysql_escape_safe($_POST['vaksite_id'][$i]));
	}

	}

	unset($_SESSION['teletop_session']);
	$ch = curl_teletop_init();
	curl_teletop_req($ch, '/tt/abvo/lms.nsf/f-MyCourses?OpenForm');
	$_SESSION['successmsg'] = 'TeleTOP&reg; credentials geupdate.';
}


header("Location: teletop.php");
?>
