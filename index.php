<?php
// #################################################################################
// #################################################################################
// Voseq index.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Index (home) page for "normal" user interface
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
error_reporting(E_ALL); // ^ E_NOTICE);

//check if conf.php exists. If not, this is a fresh download and needs installation
if( !file_exists("conf.php") ) {
	header("Location: installation/NoConfFile.php" );
	exit(0);
}

//includes
ob_start();//Hook output buffer - disallows web printing of file info...
include 'conf.php';
if( $mask_url == "true" ) {
	ob_end_clean();//Clear output buffer
}
else {
	ob_clean();
}

//check login session
include 'login/auth.php';
include 'functions.php';
include 'markup-functions.php';

//update comboboxes
update_comboboxes();

$in_includes = false;

// admin?
$admin = false;

// need yahoo?
$yahoo_map = false;

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = "Tooltip";

// process title
$title = $config_sitename;

// print html headers
include_once 'includes/header.php';

// #################################################################################
// Section: Writing index page
// #################################################################################

// print navegation bar
nav();

// header: send bugs to me message
standardHeader($title, $intro_msg);

// begin HTML page content
echo "<div id=\"content_narrow\">";


// welcome_message();



// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database; <b>You might need to configure the file "conf.php"</b>');

if( function_exists(mysql_set_charset)) {
	mysql_set_charset("utf8");
}

// generate and execute query
$query = "SELECT id, code, genus, species, extractor, latesteditor, voucherImage, timestamp
          FROM " . $p_ . "vouchers
			 ORDER BY timestamp
			 DESC LIMIT 0, 9";
	
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
// if records present
if (mysql_num_rows($result) > 0) {
	// iterate through result set
	// print article titles
	echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
			<tr><td valign=\"top\" width=\"500\">
			
			<h1>Last entries:</h1>
			<ul>";
	$dojo_i = 1; // count for tooltip of dojo
	while ($row = mysql_fetch_object($result)) {
		echo "<li><b>";

		// masking URLs, this variable is set to "true" or "false" in conf.php file
		if( $mask_url == "true" ) {
			echo "<a href=\"home.php\" onclick=\"return redirect('story.php?code=$row->code')\">$row->code</a>";
		}
		else {
			echo "<a href=\"story.php?code=$row->code\">$row->code</a>";
		}

		?>
		 </b> <i><?php echo $row->genus; echo ' ' . $row->species; ?></i>
		<?php 
		if ($row->voucherImage != 'na.gif') { 
			echo "<a href=\"" . $row->voucherImage . "\">
		 		  <img width=\"16px\" height=\"16px\" id=\"see_pic" . $dojo_i . "\" class=\"link\" src=\"images/image.png\" /></a>"; 
			echo "<span dojoType=\"tooltip\" connectId=\"see_pic" . $dojo_i . "\" delay=\"1\" toggle=\"explode\">See photo</span>";
			}
		?>
		<br />
		By <?php 
			if( $row->latesteditor ) {
				echo $row->latesteditor;
			}
			else {
				echo "Administrator";
			}
			echo ' on '; echo formatDate($row -> timestamp, $date_timezone, $php_version); ?></li>
			
		<?php
		$dojo_i++;
		}
	}
	
// if no records present
// display message
else
	{
	echo "<font size=\"-1\">No records currently available</font>";
	}
	
// close database connection
mysql_close($connection);
?>
</ul>
</td>

<td class="sidebar" valign="top">
	<?php
		make_sidebar(); 
	?>
</td>

</tr>
</table>

</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url);
?>

</body>
</html>
