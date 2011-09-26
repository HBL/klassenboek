<? include("include/init.php");
check_login();
check_isset_array($_POST, 'week', 'dag', 'lesuur', 'text0', 'text1', 'notitie_id');
check_isnonempty_array($_POST, 'week', 'dag', 'lesuur', 'notitie_id');

if ($_POST['text0'] == '' || $_POST['text1'] == '') regular_error('cont_issue.php', $_POST, 'de twee notities mogen niet leeg zijn');

$notitie_id = mysql_escape_safe($_POST['notitie_id']);

if (check_week($_POST['week'])) { $week = $_POST['week']; }
else throw new Exception('ongeldige week', 2);
if (check_dag($_POST['dag'])) { $dag = $_POST['dag']; }
else throw new Exception('ongeldige dag', 2);
if (check_lesuur($_POST['lesuur'])) { $lesuur = $_POST['lesuur']; }
else throw new Exception('ongeldige lesuur', 2);
if (isset($_POST['briefje']) && $_POST['briefje'] != 1) throw new Exception('ongeldige waarde voor briefje', 2);

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

if (mysql_num_rows($result) == 0) throw new Exception('je hebt geen schrijfrechten om een afspraak te maken', 2);

mysql_query_safe("UPDATE notities SET text = '%s' WHERE notitie_id = '$notitie_id'",
	mysql_escape_safe(bbtohtml(
		htmlspecialchars($_POST['text0'], ENT_QUOTES, 'UTF-8'))));

if ($_POST['briefje'] == 1) {
	mysql_query_safe("INSERT tags2notities ( tag_id, notitie_id ) VALUES ( ( SELECT tag_id FROM tags WHERE tag = 'briefje inleveren' ), '$notitie_id' )");
}

mysql_query_safe("INSERT notities (parent_id, text) VALUES ('$notitie_id', '%s')",
	mysql_escape_safe(bbtohtml(
		htmlspecialchars($_POST['text1'], ENT_QUOTES, 'UTF-8'))));

$new_notitie_id = mysql_insert_id();

mysql_query_safe("INSERT tags2notities ( tag_id, notitie_id ) VALUES ( ( SELECT tag_id FROM tags WHERE tag = 'afspraak' ), '$new_notitie_id' )");

mysql_query_safe("INSERT agenda ( dag, week, lesuur, schooljaar, notitie_id ) VALUES ( '$dag', '$week', '$lesuur', '$schooljaar', '$new_notitie_id' )");

$agenda_id = mysql_insert_id();

$result3 = mysql_query_safe("SELECT ppl_id, allow_edit FROM ppl2agenda JOIN agenda USING (agenda_id) WHERE notitie_id = '$notitie_id'");
while ($row = mysql_fetch_row($result3)) {
	mysql_query_safe("INSERT INTO ppl2agenda ( ppl_id, agenda_id, allow_edit ) VALUES ( '{$row[0]}', '$agenda_id', '{$row[1]}' )");
}
mysql_free_result($result3);

header("Location: $http_path/?week={$_POST['week']}&grp2vak_id={$_POST['grp2vak_id']}&doelgroep={$_POST['doelgroep']}&lln={$_POST['lln']}");
?>
