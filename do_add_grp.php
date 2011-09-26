<? require("include/init.php");
check_login_and_cap('ADD_GRP');
if ($_POST['grp_type_id'] == '' || $_POST['naam'] == '') {
	header("Location: beheer.php");
	exit;
}
mysql_query_safe(
	"INSERT INTO grp ( naam, schooljaar, grp_type_id ) ".
	"VALUES ( '%s', '$schooljaar', '%s' ) ",
	mysql_escape_safe(htmlspecialchars($_POST['naam'], ENT_QUOTES, "UTF-8")),
	mysql_escape_safe($_POST['grp_type_id']));
mysql_log('add_grp_success', "add group ${_POST['naam']} of type ${_POST['grp_type_id']} to database");
$_SESSION['successmsg'] = sprintf("Added group <code>%s</code> of type {$_POST['grp_type_id']}", htmlspecialchars($_POST['naam'], ENT_QUOTES, "UTF-8"));
header('Location: beheer.php');
exit;
?>
