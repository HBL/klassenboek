<?
function mysql_connect_safe() {
	global $mysql_server, $mysql_username, $mysql_password;
	if (!@mysql_connect($mysql_server, $mysql_username, $mysql_password))
		throw new Exception("mysql_connect(\"$mysql_server\", \"$mysql_username\", \"XXXXXXXX\"): ".mysql_error(), 0);
	if (!@mysql_set_charset('utf8')) throw new Exception("mysql_set_charset('utf8') unable to set character set", 0);
}

function mysql_query_safe() {
	global $mysql_database;

	mysql_connect_safe();

	if (!mysql_select_db($mysql_database)) throw new Exception(
			"mysql_select_db(\"$mysql_database\"): ".
			mysql_error(), 0);

	// we have an sql connection now
	$args = func_get_args();
	$query = call_user_func_array('sprintf', $args);
	if (!($result = mysql_query($query))) throw new Exception(
			"mysql_query(\"$query\"):".mysql_errno().":".mysql_error(), 1);

	return $result;
}

function mysql_query_safe_nonempty() {
	$args = func_get_args();
	$result = call_user_func_array('mysql_query_safe', $args);
	if (!mysql_num_rows($result)) throw new Exception('SQL query returned '.
		'no results, where at least one was expceted', 2);

	return $result;
}

function mysql_escape_safe($string) {
	// a database connection is needed for safe escaping
	mysql_connect_safe();
	if (get_magic_quotes_gpc()) {
		$string = stripslashes($string);
	}

	return mysql_real_escape_string($string);
}

function mysql_log($event, $comment=NULL) {
	try {
		mysql_query_safe("INSERT INTO log ( event, orig_ppl_id, ".
			"ppl_id, ip%s ) ".
			"VALUES ( '$event', %s, %s, ".
			"'${_SERVER['REMOTE_ADDR']}'%s ".
			");", $comment?", comment":"",
			isset($_SESSION['orig_ppl_id'])?$_SESSION['orig_ppl_id']:"NULL",
			isset($_SESSION['ppl_id'])?$_SESSION['ppl_id']:"NULL",
			$comment?", '".mysql_escape_safe($comment)."'":"");
	}
	catch (Exception $e) {
		/* don't log errors logging errors */
	}
}
?>
