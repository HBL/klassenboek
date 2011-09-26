<? require_once('include/init.php');

if (!isset($_SESSION['errhdr'])) {
	header("Location: $http_path/");
	exit;
}

gen_html_header('Fatale fout'); ?>
<div id="errmsg"><h1><? echo($_SESSION['errhdr']) ?></h1>
<p style="font-size:14px"><? echo($_SESSION['errhuman']) ?>
<p style="font-size:9px"><? echo(htmlspecialchars($_SESSION['errmsg'], ENT_QUOTES, 'UTF-8')) ?>
</div>
<? unset($_SESSION['errhdr']);
gen_html_footer(); ?>
