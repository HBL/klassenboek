<? require("include/init.php");
check_login_and_cap('AANMAAKCODES_DOC');

$result=mysql_query_safe(
	"SELECT DISTINCT ppl.login, ".
	"KB_NAAM(naam0, naam1, naam2) ppl_naam, ".
	"ppl.naam1 voorletters_tussenvoegsel, ppl.naam0 achternaam, ".
	"SUBSTRING(SHA1(CONCAT(ppl.ppl_id,'$aanmeld_secret')), 1, 16) q ".
	"FROM ppl ".
	"JOIN doc2grp2vak USING (ppl_id) ".
	"JOIN grp2vak USING (grp2vak_id) ".
	"JOIN grp USING (grp_id) ".
	"JOIN grp_types USING (grp_type_id) ".
	"WHERE grp.schooljaar = '$schooljaar' AND ( password = '' OR ".
	"password IS NULL OR SUBSTRING(password, 1, 1) = 'x' ) ".
	"AND active IS NOT NULL AND grp_type_naam != 'gezin' ".
	"ORDER BY login");

if (mysql_num_rows($result) == 0) {
	$_SESSION['successmsg'] = "Er zijn geen docenten in deze groep ".
		"die niet zijn aangemeld";
	header('Location: index.php');
}

gen_html_header();
?>
Hier alle aanmaakcodes van docenten die dit schooljaar minstens &eacute;&eacute;n groep hebben en nog geen account hebben.
<br>
<?  while ($row = mysql_fetch_assoc($result)) {
	print('<br>'.$row['login'].'/'.$row['ppl_naam'].', Aanmaakcode: '.
		$row['q']."\nJe kunt met deze code en je afkorting een account ".
		"aanmaken op $http_server$http_path, klik op 'Aanmaken'<br><br>");
}

gen_html_footer(); ?>
