<?php
// #################################################################################
// #################################################################################
// Voseq includes/make_table.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Makes table with choosen codes and genes and choosen
// other information
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
error_reporting (E_ALL); // ^ E_NOTICE);
//check login session
include'../login/auth.php';
// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes

// #################################################################################
// Section: Functions - clean_item() and show_errors()
// #################################################################################
function clean_item ($item) {
	$item = stripslashes($item);
	$item = str_replace("'", "", $item);
	$item = str_replace('"', "", $item);
	$item = str_replace(',', "", $item);
	$item = preg_replace('/^\s+/', '', $item);
	$item = preg_replace('/\s+$/', '', $item);
	$item = strtolower($item);
	return $item;
}

function show_errors($se_in) {
	// error found
	include'../markup-functions.php';
	// print navegation bar
	nav();
	// begin HTML page content
	echo "<div id=\"content_narrow\">";
	echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
			<tr>
				<td valign=\"top\">";
		
				// print as list
				echo "<img src=\"../images/warning.png\" alt=\"\"> The following errors were encountered:";
				echo '<br>';
				echo '<ul>';
					$se_in = array_unique($se_in);
					$se_in[] = "</br>Please revise your data!"; 
				foreach($se_in AS $item) {
					echo "$item</br>";
				}
				echo "</td>";
	
				echo "<td class='sidebar'>";
				make_sidebar();
				echo "</td>";
				echo "</tr>
			</table> <!-- end super table -->
			</div> <!-- end content -->";
	//make footer
	make_footer($date_timezone, $config_sitename, $version, $base_url);
	?></body></html><?php
}

// #################################################################################
// Section: Get code(s) and gene(s)
// #################################################################################
if ( $_POST['field_delimitor'] == 'comma') {
	$field_delimitor = ",";
	}
else {
	$field_delimitor = "	";
}

//$raw_geneCodes = explode("\n", $_POST['geneCodes']);
if (trim($_POST['codes']) != ""){
	$raw_codes = explode("\n", $_POST['codes']);
}else{ unset($raw_codes); }
// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');

if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}



#geneCodes here
unset($geneCodes);
if (isset($_POST['geneCodes'])){
	foreach ( $_POST['geneCodes'] as $k1=> $c1){ //putting choosen genes into array
		if ($c1 == 'on')	{
			$genes[] =  $k1;
		}
	}
} else {unset($genes, $geneCodes);}//$errorList[] = "No genes choosen - Please try again!"; }

// checking taxonset choice
$taxonset = $_POST['taxonsets'];
$taxonset_taxa = array();
if ($taxonset != "Choose taxonset"){
	$TSquery = "SELECT taxonset_list FROM ". $p_ . "taxonsets WHERE taxonset_name='$taxonset'";
	$TSresult = mysql_query($TSquery) or die("Error in query: $TSquery. " . mysql_error());
		// if records present
		
		if( mysql_num_rows($TSresult) > 0 ) {
			while( $TSrow = mysql_fetch_object($TSresult) ) {
				$taxonset_taxa = explode(",", $TSrow->taxonset_list );
			}
		}
	else {$errorList[] = "No taxon set named <b>$taxonset</b> exists in database!";}
}else {unset($taxonset_taxa);}

// merging choosen taxon set taxa and input taxa lists
if (isset($taxonset_taxa) && isset($raw_codes)){$raw_codes = array_merge( $taxonset_taxa, $raw_codes) ;}
elseif (isset($taxonset_taxa) && ! isset($raw_codes)){$raw_codes = $taxonset_taxa ;}
elseif (! isset($taxonset_taxa) && isset($raw_codes)){$raw_codes = $raw_codes ;}
else { $errorList[] = "No taxa are chosen!</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pointless to make a table without taxa..."; }

#codes here
$lines = array();

