<? include("include/init.php");
check_login();
check_isset_array($_GET, 'notitie_id');
check_isnonempty_array($_GET, 'notitie_id');

$week_options = gen_week_select($_GET['week'], 0, &$week);
$dag_options = gen_dag_select($_GET['dag'], 0, &$dag, 0, 0);
$lesuur_options = gen_lesuur_select($_GET['lesuur'], 0, &$lesuur, 0);

$notitie_id = mysql_escape_safe($_GET['notitie_id']);

$result = mysql_query_safe(<<<EOQ
SELECT tag, CONCAT(IFNULL(CONCAT(afkorting, ' '), ''), parents.text) text, CONCAT(agenda.week, CASE agenda.dag WHEN 1 THEN 'ma' WHEN 2 THEN 'di' WHEN 3 THEN 'wo' WHEN 4 THEN 'do' ELSE 'vr' END, agenda.lesuur) moment
FROM notities
JOIN tags2notities USING (notitie_id)
JOIN agenda AS this ON notities.notitie_id = this.notitie_id
JOIN ppl2agenda ON ppl2agenda.agenda_id = this.agenda_id
JOIN tags USING (tag_id)
JOIN notities AS parents ON notities.parent_id = parents.notitie_id
JOIN agenda ON parents.notitie_id = agenda.notitie_id
LEFT JOIN grp2vak2agenda ON agenda.agenda_id = grp2vak2agenda.agenda_id
LEFT JOIN grp2vak USING (grp2vak_id)
LEFT JOIN vak USING (vak_id)
WHERE notities.notitie_id = '$notitie_id' AND notities.text IS NULL AND allow_edit = 1 AND ppl_id = {$_SESSION['ppl_id']}
EOQ
);

$row = mysql_fetch_array($result);

gen_html_header("Afspraak maken", '$("textarea:visible:first").focus();');
status();

switch ($row['tag']) {
	case 'repetitie gemist':
	case 'so gemist':
		$new_text = 'inhalen '.$row['text'].', lokaal 260';
		$head_text = 'Reden van afwezigheid op '.$row['moment'].'.';
		$briefje = 1; ?>
<h3>Inhaaltoets plannen</h3>

<?		break;
	case 'afspraak niet voldaan':
		$new_text = $row['text'];
		$head_text = 'Reden van afwezigheid op '.$row['moment'].'.';
		$briefje = 1; ?>
<h3>Nieuwe afspraak maken</h3>

<?		break;
	case 'hw niet in orde':
		$new_text = 'extra uurtje (hw niet in orde), lokaal ';
		$head_text = 'Waarom het huiswerk van '.$row['moment'].' niet in orde was.';
		$briefje = 0; ?>
<h3>Uurtje inhalen vanwege hw</h3>

<?		break;
	default:
		throw new Exception('onbekend issue soort', 2);
}
?>
<form action="do_cont_issue.php" accept-charset="UTF-8" method="POST">
<p><? echo($head_text) ?>
<p><textarea rows="3" cols="40" name="text0">
</textarea>
<? if ($briefje) { ?><br><input type="checkbox" name="briefje" value="1"> briefje inleveren<? } ?>
<p>Nieuwe afspraak week: <? echo($week_options) ?> dag: <? echo($dag_options) ?>
lesuur: <? echo($lesuur_options) ?>
lesgroep/vak: <? echo($grp2vak_options) ?>
<p><textarea rows="3" cols="40" name="text1">
<? echo(htmltobb($new_text)) ?>
</textarea>
<p><input type="submit" name="submit" value="Opslaan">
<input type="hidden" name="lln" value="<? echo($_GET['lln']) ?>">
<input type="hidden" name="view" value="<? echo($_GET['view']) ?>">
<input type="hidden" name="doelgroep" value="<? echo($_GET['doelgroep']) ?>">
<input type="hidden" name="notitie_id" value="<? echo($notitie_id) ?>">
<input type="hidden" name="grp2vak_id" value="<? echo($_GET['grp2vak_id']) ?>">
</form>

<? gen_html_footer(); ?>


