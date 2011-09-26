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

//echo(sprint_table($result2));

$select = '';
$join = '';
$count = 0;
$th = '';
$th2 = '';
while ($row = mysql_fetch_array($result2)) {
	//mysql_query_safe("INSERT IGNORE INTO cijfers (notitie_id, ppl_id) SELECT {$row[0]}, ppl_id FROM ppl JOIN ppl2grp USING (ppl_id) JOIN grp2vak USING (grp_id) WHERE grp2vak_id = '$grp2vak_id'");
	$select .= ", l$count.cijfer {$row[1]}";
	$join .=<<<EOT
LEFT JOIN (
	SELECT ppl_id, cijfer FROM cijfers
	WHERE notitie_id = '{$row[0]}' 
) l$count USING (ppl_id)
EOT;
	$th .= "<th><span title=\"{$row['text']}\">{$row[1]}</span>\n";
	$selected[0] = $selected[1] = $selected[2] = $selected[3] = '';
	if ($row['periode_id'] == 'per1') $selected[1] = ' selected';
	else
	if ($row['periode_id'] == 'per2') $selected[2] = ' selected';
	else
	if ($row['periode_id'] == 'per3') $selected[3] = ' selected';
	else $selected[0] = ' selected';

	$th2 .= '<th><select name="'.$row[0].'-per"><option'.$selected[0].' value=""></option><option'.$selected[1].' value="per1">1</option><option'.$selected[2].' value="per2">2</option><option'.$selected[3].' value="per3">3</option></select>'."\n";
	$nid[$count] = $row[0];
	$count++;
}

$result_per1 = mysql_query_safe(<<<EOQ
SELECT ROUND(AVG(cijfer), 1) gem
FROM ppl
LEFT JOIN ( 
	SELECT sortkey, ppl_id FROM customorder
	WHERE doc_ppl_id = {$_SESSION['ppl_id']}
	AND grp2vak_id = '$grp2vak_id'
) c USING (ppl_id)
JOIN ppl2grp USING (ppl_id)
JOIN grp2vak USING (grp_id)
JOIN grp2vak2agenda USING (grp2vak_id)
JOIN agenda USING (agenda_id)
JOIN notities USING (notitie_id)
JOIN tags2notities USING (notitie_id)
JOIN tags USING (tag_id)
JOIN cijfers USING (ppl_id, notitie_id)
JOIN tags2notities AS periode2notities ON periode2notities.notitie_id = notities.notitie_id AND  periode2notities.tag_id = ( SELECT tag_id FROM tags WHERE tag = 'per1' )
WHERE grp2vak_id = '$grp2vak_id'
AND tags.tag = 'repetitie'
GROUP BY ppl_id
ORDER BY sortkey, naam0, naam1, naam2
EOQ
);

$result_per2 = mysql_query_safe(<<<EOQ
SELECT ROUND(AVG(cijfer), 1) gem
FROM ppl
LEFT JOIN ( 
	SELECT sortkey, ppl_id FROM customorder
	WHERE doc_ppl_id = {$_SESSION['ppl_id']}
	AND grp2vak_id = '$grp2vak_id'
) c USING (ppl_id)
JOIN ppl2grp USING (ppl_id)
JOIN grp2vak USING (grp_id)
JOIN grp2vak2agenda USING (grp2vak_id)
JOIN agenda USING (agenda_id)
JOIN notities USING (notitie_id)
JOIN tags2notities USING (notitie_id)
JOIN tags USING (tag_id)
JOIN cijfers USING (ppl_id, notitie_id)
JOIN tags2notities AS periode2notities ON periode2notities.notitie_id = notities.notitie_id AND  periode2notities.tag_id = ( SELECT tag_id FROM tags WHERE tag = 'per2' )
WHERE grp2vak_id = '$grp2vak_id'
AND tags.tag = 'repetitie'
GROUP BY ppl_id
ORDER BY sortkey, naam0, naam1, naam2
EOQ
);

$result_per3 = mysql_query_safe(<<<EOQ
SELECT ROUND(AVG(cijfer), 1) gem
FROM ppl
LEFT JOIN ( 
	SELECT sortkey, ppl_id FROM customorder
	WHERE doc_ppl_id = {$_SESSION['ppl_id']}
	AND grp2vak_id = '$grp2vak_id'
) c USING (ppl_id)
JOIN ppl2grp USING (ppl_id)
JOIN grp2vak USING (grp_id)
JOIN grp2vak2agenda USING (grp2vak_id)
JOIN agenda USING (agenda_id)
JOIN notities USING (notitie_id)
JOIN tags2notities USING (notitie_id)
JOIN tags USING (tag_id)
JOIN cijfers USING (ppl_id, notitie_id)
JOIN tags2notities AS periode2notities ON periode2notities.notitie_id = notities.notitie_id AND  periode2notities.tag_id = ( SELECT tag_id FROM tags WHERE tag = 'per3' )
WHERE grp2vak_id = '$grp2vak_id'
AND tags.tag = 'repetitie'
GROUP BY ppl_id
ORDER BY sortkey, naam0, naam1, naam2
EOQ
);

//echo sprint_table($result_per1);
 
$result = mysql_query_safe(<<<EOQ
SELECT ppl_id, login leerling, KB_NAAM(naam0, naam1, naam2) naam$select
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
EOQ
);
/*
LEFT JOIN ( 
	SELECT key, ppl_id FROM customorder
	WHERE doc_ppl_id = {$_SESSION['ppl_id']}
	AND grp2vak_id = '$grp2vak_id'
) c USING (ppl_id)
 */
$num = mysql_num_rows($result);

//$grp2vak_select = sprint_grp2vak_select($_GET['grp2vak_id'], " onchange=\"document.select.doelgroep[1].checked = true; document.select.submit();\"", &$grp2vak_id, 0);

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
<tr><th>nummer<th>naam
<? echo($th); ?>
<th>per1<th>per2<th>per3<th>rap1<th>rap2<th>rap3
<? while ($row = mysql_fetch_row($result)) {
$row_per1 = mysql_fetch_row($result_per1);
$row_per2 = mysql_fetch_row($result_per2);
$row_per3 = mysql_fetch_row($result_per3);
$idx = 1; ?>
<tr><td><? echo($row[1]) ?><td><? echo($row[2]."\n"); 
for ($i = 0; $i < $count; $i++) { ?>
<td><input type="text" tabindex="<? echo($num*$i+$idx++) ?>" maxlength="2" size="2" name="<? echo($row[0].'-'.$nid[$i]) ?>" value="<? echo($row[3+$i]) ?>">
<? } ?>
<td><? echo($row_per1[0]); ?><td><? echo($row_per2[0]); ?><td><? echo($row_per3[0]); ?><td><? echo($row_per1[0]) ?>
<td><? if(isset($row_per1[0]) && isset($row_per2[0])) {
	printf("%.1F", round(($row_per1[0]+$row_per2[0])/2,1));
} ?><td>
<? if (isset($row_per1[0]) && isset($row_per2[0]) && isset($row_per3[0])) { printf("%.1F", round(($row_per1[0]+$row_per2[0]+$row_per3[0])/3, 0)); }
} ?>
</table>
<input type="submit" value="Opslaan">
<input type="hidden" name="grp2vak_id" value="<? echo($grp2vak_id) ?>">
</form>
<? gen_html_footer() ?>
