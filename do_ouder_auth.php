<? include('include/init.php');
check_isset_array($_POST, 'submit', 'kind_id', 'ouder_id', 'ouder_auth_code');
check_isnonempty_array($_POST, 'submit', 'kind_id', 'ouder_id', 'ouder_auth_code');
if ($_POST['submit'] != 'Geef toegang' && $_POST['submit'] != 'Weiger toegang')
	throw new Exception('ongeldige waarde van submit', 2);

/*
// check if gezin bestaat
$row = mysql_fetch_array(mysql_query_safe(
	"SELECT * FROM doc2grp2vak ".
	"JOIN grp2vak USING (grp2vak_id) ".
	"JOIN grp USING (grp_id) ".
	"WHERE schooljaar = '$schooljaar' ".
	"AND ppl_id = '$ouder_id'");
if (!$row) regular_error($http_path.'/', (array) NULL, 'Er is een probleem met het '.
	'toevoegen van leerlingen aan ouderaccounts die vorig schooljaar zijn gemaakt. '.
	'Dit probleem wordt zo spoedig mogelijk opgelost.');
print_r $row;
 */

$row = mysql_fetch_array(mysql_query_safe(
	"SELECT grp_id, ".
	"CONCAT(KB_NAAM(doc.naam0, doc.naam1, doc.naam2), ' (', doc.login, ')')  doc_naam ".
	"FROM ppl AS doc ".
	"JOIN doc2grp2vak USING (ppl_id) ".
	"JOIN grp2vak USING (grp2vak_id) ".
	"JOIN grp USING (grp_id) ".
	"WHERE active IS NOT NULL ".
	"AND ppl_id = '%s' ".
	"AND schooljaar = '$schooljaar' ".
	"AND SUBSTRING(SHA1(CONCAT('%s', '%s', '$pw_reset_secret')), 1, 16) = '%s'",
	mysql_escape_safe($_POST['ouder_id']),
	mysql_escape_safe($_POST['ouder_id']),
	mysql_escape_safe($_POST['kind_id']),
	mysql_escape_safe($_POST['ouder_auth_code'])));
if (!$row) regular_error($http_path.'/', (array) NULL,
	'Ouder Akkoord link is verouderd of onjuist. Stuur een mail '.
	'aan de beheerder, '.htmlspecialchars($beheerder).', als het probleem '.
	'zich herhaalt.');

$lln_naam = sprint_singular("SELECT KB_NAAM(naam0, naam1, naam2) FROM ppl WHERE ppl_id = '%s'",
	mysql_escape_safe($_POST['kind_id']));

if ($_POST['submit'] == 'Weiger toegang') {
	$result = mysql_query_safe("SELECT ppl_id FROM ppl2grp WHERE ppl_id = '%s' AND grp_id = ${row['grp_id']}", $_POST['kind_id']);
	if (mysql_numrows($result)) 
		regular_error($http_path.'/', (array)NULL, $row['doc_naam'].' heeft al toegang tot het klassenboek van '.$lln_naam.'. Toegang kan niet langs deze weg worden ontzegd.');
	$_SESSION['successmsg'] = 'Toegang geweigerd aan '.$row['doc_naam'].' tot het klassenboek van '.$lln_naam.'.';
	mysql_log('do_ouder_auth', 'toegang geweigerd aan '.$row['doc_naam'].' tot het klassenboek van '.$lln_naam);
} else {
	try {
		mysql_query_safe("INSERT INTO ppl2grp ( ppl_id, grp_id ) ".
			"VALUES ( '%s', '${row['grp_id']}' )", $_POST['kind_id']);
	}
	catch (Exception $e) {
		if (mysql_errno() != 1062) throw($e);
		regular_error($http_path.'/', (array)NULL, $row['doc_naam'].' heeft al toegang tot het klassenboek van '.$lln_naam.'.');
	}
	$_SESSION['successmsg'] = 'Toegang tot het klassenboek van '.$lln_naam.' verleend aan '.$row['doc_naam'].'.';
	mysql_log('do_ouder_auth', 'toegang tot het klassenboek van '.$lln_naam.' verleend aan '.$row['doc_naam']);
}
header('Location: '.$http_path.'/'); ?>
