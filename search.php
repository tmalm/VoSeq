<?php
//check if conf.php exists. If not, this is a fresh download and needs installation
if( !file_exists("conf.php") ) {
	header("Location: installation/NoConfFile.php" );
	exit(0);
}



//check login session
include'login/auth.php';
// includes
include'markup-functions.php';
ob_start();//Hook output buffer - disallows web printing of file info...
include 'conf.php';
ob_end_clean();//Clear output buffer//includes
include'functions.php';

// need dojo?
$dojo = true;

$in_includes = false;
$yahoo_map = false;
$admin = false;

// which dojo?
$whichDojo[] = 'ComboBox';
//$whichDojo[] = 'button';
		
// prepare title
$title = "$config_sitename - Search: ";

// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}

// check for any submit search
# clean $_POST;
if(!empty($_POST)) {
	$new_POST = array();
	$keys = array();
	$values = array();

	foreach($_POST as $k => $v) {
		$tmp = clean_string($k);
		$k = $tmp[0];
		unset($tmp);
		$keys[] = $k;
		
		$tmp = clean_string($v);
		$v = $tmp[0];
		unset($tmp);
		$values[] = $v;

	}
	$new_POST = array_combine($keys, $values);
	unset($keys);
	unset($values);
	unset($k);
	unset($v);
	unset($_POST);
}
# end cleaning $_POST;
// delete myfastafile.txt
$cwd = getcwd();
$fastafile = $cwd . '/myfastafile.txt';
$genbank_fastafile = $cwd . '/my_genbank_fastafile.txt';
if (file_exists($fastafile)) {
	unlink($fastafile);
}
if (file_exists($genbank_fastafile)) {
	unlink($genbank_fastafile);
}

