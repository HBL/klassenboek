<? function status() {
	if (isset($_SESSION['successmsg'])) { ?>
<div id="successmsg"><? echo($_SESSION['successmsg']) ?></div>
<?		unset($_SESSION['successmsg']);
	}
	if (isset($_SESSION['errormsg'])) { ?>
<div id="errormsg"><? echo($_SESSION['errormsg']) ?></div>
<?		unset($_SESSION['errormsg']);
	}
	if (isset($_SESSION['warningmsg'])) { ?>
<div id="warningmsg"><? echo($_SESSION['warningmsg']) ?></div>
<?		unset($_SESSION['warningmsg']);
	}
}

function sprint_hidden() {
	foreach ($_POST as $key => $value)
		$ret .= '<input type="hidden" name="'.$key.'" value="'.$value.'">'."\n";
	return $ret;
}

function status_success($actie) {
	if (!isset($_POST['undo']))
		return '<form method="POST" action="'.$_SERVER['SCRIPT_NAME'].'">'.
			$actie.'.'.sprint_hidden().
			'<input type="submit" name="undo" '.
			'value="Ongedaan maken"></form>';
	else return $actie.'.';
} ?>
