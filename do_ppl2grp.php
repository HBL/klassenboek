<? include("include/init.php");
check_login_and_cap('PPL2GRP_OWN');
check_isset_array($_POST, 'login', 'submit', 'grp_id');
check_isnonempty_array($_POST, 'grp_id', 'submit');
if ($_POST['submit'] != 'Toevoegen' && $_POST['submit'] != 'Verwijderen')
	throw new Exception('ongeldige waarde van submit', 2);
verify_grp($_POST['grp_id']);
check_required_POST('beheer.php', 'login');

$grp_id = mysql_escape_safe($_POST['grp_id']);
$login = htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8');
$grp_naam = sprint_singular(
	"SELECT grp.naam FROM grp WHERE grp_id = '$grp_id'");

if (!($ppl_id = sprint_singular("SELECT ppl_id FROM ppl ".
	"WHERE ppl.login = '%s' AND active IS NOT NULL",
	mysql_escape_safe($login)))) regular_error('beheer.php', (array)NULL,
	"Persoon <code>$login</code> bestaat niet.");

$row = mysql_fetch_array($result = mysql_query_safe_nonempty(
	"SELECT CONCAT(KB_NAAM(naam0, naam1, naam2), ".
	"' (', login, ')') FROM ppl WHERE ppl_id = $ppl_id"));
$ppl_naam = $row[0];
$ppl_type = $row[1];
mysql_free_result($result);

if ($_POST['submit'] == 'Toevoegen') {
	try {
		mysql_query_safe("INSERT INTO ppl2grp ( ppl_id, grp_id ) ".
			"VALUES ( '$ppl_id', '$grp_id' )");
	}
	catch (Exception $e) {
		if (mysql_errno() != 1062) throw($e);
		regular_error('beheer.php', (array)NULL,
			$ppl_naam.' zit al in '.$grp_naam);
	}

	$_POST['submit'] = 'Verwijderen';
	$_SESSION['successmsg'] =
		status_success("$ppl_naam toegevoegd aan $grp_naam");
	mysql_log('add_ppl2grp', $ppl_id.','.$grp_id);
} else  {
	mysql_query_safe("DELETE FROM ppl2grp WHERE ppl_id = '$ppl_id' ".
		"AND grp_id = '$grp_id'");
	if (!mysql_affected_rows()) regular_error('beheer.php', (array)NULL,
		$ppl_naam.' zit niet in '.$grp_naam);

	$_POST['submit'] = 'Toevoegen';
	$_SESSION['successmsg'] =
	       	status_success("$ppl_naam verwijderd uit $grp_naam");
	mysql_log('del_ppl2grp', $ppl_id.','.$grp_id);
}

header("Location: beheer.php"); ?>
