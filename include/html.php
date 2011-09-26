<?
function gen_html_header($title = NULL, $jquery = NULL) { 
	global $favicon, $http_path, $schoolnaam, $load_time; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<? if ($favicon) { ?>
<link rel="shortcut icon" href="<? echo $http_path ?>/images/<? echo($favicon) ?>">
<? } ?>
<link rel="stylesheet" href="<? echo $http_path ?>/css/style.css">
<? if (isset($jquery)) { ?>
<script src="<? echo($http_path) ?>/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<?	$args = func_get_args();
	// shift off title and jquery
	array_shift($args); array_shift($args);
	foreach ($args as $val) {
		$ext = pathinfo($val, PATHINFO_EXTENSION);
		if (!strcmp($ext, 'js')) { ?>
<script src="<? echo($http_path) ?>/js/<? echo($val) ?>" type="text/javascript"></script>
<? 		} else if (!strcmp($ext, 'css')) { ?>
<link rel="stylesheet" href="<? echo($http_path) ?>/css/<? echo($val) ?>">
<? 		}
	}
} ?>
<title>Online Klassenboek <? echo($schoolnaam); 
if (isset($title)) echo(" - $title"); 
?></title>
<? if ($jquery) { ?>
<script type="text/javascript">$(document).ready(function(){
<? echo($jquery."\n"); ?>
});</script>
<? } ?>
</head>
<body>
<div class="left"><h1>Klassenboek Het Baarnsch Lyceum</h1></div>
<ul id="menu">
<? if (!isset($_SESSION['ppl_id']) || $load_time - $_SESSION['last_load_time'] > 60*$_SESSION['timeout']) { ?>
<!--<li<? if ($title == "Inloggen") { ?> class="active"<? }?>><a href="<?
	echo($http_path) ?>/?lock_by=">Inloggen</a></li>
<li<? if ($title == "Wachtwoord Vergeten") { ?> class="active"<? }?>><a href="<?
	echo($http_path) ?>/pw_reset_request.php">Wachtwoord Vergeten</a></li>
<li<? if ($title == "Aanmaken") { ?> class="active"<? }?>><a href="<?
	echo($http_path) ?>/aanmaken.php">Aanmaken</a></li>
<li<? if ($title == "Ouders") { ?> class="active"<? }?>><a href="<?
	echo($http_path) ?>/ouders.php">Ouders</a></li>!-->
<? } else { ?>
<li><? echo($_SESSION['name']) ?></li>
<li<? if ($title == "Agenda") { ?> class="active"<? }?>><a href="<?
	echo($http_path) ?>/">Agenda</a></li>
<!--<li<? if ($title == "Profiel") { ?> class="active"<? }?>><a href="<?
	echo($http_path) ?>/profile.php">Profiel</a></li>
<li<? if ($title == "Extra") { ?> class="active"<? }?>><a href="<?
	echo($http_path) ?>/extra.php">Extra</a></li>!-->
<? if (count($_SESSION['caps'])) { ?>
<li<? if ($title == 'Beheer') { ?> class="active"<? }?>><a href="<?
	echo($http_path) ?>/beheer.php">Beheer</a></li>
<? } ?>
<li><a href="<? echo($http_path) ?>/do_logout.php">Uitloggen</a></li>
<? } ?>
</ul>
<? } 

function gen_html_footer() { ?>
<p id="footer">
Online Klassenboek &copy; 2010 Rik Snel, 2011 Het Baarnsch Lyceum.<br>
Released as <a href="http://www.gnu.org/philosophy/free-sw.html">free software</a> without warranties under <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html">GNU AGPL v3</a>.<br>
Sourcecode: subversion co <a href="http://cube.dyndns.org/svn/misc/branch/kb-0.1/">http://cube.dyndns.org/svn/misc/branch/kb-0.1/</a>
</p>
</body>
</html>
<? } ?>
