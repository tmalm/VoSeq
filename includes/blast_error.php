<?php
// #################################################################################
// #################################################################################
// Voseq includes/blast_error.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Displays an error message encountered during a BLAST when the
// BLAST software is not installed, or other encoutered errors during a BLAST.
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
ob_start();
include_once("../conf.php");

if( $mask_url == "true" ) {
	ob_end_clean();//Clear output buffer
}
else {
	ob_clean();
}

$title = "BLAST Error";
$admin = false;
$loginmodule = false;
$yahoo_map = false;
$dojo = false;
$in_includes = true;

include_once('header.php');
nav();

echo "\n<div id=\"content\">";
echo "\n<h1>Error</h1>";
// #################################################################################
// Section: Function giving error msg when BLAST files are not installed
// #################################################################################
function show_error_no_blast($local_folder, $date_timezone, $config_sitename, $version, $base_url, $system) {
	// $system = "linux";
	echo "\n<img width=\"16px\" height=\"16px\" src=\"../images/warning.png\" /> This tool runs a BLAST of your selected sequence against all the sequences held in your installation of VoSeq. However, it is necessary that you install the BLAST software:"; //and create a database of all your sequences.";
	echo "\n<ul>
			<li>You need to install the standalone BLAST executables from the 
			<a href=\"http://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Web&PAGE_TYPE=BlastDocs&DOC_TYPE=Download\">NCBI webpage</a>.
			</li>\n
			<li>Note that some commercial servers do not allow to run binary files such as those provided by NCBI BLAST.
			</li>
		";
	if( $system == "win" ) {
		echo "<li>Download and install NCBI BLAST in the folder <code class=\"code\">";
		echo "<br />" . $local_folder . "\\blast\</code></li>";
		echo "\n<li>In this way, you will have the file <code class=\"code\">blastn.exe</code> in the folder:\n
			<br /><code class=\"code\">" . $local_folder . "\\blast\\bin\</b></code></li>";
	}
	else {
		echo "<li>Download and install the BLAST executables in the folder: <code class=\"code\">";
		echo $local_folder . "/blast/bin/</b></code></li>";
	}
	echo "</ul>";
	echo "</div> <!-- end content -->";
	make_footer($date_timezone, $config_sitename, $version, $base_url);
	echo "</body>
		 </html>";
}

// #################################################################################
// Section: Function showing regular error messages encountered during BLAST
// #################################################################################
function show_error($date_timezone, $config_sitename, $version, $base_url, $db_result) {
	echo "\n<ul>";
	foreach($db_result as $error) {
		echo "<li>" . $error . "</li>\n";
	}
	echo "</ul>";
	echo "</div> <!-- end content -->";
	make_footer($date_timezone, $config_sitename, $version, $base_url);
	echo "</body>
		 </html>";
}
?>
