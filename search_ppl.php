<?php
require_once('include/init.php');

// een gebruiker die niet ingelogd is, krijgt geen informatie uit de database
if (!test_login()) exit;

switch ($_GET['t']) {
	case 'own':
		$query = <<<EOQ
SELECT * FROM (
	SELECT CONCAT(KB_NAAM(naam0, naam1, naam2), ' (', login, ')') naam, ppl.ppl_id
	FROM ppl
	JOIN ppl2grp USING (ppl_id)
	JOIN grp2vak USING (grp_id)
	JOIN doc2grp2vak USING (grp2vak_id)
	JOIN grp USING (grp_id)
	WHERE doc2grp2vak.ppl_id = {$_SESSION['ppl_id']}
	AND grp.schooljaar = '$schooljaar'
	GROUP BY ppl.ppl_id
) bla
WHERE naam LIKE '%%%s%%' LIMIT 10
EOQ;
		break;
	case 'all':
		if (!have_cap('SU')) exit;
		$query = <<<EOQ
SELECT * FROM (
        SELECT CONCAT(KB_NAAM(naam0, naam1, naam2), ' (', login, ')') naam, ppl.ppl_id
        FROM ppl
) bla
WHERE naam LIKE '%%%s%%' LIMIT 10
EOQ;
		break;
	default:
		exit;
}

$result = mysql_query_safe($query,
	addcslashes(mysql_escape_safe(htmlspecialchars($_GET['q'],
			ENT_QUOTES, "UTF-8")), '%_'));

while ($row = mysql_fetch_row($result)) {
	print $row[0].'<'.$row[1].'>';
}
