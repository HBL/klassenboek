<? require_once('../include/init.php');
//header('Content-Type: text/plain');
//echo($_SERVER['HTTP_REFERER']);
check_login();
if (!isset($_GET['name'])) throw new Exception('raar', 2);

$path = explode('/', $_GET['name']);

$ppl_id = sprint_singular("SELECT ppl_id FROM ppl WHERE type = 'personeel' AND ppl_id = '%s'", mysql_escape_safe($path[0]));

if (!isset($ppl_id)) throw new Exception('persoon '.$path[0].' niet gevonden', 2);

$file_id = sprint_singular("SELECT file_id FROM files WHERE busy = 0 AND ppl_id = '$ppl_id' AND file_naam = '%s'", mysql_escape_safe($path[1]));

$mimetype = sprint_singular("SELECT file_mimetype FROM files WHERE busy = 0 AND ppl_id = '$ppl_id' AND file_naam = '%s'", mysql_escape_safe($path[1]));

if (!isset($file_id)) throw new Exception('file '.$path[1].' niet gevonden', 2);

header("Content-type: $mimetype");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
flush();
readfile('data/'.$file_id);
mysql_query_safe("UPDATE files SET downloaded = downloaded + 1 WHERE file_id = '$file_id'");

?>
