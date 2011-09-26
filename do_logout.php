<? require("include/init.php");
mysql_log('logout');
mysql_query_safe("DELETE FROM ppl2phpsessid WHERE ppl_id = '{$_SESSION['ppl_id']}'");
session_destroy();
header('Location: index.php');
?>
