<? require_once('include/init.php');
check_login();
check_isset_array($_POST, 'grp2vak_id');
check_isnonempty_array($_POST, 'grp2vak_id');
$grp2vak_id = mysql_escape_safe($_POST['grp2vak_id']);
if (!verify_grp2vak($_POST['grp2vak_id'])) throw new Exception('ingelogd persoon heeft geen schrijfrechten in klassenboek groep');
unset($_POST['grp2vak_id']);

foreach ($_POST as $key => $value) {
	if (preg_match('/^(\d+)-(\d+)$/', $key, $exploded)) {
		//print_r($exploded);
		//continue;

	//$exploded = explode('-', $key);
	if (!isset($value)) continue;
	$value = preg_replace('/,/', '.', $value);
	if ($value == '') $value = 'NULL';
	else {
		if (!is_numeric($value)) continue;
		if ($value > 10) $value /= 10;
		if ($value < 1 || $value > 10) continue;
		$value = "'".mysql_escape_safe($value)."'";
	}
	mysql_query_safe(<<<EOQ
UPDATE cijfers
JOIN agenda USING (notitie_id)
JOIN grp2vak2agenda USING (agenda_id)
JOIN grp2vak USING (grp2vak_id)
JOIN grp USING (grp_id)
JOIN ppl2grp USING (grp_id, ppl_id)
JOIN doc2grp2vak USING (grp2vak_id)
SET cijfer = $value
WHERE cijfers.ppl_id = '%s'
AND cijfers.notitie_id = '%s'
AND doc2grp2vak.ppl_id = {$_SESSION['ppl_id']}
AND grp2vak_id = '$grp2vak_id'
AND agenda.schooljaar = '$schooljaar'
AND grp.schooljaar = '$schooljaar'
AND grp.grp_type_id = (
	SELECT grp_type_id FROM grp_types WHERE grp_type_naam = 'lesgroep'
)
EOQ
		, 
		mysql_escape_safe($exploded[1]),
		mysql_escape_safe($exploded[2]));
	} else if (preg_match('/^(\d+)-per$/', $key, $exploded)) {
		mysql_query_safe("DELETE tags2notities FROM tags2notities JOIN agenda USING (notitie_id) JOIN grp2vak2agenda USING (agenda_id) JOIN doc2grp2vak USING (grp2vak_id) WHERE doc2grp2vak.ppl_id = {$_SESSION['ppl_id']} AND notitie_id = '%s' AND tag_id = ANY ( SELECT tag_id FROM tags WHERE tag = 'per1' OR tag = 'per2' OR tag = 'per3' )", mysql_escape_safe($exploded[1]));
		if ($value == 'per1' || $value == 'per2' || $value == 'per3') {
			mysql_query_safe("INSERT INTO tags2notities ( tag_id, notitie_id ) SELECT ( SELECT tag_id FROM tags WHERE tag = '%s' ), notitie_id FROM agenda JOIN grp2vak2agenda USING (agenda_id) JOIN doc2grp2vak USING (grp2vak_id) WHERE ppl_id = {$_SESSION['ppl_id']} AND notitie_id = '%s'", mysql_escape_safe($value), mysql_escape_safe($exploded[1]));
		}
		//echo($exploded[1].'<br>');
	} else throw new Exception('invalid field name', 2);
}

header("Location: cijfers.php?grp2vak_id=$grp2vak_id");
?>
