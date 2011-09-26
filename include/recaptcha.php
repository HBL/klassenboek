<? require_once('recaptchalib.php');
function recaptcha_ask() {
	global $recaptcha_public;
	if (!$recaptcha_public) return;
	?><script>var RecaptchaOptions = { lang: 'nl' };</script><?
	echo recaptcha_get_html($recaptcha_public, NULL, TRUE);
}

function recaptcha_verify() {
	global $recaptcha_private;
	if (!$recaptcha_private) return 1;
	$resp = recaptcha_check_answer($recaptcha_private, $_SERVER["REMOTE_ADDR"],
		$_POST["recaptcha_challenge_field"],
		$_POST["recaptcha_response_field"]);
	unset($_POST['recaptcha_challenge_field']);
	unset($_POST['recaptcha_response_field']);
	return $resp->is_valid;
}
?>
