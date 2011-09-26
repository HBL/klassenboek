<?
function not_teletop_credentials() {
	if (!$_SESSION['teletop_username'] || !$_SESSION['teletop_password'] ||
		$_SESSION['teletop_username'] == '' || $_SESSION['teletop_password'] == '') return true;
	return false;
}

function curl_teletop_init() {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	//curl_setopt($ch, CURLINFO_REFERER, NULL);
	if (isset($_SESSION['teletop_session'])) curl_setopt($ch, CURLOPT_COOKIE, 'DomAuthSessId='.$_SESSION['teletop_session']);

	return $ch;
}

function find_session_cookie($ch, $line) {
	/* check if we got the session cookie */
	$cookie_id = 'Set-Cookie: DomAuthSessId=';

	if (strpos($line, $cookie_id) === FALSE) return strlen($line);

	$_SESSION['teletop_session'] = 
		substr($line, strlen($cookie_id), strpos($line, ';') - strlen($cookie_id));

	return strlen($line);
}

// de url dient een loginform in text/html te geven indien de gebruiker niet ingelogd is
// $url is het volledige pad zonder de server MET de root slash
//
// we retourneren een xpath object geladen met de gevraagde pagina of geparste json data
function curl_teletop_req($ch, $url, $postdata = NULL, $additional_headers = array(), $recurse = 0) {
	global $http_path, $teletop_server;
	if ($recurse > 1) regular_error($http_path.'/', (array) NULL,
			'curl_teletop_req() heeft zichzelf te vaak aangeroepen, kennelijk is er een BUG');

	curl_setopt($ch, CURLOPT_URL, $teletop_server.$url);

	if ($postdata) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_POST, true);
	} else curl_setopt($ch, CURLOPT_HTTPGET, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, $additional_headers);
	$ret = curl_exec($ch);
	curl_setopt($ch, CURLOPT_HTTPHEADER, (array) NULL);

	if (curl_errno($ch)) regular_error($http_path.'/', (array) NULL, 'Fout bij het laden van '.
		curl_getinfo($ch, CURLINFO_EFFECTIVE_URL).': '.curl_error($ch));

	// indien we niet ingelogd zijn, of onvoldoende rechten hebben, hebben we een HTML document gehad met
	// een login formulier
	
	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

	if (!strncasecmp($content_type, 'text/html', 9)) {
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($ret);
		$xpath = new DOMXPath($doc);

		$form = $xpath->query('//form')->item(0);
		if (!$form || $form->getAttribute('action') != '/names.nsf?Login') return $xpath;

		$errordiv = $xpath->query('//div[@class="errorLogin"]')->item(0);

		if ($errordiv) {
			$error_txt = htmlspecialchars($errordiv->nodeValue, ENT_QUOTES, 'UTF-8');
			
			if (!preg_match('/sessie/', $error_txt) || !preg_match('/is verlopen/', $error_txt)) 
				regular_error($http_path.'/', (array) NULL, 'Error accessing '.curl_getinfo($ch, CURLINFO_EFFECTIVE_URL).': '.  $error_txt);
		}

		if (not_teletop_credentials()) 
			regular_error($http_path.'/', (array) NULL, 'Geen TeleTOP&reg gebruikersnaam en/of '.
			'wachtwoord beschikbaar, vul je informatie in bij <a href="'.$http_path.'/teletop.php">TeleTOP&reg; login informatie</a>');

		dom_form_set_input($form, $xpath, 'Username', $_SESSION['teletop_username'].'/Abvo');
		dom_form_set_input($form, $xpath, 'UsernameInput', $_SESSION['teletop_username'].'/Abvo');
		dom_form_set_input($form, $xpath, 'Password', $_SESSION['teletop_password']);

		if (strtoupper($form->getAttribute('method')) != 'POST') 
			regular_error($http_path.'/', (array) NULL, 'Structuur van het TeleTOP&reg; login formulier is gewijzigd.');

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_URL, $teletop_server.$form->getAttribute('action'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, dom_form($form, $xpath));
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, find_session_cookie);

		unset($_SESSION['teletop_session']);
		curl_exec($ch);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, NULL);

		if (curl_errno($ch)) regular_error($http_path.'/', (array) NULL,
		       		'Fout bij het inloggen op TeleTOP&reg: '.curl_error($ch));

		if (!$_SESSION['teletop_session']) regular_error($http_path.'/', (array) NULL,
		       		'Inloggen bij TeleTOP&reg; mislukt; gebruikersnaam en wachtwoord worden niet geaccepteerd, ga naar de <a href="teletop.php">TeleTOP&reg; login informatiepagina</a> om je gegevens goed in te stellen');

		curl_setopt($ch, CURLOPT_COOKIE, 'DomAuthSessId='.$_SESSION['teletop_session']);

		// we zijn ingelogd, probeer opnieuw
		return curl_teletop_req($ch, $url, $postdata, $additional_headers, $recurse + 1); 

	} else if (!strncasecmp($content_type, 'application/x-json', 18)) {
		return json_decode($ret, true);
	}

	return NULL;
}

