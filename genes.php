<?php
// #################################################################################
// #################################################################################
// Voseq genes.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Displays the gene overview page
// as well as single gene info
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
//check login session
include'login/auth.php';

error_reporting (E_ALL ^ E_NOTICE);

// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'conf.php';
ob_end_clean();//Clear output buffer//includes
include 'functions.php';
include 'markup-functions.php';
include 'includes/validate_coords.php';

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'Tooltip';
$whichDojo[] = 'ComboBox';

// to indicate this is an not an administrator page
$admin = false;
// #################################################################################
// Section: Search settings
// #################################################################################
// previous and next links
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
	$query = "SELECT id FROM " . $p_ . "genes WHERE geneCode = '$id'";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	// get result set as object
	$row = mysql_fetch_object($result);
	$current_id = $row->id;

	// get previous and next links from search and search_results tables
	$current_id_search_id = $_GET['search'];

	// current id of this record in search_results ids
	$query_c_id_t_r  = "SELECT id FROM " . $p_ . "search_results WHERE search_id='$current_id_search_id' AND record_id='$current_id'";
	$result_c_id_t_r = mysql_query($query_c_id_t_r) or die("Error in query: $query_c_id_t_r. " . mysql_error());
	$row_c_id_t_r    = mysql_fetch_object($result_c_id_t_r);
	
	$link_current  = $row_c_id_t_r->id;

	// link previous
	$link_previous = $link_current - 1;
	$query_link_previous      = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_previous'";
	$result_link_previous     = mysql_query($query_link_previous) or die("Erro in query: $query_link_previous. " . mysql_error());
	$row_result_link_previous = mysql_fetch_object($result_link_previous);
	if ($row_result_link_previous) {
		$query_lp  = "SELECT record_id FROM ". $p_ . "search_results WHERE id='$link_previous'";
		$result_lp = mysql_query($query_lp) or die("Error in query: $query_lp. " . mysql_error());
		$row_lp    = mysql_fetch_object($result_lp);
		$previous  = $row_lp->record_id;
		$query_lpcode  = "SELECT geneCode FROM ". $p_ . "genes WHERE id='$previous'";
		$result_lpcode = mysql_query($query_lpcode) or die("Error in query: $query_lpcode. " . mysql_error());
		$row_lpcode    = mysql_fetch_object($result_lpcode);
		$prevgeneCode      = $row_lpcode->geneCode;
	}
	else {
		$link_previous = false;
	}

	// link next
	$link_next = $link_current + 1;
	$query_link_next  = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_next'";
	$result_link_next = mysql_query($query_link_next) or die("Erro in query: $query_link_next. " . mysql_error());
	$row_result_link_next = mysql_fetch_object($result_link_next);

	if ($row_result_link_next) {
		$query_ln  = "SELECT record_id FROM ". $p_ . "search_results WHERE id='$link_next'";
		$result_ln = mysql_query($query_ln) or die("Error in query: $query_ln. " . mysql_error());
		$row_ln    = mysql_fetch_object($result_ln);
		$next      = $row_ln->record_id;
		$query_lncode  = "SELECT geneCode FROM ". $p_ . "genes WHERE id='$next'";
		$result_lncode = mysql_query($query_lncode) or die("Error in query: $query_lncode. " . mysql_error());
		$row_lncode    = mysql_fetch_object($result_lncode);
		$nextgeneCode      = $row_lncode->geneCode;
	}
	else {
		$link_next = false;
	}
} // end previous and next links

// #################################################################################
// Section: Outputting single gene info page
// #################################################################################
elseif ($_GET['geneCode']) {
	// record to update
	// get values to prefill fields
	$geneCode1 = clean_string($_GET['geneCode']);
	$geneCode1 = $geneCode1[0];
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}

	// check for duplicate code
	$query1  = "SELECT id, geneCode, length, description, readingframe FROM ". $p_ . "genes WHERE geneCode='$geneCode1'";
	$result1 = mysql_query($query1) or die ("Error in query: $query1. " . mysql_error());
	$row1    = mysql_fetch_object($result1);
	
	// get title
	$title = "$config_sitename - Gene " . $geneCode1;
				
	// print html headers
	include_once('includes/header.php');
	nav();
				
	// begin HTML page content
	echo "<div id=\"content\">";
	
	?>
	
<!-- 	show previous and next links -->
	<?php
	echo "<h1>" . "$geneCode1" . "</h1>";
	echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
			<tr><td valign=\"top\">";
	?>
	
<table width="600px" border="0"> <!-- big parent table -->
	<tr>
		<td valign="top">
			<table border="0" cellspacing="10"> <!-- table child 1 -->
				<tr>
					<td>
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
									<input size="3" maxlength="40" type="text" name="readingframe" value="<?php echo $row1->readingframe; ?>"/>
									</select></td>
							</tr>
							<tr>
								<td class="label">Description</td>
								<td class="field" colspan = "5">
										<input size="60" maxlength="600" type="text" name="description" value="<?php echo $row1->description; ?>"/>
									</select></td>
							</tr>
						</table>
					</td>
				</tr>
			</table><!-- end table child 1 -->
		</td>
	</tr>
</table><!-- end big parent table -->

</td>
<td>
	<?php make_sidebar();  ?>
</td>
</tr>
</table> <!-- end super table -->


</div> <!-- end content -->

<?php
// close database connection
mysql_close($connection);

make_footer($date_timezone, $config_sitename, $version, $base_url);

}
// #################################################################################
// Section: Outputting Gene table intro page
// #################################################################################
// direct access - view gene table
elseif (!$_GET['geneCode']) {
	// get title
	$title = "$config_sitename - Gene list";
			
	// print html headers
	include_once('includes/header.php');
	nav();
			
	// print as list
	// open database connection
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	
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

		// begin HTML page content
		echo "<div id=\"content\">";
		echo "<h1>Existing genes:</h1>";
		echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
				<tr><td valign=\"top\">";


		echo "<table width=\"600px\" border=\"0\"><!-- big parent table -->";
		echo "<tr><td valign=\"top\">";
		echo "<ul>";

		while ($row = mysql_fetch_object($result)) {
			// query from sequences table
			echo "<li><b>";
			
			// masking URLs, this variable is set to "true" or "false" in conf.php file
			if( $mask_url =="true" ) {
				echo "<a href='" . $base_url . "/home.php' onclick=\"return redirect('genes.php?geneCode=$row->geneCode')\">$row->geneCode</a></b>";
			}
			else {
				echo "<a href='genes.php?geneCode=$row->geneCode'>$row->geneCode</a></b>";
			}

			echo " <i>$row->description - $row->length bp.</i></li>";
		}
	}

	// if no records present
	// display message
	else {
		?>
	
		<font size="-1">No records currently available</font>
	
		<?php
		}
	
// close database connection
mysql_close($connection);
echo "</ul>";
echo "</td></tr></table><!-- end big parent table -->";
?>
</td>
<td>
	<?php make_sidebar(); ?>
</td>
</tr>
</table> <!-- end super table -->

</div> <!-- end content -->

<?php
		make_footer($date_timezone, $config_sitename, $version, $base_url);

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
