<? require_once('include/init.php');
check_login(); 
$result = mysql_query_safe(<<<EOT
SELECT min, 
COUNT(IF(type = 'leerling', 1, NULL)) lln,
COUNT(IF(type != 'leerling' AND type != 'ouder', 1, NULL)) doc,
COUNT(IF(type = 'ouder', 1, NULL)) oud
FROM (
	SELECT TO_DAYS(MIN(timestamp)) dag, UNIX_TIMESTAMP(MIN(timestamp)) min , ppl.type
	FROM log
	JOIN ppl USING (ppl_id)
	WHERE event = 'login_success'
	AND timestamp > 20090824000000
	GROUP BY ppl_id ORDER BY min ) bla
GROUP BY bla.dag
EOT
);
if (mysql_num_rows($result) == 0) exit;

$lasttime = mysql_result($result, 0, 0);
$date = date("d-m-Y", mysql_result($result, 0, 0));
$array['dag'][0] = $date;
$array['lln'][0] = mysql_result($result, 0, 1);
$array['doc'][0] = mysql_result($result, 0, 2);
$array['oud'][0] = mysql_result($result, 0, 3);
$day = 0;
for ($i = 1; $i < mysql_num_rows($result); $i++) {
	while (strcmp($date, date("d-m-Y", mysql_result($result, $i, 0)))) {
		$day++;
		$lasttime += 24*60*60;
		$date = $array['dag'][$day] = date("d-m-Y", $lasttime);
		$array['lln'][$day] = $array['lln'][$day - 1];
		$array['doc'][$day] = $array['doc'][$day - 1];
		$array['oud'][$day] = $array['oud'][$day - 1];
	}
	$array['lln'][$day] +=  mysql_result($result, $i, 1);
	$array['doc'][$day] +=  mysql_result($result, $i, 2);
	$array['oud'][$day] +=  mysql_result($result, $i, 3);
}
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
        data.addColumn('number', 'Leerlingen');
        data.addColumn('number', 'Docenten');
        data.addColumn('number', 'Ouders');
	data.addRows(<? echo(count($array['dag'])) ?>);
<? for ($i = 0; $i < count($array['dag']); $i++) { 
	echo "\tdata.setValue($i, 0, '".$array['dag'][$i]."');\n";
	echo "\tdata.setValue($i, 1, ".$array['lln'][$i].");\n";
	echo "\tdata.setValue($i, 2, ".$array['doc'][$i].");\n";
	echo "\tdata.setValue($i, 3, ".$array['oud'][$i].");\n";
} ?>
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
	chart.draw(data, {min: 0, legend: 'bottom', width: 960, height: 480, logScale: false, isStacked: true, is3D: false, title: 'Aantal accounts'});
      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
<? if ($cachedir) {
	$fp = fopen($cachedir.'/graphics_aantal_accounts.html.tmp', 'w') or die('Error opening cache file');
fwrite($fp, ob_get_contents().'<p>cached on '.date(DATE_ATOM)."\n</body>\n</html>\n");
fclose($fp);
rename($cachedir.'/graphics_aantal_accounts.html.tmp', $cachedir.'/graphics_aantal_accounts.html') or die('unable to move');
}
ob_end_flush(); ?>
  </body>
</html>
