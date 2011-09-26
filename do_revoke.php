<? require("include/init.php");
check_login_and_cap('REVOKE');
mysql_query_safe(
	"DELETE FROM ppl2caps WHERE ppl_id = ".
	"( SELECT ppl_id FROM ppl WHERE login = '%s' AND active IS NOT NULL ) ".
	"AND cap_id = '%s' AND granter_ppl_id = '${_SESSION['orig_ppl_id']}'",
	mysql_escape_safe($_POST['login']), mysql_escape_safe($_POST['cap_id']));
if (mysql_affected_rows() > 0) {
$_SESSION['successmsg'] = "Capability cap_id=${_POST['cap_id']} revoked from ${_POST['login']}";
mysql_log('revoke_success', "grant ${_POST['cap_id']} to ${_POST['login']}");
} else {
$_SESSION['successmsg'] = "Capability cap_id=${_POST['cap_id']} not granted by you to ${_POST['login']}";
}
header('Location: beheer.php');
exit;
?>
