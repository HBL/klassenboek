<? include("include/init.php");
check_login();

if ($_POST['submit'] != "Verwijder" && $_POST['submit'] != "Opslaan") {
	header("Location: index.php?week=${_POST['week']}&dag=${_POST['dag']}&lesuur=${_POST['lesuur']}&doelgroep=${_POST['doelgroep']}&grp2vak_id=${_POST['grp2vak_id']}&lln=${_POST['lln']}");
	exit;
}

if (!verify_grp2vak($_POST['grp2vak_id'])) throw new Exception('ingelogd persoon heeft geen schrijfrechten in klassenboek groep');

$grp2vak_id = $_POST['grp2vak_id'];

if ($_POST['submit'] == "Verwijder" ) {
	delete_from_teletop($_POST['notitie_id']);

	$result = mysql_query_safe("SELECT agenda_id FROM agenda ".
		"JOIN grp2vak2agenda USING (agenda_id) ".
		"WHERE notitie_id = '%s'",
		mysql_escape_safe($_POST['notitie_id']));
	$query = "DELETE FROM grp2vak2agenda WHERE 0";
	while ($row = mysql_fetch_row($result))
		$query .= " OR agenda_id = ${row[0]}";
	mysql_free_result($result);
	mysql_query_safe($query);
	mysql_query_safe("DELETE FROM tags2notities WHERE notitie_id = '%s'",
		mysql_escape_safe($_POST['notitie_id']));
} else {
	if (check_week($_POST['week'])) { $week = $_POST['week']; }
	else { header("Location: index.php"); exit; }
	if (check_dag($_POST['dag'])) { $dag = $_POST['dag']; }
	else { header("Location: index.php"); exit; }
	if (check_lesuur($_POST['lesuur'])) { $lesuur = $_POST['lesuur']; }
	else { header("Location: index.php"); exit; }

	check_group_tags_allowed($_POST['tags']);
	mysql_query_safe("DELETE FROM tags2notities WHERE notitie_id = '%s'",
		mysql_escape_safe($_POST['notitie_id']));

	for ($i = 0; $i < count($_POST['tags']); $i++) {
		mysql_query_safe("INSERT INTO tags2notities ".
			"( tag_id, notitie_id ) VALUES ".
			"( '%s', '%s' )", mysql_escape_safe($_POST['tags'][$i]),
			mysql_escape_safe($_POST['notitie_id']));
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
		mysql_query_safe("INSERT INTO tags2notities ( tag_id, notitie_id ) VALUES( ( SELECT tag_id FROM tags WHERE tag = '%s' ), '%s' )", mysql_escape_safe($_POST['per']), mysql_escape_safe($_POST['notitie_id']));
	}

	mysql_query_safe("UPDATE notities ".
		"JOIN agenda USING (notitie_id) ".
		"JOIN grp2vak2agenda USING (agenda_id) ".
		"SET notities.text = '%s', ".
		"agenda.week = '%s', ".
		"agenda.dag = '%s', ".
		"agenda.lesuur = '%s', ".
		"grp2vak_id = '$grp2vak_id' ".
		"WHERE notities.notitie_id = '%s'",
		mysql_escape_safe(bbtohtml(htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8'))),
		mysql_escape_safe($_POST['week']),
		mysql_escape_safe($_POST['dag']),
		mysql_escape_safe($_POST['lesuur']),
		mysql_escape_safe($_POST['notitie_id']));

	if ($_POST['teletop'] == 'yes') {
		/* de gebruiker wil deze notitie opnemen in TeleTOP */

		/* kijk of deze notitie er al staat */
		$result = mysql_query_safe("SELECT vaksite_id FROM notities2teletop WHERE notitie_id = '%s'", mysql_escape_safe($_POST['notitie_id']));
		$result2 = mysql_query_safe("SELECT vaksite_id FROM grp2vak2vaksite JOIN vaksites USING (vaksite_id) WHERE grp2vak_id = $grp2vak_id");

		if (!mysql_numrows($result)) {
			/* de notitie staat momenteel niet in TeleTOP, we zetten de notitie erin als de vaksite bekend is */
			if (mysql_numrows($result2)) add_to_teletop($grp2vak_id, $_POST['notitie_id'], $week, $dag, $lesuur.'e uur: '.bbtohtml(
					htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8')).$tags, $sa);
		} else if (mysql_numrows($result2) && mysql_result($result, 0, 'vaksite_id') == mysql_result($result2, 0, 'vaksite_id')) {
			//$_SESSION['errormsg'] = 'Het aanpassen van een notitie terwijl deze blijft in dezelfde vaksite is nog niet geimplementeerd';
			change_in_teletop($grp2vak_id, $_POST['notitie_id'], $week, $dag, $lesuur.'e uur: '.bbtohtml(
				htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8')).$tags, $sa);
		} else {
			delete_from_teletop($_POST['notitie_id']);
			if (mysql_numrows($result2)) add_to_teletop($grp2vak_id, $_POST['notitie_id'], $week, $dag, $lesuur.'e uur: '.bbtohtml(
					htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8')).$tags, $sa);
			else $_SESSION['errormsg'] = 'De nieuwe klas/vak combinatie heeft geen geassocieerde vaksite, de notitie staat niet (meer) in TeleTOP&reg;';
		}
	} else {
		// wis from TeleTOP (als het erin staat)
		delete_from_teletop($_POST['notitie_id']);
	}
}
	
header("Location: index.php?week=${_POST['week']}&dag=${_POST['dag']}&lesuur=${_POST['lesuur']}&doelgroep=${_POST['doelgroep']}&grp2vak_id=${_POST['grp2vak_id']}&lln=${_POST['lln']}");

mysql_close(); ?>
