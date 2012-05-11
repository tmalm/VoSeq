<?php
// #################################################################################
// #################################################################################
// Voseq admin/add_gene.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: add and update genes and their information:
//  - reading frames
//  - number of basepairs 
//  - description
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check admin login session
include'../login/auth-admin.php';

error_reporting (E_ALL ^ E_NOTICE);

// includes
#include '../login/redirect.html';
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes
include 'adfunctions.php'; // administrator functions
include 'admarkup-functions.php';
include '../includes/validate_coords.php';

// process title
$title = $config_sitename;

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'Tooltip';
$whichDojo[] = 'ComboBox';

// to indicate this is an administrator page
$admin = true;



// #################################################################################
// previous and next links
// #################################################################################
if ( isset($_GET['search']) || trim($_GET['search']) != '') {
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}

// generate and execute query
$id = $_GET['geneCode'];
$query = "SELECT id FROM ". $p_ . "genes WHERE geneCode = '$id'";
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
// get result set as object
$row = mysql_fetch_object($result);
$current_id = $row->id;

// get previous and next links from search and search_results tables
$current_id_search_id = $_GET['search'];

// current id of this record in search_results ids
$query_c_id_t_r  = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND record_id='$current_id'";
$result_c_id_t_r = mysql_query($query_c_id_t_r) or die("Error in query: $query_c_id_t_r. " . mysql_error());
$row_c_id_t_r    = mysql_fetch_object($result_c_id_t_r);

$link_current  = $row_c_id_t_r->id;

// link previous
$link_previous = $link_current - 1;
$query_link_previous      = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_previous'";
$result_link_previous     = mysql_query($query_link_previous) or die("Erro in query: $query_link_previous. " . mysql_error());
$row_result_link_previous = mysql_fetch_object($result_link_previous);
if ($row_result_link_previous)
	{
	$query_lp  = "SELECT record_id FROM ". $p_ . "search_results WHERE id='$link_previous'";
	$result_lp = mysql_query($query_lp) or die("Error in query: $query_lp. " . mysql_error());
	$row_lp    = mysql_fetch_object($result_lp);
	$previous  = $row_lp->record_id;
	$query_lpcode  = "SELECT geneCode FROM ". $p_ . "genes WHERE id='$previous'";
	$result_lpcode = mysql_query($query_lpcode) or die("Error in query: $query_lpcode. " . mysql_error());
	$row_lpcode    = mysql_fetch_object($result_lpcode);
	$prevgeneCode      = $row_lpcode->geneCode;
	}
else
	{
	$link_previous = false;
	}

// link next
$link_next = $link_current + 1;
$query_link_next  = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_next'";
$result_link_next = mysql_query($query_link_next) or die("Erro in query: $query_link_next. " . mysql_error());
$row_result_link_next = mysql_fetch_object($result_link_next);
if ($row_result_link_next)
	{
	$query_ln  = "SELECT record_id FROM ". $p_ . "search_results WHERE id='$link_next'";
	$result_ln = mysql_query($query_ln) or die("Error in query: $query_ln. " . mysql_error());
	$row_ln    = mysql_fetch_object($result_ln);
	$next      = $row_ln->record_id;
	$query_lncode  = "SELECT geneCode FROM ". $p_ . "genes WHERE id='$next'";
	$result_lncode = mysql_query($query_lncode) or die("Error in query: $query_lncode. " . mysql_error());
	$row_lncode    = mysql_fetch_object($result_lncode);
	$nextgeneCode      = $row_lncode->geneCode;
	}
else
	{
	$link_next = false;
	}
} // end previous and next links



