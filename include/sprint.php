<?
function sprint_tag_checkbox($name, $tag, $checked_tags = NULL) {
	$tag_id = sprint_singular("SELECT tag_id FROM tags WHERE tag = '$tag'");
	if (!isset($tag_id)) throw new Exception('impossible tag asked from system');
	if (is_array($checked_tags) && in_array($tag_id, $checked_tags)) $checked = "checked ";
	return '<input type="checkbox" '.$checked.'name="'.$name.'" value="'.$tag_id.'">'.$tag;
}

function sprint_tag_select($name, $available_tags) {
	$args = func_get_args();
	
	// shift off arguments
	array_shift($args); array_shift($args);

	$ret = '<select name="'.$name.'">'."\n";
	$ret .= '</select>'."\n";

	return $ret;
}

function sprint_table($result) {
	if (mysql_num_rows($result) == 0) return '<table></table>';
	$no_fields = mysql_num_fields($result);

	$html_table = '<table><tr>';
	for ($i = 0; $i < $no_fields; $i++) {
		$html_table .= '<th>'.mysql_field_name($result, $i);
	}
	$html_table .= "\n";

	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$html_table .= '<tr>';
		for ($i = 0; $i < $no_fields; $i++) {
			if (isset($row[$i])) $html_table .= '<td>'.$row[$i];
			else $html_table .= '<td><i>NULL</i>';
		}
		$html_table .= "\n";
	}

	$html_table .= '</table>';
	mysql_data_seek($result, 0);
	return $html_table;
}

function sprint_singular() {
	$args = func_get_args();
	$row = mysql_fetch_row($result = call_user_func_array(mysql_query_safe, $args));
	mysql_free_result($result);
	return $row[0];
}

function sprint_url_parms($array, $add_key = NULL, $add_val = NULL) {
	$send = "?";
	foreach ($array as $key => $value) {
		if ($key == 'password' || $key == 'new_pw0' || $key == 'new_pw1' | $key == $add_key)
			continue;
		$send .= urlencode($key).'='.urlencode($value).'&';
	}
	if ($add_key) return $send.urlencode($add_key).'='.urlencode($add_val);
	return substr($send, 0, strlen($send) - 1);
}

function sprint_ahref_parms($link, $text, $array, $add_key = NULL, $add_val = NULL) {
	return '<a href="'.$link.sprint_url_parms($array,
		$add_key, $add_val).'">'.$text.'</a>';
}

function sprint_select($default, $autosubmit, &$ret, $allow_empty) {
	global $reload;
	$args = func_get_args();
	
	// shift off arguments
	array_shift($args); array_shift($args);
	array_shift($args); array_shift($args);

	$result = call_user_func_array(mysql_query_safe, $args);

	if (!mysql_num_rows($result)) return '';

	$selected = 0;
	$ret = NULL;

	$out = "<select name=\"".mysql_field_name($result, 0)."\"$autosubmit>\n";
	while ($row = mysql_fetch_row($result)) {
		$add = '';
		if ($ret == NULL && !$allow_empty) {
			$ret = $row[0];
		}
		if ($row[0] == $default) {
			$selected = 1;
			$add = 'selected ';
			$ret = $default;
		}
		$out .= "<option ${add}value=\"${row[0]}\">${row[1]}</option>\n";
	}
	mysql_free_result($result);

	$out .= "</select>\n";

	if ($allow_empty) {
		if ($default = NULL) $add = 'selected ';
		else $add = '';
		$out = preg_replace("/\">/",
			"\">\n<option ${add}value=\"\">-</option>", $out, 1);
	}

	if (!$selected) {
		if (!$allow_empty) $reload = 1;
		$out = preg_replace("/value/", "selected value", $out, 1);
	}

	return $out;
}

function sprint_grp2vak_select($default, $autosubmit, &$ret, $allow_empty) {
	global $schooljaar;
	return sprint_select($default, $autosubmit, &$ret, $allow_empty,
		"SELECT grp2vak_id, ".
		"IF (vak.afkorting IS NULL, grp.naam, KB_LGRP(grp.naam, vak.afkorting)) ".
		"FROM doc2grp2vak ".
		"JOIN grp2vak USING (grp2vak_id) ".
		"JOIN grp USING (grp_id) ".
		"LEFT JOIN vak USING (vak_id) ".
		"WHERE grp.schooljaar = '$schooljaar' ".
		"AND doc2grp2vak.ppl_id = '${_SESSION['ppl_id']}' ".
		"ORDER BY grp.naam;");
}

function sprint_leerling_select($default, $autosubmit, &$ret, $allow_empty, $grp2vak) {
	return sprint_select($default, $autosubmit, &$ret, $allow_empty,
		"SELECT ppl.ppl_id lln, ".
		"CONCAT(KB_NAAM(naam0, naam1, naam2), ' (', login, ')') naam ".
		"FROM ppl ".
		"JOIN ppl2grp USING (ppl_id) ".
		"JOIN grp2vak USING (grp_id) ".
		"JOIN doc2grp2vak USING (grp2vak_id) ".
		"WHERE grp2vak_id = '%s' ".
		"AND doc2grp2vak.ppl_id = ${_SESSION['ppl_id']} ".
		"ORDER BY ppl.naam0, ppl.naam1, ppl.naam2",
		mysql_escape_safe($grp2vak));
}

?>
