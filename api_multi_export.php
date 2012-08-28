<? require('include/init.php');
check_isset_array($_GET, 'week_id', 'student_ids');
header('Content-Type: text/javascript'); // BB4 won't recognize JSONP-calls without this header been sent ...
$student_ids = explode(',', $_GET['student_ids']);
$students = array();
foreach ($student_ids as $student_id) {
$assignments = array();
$cursor = mysql_query_safe(<<<EOT
SELECT agenda_id, notitie_id, lesuur, dag, 
	(SELECT afkorting FROM vak WHERE vak_id = 
		(SELECT vak_id FROM grp2vak WHERE grp2vak_id = 
			(SELECT grp2vak_id FROM grp2vak2agenda WHERE agenda_id = agenda.`agenda_id`)
        )
	) AS vak,
	(SELECT naam FROM grp WHERE grp_id = 
		(SELECT grp_id FROM grp2vak WHERE grp2vak_id = 
			(SELECT grp2vak_id FROM grp2vak2agenda WHERE agenda_id = agenda.`agenda_id`)
        )
	) AS groep,
	(SELECT `text` FROM notities WHERE `notitie_id`=agenda.notitie_id
	) AS `text` 
FROM agenda WHERE agenda_id IN 
	(SELECT agenda_id FROM grp2vak2agenda WHERE grp2vak_id IN 
		(SELECT grp2vak_id FROM grp2vak WHERE grp_id IN 
			(SELECT grp_id FROM ppl2grp WHERE ppl_id = 
				(SELECT ppl_id FROM ppl WHERE login="%s" LIMIT 1)
            )
       	) OR grp2vak_id IN
		(SELECT grp2vak_id FROM doc2grp2vak WHERE ppl_id = 
			(SELECT ppl_id FROM ppl WHERE login="%s" LIMIT 1)
		)
	) AND `week`="%s" AND schooljaar="%s" ORDER BY dag, lesuur
EOT
, mysql_escape_safe($student_id), mysql_escape_safe($student_id),
mysql_escape_safe($_GET['week_id']), mysql_escape_safe($schooljaar));

$assignments = array();

while ($row = mysql_fetch_array($cursor)) {
	$tag_cursor = mysql_query_safe('SELECT tag FROM tags WHERE 
tag_id IN (SELECT tag_id FROM tags2notities WHERE notitie_id=%s)', 
mysql_escape_safe($row['notitie_id']));
	$tags = array();
	while ($tag_row = mysql_fetch_array($tag_cursor)) {
		$tags[] = $tag_row['tag'];
	}
	if ($row['lesuur'] != $i || $row['dag'] != $d) {
		if ($lesson) $day["{$i}"] = $lesson;
		$lesson = array();
		$i = $row['lesuur'];
	}
	if ($row['dag'] != $d) {
		if ($day) $assignments["{$d}"] = $day;
		$day = array();
		$d = $row['dag'];
	}
	$lesson[] = array(
		'id' => $row['notitie_id'],
		'text' => $row['text'],
		'tags' => $tags,
		'subject' => $row['vak'],
		'group' => $row['groep']
	);
}
if ($lesson) $day["{$i}"] = $lesson;
if ($day) $assignments["{$d}"] = $day;
$students[$student_id] = $assignments;
}
?>
<?= json_encode($students) ?>
