<? require('include/init.php');
check_isset_array($_GET, 'callback', 'week_id', 'day_id', 'student_id');
// Now, the magic happens ...
$assignments = array();
$cursor = mysql_query_safe(<<<EOT
SELECT agenda_id, notitie_id, lesuur, (
	SELECT afkorting FROM vak WHERE vak_id = (
		SELECT vak_id FROM grp2vak WHERE grp2vak_id = (
			SELECT grp2vak_id FROM grp2vak2agenda WHERE agenda_id = agenda.`agenda_id`
		)
	)
) AS vak, (
	SELECT `text` FROM notities WHERE `notitie_id`=agenda.notitie_id
) AS `text` FROM agenda WHERE agenda_id IN (
	SELECT agenda_id FROM grp2vak2agenda WHERE grp2vak_id IN (
		SELECT grp2vak_id FROM grp2vak WHERE grp_id IN (
			SELECT grp_id FROM ppl2grp WHERE ppl_id = (
				SELECT ppl_id FROM ppl WHERE login="%s" LIMIT 1
			)
		)
	)
) AND `week`="%s" AND `dag`="%s" ORDER BY lesuur
EOT
, mysql_escape_safe($_GET['student_id']), 
mysql_escape_safe($_GET['week_id']), 
mysql_escape_safe($_GET['day_id']));

$assignments = array();

while ($row = mysql_fetch_array($cursor)) {
	$tag_cursor = mysql_query_safe('SELECT tag FROM tags WHERE 
tag_id IN (SELECT tag_id FROM tags2notities WHERE notitie_id=%s)', 
mysql_escape_safe($row['notitie_id']));
	$tags = array();
	while ($tag_row = mysql_fetch_array($tag_cursor)) {
		$tags[] = $tag_row['tag'];
	}
	if ($row['lesuur'] != $i) {
		if ($lesson) $assignments["{$i}"] = $lesson;
		$lesson = array();
		$i = $row['lesuur'];
	}
	$lesson[] = array(
		'id' => $row['notitie_id'],
		'text' => $row['text'],
		'tags' => $tags,
		'subject' => $row['vak']
	);
}
$assignments["{$i}"] = $lesson;

?>
<?= $_GET['callback'] ?>(<?= json_encode($assignments) ?>);
