<? require_once('include/init.php');
check_login();
if ($_SESSION['ppl_id'] != 3490) throw new Exception(2, 'test.php not for production use');

$query = <<<EOQ
SELECT moment, naam, notitie_id, parent_id, CONCAT(IFNULL(bla.text, 'OPENSTAAND ISSUE'), IFNULL(GROUP_CONCAT(CONCAT(' [', tags.tag, ']')), '')) text FROM (
	SELECT KB_NAAM(ppl.naam0, ppl.naam1, ppl.naam2) naam, naam0, naam1, naam2, agenda.week, agenda.dag, agenda.lesuur,
		CONCAT(agenda.week, CASE agenda.dag WHEN 1 THEN 'ma' WHEN 2 THEN 'di' WHEN 3 THEN 'wo' WHEN 4 THEN 'do' ELSE 'vr' END, agenda.lesuur) moment,
		IF(agenda.week > 30, 0, 1) sort, notities.notitie_id, notities.parent_id, notities.text
	FROM ppl
	JOIN ppl2grp USING (ppl_id)
	JOIN grp USING (grp_id)
	JOIN ppl2agenda USING (ppl_id)
	JOIN agenda USING (agenda_id)
	JOIN notities USING (notitie_id)
	JOIN ppl2agenda AS doc2agenda ON doc2agenda.agenda_id = agenda.agenda_id AND doc2agenda.ppl_id = 3490
	WHERE grp.naam = '1H7C'
	AND grp.schooljaar = '1011'
) bla LEFT JOIN tags2notities USING (notitie_id) LEFT JOIN tags USING (tag_id)
GROUP BY notitie_id
ORDER BY bla.naam0, bla.naam1, bla.naam2, sort, bla.week, bla.dag, bla.lesuur
EOQ;

$result = mysql_query_safe($query);

if (mysql_num_rows($result) == 0) exit;

$start = 0;
function cmp($a, $b) {
	if ($a['base'] > $b['base']) return 1;
	else if ($a['base'] < $b['base']) return -1;

	if ($a['level'] > $b['level']) return 1;
	else if ($a['level'] < $b['level']) return -1;

	return 0;
}

while ($start != mysql_numrows($result)) {
	$lln = mysql_result($result, $start, naam);
	echo $lln.'<br>';
	for ($i = $start; $i < mysql_num_rows($result); $i++) {
		if (mysql_result($result, $i, 'naam') != $lln) break;
		$notities[$i - $start]['text'] = mysql_result($result, $i, 'text');
		$notities[$i - $start]['moment'] = mysql_result($result, $i, 'moment');
		$notities[$i - $start]['notitie_id'] = mysql_result($result, $i, 'notitie_id');
		$notities[$i - $start]['parent_id'] = mysql_result($result, $i, 'parent_id');
		$avail[$i - $start] = mysql_result($result, $i, 'notitie_id');
		$notities[$i - $start]['tree'] = $notities[$i - $start]['notitie_id'].'/';
		$notities[$i-$start]['level'] = 1;
		$notities[$i-$start]['base'] = mysql_result($result, $i, 'notitie_id');
	}
	$no_notities = $i - $start;
	//echo $no_notities.'<br>';
	$start = $i;

	for ($i = 0; $i < $no_notities; $i++) {
		if (!in_array($notities[$i]['parent_id'], $avail)) {
			$notities[$i]['tree'] = '/'.$notities[$i]['tree'];
		}
	}

	$counter = 0;
	while ($counter < $no_notities) {
		$counter = 0;
		for ($i = 0; $i < $no_notities; $i++) {
			if(substr($notities[$i]['tree'], 0, 1) == '/') {
				$counter++;
			} else {
				$success = 0;
				foreach ($avail as $key => $value) {
					if ($value == $notities[$i]['parent_id']) {
						$success = 1;
						$notities[$i]['tree'] = $notities[$key]['tree'].$notities[$i]['tree'];
						$notities[$i]['level'] += $notities[$key]['level'];
						$notities[$i]['base'] = $notities[$key]['base'];
					}
				}
				if (!$success) {
					echo('fatal');
					exit;
				}
			}
		}
	}
	uasort($notities, 'cmp');

	foreach ($notities as $value) {
		$spaces = '';
		for ($j = 0; $j < $value['level'] - 1; $j++) {
			$spaces .= '-';
		}	
		echo $spaces.' '.$value['moment'].' '.$value['text'].'<br>';
		//echo $value['base'].' '.$value['level'].' '.$value['tree'].' '.$value['moment'].' '.$value['text'].'<br>';
	}

	echo '<br>';
	unset($notities);
}

gen_html_header('Report');

echo sprint_table($result);

gen_html_footer() ?>
