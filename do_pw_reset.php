<? require("include/init.php");
check_isset_array($_POST, 'userid', 'pw_reset_code', 'new_pw0', 'new_pw1');
check_isnonempty_array($_POST, 'userid', 'pw_reset_code');
check_required_POST('pw_reset.php', 'new_pw0', 'new_pw1');

if ($_POST['new_pw0'] != $_POST['new_pw1']) regular_error('pw_reset.php',
	$_POST, 'De twee ingevoerde nieuwe wachtwoorden zijn niet '.
	'gelijk aan elkaar.');

mysql_query_safe("UPDATE ppl SET password=PASSWORD('%s'), ".
	'pw_reset_count = pw_reset_count + 1 '.
	"WHERE login = '%s' ".
	"AND active IS NOT NULL ".
	"AND SUBSTRING(SHA1(CONCAT(ppl.pw_reset_count, ".
	"ppl.ppl_id, '$pw_reset_secret')), 1, 16) = '%s'",
	mysql_escape_safe($_POST['new_pw0']),
	mysql_escape_safe(htmlspecialchars($_POST['userid'], ENT_QUOTES, 'UTF-8')),
	mysql_escape_safe($_POST['pw_reset_code'])); 

if (!mysql_affected_rows()) regular_error('pw_reset.php', $_POST,
	'Wachtwoord Reset link is verouderd of onjuist. Stuur een mail '.
	'aan de beheerder '.htmlspecialchars($beheerder).', als het probleem '.
	'zich herhaalt.');

mysql_query_safe("DELETE FROM ppl2teletop WHERE ppl_id = ( SELECT ppl_id FROM ppl WHERE login = '%s' AND active IS NOT NULL ) ", mysql_escape_safe(htmlspecialchars($_POST['userid'], ENT_QUOTES, 'UTF-8')));
if (!mysql_affected_rows()) $_SESSION['successmsg'] = 'Je kunt nu je nieuwe wachtwoord gebruiken.';
else $_SESSION['successmsg'] = 'Je kunt je nieuwe wachtwoord gebruiken. Je TeleTOP&reg; logingegevens waren versleuteld met je oude wachtwoord, je moet deze gegevens opniuw invoeren, nadat je bent ingelogd';

header("Location: $http_path/"); ?>
