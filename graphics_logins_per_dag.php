<? require_once('include/init.php');
check_login(); 
$result = mysql_query_safe(<<<EOT
SELECT CONCAT(DAYNAME(log.timestamp), ' ', DATE(timestamp)) datum,
COUNT(DISTINCT IF(ppl.type = 'leerling', log.ppl_id, NULL)) uniek_lln,
COUNT(DISTINCT IF(ppl.type != 'leerling' AND ppl.type != 'ouder', log.ppl_id, NULL)) uniek_personeel,
COUNT(DISTINCT IF(ppl.type = 'ouder', log.ppl_id, NULL)) uniek_ouders,
COUNT(log.ppl_id) totaal,
COUNT(DISTINCT log.ppl_id) uniek
FROM log JOIN ppl USING (ppl_id)
WHERE event = 'login_success'
GROUP BY datum
ORDER BY timestamp DESC
LIMIT 90
EOT
);
?>
<? ob_start(); ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<? if ($svgweb) { ?>
<script type="text/javascript" src="<? echo($svgweb) ?>"></script>
<? } ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('visualization', '1', {'packages':['columnchart']});
google.setOnLoadCallback(drawChart);
      
function drawChart() {
	var data = new google.visualization.DataTable();
        data.addColumn('string', 'Datum');
        data.addColumn('number', 'leerlingen');
        data.addColumn('number', 'personeel');
        data.addColumn('number', 'ouders');
	data.addRows(<? echo(mysql_num_rows($result)) ?>);
<? for ($i = 0; $i < mysql_num_rows($result); $i++) { 
	echo "\tdata.setValue($i, 0, '".mysql_result($result, $i, 0)."');\n";

	for ($j = 1; $j < 4; $j++) 
		echo "\tdata.setValue($i, $j, ".mysql_result($result, $i, $j).");\n";
	} ?>
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, {reverseAxis: true, legend: 'bottom', min: 0, width: 960, height: 480, isStacked: true, is3D: false, title: 'Aantal unieke logins per dag'});
      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
<? if ($cachedir) {
	        $fp = fopen($cachedir.'/graphics_logins_per_dag.html.tmp', 'w') or die('Error opening cache file');
fwrite($fp, ob_get_contents().'<p>cached on '.date(DATE_ATOM)."\n</body>\n</html>\n");
fclose($fp);
rename($cachedir.'/graphics_logins_per_dag.html.tmp', $cachedir.'/graphics_logins_per_dag.html') or die('unable to move');
}
ob_end_flush(); ?>
  </body>
</html>
