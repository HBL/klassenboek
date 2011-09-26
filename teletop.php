<? include("include/init.php");
check_login();
//if ($_SESSION['type'] != 'personeel') regular_error($http_path.'/', (array) NULL, 
//	'de gevraagde pagina is alleen toegankelijk voor personeel');

$result = mysql_query_safe("SELECT teletop_username FROM ppl2teletop WHERE ppl_id = {$_SESSION['ppl_id']}");
if (mysql_numrows($result)) $teletop_username = mysql_result($result, 0, 0);
mysql_free_result($result);

if ($_SESSION['type'] == 'personeel' && $_SESSION['teletop_session']) {
	$ch = curl_teletop_init();
	$xpath = curl_teletop_req($ch, '/tt/abvo/lms.nsf/f-MyCourses?OpenForm');
	//echo($xpath->document->saveHTML());
	$lijst = $xpath->query('//table[@id="viewMyCourses"]/tbody/tr[contains(@id, "~teachers")]/td[2]/a[1]');
	
	$escaped_prefix = addcslashes($teletop_vaksite_prefix, '\/');
	$out = new DOMDocument();
	$select = $out->createElement('select');
	$select->setAttribute('name', 'vaksite_id[]');
	$out->appendChild($select);
	$default_option = $out->createElement('option');
	$default_option->setAttribute('value', '');
	$select->appendChild($default_option);
	
	foreach ($lijst as $link) {
		// database bijwerken (meestal zinloos)
		$href = $link->getAttribute('href');
		$vaksite = preg_replace('/'.$escaped_prefix.'(.*)\.nsf/', '$1', $href);
		if ($href == $vaksite) regular_error($http_path.'/', (array) NULL, 'TeleTOP&reg; geeft ons misvormde vaksites, notificeer de beheerder');

		mysql_query_safe("INSERT INTO vaksites ( vaksite, vaksite_naam ) VALUES ( '%s', '%s' ) ON DUPLICATE KEY UPDATE vaksite_naam='%s'",
			mysql_escape_safe($vaksite), mysql_escape_safe(htmlspecialchars($link->nodeValue, ENT_QUOTES, 'UTF-8')),
			mysql_escape_safe(htmlspecialchars($link->nodeValue, ENT_QUOTES, 'UTF-8')));


		$vaksite_id = sprint_singular("SELECT vaksite_id FROM vaksites WHERE vaksite = '%s'", mysql_escape_safe($vaksite));
		//$vaksites .= '<p>'.$vaksite.' '.$link->nodeValue."\n";
		$option = $out->createElement('option');
		$option->setAttribute("value", $vaksite_id);
		$text = $out->createTextNode($link->nodeValue);
		$option->appendChild($text);
		$select->appendChild($option);
	}

	$result = mysql_query_safe(<<<EOT
SELECT grp2vak_id, vaksite_id, KB_LGRP(grp.naam, vak.afkorting) grp2vak
FROM grp
JOIN grp2vak USING (grp_id)
LEFT JOIN grp2vak2vaksite USING (grp2vak_id)
JOIN doc2grp2vak USING (grp2vak_id)
JOIN vak USING (vak_id)
WHERE ppl_id = '{$_SESSION['ppl_id']}' AND schooljaar = '$schooljaar'
EOT
);
	$vaksites .= '<table>';

	$xpath = new DOMXpath($out);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$option = $xpath->query('//select/option[@value="'.$row['vaksite_id'].'"]')->item(0);
		if (!$option) $option = $defualt_option;

		$option->setAttribute('selected', 'selected');
		$vaksites .= '<tr><td>'.$row['grp2vak'].'<td><input type="hidden" name="grp2vak_id[]" value="'.$row['grp2vak_id'].'">'.$out->saveHTML();
		$option->removeAttribute('selected');
	}
	$vaksites .= '</table>';
	//echo($out->saveHTML());
} else if ($_SESSION['type'] == 'personeel' && not_teletop_credentials()) {
	$vaksites = <<<EOT
Je kunt hier je klassen koppelen aan je TeleTOP&reg; vaksites nadat je je TeleTOP&reg; logingegevens hebt ingevuld en op 'Opslaan' hebt geklikt.
EOT;
} else if ($_SESSION['type'] == 'personeel') { 
	$vaksites = <<<EOT
Je bent niet ingelogd in TeleTOP&reg;, dit is vanwege een storing of vanwege het feit dat je TeleTOP&reg; gebruikersnaam of wachtwoord niet goed zijn ingesteld.
EOT;
} 

gen_html_header('TeleTOP&reg; login informatie'); 
status();
?>
<form action="do_teletop.php" method="POST" accept-charset="UTF-8">
<table>
<tr><td>TeleTOP&reg; gebruikersnaam</td><td><input name="ttusr" value="<? echo($teletop_username) ?>">
<tr><td>TeleTOP&reg; wachtwoord:</td><td><input name="new_pw0" type="password">
</table>
<? echo($vaksites); ?>
<table>
<tr><td>klassenboek wachtwoord:</td><td><input name="password" type="password"> (verplicht!)
</table>
<p><input type="submit" name="action" value="Opslaan"><input type="submit" name="action" value="Verwijderen">
</form>

<? gen_html_footer(); ?>
