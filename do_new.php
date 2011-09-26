<? include("include/init.php");
check_login();
if ($_SESSION['type'] == 'ouder')
	throw new Exception('ouders mogen geen groepsnotities maken', 2);

if (check_week($_POST['week'])) { $week = $_POST['week']; }
else { header("Location: index.php"); exit; }
if (check_dag($_POST['dag'])) { $dag = $_POST['dag']; }
else { header("Location: index.php"); exit; }
if (check_lesuur($_POST['lesuur'])) { $lesuur = $_POST['lesuur']; }
else { header("Location: index.php"); exit; }

if (!verify_grp2vak($_POST['grp2vak_id'])) throw new Exception('ingelogd persoon mag geen notities maken in klassenboek van deze groep', 2);
$grp2vak_id = $_POST['grp2vak_id'];

mysql_query_safe("INSERT INTO notities ( notitie_id, creat, text ) ".
		"VALUES ( NULL, NULL, '%s' );", mysql_escape_safe(bbtohtml(
				htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8'))));
$notitie_id = mysql_insert_id();

mysql_query_safe("INSERT INTO agenda ( agenda_id, schooljaar, week, dag, ".
	"lesuur, notitie_id ) VALUES ( NULL, '$schooljaar', '$week', ".
	"$dag, $lesuur, $notitie_id )");
$agenda_id = mysql_insert_id();

mysql_query_safe("INSERT INTO grp2vak2agenda ( grp2vak_id, agenda_id ) VALUES ".
	"( $grp2vak_id, $agenda_id )");

check_group_tags_allowed($_POST['tags']);

for ($i = 0; $i < count($_POST['tags']); $i++) {
	mysql_query_safe("INSERT INTO tags2notities ( tag_id, notitie_id ) VALUES ".
		"( '%s', $notitie_id )", mysql_escape_safe($_POST['tags'][$i]));
	$tag = sprint_singular("SELECT tag FROM tags WHERE tag_id = '%s'", mysql_escape_safe($_POST['tags'][$i]));
	if (!$sa && $tag == 'repetitie') $sa = 'Toets';
	else if (!$sa && $tag == 'vt') $sa = 'VT';
	else if (!$sa && $tag == 'so') $sa = 'SO';
	else if (!$sa && $tag == 'st') $sa = 'SE';
	else if (!$sa && $tag == 'inleveren') $sa = 'HO';
	$tags .= ' ['.$tag.']';
}
if ($tags) $tags = '<font size="1">'.$tags.'</font>';


if ($_POST['per'] == 'per1' ||$_POST['per'] == 'per2' || $_POST['per'] == 'per3') {
	mysql_query_safe("INSERT INTO tags2notities ( tag_id, notitie_id ) VALUES( ( SELECT tag_id FROM tags WHERE tag = '%s' ), $notitie_id )", mysql_escape_safe($_POST['per']));
}

$result = mysql_query_safe("SELECT * FROM grp2vak2vaksite JOIN vaksites USING (vaksite_id) WHERE grp2vak_id = $grp2vak_id");
if (mysql_numrows($result) && $_POST['teletop'] == 'yes') add_to_teletop($grp2vak_id, $notitie_id, $week, $dag, $lesuur.'e uur: '.bbtohtml(
	                                htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8')).$tags, $sa);
else if ($_POST['teletop'] == 'yes')
	$_SESSION['errormsg'] = 'De nieuwe klas/vak combinatie heeft geen geassocieerde vaksite, de notitie is niet in TeleTOP&reg; geplaatst.';

header("Location: index.php?week=$week&doelgroep=lesgroep&lln=${_POST['lln']}&grp2vak_id=${_POST['grp2vak_id']}");

?>
