<?php
// #################################################################################
// #################################################################################
// Voseq includes/header.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Builds the page header and dojo and jscript options
// #################################################################################
#setting character set in HTTP headers
header('Content-type: text/html; charset=utf8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>
	<?php 
		if( isset($title) ) {
			echo "$title"; 
		}
		else {
			echo "$config_sitename";
		}
	?>
	</title>
	
	<?php
	echo "<link rel=\"stylesheet\" href=\"";

	if( isset($admin) && $admin != false ) {
		echo $base_url . "/admin/" . $currentTemplate . "/css/1.css\" type=\"text/css\" />"; //"/admin/" 
	}
	elseif( isset($in_includes) && $in_includes != false ) {
		echo $base_url . "/" . $currentTemplate . "/css/1.css\" type=\"text/css\" />";
	}
	else {
		echo $currentTemplate . "/css/1.css\" type=\"text/css\" />";
	}

	if( isset($loginmodule) && $loginmodule != false ) {
		echo "\n<link rel=\"stylesheet\" href=\"" . $base_url . "/login/loginmodule.css\" rel=\"stylesheet\" type=\"text/css\" />";
	}

	if (isset($yahoo_map) && $yahoo_map != false) {
		echo "\n<script src=\"http://api.maps.yahoo.com/ajaxymap?v=3.7&appid=" . $yahoo_key . "\">";
		echo "</script>";
	}
	echo "\n\n<script type=\"text/javascript\" src=\"" . $base_url . "/includes/jquery.js\"></script>\n";

	?>
	
	<link rel="SHORTCUT ICON" href="<?php echo $base_url . "/favicon.ico"; ?>" />
	<meta content="Gvim" name="GENERATOR" />
	<meta content="Carlos Pe&ntilde;a" name="author" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<?php 
if( isset($dojo) && $dojo == true ) {
	echo "<script type=\"text/javascript\" src=\"";
	if ($admin) {
		echo "../";
		}
	echo "dojo/dojo.js\"></script>\n<script type=\"text/javascript\">\n";
	echo "\tdojo.require(\"dojo.widget.*\");\n"; // load code relating to widget managing fuctions
	foreach ($whichDojo as $value) {
		echo "\tdojo.require(\"dojo.widget." . $value . "\");\n"; // put_dojo();
		}
	echo "</script>\n";
}

// redirect function for masking urls
echo "
<script type=\"text/javascript\">
	function redirect(URL) {
		document.location=URL;
		return false;
	}
</script>\n";
?>
</head>
<body>
