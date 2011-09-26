<? include("include/init.php");
check_login();
if ($_SESSION['type'] == 'ouder') {
	header('Location: index.php');
	exit;
}

$reload = 0;

$week_options = gen_week_select($_GET['week'], 0, &$week);
$dag_options = gen_dag_select($_GET['dag'], 0, &$dag, 0, 0);
$lesuur_options = gen_lesuur_select($_GET['lesuur'], 0, &$lesuur, 0);

if ($reload) {
	header("Location: new.php?index=$week&dag=$dag&".
		"lesuur=$lesuur&doelgroep=lesgroep&grp2vak_id=${_GET['grp2vak_id']}");
	exit;
}

gen_html_header("Nieuwe Notitie", '$("textarea:visible:first").focus();'); 
?>
Deze notitie verschijnt in je eigen agenda.
<form id="notitie" name="notitie" action="<? echo("do_new_zelf.php") ?>" method="POST" accept-charset="UTF-8">
<p>week: <? echo($week_options) ?> dag: <? echo($dag_options) ?>
lesuur: <? echo($lesuur_options) ?>
<br><textarea rows="3" cols="40" name="text">
</textarea><br>
<input type="hidden" name="grp2vak_id" value="<? echo($_GET['grp2vak_id']) ?>">
<input type="hidden" name="lln" value="<? echo($_GET['lln']) ?>">
<input type="submit" value="Opslaan">
</form>
<? gen_html_footer(); ?>
