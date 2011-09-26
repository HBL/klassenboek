<? include("include/init.php");
check_login_and_caps();

if (have_cap("GRANT")) 
	$grant_cap_select = sprint_select(NULL, '', $tmp, 1,
		'SELECT cap_id, name '.
		'FROM caps '.
		'JOIN ppl2caps USING (cap_id) '.
		'WHERE ppl_id = '.$_SESSION['orig_ppl_id']);

if (have_cap("REVOKE")) 
	$revoke_cap_select = sprint_select(NULL, '', $tmp, 1,
		'SELECT cap_id, name '.
		'FROM caps '.
		'WHERE 1');

if (have_cap("ADD_GRP"))
	$new_grp_select = sprint_select(NULL, '', $tmp, 1,
		"SELECT grp_type_id, grp_type_naam ".
		"FROM grp_types WHERE 1 ORDER BY grp_type_id");

if (have_cap("MOD_GRP"))
	$new_grp_select = sprint_select(NULL, '', $tmp, 1,
		"SELECT grp_type_id, grp_type_naam ".
		"FROM grp_types WHERE 1 ORDER BY grp_type_id");

if (have_cap("NEW_PPL")) {
	$new_ppl_output .= <<<EOT
<h2>Nieuwe leerling/docent aanmaken</h2>

Beheerders kunnen leerlingen en personeel <a href="new_ppl.php">toevoegen</a> aan de database.

EOT;
}

if (have_cap("PPL2GRP_OWN")) {
	$tmp = sprint_select(NULL, '', $tmp, 0,
<<<EOT
SELECT grp_id, grp.naam
FROM grp 
JOIN grp2vak USING (grp_id) 
JOIN doc2grp2vak USING (grp2vak_id)
JOIN ppl USING (ppl_id)
WHERE schooljaar = '$schooljaar'
AND ppl_id = ${_SESSION['ppl_id']}
GROUP BY grp_id 
ORDER BY grp.naam
EOT
	);
$output .= <<<EOT
<h2>Leerlingen toevoegen/verwijderen uit eigen groepen</h2>

Typ het <b>leerlingnummer</b> of de <b>afkorting</b> van de persoon in en selecteer de groep.
Als meerdere mensen les geven aan een van jouw groepen, dan zien zij de wijzigingen ook.

<form method="POST" action="do_ppl2grp.php" accept-charset="UTF-8">
<input type="text" name="login">
$tmp
<input type="submit" name="submit" value="Toevoegen">
<input type="submit" name="submit" value="Verwijderen">
</form>

EOT;
}

gen_html_header("Beheer", <<<EOT
$("#su_ppl").autocomplete("search_ppl.php", {                         
        width: 260,
        selectFirst: false,
	matchSubset: false,
	highlight: false,
	extraParams: { t: "all" }
});

$("#su_ppl").result(function(event, data, formatted) {
        if (data) $("#hidden_ppl_id").val(data[1]);
});
EOT
, 'jquery.bgiframe.min.js', 'jquery.ajaxQueue.js', 'jquery.autocomplete.min.js',
 'jquery.autocomplete.css'); 
status();
?>

Deze pagina is voor gebruikers die beheersrechten hebben.
<a href="do_drop_caps.php">DROP_CAPABILITIES</a>

<? if (have_cap("STATS")) { ?>
<h2>Statistieken</h2>

Statistieken over alle gebruikers.
<ul>
<li><a href="last_login.php?doc=0">recente leerlinglogins</a></li>
<li><a href="last_login.php?doc=1">recente docentenlogins</a></li>
<li><a href="last_login.php?doc=2">recente ouderlogins</a></li>
</ul>

<? } ?>
<? echo($output); ?>
<? echo($new_ppl_output); ?>
<? if (have_cap("AANMAAKCODES_DOC")) { ?>
<h2>Aanmaakcodes docenten</h2>

Genereer een lijst met <a href="aanmaakcodes_doc.php">aanmaakcodes voor docenten</a>.

<? } ?>
<? if (have_cap("SU")) { ?>
<h2>Switch user</h2>

Switch naar user. Je effectieve ppl_id verandert, maar je oorspronkelijke identiteit
wordt gelogd!

<form method="POST" action="do_su.php" accept-charset="UTF-8">
<input id="su_ppl" name="q" type="text">
<input type="hidden" id="hidden_ppl_id" name="ppl_id">
<input type="submit" value="SU">
</form>

<? } ?>
<? if (have_cap("GRANT")) { ?>
<h2>Grant</h2>

Je kunt andere gebruikers rechten geven die je zelf ook hebt.

<form method="POST" action="do_grant.php" accept-charset="UTF-8">
<input type="text" name="login">
<? echo($grant_cap_select); ?>
<input type="submit" value="Grant">
</form>

<? } ?>
<? if (have_cap("REVOKE")) { ?>
<h2>Revoke</h2>

Je kunt andere gebruikers rechten ontnemen, dit geldt alleen voor
rechten die je zelf hebt gegeven.

<form method="POST" action="do_revoke.php" accept-charset="UTF-8">
<input type="text" name="login">
<? echo($revoke_cap_select); ?>
<input type="submit" value="Revoke">
</form>

<? } ?>
<? if (have_cap('ADD_DOC2GRP2VAK')) { ?>
<h2>Voeg docent toe aan grp2vak</h2>

Hier kun je een docent toekennen aan een groep. Voorwaarde is dat de groep al bestaat.
Dus om SNL wiskunde te laten geven aan 3C2 typ je: <CODE>SNL/3C2/wi</CODE>.

<form method="POST" action="do_add_doc2grp2vak.php" accept-charset="UTF-8">
<input type="text" name="doc2grp2vak">
<input type="submit" value="Add">
</form>

<? } ?>
<? if (have_cap('DEL_DOC2GRP2VAK')) { ?>
<h2>Verwijder docent van grp2vak</h2>

Hier kun je een docent afhalen van een groep.
Dus om SNL geen wiskunde meer te laten geven aan 3C2 typ je: <CODE>SNL/3C2/wi</CODE>.
<form method="POST" action="do_del_doc2grp2vak.php" accept-charset="UTF-8">
<input type="text" name="doc2grp2vak">
<input type="submit" value="Del">
</form>

<? } ?>
<? if (have_cap('ADD_GRP')) { ?>
<h2>Toevoegen van groep.</h2>

Typ de naam van de groep en selecteer een type.
<form method="POST" action="do_add_grp.php" accept-charset="UTF-8">
<input type="text" name="naam">
<? echo($new_grp_select) ?>
<input type="submit" value="Add">
</form>

<? } ?>
<? gen_html_footer(); ?>
