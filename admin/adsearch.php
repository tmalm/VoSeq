<?php
// #################################################################################
// #################################################################################
// Voseq admin/adsearch.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Search function of administrator interface
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check if conf.php exists. If not, this is a fresh download and needs installation
if( !file_exists("../conf.php") ) {
        header("Location: installation/NoConfFile.php" );
        exit(0);
}



//check admin login session
include'../login/auth-admin.php';
// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes

include 'admarkup-functions.php';
include 'adfunctions.php';

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'ComboBox';

// prepare title
$title = $config_sitename . "-Search: ";

// to indicate this is an administrator page
$admin = true;



// #################################################################################
// Section: check for any submit search
// #################################################################################
if (!empty($_POST))
	{
	if ( (!$_POST['code']    || trim($_POST['code'])    == '') && 
	     (!$_POST['orden']  || trim($_POST['orden'])  == '') &&
	     (!$_POST['family']  || trim($_POST['family'])  == '') &&
	     (!$_POST['genus']   || trim($_POST['genus'])   == '') &&
	     (!$_POST['species'] || trim($_POST['species']) == '') &&
		  (!$_POST['subfamily']  || trim($_POST['subfamily'])  == '') &&
	     (!$_POST['tribe']      || trim($_POST['tribe'])      == '') &&
	     (!$_POST['subtribe']   || trim($_POST['subtribe'])   == '') &&
	  	  (!$_POST['subspecies'] || trim($_POST['subspecies']) == '') &&
	       ($_POST['typeSpecies'] !='0' ) && ($_POST['typeSpecies'] !='1') &&
		    ($_POST['genbank'] !='yes' ) && ($_POST['genbank'] !='no') &&
		  (!$_POST['country']    || trim($_POST['country'])    == '') &&
	     (!$_POST['specificLocality']|| trim($_POST['specificLocality'])== '') &&
	     (!$_POST['latitude']   || trim($_POST['latitude'])== '') &&
	     (!$_POST['longitude']  || trim($_POST['longitude'])== '') &&
	     (!$_POST['altitude']   || trim($_POST['altitude'])== '') &&
	     (!$_POST['collector']  || trim($_POST['collector'])== '') &&
	     (!$_POST['dateCollection'] || trim($_POST['dateCollection'])== '') &&
	     (!$_POST['voucherLocality']|| trim($_POST['voucherLocality'])== '') &&
	     (!$_POST['voucher']    || trim($_POST['voucher'])== '') &&
	     (!$_POST['sex']        || trim($_POST['sex'])== '') &&
	     (!$_POST['voucherImage']  || trim($_POST['voucherImage'])== '') &&
	     (!$_POST['voucherCode']   || trim($_POST['voucherCode'])== '') &&
	     (!$_POST['extraction']    || trim($_POST['extraction'])== '') &&
	     (!$_POST['extractionTube']|| trim($_POST['extractionTube'])== '') &&
	     (!$_POST['extractor']     || trim($_POST['extractor'])== '') &&
	     (!$_POST['dateExtraction']|| trim($_POST['dateExtraction'])== '') &&
	     (!$_POST['geneCode']      || trim($_POST['geneCode'])== '') &&
	     (!$_POST['publishedIn']   || trim($_POST['publishedIn'])== '') &&
	     (!$_POST['hostorg']   || trim($_POST['hostorg'])== '') && 
	     (!$_POST['edits']   || trim($_POST['edits'])== '') && 
	     (!$_POST['notes']         || trim($_POST['notes'])== '') )
		{
		// process title
		$title = "$config_sitename - Error";

		// print html headers
		include_once'../includes/header.php';

		// print navegation bar
		admin_nav();

		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		echo "<img src=\"images/warning.png\" alt=\"\" /> Please enter a <b>string</b> to search.";
		echo "</td>";
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
		// open database connections
		@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
		// select database
		mysql_select_db($db) or die ('Unable to select database');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}
		
		// DO SEARCHES
		$search_msg = "<br />Results for query: ";
		
		//if user enters fields to query from sequences table
		if ($_POST['geneCode'] || $_POST['accession'] || (isset($_POST['genbank']) && $_POST['genbank']=='no') || (isset($_POST['genbank']) && $_POST['genbank']=='yes') ) {
			foreach ($_POST as $key => $value) {
				if (!empty($value)) {
 					if ($key == 'submit') {
						continue;
						}
					if ($key == 'geneCode') {
						$title   .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						$query_b .= $p_ . "sequences." . $key . ", ";
						$query_d .= $p_ . "sequences." . $key . " like '%" . $value . "%' AND ";
						}
					elseif ($key == 'genbank')
						{
						$search_msg .= "Genbank=<b>";
						if( $_POST['genbank'] =='no' )
							{
							$search_msg .= "NO</b> + ";
							$query_b .= $p_ . "sequences." . $key . ", ";
							$query_d .= $p_ . "sequences." . $key . "=false AND ";
							}
						elseif( $_POST['genbank'] =='yes' )
							{
							$search_msg .= "YES</b> + ";
							$query_b .= $p_ . "sequences." . $key . ", ";
							$query_d .= $p_ . "sequences." . $key . "=true AND ";
							}
						}
					elseif ($key == 'accession')
						{
						$title      .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						$query_b .= $p_ . "sequences." . $key . ", ";
						$query_d .= $p_ . "sequences." . $key . " like '" . $value . "%' AND ";
						}
					elseif ($key == 'genus')
						{
						$title   .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						$query_b .= $p_ . "vouchers." . $key . ", ";
						$query_d .= $p_ . "vouchers." . $key . " like '" . $value . "%' AND ";
						}
					elseif ($key=='code_selected' || $key=='collector_selected' || $key=='country_selected' ||
							  $key=='extractor_selected' || $key=='orden_selected' || $key=='family_selected' || $key=='genus_selected' ||
							  $key=='species_selected' || $key=='subfamily_selected' || $key=='subspecies_selected' ||
							  $key=='subtribe_selected'|| $key=='tribe_selected' || $key=='geneCode_selected' ||
							  $key=='genus_selected' || $key=='hostorg_selected' || $key=='edits_selected' || $key=='accession_selected')
						{
						continue;
						}
					else {
						$title   .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						if( isset($query_b) ) {
							$query_b .= $p_ . "vouchers." . $key . ", ";
						}
						else {
							$query_b = $p_ . "vouchers." . $key . ", ";
						}

						if( isset($query_d) ) {
							$query_d .= $p_ . "vouchers." . $key . " like '%" . $value . "%' AND ";
						}
						else {
							$query_d = $p_ . "vouchers." . $key . " like '%" . $value . "%' AND ";
						}
					}
				}
			}
			
			// finish building query, if user wants primer table
			if     ($_POST['code'] && $_POST['genus'] && $_POST['species'])
						 { $query_a = "SELECT "; }
			elseif (!$_POST['code'] && $_POST['genus'] && $_POST['species'])
						 { $query_a = "SELECT " . $p_ . "vouchers.code, "; }
			elseif (!$_POST['code'] && !$_POST['genus'] && $_POST['species'])
				       { $query_a = "SELECT " . $p_ . "vouchers.code, " . $p_ . "vouchers.genus, "; }
			elseif (!$_POST['code'] && $_POST['genus'] && !$_POST['species'])
					 { $query_a = "SELECT " . $p_ . "vouchers.code, " . $p_ . "vouchers.species, "; }
			elseif ($_POST['code'] && !$_POST['genus'] && !$_POST['species'])
						 { $query_a = "SELECT " . $p_ . "vouchers.genus, " . $p_ . "vouchers.species, "; }			 
			elseif ($_POST['code'] && !$_POST['genus'] && $_POST['species'])
						 { $query_a = "SELECT " . $p_ . "vouchers.genus, "; }
			elseif ($_POST['code'] && $_POST['genus'] && !$_POST['species'])
						 { $query_a = "SELECT " . $p_ . "vouchers.species, "; }
			else
						 { $query_a = "SELECT " . $p_ . "vouchers.code, " . $p_ . "vouchers.genus, " . $p_ . "vouchers.species, "; }
			
			$search_msg = substr($search_msg, 0, strlen($search_msg) - 4);
			$query_c = " " . $p_ . "vouchers.timestamp, " . $p_ . "vouchers.extractor, " . $p_ . "vouchers.latesteditor, " . $p_ . "vouchers.id FROM " . $p_ . "vouchers, " . $p_ . "sequences WHERE " . $p_ . "vouchers.code=" . $p_ . "sequences.code AND ";
			$query2   = $query_a . $query_b . $query_c . $query_d;
			$count = strlen($query2)-4;
			$query_p = substr($query2, 0, $count);
			$query = $query_p . "ORDER BY code;";
// 			echo "$query";
			$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
			
			if (mysql_num_rows($result) > 0)
				{
// ---------insert query results to search and search_results tables
				
				// enter id for this search in search table
				$query_search  = "INSERT INTO " . $p_ . "search (timestamp) VALUES (NOW())";
				$result_search = mysql_query($query_search) or die("Error in query: $query_search. " . mysql_error());
				
				// get the id for current search	
				$query_search2  = "SELECT id FROM " . $p_ . "search ORDER BY timestamp DESC LIMIT 1;";
				$result_search2 = mysql_query($query_search2) or die("Error in query: $query_search2. " . mysql_error());
				$row_search2    = mysql_fetch_object($result_search2);
				$search_id      = $row_search2->id;

				// prepare query for entering all results to search_results table
				$query_search_results1  = "INSERT INTO " . $p_ . "search_results (search_id, record_id, timestamp) VALUES ('" . $search_id . "', '";
				
				// process title
				$title = substr($title, 0, strlen($title) - 2);
								
				// print html headers
				include_once'../includes/header.php';
				admin_nav();
				
				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
					
// ---------enter result data into result tables
				$count_result = mysql_num_rows($result);
				while ($row = mysql_fetch_object($result)) {
					// check to avoid listing a record more than once
					$myNewCode = $row->code;
					if( isset($myOldCode) && $myNewCode == $myOldCode ) { // if duplicates add -1 to the counting
						$count_result = $count_result - 1;
					}
					else {
						// insert record_id into search_results table
						$rec_id = $row->id;
						$query_search_results2  = $query_search_results1 . $rec_id . "', NOW())";
						$result_search_results2 = mysql_query($query_search_results2) or die("Error in query: $query_search_results2. " . mysql_error());
					}
					$myOldCode = $myNewCode;
				}
				
// ---------now print results
				echo "<img src=\"images/info.png\" alt=\"\" /> Found <b>" . $count_result . "</b> records:$search_msg<br />\n";
				$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
				unset($myOldCode);

				while( $row = mysql_fetch_object($result) ) {
					// check to avoid listing a record more than once
					$myNewCode = $row->code;
					if( isset($myOldCode) && $myNewCode == $myOldCode ) {
						continue;
					}
					else {
						$code      = $row->code;
						$genus     = $row->genus;
						$species   = $row->species;
						$extractor = $row->extractor;
						$latesteditor = $row->latesteditor;
						$timestamp = $row->timestamp;
						searchList($code, $genus, $species,  $extractor, $latesteditor, $search_id, $timestamp, $date_timezone, $base_url, $php_version, $mask_url, $p_);
					}
					$myOldCode = $myNewCode;
				}
				echo "</td>";
				echo "<td class=\"sidebar\" valign=\"top\">";
				admin_make_sidebar(); // includes td and /td already
				echo "</td>";
				echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				}
			else
				{
				// print html headers
				include_once'../includes/header.php';

				// print navegation bar
				admin_nav();
				// process title
				$title = $config_sitename . "-Search results:";
				
				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
				echo $search_msg . "<br /><img src=\"images/warning.png\" alt=\"\" /> Couldn't find that <b>record</b>, please try again.";
				echo "</td>";
				echo "<td class=\"sidebar\" valign=\"top\">";
				admin_make_sidebar(); // includes td and /td already
				echo "</td>";
				echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				}
			}
		
		else //if user does NOT enter fields to query form sequences table
			{
			foreach ($_POST as $key => $value)
				{
				if (!empty($value))
					{
 					if ($key == 'submit')
						{
						continue;
						}
					elseif ($key == 'genus') {
						$title   .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						if( isset($query_b) ) {
							$query_b .= $p_ . "vouchers." . $key . ", ";
						}
						else {
							$query_b = $p_ . "vouchers." . $key . ", ";
						}
						
						if( isset($query_d) ) {
							$query_d .= $p_ . "vouchers." . $key . " like '" . $value . "%' AND ";
						}
						else {
							$query_d = $p_ . "vouchers." . $key . " like '" . $value . "%' AND ";
						}
						}
					elseif ($key=='code_selected' || $key=='collector_selected' || $key=='country_selected' ||
							  $key=='extractor_selected' || $key=='orden_selected' || $key=='family_selected' || $key=='genus_selected' ||
							  $key=='species_selected' || $key=='subfamily_selected' || $key=='subspecies_selected' ||
							  $key=='subtribe_selected'|| $key=='tribe_selected' || $key=='hostorg_selected' || $key=='geneCode_selected' ||
							   $key=='edits_selected' ||$key=='genus_selected')
						{
						continue;
						}
					else {
						$title   .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						if( isset($query_b) ) {
							$query_b .= $p_ . "vouchers." . $key . ", ";
						}
						else {
							$query_b = $p_ . "vouchers." . $key . ", ";
						}

						if( isset($query_d) ) {
							$query_d .= $p_ . "vouchers." . $key . " like '%" . $value . "%' AND ";
						}
						else {
							$query_d = $p_ . "vouchers." . $key . " like '%" . $value . "%' AND ";
						}
					}
				}
			}
		
			// finish building query
			if     ($_POST['code'] && $_POST['genus'] && $_POST['species'])
						 { $query_a = "SELECT "; }
			elseif (!$_POST['code'] && $_POST['genus'] && $_POST['species'])
						 { $query_a = "SELECT code, "; }
			elseif (!$_POST['code'] && !$_POST['genus'] && $_POST['species'])
				       { $query_a = "SELECT code, genus, "; }
			elseif (!$_POST['code'] && $_POST['genus'] && !$_POST['species'])
					 { $query_a = "SELECT code, species, "; }
			elseif ($_POST['code'] && !$_POST['genus'] && !$_POST['species'])
						 { $query_a = "SELECT genus, species, "; }			 
			elseif ($_POST['code'] && !$_POST['genus'] && $_POST['species'])
						 { $query_a = "SELECT genus, "; }
			elseif ($_POST['code'] && $_POST['genus'] && !$_POST['species'])
						 { $query_a = "SELECT species, "; }
			else
					    { $query_a = "SELECT " . $p_ . "vouchers.code, " . $p_ . "vouchers.genus, " . $p_ . "vouchers.species, "; }
			
			$search_msg = substr($search_msg, 0, strlen($search_msg) - 4);
			$query_c = "extractor, latesteditor, id, timestamp FROM " . $p_ . "vouchers WHERE ";
			$query2   = $query_a . $query_b . $query_c . $query_d;
			$count = strlen($query2)-4;
			$query_p = substr($query2, 0, $count);
			$query = $query_p . "ORDER BY code;";
			
			$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
			
			if (mysql_num_rows($result) > 0)
				{
// ---------insert query results to search and search_results tables
				
				// enter id for this search in search table
				$query_search  = "INSERT INTO " . $p_ . "search (timestamp) VALUES (NOW())";
				$result_search = mysql_query($query_search) or die("Error in query: $query_search. " . mysql_error());
				
				// get the id for current search	
				$query_search2  = "SELECT id FROM " . $p_ . "search ORDER BY timestamp DESC LIMIT 1;";
				$result_search2 = mysql_query($query_search2) or die("Error in query: $query_search2. " . mysql_error());
				$row_search2    = mysql_fetch_object($result_search2);
				$search_id      = $row_search2->id;

				// prepare query for entering all results to search_results table
				$query_search_results1  = "INSERT INTO " . $p_ . "search_results (search_id, record_id, timestamp) VALUES ('" . $search_id . "', '";
				
// ---------print results
				$count_result = mysql_num_rows($result);
				
				// process title
				$title = substr($title, 0, strlen($title) - 2);

				// print html headers
				include_once'../includes/header.php';
				admin_nav();

				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
						<tr><td valign=\"top\">";
				echo "<img src=\"images/info.png\" alt=\"\" /> Found <b>" . $count_result . "</b> records:$search_msg";
				echo "<ol>";

				while ($row = mysql_fetch_object($result)) {
					// insert record_id into search_results table
					$rec_id = $row->id;
					$query_search_results2  = $query_search_results1 . $rec_id . "', NOW())";
					$result_search_results2 = mysql_query($query_search_results2) or die("Error in query: $query_search_results2. " . mysql_error());
					
					$code      = $row->code;
					$genus     = $row->genus;
					$species   = $row->species;
					$extractor = $row->extractor;
					$latesteditor = $row->latesteditor;
					$timestamp = $row->timestamp;
					searchList($code, $genus, $species, $extractor, $latesteditor, $search_id, $timestamp, $date_timezone, $base_url, $php_version, $mask_url, $p_);
					}
				echo "</ol>";
				echo "</td>";
				echo "<td class=\"sidebar\" valign=\"top\">";
				admin_make_sidebar(); // includes td and /td already
				echo "</td>";
				echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				}
			else
				{
				// print html headers
				include_once'../includes/header.php';

				// print navegation bar
				admin_nav();

				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
				echo $search_msg . "<br /><br /><img src=\"images/warning.png\" alt=\"\" /> Couldn't find that <b>record</b>, please try again.";
				echo "</td>";
				echo "<td class=\"sidebar\" valign=\"top\">";
				admin_make_sidebar(); // includes td and /td already
				echo "</td>";
				echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				}
			}
		}
	}

