<?php
// #################################################################################
// #################################################################################
// Voseq admin/processSeq.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Process sequences before uploading to MySQL
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check admin login session
include'../login/auth-admin.php';
// includes
include 'admarkup-functions.php';
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes
include '../functions.php';

error_reporting (E_ALL ^ E_NOTICE);

// to indicate this is an administrator page
$admin = true;

$title = "VoSeq: Update";
// print html headers
include_once'../includes/header.php';

// print navegation bar
admin_nav();


// #################################################################################
// Section: Delete this sequence record by using its ID
// #################################################################################
if( $_POST['delete_seq'] ) {
	// set up error list array
	$errorList = array();
	//validate text input fields
	if (trim($_POST['id']) == '') {
		$errorList[] = "Invalid entry: <b>Record</b>";
		}
	if (trim($_POST['geneCode']) == '') {
		$errorList[] = "Invalid entry: <b>Gene Code</b>";
		}

	// check for errors
	// if none found ...
	if (sizeof($errorList) == 0 ) {
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}

		// get values
		$id = trim($_POST['id']);
		$geneCodeDel = $_POST['geneCode'];
		$codeDel = $_POST['code'];
		//delete sequence data
		$query = "DELETE FROM ". $p_ . "sequences WHERE id='$id'";
		$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
		//delete primer data
		$query = "DELETE FROM ". $p_ . "primers WHERE code='$codeDel' AND geneCode='$geneCodeDel'";
		$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
		//record sequence deletion in history
							$latesteditor = utf8_encode($_SESSION['SESS_FIRST_NAME']. " ". $_SESSION['SESS_LAST_NAME']);
							$editsed = $geneCodeDel . " sequence deleted by ". $latesteditor ." on ";
							mysql_query("time for add-list");
							$querytime = "SELECT NOW()";
							$resulttime = mysql_query($querytime) or die ("Error in query: $querytime. " . mysql_error());
							$rowtime    = mysql_result($resulttime,0);
							$editsed = $editsed . $rowtime;
							$queryed = "SELECT edits FROM ". $p_ . "vouchers WHERE code='$codeDel'";
							$resulted = mysql_query($queryed) or die ("Error in query: $querytime. " . mysql_error());
								if (mysql_num_rows($resulted) > 0) {
								$rowed    = mysql_result($resulted,0);
								$editsed = $editsed . "\n" . $rowed ; }
						
						$querydel = "UPDATE ". $p_ . "vouchers SET edits='$editsed', latesteditor='$latesteditor', timestamp=NOW() WHERE code='$codeDel'";
						$result = mysql_query($querydel) or die ("Error in query: $querydel. " . mysql_error());
							
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		
		// success mesg
		echo "<img src=\"images/success.png\" alt=\"\"><br />Sequence record:<br /><br />";
		echo "code: <b>" . $_POST['code'] . "</b> and<br />";
		echo "gene code: <b>" . $_POST['geneCode'] . "</b> was successfuly deleted";
		echo "</td>";
		
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); 
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		echo "</body>
				</html>";
	}
	else {
		echo "error";
	}
}

