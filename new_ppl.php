<? include("include/init.php");
check_login_and_cap('NEW_PPL');
gen_html_header('Nieuwe leerling/docent', '$("input:text:visible:first").focus();');
status();
?>
<p><form action="do_new_ppl.php" method="post" accept-charset="UTF-8">
<fieldset>
<legend>Nieuwe leerling/docent</legend>
Voer hier de informatie in over de nieuwe docent of leerling. Leerlingen hebben hun (door school uitgegeven leerlingnummer) als login, docenten hun 3 letterige afkorting. Docenten
die geen afkorting hebben (stage-mensen, bepaalde gastdocenten?) krijgen een afkorting bestaande uit meer dan 3 letters.

<table>
<tr><td>Loginnaam</td>
<td><input type="text" name="login" value="<? echo($_GET['login']) ?>"></td></tr>
<tr><td>Voorletters/voornaam</td>
<td><input type="text" name="naam1" value="<? echo($_GET['naam1']) ?>"></td></tr>
<tr><td>Tussenvoegsel</td>
<td><input type="text" name="naam2" value="<? echo($_GET['naam2']) ?>"></td></tr>
<tr><td>Achternaam</td>
<td><input type="text" name="naam0" value="<? echo($_GET['naam0']) ?>"></td></tr>
</table>
<p>
<input type="submit" value="Verzend">
</fieldset>
</form>

<? gen_html_footer(); ?>