function delete_from_teletop($notitie_id) {
	global $schooljaar_long, $http_path, $teletop_vaksite_prefix, $teletop_server;
	$result = mysql_query_safe("SELECT vaksite, vaksite_id, doc_id FROM notities2teletop JOIN vaksites USING (vaksite_id) WHERE notitie_id = '%s'", mysql_escape_safe($notitie_id));
	if (!mysql_numrows($result)) return; // notitiestaat niet meer in TeleTOP

	$vaksite = $teletop_vaksite_prefix.mysql_result($result, 0, 'vaksite').'.nsf';
	$doc_id = mysql_result($result, 0, 'doc_id');

	$ch = curl_teletop_init();

	curl_teletop_req($ch, $vaksite.'/a-DeleteDocuments?OpenAgent&id='.$doc_id.'&docType=rosterrow');

	// assume success
	mysql_query_safe("DELETE FROM notities2teletop WHERE notitie_id = '%s'", mysql_escape_safe($notitie_id));
}

function teletop_check_conflict($ch, $datum, $klas) {
	$json_data = curl_teletop_req($ch, '/tt/abvo/updates.nsf/a-SearchRosterRows?OpenAgent&rand='.rand(0,99999998),
		'sa=1&dformat=mm/dd/yyyy&date='.$datum.'&ug='.$klas.'&',
       		array('X-Requested-With: XMLHttpRequest'));

	if (!$json_data) $_SESSION['warningmsg'] = 'De server van TeleTOP&reg; heeft ongeldige JSON data naar ons teruggestuurd, we hebben helaas niet kunnen constateren of er toetsconflicten waren.';
	else {
		$bla = '';
		foreach ($json_data['result']['rows'] as $value) $bla = $value['cn'].' '.$value['sa'].'<br>';

		if ($bla != '') {
			$_SESSION['warningmsg'] = 'Voor deze klas zijn de volgende belastende activiteiten gepland. De nieuwe notitie is in TeleTOP&reg; geplaatst, maar moet wellicht nog worden verplaatst.<br><div>'.$bla.'</div>';
		}
	}
}

function change_in_teletop($grp2vak_id, $notitie_id, $week, $dag, $text, $sa = '') {
	global $schooljaar_long, $http_path, $teletop_vaksite_prefix, $teletop_server;
	$vaksite = $teletop_vaksite_prefix.sprint_singular("SELECT vaksite FROM grp2vak2vaksite JOIN vaksites USING (vaksite_id) WHERE grp2vak_id = '%s'",
		mysql_escape_safe($grp2vak_id)).'.nsf';
	$vaksite_id = sprint_singular("SELECT vaksite_id FROM grp2vak2vaksite WHERE grp2vak_id = '%s'",
		                mysql_escape_safe($grp2vak_id));

	$doc_id = sprint_singular("SELECT doc_id FROM notities2teletop WHERE notitie_id = '%s'",
		mysql_escape_safe($notitie_id));

	$ch = curl_teletop_init();
	$xpath = curl_teletop_req($ch, $vaksite.'/id/'.$doc_id.'?EditDocument');

	$form = $xpath->query('//form')->item(0);

	if (!$form || strcasecmp($form->getAttribute('method'), 'POST')) regular_error($http_path.'/', (array) NULL, 'Interface van TeleTOP&reg drastisch gewijzigd.');

	if ($week < 30) $year = substr($schooljaar_long, 5);
	else $year = substr($schooljaar_long, 0, 4);
	$day_in_week = strtotime(sprintf("$year-01-04 + %d weeks", $week - 1));
	$thismonday = $day_in_week - ((date('w', $day_in_week) + 6)%7)*24*60*60;

	$datum = date("m/d/Y", $thismonday + ($dag - 1)*24*60*60); //'09/03/2010'; $datum; // mm/dd/yy
	$klas = sprint_singular("SELECT naam FROM grp JOIN grp2vak USING (grp_id) WHERE grp2vak_id = '%s'",
		mysql_escape_safe($grp2vak_id));
	
	// deze twee controls worden gedisabled in javascript, dus dat doen we hier ook maar
	dom_form_disable_inputs($form, $xpath, 'PublicationDate');
	dom_form_disable_inputs($form, $xpath, 'PublicationDateOff');

	// hier simuleren we userinput in het form
	dom_form_check_checkbox($form, $xpath, 'TargetAudienceUserGroup', $klas);
	dom_form_set_input($form, $xpath, 'RosterRowDate', $datum);
	dom_form_set_input($form, $xpath, 'RosterRowWeek', $week);
	dom_form_set_textarea($form, $xpath, 'RosterRowItem2', $text);

	if ($sa) {
		dom_form_check_checkboxen($form, $xpath, 'RosterRowIsSpecialActivity');
		dom_form_enable_selects($form, $xpath, 'RosterRowSpecialActivity');
		dom_form_select_select_option($form, $xpath, 'RosterRowSpecialActivity', $sa);
	} else {
		dom_form_uncheck_checkboxen($form, $xpath, 'RosterRowIsSpecialActivity');
		dom_form_disable_selects($form, $xpath, 'RosterRowSpecialActivity');
	}

	if ($sa) teletop_check_conflict($ch, $datum, $klas);

	// submit form
	curl_teletop_req($ch, $form->getAttribute('action'), dom_form($form, $xpath));
}

