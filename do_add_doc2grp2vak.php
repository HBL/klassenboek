<? require("include/init.php");
check_login_and_cap('ADD_DOC2GRP2VAK');
$command = explode('/', $_POST['doc2grp2vak']);

$tmp = mysql_fetch_row(mysql_query_safe(
	"SELECT grp_id FROM grp WHERE schooljaar='$schooljaar' ".
	"AND grp.naam = '%s'", mysql_escape_safe(htmlspecialchars($command[1], ENT_QUOTES, "UTF-8"))));

if ($tmp == NULL) {
	echo("bestaat niet $command[1]");
	header("Location: index.php");
}

$grp_id = $tmp[0];

if ($command[2] == '') {
	$vak_id = 'NULL';
	$vak_id_query = "vak_id IS NULL";
} else {
	$tmp = mysql_fetch_row(mysql_query_safe(
		"SELECT vak_id FROM vak WHERE vak.afkorting = '%s'",
		mysql_escape_safe($command[2])));

	if ($tmp == NULL) {
		echo("bestaat niet $command[2]");
		header("Location: index.php");
	}

	$vak_id = $tmp[0];
	$vak_id_query = "vak_id = $vak_id";
}

$tmp = mysql_fetch_row(mysql_query_safe(
	"SELECT ppl_id FROM ppl WHERE ppl.login = '%s' AND active IS NOT NULL",
	mysql_escape_safe($command[0])));

if ($tmp == NULL) {
	echo("bestaat niet $command[0]");
	header("Location: index.php");
}

$ppl_id = $tmp[0];

$tmp = mysql_fetch_row(mysql_query_safe(
	"SELECT grp2vak_id FROM grp2vak WHERE grp_id = $grp_id AND $vak_id_query",
	mysql_escape_safe($command[2])));

if ($tmp == NULL) {
	mysql_query_safe("INSERT INTO grp2vak ( grp_id, vak_id ) VALUES ( $grp_id, $vak_id )");
	$grp2vak_id = mysql_insert_id();
} else $grp2vak_id = $tmp[0];

mysql_query_safe("INSERT INTO doc2grp2vak ( ppl_id, grp2vak_id ) VALUES ( $ppl_id, $grp2vak_id )");

if (mysql_affected_rows() > 0) {
	$_SESSION['successmsg'] = "Inserted ${command[0]} ($ppl_id) as docent on ${command[1]}/${command[2]} ($grp2vak_id).";
	mysql_log("add_doc2grp2vak_success", "insert ($ppl_id, $grp2vak_id) in doc2grp2vak");
}

header("Location: beheer.php");
exit;
?>
