<?  function exception_handler($e) {
	global $http_path, $beheerder;
	switch ($e->getCode()) {
		case 0:
			$_SESSION['errhdr'] = 'SQL server error';
			$_SESSION['errhuman'] = 'Er is een probleem bij het verbinding maken met de database server. De server is overbelast of gecrashed etc. Er zit niets anders op dan even te wachten en het opnieuw te proberen.';
			/* can't connect or select database, it makes no
			 * sense to try to log this error */
			break;
		case 1:
			$_SESSION['errhdr'] = 'SQL query error';
			$_SESSION['errhuman'] = 'Het is in principe niet mogelijk dat u deze foutmelding krijgt. Als het probleem zich opnieuw voordoet, wilt u dan de beheerder, '.htmlspecialchars($beheerder).' op de hoogte brengen?';
			mysql_log('sql_error', $e->getMessage());
			break;
		case 2:
			$_SESSION['errhdr'] = 'Permission Denied';
			$_SESSION['errhuman'] = 'Een script heeft onjuiste argumenten binnen gekregen via een POST of GET operatie. Of de gebruiker probeert een pagina te bekijken of een bewerking uit te voeren waarvoor hij geen rechten heeft. Dit wordt veroorzaakt door een fout in het aanroepende script, of een gebruiker die de veiligheid van dit systeem aan het testen is.';
			mysql_log('arg_error', $e->getMessage());
			break;
		default:
			$_SESSION['errhdr'] = 'Niet bestaande error code?!?!?!';
			$_SESSION['errhuman'] = 'Een script heeft ons een niet bestaande errorcode gegeven, dit is onmogelijk.';
			mysql_log('errno_error', $e->getMessage());
	}
	$_SESSION['errmsg'] = $e->getFile().':'.$e->getLine().':'.$e->getMessage();
	header('Location: '.$http_path.'/fatal.php');
	exit;
}

function regular_error($location, $array, $message) {
	$args = func_get_args();

	// shift off arguments
	array_shift($args); array_shift($args);

	mysql_log('regular_error', $location.': '.$message); 

	$_SESSION['errormsg'] = call_user_func_array(sprintf, $args);
	//header('Location: '.$_SERVER['SCRIPT_NAME'].sprint_url_parms($array));
	header('Location: '.$location.sprint_url_parms($array));
	exit;
}

?>
