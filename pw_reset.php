<? include("include/init.php");
check_isset_array($_GET, 'userid', 'pw_reset_code');
check_isnonempty_array($_GET, 'userid', 'pw_reset_code');
$naam = sprint_singular("SELECT KB_NAAM(naam0, naam1, naam2) FROM ppl WHERE active IS NOT NULL AND login = '%s' AND SUBSTRING(SHA1(CONCAT(pw_reset_count, ppl_id, '$pw_reset_secret')), 1, 16) = '%s'",
	mysql_escape_safe(htmlspecialchars($_GET['userid'], ENT_QUOTES, 'UTF-8')),
	mysql_escape_safe($_GET['pw_reset_code']));
if (!$naam) regular_error($http_path.'/', (array) NULL, 
	'Wachtwoord Reset link is verouderd of onjuist. Stuur een mail '.
	'aan de beheerder '.htmlspecialchars($beheerder).', als het '.
	'probleem zich herhaalt.');
gen_html_header('Wachtwoord Veranderen', '$("input:password:visible:first").focus();');
status(); ?>
<p><form action="do_pw_reset.php" method="post" accept-charset="UTF-8">
<fieldset>
<legend>Wachtwoord veranderen</legend>
<p>Stel hier een nieuw wachtwoord in voor <? echo($naam.' ('.$_GET['userid'].')'); ?>.
<p><table>
<tr><td>Nieuw wachtwoord</td><td><input type="password" name="new_pw0"></td></tr>
<tr><td>Nogmaals nieuw wachtwoord</td><td><input type="password" name="new_pw1"></td></tr>
<input type="hidden" name="userid" value="<? echo($_GET['userid']) ?>">
<input type="hidden" name="pw_reset_code" value="<? echo($_GET['pw_reset_code']); ?>">
</table>
<input type="submit" value="Opslaan">
</fieldset>
</form>
<? gen_html_footer(); ?>
