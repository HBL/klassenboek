<? function check_login() {
	global $http_path, $load_time, $timeouts;
	if (!isset($_SESSION['ppl_id'])) {
		throw new Exception('de gevraagde pagina of bewerking ('.$_SERVER['script_name'].') is alleen toegankelijk voor mensen die ingelogd zijn', 2);
	} else if ($load_time - $_SESSION['last_load_time'] > 60*$_SESSION['timeout']) {
		$_SESSION['old_post'] = $_POST;
		$_SESSION['old_get'] = $_GET;
		$_SESSION['old_location'] = $_SERVER['SCRIPT_NAME'];
		regular_error($http_path.'/', array('lock_by' => $_SESSION['orig_login']), 'Wegens een inactiviteit van minstens '.$timeouts[$_SESSION['timeout']].' (instelbaar in profiel) is je sessie gelocked. Log hieronder opnieuw in, dan kun je in bepaalde gevallen verder waar je gebleven was.');
	}
	$_SESSION['last_load_time'] = $load_time;
}

function check_group_tags_allowed($tags) {
	if (!isset($tags)) return;
	foreach ($tags as $value) {
		$tmp = sprint_singular(<<<EOQ
SELECT tag_id
FROM tags
WHERE ( tag = 'repetitie'
	OR tag = 'so'
	OR tag = 'proefwerk'
	OR tag = 'inleveren'
	OR tag = 'SE'
	OR tag = 'maken'
	OR tag = 'lezen'
	OR tag = 'maken'
	OR tag = 'leren'
	OR tag = 'in de les'
) AND tag_id = '%s'
EOQ
		, mysql_escape_safe($value));
		if (!isset($tmp)) throw new Exception('illegal tag '.$value);
	}
}

function get_list_of_files() {
// Disabled
return '';

if ($_SESSION['type'] == 'personeel') {
$table = "<h2>Recent geuploade files</h2><p>Hier is een overzicht van de files die je recentelijk hebt geupload. Als je een link wilt maken naar &eacute;&eacute;n van deze files, kopieer dan de betreffende bbcode link naar je notitie";
$result = mysql_query_safe("SELECT file_naam, CONCAT('<code>[url=store/{$_SESSION['ppl_id']}/', file_naam, ']link[/url]</code>') `bbcode link`, CONCAT('<a href=\"store/{$_SESSION['ppl_id']}/', file_naam, '\">download</a>') download FROM files WHERE ppl_id = {$_SESSION['ppl_id']} ORDER BY file_id DESC LIMIT 5");
$table .= sprint_table($result);
if (mysql_num_rows($result) == 0) $table .= '<i>er zijn geen files aanwezig</i><p>Wil je een file uploaden en gebruiken in je notitie. Ga dan eerst naar <a href="upload.php">upload</a>';
mysql_free_result($result);
}
return $table;
}

function ie_warning() {
	if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { ?>
Gebruikers van Internet Explorer 6 kunnen een waarschuwing krijgen over 'insecure
items' bij het bekijken van de grafieken. Deze waarschuwing wordt veroorzaakt door een <a href="http://code.google.com/p/svgweb/issues/detail?id=337">bug</a> in svgweb en kan veilig worden genegeerd. Gebruikers van <b>'thin clients'</b> op school, kunnen de grafieken niet zien met deze browser.
<? } 
}

function test_login() {
	global $load_time;
	if (!isset($_SESSION['ppl_id']) || $load_time  - $_SESSION['last_load_time'] > 60*$_SESSION['timeout']) return 0;
	$_SESSION['last_load_time'] = $load_time;
	return 1;
}

function check_nologin($fatal = 1) {
	global $http_path, $load_time;
	if (!isset($_SESSION['ppl_id'])) return; // OK
	if ($load_time - $_SESSION['last_load_time'] <= 60*$_SESSION['timeout']) {
		$_SESSION['last_load_time'] = $load_time;
		if ($fatal) throw new Exception('de gevraagde pagina of bewerking ('.$_SERVER['SCRIPT_NAME'].') is alleen toegankelijk als je niet ingelogd bent', 2);
		regular_error($http_path.'/', (array)NULL, 
			'De gevraagde pagina of bewerking is '.
			'alleen toegankelijk als je niet ingelogd bent.');
	}
}
		
function check_login_and_caps() {
	check_login();
	if (!count($_SESSION['caps'])) 
		throw new Exception("\$_SESSION['caps'] is empty", 2);
}

function have_cap($cap) {
	return in_array($cap, $_SESSION['caps']);
}

function check_login_and_cap($cap) {
	check_login_and_caps();
	if (!have_cap($cap)) 
		throw new Exception("user doesnt have capability ".
			"<code>$cap</code>", 2);
}

function check_isset_array($array) {
	$args = func_get_args();

	array_shift($args);

	foreach ($args as $value) 
		if (!isset($array[$value])) $complain .= $value.', ';

	if ($complain) throw new Exception(
		substr($complain, 0, strlen($complain) - 2).
		' missing from GET/POST data', 2);
}

