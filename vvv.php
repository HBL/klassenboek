<? include("include/init.php");
check_login();

$week_options = gen_week_select($_GET['week'], 0, &$week);

$result = mysql_query_safe("SELECT grp2vak_id, grp.naam, notities.text, GROUP_CONCAT(tag_id) tag_ids, agenda.*, doc_id FROM notities ".
	"LEFT JOIN tags2notities USING (notitie_id) ".
	"JOIN agenda USING (notitie_id) ".
	"JOIN grp2vak2agenda USING (agenda_id) ".
	"JOIN grp2vak USING (grp2vak_id) ".
	"JOIN doc2grp2vak USING (grp2vak_id) ".
	"JOIN grp USING (grp_id) ".
	"LEFT JOIN notities2teletop USING (notitie_id) ".
	"WHERE notities.notitie_id = %s ".
	"AND doc2grp2vak.ppl_id = ${_SESSION['ppl_id']} GROUP BY notitie_id",
	mysql_escape_safe($_GET['notitie_id']));
$num = mysql_numrows($result);
if (!$num) {
	header("Location: index.php");
	exit;
}
if (mysql_result($result, 0, 'tag_ids')) $tag_ids = explode(',', mysql_result($result, 0, 'tag_ids'));
print_r($tags);
$grp2vak_options =
       	sprint_grp2vak_select(mysql_result($result, 0, 'grp2vak_id'), 0, &$grp2vak_id, 0);

$dag_options = gen_dag_select(mysql_result($result, 0, "agenda.dag"), 0, &$dag, 0, 0);
$lesuur_options = gen_lesuur_select(mysql_result($result, 0, "agenda.lesuur"), 0, &$lesuur, 0);

$default0= '';
$default1= '';
$default2= '';
$default3= '';
if (is_array($tag_ids) && in_array(sprint_singular("SELECT tag_id FROM tags WHERE tag = 'per1'"), $tag_ids)) {
	$default1 = ' selected';
} else if (is_array($tag_ids) && in_array(sprint_singular("SELECT tag_id FROM tags WHERE tag = 'per2'"), $tag_ids)) {
	$default2 = ' selected';
} else if (is_array($tag_ids) && in_array(sprint_singular("SELECT tag_id FROM tags WHERE tag = 'per3'"), $tag_ids)) {
	$default3 = ' selected';
} else {
	$default0 = ' selected';
}

$tags .= 'voor cijfer: ';
$tags .= sprint_tag_checkbox('tags[]', 'repetitie', $tag_ids);
$tags .= ' '.sprint_tag_checkbox('tags[]', 'so', $tag_ids);
$tags .= ' '.sprint_tag_checkbox('tags[]', 'SE', $tag_ids);
$tags .= ' '.sprint_tag_checkbox('tags[]', 'proefwerk', $tag_ids);
$tags .= ' '.sprint_tag_checkbox('tags[]', 'inleveren', $tag_ids).'<br>';
$tags .= 'huiswerk: ';
$tags .= sprint_tag_checkbox('tags[]', 'maken', $tag_ids);
$tags .= ' '.sprint_tag_checkbox('tags[]', 'lezen', $tag_ids);
$tags .= ' '.sprint_tag_checkbox('tags[]', 'leren', $tag_ids).'<br>';
$tags .= 'planning: ';
$tags .= sprint_tag_checkbox('tags[]', 'in de les', $tag_ids).'<br>';

gen_html_header("Veranderen/Verplaatsen/Verwijderen", '$("textarea:visible:first").focus();'); 
$dagen = array('ma', 'di', 'wo', 'do', 'vr');
?>

<form id="vvv" action="do_vvv.php" method="POST" accept-charset="UTF-8">

<h3>Verplaatsen/Veranderen</h3>
Verplaatsen kan zowel naar een ander lesuur, als naar een andere lesgroep.
<p>week: <? echo($week_options) ?> dag: <? echo($dag_options) ?>
lesuur: <? echo($lesuur_options) ?>
lesgroep/vak: <? echo($grp2vak_options) ?>
<br><textarea rows="3" cols="40" name="text">
<? echo(htmltobb(mysql_result($result, 0, "notities.text"))); ?>
</textarea><br>
<? echo($tags) ?><br>
<? if ($_SESSION['teletop_username'] && $_SESSION['teletop_password']) { ?>
<input type="checkbox" name="teletop" value="yes"<? if (mysql_result($result, 0, 'doc_id')) echo(' checked') ?>>Notitie opnemen in kolom 'Huiswerk' van TeleTOP&reg;<br><br>
<? } ?>
<input type="submit" name="submit" value="Opslaan">
<h3>Verwijderen</h3>
De notitie wordt onherroepelijk uit het klassenboek verwijderd. Krijg je spijt? Dan moet je een nieuwe notitie aanmaken.
<p><input type="submit" name="submit" value="Verwijder">

<h3>Terug</h3>
Gebruik de 'back' button in de browser om de notitie ongemoeid te laten.

<input type="hidden" name="doelgroep" value="<? echo($_GET['doelgroep']) ?>">
<input type="hidden" name="lln" value="<? echo($_GET['lln']) ?>">
<input type="hidden" name="notitie_id" value="<? echo($_GET['notitie_id']) ?>">
</form>

<? gen_html_footer(); ?>
