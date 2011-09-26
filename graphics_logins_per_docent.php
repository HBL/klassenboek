<? require_once('include/init.php');
check_login(); 
$result = mysql_query_safe(<<<EOT
SELECT login,
	COUNT(IF(DATEDIFF(NOW(), timestamp) = 0, ppl.ppl_id, NULL)) today,
	COUNT(IF(DATEDIFF(NOW(), timestamp) > 0 AND DATEDIFF(NOW(), timestamp) <= 3, ppl.ppl_id, NULL)) week,
	COUNT(IF(DATEDIFF(NOW(), timestamp) > 3 AND DATEDIFF(NOW(), timestamp) <= 10, ppl.ppl_id, NULL)) month,
	MAX(timestamp) quux
FROM log
JOIN ppl ON ppl.ppl_id = log.orig_ppl_id
WHERE event = 'login_success'
AND type != 'leerling' AND type != 'ouder'
GROUP BY ppl.ppl_id
ORDER BY quux
EOT
// ORDER BY today, week, month, quarter, login
);
?>
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
        data.addColumn('string', 'Klas');
        data.addColumn('number', 'Vandaag');
        data.addColumn('number', 'Heel recent');
        data.addColumn('number', 'Recent');
<? $start = 0; while (mysql_result($result, $start, 3) == 0) $start++ ?>
	data.addRows(<? echo(mysql_num_rows($result) - $start) ?>);
<? for ($i = $start; $i < mysql_num_rows($result); $i++) { 
	echo "\tdata.setValue($i - $start, 0, '".mysql_result($result, $i, 0)."');\n";
	echo "\tdata.setValue($i - $start, 1, ".mysql_result($result, $i, 1).");\n";
	echo "\tdata.setValue($i - $start, 2, ".mysql_result($result, $i, 2).");\n";
	echo "\tdata.setValue($i - $start, 3, ".mysql_result($result, $i, 3).");\n";
} ?>
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
	chart.draw(data, {min: 0, legend: 'bottom', width: 960, height: 480, logScale: true, isStacked: true, is3D: false, title: 'Recente docentenlogins'});
      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
  </body>
</html>
