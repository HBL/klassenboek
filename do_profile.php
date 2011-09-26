<? include("include/init.php");
check_login();
check_isset_array($_POST, 'email', 'new_pw0', 'new_pw1', 'password', 'timeout');
check_required_POST('profile.php', 'email', 'password');
check_email_POST('profile.php');
$query = sprintf("UPDATE ppl SET email='%s'",
	mysql_escape_safe(htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8')));
if ($_POST['timeout'] != '') {
	if (!$timeouts[$_POST['timeout']]) throw new Exception('ongeldige waarde voor timeout', 2);
	$query .= sprintf(", timeout = '%s'", mysql_escape_safe($_POST['timeout']));	
	if ($_SESSION['ppl_id'] == $_SESSION['orig_ppl_id']) $_SESSION['timeout'] = $_POST['timeout'];
}
if ($_POST['new_pw0'] != $_POST['new_pw1']) regular_error('profile.php',
	$_POST, 'De vorige poging om het profiel op te slaan met een '.
	'nieuw wachtwoord is mislukt. Dit komt doordat de twee invoervelden '.
	'met het nieuwe wachtwoord niet gelijk aan elkaar waren. Zorg ervoor '.
	'dat deze velden exact dezelfde text bevatten.');
else if ($_POST['new_pw0'] != '') {
	$query .= sprintf(", password=PASSWORD('%s')", 
		mysql_escape_safe($_POST['new_pw0']));
}
$query .= " WHERE ppl_id='${_SESSION['ppl_id']}' AND active IS NOT NULL";
if ($_SESSION['beheer'] != 1) 
	$query .= sprintf(" AND password=PASSWORD('%s')",
		mysql_escape_safe($_POST['password']));
mysql_query_safe($query);
if (!mysql_affected_rows()) regular_error('profile.php', $_POST,
	'De vorige poging om het profiel op te slaan is mislukt. '.
	'Het ingevoerde huidige wachtwoord is fout of er waren geen '.
	'wijzigingen om op te slaan.');

if (!not_teletop_credentials() && $_POST['new_pw0'] != '') {
	$td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
	mcrypt_generic_init($td, $_POST['new_pw0'], 'TeleTop&reg;01234567890123456789');
	$plain_teletop_password =  $_SESSION['teletop_password'];

        $password = mcrypt_generic($td, $plain_teletop_password);
        $safe_password = mysql_real_escape_string($password);
        $safe_username = mysql_real_escape_string($_SESSION['teletop_username']);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

	mysql_query_safe("INSERT INTO ppl2teletop (ppl_id, teletop_username, ".
		"teletop_password ) VALUES ( '{$_SESSION['ppl_id']}', '%s', '%s' ) ".
		"ON DUPLICATE KEY UPDATE teletop_username = '%s', ".
		"teletop_password = '%s'",
		$safe_username, $safe_password, $safe_username, $safe_password);
}

$_SESSION['successmsg'] = 'Het profiel is opgeslagen';
mysql_log('profile_success');
header('Location: profile.php');
?>