function add_to_teletop($grp2vak_id, $notitie_id, $week, $dag, $text, $sa = NULL) {
	global $schooljaar_long, $http_path, $teletop_vaksite_prefix, $teletop_server;

	$result = mysql_query_safe(<<<EOF
SELECT vaksite, vaksite_id, naam klas
FROM grp2vak2vaksite 
JOIN vaksites USING (vaksite_id)
JOIN grp2vak USING (grp2vak_id)
JOIN grp USING (grp_id)
WHERE grp2vak_id = '%s'
EOF
, mysql_escape_safe($grp2vak_id)); 

	if (!mysql_numrows($result)) regular_error($http_path.'/', (array) NULL, 'We kunnen deze notitie niet in TeleTOP&reg; zetten, omdat de vaksite onbekend is.');

	$info = mysql_fetch_array($result, MYSQL_ASSOC);
	$vaksite = $teletop_vaksite_prefix.$info['vaksite'].'.nsf';

	$ch = curl_teletop_init();
	$xpath = curl_teletop_req($ch, $vaksite.'/f-RosterRow?OpenForm');

	if (!$xpath) regular_error($http_path.'/', (array) NULL, 'TeleTOP&reg; doet net of '.$vaksite.'/f-RosterRow?OpenForm niet bestaat; '.
		'de notitie kan momenteel niet worden geplaatst.');

	$form = $xpath->query('//form')->item(0);

	if (!$form || strcasecmp($form->getAttribute('method'), 'POST')) regular_error($http_path.'/', (array) NULL, 'Interface van TeleTOP&reg drastisch gewijzigd.');

	if ($week < 30) $year = substr($schooljaar_long, 5);
	else $year = substr($schooljaar_long, 0, 4);
	$day_in_week = strtotime(sprintf("$year-01-04 + %d weeks", $week - 1));
	$thismonday = $day_in_week - ((date('w', $day_in_week) + 6)%7)*24*60*60;

	$datum = date("m/d/Y", $thismonday + ($dag - 1)*24*60*60); //'09/03/2010'; $datum; // mm/dd/jjjj

	// deze twee controls worden gedisabled in javascript, dus dat doen we hier ook maar
	dom_form_disable_inputs($form, $xpath, 'PublicationDate');
	dom_form_disable_inputs($form, $xpath, 'PublicationDateOff');

	// hier simuleren we userinput in het form
	dom_form_check_checkbox($form, $xpath, 'TargetAudienceUserGroup', $info['klas']);
	dom_form_set_input($form, $xpath, 'RosterRowDate', $datum);
	dom_form_set_input($form, $xpath, 'RosterRowWeek', $week);
	dom_form_set_textarea($form, $xpath, 'RosterRowItem2', $text);

	if ($sa) {
		dom_form_check_checkboxen($form, $xpath, 'RosterRowIsSpecialActivity');
		dom_form_enable_selects($form, $xpath, 'RosterRowSpecialActivity');
		dom_form_select_select_option($form, $xpath, 'RosterRowSpecialActivity', $sa);
	}

	// extract het rijnummer waarmee we straks de doc_id gaan terugvinden
	$roster_row_nr = $xpath->query('.//input[@name="RosterRowNr"]', $form)->item(0)->getAttribute('value');

	if ($sa) teletop_check_conflict($ch, $datum, $info['klas']);

	curl_teletop_req($ch, $form->getAttribute('action'), dom_form($form, $xpath));

	$xpath = curl_teletop_req($ch, $vaksite.'/f-Roster?ReadForm');

	$dingies = $xpath->query('//table[@id="view"]/tbody/tr[@id="'.$roster_row_nr.'"]/td[2]');
	if (!$dingies->item(0)) regular_error($http_path.'/', (array) NULL,
			'Notitie geprobeerd in te voegen in TeleTOP&reg; met regelnummer '.$roster_row_nr.
			', de notitie is echter niet teruggevonden in TeleTOP&reg;.');

	$doc_id = preg_replace('/writeEditLink\(\'id\/(.*)\?EditDocument.*$/', '$1', $dingies->item(0)->nodeValue);

	mysql_query_safe("INSERT INTO notities2teletop ( notitie_id, vaksite_id, doc_id, col ) VALUES ( '%s', '{$info['vaksite_id']}', '%s', 'col2' )",
		mysql_escape_safe($notitie_id), mysql_escape_safe($doc_id));
}

?>
