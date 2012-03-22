<?php
// #################################################################################
// #################################################################################
// Voseq includes/blast_locally.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Creates a BLAST against local DB, for one gene
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
error_reporting(E_ALL);
//check admin login session
include'../login/auth-admin.php';

include_once'../functions.php';
include_once'../markup-functions.php';
ob_start();//Hook output buffer - disallows web printing of file info...
include_once'../conf.php';
ob_end_clean();//Clear output buffer//includes

$errorList = array();
$in_includes = true;


//
// $system variable for "win" or "linux" operative system of server
$system = "";

// #################################################################################
// Section: Look for executable blast file
// if no give instructions to download and install BLAST from NCBI, 
// and how to create a local database
// #################################################################################
if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
	set_time_limit(60);

	$system = "win";
	$blastn_file = $local_folder . "\\blast\\bin\\blastn.exe";
	if( !file_exists($blastn_file) ) {
		include_once("blast_error.php");
		show_error_no_blast($local_folder, $date_timezone, $config_sitename, $version, $base_url, $system);
		exit(0);
	}
}
else {
	$system = "linux";
	$blastn_file = $local_folder . "/blast/bin/blastn";
	if( !file_exists($blastn_file) ) {
		include_once("blast_error.php");
		show_error_no_blast($local_folder, $date_timezone, $config_sitename, $version, $base_url, $system);
		exit(0);
	}
}

// #################################################################################
// Section: Error list
// #################################################################################
function show_errors($se_in) {
	// error found
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
		$se_in[] = "</br>Please try again!"; 
	foreach($se_in AS $item) {
		echo "$item</br>";
	}
	echo "</td>";
	echo "<td class=\"sidebar\" valign=\"top\">";
	make_sidebar(); // NOT includes td and /td already
	echo "</td>";
	echo "</tr>
		</table> <!-- end super table -->
		</div> <!-- end content -->";
	//make footer
	make_footer($date_timezone, $config_sitename, $version, $base_url);
	?></body></html><?php
}

// #################################################################################
// Section: Get code and geneCode and prepare query sequence and info
// #################################################################################
foreach($_GET as $k => $v) {
	$k = clean_string($k);
	$v = clean_string($v);
	if($k[0] == 'code') {
		$code = $v[0];
	}
	if($k[0] == 'geneCode') {
		$geneCode = $v[0];
	}
}
unset($_GET);

@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
mysql_select_db($db) or die ('Unable to connect!');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}

//fixing and checking input values
unset($name, $sequence, $genes);
if (isset($_POST['new_sequence']) && $_POST['new_sequence'] != "") {
	$sequence      = strtoupper(trim($_POST['new_sequence']));
	$sequence      = preg_replace("/\s/", "", $sequence);
	$replace_chars = array("?", "-", "~");
	$sequence = str_replace($replace_chars, "", $sequence);
	if (strlen($sequence)<11) {$errorList[] = "The BLASTed sequence was too short (<11n)!";}
	if (isset($_POST['name'])){
		$name = trim($_POST['name']);
		$name = str_replace(" ", "_", $name);
	}
	else {
		$name = "new_sequence";
	}
	if ($name == "") {
		$name = "new_sequence";
	}
	#geneCodes here
	unset($geneCodes);$genes = array();
	if (isset($_POST['geneCodes'])) {
		foreach ( $_POST['geneCodes'] as $k1=> $c1) { //putting choosen genes into array
			if ($c1 == 'on')	{
				$genes_array[] =  $k1;
			}
		}
		$genes = implode(",",$genes_array);
	}
	else {
		$genes = "all";
	}
}
else {
	$errorList[] = "No blastable sequence added!";
}



//
//check for error and if none proceed, otherwise exit(0)
if (sizeof($errorList) != 0 ){
	$title = "$config_sitename: Dataset Error";
	// print html headers
	$admin = false;
	$in_includes = true;
	include_once 'header.php';
	//print errors
	show_errors($errorList);
	exit(0);
}

// #################################################################################
// Section: create a fresh query sequence file: query.fa in folder /blasts 
// for Mac/Linux or Windows
// #################################################################################
if( $system == "win" ) {
	$file = "blasts\query.fa";
}
else {
	$file = "blasts/query.fa";
}
if( file_exists($file) ) {
	unlink($file);
}


$handle = fopen($file, "w");
chmod($file, 0777);
// building the blasted specimen/sequence file
fwrite($handle, ">$name\n$sequence)");
fclose($handle);

