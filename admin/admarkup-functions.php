<?php
// #################################################################################
// #################################################################################
// Voseq admin/admarkup-functions.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: markup functions for the administrator interface
//
// #################################################################################



//include redirect-URL script
#include '../login/redirect.html';


// #################################################################################
// Section: top bar with HOME SEARCH ADMIN links
// #################################################################################
function admin_nav() {
	ob_start();//Hook output buffer - disallows web printing of file info...
	include'../conf.php';
	ob_end_clean();//Clear output buffer//includes

	echo "<div id=\"menu\">";
	if ($mask_url == "true") {
		echo "<a href='" . $base_url . "/home.php'  onclick=\"return redirect('" . $base_url . "/index.php');\" title='This link takes you back to the homepage' >home</a>";
		echo "<a href='" . $base_url . "/home.php'  onclick=\"return redirect('" . $base_url . "/admin/adsearch.php');\" title='Go to Search page' >search</a>";
		echo "<a href='" . $base_url . "/home.php'  onclick=\"return redirect('" . $base_url . "/admin/admin.php');\" title='Go to Administration page' >admin</a>";
		echo "</div>\n\n";
	}
	else {
		echo "<a href='" . $base_url . "/index.php' title='This link takes you back to the homepage' >home</a>";
		echo "<a href='" . $base_url . "/admin/adsearch.php' title='Go to Search page' >search</a>";
		echo "<a href='" . $base_url . "/admin/admin.php' title='Go to Administration page' >admin</a>";
		echo "</div>\n\n";
	}
}



// #################################################################################
// Section: intro message for intro page
// #################################################################################
function admin_standardHeader($title, $intro_msg) {
echo "<!--standard page header begins-->
		<div id=\"header\">
			<h1>". $title . "</h1>
	   	" . $intro_msg. "
			
			<p class=\"introduction_admin\">
			<b>1.</b> This is the <b>administrator</b> interface of this database, which means you can add, update and delete records.<br />
			<b>2.</b> If you are looking for sequences and want to use the database as <i>mortal user</i> click the <b>\"home\"</b></p>
		</div>
		<!-- standard page header ends -->";
}


// #################################################################################
// Section: sidebar with logo and "sponsor" list
// #################################################################################
function admin_make_sidebar() {
echo "<img src=\"images/logo-small.jpg\" alt=\"VoSeq database\" class=\"logo\" />

		 <h1>Powered by:</h1>
		 <div class=\"submenu\">
			<a href=\"http://httpd.apache.org\"><img src=\"images/apache.png\" alt=\"Apache\" class=\"link\" /></a>
			<a href=\"http://www.php.net\"><img src=\"images/php.png\" alt=\"PHP\" class=\"link\" /></a>
			<a href=\"http://www.mysql.com\"><img src=\"images/mysql.png\" alt=\"MySQL\" class=\"link\" /></a>
			<a href=\"http://www.ubuntu.com\"><img src=\"images/ubuntu.png\" alt=\"Ubuntu\" class=\"link\"></a>
			<a href=\"http://dojotoolkit.org\"><img src=\"images/dojo.png\" alt=\"Dojo toolkit\" class=\"link\"></a>
		 </div>";
}



// #################################################################################
// Section: footer with some statistics and closing html page
// #################################################################################
function make_footer($date_timezone, $config_sitename, $version, $base_url, $p_) {
		ob_start();//Hook output buffer - disallows web printing of file info...
		include'../conf.php';
		ob_end_clean();//Clear output buffer//includes
		
		//fixing some output variables
			// open database connection
			@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
			//select database
			mysql_select_db($db) or die ('Unable to content');
			if( function_exists(mysql_set_charset) ) {
				mysql_set_charset("utf8");
			}
			$num_rows_vouchers = array();
			$query = "SELECT * FROM ". $p_ . "vouchers";
			$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
			$num_rows_vouchers['all'] = mysql_num_rows($result);
			$count_array = array("orden", "family","genus", "genus, species");
			foreach ($count_array as $count){
				$query = "SELECT $count FROM ". $p_ . "vouchers GROUP BY $count";
				$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
				$num_rows_vouchers[$count] = mysql_num_rows($result);
			}
			$query = "SELECT * FROM ". $p_ . "sequences";
			$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
			$num_rows_sequences = mysql_num_rows($result);
	if( $php_version == "5" ) {
		date_default_timezone_set($date_timezone); //php5
	}
	
	//output - yay!
	echo "<!-- standard page footer begins -->\n<div id=\"footer_admin\">" . date('Y') . ' ' . $config_sitename;
	
	if( $mask_url == "true" ) {
		echo "\n <a href=\"" . $base_url . "/home.php\" onclick=\"return redirect('" . $base_url . "/changelog.txt');\" title='check verion history' >version " . $version . "</a>";
		echo " \n Logged in as: " . $_SESSION['SESS_FIRST_NAME'] ." ". $_SESSION['SESS_LAST_NAME'] . "\n <a href='
		" . $base_url . "/home.php' onclick=\"return redirect('". $base_url . "/login/logout.php');\">logout </a>";
	}
	else {
		echo "\n <a href=\"" . $base_url . "/changelog.txt\" title='check verion history' >version " . $version . "</a>";
		echo " \n Logged in as: " . $_SESSION['SESS_FIRST_NAME'] ." ". $_SESSION['SESS_LAST_NAME'] . "\n <a href='" . $base_url . "/login/logout.php'>logout </a>";
	}

	echo "<br />Now with ". $num_rows_vouchers['all'] ." vouchers, over ".$num_rows_vouchers['orden']." orders, 
	".$num_rows_vouchers['family']." families, ".$num_rows_vouchers['genus']." genera and ".$num_rows_vouchers["genus, species"]." species,
	with together ". $num_rows_sequences ." sequences! </br>\n<img src=\"images/colofon_xhtml.png\" alt=\"Valid XHTML\" title=\"Valid XHTML\" />
	<img src=\"images/colofon_css.png\" alt=\"Valid CSS\" title=\"Valid CSS\" />\n</div>";
}

?>
