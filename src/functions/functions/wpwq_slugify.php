<?php
/*
	grunt.concat_in_order.declare('wpwq_slugify');
	grunt.concat_in_order.require('init');

*/

function wpwq_slugify($text){
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);		// replace non letter or digits by -
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);	// transliterate
	$text = preg_replace('~[^-\w]+~', '', $text);		 // remove unwanted characters
	$text = trim($text, '-');		// trim
	$text = preg_replace('~-+~', '-', $text);		// remove duplicate -
	$text = strtolower($text);		// lowercase
	
	if (empty($text)) {
		return 'n-a';
	}
	
	return $text;
}

?>