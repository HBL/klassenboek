<? include("include/init.php");
check_nologin();
gen_html_header('Aanmaken', '$("input:text:visible:first").focus();');
status();
?>
<p><form action="do_aanmaken.php" accept-charset="UTF-8" method="post">
<fieldset>
<legend>Aanmaken</legend>
Hier kun je een account aanmaken. Vul je leerlingnummer/afkorting, aanmaakcode en emailadres in. Klik
daarna op 'Verzend'. Je krijgt een email met daarin een link naar een pagina
waarop je je wachtwoord kunt instellen.

<p>Ouders gebruiken de bovenstaande link 'Ouders' om een account aan te maken.

<table>
<tr><td>Leerlingnummer/Afkorting</td>
<td><input type="text" name="userid" value="<? echo($_GET['userid']) ?>"></td></tr>
<tr><td>Emailadres</td>
<td><input type="text" name="email" value="<? echo($_GET['email']) ?>"></td></tr>
<tr><td>Aanmaakcode</td>
<td><input type="text" name="aanmaakcode" value="<? echo($_GET['aanmaakcode']) ?>"></td></tr>
</table>
<p>
<input type="submit" value="Verzend">
</fieldset>
</form>

<? gen_html_footer(); ?>
