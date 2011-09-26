<? require("include/init.php");
check_nologin();
check_isset_array($_POST, 'login', 'password');
check_required_POST($http_path.'/', 'login', 'password');

// Check login BLIOS
$normal_db = $mysql_database;
$mysql_database = 'hbl_blios';
$result = mysql_query_safe("SELECT username FROM users WHERE LOWER(username) =LOWER('%s') AND password = MD5('%s')", 
				mysql_escape_safe(htmlspecialchars($_POST['login']), ENT_QUOTES, 'UTF-8'),
				mysql_escape_safe($_POST['password']));
//Switch back to the normal db
$mysql_database = $normal_db;

if(mysql_num_rows($result) == 0) {
        if (isset($_POST['lock_by'])) regular_error($http_path.'/', $_POST, 'Wachtwoord onjuist. '.
                'Wil je als iemand anders inloggen? Klik dan op '."'Inloggen'.");
        else regular_error($http_path.'/', $_POST, 'Gebruikersnaam <code>'.
                "${_POST['login']}</code> of wachtwoord onjuist. ".
                "Aanmaakcodes horen in het tabblad 'Aanmaken', ".
                'niet in de loginpagina.');
}

// Login is correct, now let's match it with Klassenboek login

$result = mysql_query_safe("SELECT KB_NAAM(naam0, naam1, naam2) naam, ".
	"type, ppl_id, login, timeout, ".
	"GROUP_CONCAT(caps.name) caps ".
	"FROM caps ".
	"LEFT JOIN ppl2caps USING (cap_id) ".
	"RIGHT JOIN ppl USING (ppl_id) ".
	"WHERE login = '%s' ".
	"AND active IS NOT NULL ".
	"GROUP BY ppl_id;",
	mysql_escape_safe(htmlspecialchars($_POST['login']), ENT_QUOTES, 'UTF-8')
	); // Password check is removed here, because of BLIOS check 

if (!($row = mysql_fetch_assoc($result))) {
	if (isset($_POST['lock_by'])) regular_error($http_path.'/', $_POST, 'Wachtwoord onjuist. '.
		'Wil je als iemand anders inloggen? Klik dan op '."'Inloggen'.");
	else regular_error($http_path.'/', $_POST, 'Gebruikersnaam <code>'.
		"${_POST['login']}</code> of wachtwoord onjuist. ".
		"Aanmaakcodes horen in het tabblad 'Aanmaken', ".
		'niet in de loginpagina.');
}

if (isset($_POST['lock_by']) && $_SESSION['ppl_id']) {
	if ($_POST['login'] != $_POST['lock_by']) throw new Exception('login en lock_by niet gelijk', 2);
	$_SESSION['last_load_time'] = $load_time;
	if (!isset($_SESSION['old_location'])) throw new Exception('old_location not set in session', 2);

	if ($_SESSION['old_post']) {
		if ($_SESSION['old_get']) {
			unset($_SESSION['old_get']);
			unset($_SESSION['old_post']);
			$old = $_SESSION['old_location'];
			unset($_SESSION['old_location']);
			regular_error($http_path.'/', (array)NULL,
				'Het resumen van '.$old.' kan niet; '.
				'er is zowel POST als GET data excuses voor de eventuele valse hoop');
		}
		unset($_SESSION['old_get']);
		unset($_SESSION['old_post']);
		$old = $_SESSION['old_location'];
		unset($_SESSION['old_location']);
		regular_error($http_path.'/', (array)NULL,
			'Het resumen van '.$old.' is niet '.
			'geimplementeerd, met excuses voor de eventuele valse hoop');
	} 

	header('Location: '.$_SESSION['old_location'].sprint_url_parms($_SESSION['old_get']));
} else {
	$_SESSION['type'] = $row['type'];
	$_SESSION['name'] = $row['naam'];
	$_SESSION['login'] = $row['login'];
	$_SESSION['orig_login'] = $row['login'];
	$_SESSION['last_load_time'] = $load_time;
	$_SESSION['timeout'] = $row['timeout'];
	$_SESSION['orig_ppl_id'] = $row['ppl_id'];
	$_SESSION['ppl_id'] = $row['ppl_id'];
	if ($row['caps'] != '') $_SESSION['caps'] = explode(',', $row['caps']);
	header('Location: '.$http_path.'/');
	mysql_query_safe("INSERT INTO ppl2phpsessid (ppl_id, phpsessid) VALUES ('{$_SESSION['ppl_id']}', '%s')", session_id());
} 
unset($_SESSION['old_get']);
unset($_SESSION['old_post']);
unset($_SESSION['old_location']);
if ($row['teletop_username'] && $row['teletop_password']) {

	// experiment met mcrypt
	$td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
	mcrypt_generic_init($td, $_POST['password'], 'TeleTop&reg;01234567890123456789');
	$safe_password = rtrim(mdecrypt_generic($td, $row['teletop_password']), "\0");
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);

	$_SESSION['teletop_username'] = $row['teletop_username'];
	$_SESSION['teletop_password'] = $safe_password;

	$ch = curl_teletop_init();
	curl_teletop_req($ch, '/tt/abvo/lms.nsf/f-MyCourses?OpenForm');

}

mysql_log('login_success');
?>
