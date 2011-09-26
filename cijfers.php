<? require_once('include/init.php');
check_login();
check_isset_array($_GET, 'grp2vak_id');
check_isnonempty_array($_GET, 'grp2vak_id');
$grp2vak_id = mysql_escape_safe($_GET['grp2vak_id']);

$result2 = mysql_query_safe(<<<EOQ
SELECT notities.notitie_id, CONCAT(week, CASE dag
	WHEN 1 THEN 'ma'
	WHEN 2 THEN 'di'
	WHEN 3 THEN 'wo'
	WHEN 4 THEN 'do'
	ELSE 'vr' END, lesuur) uur, periode.tag periode_id, text
FROM notities
JOIN tags2notities USING (notitie_id)
JOIN tags USING (tag_id)
JOIN agenda USING (notitie_id)
JOIN grp2vak2agenda USING (agenda_id)
JOIN grp2vak USING (grp2vak_id)
JOIN grp USING (grp_id)
JOIN vak USING (vak_id)
JOIN doc2grp2vak USING (grp2vak_id)
LEFT JOIN tags2notities AS periode2notities ON periode2notities.notitie_id = notities.notitie_id AND periode2notities.tag_id = ANY ( SELECT tag_id FROM tags WHERE tag = 'per1' OR tag = 'per2' OR tag = 'per3' )
LEFT JOIN tags AS periode ON periode.tag_id = periode2notities.tag_id
WHERE grp2vak_id = '$grp2vak_id' AND (
	tags.tag = 'repetitie' OR 
	tags.tag = 'so' OR
	tags.tag = 'vt' OR
	tags.tag = 'st' OR
	tags.tag = 'inleveren'
) AND
doc2grp2vak.ppl_id = {$_SESSION['ppl_id']} AND
grp.schooljaar = '$schooljaar' AND
agenda.schooljaar = '$schooljaar'
GROUP BY notitie_id
ORDER BY IF({$lesweken[0]} > week, 1, 0), week, dag, lesuur
EOQ
);

$select = '';
$join = '';
$count = 0;
$th = '';
$th2 = '';
$per1_select = NULL;
$per1_count = 0;
$per2_select = NULL;
$per2_count = 0;
$per3_select = NULL;
$per3_count = 0;

while ($row = mysql_fetch_array($result2)) {
	mysql_query_safe("INSERT IGNORE INTO cijfers (notitie_id, ppl_id) SELECT {$row[0]}, ppl_id FROM ppl JOIN ppl2grp USING (ppl_id) JOIN grp2vak USING (grp_id) WHERE grp2vak_id = '$grp2vak_id'");
	$select .= ", l$count.cijfer {$row[1]}";
	$join .=<<<EOT
LEFT JOIN (
	SELECT ppl_id, cijfer FROM cijfers
	WHERE notitie_id = '{$row[0]}' 
) l$count USING (ppl_id)
EOT;
	$selected[0] = $selected[1] = $selected[2] = $selected[3] = '';
	if ($row['periode_id'] == 'per1') {
		$selected[1] = ' selected';
		if ($per1_count == 0) $per1_select = "l$count.cijfer";
		else $per1_select .= " + l$count.cijfer";
		$per1_count++;
	} else if ($row['periode_id'] == 'per2') {
		$selected[2] = ' selected';
		if ($per2_count == 0) $per2_select = "l$count.cijfer";
		else $per2_select .= " + l$count.cijfer";
		$per2_count++;
	} else if ($row['periode_id'] == 'per3') {
		$selected[3] = ' selected';
		if ($per3_count == 0) $per3_select = "l$count.cijfer";
		else $per3_select .= " + l$count.cijfer";
		$per3_count++;
	} else $selected[0] = ' selected';

	$th2 .= '<th><select name="'.$row[0].'-per"><option'.$selected[0].' value=""></option><option'.$selected[1].' value="per1">1</option><option'.$selected[2].' value="per2">2</option><option'.$selected[3].' value="per3">3</option></select>'."\n";
	$nid[$count] = $row[0];
	$count++;
}
mysql_data_seek($result2, 0);

if ($per1_select) {
	$select .= ", ROUND(( $per1_select )/$per1_count, 1) per1";
}
if ($per2_select) {
	$select .= ", ROUND(( $per2_select )/$per2_count, 1) per2";
}
if ($per3_select) {
	$select .= ", ROUND(( $per3_select )/$per3_count, 1) per3";
}
$addselect = '';
if ($per1_select) {
	$add = 2;
	$addselect = ", per1 rap1";
	if ($per2_select) {
		$add = 4;
		$addselect .= ", ROUND((per1 + per2)/2, 1) rap2";
		if ($per3_select) {
			$add = 6;
			$addselect .= ", ROUND((per1 + per2 + per3)/3, 0) rap3";
		}
	}
}

$result = mysql_query_safe(<<<EOQ
SELECT *$addselect FROM (
	SELECT ppl_id, login nummer, KB_NAAM(naam0, naam1, naam2) naam$select
	FROM ppl
	LEFT JOIN ( 
		SELECT sortkey, ppl_id FROM customorder
		WHERE doc_ppl_id = {$_SESSION['ppl_id']}
		AND grp2vak_id = '$grp2vak_id'
	) c USING (ppl_id)
	JOIN ppl2grp USING (ppl_id)
	JOIN grp2vak USING (grp_id)
	$join
	WHERE grp2vak_id = '$grp2vak_id'
	ORDER BY sortkey, naam0, naam1, naam2
) perioden
EOQ
);

$num = mysql_num_rows($result);

gen_html_header('Cijferinvoer', <<<EOT
$("#cijfers").autoAdvance();
EOT
        , 'jquery.field.min.js');
//echo(htmlspecialchars(sprint_tag_select('per', NULL, 'per1', 'per2', 'per3')));
?>

<form id="cijfers" action="do_cijfers.php" method="POST" accept-charset="UTF-8">
<table>
<tr><td><td>periode:
<? echo($th2); ?>
<tr>
<? $num_fields = mysql_num_fields($result);
for ($i = 1; $i < $num_fields; $i++) {
	echo('<th>'.mysql_field_name($result, $i));
}?>
<? while ($row = mysql_fetch_row($result)) {
$idx = 1; ?>
<tr><td><? echo($row[1]) ?><td><? echo($row[2]."\n");
for ($i = 0; $i < $count; $i++) { 
	if ($row[3+$i]) {
		$sum[$i] += $row[3+$i];
		$aantal[$i] += 1;
	}
?>
<td><input type="text" tabindex="<? echo($num*$i+$idx++) ?>" maxlength="2" size="2" name="<? echo($row[0].'-'.$nid[$i]) ?>" value="<? echo($row[3+$i]) ?>">
<? } 
for ($i = $count + 3; $i < $count + 3 + $add; $i++) {
	echo('<td>'.$row[$i]);
	if ($row[$i]) {
		$sum[$i-3] += $row[$i];
		$aantal[$i-3] += 1;
	}
}
} ?>
<tr><td><td>gemiddeld
<? for ($i = 0; $i < $count + $add; $i++) { ?>
<td><? if($aantal[$i] >= 1) printf("%.2F", round($sum[$i]/$aantal[$i], 2)); ?>
<? } ?>
</table>
<input type="submit" value="Opslaan">
<input type="hidden" name="grp2vak_id" value="<? echo($grp2vak_id) ?>">
</form>
<? gen_html_footer() ?>

