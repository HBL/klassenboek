<? include("include/init.php");
check_login();

switch ($_GET['submit']) {
case 'Genereer aanmaakcodes':
$result = mysql_query_safe(
	"SELECT KB_NAAM(naam0, naam1, naam2) naam, ".
	"SUBSTRING(SHA1(CONCAT(ppl.ppl_id,'$aanmeld_secret')), 1, 16) code ".
	"FROM ppl ".
	"JOIN ppl2grp USING (ppl_id) ".
	"JOIN grp2vak USING (grp_id) ".
	"JOIN doc2grp2vak USING (grp2vak_id) ".
	"WHERE doc2grp2vak.ppl_id = ${_SESSION['ppl_id']} ".
	"AND grp_id = '%s' AND ppl.password IS NULL GROUP BY ppl.ppl_id ORDER BY naam0, naam1, naam2",
	mysql_escape_safe($_GET['grp_id']));

$num = mysql_num_rows($result);
if ($num == 0) {
	$personen = sprint_singular("SELECT grp_type_lid_mv FROM grp_types JOIN grp USING (grp_type_id) WHERE grp_id = '%s'", mysql_escape_safe($_GET['grp_id']));
	$_SESSION['successmsg'] = "Er zijn geen $personen in deze groep die niet zijn aangemeld";
	header('Location: extra.php');
}

	gen_html_header();
?>	Alle leerlingen die hier niet niet staan hebben al een account, of zijn,
	ten onrechte, niet in deze groep geplaatst.<br><br>
<?
for ($i = 0; $i < $num; $i++) {
	print("<br>".mysql_result($result, $i, 'naam').", Aanmaakcode: ".
		mysql_result($result, $i, 'code')."\nJe kunt je met deze code en je ".
		"leerlingnummer/afkorting aanmelden op $http_server$http_path (klik op ".
		"'Aanmaken', voer je leerlingnummer/afkorting, emailadres en deze ".
		"code in en lees je mail)<br><br>");
}
	gen_html_footer();
break;
case 'Last login':
	$result = mysql_query_safe("SELECT IF(max_timestamp IS NULL, ".
		"'nog nooit ingelogd', max_timestamp) last_login_time, ".
		"CONCAT(KB_NAAM(naam0, naam1, naam2), ' (', ppl.login, ')')  naam, ".
		"IF (`count` IS NULL, 0, `count`) aantal_logins ".
		"FROM ppl2grp ".
		"JOIN ppl USING (ppl_id) ".
		"JOIN grp2vak USING (grp_id) ".
		"JOIN doc2grp2vak USING (grp2vak_id) ".
		"LEFT JOIN ( ".
			"SELECT ppl.ppl_id, MAX(timestamp) max_timestamp, ".
			"COUNT(DISTINCT timestamp) `count` FROM ppl ".
			"JOIN ppl2grp USING (ppl_id) ".
			"JOIN log ON ppl.ppl_id = log.ppl_id ".
			"JOIN grp2vak USING (grp_id) ".
			"JOIN doc2grp2vak USING (grp2vak_id) ".
			"WHERE event = 'login_success' ".
			"AND grp_id = '%s' ".
			"AND doc2grp2vak.ppl_id = ${_SESSION['ppl_id']} ".
			"GROUP BY ppl.ppl_id ".
		") AS login ON login.ppl_id = ppl.ppl_id ".
		"WHERE ppl.active IS NOT NULL ".
		"AND doc2grp2vak.ppl_id = ${_SESSION['ppl_id']} ".
		"AND grp_id = '%s' ".
		"GROUP BY ppl.ppl_id ORDER BY max_timestamp DESC",
		mysql_escape_safe($_GET['grp_id']),
		mysql_escape_safe($_GET['grp_id']));
	gen_html_header();
	echo(sprint_table($result));
	gen_html_footer();
	break;

case 'Ouders':
	$result = mysql_query_safe(<<<EOT
SELECT DISTINCT KB_NAAM(lln.naam0, lln.naam1, lln.naam2) leerling, GROUP_CONCAT(CONCAT( KB_NAAM(ouders.naam0, ouders.naam1, ouders.naam2), ' &lt;', ouders.email, '&gt;') SEPARATOR ', ') AS `ouder(s)` FROM ppl AS lln
JOIN ppl2grp AS ppl2gezin USING (ppl_id)
JOIN grp AS gezin USING (grp_id)
JOIN grp2vak ON gezin.grp_id = grp2vak.grp_id
JOIN doc2grp2vak ON doc2grp2vak.grp2vak_id = grp2vak.grp2vak_id
JOIN ppl AS ouders ON doc2grp2vak.ppl_id = ouders.ppl_id
JOIN ppl2grp  ON ppl2grp.ppl_id = lln.ppl_id
WHERE gezin.grp_type_id = ( SELECT grp_type_id FROM grp_types WHERE grp_type_naam = 'gezin')
AND gezin.schooljaar = '0910'
AND ppl2grp.grp_id = '%s'
AND '%s' = ANY (
	SELECT grp_id FROM grp JOIN grp2vak USING (grp_id) JOIN doc2grp2vak USING (grp2vak_id) WHERE ppl_id = {$_SESSION['ppl_id']}
)
GROUP BY lln.ppl_id
EOT
		, mysql_escape_safe($_GET['grp_id'])
		, mysql_escape_safe($_GET['grp_id']));
	gen_html_header(); ?>
	Hier volgen alle leerlingen uit de groep met ten minste &eacute;&eacute;n ouder.<p>
<? 	echo(sprint_table($result));
	gen_html_footer();
	break;
case 'Emailadressen':
	$result = mysql_query_safe(
		"SELECT ppl.email, KB_NAAM(naam0, naam1, naam2) naam FROM ppl ".
		"JOIN ppl2grp USING (ppl_id) ".
		"JOIN grp2vak USING (grp_id) ".
		"JOIN doc2grp2vak USING (grp2vak_id) ".
		"WHERE grp_id = '%s' ".
		"AND doc2grp2vak.ppl_id = ${_SESSION['ppl_id']} ".
		"GROUP BY ppl.ppl_id ".
		"ORDER BY naam0, naam1, naam2",
		mysql_escape_safe($_GET['grp_id']));

	gen_html_header();
	$num = mysql_num_rows($result);
	for ($i = 0; $i < $num; $i++) {
		if (mysql_result($result, $i, "ppl.email") != "") {
			echo mysql_result($result, $i, "ppl.email");
			if ($i < $num - 1) echo "; ";
		} else { 
			$geenemail .= mysql_result($result, $i, 'naam')." ";
		}
	}

	if ($geenemail != "") echo "<br><br>Van de onderstaande leerlingen is ".
		"nog geen emailadres bekend<br>$geenemail";
	gen_html_footer();
	break;

case 'oude vakken/docenten':
	$result = mysql_query_safe(
		"SELECT CONCAT(KB_NAAM(lln_naam0, lln_naam1, lln_naam2), ".
		"' (', lln_login, ')') AS naam, ".
		"GROUP_CONCAT(DISTINCT IF(grp_stamklas, grp_naam, NULL)) ".
		"AS `vorige klas`, ".
		"GROUP_CONCAT(DISTINCT oldgrp_naam) ".
		"AS `huidige klas`, ".
		"GROUP_CONCAT(DISTINCT vak_docenten ORDER BY vak_afkorting ".
		"SEPARATOR ' ') AS `oude vakken/docenten` ".
		"FROM ( ".
		"SELECT lln.naam0 AS lln_naam0, ".
			"lln.naam1 AS lln_naam1, ".
			"lln.naam2 AS lln_naam2, ".
			"lln.login AS lln_login, ".
			"nwgrp.naam AS grp_naam, ".
			"nwgrp.stamklas AS grp_stamklas, ".
			"oldgrp.naam AS oldgrp_naam, ".
			"oldgrp.stamklas AS oldgrp_stamklas, ".
			"lln.ppl_id AS lln_id, ".
			"vak.afkorting AS vak_afkorting, ".
			"CONCAT(vak.afkorting, '/', ".
			"GROUP_CONCAT(DISTINCT doc.login ORDER BY doc.login)) ".
			"AS vak_docenten ".
			"FROM ppl AS lln ".
			"JOIN ppl2grp AS vorig_schooljaar USING (ppl_id) ".
			"JOIN ppl2grp AS dit_schooljaar USING (ppl_id) ".
			"JOIN ppl2grp AS oude_stamklas USING (ppl_id) ".
			"JOIN grp AS nwgrp ON dit_schooljaar.grp_id = nwgrp.grp_id ".
			"JOIN grp AS oldgrp ON oldgrp.grp_id = oude_stamklas.grp_id ".
			"JOIN grp2vak ".
			"ON dit_schooljaar.grp_id = grp2vak.grp_id ".
			"JOIN doc2grp2vak USING (grp2vak_id) ".
			"JOIN ppl AS doc ON doc.ppl_id = doc2grp2vak.ppl_id ".
			"RIGHT JOIN vak USING (vak_id) ".
			"JOIN grp2vak AS old_grp2vak ".
			"ON old_grp2vak.grp_id = vorig_schooljaar.grp_id ".
			"JOIN doc2grp2vak AS old_doc2grp2vak ".
			"ON old_grp2vak.grp2vak_id = old_doc2grp2vak.grp2vak_id ".
			"WHERE vorig_schooljaar.grp_id = '%s' ".
			"AND oldgrp.stamklas = 1 ".
			"AND oldgrp.schooljaar = '$schooljaar' ".
			"AND nwgrp.schooljaar = '$vorig_schooljaar' ".
			"AND old_doc2grp2vak.ppl_id = '${_SESSION['ppl_id']}' ".
			"GROUP BY lln.ppl_id, vak.vak_id ".
		") AS bla WHERE 1 GROUP BY bla.lln_id ".
		"ORDER BY lln_naam0, lln_naam1, lln_naam2",
		mysql_escape_safe($_GET['grp_id']));
	gen_html_header();
	echo(sprint_table($result));
	gen_html_footer();
	break;

case 'nieuwe vakken/docenten':
	$result = mysql_query_safe(
		"SELECT CONCAT(KB_NAAM(lln_naam0, lln_naam1, lln_naam2), ".
		"' (', lln_login, ')') AS naam, ".
		"GROUP_CONCAT(DISTINCT oldgrp_naam) ".
		"AS `vorige klas`, ".
		"GROUP_CONCAT(DISTINCT IF(grp_stamklas, grp_naam, NULL)) ".
		"AS `huidige klas`, ".
		"GROUP_CONCAT(DISTINCT vak_docenten ORDER BY vak_afkorting ".
		"SEPARATOR ' ') AS `nieuwe vakken/docenten` ".
		"FROM ( ".
		"SELECT lln.naam0 AS lln_naam0, ".
			"lln.naam1 AS lln_naam1, ".
			"lln.naam2 AS lln_naam2, ".
			"lln.login AS lln_login, ".
			"nwgrp.naam AS grp_naam, ".
			"nwgrp.stamklas AS grp_stamklas, ".
			"oldgrp.naam AS oldgrp_naam, ".
			"oldgrp.stamklas AS oldgrp_stamklas, ".
			"lln.ppl_id AS lln_id, ".
			"vak.afkorting AS vak_afkorting, ".
			"CONCAT(vak.afkorting, '/', ".
			"GROUP_CONCAT(DISTINCT doc.login ORDER BY doc.login)) ".
			"AS vak_docenten ".
			"FROM ppl AS lln ".
			"JOIN ppl2grp AS vorig_schooljaar USING (ppl_id) ".
			"JOIN ppl2grp AS dit_schooljaar USING (ppl_id) ".
			"JOIN ppl2grp AS oude_stamklas USING (ppl_id) ".
			"JOIN grp AS nwgrp ON dit_schooljaar.grp_id = nwgrp.grp_id ".
			"JOIN grp AS oldgrp ON oldgrp.grp_id = oude_stamklas.grp_id ".
			"JOIN grp2vak ".
			"ON dit_schooljaar.grp_id = grp2vak.grp_id ".
			"JOIN doc2grp2vak USING (grp2vak_id) ".
			"JOIN ppl AS doc ON doc.ppl_id = doc2grp2vak.ppl_id ".
			"RIGHT JOIN vak USING (vak_id) ".
			"JOIN grp2vak AS old_grp2vak ".
			"ON old_grp2vak.grp_id = vorig_schooljaar.grp_id ".
			"JOIN doc2grp2vak AS old_doc2grp2vak ".
			"ON old_grp2vak.grp2vak_id = old_doc2grp2vak.grp2vak_id ".
			"WHERE vorig_schooljaar.grp_id = '%s' ".
			"AND oldgrp.stamklas = 1 ".
			"AND oldgrp.schooljaar = '$vorig_schooljaar' ".
			"AND nwgrp.schooljaar = '$schooljaar' ".
			"AND old_doc2grp2vak.ppl_id = '${_SESSION['ppl_id']}' ".
			"GROUP BY lln.ppl_id, vak.vak_id ".
		") AS bla WHERE 1 GROUP BY bla.lln_id ".
		"ORDER BY lln_naam0, lln_naam1, lln_naam2",
		mysql_escape_safe($_GET['grp_id']));
	gen_html_header();
	echo(sprint_table($result));
	gen_html_footer();
	break;
case 'Login histogram':
	header("Location: graphics_logins_per_leerling.php?grp_id={$_GET['grp_id']}");
	break;
}
?>