// #################################################################################
// Section: clear old searches - to save data space
// #################################################################################
else
	{
			// open database connection
			@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
			//select database
			mysql_select_db($db) or die ('Unable to content');
			if( function_exists(mysql_set_charset) ) {
				mysql_set_charset("utf8");
			}
			$querydel = "TRUNCATE TABLE " . $p_ . "search";
			$result = mysql_query($querydel) or die ("Error in query: $querydel. " . mysql_error());
			$querydel = "TRUNCATE TABLE " . $p_ . "search_results";
			$result = mysql_query($querydel) or die ("Error in query: $querydel. " . mysql_error());
	// process title
	$title = "$config_sitename - Admin: Search ";

	// print html headers
	include_once'../includes/header.php';

	// print navegation bar
	admin_nav();

	// begin HTML page content
	echo "<div id=\"content\">";
	echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
			<tr><td valign=\"top\">";
	?>
	<!-- 	search boxes and options -->
	<form action="adsearch.php" method="post">
	<table width="800" border="0"> <!-- big parent table -->
	<tr><td>
		<table border="0" cellspacing="10"> <!-- table child 1 -->
		<tr><td>
		
		<table width="350" cellspacing="0" border="0">
		<caption>Search options</caption>
			<tr>
				<td class="label">Order</td>
				<td class="field">
					<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_orden.js" style="width: 90px;" name="orden" maxListLength="20" />
				</td>
				<!-- <td>&nbsp;</td> -->
				<td class="label3">Subfamily</td>
				<td class="field2">
					<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_subfamily.js" style="width: 90px;" name="subfamily" maxListLength="20" />
				</td>
			</tr>
			<tr>
				<td class="label">Family</td>
				<td class="field">
					<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_family.js" style="width: 90px;" name="family" maxListLength="20" />
				</td>
				<!-- <td>&nbsp;</td> -->
				<td class="label3">Tribe</td>
				<td class="field2">
					<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_tribe.js" style="width: 90px;" name="tribe" maxListLength="20" />
				</td>
			</tr>
			<tr>
			<td class="label">Genus</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_genus.js" style="width: 90px;" name="genus" maxListLength="20" />
			</td>
			<!-- <td>&nbsp;</td> -->
			<td class="label3">Subtribe</td>
			<td class="field2">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_subtribe.js" style="width: 90px;" name="subtribe" maxListLength="20" />
			</td>
		</tr>
		<tr>
			<td class="label">Species</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_species.js" style="width: 90px;" name="species" maxListLength="20" />
			</td>
			<!-- <td>&nbsp;</td> -->
			<td class="label3">Host org.</td>
			<td class="field2">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_hostorg.js" style="width: 90px;" name="hostorg" maxListLength="20" />
			</td>
		<tr>
			<td class="label">Subspecies</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_subspecies.js" style="width: 90px;" name="subspecies" maxListLength="20" />
			</td>
			<!-- <td>&nbsp;</td> -->
			<td rowspan="1" class="label3" style="font-size:8px;">Type species</td>
			<td rowspan="1" class="field2" ><input type="radio"  name="typeSpecies" value="1"> Yes<input type="radio" name="typeSpecies" value="0"> No</td>
		</tr>
		</tr>
	</table>
	
	</td></tr>
	<tr><td>
	
	<table width="350" cellspacing="0" border="0">
	<caption>Locality Information</caption>
		<tr><td colspan="3" class="label2">Country</td></tr>
		<tr><td colspan="3" class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_country.js" style="width: 120px;" name="country" maxListLength="20" />
				</select>
			 </td>
		</tr>
		
		<tr><td colspan="3" class="label2">Specific Locality</td></tr>
		<tr><td colspan="3" class="field"><input type="text" name="specificLocality" size="40" /></td></tr>
		
		<tr><td class="label2">Latitude</td>
		    <td class="label3">Longitude</td>
			 <td class="label3">Altitude</td></tr>
		<tr><td class="field"><input type="text" name="latitude" size="12" /></td>
		    <td class="field2"><input type="text" name="longitude" size="12" /></td>
			 <td class="field2"><input type="text" name="altitude" size="12" /></td></tr>
	</table>
	
	</td></tr>
	<tr><td>
	
	<table width="350" cellspacing="0" border="0">
	<caption>Collector Information</caption>
		<tr>
			<td class="code">Code in VoSeq</td>
			<td class="label3">Collector</td>
			<td class="label3">Collection date</td>
		</tr>
		<tr>
			<td class="field3">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_code.js" style="width: 90px;" name="code" maxListLength="20" />
				</select></td>
			<td class="field2">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_collector.js" style="width: 90px;" name="collector" maxListLength="20" />
				</select></td>
			<td class="field2"><input type="text" name="dateCollection" size="12" /></td>
		</tr>
		<tr>
			<td class="label2">Voucher Locality</td>
			<td class="label3">Voucher <img width="15px" height="16px" src="images/question.png" id="voucher" alt="" />
								 <span dojoType="tooltip" connectId="voucher" delay="1" toggle="explode">-Spread?<br /> -Unspread?<br /> -In Slide?</span></td>
			<td class="label3">Sex</td>
		</tr>
		<tr>
			<td class="field"><input type="text" name="voucherLocality" size="12" /></td>
			<td class="field2"><input type="text" name="voucher" size="12" /></td>
			<td class="field2" rowspan="3"><input type="radio" name="sex" value="larva"> Larva<br /><input type="radio" name="sex" value="male"> Male<br /><input type="radio" name="sex" value="female"> Female</td>
		</tr>
		<tr>
			<td class="label2">Flickr photo id</td>
			<td class="label3">Voucher Code</td>
		</tr>
		<tr>
			<td class="field"><input type="text" name="voucherImage" size="12" /></td>
			<td class="field2"><input type="text" name="voucherCode" size="12" /></td>
		</tr>
	</table>


	</td></tr>
	</table> <!-- end table child 1 -->

</td>
<td valign="top">

	<table border="0" cellspacing="10"> <!-- table child 2 -->
	<tr><td valign="top">
	
	<table width="160" cellspacing="0" border="0">
	<caption>DNA</caption>
		<tr>
			<td class="label2">Extraction <img width="15px" height="16px" src="images/question.png" id="extraction" alt="" />
								 <span dojoType="tooltip" connectId="extraction" delay="1" toggle="explode">Lab extraction kept in box number:</span></td>
			<td class="label3">Tube <img width="15px" height="16px" src="images/question.png" id="tube" alt="" />
								 <span dojoType="tooltip" connectId="tube" delay="1" toggle="explode">DNA extraction is in vial number:</span></td>
		</tr>
		<tr>
			<td class="field"><input type="text" name="extraction" size="6" /></td>
			<td class="field2"><input type="text" name="extractionTube" size="8" /></td>
		</tr>
		<tr>
			<td colspan="2" class="label2">Extractor <img width="15px" height="16px" src="images/question.png" id="extractor" alt="" />
								 <span dojoType="tooltip" connectId="extractor" delay="1" toggle="explode">Person that performed the DNA extraction</span></td>
		</tr>
		<tr>
			<td colspan="2" class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_extractor.js" style="width: 130px;" name="extractor" maxListLength="20" />
				</select>
			</td>
		</tr>
		<tr>
			<td class="label">Date</td>
			<td class="field"><input type="text" name="dateExtraction" size="8" /></td>
		</tr>
	</table>
	
	</td>
	<td width="200px">
		&nbsp;
	</td>
	
	</tr>
	<tr><td colspan="2">
	
	<table width="200" cellspacing="0" border="0">
	<caption>Sequence Information</caption>
		<tr>
			<td class="label">Gene Code</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_geneCode.js" style="width: 90px;" name="geneCode" maxListLength="20" />
				</select>
			</td>
		</tr>
		<tr>
			<td class="label">In Genbank?</td>
			<td class="field">
				<input type="radio" name="genbank" value="yes">Yes<br /><input type="radio" name="genbank" value="no">No
			</td>
		</tr>
		<tr>
			<td class="label">Accession</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="../dojo/comboBoxData_accession.js" style="width: 90px;" name="accession" maxListLength="20" />
				</select>
			</td>
		</tr>
	</table>
	
	</td></tr>
	<tr><td colspan="1">
	
	<table width="240px" cellspacing="0" border="0">
	<caption>Publication and Notes</caption>
		<tr>
			<td class="label2">Published in</td></tr>
			<td class="field"><input type="text" name="publishedIn" size="38" /></td>
		</tr>
		<tr>
			<td class="label2">Notes</td></tr>
			<td class="field"><input type="text" name="notes" size="38" /></td>
		</tr>
		<tr>
	</table>
	
		</td></tr>
	<tr><td colspan="1"> <!-- start edits table -->
	
	<table width="240px" cellspacing="0" border="0">
	<caption>Record history</caption>
		<tr>
			<td class="label2"><i>Field</i> edited by <i>name</i> on <i>time (YYYY-MM-DD)</i></td>
		</tr>
		<tr>
			<td class="field"><input type="text" name="edits" size="38" /></td>
		</tr>
		<tr>
			<td><input type="submit" name="submit" value="Search" />
			</td>
		</tr>
	</table>
	
	</td></tr>
	</table><!-- end table child 2 -->

</td></tr>
</table><!-- end big parent table -->
</form>

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
?>


</body>
</html>
