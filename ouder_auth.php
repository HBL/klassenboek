<? include("include/init.php");
check_isset_array($_GET, 'kind_id', 'ouder_id', 'ouder_auth_code');
check_isnonempty_array($_GET, 'kind_id', 'ouder_id', 'ouder_auth_code');
$row = mysql_fetch_array(mysql_query_safe(
	"SELECT ".
	//"CONCAT(KB_NAAM(lln.naam0, lln.naam1, lln.naam2), ' (', lln.login, ')')  lln_naam, ".
	"CONCAT(KB_NAAM(doc.naam0, doc.naam1, doc.naam2), ' (', doc.login, ')')  doc_naam ".
	"FROM ppl AS doc, ppl AS lln ".
	"WHERE doc.active IS NOT NULL ".
	"AND lln.active IS NOT NULL ".
	"AND doc.ppl_id = '%s' ".
	"AND lln.ppl_id = '%s' ".
	"AND SUBSTRING(SHA1(CONCAT('%s', '%s', '$pw_reset_secret')), 1, 16) = '%s'",
	mysql_escape_safe($_GET['ouder_id']),
	mysql_escape_safe($_GET['kind_id']),
	mysql_escape_safe($_GET['ouder_id']),
	mysql_escape_safe($_GET['kind_id']),
	mysql_escape_safe($_GET['ouder_auth_code'])));
if (!$row) regular_error($http_path.'/', (array) NULL, 
	'Ouder Akkoord link is verouderd of onjuist. Stuur een mail '.
	'aan de beheerder, '.htmlspecialchars($beheerder). ', als het '.
	'probleem zich herhaalt.');
gen_html_header('Ouder Akkoord');
status(); ?>
<p><form action="do_ouder_auth.php" method="POST" accept-charset="UTF-8">
<fieldset>
<legend>Ouder Akkoord</legend>
<p><? echo($row['doc_naam']) ?> wil graag toegang tot jouw online klassenboek. Als
deze persoon een ouder/verzorgen van jou is, mag je toegang geven.
<p><input type="submit" name="submit" value="Geef toegang">
<input type="submit" name="submit" value="Weiger toegang">
<input type="hidden" name="kind_id" value="<? echo($_GET['kind_id']) ?>">
<input type="hidden" name="ouder_id" value="<? echo($_GET['ouder_id']) ?>">
<input type="hidden" name="ouder_auth_code" value="<? echo($_GET['ouder_auth_code']) ?>">
</fieldset>
</form>
<? gen_html_footer(); ?>
