<?php
// #################################################################################
// #################################################################################
// Voseq api/getTaxonAuthority_SOAP.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: gets authority (author and year) given genus and species names
//
// #################################################################################


// #################################################################################
//  Uses SOAP protocol to get authority from PESI Euo-nomen http://www.eu-nomen.eu/portal/index.php
//  and ubio.org via findIT service
// 
//  uses nusoap PHP library 
// 
//  input: genus and species
//  output: valid authority
// #################################################################################
function getAuthority($genus, $species) {
	require('nusoap/lib/nusoap.php');

	# initiate the SOAP client class pointing to the server
	$client = new nusoap_client('http://www.eu-nomen.eu/portal/soap.php?p=soap&wsdl');

	$guid = $client->call('getGUID', array('scientificname' => "$genus $species"));

	// Check for a fault
	if ($client->fault) {
		echo '';
		break;
	} 

	$record = $client->call('getPESIRecordByGUID', array('GUID' => $guid));
	$authority = $record['valid_authority'];

	return $authority;
}



// #################################################################################
//  Pull authority from EOL
// #################################################################################
function getAuthority_eol($eol_api_key) {
	# using jquery
	echo "<script type='text/javascript'>

			$(document).ready(function() {

				var genus = $('#genus').val();
				var species = $('#species').val();

				//-------------
				// call EOL and get content field having full species name including author and year (might also have parenthesis or not)
				//
				$.getJSON('http://eol.org/api/search/1.0/' + genus + '%20' + species + '.json?exact=1&key=$eol_api_key' + '&callback=?', 
					function(data) {
						var html = '';

						if(data.totalResults != 0) {
							var results = data.results;

							$.each(results, function (i,o) {
								for (var t in o) {
									console.log(o);
									if( t == 'title' ) {
										var title = o[t];
									}
									if( t == 'link' ) {
										var link = o[t];
									}
								}

								//
								// print to screen, to <div id='from_eol'>
								html += '<span class=\"eol_taxonomy\">' + title + '</span>&nbsp;';
								html += '<br /><span class=\"eol_link\">authority from <a href=\"' + link + '\" title=\"See it in EOL\">EOL</a></span>';
								$('#from_eol').html(html);

							});
						}
					});



			});


		  </script>
		";
}

?>