// #################################################################################
// form not yet submitted
// display initial form and empty
// #################################################################################
if ($_GET['new'])
	// brand new record
	{

	// print html headers
	include_once('../includes/header.php');

	// print navegation bar
	admin_nav();

	// begin HTML page content
	echo "<div id=\"content\">";
	?>

<table border="0" width="960px"> <!-- super table -->
<tr><td valign="top">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<b>Create a definition for genes. Specify "Reading frame" if you want to create datasets by codon positions.</b>

<table width="800" border="0"> <!-- big parent table -->
<tr><td valign="top">
	<table border="0" cellspacing="10"> <!-- table child 1 -->
	<tr><td>
	<table width="500" cellspacing="0" border="0">
	<caption>Gene information</caption>
		<tr>
			<td class="label">Gene code</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_geneCode.js" style="width: 90px;" name="geneCode" maxListLength="20">
				</select></td>
			<td class="label3">Length</td>
			<td class="field2">
				<input size="10" maxlength="40" type="text" name="length" />
				</select></td>
			<td class="label3">Reading frame</td>
			<td class="field2">
				<input type="radio" name="readingframe" value="1" >1 
				<input type="radio" name="readingframe" value="2" >2 
				<input type="radio" name="readingframe" value="3" >3
			</td>
		</tr>
		<tr>
			<td class="label">Description</td>
			<td class="field" colspan = "5">
					<input size="80" maxlength="500" type="text" name="description" />
				</select></td></tr>
		<tr>
			<td class="label">Notes:
			</td>
			<td class="field" colspan="5">
					<input size="80" maxlength="500" type="text" name="notes" value="<?php echo $row1->notes; ?>"/>
			</td>
		</tr>
		<tr>
			<td></td><td></td><td></td><td></td><td></td><td>
				<input type="submit" name="submitNew" value="Add gene" />
			</td>
		</tr>
	</table>
	
	</td></tr>
	</table><!-- end table child 2 -->

</td></tr>
</table><!-- end big parent table -->

</td>
<td class="sidebar" valign="top">
	<?php admin_make_sidebar(); ?>
</td>
</tr>
</table> <!-- end super table -->

</form>
</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
?>
	
	<?php
	}
elseif ($_POST['submitNew']) {
	// set up error list array
	$errorList = array();
	
	//validate text input fields
	if (trim($_POST['geneCode']) == '')
		{
		$errorList[] = "Invalid entry: <b>Gene code</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You must specify a gene code to proceed!";
		}

	$geneCode      = trim($_POST['geneCode']);
	$description = utf8_encode (trim($_POST['description']));
	$length     = trim($_POST['length']);
	$readingframe     = $_POST['readingframe'];
	$notes     = $_POST['notes'];
	
	if ($length){
		if (is_numeric($length)){ }
		else {
			$errorList[] = "Invalid entry: <b>Length = \"$length\"</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Length needs to be an integer!";
			}
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
		
		// check for duplicate geneCode
		$querygCode = "SELECT * FROM ". $p_ . "genes WHERE geneCode='$geneCode'";
		$resultgCode = mysql_query($querygCode) or die ("Error in query: $querygCode. " . mysql_error());
		if (mysql_num_rows($resultgCode) > 0)
			{
			// process title
			$title = "$config_sitename - Error, duplicate gene code";
			
			// print html headers
			include_once('../includes/header.php');
			admin_nav();
			
			// begin HTML page content
			echo "<div id=\"content_narrow\">";
			echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
			echo "<img src=\"../images/warning.png\" alt=\"\">
						The record's <b>code</b> you entered is already preoccupied.<br />There can't be two genes with the same gene code!.<br />Please click \"Go back\" in your browser and enter a different gene code.</td>";
			echo "<td class=\"sidebar\" valign=\"top\">";
				admin_make_sidebar();
			echo "</td>";
			echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
			make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
			echo "\n</body>\n</html>";
			exit();
			}
		else
			{	
			//setting the edits add values
			// $editsadd = "Added by ". $_SESSION['SESS_FIRST_NAME']. " ". $_SESSION['SESS_LAST_NAME'] ." on ";
			// mysql_query("time for add-list");
			// $querytime = "SELECT NOW()";
			// $resulttime = mysql_query($querytime) or die ("Error in query: $querytime. " . mysql_error());
			// $rowtime    = mysql_result($resulttime,0);
			// $editsadd = $editsadd . $rowtime;
			// mysql_query("set names utf8");
			
			// generate and execute query
			$gquery = "INSERT INTO ". $p_ . "genes(geneCode, length, description, readingframe, notes, timestamp) VALUES ('$geneCode', '$length', '$description', '$readingframe', '$notes', NOW())";
		
			$gresult = mysql_query($gquery) or die ("Error in query: $query. " . mysql_error());
			
			// process title
			$title = "$config_sitename - Gene " . $geneCode . " created";

			// print html headers
			include_once('../includes/header.php');

			// print navegation bar
			admin_nav();

			// begin HTML page content
			echo "<div id=\"content_narrow\">";
			echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
			// print result
			echo "<span class=\"title\"><img src=\"images/success.png\" alt=\"\"> Gene creation was successful!</span>";
			}

			echo "<td class=\"sidebar\" valign=\"top\">";
				admin_make_sidebar();
			echo "</td>";
			echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
			make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		}
	else
		{
		// error found
		
		// get title
		$title = "$config_sitename - Error, missing info";
		
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
		
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		echo "<img src=\"../images/warning.png\" alt=\"\"> The following errors were encountered:";
		echo '<br>';
		echo '<ul>';
		for ($x=0; $x<sizeof($errorList); $x++)
			{
			echo "<li>$errorList[$x]";
			}
		echo "</ul></td>";
		echo "<td class=\"sidebar\" valign=\"top\">";
			admin_make_sidebar();
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		}
	}

