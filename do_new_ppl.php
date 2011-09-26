<? include('include/init.php');
check_login_and_cap('NEW_PPL');

check_isset_array($_POST, 'login', 'naam0', 'naam1', 'naam2');
check_required_POST('new_ppl.php', 'login', 'naam0', 'naam1');

$login = htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8');
if (strlen($login) > 32)
	regular_error('new_ppl.php', $_POST, 'Een zelfgekozen '.
		'gebruikersnaam mag, na vervanging van &lt;, &gt;, '.
		'&amp;, &quot;, &#039; door de corresponderende '.
		'HTML entities, uit maximaal 32 bytes bestaan.');

$naam0 = htmlspecialchars($_POST['naam0'], ENT_QUOTES, 'UTF-8');
if (strlen($naam0) > 128)
	regular_error('new_ppl.php', $_POST, 'Uw achternaam mag, '.
		'na vervanging van &lt;, &gt;, &amp;, &quot;, &#039; '.
		'door de corresponderende HTML entities, '.
		'uit maximaal 128 bytes bestaan.');

$naam1 = htmlspecialchars($_POST['naam1'], ENT_QUOTES, 'UTF-8');
if (strlen($naam1) > 64)
	regular_error('new_ppl.php', $_POST, 'Uw voornaam mag, '.
		'na vervanging van &lt;, &gt;, &amp;, &quot;, &#039; '.
		'door de corresponderende HTML entities, '.
		'uit maximaal 64 bytes bestaan.');

$naam2 = htmlspecialchars($_POST['naam2'], ENT_QUOTES, 'UTF-8');
if (strlen($naam2) > 16)
	regular_error('new_ppl.php', $_POST, 'Uw tussenvoegsel mag, '.
		'na vervanging van &lt;, &gt;, &amp;, &quot;, &#039; '.
		'door de corresponderende HTML entities, '.
		'uit maximaal 16 bytes bestaan.');

if (preg_match('/^[[:digit:]]+$/', $_POST['login'])) {
	$type = 'leerling';
} else if (preg_match('/^[[:alpha:]]+$/', $_POST['login'])) {
	$type = 'personeel';
} else regular_error('new_ppl.php', $_POST, 'Gewenste loginnaam bestaat niet alleen '.  'uit letters of alleen uit cijfers. Dat kan bij leerlingen en personeel niet.');

try {
	mysql_query_safe('INSERT INTO ppl ( naam1, naam2, naam0, login, type ) '.
			"VALUES ( '%s', '%s', '%s', '%s', '$type' )",
		mysql_escape_safe($naam1),
		mysql_escape_safe($naam2),
		mysql_escape_safe($naam0),
		mysql_escape_safe($login));
}
catch (Exception $e) {
	if (mysql_errno() != 1062) throw($e);
	$naam = sprint_singular("SELECT KB_NAAM(naam0, naam1, naam2) FROM ppl WHERE login = '%s' AND active IS NOT NULL", mysql_escape_safe($login));
	regular_error('new_ppl.php', $_POST, 'Loginnaam <code>'.$login.'</code> '.
		'is al in gebruik door '.$naam);
}

$_SESSION['successmsg'] = "Leerling/docent toegevoegd";
header("Location: beheer.php");
?>
