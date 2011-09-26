<? function dom_check_elt($elt, $tag) {
	if ($elt->nodeType != XML_ELEMENT_NODE || $elt->tagName != $tag)
		throw new Exception('dom_check_elt() wrong type', 2);
}

function dom_form_select_option($option) {
	dom_check_elt($option, 'option');

	if ($option->getAttribute('value')) return urlencode($option->getAttribute('value'));
	return trim($option->nodeValue);
}

function dom_form_select($select, $xpath) {
	dom_check_elt($select, 'select');

	$options = $xpath->query('.//option[@selected]', $select);

	if ($options->length == 0) {
		$options = $xpath->query('.//option', $select);

		if ($options->length == 0) return '';

		return urlencode($select->getAttribute('name')).'='.dom_form_select_option($options->item(0)).'&';
	}

	if (!$select->getAttribute('multiple')) {
		return urlencode($select->getAttribute('name')).'='.dom_form_select_option($options->item(0)).'&';
	}

	foreach ($options as $option) {
		$ret .=  urlencode($select->getAttribute('name')).'='.dom_form_select_option($option).'&';
	}

	return $ret;
}

function dom_form_find_checkbox($form, $xpath, $name, $value) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($checkbox = $xpath->query('.//input[@name="'.$name.'" and @type="checkbox" and not(@disabled) and @value="'.$value.'"]', $form)->item(0)))
		regular_error($http_path.'/', (array) NULL, 'form heeft geen checkbox met name="'.$name.'" en value="'.$value.'"');

	return $checkbox;
}

function dom_form_find_checkboxen($form, $xpath, $name) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($checkboxen = $xpath->query('.//input[@name="'.$name.'" and @type="checkbox" and not(@disabled)]', $form)))
		regular_error($http_path.'/', (array) NULL, 'form heeft geen checkboxen met name="'.$name.'"');

	return $checkboxen;
}

function dom_form_check_checkbox($form, $xpath, $name, $value) {
	dom_form_find_checkbox($form, $xpath, $name, $value)->setAttribute('checked', 'checked');
}

function dom_form_uncheck_checkbox($form, $xpath, $name, $value) {
	dom_form_find_checkbox($form, $xpath, $name, $value)->removeAttribute('checked');
}

function dom_form_check_checkboxen($form, $xpath, $name) {
	foreach (dom_form_find_checkboxen($form, $xpath, $name) as $checkbox) $checkbox->setAttribute('checked', 'checked');
}

function dom_form_uncheck_checkboxen($form, $xpath, $name) {
	foreach (dom_form_find_checkboxen($form, $xpath, $name) as $checkbox) $checkbox->removeAttribute('checked');
}

function dom_form_set_textarea($form, $xpath, $name, $value) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($textarea = $xpath->query('.//textarea[@name="'.$name.'" and not(@disabled)]', $form)->item(0)))
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;input name="'.$name.'" ...&gt;');

	while ($textarea->firstChild) $textarea->removeChild($textarea->firstChild);

	$textarea->appendChild(new DOMText($value));
}

function dom_form_enable_inputs($form, $xpath, $name) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($inputs = $xpath->query('.//input[@name="'.$name.'" and @disabled]', $form)))
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;input name="'.$name.'" ...&gt;');

	foreach ($inputs as $input) $input->removeAttribute('disabled');
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;input name="'.$name.'" ...&gt;');
}

function dom_form_select_select_option($form, $xpath, $name, $opt) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($select = $xpath->query('.//select[@name="'.$name.'" and not (@disabled)]', $form)->item(0)))
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;select name="'.$name.'" ...&gt;');

	if (!$select->getAttribute('multiple'))
		foreach ($xpath->query('.//option[@selected]', $select) as $option)
			$option->removeAttribute('selected');

	$options = $xpath->query('.//option', $select);

	foreach ($options as $option) {
		if (trim($option->nodeValue) == $opt) {
			$option->setAttribute('selected', 'selected');
			if (!$select->getAttribute('multiple')) break;
		}
	}
}

function dom_form_unselect_select_option($form, $xpath, $name, $opt) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($select = $xpath->query('.//select[@name"'.$name.'" and not (@disabled)]', $form)->item(0)))
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;select name="'.$name.'" ...&gt;');

	$options = $xpath->query('.//option', $select);

	foreach ($options as $option) {
		if (trim($option->nodeValue) == $opt) {
			$option->removeAttribute('selected');
		}
	}
}

function dom_form_disable_inputs($form, $xpath, $name) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($inputs = $xpath->query('.//input[@name="'.$name.'" and not(@disabled)]', $form)))
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;input name="'.$name.'" ...&gt;');

	foreach ($inputs as $input) $input->setAttribute('disabled', 'disabled');
}

function dom_form_enable_selects($form, $xpath, $name) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($selects = $xpath->query('.//select[@name="'.$name.'" and @disabled]', $form)))
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;select name="'.$name.'" ...&gt;');

	foreach ($selects as $select) $select->removeAttribute('disabled');
}

function dom_form_disable_selects($form, $xpath, $name) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($selects = $xpath->query('.//select[@name="'.$name.'" and not(@disabled)]', $form)))
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;select name="'.$name.'" ...&gt;');

	foreach ($selects as $select) $select->setAttribute('disabled', 'disabled');
}

function dom_form_set_input($form, $xpath, $name, $value) {
	global $http_path;
	dom_check_elt($form, 'form');

	if (!($input = $xpath->query('.//input[@name="'.$name.'" and not(@disabled)]', $form)))
	       	regular_error($http_path.'/', (array) NULL, 'form heeft geen &lt;input name="'.$name.'" ...$gt;');

        $input->item(0)->setAttribute('value', $value);
}

function dom_form($form, $xpath) {
	dom_check_elt($form, 'form');

	$dingies = $xpath->query(<<<EOT
(.//input[not(@type) or ((@type = "checkbox" or @type = "radio") and @checked) or (@type != "radio" and @type != "checkbox")]|.//textarea|.//select)[not(@disabled) and @name and @name != ""]
EOT
, $form);
	foreach ($dingies as $elt) {
		switch ($elt->tagName) {
			case 'input':
				$ret .= urlencode($elt->getAttribute('name')).'='.urlencode($elt->getAttribute('value')).'&';
				break;
			case 'textarea':
				$ret .= urlencode($elt->getAttribute('name')).'='.urlencode($elt->nodeValue).'&';
				break;
			case 'select':
				$ret .= dom_form_select($elt, $xpath);
				break;
		}
	}
	$ret = rtrim($ret, '&');
	return $ret;
}
?>
