<? require("include/init.php");
mysql_log('drop_caps');
$_SESSION['caps'] = NULL;
header('Location: index.php');
?>
