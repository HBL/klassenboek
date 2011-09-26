<? include("include/init.php");
check_login();
$row = mysql_fetch_array(mysql_query_safe_nonempty("SELECT email, timeout ".
	"FROM ppl ".
	"WHERE ppl_id = ${_SESSION['ppl_id']};"));

if (filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) $row['email'] = $_GET['email'];
if ($timeouts[$_GET['timeout']]) $row['timeout'] = $_GET['timeout'];

$timeout_select = get_timeout_select($row['timeout'], 0, $tmp, 0);

gen_html_header('Profiel'); 
status();
?>
<form action="do_profile.php" method="POST" accept-charset="UTF-8">
<table>
<tr><td>email:</td><td><input type="text" name="email" value="<? echo($row['email']) ?>">
<tr><td>nieuw wachtwoord:</td><td><input name="new_pw0" type="password">
<tr><td>nogmaals nieuw wachtwoord:</td><td><input name="new_pw1" type="password">
<tr><td>lock sessie na:</td><td>
<? echo($timeout_select) ?>
<tr><td>huidig wachtwoord:</td><td><input name="password" type="password"> (verplicht!)
</table>
<input type="submit" value="Opslaan"> <a href="profile.php">[reset]</a>
</form>

<? gen_html_footer(); ?>
