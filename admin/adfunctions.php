<?php
// #################################################################################
// #################################################################################
// Voseq admin/adfunctions.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: functions to be used in the administrator interface
//
// #################################################################################


// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes

#include '../login/redirect.html';



// #################################################################################
// format MySQL DATETIME value into a more readable string
// #################################################################################
function admin_formatDate($val, $date_timezone, $php_version) {
	if( $php_version == "5" ) {
		date_default_timezone_set($date_timezone); //php5
	}
	$arr = explode('-', $val);
	$day = explode(" ", $arr[2]);
	return date('d M Y', mktime(0,0,0, $arr[1], $day[0], $arr[0]));
}


// #################################################################################
// Get extraction value (from extraction sample)
// #################################################################################
function getExtraction($p_) {
	// open database connection
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}

	// generate and execute query
	$query = "SELECT DISTINCT extraction FROM ". $p_ ."vouchers";
	$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
	
}

if (!function_exists('stripos')) // for php4
	{
	function stripos($haystack, $needle){
		return strpos($haystack, stristr( $haystack, $needle ));
	}
}



// #################################################################################
// show results of creating a record
// #################################################################################
function addRecordResult($code) {
	echo "<span class=\"text\">Update sucessful.</span>";
	 ?>
			Do you want to:
				<ol>
				
				<li>Upload a picture for record of code <b><?php echo $code; ?></b>:
				<!-- 		upload file -->
				<table>
				<tr><td>
				<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    			<input type="hidden" name="MAX_FILE_SIZE" value="500000" />
    			<input name="userfile" type="file" /><br />
    			<input type="Submit" name="submit" value="Upload" />
				</form>
				</td>
				</tr>
				</table></li>
			
				<li>Enter sequences for record: <?php echo "<a href='" .$base_url . "/home.html'" ?> onclick="return redirect('addseq.php');">Add Sequences</a></li>
			
				<li><?php echo "<a href='" .$base_url . "/home.html'" ?> onclick="return redirect('admin.php');">Go back to the main menu</a>.</span></li>
			
				</ol>";
		 <?php
	chdir('../');
		$cwd = getcwd();
		$destination = "$cwd" . "/pictures/" . $_FILES['userfile']['name'];
		$temp_file = $_FILES['userfile']['tmp_name'];
		move_uploaded_file($temp_file, $destination);
		echo "File is valid and was sucessfuly uploaded.<br />
				{$_FILES['userfile']['name']}
				({$_FILES['userfile']['size']})</p>";
}
	
	 

