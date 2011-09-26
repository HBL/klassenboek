<? require_once("include/init.php");
check_login();

gen_html_header('Practica'); 
status();

?>
<table style="table-layout: auto" border="1" width="100%">
<colgroup width="2%"></colgroup>
<colgroup width="12.25%" span="5"></colgroup>
<tr>
<th>
<th>122
<th>123
<th>125
<th>127
<th>115
<th>113
<th>110
<th>overig

<?  for ($i = 1; $i <= 9; $i++) { ?>
<tr align="left" valign="top"><td><? echo '<span title="'.$lestijden[$i].'">'.$i.'</span>' ?></td>
<?	for ($j = 1; $j <= 8; $j++) {
		echo('<td>&nbsp;');
	}
}
?>
</table>
</p>
<? gen_html_footer(); ?>
