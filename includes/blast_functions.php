<?php
// #################################################################################
// #################################################################################
// Voseq includes/blast_functions.php
// author(s): Carlos Peña & Tobias Malm
//               Code borrowed from Rod Page
//               https://github.com/rdmpage/phyloinformatics on Feb 2012.
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Displays an error message encountered during a BLAST when the
// BLAST software is not installed, or other encoutered errors during a BLAST.
// #################################################################################
// #################################################################################
// Section: Startup/includes/prepare variables
// #################################################################################

#error_reporting(0);
include_once('../conf.php');


$code = "";
if( isset($_GET['code']) ) {
	$code = $_GET['code'];
}

$geneCode = "";
if( isset($_GET['geneCode']) ) {
	$geneCode = $_GET['geneCode'];
}

$callback = "";
if( isset($_GET['callback']) ) {
	$callback = $_GET['callback'];
}

$rid = "";
if( isset($_GET['rid']) ) {
	$rid = $_GET['rid'];
}

$rtoe = "";
if( isset($_GET['rtoe']) ) {
	$rtoe = $_GET['rtoe'];
}

## BLAST------------- 
if( $code != "" ) {
	$job = do_blast($code, $geneCode, $host, $user, $pass, $db, $p_);
	if( $callback != "") {
		echo $callback . "(";
	}
	echo json_encode($job);
	
	if( $callback != "") {
		echo ")";
	}
}
## Get results from RID------------- 
elseif ($rtoe != "") {
	sleep($rtoe);
	$output = get_results($rid);
	echo $output;
}
// #################################################################################
// Get response from NCBI to see if results are ready
// #################################################################################
function get_response($rid, $ch, $url) {
	curl_setopt($ch, CURLOPT_POST, TRUE);   //set POST method
	curl_setopt($ch, CURLOPT_POSTFIELDS, "CMD=Get&RID=$rid&FORMAT_TYPE=XML");   //add POST fields
	curl_setopt($ch, CURLOPT_URL, $url); //set url to post to
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);   //return output as variable

	$result = curl_exec($ch); // run the whole process

	$xml = new SimpleXMLElement($result);
	foreach($xml->BlastOutput_iterations->Iteration->Iteration_hits->Hit as $hit) {
		$hit_accession = $hit->Hit_accession;

		$hit_definition = $hit->Hit_def;
		$hit_definition = preg_replace('/\s+/', " ", $hit_definition);
		$hit_definition = explode(" ", $hit_definition);
		$hit_definition = $hit_definition[0] . " " . $hit_definition[1];

			$hit_identities_n = $hit->Hit_hsps->Hsp->Hsp_positive;
			$hit_identities_d = $hit->Hit_hsps->Hsp->{'Hsp_align-len'};
		$hit_percentage = ($hit_identities_n*100)/$hit_identities_d;
		$hit_bitscore = $hit->Hit_hsps->Hsp->{'Hsp_bit-score'};
		$hit_gaps = $hit->Hit_hsps->Hsp->Hsp_gaps;
		$hit_align_length = $hit_identities_d;
		$hit_query_start = $hit->Hit_hsps->Hsp->{'Hsp_query-from'};
		$hit_query_end = $hit->Hit_hsps->Hsp->{'Hsp_query-to'};
		$hit_subject_start = $hit->Hit_hsps->Hsp->{'Hsp_hit-from'};
		$hit_subject_end = $hit->Hit_hsps->Hsp->{'Hsp_hit-to'};

		//$hit_query = $hit->Hit_hsps->Hsp->Hsp_qseq;
		//$hit_subject = $hit->Hit_hsps->Hsp->Hsp_hseq;
		//$hit_midline = $hit->Hit_hsps->Hsp->Hsp_midline;


		$output .= "<tr><td class='field4'><a href=\"http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Search&db=nuccore&term=". $hit_accession. "[accn]&doptcmdl=GenBank\" target=\"_blank\">" . $hit_accession. "</a></td>
						<td class='field4'>" . $hit_definition . "</td>
						<td class='field4'>" . round($hit_percentage, 2) . "%</td>
						<td class='field4'>" . $hit_bitscore . "</td>
						<td class='field4'>" . $hit_gaps . "</td>
						<td class='field4'>" . $hit_align_length . "</td>
						<td class='field4'>" . $hit_query_start . "</td>
						<td class='field4'>" . $hit_query_end . "</td>
						<td class='field4'>" . $hit_subject_start . "</td>
						<td class='field5'>" . $hit_subject_end . "</td>
					</tr>";
	}
	curl_close($ch);
	return $output;
}