if( !file_exists($file) ) {
	$errorList[] = "File $file! not created!";
}

// #################################################################################
// Section: create a fasta file from selected gene 
// #################################################################################
if ($genes == "all") {
	$query_db = "SELECT sequences, code, geneCode FROM ". $p_ . "sequences where length(sequences) > 101 ORDER BY code";
}
else {
	$geneCodes2 = implode("' OR geneCode='", $genes_array);
	$query_db = "SELECT sequences, code, geneCode FROM ". $p_ . "sequences WHERE length(sequences) > 101 AND geneCode='$geneCodes2' ORDER BY code"; 
}

$result_db = mysql_query($query_db) or die("Error in query: $query.  " . mysql_error());
$seqfasta_db = "";
if(mysql_num_rows($result_db) > 0) {
	while($row_db = mysql_fetch_object($result_db)) {
		$seq_db = strtoupper($row_db->sequences);
		$replace_chars = array("?", "-", "~");
		$seq_db = str_replace($replace_chars, "", $seq_db);
		$geneCode = $row_db->geneCode;
		$query_dbv = "SELECT family, subfamily, genus, species FROM ". $p_ . "vouchers WHERE code='$row_db->code'";
		$result_dbv = mysql_query($query_dbv) or die("Error in query: $query.  " . mysql_error());
		if(mysql_num_rows($result_dbv) > 0) {
			while($row_dbv = mysql_fetch_object($result_dbv)) {
				$family_dbv = $row_dbv->family;
				$subfamily_dbv = $row_dbv->subfamily;
				$genus_dbv = $row_dbv->genus;
				$species_dbv = $row_dbv->species;
				$tax_dbv = ">" . $row_db->code . "#" . $family_dbv . "#". $subfamily_dbv . "#" . $genus_dbv . "#" . $species_dbv . "#" . $geneCode;
			}
		}
		else { 
			//$errorList[] = "Couldn't retrieve voucher information about of $code!";
		}
	$seqfasta_db .= $tax_dbv . "\n" . $seq_db . "\n";
	}
}else {
	if (!isset($name)){
		$errorList[] = "Couldn't find any other sequences of $geneCode in database!";
	}
}
$file2 = "blasts/seqfasta_db.fa";
if( file_exists($file2) ) {
	unlink($file2);
}

$handle = fopen($file2, "w");
chmod($file2, 0777);
fwrite($handle, $seqfasta_db);
fclose($handle);

if( !file_exists($file2) ) {
	$errorList[] = "File $file2! not created!";
}
unset($file2);

