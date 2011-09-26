<? require_once('include/init.php');
check_login();
if ($_SESSION['ppl_id'] != 3490) throw new Exception(2, 'test.php not for production use');

$query = <<<EOQ
SELECT week, GROUP_CONCAT(toetsen ORDER BY toetsen SEPARATOR '<br>') toetsen FROM (
SELECT  week, CONCAT(grp.naam, '/', GROUP_CONCAT(CONCAT(CASE dag
		WHEN 1 THEN 'ma'
		WHEN 2 THEN 'di'
		WHEN 3 THEN 'wo'
		WHEN 4 THEN 'do'
		ELSE 'vr' END, lesuur, ':', vak.afkorting) ORDER BY dag, lesuur)) toetsen
 FROM tags JOIN tags2notities USING (tag_id) JOIN notities
USING (notitie_id) JOIN agenda USING (notitie_id) JOIN grp2vak2agenda
USING (agenda_id) JOIN grp2vak USING (grp2vak_id) LEFT JOIN vak USING
(vak_id) JOIN grp USING (grp_id) WHERE tags.tag = 'repetitie'
AND grp.naam LIKE '%%C%%'
 GROUP BY week, grp_id
) blaat  GROUP BY week
ORDER BY IF(week >
30, 0, 1), week
EOQ;

$result = mysql_query_safe($query);

gen_html_header('Cijferinvoer');

echo sprint_table($result);

gen_html_footer() ?>
