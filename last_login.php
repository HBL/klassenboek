<?
require("include/init.php");
check_login_and_cap('STATS');
if ($_GET['doc'] == 1) {
	$header = 'docenten'; $select = "(ppl.type != 'leerling' AND ppl.type != 'ouder')";
} else if ($_GET['doc'] == 2) {
	$header = 'ouder'; $select = "ppl.type ='ouder'";
} else {
	$header = 'leerling'; $select = "ppl.type ='leerling'";
}

gen_html_header();
echo("<h3>Recente ${header}logins</h3>");
echo(sprint_table(mysql_query_safe(
	"SELECT ".
		"MAX(`log`.`timestamp`) login_time, ".
		"KB_NAAM(naam0, naam1, naam2) naam, ".
		"COUNT(DISTINCT log.timestamp) aantal, ".
		"GROUP_CONCAT(DISTINCT grp.naam ORDER BY grp_type_id, stamklas DESC, grp.naam) `groep(en)` ".
	"FROM ppl ".
	"JOIN log ON ppl.ppl_id = log.orig_ppl_id ".
	"LEFT JOIN ppl2grp ON ppl.ppl_id = ppl2grp.ppl_id ".
	"LEFT JOIN grp ON grp.grp_id = ppl2grp.grp_id AND grp.schooljaar = '$schooljaar' ".
	"WHERE ".
		"event = 'login_success' ".
		"AND $select ".
//		"AND (grp.schooljaar = '$schooljaar' OR ppl2grp.grp_id IS NULL) ".
	"GROUP BY ppl.ppl_id ".
	"ORDER BY login_time DESC ".
	"LIMIT 0,256".
	";")));
gen_html_footer();
?>
