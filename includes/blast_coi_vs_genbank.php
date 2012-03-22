<?php

include_once('../functions.php');
include_once('../markup-functions.php');
include_once('../conf.php');

$in_includes = true;

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

$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
mysql_select_db($db) or die ('Unable to connect!');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}


$query = "select sequences FROM " . $p_ . "sequences where code='$code' and geneCode='$geneCode'";
$result = mysql_query($query) or die("Error in query: $query.  " . mysql_error());

if(mysql_num_rows($result) > 0) {
	while($row = mysql_fetch_object($result)) {
		$sequence = $row->sequences;
	}
}

$query = "select family, genus, species FROM ". $p_ . "vouchers where code='$code'";
$result = mysql_query($query) or die("Error in query: $query.  " . mysql_error());

if(mysql_num_rows($result) > 0) {
	while($row = mysql_fetch_object($result)) {
		$family = $row->family;
		$genus = $row->genus;
		$species = $row->species;
	}
}

$url = "http://www.ncbi.nlm.nih.gov/blast/Blast.cgi";
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url); //set url to post to
curl_setopt($ch, CURLOPT_POST, 1);   //set POST method
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);   //return output as variable
curl_setopt($ch, CURLOPT_POSTFIELDS, "QUERY=$sequence&DATABASE=nr&HITLIST_SIZE=100&FILTER=L&EXPECT=100&FORMAT_TYPE=HTML&PROGRAM=blastn&CLIENT=web&SERVICE=plain&NCBI_GI=on&PAGE=Nucleotides&CMD=Put");   //add POST fields

$result = curl_exec($ch); // run the whole process

$subject = $result;
$pattern = '/\s+RID =.+/';
preg_match($pattern, $subject, $matches);

$rid = trim($matches[0]);
$rid = explode(" = ", $rid);
$rid = $rid[1];

$pattern = '/\s+RTOE =.+/';
preg_match($pattern, $subject, $matches);

$rtoe = trim($matches[0]);
$rtoe = explode(" = ", $rtoe);
$rtoe = $rtoe[1];

$wait = ($rtoe + 3);
sleep($wait);

# get response
$_ch = get_response($rid, $ch, $url);
$result = curl_exec($_ch); // run the whole process
#echo $result;
#$result = file_get_contents("b.html");

# find out if it is ready
$status = check_if_ready($result);

$i = 0;
if($status[0] != "READY" && $status[1] != "READY") {
	while($i < 6) {
		sleep(5);
		$_ch = get_response($rid, $ch, $url);
		$result = curl_exec($_ch); // run the whole process
		#echo $result;
		$status = check_if_ready($result);
		if($status[0] == "READY" || $status[1] == "READY") {
			break;
		}
		$i++;
	}
}

#=========================== markup formatting
$yahoo_map = false;
$dojo = false;
$title = $config_sitename . ": Result from a COI blast vs Genbank";
include_once('header.php');
nav();
echo "<div id=\"content\">";

$table = "\n<table border='0' cellspacing='0'><caption>Results of the COI blast against GenBank:</caption>";
$table .= "\n<tr><td><b>$family> $genus $species> $code</b></td></tr>";
$table .= "\n<tr><td class='label'>accession number</td>
			     <td class='label'>Identifier</td>
				 <td class='label2'>Identity</td>
			</tr>";
if($status[0] == "READY" || $status[1] == "READY") {
	$xmlstr = file_get_contents("result.txt");
	$xml = new SimpleXMLElement($xmlstr);
	foreach($xml->BlastOutput_iterations->Iteration->Iteration_hits->Hit as $hit) {
		$hit_definition = $hit->Hit_def;
		$hit_accession = $hit->Hit_accession;
		$hit_identities_n = $hit->Hit_hsps->Hsp->Hsp_positive;
		$hit_identities_d = $hit->Hit_hsps->Hsp->{'Hsp_align-len'};
		$hit_percentage = ($hit_identities_n*100)/$hit_identities_d;
		$table .= "<tr><td class='field4'><a href=\"http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Search&db=nuccore&term=". $hit_accession. "[accn]&doptcmdl=GenBank\" target=\"_blank\">" . $hit_accession. "</a></td><td class='field4'>" . substr($hit_definition, 0, 68) . "</td><td class='field5'>". $hit_identities_n . "/" . $hit_identities_d . " <b>(". round($hit_percentage, 2) . "%)</b></td></tr>";
	}
}
else {
	echo "";
}

echo $table;
echo "</table>";
echo "</div>";

make_footer($date_timezone, $config_sitename, $version, $base_url);




function get_response($rid, $ch, $url) {
	curl_setopt($ch, CURLOPT_POST, TRUE);   //set POST method
	curl_setopt($ch, CURLOPT_POSTFIELDS, "CMD=Get&RID=$rid&FORMAT_TYPE=XML");   //add POST fields
	curl_setopt($ch, CURLOPT_URL, $url); //set url to post to
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);   //return output as variable
	return $ch;
}

function check_if_ready($result) {
	$subject = $result;
	$pattern = '/\s+Status=.+/';
	preg_match($pattern, $subject, $matches);

	$status = trim($matches[0]);
	$status = explode("=", $status);

	$output = array();
	$output[] = $status[1];

	$fp = fopen("result.txt", "w");
	fwrite($fp, $result);
	fclose($fp);

	$file = file("result.txt");
	
	trim($file[2]);
	if(trim($file[2]) == "<head>") { # it is not HTML file
		echo "";
	}
	else {
		$xml = simplexml_load_file("result.txt");
		$tmp = $xml->BlastOutput->BlastOutput_iterations->Iteration->Iteration_hits->Hit->Hit_num;
		$output[] = "READY";
		if($tmp == "1") {
			$output[] = "READY";
		}
	}

	unset($xml);
	return $output;
}



curl_close($ch);
mysql_close($connection);

echo "</body>\n</html>";
?>
