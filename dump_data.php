<?php
// #################################################################################
// #################################################################################
// Voseq dump_data.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Creating data table file for submission to GBIF
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
ob_start();//Hook output buffer - disallows web printing of file info...
include 'conf.php';
ob_end_clean();//Clear output buffer//includes


$request = $_POST['request'];

// #################################################################################
// Section: Query info from DB
// #################################################################################
// if request = count_data_and_filename
if( $request == "count_data" ) {
	// get everything
	@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
	mysql_select_db($db) or die ('Unable to select database; <b>You might need to configure the file "conf.php"</b>');

	if( function_exists(mysql_set_charset)) {
		mysql_set_charset("utf8");
	}

	// generate and execute query
	$query = "SELECT id FROM " . $p_ . "vouchers";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	$count = mysql_num_rows($result);

	mysql_close($connection);

	echo "{ ";
	echo "\"count\": \"$count\"";
	echo "}";
}
//
// if request = count_data_and_filename
elseif( $_GET['request'] == "make_file" ) {
	// get everything
	@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
	mysql_select_db($db) or die ('Unable to select database; <b>You might need to configure the file "conf.php"</b>');

	if( function_exists(mysql_set_charset)) {
		mysql_set_charset("utf8");
	}

	// generate and execute query
	$query = "SELECT id, code, orden, family, subfamily, tribe, subtribe, genus, species, subspecies, typeSpecies,
					country, specificLocality, latitude, longitude, altitude, collector, dateCollection,
					voucherLocality, hostorg, sex, voucher, voucherCode, voucherImage, dateExtraction, extractor,
					extraction, extractionTube, publishedIn, notes  
				FROM " . $p_ . "vouchers ORDER BY id";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
	$output =  "MySQL_ID\tCode\tOrder\tFamily\tSubfamily\tTribe\tSubtribe\tGenus\tSpecies\tSubspecies\tTypeSpecies\t";
	$output .= "Country\tSpecific_Locality\tLatitude\tLongitude\tAltitude\tCollector\tDate_of_Collection\t";
	$output .= "Voucher_Locality\tHost_Organism\tSex\tVoucher_State\tVoucher_code_from_others\tVoucher_Imagen\t";
	$output .= "Date_of_DNA_extraction\tExtractor\tExtraction_#\tExtraction_Vial\tPublished_in\tNotes\n";

	if (mysql_num_rows($result) > 0) {
		while( $row = mysql_fetch_object($result) ) {
			foreach( $row as $k => $v ) {
				$output .= "$v\t";
			}
			$output .= "\n";
		}
	}

// #################################################################################
// Section: Create outfile
// #################################################################################
	# filename for download
	if( $php_version == "5" ) {
		//date_default_timezone_set($date_timezone);php5
		date_default_timezone_set($date_timezone);
	}
	$excel_file = "data_for_GBIF_" . date('Ymd') . ".xls";
	header("Content-Disposition: attachment; filename=\"$excel_file\"");
	header("Content-Type: application/vnd.ms-excel");
	echo $output;
}
?>
