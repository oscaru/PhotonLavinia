<?php

namespace photonLavinia ;


if(!defined('PhotonLavinia_fronturl')){
    define('PhotonLavinia_fronturl', "http://{$_SERVER['HTTP_HOST']}");
}


class PhotonLavinia {
    protected static $enabled = true;
    
    
    static function add_script ( $content ) {
        $loader = '<script type="application/javascript">
                        function loadJS(file) {
                            // DOM: Create the script element
                            var jsElm = document.createElement("script");
                            // set the type attribute
                           jsElm.type = "application/javascript";
                           // make the script element load file
                           jsElm.src = file;
                            // finally insert the element to the body element in order to load the script
                            document.body.appendChild(jsElm);
                        }
                    if(typeof jQuery == "undefined") loadJS("'.PhotonLavinia_fronturl.'/js/jquery.js");
                    loadJS("'.PhotonLavinia_fronturl.'/js/photonlavinia.js");
                    </script>   ';
        
        $content = str_replace('</body>', "{$loader} </body>", $content);
        return $content;
        
    }
    
    
    
    // Marcadores de posición
    static function add_image_placeholders( $content ) {
        if ( ! self::is_enabled() )
			return $content;
        
        if ( false !== strpos( $content, 'data-lazy-src' ) )
			return $content;
       
        // This is a pretty simple regex, but it works
		$content = preg_replace_callback( '#<(img)([^>]+?)(>(.*?)</\\1>|[\/]?>)#si', array( __CLASS__, 'process_image' ), $content );

        return $content;
    }
    
    
    static function process_image( $matches ) {
		// In case you want to change the placeholder image
		$placeholder_image =  self::get_url( 'images/1x1.trans.gif' ) ;

        $allowedProtocols = wp_allowed_protocols();
		$old_attributes_str = $matches[2];
		$old_attributes = wp_kses_hair( $old_attributes_str,$allowedProtocols);

		if ( empty( $old_attributes['src'] ) ) {
			return $matches[0];
		}

		$image_src = $old_attributes['src']['value'];

		// Remove src and lazy-src since we manually add them
		$new_attributes = $old_attributes;
		unset( $new_attributes['src'], $new_attributes['data-lazy-src'] );

		$new_attributes_str = self::build_attributes_string( $new_attributes );

		return sprintf( '<img src="%1$s" data-lazy-src="%2$s" %3$s><noscript>%4$s</noscript>', esc_url( $placeholder_image ), esc_url( $image_src ), $new_attributes_str, $matches[0] );
	}
    
    private function get_url($file){
       return PhotonLavinia_fronturl."/{$file}";
    }
    
    private static function build_attributes_string( $attributes ) {
		$string = array();
		foreach ( $attributes as $name => $attribute ) {
			$value = $attribute['value'];
			if ( '' === $value ) {
				$string[] = sprintf( '%s', $name );
			} else {
				$string[] = sprintf( '%s="%s"', $name, esc_attr( $value ) );
			}
		}
		return implode( ' ', $string );
	}
    
    static function is_enabled() {
		return self::$enabled;
	}
    
    
    
/**
 * Builds an attribute list from string containing attributes.
 *
 * This function does a lot of work. It parses an attribute list into an array
 * with attribute data, and tries to do the right thing even if it gets weird
 * input. It will add quotes around attribute values that don't have any quotes
 * or apostrophes around them, to make it easier to produce HTML code that will
 * conform to W3C's HTML specification. It will also remove bad URL protocols
 * from attribute values. It also reduces duplicate attributes by using the
 * attribute defined first (foo='bar' foo='baz' will result in foo='bar').
 *
 * @since 1.0.0
 *
 * @param string $attr Attribute list from HTML element to closing HTML element tag
 * @param array $allowed_protocols Allowed protocols to keep
 * @return array List of attributes after parsing
 */




}