// #################################################################################
// Do the BLASTing
// #################################################################################
function do_blast($code, $geneCode, $host, $user, $pass, $db, $p_) {
	# get sequence from $code $geneCode
	@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	mysql_select_db($db) or die ('Unable to connect!');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}


	$query = "select sequences FROM ". $p_ . "sequences where code='$code' and geneCode='$geneCode'";
	$result = mysql_query($query) or die("Error in query: $query.  " . mysql_error());

	if(mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_object($result)) {
			$sequence = $row->sequences;
		}
	}

	$url = "http://www.ncbi.nlm.nih.gov/blast/Blast.cgi";

	$job = new stdclass;
	$job->rid = "";
	$job->rtoe = 0;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url); //set url to post to
	curl_setopt($ch, CURLOPT_POST, 1);   //set POST method
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);   //return output as variable
	curl_setopt($ch, CURLOPT_POSTFIELDS, "QUERY=$sequence&DATABASE=nr&HITLIST_SIZE=50&FILTER=L&EXPECT=50&THRESHOLD=0.03&FORMAT_TYPE=HTML&PROGRAM=blastn&CLIENT=web&SERVICE=plain&NCBI_GI=on&PAGE=Nucleotides&CMD=Put");   //add POST fields

	$result = curl_exec($ch); // run the whole process
	curl_close($ch);

	$subject = $result;
	$pattern = '/\s+RID =.+/';
	preg_match($pattern, $subject, $matches);

	$rid = trim($matches[0]);
	$rid = explode(" = ", $rid);
	$job->rid = $rid[1];

	$pattern = '/\s+RTOE =.+/';
	preg_match($pattern, $subject, $matches);

	$rtoe = trim($matches[0]);
	$rtoe = explode(" = ", $rtoe);
	$job->rtoe = $rtoe[1];

	return $job;
}

// #################################################################################
// Get BLAST results
// #################################################################################

function get_results($rid) {
	$ch = curl_init();
	$done = false;
	while( !$done ) {
		sleep(2);
		$url = "http://www.ncbi.nlm.nih.gov/blast/Blast.cgi";
		curl_setopt($ch, CURLOPT_POST, TRUE);   //set POST method
		curl_setopt($ch, CURLOPT_POSTFIELDS, "CMD=Get&RID=$rid");   //add POST fields
		curl_setopt($ch, CURLOPT_URL, $url); //set url to post to
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);   //return output as variable
		$result = curl_exec($ch);

		if( preg_match('/\s+Status=WAITING/m', $result, $match) ) {
#			echo "Searching...\n";
		}

		if( preg_match('/\s+Status=FAILED/m', $result, $match) ) {
#			echo "Search failed;\n";
			exit(4);
		}

		if( preg_match('/\s+Status=UNKNOWN/m', $result, $match) ) {
#			echo "Search expired\n";
			exit(3);
		}

		if( preg_match('/\s+Status=READY/m', $result, $match) ) {
#echo "Search complete, retrieving results...\n";
			$done = true;
		}
	}
	$url = "http://www.ncbi.nlm.nih.gov/blast/Blast.cgi";
	$result = get_response($rid, $ch, $url); 
	return $result;
}

	
?>
