<? require("include/init.php");
check_login_and_cap('GRANT');
mysql_query_safe(
	"INSERT INTO ppl2caps ( ppl_id, cap_id, granter_ppl_id ) ".
	"VALUES ( ( SELECT ppl_id FROM ppl WHERE login = '%s' ".
	"AND active IS NOT NULL ), '%s', '${_SESSION['orig_ppl_id']}' ) ",
	mysql_escape_safe($_POST['login']),
	mysql_escape_safe($_POST['cap_id']));
mysql_log('grant_success', "grant ${_POST['cap_id']} to ${_POST['login']}");
$_SESSION['successmsg'] = "Capability cap_id=${_POST['cap_id']} granted to ${_POST['login']}";
header('Location: beheer.php');
exit;
?>
