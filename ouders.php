<? 
die();
include("include/init.php");
check_nologin();
gen_html_header('Ouders', '$("input:text:visible:first").focus();');
status();
?>
<p><form action="do_ouders.php" method="post" accept-charset="UTF-8">
<fieldset>
<legend>Aanmaken</legend>
Hier kun je een ouderaccount aanmaken. Elke ouder/verzorger kan een eigen account maken. Verzin zelf een inlognaam die niet alleen uit letters of alleen uit cijfers bestaat (toegestaan zijn bijvoorbeeld <code>p.jansen</code>, <code>derkeiler211</code>). Alle velden zijn verplicht, behalve 'Tussenvoegsel'.

<table>
<tr><td>Gewenste loginnaam</td>
<td><input type="text" name="login" value="<? echo($_GET['login']) ?>"></td></tr>
<tr><td>Voorletters/voornaam</td>
<td><input type="text" name="naam1" value="<? echo($_GET['naam1']) ?>"></td></tr>
<tr><td>Tussenvoegsel</td>
<td><input type="text" name="naam2" value="<? echo($_GET['naam2']) ?>"></td></tr>
<tr><td>Achternaam</td>
<td><input type="text" name="naam0" value="<? echo($_GET['naam0']) ?>"></td></tr>
<tr><td>Emailadres</td>
<td><input type="text" name="email" value="<? echo($_GET['email']) ?>"></td></tr>
</table>
<p>Toon aan dat je een mens bent door de twee onderstaande woorden in te typen.
<? require_once('include/recaptcha.php'); recaptcha_ask(); ?>
<p>
<input type="submit" value="Verzend">
</fieldset>
</form>

<? gen_html_footer(); ?>
