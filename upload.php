<? require_once('include/init.php');

function bytesToSize($bytes, $precision = 2) {  
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
   
    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
 
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
 
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
 
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
 
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $gigabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}
 
check_login();
if ($_SESSION['type'] != 'personeel') throw new Exception('Alleen personeel mag files uploaden', 2);

$quotum = sprint_singular("SELECT quotum FROM ppl WHERE ppl_id = {$_SESSION['ppl_id']}");
$usage = sprint_singular("SELECT SUM(file_size) FROM files WHERE ppl_id = {$_SESSION['ppl_id']}");
if (!isset($usage)) $usage = 0;
$maxupload = sprint_singular("SELECT maxupload FROM ppl WHERE ppl_id = {$_SESSION['ppl_id']}");
if ($quotum - $usage < $maxupload)
	$maxupload = $quotum - $usage;

$result = mysql_query_safe("SELECT file_naam, file_size, CONCAT('<code>[url=store/{$_SESSION['ppl_id']}/', file_naam, ']link[/url]</code>') `bbcode link (voor in notities)`, date datum, CONCAT('<a href=\"delete.php?file_id=', file_id, '\">delete</a>') verwijderen, CONCAT('<a href=\"store/{$_SESSION['ppl_id']}/', file_naam, '\">download</a>') download, downloaded hits FROM files WHERE ppl_id = {$_SESSION['ppl_id']} ORDER BY file_id");

$table = sprint_table($result);
if (mysql_num_rows($result) == 0) $table .= '<i>er zijn geen files aanwezig</i>';
mysql_free_result($result);

gen_html_header("Upload");
status() ?>
Hier kun je een file uploaden naar het klassenboek. Je mag
maximaal <? echo(bytesToSize($quotum)) ?> gebruiken, waarvan je
momenteel <? echo(bytesToSize($usage)) ?> (<? $perc = $usage/$quotum*100; echo round($perc, 1).'%' ?>) hebt gebruikt.

<h2>Geuploade files</h2><p><? echo($table) ?>

<p>Als je een nieuwe groepsnotitie maakt, komt er automatisch een lijst met je meest
recente files onder te staan. Je kunt de bbcode link knippen en plakken naar je notitie.

<h2>Upload</h2>
<? if ($maxupload <= 0) { ?>
<p>Je hebt alle ruimte gebruikt. Je moet files gaan verwijderen of aan de beheerder vragen om meer ruimte. 
<? } else { ?>

<p>Je mag een file uploaden van maximaal <? echo(bytesToSize($maxupload)) ?>.

<p><form enctype="multipart/form-data" action="do_upload.php" method="POST" accept-charset="UTF-8">
<input type="hidden" name="MAX_FILE_SIZE" value="<? echo($maxupload) ?>">
Choose a file to upload: <input name="uploadedfile" type="file">
<p><input type="submit" value="Upload">
</form>

<? } ?>
<? gen_html_footer(); ?>
