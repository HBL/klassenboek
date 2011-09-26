<? require_once('include/init.php');
check_login(); 
$result = mysql_query_safe(<<<EOT
SELECT KB_NAAM(naam0, naam1, naam2) naam,
	COUNT(IF(DATEDIFF(NOW(), timestamp) = 0, ppl.ppl_id, NULL)) today,
	COUNT(IF(DATEDIFF(NOW(), timestamp) > 0 AND DATEDIFF(NOW(), timestamp) <= 7, ppl.ppl_id, NULL)) week,
	COUNT(IF(DATEDIFF(NOW(), timestamp) > 7 AND DATEDIFF(NOW(), timestamp) <= 30, ppl.ppl_id, NULL)) month,
	COUNT(IF(DATEDIFF(NOW(), timestamp) > 30 AND DATEDIFF(NOW(), timestamp) <= 90, ppl.ppl_id, NULL)) quarter
FROM log
JOIN ppl USING (ppl_id)
JOIN ppl2grp USING (ppl_id)
JOIN grp USING (grp_id)
JOIN grp_types USING (grp_type_id)
JOIN grp2vak USING (grp_id)
JOIN doc2grp2vak USING (grp2vak_id)
WHERE event = 'login_success'
AND grp_id = '%s'
AND schooljaar = '$schooljaar'
AND doc2grp2vak.ppl_id = '{$_SESSION['ppl_id']}'
GROUP BY ppl.ppl_id
ORDER BY today, week, month, quarter
EOT
, mysql_escape_safe($_GET['grp_id']));
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
        data.addColumn('number', 'Recent');
        data.addColumn('number', 'Niet zo recent');
        data.addColumn('number', 'Lang geleden');
	data.addRows(<? echo(mysql_num_rows($result)) ?>);
<? for ($i = 0; $i < mysql_num_rows($result); $i++) { 
	echo "\tdata.setValue($i, 0, '".addslashes(htmlspecialchars_decode(mysql_result($result, $i, 0), ENT_QUOTES))."');\n";
	echo "\tdata.setValue($i, 1, ".mysql_result($result, $i, 1).");\n";
	echo "\tdata.setValue($i, 2, ".mysql_result($result, $i, 2).");\n";
	echo "\tdata.setValue($i, 3, ".mysql_result($result, $i, 3).");\n";
	echo "\tdata.setValue($i, 4, ".mysql_result($result, $i, 4).");\n";
} ?>
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
	chart.draw(data, {min: 0, legend: 'bottom', width: 960, height: 480, logScale: true, isStacked: true, is3D: false, title: 'Logins per klas'});
      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
  </body>
</html>
