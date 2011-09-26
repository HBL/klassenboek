<? include("include/init.php");
check_login();
check_isset_array($_GET, 'action_id', 'isgrp', 'notitie_id', 'lln');
check_isnonempty_array($_GET, 'action_id', 'isgrp', 'notitie_id', 'lln');

$notitie_id = mysql_escape_safe($_GET['notitie_id']);
$action_id = mysql_escape_safe($_GET['action_id']);
$lln_id = mysql_escape_safe($_GET['lln']);

//print_r($_GET);

// auth
if ($_GET['isgrp'] == 1) {
	$isgrp = 1;
	$result = mysql_query_safe(<<<EOQ
SELECT bla5.replaceby_tag_id, bla5.new_tag_id, notitie_id, GROUP_CONCAT(IF(action_id = '$action_id', 1, NULL) SEPARATOR '') action_id_ok, GROUP_CONCAT(dont) dont FROM (
SELECT notities.notitie_id, actions.*, GROUP_CONCAT(CONCAT(bla.agenda_id, '-', tags2children.tag_id)) dont
FROM notities 
JOIN agenda USING (notitie_id)
JOIN grp2vak2agenda USING (agenda_id)
JOIN grp2vak USING (grp2vak_id)
JOIN doc2grp2vak USING (grp2vak_id)
JOIN tags2notities USING (notitie_id)
JOIN tags2actions USING (tag_id)
JOIN actions USING (action_id)
LEFT JOIN notities AS children ON notities.notitie_id = children.parent_id
LEFT JOIN agenda AS c_agenda ON children.notitie_id = c_agenda.notitie_id
LEFT JOIN tags2notities AS tags2children ON children.notitie_id = tags2children.notitie_id AND actions.new_tag_id = tags2children.tag_id
LEFT JOIN ppl2agenda AS bla ON c_agenda.agenda_id = bla.agenda_id AND bla.ppl_id = '$lln_id'
WHERE notities.notitie_id = '$notitie_id' AND doc2grp2vak.ppl_id = '{$_SESSION['ppl_id']}' AND action_id = '$action_id'
GROUP BY action_id
) bla5
#WHERE action_id = '$action_id'
GROUP BY notitie_id
EOQ
);
} else if ($_GET['isgrp'] == 0) {
	$isgrp = 0;
	$result = mysql_query_safe(<<<EOQ
SELECT 
	GROUP_CONCAT(IF(action_id = '$action_id', bla5.replaceby_tag_id, NULL)) replaceby_tag_id,
	GROUP_CONCAT(IF(action_id = '$action_id', bla5.new_tag_id, NULL)) new_tag_id,
	notitie_id, GROUP_CONCAT(IF(action_id = '$action_id', 1, NULL) SEPARATOR '') action_id_ok, GROUP_CONCAT(dont) dont FROM (
SELECT notities.notitie_id, actions.*, GROUP_CONCAT(CONCAT(bla.agenda_id, '-', tags2children.tag_id)) dont
FROM notities
JOIN agenda USING (notitie_id)
JOIN ppl2agenda USING (agenda_id)
JOIN tags2notities USING (notitie_id)
JOIN tags2actions USING (tag_id)
JOIN actions USING (action_id)
JOIN ppl2agenda AS auth USING (agenda_id)
LEFT JOIN ppl2agenda AS targets ON agenda.agenda_id = targets.agenda_id AND targets.allow_edit = 0
LEFT JOIN notities AS children ON notities.notitie_id = children.parent_id
LEFT JOIN agenda AS c_agenda ON children.notitie_id = c_agenda.notitie_id
LEFT JOIN tags2notities AS tags2children ON children.notitie_id = tags2children.notitie_id AND actions.new_tag_id = tags2children.tag_id
LEFT JOIN ppl2agenda AS bla ON c_agenda.agenda_id = bla.agenda_id AND bla.ppl_id = targets.ppl_id
WHERE notities.notitie_id = '$notitie_id' AND auth.ppl_id = '{$_SESSION['ppl_id']}' AND action_id = '$action_id'
GROUP BY action_id
) bla5
#WHERE action_id = '$action_id'
GROUP BY notitie_id
EOQ
);
} else throw new Exception('illegal value of isgrp', 2);
//if ($_SESSION['ppl_id'] == 3490) echo sprint_table($result);


if (mysql_num_rows($result) == 0 || mysql_result($result, 0, 'dont')) throw new Exception("actie door {$_SESSION['ppl_id']} niet mogelijk op notitie_id=$notitie_id", 2);

if (mysql_result($result, 0, 'replaceby_tag_id')) {
	mysql_query_safe("DELETE FROM tags2notities USING tags2notities JOIN tags2actions USING (tag_id) WHERE action_id = '$action_id' AND notitie_id = '$notitie_id'");
	mysql_query_safe("INSERT INTO tags2notities (tag_id, notitie_id) VALUES ( '%s', '$notitie_id' )", mysql_result($result, 0, 'replaceby_tag_id'));
}

if (mysql_result($result, 0, 'new_tag_id')) {
	$result3 = mysql_query_safe("SELECT agenda.dag, agenda.lesuur, agenda.week, agenda.schooljaar FROM agenda WHERE notitie_id = '$notitie_id'");
        if (mysql_numrows($result3) != 1) throw new Exception('geen unieke notitie met id='.$notitie_id.' gevonden', 2);
        $row = mysql_fetch_array($result3);
        mysql_query_safe("INSERT INTO notities ( parent_id ) VALUES ( '$notitie_id' )");
        $new_notitie_id = mysql_insert_id();
	mysql_query_safe("INSERT INTO tags2notities ( tag_id, notitie_id ) VALUES ( '%s', '$new_notitie_id' )", mysql_result($result, 0, 'new_tag_id'));
	mysql_query_safe("INSERT INTO agenda ( schooljaar, week, dag, lesuur, notitie_id ) VALUES ( '{$row['schooljaar']}', '{$row['week']}', '{$row['dag']}', '{$row['lesuur']}', '$new_notitie_id' )");
	$agenda_id = mysql_insert_id();
	mysql_free_result($result3);
	if ($isgrp == 0) {
		$result3 = mysql_query_safe("SELECT ppl_id, allow_edit FROM ppl2agenda JOIN agenda USING (agenda_id) WHERE notitie_id = '$notitie_id'");
		while ($row = mysql_fetch_row($result3)) {
			mysql_query_safe("INSERT INTO ppl2agenda ( ppl_id, agenda_id, allow_edit ) VALUES ( '{$row[0]}', '$agenda_id', '{$row[1]}' )");
		}
		mysql_free_result($result3);
	} else {
		mysql_query_safe("INSERT INTO ppl2agenda ( ppl_id, agenda_id, allow_edit ) VALUES ( '{$_SESSION['ppl_id']}', '$agenda_id', '1' ), ( '$lln_id', '$agenda_id', '0' )");
	}
}	

header("Location: $http_path/?week={$_GET['week']}&grp2vak_id={$_GET['grp2vak_id']}&doelgroep={$_GET['doelgroep']}&lln={$_GET['lln']}");

?>
