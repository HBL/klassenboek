<? include("include/init.php");
check_login();

$reload = 0;

$week_options = gen_week_select($_GET['week'], 0, &$week);
$dag_options = gen_dag_select($_GET['dag'], 0, &$dag, 0, 0);
$lesuur_options = gen_lesuur_select($_GET['lesuur'], 0, &$lesuur, 0);

if ($reload) {
	header("Location: new.php?index=$week&dag=$dag&".
		"lesuur=$lesuur&doelgroep=lesgroep&grp2vak_id=${_GET['grp2vak_id']}");
	exit;
}
$row = mysql_fetch_array($result = mysql_query_safe_nonempty(
		"SELECT CONCAT(KB_NAAM(naam0, naam1, naam2), ".
		"' (', login, ')') FROM ppl WHERE ppl_id = '%s'", $_GET['lln']));
$ppl_naam = $row[0];
mysql_free_result($result);

gen_html_header("Nieuwe Notitie", '$("textarea:visible:first").focus();'); 
?>
Deze notitie verschijnt in je eigen agenda en in die van <? echo($ppl_naam) ?>.
<form name="notitie" action="do_new_leerling.php" method="POST" accept-charset="UTF-8">
<p>week: <? echo($week_options) ?> dag: <? echo($dag_options) ?>
lesuur: <? echo($lesuur_options) ?>
<br><textarea rows="3" cols="40" name="text">
</textarea><br>
<input type="hidden" name="doelgroep" value="<? echo($_GET['doelgroep']) ?>">
<input type="hidden" name="lln" value="<? echo($_GET['lln']) ?>">
<input type="hidden" name="grp2vak_id" value="<? echo($_GET['grp2vak_id']) ?>">
<input type="submit" value="Opslaan">
</form>
<? gen_html_footer(); ?>
