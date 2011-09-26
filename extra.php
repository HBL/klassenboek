<? include("include/init.php");
check_login();

$grp_select = sprint_select(NULL, '', $tmp, 0,
	"SELECT grp_id, grp.naam FROM grp ".
	"JOIN grp2vak USING (grp_id) ".
	"JOIN doc2grp2vak USING (grp2vak_id) ".
	"JOIN ppl USING (ppl_id) ".
	"WHERE schooljaar = '$schooljaar' ".
	"AND ppl_id = ${_SESSION['ppl_id']} ".
	"GROUP BY grp_id ".
	"ORDER BY grp.naam");

$grp_select_vorig_jaar = sprint_select(NULL, '', $tmp, 0,
	"SELECT grp_id, grp.naam FROM grp ".
	"JOIN grp2vak USING (grp_id) ".
	"JOIN doc2grp2vak USING (grp2vak_id) ".
	"JOIN ppl USING (ppl_id) ".
	"WHERE schooljaar = '$vorig_schooljaar' ".
	"AND ppl_id = ${_SESSION['ppl_id']} ".
	"GROUP BY grp_id ".
	"ORDER BY grp.naam");

$grp_type_select = sprint_select(NULL, '', $tmp, 0,
	"SELECT grp_type_id, grp_type_naam FROM grp_types ".
	"WHERE grp_type_naam != 'gezin' ORDER BY grp_type_id");

	//SELECT GROUP_CONCAT(CONCAT(KB_NAAM(meerdere.naam0, meerdere.naam1, meerdere.naam2), CONCAT(' &lt;', meerdere.email, '&gt;')) SEPARATOR ', ') id,
	//SELECT GROUP_CONCAT(CONCAT(KB_NAAM(meerdere.naam0, meerdere.naam1, meerdere.naam2), IF(meerdere.type = 'personeel', '', CONCAT(' &lt;', meerdere.email, '&gt;'))) SEPARATOR ', ') id,
$result = mysql_query_safe(<<<EOT
SELECT CONCAT(IF(count = 1, grp_type_baas_ev, grp_type_baas_mv), ': ', id) AS `functie: perso(o)n(en)`,
	soort, naam, afkorting AS vak FROM (
	SELECT GROUP_CONCAT(CONCAT(KB_NAAM(meerdere.naam0, meerdere.naam1, meerdere.naam2), IF(meerdere.email LIKE '%%_%%', CONCAT(' &lt;', meerdere.email, '&gt;'), '')) SEPARATOR ', ') id,
		grp_type_baas_ev, grp_type_baas_mv, grp_type_naam soort, grp.naam naam,
		grp.grp_id, COUNT(meerdere.ppl_id) count, vak.afkorting FROM ppl
	JOIN ppl2grp USING (ppl_id)
	JOIN grp USING (grp_id)
	JOIN grp_types USING (grp_type_id)
	JOIN grp2vak USING (grp_id)
	LEFT JOIN vak USING (vak_id)
	LEFT JOIN doc2grp2vak USING (grp2vak_id)
	LEFT JOIN ppl AS meerdere ON doc2grp2vak.ppl_id = meerdere.ppl_id
	WHERE ppl.ppl_id = {$_SESSION['ppl_id']} AND schooljaar = '$schooljaar'
	GROUP BY grp2vak_id
) bla
EOT
);

if (mysql_num_rows($result)) {
	$fun_facts="<h2>Informatie</h2>\n\nOverzicht van groepen waar je in zit.<p>".sprint_table($result)."\n";
}

mysql_free_result($result);

gen_html_header('Extra');
/*, <<<EOT
$("#autocomplete_test").autocomplete("search_ppl.php", { matchSubset: 0 });
EOT
, 'jquery.autocomplete.js', 'jquery.autocomplete.css');
 */status(); 
?> 

<p>Deze pagina bevat experimentele (en hopelijk nuttige) functies. Er is nog 
geen procedure om automatisch te synchroniseren met de schooldatabase. De 
meeste informatie is actueel, maar niet alles.

