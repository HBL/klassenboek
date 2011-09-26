<? include("include/init.php");
check_login();

if ($_POST['submit'] != "Verwijder" && $_POST['submit'] != "Opslaan" && $_POST['submit'] != "Sluiten") 
	throw new Exception('impossible submit', 2);

$result = mysql_query_safe(
	"SELECT allow_edit FROM ppl2agenda ".
	"JOIN agenda USING (agenda_id) ".
	"WHERE ppl_id = ${_SESSION['ppl_id']} ".
	"AND allow_edit = 1 ".
	"AND notitie_id = '%s'", mysql_escape_safe($_POST['notitie_id']));

if (!mysql_numrows($result)) {
	header("Location: index.php");
	exit;
}

if ($_POST['submit'] == "Verwijder" ) {
	$result = mysql_query_safe("SELECT agenda_id FROM agenda ".
		"JOIN ppl2agenda USING (agenda_id) ".
		"WHERE notitie_id = '%s'",
		mysql_escape_safe($_POST['notitie_id']));
	$query = "DELETE FROM ppl2agenda WHERE 0";
	while ($row = mysql_fetch_row($result)) 
		$query .= " OR agenda_id = ${row[0]}";
	mysql_free_result($result);
	mysql_query_safe($query);
} else {
	mysql_query_safe("UPDATE notities ".
		"JOIN agenda USING (notitie_id) ".
		"SET notities.text='%s', ".
		"agenda.week = '%s', ".
		"agenda.dag = '%s', ".
		"agenda.lesuur = '%s' ".
		"WHERE notities.notitie_id='%s';",
		mysql_escape_safe(bbtohtml(htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8'))),
		mysql_escape_safe($_POST['week']),
		mysql_escape_safe($_POST['dag']),
		mysql_escape_safe($_POST['lesuur']),
		mysql_escape_safe($_POST['notitie_id']));
}
	
header("Location: index.php?week=${_POST['week']}&dag=${_POST['dag']}&lesuur=${_POST['lesuur']}&doelgroep=${_POST['doelgroep']}&grp2vak_id=${_POST['grp2vak_id']}&lln=${_POST['lln']}");

mysql_close(); ?>
