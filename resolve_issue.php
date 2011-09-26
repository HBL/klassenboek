<? include("include/init.php");
check_login();
check_isset_array($_GET, 'notitie_id', 'dag', 'lesuur');
check_isnonempty_array($_GET, 'notitie_id', 'dag', 'lesuur');

$notitie_id = mysql_escape_safe($_GET['notitie_id']);
$dag = mysql_escape_safe($_GET['dag']);
$lesuur = mysql_escape_safe($_GET['lesuur']);
$lln_id = mysql_escape_safe($_GET['lln']);

$issue = mysql_query_safe(<<<EOQ
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
AND anderen.ppl_id = '$lln_id'
AND notities.text IS NULL
AND notities.notitie_id = '$notitie_id'
GROUP BY notities.notitie_id
EOQ
);

if (mysql_num_rows($issue) == 0) {
	$_SESSION['success'] = 'Issue bestaat niet (meer)';
	header('Location: '.$http_path.'/?week='.$_GET['week'].'&grp2vak_id='.$_GET['grp2vak_id'].'&doelgroep='.$_GET['doelgroep'].'&lln='.$_GET['lln']);
	exit;
}

gen_html_header('Resolve issue');

echo(sprint_table($issue));
?>
<form action="do_resolve_issue.php" accept-charset="UTF-8" method="POST">