function wp_kses_hair($attr, $allowed_protocols) {
	$attrarr = array();
	$mode = 0;
	$attrname = '';
	$uris = array('xmlns', 'profile', 'href', 'src', 'cite', 'classid', 'codebase', 'data', 'usemap', 'longdesc', 'action');

	# Loop through the whole attribute list

	while (strlen($attr) != 0) {
		$working = 0; # Was the last operation successful?

		switch ($mode) {
			case 0 : # attribute name, href for instance

				if ( preg_match('/^([-a-zA-Z:]+)/', $attr, $match ) ) {
					$attrname = $match[1];
					$working = $mode = 1;
					$attr = preg_replace( '/^[-a-zA-Z:]+/', '', $attr );
				}

				break;

			case 1 : # equals sign or valueless ("selected")

				if (preg_match('/^\s*=\s*/', $attr)) # equals sign
					{
					$working = 1;
					$mode = 2;
					$attr = preg_replace('/^\s*=\s*/', '', $attr);
					break;
				}

				if (preg_match('/^\s+/', $attr)) # valueless
					{
					$working = 1;
					$mode = 0;
					if(false === array_key_exists($attrname, $attrarr)) {
						$attrarr[$attrname] = array ('name' => $attrname, 'value' => '', 'whole' => $attrname, 'vless' => 'y');
					}
					$attr = preg_replace('/^\s+/', '', $attr);
				}

				break;

			case 2 : # attribute value, a URL after href= for instance

				if (preg_match('%^"([^"]*)"(\s+|/?$)%', $attr, $match))
					# "value"
					{
					$thisval = $match[1];
					if ( in_array(strtolower($attrname), $uris) )
						$thisval = wp_kses_bad_protocol($thisval, $allowed_protocols);

					if(false === array_key_exists($attrname, $attrarr)) {
						$attrarr[$attrname] = array ('name' => $attrname, 'value' => $thisval, 'whole' => "$attrname=\"$thisval\"", 'vless' => 'n');
					}
					$working = 1;
					$mode = 0;
					$attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
					break;
				}

				if (preg_match("%^'([^']*)'(\s+|/?$)%", $attr, $match))
					# 'value'
					{
					$thisval = $match[1];
					if ( in_array(strtolower($attrname), $uris) )
						$thisval = wp_kses_bad_protocol($thisval, $allowed_protocols);

					if(false === array_key_exists($attrname, $attrarr)) {
						$attrarr[$attrname] = array ('name' => $attrname, 'value' => $thisval, 'whole' => "$attrname='$thisval'", 'vless' => 'n');
					}
					$working = 1;
					$mode = 0;
					$attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
					break;
				}

				if (preg_match("%^([^\s\"']+)(\s+|/?$)%", $attr, $match))
					# value
					{
					$thisval = $match[1];
					if ( in_array(strtolower($attrname), $uris) )
						$thisval = wp_kses_bad_protocol($thisval, $allowed_protocols);

					if(false === array_key_exists($attrname, $attrarr)) {
						$attrarr[$attrname] = array ('name' => $attrname, 'value' => $thisval, 'whole' => "$attrname=\"$thisval\"", 'vless' => 'n');
					}
					# We add quotes to conform to W3C's HTML spec.
					$working = 1;
					$mode = 0;
					$attr = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
				}

				break;
		} # switch

		if ($working == 0) # not well formed, remove and try again
		{
			$attr = wp_kses_html_error($attr);
			$mode = 0;
		}
	} # while

	if ($mode == 1 && false === array_key_exists($attrname, $attrarr))
		# special case, for when the attribute list ends with a valueless
		# attribute like "selected"
		$attrarr[$attrname] = array ('name' => $attrname, 'value' => '', 'whole' => $attrname, 'vless' => 'y');

	return $attrarr;
}


function wp_kses_html_error($string) {
	return preg_replace('/^("[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*/', '', $string);
}


function wp_kses_bad_protocol($string, $allowed_protocols) {
	$string = wp_kses_no_null($string);
	$iterations = 0;

	do {
		$original_string = $string;
		$string = wp_kses_bad_protocol_once($string, $allowed_protocols);
	} while ( $original_string != $string && ++$iterations < 6 );

	if ( $original_string != $string )
		return '';

	return $string;
}

function wp_kses_no_null($string) {
	$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string);
	$string = preg_replace('/(\\\\0)+/', '', $string);

	return $string;
}

function wp_kses_bad_protocol_once($string, $allowed_protocols, $count = 1 ) {
	$string2 = preg_split( '/:|&#0*58;|&#x0*3a;/i', $string, 2 );
	if ( isset($string2[1]) && ! preg_match('%/\?%', $string2[0]) ) {
		$string = trim( $string2[1] );
		$protocol = wp_kses_bad_protocol_once2( $string2[0], $allowed_protocols );
		if ( 'feed:' == $protocol ) {
			if ( $count > 2 )
				return '';
			$string = wp_kses_bad_protocol_once( $string, $allowed_protocols, ++$count );
			if ( empty( $string ) )
				return $string;
		}
		$string = $protocol . $string;
	}

	return $string;
}

