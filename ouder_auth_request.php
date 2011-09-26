<? include("include/init.php");
check_login();
if ($_SESSION['type'] != 'ouder') throw new Exception('alleen toegankelijk voor ouders', 2);
gen_html_header('Kind toevoegen', '$("input:text:visible:first").focus();');
status(); ?>
<p><form action="do_ouder_auth_request.php" method="POST" accept-charset="UTF-8">
<fieldset>
<legend>Kind toevoegen</legend>
Hier kunt u een kind toevoegen aan uw account. Typ het leerlingnummer van uw kind in het
onderstaande vakje. Uw kind krijgt een email met het verzoek om uw aanmelding goed te keuren.
<p><input type="text" name="login" value="<? echo($_GET['login']) ?>">
<p>Toon aan dat u een mens bent door de twee onderstaande woorden in te typen.
<? require_once('include/recaptcha.php'); recaptcha_ask(); ?>
<p><input type="submit" value="Verzoek sturen">
</fieldset>
</form>
<? gen_html_footer(); ?>
