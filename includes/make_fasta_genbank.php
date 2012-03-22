<?php
// #################################################################################
// #################################################################################
// Voseq includes/make_fasta_genbank.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Creates a fasta list with voucher info and sequence
// for GenBank submission
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
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
					<tr><td valign=\"top\">";
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
// Section: Get code(s) and gene(s) and taxonomic info
// #################################################################################
// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}


if (trim($_POST['codes']) != ""){
	$raw_codes = explode("\n", $_POST['codes']);
}else{ unset($raw_codes); }
//$raw_codes = split("\n", $_POST['codes']);

#geneCodes here
unset($geneCodes);
if (isset($_POST['geneCodes'])){
	foreach ( $_POST['geneCodes'] as $k1=> $c1){ //putting choosen genes into array
		if ($c1 == 'on')	{
			$genes[] =  $k1;
		}
	}
}else {$errorList[] = "No genes choosen - Please try again!"; }

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
else{ 
// #################################################################################
// Section: Build fasta list
// #################################################################################
$output = "";
foreach($genes as $geneCode) {
	if ($output != "") {
		$output .= "\n\n";
	}
	foreach($lines as $code) {
		$query = "SELECT " . $p_ . "vouchers.orden, 
						 " . $p_ . "vouchers.family, 
						 " . $p_ . "vouchers.subfamily, 
						 " . $p_ . "vouchers.tribe, 
						 " . $p_ . "vouchers.genus, 
						 " . $p_ . "vouchers.species, 
						 " . $p_ . "sequences.sequences, 
						 " . $p_ . "genes.description FROM 
						 " . $p_ . "vouchers, 
						 " . $p_ . "sequences, 
						 " . $p_ . "genes WHERE 
						 " . $p_ . "vouchers.code='$code' AND 
						 " . $p_ . "sequences.code='$code' AND 
						 " . $p_ . "sequences.geneCode='$geneCode' AND 
						 " . $p_ . "genes.geneCode='$geneCode'";
		$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());

		if (mysql_num_rows($result) > 0) {
			unset($lineage);
			$lineage = " [Lineage=";
			while ($row = mysql_fetch_object($result)) {
				if( $row->orden ) {
					$lineage .= " $row->orden;";
				}
				if( $row->family ) {
					$lineage .= " $row->family;";
				}
				if( $row->subfamily ) {
					$lineage .= " $row->subfamily;";
				}
				if( $row->tribe ) {
					$lineage .= " $row->tribe;";
				}
				$lineage .= " $row->genus] ";
				$species = str_replace(" ", "_", $row->species);
				$output .= ">" . $row->genus . "_" . $species . "_" . $code . " [org=$row->genus $row->species] [Specimen-voucher=$code]";
				$output .= " [note=" . $row->description . " gene, partial cds.] $lineage";
				$output .= "\n$row->sequences\n";
			}
		}
	}
}

// #################################################################################
// Section: Show output or error message
// #################################################################################
if ($output == "" ){
	$errorList[] = "No voucher had any of the choosen genes!</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please choose existing voucher-gene combinations...";
	$title = "$config_sitename: Dataset Error";
	// print html headers
	$admin = false;
	$in_includes = true;
	include_once 'header.php';
	//print errors
	show_errors($errorList);
}
else{ //start building dataset
	if( $php_version == "5" ) {
		// date_default_timezone_set($date_timezone); php5
		date_default_timezone_set($date_timezone);
	}
	# filename for download
	//date_default_timezone_set($date_timezone);php5
	$genbank_file = "genbank_file_" . date('Ymd') . ".txt"; 
	header("Content-Type: application/vnd.ms-notepad");
	header("Content-Disposition: attachment; filename=$genbank_file");
	echo $output;
}
}
?>
