<? include('include/init.php');
check_nologin();
require_once('include/recaptcha.php');
//if (!recaptcha_verify()) regular_error('ouders.php', $_POST, 
//	'Om een ouderaccount aan te maken moet je de CAPTCHA goed oplossen.');

check_isset_array($_POST, 'login', 'naam0', 'naam1', 'naam2', 'email');
check_required_POST('ouders.php', 'login', 'naam0', 'naam1', 'email');

$login = htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8');
if (strlen($login) > 32)
	regular_error('ouders.php', $_POST, 'Een zelfgekozen '.
		'gebruikersnaam mag, na vervanging van &lt;, &gt;, '.
		'&amp;, &quot;, &#039; door de corresponderende '.
		'HTML entities, uit maximaal 32 bytes bestaan.');

$naam0 = htmlspecialchars($_POST['naam0'], ENT_QUOTES, 'UTF-8');
if (strlen($naam0) > 128)
	regular_error('ouders.php', $_POST, 'Uw achternaam mag, '.
		'na vervanging van &lt;, &gt;, &amp;, &quot;, &#039; '.
		'door de corresponderende HTML entities, '.
		'uit maximaal 128 bytes bestaan.');

$naam1 = htmlspecialchars($_POST['naam1'], ENT_QUOTES, 'UTF-8');
if (strlen($naam1) > 64)
	regular_error('ouders.php', $_POST, 'Uw voornaam mag, '.
		'na vervanging van &lt;, &gt;, &amp;, &quot;, &#039; '.
		'door de corresponderende HTML entities, '.
		'uit maximaal 64 bytes bestaan.');

$naam2 = htmlspecialchars($_POST['naam2'], ENT_QUOTES, 'UTF-8');
if (strlen($naam2) > 16)
	regular_error('ouders.php', $_POST, 'Uw tussenvoegsel mag, '.
		'na vervanging van &lt;, &gt;, &amp;, &quot;, &#039; '.
		'door de corresponderende HTML entities, '.
		'uit maximaal 16 bytes bestaan.');

$email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
if (strlen($email) > 128)
	regular_error('ouders.php', $_POST, 'Uw emailadres mag, '.
		'na vervanging van &lt;, &gt;, &amp;, &quot;, &#039; '.
		'door de corresponderende HTML entities, '.
		'uit maximaal 128 bytes bestaan.');

if (preg_match('/^[[:digit:]]+$/', $_POST['login'])) regular_error(
	'ouders.php', $_POST, 'Gewenste loginnaam bestaat alleen '.
		'uit cijfers. De loginnaam van een ouderaccount mag niet '.
		'uit alleen cijfers of alleen letters bestaan.');

if (preg_match('/^[[:alpha:]]+$/', $_POST['login'])) regular_error(
	'ouders.php', $_POST, 'Gewenste loginnaam bestaat alleen '.
		'uit letters. De loginnaam van een ouderaccount mag niet '.
		'uit alleen cijfers of alleen letters bestaan.');

check_email_POST('ouders.php');

try {
	mysql_query_safe('INSERT INTO ppl ( naam1, naam2, naam0, login, email, type ) '.
			"VALUES ( '%s', '%s', '%s', '%s', '%s', 'ouder' )",
		mysql_escape_safe($naam1),
		mysql_escape_safe($naam2),
		mysql_escape_safe($naam0),
		mysql_escape_safe($login),
		mysql_escape_safe($email));
}
catch (Exception $e) {
	if (mysql_errno() != 1062) throw($e);
	regular_error('ouders.php', $_POST, 'Loginnaam <code>'.$login.'</code> '.
		'is al in gebruik, probeer een andere.');
}
$ppl_id = mysql_insert_id();
mysql_query_safe(
	'INSERT INTO grp ( naam, schooljaar, grp_type_id ) '.
	"VALUES ( '%s', '$schooljaar', ".
	"( SELECT grp_type_id FROM grp_types WHERE grp_type_naam = 'gezin' ) )",
       	'gezin van '.$login);
mysql_query_safe("INSERT INTO grp2vak ( grp_id ) VALUES ( '%s' )", mysql_insert_id());
mysql_query_safe("INSERT INTO doc2grp2vak ( grp2vak_id, ppl_id ) ".
	"VALUES ( '%s', '$ppl_id' )", mysql_insert_id());

// account is aangemaakt, verstuur password link

$row = mysql_fetch_array(mysql_query_safe_nonempty(
	"SELECT KB_NAAM(naam0, naam1, naam2) naam, ".
	"SUBSTRING(SHA1(CONCAT(ppl.pw_reset_count, ppl.ppl_id, ".
	"'$pw_reset_secret')), 1, 16) code, email ".
	"FROM ppl WHERE login='%s' AND active IS NOT NULL",
	mysql_escape_safe($login)));

mail(htmlspecialchars_decode($row['naam']. ' <'.$row['email'].'>', ENT_QUOTES),
	"wachtwoord instellen op $http_server$http_path",
"Beste ".htmlspecialchars_decode($row['naam'], ENT_QUOTES).",

Met dit emailadres is een account aangemaakt op $http_server$http_path.
Als u dat zelf hebt gedaan, volg dan de onderstaande link om een
wachtwoord in te stellen.

https://$http_server$http_path/pw_reset.php?userid=".urlencode($_POST['login'])."&pw_reset_code=${row['code']}

Weet u van niks, dan mag u dit bericht als niet verzonden beschouwen. Bij 
herhaling kan ik (de beheerder van $http_server$http_path) de boosdoener 
achterhalen en maatregelen nemen. Het account is aangevraagd vanaf
ip adres ${_SERVER['REMOTE_ADDR']}.

Met vriendelijke groeten,

".$beheerder."\n", 'From: '.$beheerder."\r\n");

$_SESSION['successmsg'] = "Email verzonden naar <code>$email</code>. Volg de instructies in de email. Komt de email niet aan? Controleer het adres en/of check je spamfolder. Is het adres fout? Maak nog een account aan met een andere inlognaam en het juiste emailadres.";
header("Location: $http_path/");
?>
