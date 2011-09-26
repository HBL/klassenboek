<? 
require("include/init.php");
check_login();

$row = mysql_fetch_row(mysql_query_safe_nonempty(
	"SELECT grp_type_naam, grp_type_lid_ev FROM grp_types ".
	"WHERE grp_type_id = '%s' AND grp_type_naam != 'gezin'",
	mysql_escape_safe($_GET['grp_type_id'])));

$table = sprint_table(mysql_query_safe(
	"SELECT * FROM ( SELECT grp.naam naam, ".
	"COUNT(lln.password) aantal, ".
	"COUNT(lln.ppl_id) totaal, ".
	"ROUND(COUNT(lln.password)/COUNT(lln.ppl_id)*100, 1) percentage ".
	"FROM grp ".
	"JOIN ppl2grp USING (grp_id) ".
	"JOIN ppl AS lln USING(ppl_id) ".
	"WHERE grp_type_id = '%s' ".
	"AND grp.stamklas = 1 ".
	"AND grp.schooljaar = '$schooljaar' ".
	"GROUP BY grp.naam ".
	"ORDER BY percentage DESC, aantal DESC ) bla WHERE aantal != 0; ",
	mysql_escape_safe($_GET['grp_type_id'])));

gen_html_header();
echo "<h3>Aantal ${row[1]}accounts per ${row[0]}.</h3>";
echo($table);
gen_html_footer();
?>