// check for any submit search
if (!empty($new_POST)) {

if(   (!$new_POST['orden']  || trim($new_POST['orden'])     == '') && 
	  (!$new_POST['family']  || trim($new_POST['family'])     == '') && 
	  (!$new_POST['subfamily']  || trim($new_POST['subfamily'])  == '') &&
	  (!$new_POST['tribe']      || trim($new_POST['tribe'])      == '') &&
	  (!$new_POST['subtribe']   || trim($new_POST['subtribe'])   == '') &&
	  (!$new_POST['genus']      || trim($new_POST['genus'])      == '') &&
	  (!isset($new_POST['species'])    || trim($new_POST['species'])    == '') &&
	  (!$new_POST['subspecies'] || trim($new_POST['subspecies']) == '') &&
	  (!$new_POST['country'] || trim($new_POST['country']) == '') &&
	  (!$new_POST['specificLocality'] || trim($new_POST['specificLocality']) == '') &&
	  (!$new_POST['longitude']  || trim($new_POST['longitude'])== '') &&
	  (!$new_POST['altitude']   || trim($new_POST['altitude'])== '') &&
	  (!$new_POST['code']       || trim($new_POST['code'])== '') &&
	  (!$new_POST['collector']  || trim($new_POST['collector'])== '') &&
	  (!$new_POST['dateCollection'] || trim($new_POST['dateCollection'])== '') &&
	  (!$new_POST['voucherLocality']|| trim($new_POST['voucherLocality'])== '') &&
	  (!$new_POST['voucher']    || trim($new_POST['voucher'])== '') &&
	  (!$new_POST['sex']        || trim($new_POST['sex'])== '') &&
	  (!$new_POST['voucherImage']  || trim($new_POST['voucherImage'])== '') &&
	  (!$new_POST['voucherCode']   || trim($new_POST['voucherCode'])== '') &&
	  (!$new_POST['extraction']    || trim($new_POST['extraction'])== '') &&
	  (!$new_POST['extractionTube']|| trim($new_POST['extractionTube'])== '') &&
	  (!$new_POST['extractor']     || trim($new_POST['extractor'])== '') &&
	  (!$new_POST['dateExtraction']|| trim($new_POST['dateExtraction'])== '') &&
	  (!$new_POST['geneCode']      || trim($new_POST['geneCode'])== '') &&
	  (!$new_POST['publishedIn']   || trim($new_POST['publishedIn'])== '') &&
	  (!$new_POST['notes']         || trim($new_POST['notes'])== '') && 
	  (!$new_POST['hostorg']       || trim($new_POST['hostorg'])== '') && 
      (!$new_POST['accession']     || trim($new_POST['accession'])== '')
	)
		{
		// get title
		$title = "$config_sitename - Error";
		
		// print html headers
		include_once'includes/header.php';
 		nav();
		
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		echo "<img src=\"images/warning.png\" alt=\"\" /><span class=\"text\"> Please enter a <b>string</b> to search.</span>
				</td>";
		
		echo "<td class=\"sidebar\" valign=\"top\">";
		make_sidebar();
		echo "</td";

		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url);
		}
	else {
		// DO SEARCHES
		$search_msg = "<br />Results for query: ";
		
		//if user enters fields to query from sequences table
		if( $new_POST['geneCode'] || $new_POST['accession'] || isset($new_POST['genbank']) ) {
			foreach ($new_POST as $key => $value) {
				if (!empty($value)) {
 					if ($key == 'submit') {
						continue;
						}
					elseif ($key == 'geneCode') {
						$title      .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						if( isset($query_b) ) {
							$query_b .= $p_ . "sequences." . $key . ", ";
						}
						else {
							$query_b =  $p_ . "sequences." . $key . ", ";
						}
						if( isset($query_d) ) {
							$query_d .= $p_ . "sequences." . $key . "='" . $value . "' AND ";
						}
						else {
							$query_d =  $p_ . "sequences." . $key . "='" . $value . "' AND ";
						}
					}
					elseif ($key == 'genbank') {
						$search_msg .= "Genbank=<b>";
						if( $new_POST['genbank'] =='no' ) {
							$search_msg .= "NO</b> + ";
							if( isset($query_b) ) {
								$query_b .= $p_ . "sequences." . $key . ", ";
							}
							else {
								$query_b =  $p_ . "sequences." . $key . ", ";
							}
							if( isset($query_d) ) {
								$query_d .= $p_ . "sequences." . $key . "=false AND ";
							}
							else {
								$query_d =  $p_ . "sequences." . $key . "=false AND ";
							}
						}
						elseif( $new_POST['genbank'] =='yes' ) {
							$search_msg .= "YES</b> + ";
							if( isset($query_b) ) {
								$query_b .= $p_ . "sequences." . $key . ", ";
							}
							else {
								$query_b =  $p_ . "sequences." . $key . ", ";
							}
							if( isset($query_d) ) {
								$query_d .= $p_ . "sequences." . $key . "=true AND ";
							}
							else {
								$query_d =  $p_ . "sequences." . $key . "=true AND ";
							}
						}
					}
					elseif ($key == 'accession') {
						$title      .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						if( isset($query_b) ) {
							$query_b .= $p_ . "sequences." . $key . ", ";
						}
						else {
							$query_b =  $p_ . "sequences." . $key . ", ";
						}
						if( isset($query_d) ) {
							$query_d .= $p_ . "sequences." . $key . " like '" . $value . "%' AND ";
						}
						else {
							$query_d =  $p_ . "sequences." . $key . " like '" . $value . "%' AND ";
						}
						}
					elseif ($key == 'genus') {
						$title      .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						$query_b .= $p_ . "vouchers." . $key . ", ";
						$query_d .= $p_ . "vouchers." . $key . " like '" . $value . "%' AND ";
						}
					elseif ($key=='code_selected' || $key=='collector_selected' || $key=='country_selected' ||
							  $key=='extractor_selected' || $key=='orden_selected' || $key=='family_selected' || $key=='genus_selected' ||
							  $key=='species_selected' || $key=='subfamily_selected' || $key=='subspecies_selected' ||
							  $key=='subtribe_selected'|| $key=='tribe_selected' || $key=='geneCode_selected' ||
							  $key=='genus_selected' || $key=='hostorg_selected' || $key=='accession_selected')
						{
						continue;
						}
					else {
						$title      .= $value . " + ";
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
			if     ($new_POST['code'] && $new_POST['genus'] && $new_POST['species'])
						 { $query_a = "SELECT "; }
			elseif (!$new_POST['code'] && $new_POST['genus'] && $new_POST['species'])
						 { $query_a = "SELECT " . $p_ . "vouchers.code, "; }
			elseif (!$new_POST['code'] && !$new_POST['genus'] && isset($new_POST['species']) )
				       { $query_a = "SELECT ". $p_ . "vouchers.code, ". $p_ . "vouchers.genus, "; }
			elseif (!$new_POST['code'] && $new_POST['genus'] && !$new_POST['species'])
					 { $query_a = "SELECT ". $p_ . "vouchers.code, ". $p_ . "vouchers.species, "; }
			elseif ($new_POST['code'] && !$new_POST['genus'] && !$new_POST['species'])
						 { $query_a = "SELECT ". $p_ . "vouchers.genus, ". $p_ . "vouchers.species, "; }			 
			elseif ($new_POST['code'] && !$new_POST['genus'] && $new_POST['species'])
						 { $query_a = "SELECT ". $p_ . "vouchers.genus, "; }
			elseif ($new_POST['code'] && $new_POST['genus'] && !$new_POST['species'])
						 { $query_a = "SELECT ". $p_ . "vouchers.species, "; }
			else
						 { $query_a = "SELECT ". $p_ . "vouchers.code, ". $p_ . "vouchers.genus, ". $p_ . "vouchers.species, "; }
			
			$search_msg = substr($search_msg, 0, strlen($search_msg) - 3);
			$query_c = " ". $p_ . "vouchers.timestamp, ". $p_ . "vouchers.extractor, ". $p_ . "vouchers.latesteditor, ". $p_ . "vouchers.voucherImage, ". $p_ . "vouchers.id FROM ". $p_ . "vouchers, ". $p_ . "sequences WHERE ". $p_ . "vouchers.code=". $p_ . "sequences.code AND ";
			$query2  = $query_a . $query_b . $query_c . $query_d;
			$query2  = substr($query2, 0, strlen($query2) - 4);
			$query   = $query2 . "ORDER BY ". $p_ . "vouchers.code;";
#echo "$query\n";
			$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
			
			if (mysql_num_rows($result) > 0)
				{
// ---------insert query results to search and search_results tables
				
				// enter id for this search in search table
				$query_search  = "INSERT INTO " . $p_ . "search (timestamp) VALUES (NOW())";
				$result_search = mysql_query($query_search) or die("Error in query: $query_search. " . mysql_error());
				
				// get the id for current search	
				$query_search2  = "SELECT id FROM ". $p_ . "search ORDER BY timestamp DESC LIMIT 1;";
				$result_search2 = mysql_query($query_search2) or die("Error in query: $query_search2. " . mysql_error());
				$row_search2    = mysql_fetch_object($result_search2);
				$search_id      = $row_search2->id;

				// prepare query for entering all results to search_results table
				$query_search_results1  = "INSERT INTO ". $p_ . "search_results (search_id, record_id, timestamp) VALUES ('" . $search_id . "', '";
				
				// process title
				$title = substr($title, 0, strlen($title) - 2);
				
				// print html headers
				include_once'includes/header.php';
				nav();
		
				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
			
// ---------enter result data into result tables
				$count_result = mysql_num_rows($result);
				while ($row = mysql_fetch_object($result)) {
					// check to avoid listing a record more than once
					if( !isset($myOldCode) ) {
						$myOldCode = "";
					}
					$myNewCode = $row->code;
					if( $myNewCode == $myOldCode ) // if duplicates add -1 to the counting
						{
						$count_result = $count_result - 1;
						}
					else
						{
						// insert record_id into search_results table
						$rec_id = $row->id;
						$query_search_results2  = $query_search_results1 . $rec_id . "', NOW())";
						$result_search_results2 = mysql_query($query_search_results2) or die("Error in query: $query_search_results2. " . mysql_error());
						}
					$myOldCode = $myNewCode;
					}
						
// ---------now print results
				echo "<img src=\"images/info.png\" alt=\"\" /> Found <b>" . $count_result . "</b> records:$search_msg<br />\n";
				if( $new_POST['geneCode'] ) {
					echo "<br /><a href=\"myfastafile.txt\">::Get me sequences in Fasta file!::</a>";
					echo "<br /><a href=\"my_genbank_fastafile.txt\">::Get me sequences in GenBank Fasta file!::</a>";
				}
				echo "<ol>";
				$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
				unset($myOldCode);
				$myOldCode = "";
				while( $row = mysql_fetch_object($result) ) {
					// check to avoid listing a record more than once
					$myNewCode = $row->code;
					if( $myNewCode == $myOldCode ) { // if duplicates add -1 to the counting 
						continue;
						}
					else {
						echo "<li>";

						// masking URLs, this variable is set to "true" or "false" in conf.php file
						if( $mask_url == "true" ) {
							echo "<b><a href=home.php onclick=\"return redirect('story.php?code=$row->code&amp;search=$search_id')\">";
							echo $row->code . "</a></b> <i>$row->genus $row->species</i>";
						}
						else {
							echo "<b><a href='story.php?code=$row->code&amp;search=$search_id'>";
							echo $row->code . "</a></b> <i>$row->genus $row->species</i>";
						}

						if( $row->voucherImage != 'na.gif' ) {
							echo " <a href=\"$row->voucherImage\"  target=\"_blank\">";
							echo "<img src=\"images/image.png\" alt=\"See photo\" class=\"link\" title=\"See photo\" />";
							echo "</a>";
						}
					}
					?>
					<br />
					By <?php echo $row->latesteditor .' on '; echo formatDate($row->timestamp, $date_timezone, $php_version); ?>
					</li>
					<?php
					if( $new_POST['geneCode'] ) {
						dofastafiles($row->geneCode, $row->code, $p_);
					}
				$myOldCode = $myNewCode;
				}
				echo "</ol>";
				echo "</td>";
			
				echo "<td class=\"sidebar\" valign=\"top\">";
				make_sidebar();
				echo "</td";

				echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url);
				}
			else
				{
				// get title
				$title = "$config_sitename - No results";
				
				// print html headers
				include_once'includes/header.php';
				nav();
		
				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
				echo "$search_msg<br /><img src=\"images/warning.png\" alt=\"\" />Couldn't find that <b>record</b>, please try again.</td>";
			
				echo "<td class=\"sidebar\" valign=\"top\">";
				make_sidebar();
				echo "</td";

				echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url);
				}
			}
			
		else //if user does NOT enter fields to query form sequences table
			{
//   			print_r($new_POST);
			foreach ($new_POST as $key => $value) {
				if (!empty($value)) {
 					if ($key == 'submit') {
						continue;
					}
					elseif ($key == 'genus') {
						$title   .= $value . " + ";
						$search_msg .= "<b>" . $value . "</b> + ";
						$query_b .= $p_ . "vouchers." . $key . ", ";
						$query_d .= $p_ . "vouchers." . $key . " like '" . $value . "%' AND ";
					}
					elseif ($key=='code_selected' || $key=='collector_selected' || $key=='country_selected' ||
							  $key=='extractor_selected' || $key=='orden_selected' || $key=='family_selected' || $key=='genus_selected' ||
							  $key=='species_selected' || $key=='subfamily_selected' || $key=='subspecies_selected' ||
							  $key=='subtribe_selected'|| $key=='tribe_selected' || $key=='geneCode_selected' || $key=='hostorg_selected' ||
							  $key=='genus_selected') {
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
			if     ($new_POST['code'] && $new_POST['genus'] && $new_POST['species'])
						 { $query_a = "SELECT "; }
			elseif (!$new_POST['code'] && $new_POST['genus'] && $new_POST['species'])
						 { $query_a = "SELECT ". $p_ . "vouchers.code, "; }
			elseif (!$new_POST['code'] && !$new_POST['genus'] && isset($new_POST['species']) )
				       { $query_a = "SELECT ". $p_ . "vouchers.code, ". $p_ . "vouchers.genus, "; }
			elseif (!$new_POST['code'] && $new_POST['genus'] && !$new_POST['species'])
					 { $query_a = "SELECT ". $p_ . "vouchers.code, ". $p_ . "vouchers.species, "; }
			elseif ($new_POST['code'] && !$new_POST['genus'] && !$new_POST['species'])
						 { $query_a = "SELECT ". $p_ . "vouchers.genus, ". $p_ . "vouchers.species, "; }			 
			elseif ($new_POST['code'] && !$new_POST['genus'] && $new_POST['species'])
						 { $query_a = "SELECT ". $p_ . "vouchers.genus, "; }
			elseif ($new_POST['code'] && $new_POST['genus'] && !$new_POST['species'])
						 { $query_a = "SELECT ". $p_ . "vouchers.species, "; }
			else
					    { $query_a = "SELECT " . $p_ . "vouchers.code, " . $p_ . "vouchers.genus, " . $p_ . "vouchers.species, "; }
			
			$search_msg = substr($search_msg, 0, strlen($search_msg) - 3);
			$query_c = $p_ . "vouchers.extractor, " . $p_ . "vouchers.latesteditor, ". $p_ . "vouchers.timestamp, voucherImage, ". $p_ . "vouchers.id FROM ". $p_ . "vouchers WHERE ";
			$query2  = $query_a . $query_b . $query_c . $query_d;
			$query2  = substr($query2, 0, strlen($query2) - 4);
			$query = $query2 . "ORDER BY code COLLATE utf8_swedish_ci";
#echo "$query\n";
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
				$query_search_results1  = "INSERT INTO ". $p_ . "search_results (search_id, record_id, timestamp) VALUES ('" . $search_id . "', '";
				
// ---------print results
				$count_result = mysql_num_rows($result);
				
				// process title
				$title = substr($title, 0, strlen($title) - 2);
				
				// print html headers
				include_once'includes/header.php';
				nav();
				
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
					echo "<li>";

					// masking URLs, this variable is set to "true" or "false" in conf.php file
					if( $mask_url == "true" ) {
						echo "<b><a href=home.php onclick=\"return redirect('story.php?code=$row->code&amp;search=$search_id')\">";
						echo "$row->code</a></b> <i>$row->genus $row->species</i>";
					}
					else {
						echo "<b><a href='story.php?code=$row->code&amp;search=$search_id'>";
						echo "$row->code</a></b> <i>$row->genus $row->species</i>";
					}

							if( $row->voucherImage != 'na.gif' ) {
								echo " <a href=\"$row->voucherImage\" target=\"_blank\">";
								echo "<img src=\"images/image.png\" alt=\"See photo\" class=\"link\" title=\"See photo\" />";
								echo "</a>";
							}
						?>
					   <br />
						By <?php echo $row->latesteditor.' on '; echo formatDate($row->timestamp, $date_timezone, $php_version); ?>
					</li>
					
					<?php
					}
				echo "</ol>";
				echo "</td>";
			
				echo "<td class=\"sidebar\" valign=\"top\">";
				make_sidebar();
				echo "</td";

				echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url);
				}
			else
				{
				// get title
				$title = "$config_sitename - No results";
				
				// print html headers
				include_once'includes/header.php';
				nav();
				
				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
						<tr><td valign=\"top\">";
				echo "$search_msg<br /><img src=\"images/warning.png\" alt=\"\" /> Couldn't find that <b>record</b>, please try again.</td>";
			
				echo "<td class=\"sidebar\" valign=\"top\">";
				make_sidebar();
				echo "</td";

				echo "</tr>
					  </table> <!-- end super table -->
					  </div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url);
				}	
			}
		}
	}
