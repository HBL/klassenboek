<? require("include/init.php");
check_login_and_cap('SU');
/* als het vragende script geen ppl_id meezendt, dan moeten wij zoeken op
 * basis van de autocomplete zoekterm. Eerst op exacte loginnaam en dan
 * een LIKE %% query op de volledige naam. Wanneer er 1 resultaat komt, dan nemen
 * we dat over */
if ($_POST['ppl_id'] == '') {
	$result = mysql_query_safe("SELECT ppl_id FROM ppl WHERE login = '%s'",
		mysql_escape_safe(htmlspecialchars($_POST['q'])));
	if (mysql_numrows($result) == 0) {
		mysql_free_result($result);
		$result = mysql_query_safe("SELECT ppl_id FROM ppl WHERE CONCAT(KB_NAAM(naam0, naam1, naam2), ' (', login, ')') LIKE '%%%s%%'", addcslashes(mysql_escape_safe(htmlspecialchars($_POST['q'], ENT_QUOTES, "UTF-8")), '%_'));
		if (mysql_numrows($result) == 0) regular_error('beheer.php', (array) NULL, 'Gebruiker niet gevonden met zoekterm <code>%s</code>.', htmlspecialchars($_POST['q']));
		if (mysql_numrows($result) > 1) regular_error('beheer.php', (array) NULL,
			'Er zijn meerdere gebruikers gevonden op zoekterm <code>%s</code>', htmlspecialchars($_POST['q']));
		$ppl_id = mysql_result($result, 0, 'ppl_id');
	} else if (mysql_numrows($result) > 1) regular_error('beheer.php', (array) NULL,
		'Er zijn meerdere gebruikers gevonden op zoekterm <code>%s</code>',
		htmlspecialchars($_POST['q']));
	else $ppl_id = mysql_result($result, 0, 'ppl_id');
} else {
	$ppl_id = $_POST['ppl_id'];
}
$result = mysql_query_safe(
	"SELECT KB_NAAM(naam0, naam1, naam2) naam, ".
	"type, ppl_id, login ".
	"FROM ppl WHERE ppl_id = '%s' AND active IS NOT NULL; ",
		mysql_escape_safe($ppl_id));
if ($row = mysql_fetch_assoc($result)) {
	$_SESSION['type'] = $row['type'];
	$_SESSION['name'] = $row['naam'];
	$_SESSION['login'] = $row['login'];
	$_SESSION['ppl_id'] = $row['ppl_id'];
	mysql_log('su_success');
} else regular_error('beheer.php', (array)NULL,
	'Gebruiker <code>'.htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8').'</code> bestaat niet.');

header('Location: index.php'); ?>
