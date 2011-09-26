<? include("include/init.php");
check_login();

if (check_week($_POST['week'])) { $week = $_POST['week']; }
else { header("Location: index.php"); exit; }
if (check_dag($_POST['dag'])) { $dag = $_POST['dag']; }
else { header("Location: index.php"); exit; }
if (check_lesuur($_POST['lesuur'])) { $lesuur = $_POST['lesuur']; }
else { header("Location: index.php"); exit; }

$result = mysql_query_safe(
	"SELECT * FROM ppl AS lln ".
	"JOIN ppl2grp USING (ppl_id) ".
	"JOIN grp2vak USING (grp_id) ".
	"JOIN doc2grp2vak USING (grp2vak_id) ".
	"JOIN ppl AS doc ON doc.ppl_id = doc2grp2vak.ppl_id ".
	"WHERE lln.ppl_id = '%s' AND doc.ppl_id = ${_SESSION['ppl_id']}",
	mysql_escape_safe($_POST['lln']));

if (!mysql_num_rows($result)) { header("Location: index.php"); exit; }

$result = mysql_query_safe("INSERT INTO notities ( notitie_id, creat, text ) ".
		"VALUES ( NULL, NULL, '%s' );",
	       	mysql_escape_safe(bbtohtml(htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8'))));
$notitie_id = mysql_insert_id();

$result = mysql_query_safe("INSERT INTO agenda ( agenda_id, schooljaar, week, dag, ".
	"lesuur, notitie_id ) VALUES ( NULL, '$schooljaar', $week, ".
	"$dag, $lesuur, $notitie_id );");
$agenda_id = mysql_insert_id();

$query = "INSERT INTO ppl2agenda ( ppl_id, agenda_id, allow_edit ) VALUES ".
	"( ${_SESSION['ppl_id']}, $agenda_id, 1 )";
if (mysql_escape_safe($_POST['lln']) != $_SESSION['ppl_id']) 
	$query .= sprintf(", ( '%s', $agenda_id, 0 )", mysql_escape_safe($_POST['lln']));

mysql_query_safe($query);

header("Location: index.php?week=$week&doelgroep=leerling&grp2vak_id=${_POST['grp2vak_id']}&lln=${_POST['lln']}");
?>
