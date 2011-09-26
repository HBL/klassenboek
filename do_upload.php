<? require_once('include/init.php');
check_login();
if ($_SESSION['type'] != 'personeel') throw new Exception('Alleen personeel mag files uploaden', 2);

switch ($_FILES['uploadedfile']['error']) {
	case UPLOAD_ERR_INI_SIZE:
		regular_error('upload.php', (array) NULL, 'ge-uploade file te groot volgens php.ini');
	case UPLOAD_ERR_FORM_SIZE:
		regular_error('upload.php', (array) NULL, 'ge-uploade file te groot volgens de policy van het klassenboek');
	case UPLOAD_ERR_PARTIAL:
		regular_error('upload.php', (array)NULL,
			'upload mislukt, file is slechts '.
			'gedeeltelijk aangekomen', 2);
	case UPLOAD_ERR_NO_FILE:
		regular_error('upload.php', (array)NULL,
			'Er is geen file geselecteerd.');
	case UPLOAD_ERR_NO_TMP_DIR:
		throw new Exception('/tmp bestaat niet op de server', 2);
	case UPLOAD_ERR_CANT_WRITE:
		throw new Exception('write error (disk full?)', 2);
	case UPLOAD_ERR_OK:
		break;
	default:
		throw new Exception('impossible error', 2);
}

$maxupload = sprint_singular("SELECT maxupload FROM ppl WHERE ppl_id = {$_SESSION['ppl_id']}");

if ($_FILES['uploadedfile']['size'] > $maxupload)
	throw new Exception('geuploade file te groot, daarnaast is de MAX_FILE_SIZE parameter waarschijnlijk gemanipuleerd');

$naam = htmlspecialchars($_FILES['uploadedfile']['name'], ENT_QUOTES);
if (strlen($naam) > 128) regular_error('upload.php',
	(array) NULL, 'De filename van de geuploade file is te lang');

try {
	mysql_query_safe("INSERT INTO files ( file_sha1, file_naam, ".
		"file_mimetype, file_size, ppl_id ) ".
		"VALUES ( '%s', '%s', '%s', '%s', '{$_SESSION['ppl_id']}' )",
			sha1_file($_FILES['uploadedfile']['tmp_name']), 
			mysql_escape_safe($naam),
			mysql_escape_safe($_FILES['uploadedfile']['type']),
			$_FILES['uploadedfile']['size']);
}
catch (Exception $e) {
	if (mysql_errno() != 1062) throw($e);
	regular_error('upload.php', (array) NULL,
		'Je hebt al een file met de naam <code>'.$naam.'</code> op het klassenboek staan.');
}

$file_id = mysql_insert_id();

$quotum = sprint_singular("SELECT quotum FROM ppl WHERE ppl_id = {$_SESSION['ppl_id']}");
$usage = sprint_singular("SELECT SUM(file_size) FROM files WHERE ppl_id = {$_SESSION['ppl_id']}");

if ($usage > $quotum) {
	mysql_query("DELETE FROM files WHERE file_id = $file_id");
	regular_error('upload.php', (array) NULL, 'De file past niet, je hebt te weinig ruimte over');
}

// alles is in orde, we kunnen de file verplaatsen
if (!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], 'store/data/'.$file_id)) {
	mysql_query("DELETE FROM files WHERE file_id = $file_id");
	regular_error('upload.php', (array) NULL, 'Het opslaan van de geuploade file is niet gelukt');
}

mysql_query("UPDATE files SET busy = 0 WHERE file_id = '$file_id'");

$_SESSION['successmsg'] = 'File geupload';

header('Location: upload.php') ?>