// #################################################################################
// Section: Do the local BLAST for windows and Mac/Unix
// #################################################################################
if ( $system == "win" ) {
	//$phpos = 'This is a server using Windows!';
	//echo "This is a server using Windows!<br />";
	# do blast 
	$blast_folder = "\"" . $local_folder . "\\blast\\bin\\";
	$blast_db = "blasts\\";

	# create blast dataset
	$command_make_db = $blast_folder . "makeblastdb.exe\" -in " . $blast_db . "seqfasta_db.fa -dbtype nucl -input_type fasta";
	exec($command_make_db, $output2);

	# create alias
	// $command_make_alias = $blast_folder . "blastdb_aliastool.exe\" -dblist \"" . $blast_db . "seqfasta_db.fa\" -dbtype nucl -out blasts\\seqfasta_db.fa -title \"seqfasta_db\"";
	// exec($command_make_alias, $output4); 

	# make index
	// $command_make_index = $blast_folder . "makembindex.exe\" -input \"blasts\\seqfasta_db.fa\" -iformat fasta -output blasts\\seqfasta_db";
	// exec($command_make_index, $output5); 

	# do blast
	# for some reason it needs to be in the blasts/ folder! 
	# So we do a "cd" to the folder
	chdir('blasts');
	$command = $blast_folder . "blastn.exe\" -query query.fa -db seqfasta_db.fa -task blastn -evalue 0.03 -dust no -outfmt 7 -num_alignments 50 -num_descriptions 50 -index_name seqfasta_db.00.idx"; 
	exec($command, $output_win);
	
	# get the alignment blast
	$command_align = $blast_folder . "blastn.exe\" -query query.fa -db seqfasta_db.fa -task blastn -evalue 0.03  -dust no -outfmt 0 -num_alignments 50 -num_descriptions 50 -index_name seqfasta_db.00.idx"; 
	exec($command_align, $output3);
	# return to the working folder /includes
	chdir('..');
} 
else {
	//$phpos = 'This is a server not using Windows!';
	//echo "This is a server not using Windows!</br>";
	# do blast
	$blast_folder = $local_folder . "/blast/bin/";

	# create blast dataset
	$command_make_db = $blast_folder . "makeblastdb -in blasts/seqfasta_db.fa -dbtype nucl -input_type fasta";
	exec($command_make_db, $output2);// or die ("couldn't execute command for make_db on mac/unix</br>");
	chmod('blasts/seqfasta_db.fa', 0777);
	chmod('blasts/seqfasta_db.fa.nhr', 0777);
	chmod('blasts/seqfasta_db.fa.nin', 0777);
	chmod('blasts/seqfasta_db.fa.nsq', 0777);
	
	# create alias
	$command_make_alias = $blast_folder . "blastdb_aliastool -dblist \"blasts/seqfasta_db.fa\" -dbtype nucl -out blasts/seqfasta_db.fa -title \"seqfasta_db\"";
	exec($command_make_alias, $output4); // or die ("couln't execute command for blastn 1 on mac/unix</br>");
	chmod('blasts/seqfasta_db.fa.nal', 0777);

	# make index
	$command_make_index = $blast_folder . "makembindex -input blasts/seqfasta_db.fa -iformat fasta -output blasts/seqfasta_db";
	exec($command_make_index, $output5); // or die ("couln't execute command for blastn 1 on mac/unix</br>");
	chmod('blasts/seqfasta_db.00.idx', 0777);

	# do blast
	chdir('blasts');
	$command = $blast_folder . "blastn -query query.fa -db seqfasta_db.fa -task blastn -evalue 0.03 -dust no -outfmt \"7 sseqid pident bitscore gapopen length qstart qend sstart send\" -num_alignments 50 -num_descriptions 50 -index_name seqfasta_db.00.idx";
	exec($command, $output); // or die ("couln't execute command for blastn 1 on mac/unix</br>");

	# get the alignment blast
	$command_align = $blast_folder . "blastn -query query.fa -db seqfasta_db.fa -task blastn -evalue 0.03  -dust no -outfmt 0 -num_alignments 50 -num_descriptions 50 -index_name seqfasta_db.00.idx" ; 
	exec($command_align, $output3); // or die ("couln't execute command for blastn 2 on mac/unix</br>");
	chdir('..');
}

unset($output_to_user);

// #################################################################################
// Section: Handle response
// #################################################################################
$output_to_user = "";
if (isset($output_win)) { $output = array();
	foreach($output_win as $item) { 
		if ($item[0] != "#" ){
		$ow = preg_replace("'\s+'", '#', $item);
		$ow_array = explode("#", $ow); 
		$ow_array2 = array($ow_array[1],$ow_array[2],$ow_array[3],$ow_array[4],$ow_array[5],$ow_array[6],$ow_array[7],$ow_array[16],$ow_array[10],$ow_array[8],$ow_array[11],$ow_array[12],$ow_array[13],$ow_array[14]);
		$output[] = implode("#",$ow_array2);
		}
	}
}
foreach($output as $item) {
	if ($item[0] != "#" ){
		$output_to_user[] = $itemvar = $str = preg_replace("'\s+'", '#', $item);
	}
}


#if (trim($output_to_user) === "") {$errorList[] = "No similar sequences were found in the database!";}
if( !is_array($output_to_user) && trim($output_to_user) == "" ) {
	$errorList[] = "No similar sequences were found in the database!";
}


# get response
$output_to_user2 = "";
foreach($output2 as $item2) {
	$output_to_user2 .= $item2 . "<br />";
}
//check for error and if none proceed with inputting into mysql db
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
// Section: Build output after succesful BLAST
// #################################################################################
#=========================== markup formatting
$yahoo_map = false;
$dojo = false;
$title = $config_sitename . ": Result from a local blast";
							//echo "$command_make_db</br>OS:</br>$phpos</br>Seqfasta:</br>$seqfasta_db</br>test_query:$test_query</br>output2:</br>$output_to_user2";
include_once('header.php');
nav();
echo "<div id=\"content\">";

