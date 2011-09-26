<? require("include/init.php");
check_login_and_cap('DEL_DOC2GRP2VAK');
$command = explode('/', $_POST['doc2grp2vak']);

if ($command[2] == '') {
mysql_query_safe(
	"DELETE FROM doc2grp2vak USING doc2grp2vak ".
	"JOIN grp2vak USING (grp2vak_id) ".
	"JOIN grp USING (grp_id) ".
	"JOIN ppl USING (ppl_id) ".
	"WHERE grp.naam = '%s' ".
	"AND login = '%s' ".
	"AND active IS NOT NULL ".
	"AND vak_id IS NULL",
	mysql_escape_safe(htmlspecialchars($command[1], ENT_QUOTES, "UTF-8")),
	mysql_escape_safe($command[0]));
} else {
mysql_query_safe(
	"DELETE FROM doc2grp2vak USING doc2grp2vak ".
	"JOIN grp2vak USING (grp2vak_id) ".
	"JOIN grp USING (grp_id) ".
	"JOIN vak USING (vak_id) ".
	"JOIN ppl USING (ppl_id) ".
	"WHERE grp.naam = '%s' ".
	"AND login = '%s' ".
	"AND active IS NOT NULL ".
	"AND afkorting = '%s'",
	mysql_escape_safe(htmlspecialchars($command[1], ENT_QUOTES, "UTF-8")),
	mysql_escape_safe($command[0]),
	mysql_escape_safe($command[2]));
}

if (mysql_affected_rows() > 0) {
	$_SESSION['successmsg'] = "Deleted ${command[0]} as docent on ${command[1]}/${command[2]}.";
	mysql_log("del_doc2grp2vak_error", "delete");
}

header("Location: beheer.php");
exit;
?>
