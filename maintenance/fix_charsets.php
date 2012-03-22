#!/usr/local/bin/php
<?php
ob_start();//Hook output buffer - disallows web printing of file info...
include 'conf.php';
ob_end_clean();//Clear output buffer//includes

$db = "noctuid_db";
// open database connections
$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database; <b>You might need to configure the file "conf.php"</b>');
mysql_query("set names latin1") or die("Error in query: $query. " . mysql_error());

// generate and execute query
$query = "SELECT id, code, collector FROM ". $p_ . "vouchers where length(collector) != char_length(collector)";
#$query = "SELECT code, collector, length(collector) as length, char_length(collector) as char_length FROM vouchers";
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
#echo "\nJL5-10\t->UTF-8";
#echo "\nJL5-13\t->latin1\n\n";
$exception_codes = array();


$problematic = array();
if (mysql_num_rows($result) > 0) {
	while($row = mysql_fetch_object($result)) {
		$string = $row->collector;
		$orig_encoding = mb_detect_encoding($string, "utf-8, latin1");
		$code = $row->code;

		if(!in_array($code, $exception_codes)) {
			if($orig_encoding == "UTF-8") {
				echo "<-your original string is in $code -> latin1, although here says $orig_encoding converting then\n";
				$converted = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $string);
				echo $string . "\n";
				echo $converted . "\n\n";
				$problematic[$code] = $converted;
			}
		}
	}
}

print_r($problematic);
print process_problem_to_mysql($problematic);

###!! dont forget to do a recode ISO-8859-1..UTF-8 myoutfile and then upload to MYSQL! otherwise you are uploading a latin1 file into UTF-8 database!


#process array containiing $code => $specificLocality string
function process_problem_to_mysql($array) {
	$output = "\n\nset names utf8;\n";
	foreach($array as $k => $v) {
		$v = trim($v);
		$output .= "update " . $p_ . "vouchers SET specificLocality=\"$v\" where code=\"$k\";\n";
	}
	return $output;
}
?>