function wp_kses_bad_protocol_once2( $string, $allowed_protocols ) {
	$string2 = wp_kses_decode_entities($string);
	$string2 = preg_replace('/\s/', '', $string2);
	$string2 = wp_kses_no_null($string2);
	$string2 = strtolower($string2);

	$allowed = false;
	foreach ( (array) $allowed_protocols as $one_protocol )
		if ( strtolower($one_protocol) == $string2 ) {
			$allowed = true;
			break;
		}

	if ($allowed)
		return "$string2:";
	else
		return '';
}

function wp_kses_decode_entities($string) {
	$string = preg_replace_callback('/&#([0-9]+);/', '\photonLavinia\_wp_kses_decode_entities_chr', $string);
	$string = preg_replace_callback('/&#[Xx]([0-9A-Fa-f]+);/', '\photonLavinia\_wp_kses_decode_entities_chr_hexdec', $string);

	return $string;
}

function _wp_kses_decode_entities_chr( $match ) {
	return chr( $match[1] );
}

/**
 * Regex callback for wp_kses_decode_entities()
 *
 * @param array $match preg match
 * @return string
 */
function _wp_kses_decode_entities_chr_hexdec( $match ) {
	return chr( hexdec( $match[1] ) );
}


function esc_url( $url, $protocols = null, $_context = 'display' ) {
	$original_url = $url;

	if ( '' == $url )
		return $url;
	$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
	$strip = array('%0d', '%0a', '%0D', '%0A');
	$url = _deep_replace($strip, $url);
	$url = str_replace(';//', '://', $url);
	/* If the URL doesn't appear to contain a scheme, we
	 * presume it needs http:// appended (unless a relative
	 * link starting with /, # or ? or a php file).
	 */
	if ( strpos($url, ':') === false && ! in_array( $url[0], array( '/', '#', '?' ) ) &&
		! preg_match('/^[a-z0-9-]+?\.php/i', $url) )
		$url = 'http://' . $url;

	// Replace ampersands and single quotes only when displaying.
	if ( 'display' == $_context ) {
		$url = wp_kses_normalize_entities( $url );
		$url = str_replace( '&amp;', '&#038;', $url );
		$url = str_replace( "'", '&#039;', $url );
	}

	if ( '/' === $url[0] ) {
		$good_protocol_url = $url;
	} else {
		if ( ! is_array( $protocols ) )
			$protocols = wp_allowed_protocols();
		$good_protocol_url = wp_kses_bad_protocol( $url, $protocols );
		if ( strtolower( $good_protocol_url ) != strtolower( $url ) )
			return '';
	}

	/**
	 * Filter a string cleaned and escaped for output as a URL.
	 *
	 * @since 2.3.0
	 *
	 * @param string $good_protocol_url The cleaned URL to be returned.
	 * @param string $original_url      The URL prior to cleaning.
	 * @param string $_context          If 'display', replace ampersands and single quotes only.
	 */
	return $good_protocol_url;
}

function wp_allowed_protocols(){
    return array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp' );
};

function _deep_replace( $search, $subject ) {
	$subject = (string) $subject;

	$count = 1;
	while ( $count ) {
		$subject = str_replace( $search, '', $subject, $count );
	}

	return $subject;
}

function wp_kses_normalize_entities($string) {
	# Disarm all entities by converting & to &amp;

	$string = str_replace('&', '&amp;', $string);

	# Change back the allowed entities in our entity whitelist

	$string = preg_replace_callback('/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', '\photonLavinia\wp_kses_named_entities', $string);
	$string = preg_replace_callback('/&amp;#(0*[0-9]{1,7});/', '\photonLavinia\wp_kses_normalize_entities2', $string);
	$string = preg_replace_callback('/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', '\photonLavinia\wp_kses_normalize_entities3', $string);

	return $string;
}

function wp_kses_named_entities($matches) {
	global $allowedentitynames;

	if ( empty($matches[1]) )
		return '';

	$i = $matches[1];
	return ( ( ! in_array($i, $allowedentitynames) ) ? "&amp;$i;" : "&$i;" );
}

function wp_kses_normalize_entities2($matches) {
	if ( empty($matches[1]) )
		return '';

	$i = $matches[1];
	if (valid_unicode($i)) {
		$i = str_pad(ltrim($i,'0'), 3, '0', STR_PAD_LEFT);
		$i = "&#$i;";
	} else {
		$i = "&amp;#$i;";
	}

	return $i;
}

function wp_kses_normalize_entities3($matches) {
	if ( empty($matches[1]) )
		return '';

	$hexchars = $matches[1];
	return ( ( ! valid_unicode(hexdec($hexchars)) ) ? "&amp;#x$hexchars;" : '&#x'.ltrim($hexchars,'0').';' );
}