// #################################################################################
// show lists of retrieved records in search, unordered list
// #################################################################################
function searchList($code, $genus, $species, $extractor, $latesteditor, $search_id, $timestamp, $date_timezone, $base_url, $php_version, $mask_url, $p_) {
	echo "<ul class=\"text\">
	 		<li><b>";
	if( $mask_url == "true" ) {
		echo "<a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('add.php?code=" . $code . "&amp;search=" . $search_id . "');\">";
	}
	else {
		echo "<a href=\"" . $base_url . "/admin/add.php?code=" . $code . "&amp;search=" . $search_id . "\">";
	}
	echo $code . "</a></b> <i>" . $genus . " " . $species . " </i>";
		
	//get info about picture
	$queryV  = "SELECT code, voucherImage FROM ". $p_ . "vouchers WHERE code='$code'";
	$resultV = mysql_query($queryV) or die("Error in query: $queryV. " . mysql_error());
	$rowV    = mysql_fetch_object($resultV);
	if ($rowV->voucherImage == 'na.gif') {
		if( $mask_url == "true" ) {
			echo "<a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('processPicture.php?code=" . $code . "');\">";
		}
		else {
			echo "<a href=\"" . $base_url . "/admin/processPicture.php?code=" . $code . "\">";
		}
		echo "Picture missing</a> <img src=\"images/warning.png\" alt=\"\" />";
		}
	else {
		if( $mask_url == "true" ) {
			echo "<a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('../pictures/" . $rowV->voucherImage . "');\">
			  <img class=\"link\" src=\"images/image.png\" alt=\"See photo\" title=\"See photo\" /></a>";
			echo "<a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('processPicture.php?code=" . $rowV->code . "');\">
			  <img class=\"link\" src=\"images/change_pic.png\" alt=\"Change photo\" title=\"Change photo\" /></a>";
		}
		else {
			echo "<a href=\"" . $base_url . "/../pictures/" . $rowV->voucherImage . "\">
			  <img class=\"link\" src=\"images/image.png\" alt=\"See photo\" title=\"See photo\" /></a>";
			echo "<a href=\"" . $base_url . "/admin/processPicture.php?code=" . $rowV->code . "\">
			  <img class=\"link\" src=\"images/change_pic.png\" alt=\"Change photo\" title=\"Change photo\" /></a>";
		}
	}
	echo "<ul><li>";
	
	//get list of geneCodes
	// query from sequences table
	$queryS  = "SELECT id, code, geneCode FROM ". $p_ . "sequences WHERE code='$code' ORDER BY geneCode ASC";
	$resultS = mysql_query($queryS) or die("Error in query: $queryS. " . mysql_error());
	$i = 0;
	while ($rowS = mysql_fetch_object($resultS)) {
		if( $mask_url == "true" ) {
			echo "<a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('listseq.php?code=" . $rowS->code . "&amp;geneCode=" . $rowS->geneCode . "&amp;id=" . $rowS->id . "');\">" . $rowS->geneCode . "</a>&nbsp;";
		}
		else {
			echo "<a href=\"" . $base_url . "/admin/listseq.php?code=" . $rowS->code . "&amp;geneCode=" . $rowS->geneCode . "&amp;id=" . $rowS->id . "\">" . $rowS->geneCode . "</a>&nbsp;";
		}
		# For vouchers with lots of sequences, add a line break after the 4th geneCode
		$i += 1;
		if( is_int($i/4) ) {
			echo "<br />";
		}
	}
	// add new sequence
	if( $mask_url == "true" ) {
		echo "</li><li><a href=\"" .$base_url . "/home.html\" onclick=\"return redirect('listseq.php?code=" .$code . "');\"><b>::Add new sequence::</b></a></li>";
	}
	else {
		echo "</li><li><a href=\"" .$base_url . "/admin/listseq.php?code=" .$code . "\"><b>::Add new sequence::</b></a></li>";
	}
	echo "</ul>";
	echo "By ";
	if( $latesteditor ) {
		echo $latesteditor;
	}
	else {
		echo "Administrator";
	}
	echo " on ";
	echo admin_formatDate($timestamp, $date_timezone, $php_version) . "</li></ul>";
}



// #################################################################################
// show lists of retrieved records in search, ordered list
// #################################################################################
function searchList1($code, $genus, $species, $extractor, $latesteditor, $timestamp, $base_url, $p_) {
	echo "<li><b><a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('add.php?code=" . $code . "');\">" . $code . "</a></b>
			<i>" . $genus . " " . $species . " </i>";
		
	//get info about picture
	$queryV  = "SELECT code, voucherImage FROM ". $p_ . "vouchers WHERE code='$code'";
	$resultV = mysql_query($queryV) or die("Error in query: $queryV. " . mysql_error());
	$rowV    = mysql_fetch_object($resultV);
	if ($rowV->voucherImage == 'na.gif') {
		echo "<a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('processPicture.php?code=" . $code . "');\">Picture missing</a> <img src=\"images/warning.png\" alt=\"\" />";
		}
	else {
		echo "<a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('../pictures/" . $rowV->voucherImage . "');\"><img class=\"link\" src=\"images/image.png\" alt=\"see pic\" title=\"See picture\" /></a>";
		// add or modify picture
		echo "<a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('processPicture.php?code=" . $code . "');\"><img class=\"link\" src=\"images/change_pic.png\" alt=\"change pic\" title=\"Change picture\" /></a>";
		}
	echo "<ul>";
	
	//get list of geneCodes
	// query from sequences table
	$queryS  = "SELECT code, geneCode FROM ". $p_ . "sequences WHERE code='$code' ORDER BY geneCode ASC";
	$resultS = mysql_query($queryS) or die("Error in query: $queryS. " . mysql_error());
	while ($rowS = mysql_fetch_object($resultS))
		{
		echo "<li><a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('listseq.php?code=" . $rowS->code . "&amp;geneCode=" . $rowS->geneCode . "');\">" . $rowS->geneCode . "</a></li>";
		}
	// add new sequence
	echo "<li><a href=\"" . $base_url . "/home.html\" onclick=\"return redirect('listseq.php?code=" . $code . "');\"><b>::Add new sequence::</b></a></li>";
	echo "</ul>";
	echo "By " . $latesteditor . " on ";
	echo admin_formatDate($timestamp, $date_timezone) . "</li>";
}



?>
