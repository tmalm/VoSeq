<?php
// #################################################################################
// #################################################################################
// Voseq admin/proccessPicture.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: process picture uploaded by user to be sent to Flickr
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check admin login session
include'../login/auth-admin.php';
//include
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes



if( trim($flickr_api_token) == "" ) {
	// redirect user to page for obtaining flickr_api_token
	header("location: http://nymphalidae.utu.fi/cpena/VoSeq");
	exit(0);
}


#include'../login/redirect.html';

include '../functions.php';
include 'adfunctions.php'; // administrator functions
include 'admarkup-functions.php';

// to indicate this is an administrator page
$admin = true;

// print html headers
include_once'../includes/header.php';

// print navegation bar
admin_nav();

// begin HTML page content
echo "<div id=\"content_narrow\">";
echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
		<tr><td valign=\"top\">";
				
$code = $_GET['code'];
?>
				Do you want to:
				<ol>
				
				<li>Upload a picture for record of code <b><?php echo "$code"; ?></b>:
				<!-- 		upload file -->
				<table>
				<tr><td>
				<form enctype="multipart/form-data" action="processfile.php?code=<?php echo "$code"; ?>" method="POST">
    			<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
    			<input name="userfile" type="file" size="40" /><br />
    			<input type="Submit" name="submit" value="Upload" />
				</form>
				</td>
				</tr>
				</table></li>
				<li>Enter sequences for record of code <b><?php echo "$code"; ?></b>: <?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('listseq.php?code=<?php echo "$code"; ?>');">Add Sequences</a></li>
				<li><?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('admin.php');">Go back to the main menu</a>.</li>
				</ol>

</form>

</td>

	<?php
	echo "<td class=\"sidebar\" valign=\"top\">";
	admin_make_sidebar(); // includes td and /td already
	echo "</td>";
	echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
	make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
	?>

</body>
</html>