<? if ($_SESSION['type'] == 'personeel') { ?>
<h2>TeleTOP&reg; koppeling</h2>

Vul je TeleTOP&reg; gebruikersnaam en wachtwoord in op het <a href="teletop.php">TeleTOP&reg; login informatie formulier</a>, selecteer de juiste vaksites en
voer notities via onlineklassenboek.nl in in TeleTOP&reg;. In het weekoverzicht van een stamklas (doelgroep lesgroep, waarbij de lesgroep een stamklas is)
zie je alle 'special activities'. (Toets, SO, etc...)

<h2>File upload (experimenteel)</h2>
Het is mogelijk om files up te loaden naar het klassenboek en een link in &eacute;&eacute;n of meerdere notities te plaatsen. De files die je upload zijn momenteel toegankelijk voor elke ingelogde gebruiker die de naam weet. <b>Upload dus geen toetsen en dergelijke.</b>

Ga naar <a href="upload.php">file upload</a>.

<? } ?>

<h2>Statistieken en histogrammen</h2>

Hier wat leuke statistieken en histogrammen.
<? ie_warning() ?>
<ul>
<li>
<form method="GET" action="accounts_per.php" accept-charset="UTF-8">
Aantal acconts per <? echo($grp_type_select) ?>.
<input type="submit" value="show">
</form>
</li>
<li><a href="logins_per_dag.php">Aantal (unieke) logins per dag</a> (histogram
<a href="graphics_logins_per_dag.php">per dag</a>,
<a href="graphics_logins_per_maand.php">per maand</a>)
</li>
<li>Mooie histogrammen van het aantal:
<ul>
<li><a href="graphics_leerlinglogins_per_klas.php">leerlinglogins per klas</a>,
<li><a href="graphics_docentenlogins_per_team.php">docentenlogins per team</a>,
<li><a href="graphics_docentenlogins_per_sectie.php">docentenlogins per sectie</a>,
<li><a href="graphics_logins_per_docent.php">docentenlogins</a>.
<li><a href="graphics_ouderlogins_per_klas.php">ouderlogins per klas</a>.
</ul>
</li>
<li>Histogram van het <a href="graphics_aantal_accounts.php">totale aantal geactiveerde accounts</a>.
</ul>

<? echo($fun_facts); ?>

<? if ($grp_select) { ?>
<h2>Informatie uitgaande van groepen van schooljaar
<? echo($schooljaar_long); ?></h2>

Selecteer een groep en klik op &eacute;&eacute;n van de knoppen erachter.

<form action="do_extra.php" method="GET" accept-charset="UTF-8">
<? echo($grp_select); ?>
<input type="submit" name="submit" value="Genereer aanmaakcodes">
<input type="submit" name="submit" value="Last login">
<input type="submit" name="submit" value="Emailadressen">
<input type="submit" name="submit" value="Ouders">
<input type="submit" name="submit" value="oude vakken/docenten">
<input type="submit" name="submit" value="Login histogram">
</form>

<? } ?>
<? if ($grp_select_vorig_jaar) { ?>
<h2>Informatie uitgaande van groepen van schooljaar <? 
echo($vorig_schooljaar_long); ?></h2>

Selecteer een groep en klik op &eacute;&eacute;n de knoppen erachter.

<form action="do_extra.php" method="GET" accept-charset="UTF-8">
<? echo($grp_select_vorig_jaar); ?>
<input type="submit" name="submit" value="nieuwe vakken/docenten">
</form>
<? } ?>

<!--
<h2>Leerling zoeken</h2>

<table id='list'></table>
<div id='pager'></div>
-->
<!--De onderstaande zoekbox is een experiment met <a href="http://en.wikipedia.org/wiki/Ajax_(programming)">Ajax</a>. 
Zoals hieronder dient een zoekbox IMHO te werken...

<input type="text" id="lln" name="leerling"> -->

<? mysql_close(); gen_html_footer(); ?>
