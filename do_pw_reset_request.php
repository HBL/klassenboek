<? require("include/init.php");
check_nologin();
require_once('include/recaptcha.php');
if (!recaptcha_verify()) {
	$_SESSION['errormsg'] = 'Om je wachtwoord te resetten moet je '.
		'de CAPTCHA goed oplossen.';
	header('Location: pw_reset_request.php'.sprint_url_parms($_POST));
	exit;
}
check_isset_array($_POST, 'userid');
check_required_POST('pw_reset_request.php', 'userid');

$userid = htmlspecialchars($_POST['userid'], ENT_QUOTES, 'UTF-8');

$result = mysql_query_safe(
	"SELECT password, email, KB_NAAM(naam0, naam1, naam2) naam, ".
	"SUBSTRING(SHA1(CONCAT(ppl.pw_reset_count, ppl.ppl_id, ".
	"'$pw_reset_secret')), 1, 16) code ".
	"FROM ppl WHERE login = '%s' AND active IS NOT NULL",
	mysql_escape_safe($userid));

// als de gebruikersnaam niet bestaat: doe niets, we geven externen hier geen
// informatie over bestaande gebruikersnamen
if (($row = mysql_fetch_array($result)) &&
	filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
	mail(htmlspecialchars_decode($row['naam'].' <'.$row['email'].">", ENT_QUOTES),
			"link om een nieuw wachtwoord in te stellen", 
"Beste ".htmlspecialchars_decode(mysql_result($result, 0, 'naam'), ENT_QUOTES).", 

Iemand (mogelijk jijzelf) heeft vanaf ip adres ${_SERVER['REMOTE_ADDR']} een
link aangevraagd om het wachtwoord van jouw account op
$http_server$http_path met gebruikersnaam ${_POST['userid']} te wijzigen.

Volg deze link als je een nieuw wachtwoord voor jezelf wilt invullen:

https://$http_server$http_path/pw_reset.php?userid=".urlencode($_POST['userid'])."&pw_reset_code=${row['code']}

Mocht je veel van deze berichten krijgen, terwijl je er zelf niet om
hebt gevraagd, stel mij op de hoogte. In veel gevallen kan ik achterhalen
wie de boosdoener is en maatregelen nemen.

Met vriendelijke groeten,

".$beheerder."\n", 'From: '.$beheerder."\r\n");
mysql_log('pw_reset_code_request',
		"reset code verzonden naar ${row['email']} $userid");
}
$_SESSION['successmsg'] = "Als gebruiker <code>$userid</code> ".
	'bestaat en hij/zij heeft een geldig emailadres dan is de email met '.
	'de link om je wachtwoord te veranderen verzonden. Als je het '.
	'bericht niet hebt gehad, en je weet zeker dat je je gebruikersnaam '.
	'goed hebt ingevuld, controleer dan of het bericht in naar je '.
	'spamfolder is gegaan.';
header("Location: index.php");
exit; ?>