// #################################################################################
// record to update
// get values to prefill fields
// #################################################################################
elseif (!$_POST['submitNoNew'] && $_GET['geneCode']) {
	$geneCode1 = $_GET['geneCode'];
	@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}

	// check for duplicate code
	$query1  = "SELECT id, geneCode, length, description, readingframe, notes FROM ". $p_ . "genes WHERE geneCode='$geneCode1'";
	$result1 = mysql_query($query1) or die ("Error in query: $query1. " . mysql_error());
	$row1    = mysql_fetch_object($result1);
	
	// get title
	$title = "$config_sitename - Edit " . $geneCode1;
				
	// print html headers
	include_once('../includes/header.php');
	admin_nav();
				
	// begin HTML page content
	echo "<div id=\"content\">";
	
	?>
	
<!-- 	show previous and next links -->
	<?php
	echo "<h1>" . $geneCode1 . "</h1>";
	echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
			<tr><td valign=\"top\">";
			
	if ( isset($_GET['search']) || trim($_GET['search']) != '')
		{
		if ($link_previous)
			{ ?>
			<span dojoType="tooltip" connectId="previous" delay="1" toggle="explode">Previous</span>
			<?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_gene.php?genecode=<?php echo $prevgeneCode; ?>&amp;search=<?php echo $current_id_search_id; ?>');"><img src="images/leftarrow.png" class="link" alt="previous" id="previous" /></a>&nbsp;&nbsp;
			<?php
			}
		else
			{
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		
		if ($link_next)
			{ ?>
			<span dojoType="tooltip" connectId="next" delay="1" toggle="explode">Next</span>
			<?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_gene.php?genecode=<?php echo $nextgeneCode; ?>&amp;search=<?php echo $current_id_search_id; ?>');"><img src="images/rightarrow.png" class="link" alt="next" id="next" /></a>
			<?php
			}
		else
			{
			echo "&nbsp;";
			}
		}
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table width="800" border="0"> <!-- big parent table -->
<tr><td valign="top">
	<table border="0" cellspacing="10"> <!-- table child 1 -->
	<tr><td>
		<!-- 	input id of this record also, useful for changing the code -->
		<input type="hidden" name="id" value="<?php echo $row1->id; ?>" />
		<!-- 	end input id -->
	<table width="350" cellspacing="0" border="0">
	<caption>Gene information</caption>
		<tr>
			<td class="label">Gene code</td>
			<td class="field">
				<input size="12" maxlength="250" type="text" name="geneCode" value="<?php echo $row1->geneCode; ?>" /></td>
				</select></td>
			<td class="label3">Length</td>
			<td class="field2">
				<input size="10" maxlength="40" type="text" name="length" value="<?php echo $row1->length; ?>"/>
				</select></td>
			<td class="label3">Reading frame</td>
			<td class="field2">
				<input type="radio" name="readingframe" value="1" <?php if ($row1->readingframe == 1) { echo "checked"; } ?> />1 
				<input type="radio" name="readingframe" value="2" <?php if ($row1->readingframe == 2) { echo "checked"; } ?> />2 
				<input type="radio" name="readingframe" value="3" <?php if ($row1->readingframe == 3) { echo "checked"; } ?> />3
			</td>
		</tr>
		<tr>
			<td class="label">Description</td>
			<td class="field" colspan = "5">
					<input size="80" maxlength="500" type="text" name="description" value="<?php echo utf8_decode($row1->description); ?>"/>
				</select></td>
		</tr>
		<tr>
			<td class="label">Notes:
			</td>
			<td class="field" colspan="5">
					<input size="80" maxlength="500" type="text" name="notes" value="<?php echo $row1->notes; ?>"/>
			</td>
		</tr>
		<tr>
			<td></td><td></td><td></td><td></td><td></td><td>
				<input type="submit" name="submitNoNew" value="Update gene" />
			</td>
		</tr>
	</table>
	
	</td></tr>
	</table><!-- end table child 2 -->

</td></tr>
</table><!-- end big parent table -->

</td>

<td class="sidebar" valign="top">
	<?php admin_make_sidebar();  ?>
</td>

</tr>
</table>
</table> <!-- end super table -->

</form>
</div> <!-- end content -->

	<?php
	// close database connection
	mysql_close($connection);

	make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);

}
elseif ($_POST['submitNoNew']) {
	// set up error list array
	$errorList = array();
	
	//validate text input fields
	if (trim($_POST['geneCode']) == '')
		{
		$errorList[] = "Invalid entry: <b>Gene code</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You must specify a gene code to proceed!";
		}
	$id1       = $_POST['id'];
	$geneCode1      = trim($_POST['geneCode']);
	$description = trim($_POST['description']);
	$length     = trim($_POST['length']);
	$readingframe = $_POST['readingframe'];
	$notes = $_POST['notes'];
	
	//echo "$id1,	$geneCode1, $description, $length</br>";
	if ($length){
		if (is_numeric($length)){ }
		else {
			$errorList[] = "Invalid entry: <b>Length = \"$length\"</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Length needs to be an integer!";
			}
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
		
		// check if submitted code is meant to replace old one
		// get old code
		$queryOldCode = "SELECT geneCode FROM ". $p_ . "genes WHERE id='$id1'";
		$resultOldCode = mysql_query($queryOldCode) or die ("Error in query: $queryOldCode. " . mysql_error());
		$rowOldCode    = mysql_fetch_object($resultOldCode);
		$oldCode = $rowOldCode->geneCode;
		// get new code
		$newCode = $geneCode1;
		//  if new code != old code
		if ($oldCode != $newCode)
			{
			// check for duplicate
			$queryCode1 = "SELECT geneCode FROM ". $p_ . "genes WHERE geneCode='$newCode'";
			$resultCode1 = mysql_query($queryCode1) or die ("Error in query: $queryCode1. " . mysql_error());
			if (mysql_num_rows($resultCode1) > 0)
				{
				// get title
				$title = "$config_sitename - Error, duplicate gene code";
				
				// print html headers
				include_once('../includes/header.php');
				admin_nav();
				
				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
						<tr><td valign=\"top\">";
				echo "<img src=\"../images/warning.png\" alt=\"\">
						The record's <b>gene code</b> ($newCode) you entered is already preoccupied.<br />
						There can't be two genes with the same gene code!.<br /><br />
						Please click \"Go back\" in your browser and enter a different code.</span>
						</td>";
				echo "<td class=\"sidebar\" valign=\"top\">";
				admin_make_sidebar(); 
				echo "</td>";
				echo "</tr>
					  </table> <!-- end super table -->
					  </div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				exit();
				}
			}
		// utf8 encode some fields
		$geneCode1 = $geneCode1;
		$description = utf8_encode($description);
		// generate and execute query UPDATE
		$query = "UPDATE ". $p_ . "genes SET geneCode='$geneCode1', length='$length', description='$description', readingframe='$readingframe', notes='$notes', timestamp=NOW() WHERE id='$id1'";

		$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
		
		// get title
		$title = "$config_sitename - Record " . $geneCode1 . " updated";
				
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
				
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		echo "<img src=\"images/success.png\" alt=\"\"> Record update was successful!";
		echo "</td>";
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); // includes td and /td already
		echo "</td>";
		echo "</tr>
			  </table> <!-- end super table -->
			  </div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				
		mysql_close($connection);
		}
	else
		{
		// error found
		
		// get title
		$title = "$config_sitenae - Error";
				
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
				
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";

		// print as list
		echo "<img src=\"../images/warning.png\" alt=\"\">The following errors were encountered:";
		echo '<br>';
		echo '<ul>';
		for ($x=0; $x<sizeof($errorList); $x++)
			{
			echo "<li>$errorList[$x]";
			}
		echo "</ul></td>";
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); // includes td and /td already
		echo "</td>";
		echo "</tr>
			  </table> <!-- end super table -->
			  </div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		}
	}