if (isset($raw_codes)){
$raw_codes = array_unique($raw_codes);
foreach($raw_codes AS $item) {
	$item = clean_item($item);
	$item = trim($item);
	if ($item != "") {
		$cquery = "SELECT code FROM ". $p_ . "vouchers WHERE code='$item'";
		$cresult = mysql_query($cquery) or die("Error in query: $query. " . mysql_error());
		// if records present
		if( mysql_num_rows($cresult) > 0 ) {
			while( $row = mysql_fetch_object($cresult) ) {		
				array_push($lines, $item);
			}
		}
		else {
		$errorList[] = "No voucher named <b>$item</b> exists in database!</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please add it in the voucher section or remove it from taxon set!";
		}
	}
}unset($item);

$lines = array_unique($lines);
}
//check for error and if none proceed with building table
if (sizeof($errorList) != 0 ){
	$title = "$config_sitename: Dataset Error";
	// print html headers
	$admin = false;
	$in_includes = true;
	include_once 'header.php';
	//print errors
	show_errors($errorList);
}
// #################################################################################
// Section: Build table with info
// #################################################################################
else{ //start building dataset
foreach ( $_POST['tableadds'] as $k=> $c) {//loops through checkbox values and adds checked values to taxon name
	if ($c == 'on')	{
			if ($k == specificLocality) { $xls_file .=  "Locality" . $field_delimitor;	}
			elseif ($k == dateCollection) { $xls_file .=  "Coll. date" . $field_delimitor;	}
			else {	$xls_file .= ucfirst($k) . $field_delimitor ;}
	}
}
if (isset($genes)) {
	foreach( $genes as $item ) {
		$xls_file .= strtoupper($item) . $field_delimitor;
	}
}
$xls_file .= "\n";

foreach ( $lines as $line ) {
	$line = str_replace('"', "", $line);
	$code = strtoupper(trim($line));

	$query2 = "select code, genus, species, orden, family, subfamily, tribe, subtribe, subspecies, hostorg, collector, specificLocality, dateCollection, latitude, longitude, altitude, auctor, determinedBy, country FROM ". $p_ . "vouchers where code='$code'";
	$result2 = mysql_query($query2) or die("Error in query: $query2. " . mysql_error());
	while( $row2 = mysql_fetch_object($result2) ) {
		foreach ( $_POST['tableadds'] as $k=> $c) {//loops through checkbox values and adds checked values to table
			if ($c == 'on')	{
					$xls_file .= $row2->$k . $field_delimitor;	
		//$species = "$row2->genus $row2->species";
		//$coll_locality = "$row2->country: $row2->specificLocality";
	}}}
	if (isset($genes)) {
		$geneCodes = array();
		foreach( $genes as $gene ) {
			$gene = trim($gene);

			# if accession not, and no sequences, print - or leave empty
			if ( $_POST['geneinfo'] == 'nobp') {	$geneCodes[$gene] = "";	}
			else {	$geneCodes[$gene] = "-";	}

			$query1 = "SELECT accession, sequences FROM ". $p_ . "sequences WHERE code='$code' AND geneCode='$gene'";
			$result1 = mysql_query($query1) or die("Error in query: $query1. " . mysql_error());
		
			while( $row1 = mysql_fetch_object($result1) ) {
				# if accession yes, then print accession number
				if ( $_POST['geneinfo'] == 'accno') {
					if ($row1->accession == true && $row1->accession != "NULL") {
						$geneCodes[$gene] = $row1->accession;
						}
					}
					elseif ( $_POST['geneinfo'] == 'x-') {
						if ( strlen($row1->sequences) > 10 ) {
							$geneCodes[$gene] = 'X';
						}
					}
					# if accession not, but there is sequences, print X
					else {
						if ( strlen($row1->sequences) > 10 ) {
						//$geneCodes[$gene] = "X";
						
						 if ($_POST['star'] == 'star' ) {
							unset($firstbase, $lastbase);
							if ( $row1->sequences[0] == "?" ) { $firstbase = "*";}
							if ( $row1->sequences[strlen($row1->sequences)-1]  == "?" ) { $lastbase = "*";}
							$geneCodes[$gene] = $firstbase . strlen(str_replace("?" , "" , $row1->sequences)) . $lastbase;
						 }
						else{
							$geneCodes[$gene] = strlen(str_replace("?" , "" , $row1->sequences));
						}
					}
				}
			}
		}
		//$xls_file .= "\"$species\",\"$code\",\"$coll_locality\",";
		foreach($geneCodes as $key => $val) {
			$xls_file .= $val . $field_delimitor ;
		}
	}
	$xls_file .= "\n";
	}
	
	// #################################################################################
	// Section: Create downloadable file with table
	// #################################################################################
	# filename for download
	if( $php_version == "5" ) {
		//date_default_timezone_set($date_timezone);php5
		date_default_timezone_set($date_timezone);
	}
	$excel_file = "db_table_" . date('Ymd') . ".xls";
	header("Content-Disposition: attachment; filename=\"$excel_file\"");
	header("Content-Type: application/vnd.ms-excel");
	echo $xls_file;
}
?>
