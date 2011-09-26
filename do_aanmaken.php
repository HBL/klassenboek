<? include("include/init.php");
check_nologin();
check_isset_array($_POST, 'userid', 'email', 'aanmaakcode');
check_required_POST('aanmaken.php', 'userid', 'email', 'aanmaakcode');
check_email_POST('aanmaken.php');

$userid = htmlspecialchars($_POST['userid'], ENT_QUOTES, 'UTF-8');

// verbeter veelgemaakte fouten in de aanmaakcode
$aanmaakcode = strtolower($_POST['aanmaakcode']);
$aanmaakcode = preg_replace('/o/', '0', $aanmaakcode);
$aanmaakcode = preg_replace('/l/', '1', $aanmaakcode);
$aanmaakcode = trim($aanmaakcode);

// vertel de gebruiker over een onmogelijke aanmaakcode
if (strlen($aanmaakcode) != 16) regular_error('aanmaken.php', $_POST, 'Een geldige aanmaakcode bestaat uit precies 16 tekens.');

mysql_query_safe("UPDATE ppl SET email='%s' WHERE login = '%s' ".
	"AND active IS NOT NULL AND password IS NULL AND ( ".
	"(type = 'leerling' AND SUBSTRING(SHA1(CONCAT(login,  '$aanmeld_secret')), 1, 16) = '%s') OR ". //FIXME remove
	"(type = 'leraar'   AND SUBSTRING(SHA1(CONCAT(oldid,  '$aanmeld_secret')), 1, 16) = '%s') OR ". //FIXME remove
	"                       SUBSTRING(SHA1(CONCAT(ppl_id, '$aanmeld_secret')), 1, 16) = '%s')",
		mysql_escape_safe(htmlspecialchars($_POST['email']), ENT_QUOTES, 'UTF-8'),
		mysql_escape_safe($userid),
		mysql_escape_safe($aanmaakcode), //FIXME remove
		mysql_escape_safe($aanmaakcode), //FIXME remove
		mysql_escape_safe($aanmaakcode));

if (!mysql_affected_rows()) {
	$result = mysql_query_safe("SELECT password FROM ppl ".
			"WHERE login = '%s' ".
			"AND active IS NOT NULL ".
			"AND ( ".
	"(type = 'leerling' AND SUBSTRING(SHA1(CONCAT(login,  '$aanmeld_secret')), 1, 16) = '%s') OR ". //FIXME remove
	"(type = 'leraar'   AND SUBSTRING(SHA1(CONCAT(oldid,  '$aanmeld_secret')), 1, 16) = '%s') OR ". //FIXME remove
	"                       SUBSTRING(SHA1(CONCAT(ppl_id, '$aanmeld_secret')), 1, 16) = '%s')", mysql_escape_safe($userid),
		mysql_escape_safe($aanmaakcode), //FIXME remove
		mysql_escape_safe($aanmaakcode), //FIXME remove
		mysql_escape_safe($aanmaakcode));

	if (!mysql_num_rows($result))
		regular_error('aanmaken.php', $_POST,
			'Ongeldige combinatie van '.
			'aanmaakcode en leerlingnummer/afkorting ingevuld');

	if (mysql_result($result, 0, 'password') != '')
		regular_error($http_path.'/', (array) NULL, "Account bestaat al, klik op 'Wachtwoord vergeten' als je niet kunt inloggen.");
}

$_SESSION['successmsg'] = 'Account aangemaakt. Je krijgt nu een email '.
	'toegestuurd. In die email staat hoe je een wachtwoord moet '.
	'instellen. Pas als je wachtwoord is ingesteld, is je account '.
	'geactiveerd. Als je het bericht niet hebt gehad, kijk in '.
	'je spamfolder of probeer een ander emailadres.';

$result = mysql_query_safe("SELECT password, email, ".
	"KB_NAAM(naam0, naam1, naam2) naam, ".
	"SUBSTRING(SHA1(CONCAT(ppl.pw_reset_count, ppl.ppl_id, ".
	"'$pw_reset_secret')), 1, 16) code ".
	"FROM ppl WHERE login = '%s' AND active IS NOT NULL",
	mysql_escape_safe($userid));

$code = mysql_result($result, 0, 'code');
mail(htmlspecialchars_decode(
	mysql_result($result, 0, 'naam').' <'.mysql_result($result, 0, 'email').'>', ENT_QUOTES),
	"wachtwoord instellen op $http_server$http_path",
"Beste ".htmlspecialchars_decode(mysql_result($result, 0, 'naam'), ENT_QUOTES).",

Je hebt je zojuist aangemeld op $http_server$http_path. Volg de onderstaande 
link, via die link kun je een wachtwoord instellen.

https://$http_server$http_path/pw_reset.php?userid=".urlencode($_POST['userid'])."&pw_reset_code=$code

Met vriendelijke groeten,

".$beheerder."\n", 'From: '.$beheerder."\r\n");
mysql_log('aanmaken_succes', "instelcode code verzonden naar ".mysql_result($result, 0, 'email')." $userid");
header("Location: aanmaken.php");

mysql_close(); ?>