// #################################################################################
// Section: add new sequence
// #################################################################################
elseif ($_POST['submit'] && !$_POST['update']) 
	{
	// set up error list array
	$errorList = array();
	//validate text input fields
	if (trim($_POST['sequences']) == '')
		{
		$errorList[] = "Invalid entry: <b>Sequence</b>";
		}
	
	if (trim($_POST['geneCode']) == '')
		{
		$errorList[] = "Invalid entry: <b>Gene Code</b>";
		}
	
	if ($_POST['geneCode'] == 'choose_one')
		{
		$errorList[] = "Please choose a <b>Gene Code</b>";
		}
	// checking for existing geneCode in Genes db table
	// open database connection
	$genetest = $_POST['geneCode'];
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}
	$querygC = "SELECT geneCode FROM ". $p_ . "genes WHERE geneCode='$genetest'";
	$resultgC = mysql_query($querygC) or die ("Error in query: $querygC. " . mysql_error());
		//check for empty edits field
		if (mysql_num_rows($resultgC) == 0) {
			$errorList[] = "<b>Gene Code: $genetest</b> do not exist in gene table - please add the gene first";
		}
	// check for errors
	// if none found ...
	if (sizeof($errorList) == 0 )
		{
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}

		if( $php_version == "5" ) {
			date_default_timezone_set($date_timezone); //php5
		}
			
		// get values
		$code              = $_POST['id'];
		$sequences         = trim($_POST['sequences']);
		$sequences         = preg_replace("/\s/", "", $sequences);
		$sequences         = strtoupper($sequences);
		#$labPerson         = utf8_encode($_POST['labPerson']); # This utf8_encode seem to be unnecesary
		$labPerson         = $_POST['labPerson'];
		$geneCode          = $_POST['geneCode'];
		$accession         = $_POST['accession'];
		$dateCreation      = $_POST['dateCreation'];
		$dateModification  = date('Y-m-d');
		
		$primer1 = $_POST['primer1'];
		$primer2 = $_POST['primer2'];
		$primer3 = $_POST['primer3'];
		$primer4 = $_POST['primer4'];
		$primer5 = $_POST['primer5'];
		$primer6 = $_POST['primer6'];
		
							//setting the edits update values
							$latesteditor = utf8_encode($_SESSION['SESS_FIRST_NAME']. " ". $_SESSION['SESS_LAST_NAME']);
							$editsed = $geneCode . " sequence added by ". $latesteditor ." on ";
							mysql_query("time for add-list");
							$querytime = "SELECT NOW()";
							$resulttime = mysql_query($querytime) or die ("Error in query: $querytime. " . mysql_error());
							$rowtime    = mysql_result($resulttime,0);
							$editsed = $editsed . $rowtime;
							$queryed = "SELECT edits FROM ". $p_ . "vouchers WHERE code='$code'";
							$resulted = mysql_query($queryed) or die ("Error in query: $querytime. " . mysql_error());
								//check for empty edits field
								if (mysql_num_rows($resulted) > 0) {
								$rowed    = mysql_result($resulted,0);
									//check for number of edits
									$editsed = $editsed . "\n" . $rowed ;
								}
							//insert edited edtilist
							$querydel = "UPDATE ". $p_ . "vouchers SET edits='$editsed', latesteditor='$latesteditor', timestamp=NOW() WHERE code='$code'";
							$result = mysql_query($querydel) or die ("Error in query: $querydel. " . mysql_error());
		$queryS = "INSERT INTO ". $p_ . "sequences(code, sequences, labPerson, geneCode, accession, dateCreation, dateModification, timestamp) VALUES ('$code', '$sequences', '$labPerson', '$geneCode', '$accession', '$dateCreation', '$dateModification', NOW())";
		$resultS = mysql_query($queryS) or die ("Error in query: $queryS. " . mysql_error());
		
		$queryP = "INSERT INTO ". $p_ . "primers(code, geneCode, primer1, primer2, primer3, primer4, primer5, primer6, timestamp) VALUES ('$code', '$geneCode', '$primer1', '$primer2','$primer3','$primer4','$primer5', '$primer6', NOW())";
		$resultP = mysql_query($queryP) or die ("Error in query: $queryP. " . mysql_error());
						

		
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		
		// success mesg
		echo "<span class=\"title\"><img src=\"images/success.png\" alt=\"\"> Sequence creation was successful!</span>";
		echo "</td>";
		
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar();
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		echo "</body>
				</html>";
		}
	else
		{
		// error found
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		
		// print as list
		echo "<img src=\"../images/warning.png\" alt=\"\"> The following errors were encountered:";
		echo '<br>';
		echo '<ul>';
		for ($x=0; $x<sizeof($errorList); $x++)
			{
			echo "<li>$errorList[$x]";
			}
		echo '</ul>
				You need to fill up at least two fields: Sequence and Gene Code!';
		echo "</td>";
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar();
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		echo "\n</body>\n</html>";
		}
	}
	
