<?php
function clean_string($string) {
	$i = 0;
	if( (isset($string) && trim($string) != '') ) {
		$user_strings = array();
		$symbols = array(",",'"',"'","&","/","\\",";","=");
		#is number? then dont filter by symbols
		if( is_numeric($string) ) {
			array_push($user_strings, $string);
		}
		else { #not number, then clean by filtering symbols
			$id_subject = trim(str_replace($symbols, "", $string));
			$subject = explode(" ", $id_subject);
			foreach( $subject as $val ) {
				if( trim($val) != "" ) {
					$pattern = '/[a-öA-Ö0-9_\.\-]+/';
					preg_match($pattern, $val, $match);
					if( $i < 3 ) {
						array_push($user_strings, $match[0]);
					}
					$i++;
				}
			}
		}
		return $user_strings;
	}
}


function getParam( $arr, $name, $def=null ) {
	$return = null;

	# This is a Windows Server
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		if( isset( $arr[$name] ) ) {
			$arr[$name] = trim( $arr[$name] );
			$arr[$name] = strip_tags( $arr[$name] );
			return $arr[$name];
		}
		else {
			return $def;
		}
	}
	else {
		if (isset( $arr[$name] )) {
			if (is_string( $arr[$name] )) {
				$arr[$name] = trim( $arr[$name] );
				$arr[$name] = strip_tags( $arr[$name] );
				$arr[$name] = str_replace( ' ', '', $arr[$name] );
				}
			if (!get_magic_quotes_gpc()) {
				$arr[$name] = addslashes( $arr[$name] );
				}
			return $arr[$name];
			}
		else {
			return $def;
			}
	}
}

?>
