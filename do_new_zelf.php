<? include("include/init.php");
check_login();

if ($_SESSION['type'] == 'ouder') {
	header("Location: index.php");
	exit;
}

if (check_week($_POST['week'])) { $week = $_POST['week']; }
else { header("Location: index.php"); exit; }
if (check_dag($_POST['dag'])) { $dag = $_POST['dag']; }
else { header("Location: index.php"); exit; }
if (check_lesuur($_POST['lesuur'])) { $lesuur = $_POST['lesuur']; }
else { header("Location: index.php"); exit; }

$result = mysql_query_safe("INSERT INTO notities ( notitie_id, creat, text ) ".
		"VALUES ( NULL, NULL, '%s' );",
	       	mysql_escape_safe(bbtohtml(htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8'))));
$notitie_id = mysql_insert_id();

$result = mysql_query_safe("INSERT INTO agenda ( agenda_id, schooljaar, week, dag, ".
	"lesuur, notitie_id ) VALUES ( NULL, '$schooljaar', $week, ".
	"$dag, $lesuur, $notitie_id );");
$agenda_id = mysql_insert_id();

mysql_query_safe("INSERT INTO ppl2agenda ( ppl_id, agenda_id, allow_edit ) VALUES ".
	"( ${_SESSION['ppl_id']}, $agenda_id, 1 );");

header("Location: index.php?week=$week&doelgroep=zelf&lln=${_POST['lln']}&grp2vak_id=${_POST['grp2vak_id']}");
?>
