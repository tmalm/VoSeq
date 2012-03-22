<?php
// #################################################################################
// #################################################################################
// Voseq includes/blast_vs_genbank.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Creates a BLAST of selected sequence against GenBank
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
include_once('../functions.php');
include_once('../markup-functions.php');
include_once('../conf.php');

$in_includes = true;
$yahoo_map = false;
$dojo = false;

// #################################################################################
// Section: Get code and sequence to BLAST
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

$title = $config_sitename . ": Result from a $geneCode blast vs Genbank";
include_once('header.php');
nav();

// #################################################################################
// Section: BLAST vs GenBank function
// #################################################################################
echo "<script type='text/javascript'>
		$(window).load(function() {
				blast();
		});

		function get_results(rid, rtoe) {
			$.get('blast_functions.php?rid=' + rid + '&rtoe=' + rtoe,
				function(data) {
					var query_geneCode = $('#query_geneCode').val();
					var query_code = $('#query_code').val();
					var query_family = $('#query_family').val();
					var query_genus = $('#query_genus').val();
					var query_species = $('#query_species').val();
					var html = '';

					html += '<table width=\"800px\" border=\"0\" cellspacing=\"0\"><caption>';
					html += 'Results of the ' + query_geneCode + ' blast against GenBank:</caption>';
					html += '<tr><td colspan=\"3\"><b>' + query_family + '> ' + query_genus + ' ' + query_species + '> ' + query_code + '</b></td></tr>';
					html += '<tr>';
					html += '<td class=\"label1\">accession number</td>';
					html += '<td class=\"label1\">Identifier</td>';
					html += '<td class=\"label1\">% identical</td>';
					html += '<td class=\"label1\">Bitscore</td>';
					html += '<td class=\"label1\"># gaps</td>';
					html += '<td class=\"label1\">Align. length</td>';
					html += '<td class=\"label1\">Start # q.seq.</td>';
					html += '<td class=\"label1\">End # q.seq.</td>';
					html += '<td class=\"label1\">Start # s.seq.</td>';
					html += '<td class=\"label4\">End # s.seq.</td>';
					html += '</tr>';
					html += data;

					$(html).insertAfter('.hits');
					$('.hits').remove();
					$('.java1').remove();
				}
			);
		}

		function blast() {
			var query_code = $('#query_code').val();
			var query_geneCode = $('#query_geneCode').val();

			$(\"#output\").html('Submitting BLAST job');
			
			$.getJSON(\"blast_functions.php?code=\" + query_code + \"&geneCode=\" + query_geneCode + \"&callback=?\",
				function(data) {
					var html = '';
					if ( data.rid == 0 ) {
						html += 'Submission failed';
						$(\"#output\").html(html);
					}
					else if ( data.rid == null ) {
						html += 'Submission failed.<br /> There might be a problem with your sequence.';
						$(\"#output\").html(html);
					}
					else {
						html += 'BLAST job <b>' + data.rid + '</b> expected to take ' + data.rtoe + ' seconds';
						html += '<br/><span id=\"countdown\" style=\"font-size:100px;color:rgb(192,192,192);\">' + data.rtoe + '</span>';
						$(\"#output\").html(html);

						show_delay(data.rid, data.rtoe);
					}
				}
			);
		}

		
		function show_delay(rid, rtoe) {
			value = rtoe;
			$(\"#countdown\").html(value);
			value--;

			if(value > -1) {
				setTimeout('show_delay(\"' + rid + '\",' + value + ')', 1000);
			}
			else {
				get_results(rid, rtoe);
			}
		}

	  </script>\n\n";

echo "<div id=\"content\">";

// #################################################################################
// Section: Get sequence and code and info of selected sequence
// #################################################################################
@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
mysql_select_db($db) or die ('Unable to connect!');
$query = "select sequences FROM ". $p_ . "sequences where code='$code' and geneCode='$geneCode'";
$result = mysql_query($query) or die("Error in query: $query.  " . mysql_error());
if(mysql_num_rows($result) > 0) {
	while($row = mysql_fetch_object($result)) {
		$sequence = $row->sequences;
	}
	$replace_chars = array("?", "-", "~");
	$sequence = str_replace($replace_chars, "", $sequence);
	// check for too short sequences, minimum limit 101 bp
	if( strlen($sequence) < 101 ) {
		$error = array("Your sequence is too short to be blasted.", "It is shorter than 101 base pairs!");
		echo "\n<h1>Error</h1>";
		echo "<ul>";
		foreach( $error as $item ) {
			echo "<li>$item</li>";
		}
		echo "</ul>";
		echo "</div><!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url);
		echo "</body></html";
		exit(0);	
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


echo "\n<input type='hidden' id='query_family' value=\"$family\">";
echo "\n<input type='hidden' id='query_genus' value=\"$genus\">";
echo "\n<input type='hidden' id='query_species' value=\"$species\">";
echo "\n<input type='hidden' id='query_code' value=\"$code\">";
echo "\n<input type='hidden' id='query_geneCode' value=\"$geneCode\">";

echo "\n\n<div class=\"hits\"></div>";
echo "\n<div id=\"output\" class=\"java1\"></div> ";

echo "\n</table>";
echo "\n</div>";
make_footer($date_timezone, $config_sitename, $version, $base_url);








mysql_close($connection);

echo "</body>\n</html>";
?>
