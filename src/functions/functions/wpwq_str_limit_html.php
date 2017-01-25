<?php
/*
	grunt.concat_in_order.declare('wpwq_str_limit_html');
	grunt.concat_in_order.require('init');
*/

/**
 * Limit string without break html tags.
 * Supports UTF8
 * 
 * http://stackoverflow.com/questions/2398725/using-php-substr-and-strip-tags-while-retaining-formatting-and-without-break
 * 
 * @param string $value
 * @param int $limit Default 100
 */
function wpwq_str_limit_html($value, $limit = 100, $append = '')
{
	if ( strlen( $value ) == 0 ) {
		return $value;
	}
    if (mb_strwidth($value, 'UTF-8') <= $limit) {
        return $value;
    }

    // Strip text with HTML tags, sum html len tags too.
    // Is there another way to do it?
    do {
        $len          = mb_strwidth($value, 'UTF-8');
        $len_stripped = mb_strwidth(strip_tags($value), 'UTF-8');
        $len_tags     = $len - $len_stripped;

        $value = mb_strimwidth($value, 0, $limit + $len_tags, '', 'UTF-8');
    } while ($len_stripped > $limit);
    
    $value .= strlen( $append ) > 0 ? $append : '';
     
    // Load as HTML ignoring errors
    $dom = new DOMDocument();
    
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$value, LIBXML_HTML_NODEFDTD);

    // Fix the html errors
    $value = $dom->saveHtml($dom->getElementsByTagName('body')->item(0));

    // Remove body tag
    $value = mb_strimwidth($value, 6, mb_strwidth($value, 'UTF-8') - 13, '', 'UTF-8'); // <body> and </body>
    // Remove empty tags
    $value = preg_replace('/<(\w+)\b(?:\s+[\w\-.:]+(?:\s*=\s*(?:"[^"]*"|"[^"]*"|[\w\-.:]+))?)*\s*\/?>\s*<\/\1\s*>/', '', $value);
   
    return $value;
}
?>