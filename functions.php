<?php
// #################################################################################
// #################################################################################
// Voseq functions.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Certain funtions used as include() in other scripts
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'conf.php';
ob_end_clean();//Clear output buffer//includes

// #################################################################################
// Section: array_combine() function for PHP versions earlier than 5 
// #################################################################################
if (!function_exists('array_combine')) // for php 4 
{
	function array_combine($arr1,$arr2) {
		$out = array();
		foreach ($arr1 as $key1 => $value1) {
			$out[$value1] = $arr2[$key1];
		}
		return $out;
	}
}
// #################################################################################
// Section: stripos() function for PHP versions earlier than 5 
// #################################################################################
if (!function_exists('stripos')) // for php 4
{
	function stripos($haystack, $needle){
		return strpos($haystack, stristr( $haystack, $needle ));
	}
}
// #################################################################################
// Section: dofastafiles() function 
// returns a fasta file in GenBank format
// #################################################################################
function dofastafiles($geneCode, $code, $p_) {
	// open file
	$cwd = getcwd();
	$fastafile = $cwd . '/myfastafile.txt';
	$genbank_fastafile = $cwd . '/my_genbank_fastafile.txt';
	$handle1 = fopen($fastafile, "a");
	$handle2 = fopen($genbank_fastafile, "a");
	$query3 = "SELECT " . $p_ . "vouchers.orden, 
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
	$result3 = mysql_query($query3) or die("Error in query: $query3. " . mysql_error());
	if (mysql_num_rows($result3) > 0) {
		$lineage = " [Lineage=Eukaryota; Metazoa; Arthropoda; Hexapoda; Insecta; Pterygota; Neoptera; Endopterygota;";
		while ($row3 = mysql_fetch_object($result3)) {
			if( $row3->orden ) {
				$lineage .= " $row3->orden;";
			}
			if( $row3->family ) {
				$lineage .= " $row3->family;";
			}
			if( $row3->subfamily ) {
				$lineage .= " $row3->subfamily;";
			}
			if( $row3->tribe ) {
				$lineage .= " $row3->tribe;";
			}
			$lineage .= " $row3->genus] ";
			$species = str_ireplace(" ", "_", $row3->species);
			fwrite($handle1, ">" . $row3->genus . "_" . $species . "_" . $code . "\n$row3->sequences\n");
			fwrite($handle2, ">" . $row3->genus . "_" . $species . "_" . $code . " [org=$row3->genus $row3->species] [Specimen-voucher=$code]");
			fwrite($handle2, " [note=" . $row3->description . "] $lineage");
			fwrite($handle2, "\n$row3->sequences\n");
		}
		unset($lineage);
	}
	fclose($handle1);
	fclose($handle2);
}
// #################################################################################
// Section: dofastafile() function 
// returns a fasta file in standard format
// #################################################################################
function dofastafile($geneCode, $code, $p_){
					// 			open file
$cwd = getcwd();
$fastafile = $cwd . '/myfastafile.txt';
$handle = fopen($fastafile, "a");
$query3 = "SELECT " . $p_ . "vouchers.code, 
				  " . $p_ . "vouchers.genus, 
				  " . $p_ . "vouchers.species, 
				  " . $p_ . "sequences.sequences FROM 
				  " . $p_ . "vouchers, 
				  " . $p_ . "sequences WHERE 
				  " . $p_ . "vouchers.code='$code' AND 
				  " . $p_ . "sequences.code='$code' AND geneCode='$geneCode'";
$result3 = mysql_query($query3) or die("Error in query: $query3. " . mysql_error());
if (mysql_num_rows($result3) > 0)
	{
	while ($row3 = mysql_fetch_object($result3))
		{
		fwrite($handle, ">" . $row3->genus . "_" . $row3->species . "_" . $row3->code . "\n$row3->sequences\n");
		}
	}
fclose($handle);
}
// #################################################################################
// Section: formatdate() function 
// format MySQL DATETIME value into a more readable string
// #################################################################################
// 
function formatDate($val, $date_timezone, $php_version) {
	if( $php_version == "5" ) {
		date_default_timezone_set($date_timezone); //php5
	}
	$arr = explode('-', $val);
	$day = explode(" ", $arr[2]);
	return date('d M Y', mktime(0,0,0, $arr[1], $day[0], $arr[0]));
}
// #################################################################################
// Section: getSeqs() function 
// get and print all sequences for a voucher, for the "story" (story.php) page
// #################################################################################
function getSeqs($code, $host, $user, $pass, $db, $p_) {
	// open database connection
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	// select database
	mysql_select_db($db) or die ('Unable to select database');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}
	// generate and execute query
	$query  = "SELECT geneCode, CHAR_LENGTH(sequences) AS mylen, (2*CHAR_LENGTH(sequences) - CHAR_LENGTH(REPLACE(sequences, '?', '')) - CHAR_LENGTH(REPLACE(sequences, '-', ''))) AS amb, LEFT((labPerson),8) AS labPerson, accession FROM " . $p_ . "sequences WHERE code='$code' ORDER BY geneCode";
	
	$result  = mysql_query($query)  or die("Error in query: $query.  " . mysql_error());
	ob_start();//Hook output buffer - disallows web printing of file info...
	include 'conf.php';
	ob_end_clean();//Clear output buffer//includes
	if (mysql_num_rows($result) > 0) {
		$i = "0";
		while ($row = mysql_fetch_object($result)) {
			$i += "1";
			$labPerson = explode(" ", $row->labPerson);
			$labPerson = $labPerson[0];
			echo "<tr>";
			// masking URLs, this variable is set to "true" or "false" in conf.php file
			if($mask_url =="true") {
				echo "<td class=\"field4\"><a href='" . $base_url . "/home.php'  onclick=\"return redirect('sequences.php?code=". $code . "&amp;geneCode=" . $row->geneCode . "')\">" . $row->geneCode . "</a></td>";
			}
			else {
				echo "<td class=\"field4\"><a href=\"sequences.php?code=". $code . "&amp;geneCode=" . $row->geneCode . "\">" . $row->geneCode . "</a></td>";
			}
			echo "<td class=\"field4\">" . $row->mylen . "</td>";
			echo "<td class=\"field4\">" . $row->amb . "</td>";
			echo "<td class=\"field4\">" . $labPerson . "</td>";
			echo "<td class=\"field4\"><a href=\"http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Search&amp;db=nucleotide&amp;term=" . $row->accession . "[accn]&amp;doptcmdl=GenBank\" target=\"_blank\">" . $row->accession . "</a>&nbsp;</td>";

			echo "<td class=\"field4\">";
			echo "<a href=\"includes/blast_locally.php?code=". $code . "&amp;geneCode=$row->geneCode\" target=\"_blank\" ><img class=\"link\" width=\"18px\" height=\"14px\" src=\"images/local_blast.png\" id=\"local_blast" . $i . "\"/></a>";	
			echo "<span dojoType=\"tooltip\" connectId=\"local_blast" . $i . "\" delay=\"1\" toggle=\"explode\">Local blast: this sequence against <br /><b>all sequences of the same gene code</b></span>";

			echo "&nbsp;<a href=\"includes/blast_locally_full_db.php?code=". $code . "&amp;geneCode=$row->geneCode\" target=\"_blank\" ><img class=\"link\" width=\"16px\" height=\"16px\" src=\"images/database.png\" id=\"full_database" . $i . "\" /></a>";	
			echo "<span dojoType=\"tooltip\" connectId=\"full_database" . $i . "\" delay=\"1\" toggle=\"explode\"><b>FULL blast:</b> this sequence against <b>the full database</b></span>";
			echo "</td>";

			echo "<td class=\"field5\">";
			$tmp = explode("-", $row->geneCode);
			echo "<a href=\"includes/blast_vs_genbank.php?code=". $code ."&amp;geneCode=$row->geneCode\" target=\"_blank\"><img class=\"link\" width=\"18px\" height=\"14px\" src=\"images/ncbi_blast.png\" id=\"vs_genbank" . $i . "\" /></a>";
			echo "<span dojoType=\"tooltip\" connectId=\"vs_genbank" . $i . "\" delay=\"1\" toggle=\"explode\">BLAST against GenBank</span>";
			unset($tmp);
			echo "</td>\n\t\t</tr>\n\t\t";
  		}
		// print empty fields and finish this table
		echo "<tr>\n\t\t\t";
		//link to add new sequence for this record
		echo "<td class=\"field4\"><a href=\"admin/listseq.php?code=" . $code . "\">add seq</a></td>";
		echo "<td class=\"field4\">&nbsp;</td>
				<td class=\"field4\">&nbsp;</td>
				<td class=\"field4\">&nbsp;</td>
				<td class=\"field4\">&nbsp;</td>
				<td class=\"field4\">&nbsp;</td>
				<td class=\"field5\">&nbsp;</td>";
		echo "</tr></table>\n\t<!-- end colmun 2 second block -->\n";
		}
	else
		{
		echo "<tr>
			<td class=\"field4\"><a href=\"admin/listseq.php?code=" . $code . "\">Add Seq</a></td>
			<td class=\"field4\">&nbsp;</td>
			<td class=\"field4\">&nbsp;</td>
			<td class=\"field4\">&nbsp;</td>
			<td class=\"field4\">&nbsp;</td>
			<td class=\"field4\">&nbsp;</td>
			<td class=\"field5\">&nbsp;</td>
			</tr>
			</table><!-- 	end column 2, second block -->\n\n";
		}
	}