$table = "\n<table border='0' cellspacing='0'><caption>Results of the New Sequence blast against the local database:</caption>";
$table .= "\n<tr><td colspan=\"14\" style=\"width: 60px;\"><b>$name</b></td>";
$table .= "\n<tr><td class='label1'>Code</td>
				 <td class='label1'>Family</td>
				 <td class='label1'>Subfamily</td>
				 <td class='label1'>Genus</td>
				 <td class='label1'>Species</td>
				 <td class='label1'>Gene code</td>
				 <td class='label1'>% identical</td>
				 <td class='label1'>Bitscore</td>
				 <td class='label1'># gaps</td>
				 <td class='label1'>Align. length</td>
				 <td class='label1'>Start # q.seq.</td>
				 <td class='label1'>End # q.seq.</td>
				 <td class='label1'>Start # s.seq.</td>
				 <td class='label4'>End # s.seq.</td>
			</tr>";
foreach($output_to_user as $line) {
	$table .= "<tr>";
	
	$line_cols = explode(" ", str_replace("#", " ", $line));
	foreach ($line_cols as $k => $v){
		if ($k == 0){
			$table .= "<td class=\"field4\"><a href=" . $base_url . "/home.php onclick=\"return redirect('../story.php?code=$v');\">$v</a></td>";
		}
		elseif ($k == 1 || $k == 2 || $k == 3 ){
			$table .= "<td class=\"field4\">$v</td>";
		}
		elseif ($k == 13 ){
			$table .= "<td class=\"field\">$v</td>";
		}
		else {
			$table .= "<td class=\"field4\" align=\"center\">$v</td>";
		}
	}
	//$table .= "\n</tr>";
}
echo $table;
//echo "</table>";

				//tried to make it show alignments but multiple whitespaces in alignments truncates to 1, destrouying the alignment...


$output_to_user3 = "<tr><td colspan=13><font size=\"4\" style=\"bold\" face=\"courier\">";
foreach($output3 as $item3) {
	//$output_to_user3 .= "</br>";
	$item3 = str_replace("#", "_", $item3);
	$item3 = str_replace(" ", "&nbsp;", $item3);
	unset ($find1, $find2, $find3, $find4, $find5, $find6, $find7, $find8, $o3);
	$find1 = strpos($item3, "Query");
	$find2 = strpos($item3, "Sbjct&nbsp;");
	$find3 = strpos($item3, "|");
	$find4 = strpos($item3, ">");
	$find5 = strpos($item3, "Length");
	$find6 = strpos($item3, "Sequences&nbsp;producing&nbsp;significant&nbsp;alignments");
	$find7 = strpos($item3, "Score&nbsp;=");
	$find8 = strpos($item3, "Identities&nbsp;=");
	if ($find1 !== False || $find2 !== False || $find3 !== False || $find4 !== False || $find5 !== False || $find6 !== False || $find7 !== False || $find8 !== False || str_replace("nbsp;","", $item3) == "") { 
	if (strpos($item3, "(Bits)&nbsp;&nbsp;Value") !== False){ $item3 = str_replace("(Bits)&nbsp;&nbsp;Value", "", $item3);}
	$o3 = $item3 . "</br>"; 
	}
	//if ($find1 !== False || $find2 !== False) { $o3 = $item3 . "</br>"; } //$item3 = str_replace("&nbsp;", " ", $item3); $item3 = preg_replace('/\s\s+/', ' ', $item3); $item3 = str_replace(" ", "</td><td><font face=\"courier\">", $item3); $o3 = "<td>" . $item3 . "</font></td>";}
	//elseif ($find3 !== False){$o3 = $item3 . "</br>"; } //$o3 = "<td></td><td></td><td><font face=\"courier\">$item3</font></td><td></td>";}
	else { } //$o3 = $item3 . "</br>"; } //$o3 = "<td></td><td></td><td><font face=\"courier\">$item3</font></td><td></td>";
	$output_to_user3 .= "$o3";
}

$output_to_user3 .= "</font></td></table>";
echo $output_to_user3;
echo "</div>";

make_footer($date_timezone, $config_sitename, $version, $base_url);
echo "</body>\n</html>";
}

$file_array = array('blasts/query.fa', "blasts/seqfasta_db.00.idx", 'blasts/seqfasta_db.fa', "blasts/seqfasta_db.fa.nal", "blasts/seqfasta_db.fa.nhr", "blasts/seqfasta_db.fa.nin", "blasts/seqfasta_db.fa.nsq");
foreach ($file_array as $f) {
	if( file_exists($f) ) {
		unlink($f);
	}
}
mysql_close($connection);
?>
