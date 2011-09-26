<?
function bbtohtml($t) {
	$pattern['url'] = '/\[url\=(.+?)\](.+?)\[\/url\]/is';
	$replacement['url'] = '<a href="$1">$2</a>';
	$pattern['lf'] = '/\r\n/is';
	$replacement['lf'] = '<br>';
	return preg_replace($pattern, $replacement, $t);
}

function htmltobb($t) {
	$pattern['lf'] = '/<br>/is';
	$replacement['lf'] = "\r\n";
	$pattern['url'] = '/<a href="(.+?)">(.+?)<\/a>/is';
	$replacement['url'] = '[url=$1]$2[/url]';
	return preg_replace($pattern, $replacement, $t);
}
?>