// #################################################################################
// Section: clean_string() function 
// returns a string cleaned of certain characters
// #################################################################################
function clean_string($string) {
	$i = 0;
	if( (isset($string) && trim($string) != '') ) {
		$user_strings = array();
		$symbols = array(",",'"',"'","&","/","\\",";","=");
		#is number? then dont filter by symbols
		if( is_numeric($string) ) {
			array_push($user_strings, $string);
		}
		else { #not number, then clean by filtering symbols
			$id_subject = trim(str_replace($symbols, "", $string));
			$subject = explode(" ", $id_subject);
			foreach( $subject as $val ) {
				if( trim($val) != "" ) {
					$pattern = '/[a-öA-Ö0-9_\.\-]+/';
					preg_match($pattern, $val, $match);
					if( $i < 3 ) {
						array_push($user_strings, $match[0]);
					}
					$i++;
				}
			}
		}
		return $user_strings;
	}
}

// #################################################################################
// Section: unpdate_comboboxes() function 
// updates the Dojo comboboxes in /Dojo
// #################################################################################
function update_comboboxes() {

	ob_start();//Hook output buffer - disallows web printing of file info...
	include 'conf.php';
	ob_end_clean();//Clear output buffer//includes

	/*** make dojos .js files ***/

	// Initialize default settings
	$MYSQL_PATH = '/usr/local/mysql/bin';

    $comboName[] = 'orden';
	$comboName[] = 'family';
	$comboName[] = 'subfamily';
	$comboName[] = 'tribe';
    $comboName[] = 'subtribe';
	$comboName[] = 'genus';
    $comboName[] = 'species';
	$comboName[] = 'subspecies';
	$comboName[] = 'country';
    $comboName[] = 'code';
	$comboName[] = 'collector';
	$comboName[] = 'extractor';
	$comboName[] = 'hostorg';

	// table sequences
    $comboNameSeq[] = 'labPerson';
	$comboNameSeq[] = 'geneCode';
	$comboNameSeq[] = 'accession';

    // table primers
	$comboNamePri[] = 'primer1';
	$comboNamePri[] = 'primer2';
	$comboNamePri[] = 'primer3';
    $comboNamePri[] = 'primer4';
	$comboNamePri[] = 'primer5';
    $comboNamePri[] = 'primer6';
	
	$cwd = dirname(__FILE__);

    // connect to database
	@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
	mysql_select_db($db) or die ('Unable to select database');
	if( function_exists(mysql_set_charset) ) {
    	mysql_set_charset("utf8");
	}

	// do table vouchers
	foreach ($comboName as $value) {
    	$query = "SELECT DISTINCT $value FROM " . $p_ . "vouchers ORDER BY $value ASC";
		$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
		
		$comboFile = $local_folder . '/dojo_data/comboBoxData_' . $value . '.js';
    	if ( file_exists($comboFile) ) {
    		unlink($comboFile);
		}
		$handle = fopen($comboFile, "w");
    	fwrite($handle, "[\n");
		
		while( $row = mysql_fetch_object($result) ) {
    		if ( $row->$value == "" ) {
    			continue;
			}
			else {
				fwrite($handle, "\t[\"" . $row->$value . "\"],\n");
			}
		}
    	fwrite($handle, "]\n");
		//echo "$value\n";
		fclose($handle);
	}

	// do table sequences
    foreach ($comboNameSeq as $value) {
		$query = "SELECT DISTINCT $value FROM " . $p_ . "sequences ORDER BY $value ASC";
    	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
		
		$comboFile = $local_folder . '/dojo_data/comboBoxData_' . $value . '.js';
		if ( file_exists($comboFile) ) {
			unlink($comboFile);
    	}
		$handle = fopen($comboFile, "w");
		fwrite($handle, "[\n");
	
    	while( $row = mysql_fetch_object($result) ) {
			if ( $row->$value == "" ) {
    			continue;
			}
    		else {
				fwrite($handle, "\t[\"" . $row->$value . "\"],\n");
			}
		}
    	fwrite($handle, "]\n");
		//echo "$value\n";
		fclose($handle);
	}

	// do table primers
    foreach ($comboNamePri as $value) {
		$query = "SELECT DISTINCT $value FROM ". $p_ . "primers ORDER BY $value ASC";
    	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
		
		$comboFile = $local_folder . '/dojo_data/comboBoxData_' . $value . '.js';
		if ( file_exists($comboFile) ) {
			unlink($comboFile);
    	}
		$handle = fopen($comboFile, "w");
		fwrite($handle, "[\n");
	
    	while( $row = mysql_fetch_object($result) ) {
			if ( $row->$value == "" ) {
    			continue;
			}
    		else {
    				fwrite($handle, "\t[\"" . $row->$value . "\"],\n");
			}
		}
		fwrite($handle, "]\n");
    	//echo "$value\n";
		fclose($handle);
    }
}
?>
