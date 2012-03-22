<?php
// #################################################################################
// #################################################################################
// Voseq includes/b_functions.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Includes certain functions for the BLAST scripts
// #################################################################################
// #################################################################################
// Section: make_local_fasta_db() functon
// Create local fasta db of selected gene
//
// input: $context variable can be "one_gene", "all_genes"
//
// output: returns array of error_list or string "success"
//
// #################################################################################

function make_local_fasta_db ($code, $geneCode, $context, $system, $p_) {
	if( $context == "all_genes" ) {
		$query_db = "SELECT sequences, code, geneCode FROM ". $p_ . "sequences";
		$result_db = mysql_query($query_db) or die("Error in query: $query.  " . mysql_error());
		$seqfasta_db = "";
		if(mysql_num_rows($result_db) > 0) {
			while($row_db = mysql_fetch_object($result_db)) {
				// check for and exclude the sequence and informtion for the query voucher and sequence
				$code_test = $row_db->code; $geneCode_test = $row_db->geneCode;
				if ( $code == $code_test && $geneCode == $geneCode_test ) {}
				else {
					// $seq_db has the actual DNA sequence string, do some cleaning and use only those longer than 101bp
					$seq_db = strtoupper($row_db->sequences);
					$replace_chars = array("?", "-", "~");
					$seq_db = str_replace($replace_chars, "", $seq_db);
					$code_db = $row_db->code;
					$geneCode_db = $row_db->geneCode;

					if( strlen($seq_db) > 101 ) {
						$query_dbv = "SELECT family, subfamily, genus, species FROM ". $p_ . "vouchers WHERE code='$code_db'";
						$result_dbv = mysql_query($query_dbv) or die("Error in query: $query.  " . mysql_error());
						if(mysql_num_rows($result_dbv) > 0) {
							while($row_dbv = mysql_fetch_object($result_dbv)) {
								$family_dbv = $row_dbv->family;
								$subfamily_dbv = $row_dbv->subfamily;
								$genus_dbv = $row_dbv->genus;
								$species_dbv = $row_dbv->species;
								$tax_dbv = ">" . $code_db . "#" . $family_dbv . "#". $subfamily_dbv . "#" . $genus_dbv . "#" . $species_dbv . "#" . $geneCode_db;
							}
						}
						$seqfasta_db .= $tax_dbv . "\n" . $seq_db . "\n";
					}
					//else { 
						//$errorList[] = "Couldn't retrieve voucher information about of $row_db->code!";
					//}
				}
			}
		}
		else {
			$errorList[] = "Couldn't find any other sequences of <b>$geneCode</b> in database!";
		}
	}

	// we need only one or more geneCodes
	else {
		$query_db = "SELECT sequences, code FROM ". $p_ . "sequences WHERE code!='$code' AND geneCode='$geneCode' ORDER BY code";
		$result_db = mysql_query($query_db) or die("Error in query: $query. " . mysql_error());
		$seqfasta_db = "";
		if( mysql_num_rows($result_db) > 0 ) {
			while( $row_db = mysql_fetch_object($result_db) ) {
				$seq_db = strtoupper($row_db->sequences);
				$replace_chars = array("?", "-", "~");
				$seq_db = str_replace($replace_chars, "", $seq_db);
				$query_dbv = "SELECT family, subfamily, genus, species FROM ". $p_ . "vouchers WHERE code='$row_db->code'";
				$result_dbv = mysql_query($query_dbv) or die("Error in query: $query. " . mysql_error());
				if( mysql_num_rows($result_dbv) > 0 ) {
					while( $row_dbv = mysql_fetch_object($result_dbv) ) {
						$family_dbv = $row_dbv->family;
						$subfamily_dbv = $row_dbv->subfamily;
						$genus_dbv = $row_dbv->genus;
						$species_dbv = $row_dbv->species;
						$tax_dbv = ">" . $row_db->code . "#" . $family_dbv . "#" . $subfamily_dbv . "#" . $genus_dbv . "#" . $species_dbv . "#" . $geneCode;
					}
				}
				$seqfasta_db .= $tax_dbv . "\n" . $seq_db . "\n";
			}
		}
		else {
			$errorList[] = "Couldn't find any other sequences of <b>$geneCode</b> in database!";
		}
	}
	
	if($context == "one_gene") {
		if( $system == "win" ) {
			$file = "blasts\seqfasta_db_one_gene.fa";
		}
		else {
			$file = "blasts/seqfasta_db_one_gene.fa";
		}
	}
	elseif ($context == "all_genes") {
		if( $system == "win" ) {
			$file = "blasts\seqfasta_db_all_genes.fa";
		}
		else {
			$file = "blasts/seqfasta_db_all_genes.fa";
		}
	}

	if( file_exists($file) ) {
		unlink($file);
	}
	
	$handle = fopen($file, "w");
	chmod($file, 0777);
	fwrite($handle, $seqfasta_db);
	fclose($handle);
	

	if( !file_exists($file) ) {
		$errorList[] = "File $file! not created!";
	}

	if( count($errorList) > 0) {
		return $errorList;
	}
	else {
		return array('status' => "success", 'filename' => $file);
	}
}
?>
