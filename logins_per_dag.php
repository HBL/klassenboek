<? 
require("include/init.php");
$table = sprint_table(mysql_query_safe(<<<EOT
SELECT DATE(log.timestamp) datum,
COUNT(IF(ppl.type = 'leerling', ppl.ppl_id, NULL)) lln,
COUNT(DISTINCT IF(ppl.type = 'leerling', ppl.ppl_id, NULL)) uniek_lln,
COUNT(IF(ppl.type != 'leerling' AND ppl.type != 'ouder', ppl.ppl_id, NULL)) personeel,
COUNT(DISTINCT IF(ppl.type != 'leerling' AND ppl.type != 'ouder', ppl.ppl_id, NULL)) uniek_personeel,
COUNT(IF(ppl.type = 'ouder', ppl.ppl_id, NULL)) ouders,
COUNT(DISTINCT IF(ppl.type = 'ouder', ppl.ppl_id, NULL)) uniek_ouders,
COUNT(ppl.ppl_id) totaal,
COUNT(DISTINCT ppl.ppl_id) uniek
FROM log
JOIN ppl ON ppl.ppl_id = log.orig_ppl_id
WHERE event = 'login_success'
GROUP BY datum
ORDER BY datum DESC
EOT
	));
check_login();
gen_html_header();
echo "<h3>Aantal logins per dag</h3>";
echo($table);
gen_html_footer();
?>