function check_isnonempty_array($array) {
	$args = func_get_args();

	array_shift($args);

	foreach ($args as $value)
		if ($array[$value] == '') $complain .= $value.', ';

	if ($complain) throw new Exception(
		substr($complain, 0, strlen($complain) - 2).
		' empty in GET/POST data', 2);
}

function check_email_POST($location) {
	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
		regular_error($location, $_POST, 'Emailadres is ongeldig. '.
			'Vul een geldig emailadres in');
}

function check_required_POST($location) {
	$args = func_get_args();
	array_shift($args);

	/* // deze code noemt de interne naam van de velden, niet zo nuttig
	$fail = (array)NULL;

	foreach ($args as $value)
		if ($_POST[$value] == '')
			array_push($fail, $value);

	if (!($count = count($fail))) return;
	if ($count == 1) $message = 'Het verplicht veld <code>'.$fail[0].
		'</code> is niet ingevuld.';
	else {
		$message = 'De verplichte velden ';
		for ($i = 0; $i < $count; $i++) {
			$message .= '<code>'.$fail[$i].'</code>';
			if ($i == $count - 1) $message .= ' en ';
			else $message .= ', ';
		}
		$message .= ' zijn niet ingevuld';
	}*/
	
	foreach ($args as $value) if ($_POST[$value] == '')
		regular_error($location, $_POST, 'Niet alle verplichte velden zijn ingevuld');

}

function check_integer($int) {
	if (is_numeric($int) && $int = (int) $int) return 1;
	return 0;
}

function check_week($week) {
	global $lesweken;
	if (!check_integer($week)) return 0;
	if (!in_array($week, $lesweken)) return 0;
	return 1;
}

function check_dag($dag) {
	if (!check_integer($dag)) return 0;
	if ($dag < 1 || $dag > 5) return 0;
	return 1;
}

function check_lesuur($lesuur) {
	if (!check_integer($lesuur)) return 0;
	if ($lesuur < 1 || $dag > 9) return 0;
	return 1;
}

function verify_grp($grp_id, $fatal = 1) {
	$result = mysql_query_safe("SELECT * FROM doc2grp2vak ".
		"JOIN grp2vak USING (grp2vak_id) ".
		"WHERE grp_id = '%s' ".
		"AND doc2grp2vak.ppl_id = '${_SESSION['ppl_id']}';",
		mysql_escape_safe($grp_id));

	$num = mysql_numrows($result);
	mysql_free_result($result);
	if (!$num && $fatal) throw new Exception(sprint_singular("SELECT CONCAT(KB_NAAM(naam0, naam1, naam2), ' (', login, ')') FROM ppl WHERE ppl_id = ${_SESSION['ppl_id']}")." attempted access to ".sprint_singular("SELECT naam FROM grp WHERE grp_id = '%s'", mysql_escape_safe($grp_id)));
	
	// als !$num, dan heeft de leraar geen schrijfrechten op de lesgroep
	return $num?1:0;
}

function verify_grp2vak($grp2vak_id) {
	$result = mysql_query_safe("SELECT * FROM doc2grp2vak ".
		"WHERE doc2grp2vak.grp2vak_id='%s' ".
		"AND doc2grp2vak.ppl_id='${_SESSION['ppl_id']}';",
		mysql_escape_safe($grp2vak_id));

	$num = mysql_numrows($result);
	mysql_free_result($result);
	// als !$num, dan heeft de leraar geen schrijfrechten op de lesgroep
	return $num?1:0;
}

function get_default_week() {
	global $lesweken, $schooljaar_long, $load_time;
	$week = date('W', $load_time + 2*24*60*60 + (10 + 7*60)*60);
	$year = date('o', $load_time + 2*24*60*60 + (10 + 7*60)*60);
	$startweek = $lesweken[0];
	$eindweek = $lesweken[count($lesweken) - 1];

	if ($week >= $startweek) {
		if ($year < substr($schooljaar_long, 0, 4)) {
			$week = $startweek;
		} else if ($year > substr($schooljaar_long, 0, 4)) {
			$week = $eindweek;
		}
	} else if ($week <= $eindweek) {
		if ($year < substr($schooljaar_long, 5)) {
			$week = $startweek;
		} else if ($year > substr($schooljaar_long, 5)) {
			$week = $eindweek;
		}
	} else if ($year <= substr($schooljaar_long, 0, 4)) {
		$week = $startweek;
	} else {
		$week = $eindweek;
	}

	if (!in_array($week, $lesweken)) $week++;
	if (!in_array($week, $lesweken)) $week = 1;
	if (!in_array($week, $lesweken)) $week++;

	return $week;
}

function prevweek($week) {
	global $lesweken, $http_path, $http_get;
	$index = array_search($week, $lesweken);
	if ($index > 0) {
		$http_get['week'] = $lesweken[$index - 1];
		echo(sprint_ahref_parms("$http_path/", '&lt;', $http_get));
		$http_get['week'] = $week;
	}
}

function nextweek($week) {
	global $lesweken, $aantal_lesweken, $http_path, $http_get;
	$index = array_search($week, $lesweken);
	if ($index < $aantal_lesweken - 1) echo(sprint_ahref_parms(
		"$http_path/", '&gt;', $http_get,
		'week', $lesweken[$index + 1]));
}

