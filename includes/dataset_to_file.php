<?php
// #################################################################################
// #################################################################################
// Voseq includes/dataset_to_file.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Formatting outputs from process_dataset.php and creating 
// downloadable files
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
//check login session
include '../login/auth.php';
// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes

if( $php_version == "5" ) {
	// date_default_timezone_set($date_timezone); php5
	date_default_timezone_set($date_timezone);
}
// #################################################################################
// Section: Checks for submitted datasets and partition sets and creates files
// #################################################################################
if (isset($_POST['submit'])){
	if ( $_POST['format'] == "FASTA" ) {	$file_ext = ".fst";	}
	elseif ( $_POST['format'] == "TNT" ) {	$file_ext = ".tnt";	}
	elseif ( $_POST['format'] == "PHYLIP" ) {	$file_ext = ".phy";	}
	else {	$file_ext = ".nex";	}
	$outfile = "db_dataset_" . date('Ymd') .  $file_ext;
	header("Content-Type: application/vnd.ms-notepad");
	header("Content-Disposition: attachment; filename=$outfile");
	$dataset = $_POST['dataset'];
	$dataset = str_replace("\\\\","\\", $dataset);
	echo $dataset;
}
if 	( $_POST['phy_parts'] ) {
	$outfile2 = "db_dataset_" . date('Ymd') . ".phy.partitions";
	header("Content-Disposition: attachment; filename=\"$outfile2\"");
	header("Content-Type: application/vnd.ms-notepad");
	$dataset = $_POST['phy_partitions'];
	$dataset = str_replace("\\\\","\\", $dataset);
	echo $dataset;
}


?>
