<? require_once('include/init.php');
check_login();

if ($_POST['submit'] != 'Ja, wissen aub') {
	header("Location: upload.php");
	exit;
}

$naam = sprint_singular("SELECT file_naam FROM files WHERE file_id = '%s' AND ppl_id = {$_SESSION['ppl_id']}", mysql_escape_safe($_POST['file_id']));

if (!isset($naam)) throw new Exception("file_id={$_POST['file_id']} bestaat niet of is niet van ppl_id={$_SESSION['ppl_id']}", 2);

mysql_query_safe("UPDATE files SET busy = 1 WHERE file_id = '%s'",
	mysql_escape_safe($_POST['file_id']));

if (!unlink('store/data/'.$_POST['file_id'])) {
	mysql_query_safe("UPDATE files SET busy = 0 WHERE file_id = '%s'",
		mysql_escape_safe($_POST['file_id']));
	regular_error('upload.php', (array) NULL, 'het wissen van '.$naam.' is mislukt, vraag de beheerder om hulp');
}

mysql_query_safe("DELETE FROM files WHERE file_id = '%s'", mysql_escape_safe($_POST['file_id']));

$_SESSION['successmsg'] = "File <code>$naam</code> is gewist.";
header("Location: upload.php");

?>

