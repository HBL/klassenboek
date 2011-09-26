<? include("include/init.php");
check_login();

$week_options = gen_week_select($_GET['week'], 0, &$week);

$result = mysql_query_safe("SELECT notities.text, agenda.* FROM notities ".
	"JOIN agenda ON notities.notitie_id = agenda.notitie_id ".
	"JOIN ppl2agenda USING (agenda_id) ".
	"WHERE notities.notitie_id = %s ".
	"AND ppl2agenda.ppl_id = ${_SESSION['ppl_id']};",
	mysql_escape_safe($_GET['notitie_id']));
$num = mysql_numrows($result);

if (!$num) throw new Exception('notitie bestaat niet of je hebt geen schrijfrechten', 2);

$row = mysql_fetch_array($result);

$dag_options = gen_dag_select($row['dag'], 0, &$dag, 0, 0);
$lesuur_options = gen_lesuur_select($row['lesuur'], 0, &$lesuur, 0);
gen_html_header("Veranderen/Verplaatsen/Verwijderen", '$("textarea:visible:first").focus();'); 
$dagen = array('ma', 'di', 'wo', 'do', 'vr');
?>

<form id="vvv" action="do_vvv_zelf.php" method="POST" accept-charset="UTF-8">

<? if (isset($row['text']) != NULL) { ?>

<h3>Verplaatsen/Veranderen</h3>
Verplaatsen kan naar een ander lesuur.
<p>week: <? echo($week_options) ?> dag: <? echo($dag_options) ?>
lesuur: <? echo($lesuur_options) ?>
<br><textarea rows="3" cols="40" name="text">
<? echo(htmltobb($row['text'])); ?>
</textarea><br>
<input type="submit" name="submit" value="Opslaan">

<h3>Verwijderen</h3>
De notitie wordt onherroepelijk uit het klassenboek verwijderd. Krijg je spijt? Dan moet je een nieuwe notitie aanmaken.
<p><input type="submit" name="submit" value="Verwijder">

<h3>Terug</h3>
Gebruik de 'back' button in de browser om de notitie ongemoeid te laten.

<? } else { ?>

<h3>Issue sluiten</h3>

Voer eventueel de reden in van de sluiting en klik op 'sluiten'.
<br><textarea rows="3" cols="40" name="text">
</textarea><br>
<input type="hidden" name="week" value="<? echo($row['week']); ?>">
<input type="hidden" name="dag" value="<? echo($row['dag']); ?>">
<input type="hidden" name="lesuur" value="<? echo($row['lesuur']); ?>">
<input type="submit" name="submit" value="Sluiten">

<h3>Verwijderen</h3>
Het aanmaken van de issue wordt ongedaan gemaakt.
<p><input type="submit" name="submit" value="Verwijder">

<h3>Terug</h3>
Gebruik de 'back' button in de browser om het issue ongemoeid te laten.

<? } ?>

<input type="hidden" name="grp2vak_id" value="<? echo($_GET['grp2vak_id']) ?>">
<input type="hidden" name="lln" value="<? echo($_GET['lln']) ?>">
<input type="hidden" name="doelgroep" value="<? echo($_GET['doelgroep']) ?>">
<input type="hidden" name="notitie_id" value="<? echo($_GET['notitie_id']) ?>">
</form>

<?
//foreach ( $_GET as $key => $value ) {
//	print("$key=$value\n");
//} ?>

<? mysql_close(); gen_html_footer(); ?>
