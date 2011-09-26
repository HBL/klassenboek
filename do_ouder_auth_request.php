<? include('include/init.php');
check_login();
require_once('include/recaptcha.php');
//if (!recaptcha_verify()) {
//	$_SESSION['errormsg'] = 'Om een kind toe te voegen moet u '.
//		'de CAPTCHA goed oplossen.';
//	header('Location: ouder_auth_request.php'.sprint_url_parms($_POST));
//	exit;
//}
if ($_SESSION['type'] != 'ouder') throw new Exception('alleen toegankelijk voor ouders', 2);
check_isset_array($_POST, 'login');
check_required_POST('ouder_auth.php', 'login');

$row = mysql_fetch_array(mysql_query_safe(
	"SELECT KB_NAAM(lln.naam0, lln.naam1, lln.naam2) lln_naam, lln.email lln_email, lln.ppl_id ".
	", grp.grp_id, doc.email doc_email, ".
	"CONCAT(KB_NAAM(doc.naam0, doc.naam1, doc.naam2), ' (', doc.login, ')')  doc_naam, ".
	"SUBSTRING(SHA1(CONCAT(doc.ppl_id, lln.ppl_id, '$pw_reset_secret')), 1, 16) code ".
	"FROM ppl AS doc, ppl AS lln , doc2grp2vak, grp2vak, grp ".
	"WHERE doc.ppl_id = '${_SESSION['ppl_id']}' ".
	"AND doc2grp2vak.ppl_id = doc.ppl_id ".
	"AND doc2grp2vak.grp2vak_id = grp2vak.grp2vak_id ".
	"AND grp.grp_id = grp2vak.grp_id ".
	"AND doc.type = 'ouder' ".
	"AND lln.type = 'leerling' ".
	"AND lln.login = '%s' ".
	"AND lln.active IS NOT NULL", htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8')), MYSQL_ASSOC);

if (!$row) regular_error('ouder_auth_request.php', $_POST, "Leerling ".htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8')." is onbekend. Controleer of u het leerlingnummer goed hebt ingetoetst.");

$result = mysql_query_safe("SELECT ppl_id FROM ppl2grp JOIN grp USING (grp_id) WHERE schooljaar = '$schooljaar' AND ppl_id = '${row['ppl_id']}' AND grp_id = '${row['grp_id']}'");

if (mysql_numrows($result)) regular_error($http_path.'/', (array) NULL, "U hebt al toegang tot het klassenboek van ${row['lln_naam']}.");

if (!filter_var($row['lln_email'], FILTER_VALIDATE_EMAIL)) regular_error('ouder_auth_request.php',
	$_POST, 
	'Leerling '.htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8').' heeft nog geen geldig emailadres ingevoerd. Hij/zij moet nog een account aanmaken. De benodigde aanmaakcode is verkrijgbaar bij de mentor, een vakdocent of de beheerder, '.htmlspecialchars($beheerder).'.');

mail(htmlspecialchars_decode($row['lln_naam'].' <'.$row['lln_email'].'>', ENT_QUOTES),
	'onlineklassenboek.nl: Ouder Akkoord link',
"Beste ".htmlspecialchars_decode($row['lln_naam'], ENT_QUOTES).",

Zojuist heeft een ouder/verzorger van jou, ".htmlspecialchars_decode($row['doc_naam'], ENT_QUOTES).",
met emailadres ".htmlspecialchars_decode($row['doc_email'], ENT_QUOTES)." aangegeven
toegang te willen tot jouw online klassenboek. Controleer of dit verzoek
echt is gedaan door een ouder/verzorger van jou.

Volg de onderstaande link om toegang te verlenen of te weigeren.

https://$http_server$http_path/ouder_auth.php?kind_id=${row['ppl_id']}&ouder_id=${_SESSION['ppl_id']}&ouder_auth_code=${row['code']}

Als de bovenstaande persoon niet je ouder/verzorger is, hoef je niets te doen.

Met vriendelijke groeten,

".$beheerder."\n", 'From: '.$beheerder."\r\n");

mysql_log('do_ouder_auth_request_success', "verzonden naar ${row['lln_naam']} door ${_SESSION['name']}");
$_SESSION['successmsg'] = "Email verzonden naar leerling ".htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8')." met een goedkeuringsverzoek. Mocht u problemen ondervinden met het verkrijgen van toegang, dan kunt u zich wenden tot de beheerder, ".htmlspecialchars($beheerder).".";
header('Location: '.$http_path.'/');
?>
