<? include("include/init.php");
check_login();
check_isset_array($_GET, 'doelgroep', 'grp2vak_id', 'lln_id');
check_isnonempty_array($_GET, 'doelgroep', 'grp2vak_id', 'lln_id');
$doelgroep = $_GET['doelgroep'];
$grp2vak_id = mysql_escape_safe($_GET['grp2vak_id']);
$lln_id = mysql_escape_safe($_GET['grp2vak_id']);

switch ($doelgroep) {
	case 'zelf':
		$result = mysql_query_safe(<<<EOQ
SELECT CONCAT(week, CASE dag
		WHEN 1 THEN 'ma'
		WHEN 2 THEN 'di'
		WHEN 3 THEN 'wo'
		WHEN 4 THEN 'do'
		ELSE 'vr' END, lesuur) uur,
	CONCAT(orig.text, ' ', IFNULL(GROUP_CONCAT(DISTINCT
		CONCAT('[', tag, ']') SEPARATOR ''), '')
	) text, notities.creat `datum/tijd`, GROUP_CONCAT(KB_NAAM(ppl.naam0, ppl.naam1, ppl.naam2)) naam
#, CONCAT('<a href="issue.php?notitie_id=', notities.notitie_id, '">details</a>') details
FROM ppl2agenda
JOIN ppl2agenda AS anderen USING (agenda_id)
JOIN ppl ON ppl.ppl_id = anderen.ppl_id
JOIN agenda USING (agenda_id)
JOIN notities USING (notitie_id)
LEFT JOIN tags2notities AS moretags USING (notitie_id)
LEFT JOIN tags ON tags.tag_id = moretags.tag_id
JOIN notities AS orig ON orig.notitie_id = notities.parent_id
WHERE ppl2agenda.ppl_id = {$_SESSION['ppl_id']}
AND anderen.ppl_id != {$_SESSION['ppl_id']}
AND notities.text IS NULL
GROUP BY notities.notitie_id
ORDER BY IF(week < {$lesweken[0]}, 1, 0), week, dag, lesuur
EOQ
		);
		$table = sprint_table($result);
		mysql_free_result($result);
		break;
	default:
		throw new Exception('onmogelijke doelgroep', 2);
}

gen_html_header('Issues');
status(); ?>
<? echo($table); ?>
<? gen_html_footer(); ?>