else // empty POST, user has not entered query yet
{
		//clear old searches - to save data space
			// open database connection
			@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
			//select database
			mysql_select_db($db) or die ('Unable to content');
			if( function_exists(mysql_set_charset) ) {
				mysql_set_charset("utf8");
			}
			$querydel = "TRUNCATE TABLE ". $p_ . "search";
			$result = mysql_query($querydel) or die ("Error in query: $querydel. " . mysql_error());
			$querydel = "TRUNCATE TABLE ". $p_ . "search_results";
			$result = mysql_query($querydel) or die ("Error in query: $querydel. " . mysql_error());
	// need dojo?
	$dojo = true;
	// which dojo?
	// print html headers
	include_once'includes/header.php';
	nav();
		
	// begin HTML page content
	echo "<div id=\"content\">";
	echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
			<tr><td valign=\"top\">";
	?>
	<!-- 	search boxes and options -->
<form action="search.php" method="post">
<table width="800" border="0"> <!-- big parent table -->
<tr><td>
	<table border="0" cellspacing="10"> <!-- table child 1 -->
	<tr><td>

	<table width="350" cellspacing="0" border="0">
	<caption>Search for records</caption>
		<tr>
					<td class="label">Order</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_orden.js" style="width: 90px;" name="orden" maxListLength="20" />
			</td>
			<!-- <td>&nbsp;</td> -->
			<td class="label3">Subfamily</td>
			<td class="field2">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_subfamily.js"  style="width: 90px;" name="subfamily" maxListLength="20" />
			</td>
		</tr>
		<tr>
						<td class="label">Family</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_family.js" style="width: 90px;" name="family" maxListLength="20" />
			</td>
			<!-- <td>&nbsp;</td> -->
			<td class="label3">Tribe</td>
			<td class="field2">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_tribe.js" style="width: 90px;" name="tribe" maxListLength="20" />
			</td>
		</tr>
		<tr>
			<td class="label">Genus</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_genus.js" style="width: 90px;" name="genus" maxListLength="20" />
			</td>
			<!-- <td>&nbsp;</td> -->
			<td class="label3">Subtribe</td>
			<td class="field2">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_subtribe.js" style="width: 90px;" name="subtribe" maxListLength="20" />
			</td>
		</tr>
		<tr>
			<td class="label">Species</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_species.js" style="width: 90px;" name="species" maxListLength="20" />
			</td>
			<!-- <td>&nbsp;</td> -->
			<td class="label3">Host org.</td>
			<td class="field2">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_hostorg.js" style="width: 90px;" name="hostorg" maxListLength="20" />
			</td>
		<tr>
			<td class="label">Subspecies</td>
			<td class="field">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_subspecies.js" style="width: 90px;" name="subspecies" maxListLength="20" />
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
					dataUrl="dojo_data/comboBoxData_country.js" style="width: 120px;" name="country" maxListLength="20" />
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
					dataUrl="dojo_data/comboBoxData_code.js" style="width: 90px;" name="code" maxListLength="20" />
			</td>
			<td class="field2">
				<input dojoType="ComboBox" value="nada" autocomplete="false"
					dataUrl="dojo_data/comboBoxData_collector.js" style="width: 90px;" name="collector" maxListLength="20" />
			</td>
			<td class="field2"><input type="text" name="dateCollection" size="10" /></td>
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
			<td rowspan="3" class="field2"><input type="radio" name="sex" value="larva"> Larva<br /><input type="radio" name="sex" value="male"> Male<br /><input type="radio" name="sex" value="female"> Female</td>
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
					dataUrl="dojo_data/comboBoxData_extractor.js" style="width: 130px;" name="extractor" maxListLength="20" />
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
					dataUrl="dojo_data/comboBoxData_geneCode.js" style="width: 90px;" name="geneCode" maxListLength="20" />
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
					dataUrl="dojo_data/comboBoxData_accession.js" style="width: 90px;" name="accession" maxListLength="20" />
			</td>
		</tr>
	</table>
		
	</td></tr>
	<tr><td colspan="1">
	
	<table width="200px" cellspacing="0" border="0">
	<caption>Publication and Notes</caption>
		<tr>
			<td class="label2">Published in</td></tr>
			<td class="field"><input type="text" name="publishedIn" size="30" /></td>
		</tr>
		<tr>
			<td class="label2">Notes</td></tr>
			<td class="field"><input type="text" name="notes" size="30" /></td>
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

<td class="sidebar" valign="top">
	<?php
		make_sidebar(); 
	?>
</td>

</tr>
</table> <!-- end super table -->

</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
// close database connection
mysql_close($connection);

make_footer($date_timezone, $config_sitename, $version, $base_url);
}
?>

</body>
</html>
