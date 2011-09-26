<? 
// deze include laat een loginscherm zien, alleen aanroepen vanuit index.php aub,
// gesplitst om index.php overzichtelijk te houden
if (isset($_GET['lock_by']) && $_GET['lock_by'] != '') {
	if ($_SESSION['orig_login'] != $_GET['lock_by'])
		throw new Exception('lock_by parameter onjuist', 2);
	gen_html_header("Unlock", '$("#password").focus();');
	status(); ?>
<form id="unlock" action="do_login.php" method="post">
<fieldset style="margin: 0 auto;">
<legend>Unlock</legend>
<p>Typ hier je wachtwoord om verder te gaan waar je gebleven was.
<P><table>
<input type="hidden" name="login" value="<? echo($_GET['lock_by']) ?>">
<input type="hidden" name="lock_by" value="<? echo($_GET['lock_by']) ?>">
<tr><td>Wachtwoord</td><td><input id="password" type="password" name="password"></td></tr>
</table>
<input type="submit" value="Unlock">
</fieldset>
<?
	gen_html_footer();
	exit;
}
session_regenerate_id();
session_destroy();
gen_html_header("Inloggen", <<<EOT
$(document).scrollTop(0);
//var off = $("#placeholder").offset();
//$("#loginstuff").offset(off);
//$("#draggable").offset(off);
//$("#draggable").show();
//$("#draggable").draggable();
//$("#draggable").ready(function () { 
//	$("#loginstuff").show("slow");
//	$("input[value=\'\']:visible:first").focus();
//});
EOT
, 'jquery-ui-1.8.4.custom.min.js');
status(); ?>
<p>
<!--<div id="placeholder" style="width: 720px; height: 320px; z-index: -100"></div>-->
<!--<div style="display: none; position: absolute; z-index: 100" id="draggable"><img height="320" width="720" src="images/barbrady.jpg"></div>-->
<!--<div style="width: 720px; height: 320px; display: none; position: absolute; z-index: 50" id="loginstuff">-->
<div style="text-align: left" id="loginstuff">
<form action="do_login.php" method="post">
<fieldset style="margin: 0 auto;">
<legend>Login</legend>
Om deze website te kunnen gebruiken moet je
<a href="http://nl.wikipedia.org/wiki/Cookies">cookies</a> en
<a href="http://nl.wikipedia.org/wiki/Javascript">javascript</a> aan hebben staan.
Leerlingen loggen in met hun leerlingnummer en medewerkers van school doen dat met
hun afkorting.
<P><table>
<tr><td>Gebruikersnaam</td><td><input id="login" type="text" name="login" value="<? echo($_GET['login']) ?>"></td></tr>
<tr><td>Wachtwoord</td><td><input type="password" name="password"></td></tr>
</table>
<input type="submit" value="Login">
<!--<p>Heb je nog geen account en wel een Aanmaakcode? Klik dan bovenaan
de pagina op 'Aanmaken'.
!--></fieldset>
</form>
</div>
</center>

<? gen_html_footer(); exit; // exit is required (this is included from index.php) ?>