// #################################################################################
// direct access - view gene table
// #################################################################################
elseif (!$_GET['new'] && !$_POST['submitNew'] && !$_POST['submitNoNew'] &&  !$_GET['geneCode'] ) {
	// get title
		$title = "$config_sitename - Gene list";
				
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
				
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
				 echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_gene.php?new=new');"><b>Add gene</b></a><br />
<?php

		// print as list
		// open database connection
		@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}
		// generate and execute query from genes table
	$query = "SELECT id, geneCode, length, description, timestamp FROM ". $p_ . "genes ORDER BY geneCode";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
	// if records present
	if (mysql_num_rows($result) > 0) {
		// iterate through result set
		// print article titles
		echo "<h1>Existing genes:</h1>\n";

		echo "<b>Create a definition for genes. Specify \"Reading frame\" if you want to create datasets by codon positions.</b>";

		echo "<ul>";
		$i = 1; // count for tooltips in dojo
		while ($row = mysql_fetch_object($result))
			{
			// query from sequences table
			$code    = $row->geneCode;
			$queryS  = "SELECT id, geneCode, length, timestamp FROM ". $p_ . "genes WHERE geneCode='$geneCode'";
			$resultS = mysql_query($queryS) or die("Error in query: $queryS. " . mysql_error());
			$descrutf8 = utf8_decode($row->description);
			?>
			<li><b>
			<?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_gene.php?geneCode=<?php echo $row -> geneCode; ?>');"><?php echo $row -> geneCode; ?></a></b>
			<i><?php echo $descrutf8; echo ' - ' . $row->length . 'bp.'; ?></i>
			<?php
			}
		}

	// if no records present
	// display message
	else
		{
		?>
	
		<b>Create a definition for genes. Specify "Reading frame" if you want to create datasets by codon positions.</b>
		<br />
		<br />
		<font size="-1">No records currently available</font>
	
		<?php
		}
	
// close database connection
mysql_close($connection);
?>
</ul>
</td>
<?php
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); 
		echo "</td>";
		echo "</tr>
			  </table> <!-- end super table -->
			  </div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);

	}
else
{
	{
	echo "<div id=\"rest1\"><img src=\"images/warning.png\" alt=\"\" /><span class=\"text\"> Some kind of error ocurred, but I do not know what it is, please try again!</span></div>";
	}
}
	?>
	
</body>
</html>