// #################################################################################
// Section: update not insert
// #################################################################################
elseif ($_POST['submit'] && $_POST['update'] && $_POST['id']) {
	// set up error list array
	$errorList = array();
	//validate text input fields
	if (trim($_POST['sequences']) == '')
		{
		$errorList[] = "Invalid entry: <b>Sequence</b>";
		}
	
	if (trim($_POST['geneCode']) == '')
		{
		$errorList[] = "Invalid entry: <b>Gene Code</b>";
		}
	
	if ($_POST['geneCode'] == 'choose_one')
		{
		$errorList[] = "Please choose a <b>Gene Code</b>";
		}
	// checking for existing geneCode in Genes db table
	// open database connection
	$genetest = $_POST['geneCode'];
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}
	$querygC = "SELECT geneCode FROM ". $p_ . "genes WHERE geneCode='$genetest'";
	$resultgC = mysql_query($querygC) or die ("Error in query: $querygC. " . mysql_error());
		//check for empty edits field
		if (mysql_num_rows($resultgC) == 0) {
			$errorList[] = "<b>Gene Code: $genetest</b> do not exist in gene table - please add the gene first";
		}
	// check for errors
	// if none found ...
	if (sizeof($errorList) == 0 )
		{
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}
		
		// get values
		$id                = $_POST['id'];
		
		// clean sequences
		$sequences         = trim($_POST['sequences']);
		$sequences         = preg_replace("/\s/", "", $sequences);
		$sequences         = strtoupper($sequences);
		$code              = $_POST['code'];
		#$labPerson         = utf8_encode($_POST['labPerson']); # This utf8_encode seem to be unnecesary
		$labPerson         = $_POST['labPerson'];
		$geneCode          = $_POST['geneCode'];
		$accession         = $_POST['accession'];
		$dateCreation      = $_POST['dateCreation'];
		if($php_version == "5") {
			date_default_timezone_set($date_timezone); // php5
		}
		$dateModification  = date('Y-m-d');
		
		$primer1 = $_POST['primer1'];
		$primer2 = $_POST['primer2'];
		$primer3 = $_POST['primer3'];
		$primer4 = $_POST['primer4'];
		$primer5 = $_POST['primer5'];
		$primer6 = $_POST['primer6'];
		
			//checking which values are updated and fixing edit list
			$querycompareS  = "SELECT sequences, labPerson, geneCode, dateCreation, accession, dateModification FROM ". $p_ . "sequences WHERE id='$id'";
			$resultcompareS = mysql_query($querycompareS) or die ("Error in query: $querycompare. " . mysql_error());
			$rowcompareS    = mysql_fetch_object($resultcompareS);
			$edvalues = '';
			$edcount = '0';
			if ($sequences != $rowcompareS->sequences) {$edvalues = $edvalues . ", sequence"; $edcount = $edcount + 1; }
			if ($labPerson != $rowcompareS->labPerson) {$edvalues = $edvalues . ", lab person"; $edcount = $edcount + 1; }
			if ($geneCode != $rowcompareS->geneCode) {$edvalues = $edvalues . ", gene code"; $edcount = $edcount + 1; }
			if ($accession != $rowcompareS->accession) {$edvalues = $edvalues . ", accession"; $edcount = $edcount + 1; }
			if ($dateCreation != $rowcompareS->dateCreation) {$edvalues = $edvalues . ", creation date"; $edcount = $edcount + 1; }
			$querycompareP  = "SELECT primer1, primer2, primer3, primer4, primer5, primer6 FROM ". $p_ . "primers WHERE code='$code' AND geneCode='$geneCode'";
			$resultcompareP = mysql_query($querycompareP) or die ("Error in query: $querycompare. " . mysql_error());
			$rowcompareP    = mysql_fetch_object($resultcompareP);
			
			if(  count((array)$rowcompareP) != "1" ) { 
				if ($primer1 != $rowcompareP->primer1) {$edvalues = $edvalues . ", primer1"; $edcount = $edcount + 1; }
				if ($primer2 != $rowcompareP->primer2) {$edvalues = $edvalues . ", primer2"; $edcount = $edcount + 1; }
				if ($primer3 != $rowcompareP->primer3) {$edvalues = $edvalues . ", primer3"; $edcount = $edcount + 1; }
				if ($primer4 != $rowcompareP->primer4) {$edvalues = $edvalues . ", primer4"; $edcount = $edcount + 1; }
				if ($primer5 != $rowcompareP->primer5) {$edvalues = $edvalues . ", primer5"; $edcount = $edcount + 1; }
				if ($primer6 != $rowcompareP->primer6) {$edvalues = $edvalues . ", primer6"; $edcount = $edcount + 1; }
					
				//fix edvalues-string
				$edvalues = preg_replace('/, /', '', $edvalues, 1);
				$edvalues = ucfirst($edvalues);
			}
			
			//setting the edits update values
			$latesteditor = utf8_encode($_SESSION['SESS_FIRST_NAME']. " ". $_SESSION['SESS_LAST_NAME']);
			$editsed = $geneCode . " " . $edvalues . " edited by ". $latesteditor ." on ";
			mysql_query("time for add-list");
			$querytime = "SELECT NOW()";
			$resulttime = mysql_query($querytime) or die ("Error in query: $querytime. " . mysql_error());
			$rowtime    = mysql_result($resulttime,0);
			$editsed = $editsed . $rowtime;
			$queryed = "SELECT edits FROM ". $p_ . "vouchers WHERE code='$code'";
			$resulted = mysql_query($queryed) or die ("Error in query: $querytime. " . mysql_error());
				//check for empty edits field
				if (mysql_num_rows($resulted) > 0) {
				$rowed    = mysql_result($resulted,0);
					//check for number of edits
					if ($edcount != '0') { 
					$editsed = $editsed . "\n" . $rowed ;}
					else { $editsed = $rowed ; }
					}
			//insert edited edtilist
			$querydel = "UPDATE ". $p_ . "vouchers SET edits='$editsed', latesteditor='$latesteditor', timestamp=NOW() WHERE code='$code'";
			$result = mysql_query($querydel) or die ("Error in query: $querydel. " . mysql_error());
		
			//update sequence and primer tables
			$queryS = "UPDATE ". $p_ . "sequences SET sequences='$sequences', labPerson='$labPerson', geneCode='$geneCode', dateCreation='$dateCreation', accession='$accession', dateModification='$dateModification', timestamp=NOW() WHERE id='$id'";
			mysql_query($queryS) or die ("Error in query: $queryS. " . mysql_error());
		
			if( count((array)$rowcompareP) != "1" ) {
				$queryP = "UPDATE ". $p_ . "primers SET primer1='$primer1', primer2='$primer2', primer3='$primer3', primer4='$primer4', primer5='$primer5', primer6='$primer6', timestamp=NOW() WHERE code='$code' AND geneCode='$geneCode'";
			}
			else {
				$queryP = "INSERT INTO ". $p_ . "primers (code, geneCode, primer1, primer2, primer3, primer4, primer5, primer6, timestamp) values ('$code', '$geneCode', '$primer1', '$primer2', '$primer3', '$primer4', '$primer5', '$primer6', now())";
			}
			mysql_query($queryP) or die ("Error in query: $queryP. " . mysql_error());
		
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
				
		// success mesg
		echo "<span class=\"title\"><img src=\"images/success.png\" alt=\"\"> Sequence creation was successful!</span>";
		echo "</td>";
		
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); 
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		echo "</body>
		</html>";
		}
	else
		{
		// error found
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
				
		// print as list
		echo "<img src=\"../images/warning.png\" alt=\"\"> The following errors were encountered:";
		echo '<br>';
		echo '<ul>';
		for ($x=0; $x<sizeof($errorList); $x++)
			{
			echo "<li>$errorList[$x]";
			}
		echo '</ul>
				You need to fill up at least two fields: Sequence and Gene Code!';
		echo "</td>";
		
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); 
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		echo "\n</body>\n</html>";
		}
	}
// #################################################################################
// Section: message after successful upload
// #################################################################################
else
	{
	// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
				
	echo "Do you want to add:
	<ul>
	<li> A new sequence for existing record? <a href=\"\">here</a></li>
	<li> A picture for existing record? <a href=\"\">here</a></li>
	<li> A new record? <a href=\"add.php\">here</a></li>";
	echo "</td>";
		
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); 
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		echo "\n</body>\n</html>";
	}

?>
