<? require_once('include/init.php');
check_login();

$naam = sprint_singular("SELECT file_naam FROM files WHERE file_id = '%s' AND ppl_id = {$_SESSION['ppl_id']}", mysql_escape_safe($_GET['file_id']));

if (!isset($naam)) throw new Exception("file_id={$_GET['file_id']} bestaat niet of is niet van ppl_id={$_SESSION['ppl_id']}", 2);

gen_html_header("Delete");
?>

<form action="do_delete.php" method="POST" accept-charset="UTF-8">
<input type="hidden" name="file_id" value="<? echo($_GET['file_id']) ?>">
Weet je zeker dat je de file met naam <code><? echo($naam) ?></code> wilt wissen?
<p><input type="submit" name="submit" value="Ja, wissen aub">
<input type="submit" name="submit" value="Nee, file behouden">
</form>

<? gen_html_footer() ?>