function gen_week_select($week, $autosubmit, &$ret) {
	global $lesweken, $aantal_lesweken, $reload;
	if (!check_week($week)) {
		$reload = 1;
		$week = get_default_week();
	}
	$ret = $week;
	$week_options = sprintf("<select name=\"week\" %s>\n",
		$autosubmit?"onchange=\"document.select.submit()\"":"");
	for ($i = 0; $i < $aantal_lesweken; $i++) {
		$week_options .= sprintf("<option %svalue=\"%s\">%s</option>\n",
			($lesweken[$i] == $week)?"selected ":"", 
			$lesweken[$i], $lesweken[$i]);
	}
	$week_options .= "</select>\n";
	return $week_options;
}

function get_timeout_select($timeout, $autosubmit, &$ret, $allow_none) {
	global $reload, $timeouts;
	$ret = '<select name="timeout">';
	if (!$timeouts[$timeout]) $ret .= '<option selected value=""></option>';
	foreach ($timeouts as $key => $value)
		$ret .= sprintf("<option %svalue=\"$key\">$value</option>",
		       ($key == $timeout)?'selected ':'');
	return $ret.'</select>';
}

function gen_dag_select($dag, $autosubmit, &$ret, $allow_none) {
	global $load_time, $reload;
	if (!check_dag($dag)) {
		if (!$allow_none) {
			// het 9e uur is om 16:50 afgelopen, dat is 
			// 10+7*60 minuten voor middernacht
			$reload = 1;
			$dag = date('w', $load_time + (10 + 7*60)*60);
			if ($dag == 0 || $dag == 6) $dag = 1;
		} else {
			$dag = NULL;
		}
	}
	$ret = $dag;
	$dagen = array('ma', 'di', 'wo', 'do', 'vr');
	$dag_options = sprintf("<select name=\"dag\" %s>\n",
		$autosubmit?"onchange=\"document.select.submit()\"":"");
	if ($allow_none) {
		$dag_options .= sprintf("<option %svalue=\"\">-</option>\n",
			(!$dag)?"selected ":"");
	}
	for ($i = 1; $i <= 5; $i++) {
		$dag_options .= sprintf("<option %svalue=\"%s\">%s</option>\n",
			($dag == $i)?"selected ":"", $i, $dagen[$i-1]);
	}
	$dag_options .= "</select>\n";
	return $dag_options;
}

function gen_lesuur_select($lesuur, $autosubmit, &$ret, $allow_none) {
	global $load_time, $reload;
	if (!check_lesuur($lesuur)) {
		if (!$allow_none) {
			$reload = 1;
			$uur = date('G', $load_time);
			$minuut = date('i', $load_time);
			if ($uur < 9 && $minuut < 20) $lesuur = 1;
			else if ($uur < 10 && $minuut < 10) $lesuur = 2;
			else if ($uur < 11 && $minuut < 20) $lesuur = 3;
			else if ($uur < 12 && $minuut < 10) $lesuur = 4;
			else if ($uur < 13 && $minuut < 30) $lesuur = 5;
			else if ($uur < 14 && $minuut < 20) $lesuur = 6;
			else if ($uur < 15 && $minuut < 10) $lesuur = 7;
			else if ($uur < 16 && $minuut < 0) $lesuur = 8;
			else if ($uur < 16 && $minutt < 50) $lesuur = 9;
			else $lesuur = 1; // eerste uur volgende dag
		} else {
			$lesuur = NULL;
		}
	}
	$ret = $lesuur;
	$lesuur_options = sprintf("<select name=\"lesuur\" %s>\n",
		$autosubmit?"onchange=\"document.select.submit()\"":"");
	if ($allow_none) {
		$lesuur_options .= sprintf("<option %svalue=\"\">-</option>\n",
			(!$dag)?"selected ":"");
	}
	for ($i = 1; $i <= 9; $i++) {
		$lesuur_options .= sprintf(
			"<option %svalue=\"%s\">%s</option>\n",
			($lesuur == $i)?"selected ":"", $i, $i);
	}
	$lesuur_options .= "</select>\n";
	return $lesuur_options;
}

function get_rooster_id() {
if ($_SESSION['type'] != 'leerling' && $_SESSION['type'] != 'ouder' && strlen($_SESSION['login']) == 3) return $_SESSION['login'];
if ($_SESSION['type'] == 'leerling') return sprint_singular(<<<EOQ
SELECT grp.naam
FROM grp
JOIN ppl2grp USING (grp_id)
JOIN ppl USING (ppl_id)
JOIN grp_types USING (grp_type_id)
WHERE grp_type_naam = 'lesgroep'
AND stamklas = 1
AND schooljaar = '$schooljaar'
AND ppl_id = {$_SESSION['ppl_id']}
EOQ
		);
}

function get_rooster_link() {
	if ($id = get_rooster_id()) {
		echo('rooster van <a href="http://ovc.roosternet.nl/?week='.$id.'" onClick="');
		echo("window.open('http://ovc.roosternet.nl/?week=$id', 'rooster', 'width=680,height=512'); return false");
		echo('">'.$id.'</a>');
	}
} ?>
