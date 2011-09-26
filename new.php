<? include("include/init.php");
check_login();
if ($_SESSION['type'] == 'ouder') throw new Exception('ouders kunnen geen groepsnotities maken');

$reload = 0;

$week_options = gen_week_select($_GET['week'], 0, &$week);
$dag_options = gen_dag_select($_GET['dag'], 0, &$dag, 0, 0);
$lesuur_options = gen_lesuur_select($_GET['lesuur'], 0, &$lesuur, 0);

$grp2vak_options =
	sprint_grp2vak_select($_GET['grp2vak_id'], 0, &$grp2vak_id, 0);

if (!$grp2vak_options) throw new Exception('ingelogd persoon heeft geen lesgroepen en kan dus ook geen groepsnotities maken', 2);

if ($reload) {
	header("Location: new.php?index=$week&dag=$dag&".
		"lesuur=$lesuur&doelgroep=lesgroep&grp2vak_id=$grp2vak_id");
	exit;
}

$table = get_list_of_files();

$tags .= 'voor cijfer: ';
$tags .= sprint_tag_checkbox('tags[]', 'repetitie');
$tags .= ' '.sprint_tag_checkbox('tags[]', 'proefwerk');
$tags .= ' '.sprint_tag_checkbox('tags[]', 'so');
$tags .= ' '.sprint_tag_checkbox('tags[]', 'SE');
$tags .= ' '.sprint_tag_checkbox('tags[]', 'inleveren').'<br>';
$tags .= 'huiswerk: ';
$tags .= sprint_tag_checkbox('tags[]', 'maken');
$tags .= ' '.sprint_tag_checkbox('tags[]', 'lezen');
$tags .= ' '.sprint_tag_checkbox('tags[]', 'leren').'<br>';
$tags .= 'planning: ';
$tags .= sprint_tag_checkbox('tags[]', 'in de les').'<br>';

$result = mysql_query_safe("SELECT vaksite FROM grp2vak2vaksite JOIN vaksites USING (vaksite_id) WHERE grp2vak_id = '$grp2vak_id'");
if (mysql_numrows($result)) $vaksite = mysql_result($result, 0, 0);

gen_html_header("Nieuwe Notitie", '$("textarea:visible:first").focus();');
?>
<form name="notitie" action="do_new.php" method="POST" accept-charset="UTF-8">
<p>week: <? echo($week_options) ?> dag: <? echo($dag_options) ?>
lesuur: <? echo($lesuur_options) ?>
lesgroep/vak: <? echo($grp2vak_options) ?>
<br><textarea rows="3" cols="40" name="text">
<? echo($_GET['text']) ?>
</textarea><br>
<? echo($tags) ?><br>
<? if ($_SESSION['teletop_username'] && $_SESSION['teletop_password'] && $vaksite) { ?>
<input type="checkbox" checked name="teletop" value="yes">Notitie opnemen in kolom 'Huiswerk' van TeleTOP&reg;<br><br>
<? } ?>
<input type="submit" value="Opslaan">
<input type="hidden" name="doelgroep" value="lesgroep">
<input type="hidden" name="lln" value="<? echo($_GET['lln']) ?>">
</form>
<? echo($table) ?>
<? mysql_close(); gen_html_footer(); ?>
