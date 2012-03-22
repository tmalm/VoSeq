<?php
// #################################################################################
// #################################################################################
// Voseq story.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Page for single voucher - displays collected info
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
#error_reporting (E_ALL);
ini_set("display_errors", "0");
//check login session
include'login/auth.php';
// includes
include'markup-functions.php';
ob_start();//Hook output buffer - disallows web printing of file info...
include 'conf.php';
ob_end_clean();//Clear output buffer//includes
include'functions.php';
include'includes/yahoo_map.php';
include'includes/show_coords.php';
include'api/getTaxonAuthority_SOAP.php';

$admin = false;
$in_includes = false;

// #################################################################################
// check for record ID
// #################################################################################

if ((!isset($_GET['code']) || trim($_GET['code']) == '')) {
	die('Missing record ID!');
}
// #################################################################################
// Section: If ID found - set values and query info
// #################################################################################
// generate and execute query
# clean $_GET;
unset($new_GET);
if(!empty($_GET)) {
	$keys = array();
	$values = array();

	foreach($_GET as $k => $v) {
		$tmp = clean_string($k);
		$k = $tmp[0];
		unset($tmp);
		$keys[] = $k;

		$tmp = clean_string($v);
		$v = $tmp[0];
		unset($tmp);
		$values[] = $v;
	}

	$new_GET = array_combine($keys, $values);
	unset($keys);
	unset($values);
	unset($k);
	unset($v);
	unset($_GET);
}
# end cleaning $_GET;
	
// need to draw yahoo map?
$yahoo_map = true;

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'Tooltip';

// open database connection
$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}

// generate and execute query
$id = $new_GET['code'];
$query = "SELECT id, code, orden, family, subfamily, tribe, subtribe, genus, species, subspecies, typeSpecies, country, specificLocality, latitude, longitude, altitude, collector, dateCollection, voucherLocality, voucher, sex, flickr_id, voucherImage, thumbnail, voucherCode, publishedIn, notes, hostorg, extraction, extractionTube, extractor, dateExtraction, timestamp FROM ". $p_ . "vouchers WHERE code = '$id'";
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
// get result set as object
$row = mysql_fetch_object($result);

// #################################################################################
// Section: if search is active - fix prev/next link info
// #################################################################################
// previous and next links
if ( isset($new_GET['search']) ) {
	if( trim($new_GET['search']) != '') {
		$current_id = $row->id;

	// get previous and next links from search and search_results tables
	$current_id_search_id = $new_GET['search'];

	// current id of this record in search_results ids
	$query_c_id_t_r  = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND record_id='$current_id'";
	$result_c_id_t_r = mysql_query($query_c_id_t_r) or die("Error in query: $query_c_id_t_r. " . mysql_error());
	$row_c_id_t_r    = mysql_fetch_object($result_c_id_t_r);

	$link_current  = $row_c_id_t_r->id;

	// link previous
	$link_previous = $link_current - 1;
	$query_link_previous      = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_previous'";
	$result_link_previous     = mysql_query($query_link_previous) or die("Error in query: $query_link_previous. " . mysql_error());
	$row_result_link_previous = mysql_fetch_object($result_link_previous);
	if ($row_result_link_previous)
		{
		$query_lp  = "SELECT record_id FROM ". $p_ . "search_results WHERE id='$link_previous'";
		$result_lp = mysql_query($query_lp) or die("Error in query: $query_lp. " . mysql_error());
		$row_lp    = mysql_fetch_object($result_lp);
		$previous  = $row_lp->record_id;
		$query_lpcode  = "SELECT code FROM ". $p_ . "vouchers WHERE id='$previous'";
		$result_lpcode = mysql_query($query_lpcode) or die("Error in query: $query_lpcode. " . mysql_error());
		$row_lpcode    = mysql_fetch_object($result_lpcode);
		$prevCode      = $row_lpcode->code;
		}
	else
		{
		$link_previous = false;
		}

	// link next
	$link_next = $link_current + 1;
	$query_link_next  = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_next'";
	$result_link_next = mysql_query($query_link_next) or die("Erro in query: $query_link_next. " . mysql_error());
	$row_result_link_next = mysql_fetch_object($result_link_next);
	if ($row_result_link_next)
		{
		$query_ln  = "SELECT record_id FROM ". $p_ . "search_results WHERE id='$link_next'";
		$result_ln = mysql_query($query_ln) or die("Error in query: $query_ln. " . mysql_error());
		$row_ln    = mysql_fetch_object($result_ln);
		$next      = $row_ln->record_id;
		$query_lncode  = "SELECT code FROM ". $p_ . "vouchers WHERE id='$next'";
		$result_lncode = mysql_query($query_lncode) or die("Error in query: $query_lncode. " . mysql_error());
		$row_lncode    = mysql_fetch_object($result_lncode);
		$nextCode      = $row_lncode->code;
		}
	else
		{
		$link_next = false;
		}
	}
} // end previous and next links

// #################################################################################
// Section: Output Voucher info
// #################################################################################
// process title
if ( !isset($title) ) {
	$title = "NSG's db-" . $row->code;
}
	
// print beginning of html page -- headers
include_once'includes/header.php';
nav();

// begin HTML page content
echo "<div id=\"content\">";

$genus = $row->genus;
$species = $row->species;

echo "<input id='genus' type='hidden' value='$genus' />";
echo "<input id='species' type='hidden' value='$species' />";
// print details
if ($row)
	{
		echo "<h1>";
		echo $row -> code;
		echo "</h1>";
		// Now pulling authority from EOL
		//echo "<h3>" . $genus . " " . $species . " " . getAuthority($genus, $pecies) .  "</h3>";
		if($genus != "" && $species != "") {
			getAuthority_eol($eol_api_key);
			echo "<div id='from_eol'></div>";
		}
	?>

	
<!-- 	show previous and next links -->
	<?php
	if ( isset($new_GET['search']) ) {
		if( trim($new_GET['search']) != '') {
			if ($link_previous) { 
				echo "<span dojoType=\"tooltip\" connectId=\"previous\" delay=\"1\" toggle=\"explode\">Previous</span>";

				// masking URLs, this variable is set to "true" or "false" in conf.php file
				if( $mask_url == "true" ) {
					echo "<a href='" .$base_url . "/home.php' onclick=\"return redirect('story.php?code=$prevCode&amp;search=$current_id_search_id')\">";
					echo "<img src=\"images/leftarrow.png\" class=\"link\" alt=\"previous\" id=\"previous\" /></a>&nbsp;&nbsp;";
				}
				else {
					echo "<a href='" .$base_url . "/story.php?code=$prevCode&amp;search=$current_id_search_id'>";
					echo "<img src=\"images/leftarrow.png\" class=\"link\" alt=\"previous\" id=\"previous\" /></a>&nbsp;&nbsp;";
				}
			}
		}
		else {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		
		if ($link_next) { 
			echo "<span dojoType=\"tooltip\" connectId=\"next\" delay=\"1\" toggle=\"explode\">Next</span>";
			
			// masking URLs, this variable is set to "true" or "false" in conf.php file
			if( $mask_url == "true" ) {
				echo "<a href='" .$base_url . "/home.php' onclick=\"return redirect('story.php?code=$nextCode&amp;search=$current_id_search_id')\">";
				echo "<img src=\"images/rightarrow.png\" class=\"link\" alt=\"next\" id=\"next\" /></a>";
			}
			else {
				echo "<a href='" .$base_url . "/story.php?code=$nextCode&amp;search=$current_id_search_id'>";
				echo "<img src=\"images/rightarrow.png\" class=\"link\" alt=\"next\" id=\"next\" /></a>";
			}

		}
		else {
			echo "&nbsp;";
			}
		}
	?>
<!-- 	show previous and next links -->
<table border="0" width="960px"> <!-- super table -->
<tr><td valign="top">


<table width="760" border="0"> <!-- big parent table -->
<tr><td valign="top">
	<table border="0" cellspacing="10"> <!-- table child 1 -->
	<tr><td>
	
	<table width="350" cellspacing="0" border="0">
	<caption>Specimen name</caption>
		<tr>
			<td class="label">Order</td><td class="field"><?php echo $row->orden; ?>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="label">Subfamily</td><td class="field"><?php echo $row->subfamily; ?>&nbsp;</td>
		</tr>
		<tr>
			<td class="label">Family</td><td class="field"><?php echo $row->family; ?>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="label">Tribe</td><td class="field"><?php echo $row->tribe; ?>&nbsp;</td>
			
		</tr>
		<tr>
			<td class="label">Genus</td><td class="field"><?php echo $row->genus; ?>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="label">Subtribe</td><td class="field"><?php echo $row->subtribe; ?>&nbsp;</td>
		</tr>
		<tr>
			<td class="label">Species</td><td class="field"><?php echo $row->species; ?>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="label">Host org.</td><td class="field"><?php echo $row->hostorg; ?>&nbsp;</td>
		</tr>
		<tr>
			<td class="label">Subspecies</td><td class="field"><?php echo $row->subspecies; ?>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="label">Type species?</td><td class="field"><?php if ($row->typeSpecies == 2) { echo "No"; } elseif ($row->typeSpecies == 1) { echo "Yes"; } else { echo "don't know"; } ?></td>
		</tr>
	</table>
	
	</td></tr>
	<tr><td>
	
	<table width="350" cellspacing="0" border="0">
	<caption>Locality Information</caption>
		<tr><td colspan="3" class="label2">Country</td></tr>
		<tr><td colspan="3" class="field"><?php echo $row->country; ?>&nbsp;</td></tr>
		
		<tr><td colspan="3" class="label2">Specific Locality</td></tr>
		<tr><td colspan="3" class="field"><?php echo $row->specificLocality; ?>&nbsp;</td></tr>
		
		<tr><td class="label2">Latitude</td><td class="label3">Longitude</td><td class="label3">Altitude</td></tr>
		<tr><td class="field"><?php show_latitude($row->latitude); ?>&nbsp;</td><td class="field2"><?php show_longitude($row->longitude); ?>&nbsp;</td><td class="field2"><?php echo $row->altitude; ?>&nbsp;</td></tr>
	</table>
	
	</td></tr>
	<tr><td>
	
	<table width="350" cellspacing="0" border="0">
	<caption>Collector Information</caption>
		<tr>
			<td class="code">Code in VoSeq</td>
			<td class="label3">Collector</td>
			<td class="label3">Collection date</td>
		</tr>
		<tr>
			<td class="field3"><?php echo $row->code; ?>&nbsp;</td>
			<td class="field2"><?php echo $row->collector; ?>&nbsp;</td>
			<td class="field2"><?php echo $row->dateCollection; ?>&nbsp;</td>
		</tr>

		<tr>
			<td class="label2">Voucher Locality</td>
			<td class="label3">Voucher <img width="15px" height="16px" src="images/question.png" id="voucher" alt="" />
								 <span dojoType="tooltip" connectId="voucher" delay="1" toggle="explode">-Spread?<br /> -Unspread?<br /> -In Slide?</span></td>
			<td class="label3">Sex</td>
		</tr>
		<tr>
			<td class="field"><?php echo $row->voucherLocality; ?>&nbsp;</td>
			<td class="field2"><?php echo $row->voucher; ?>&nbsp;</td>
			<td class="field2"><?php echo $row->sex; ?>&nbsp;</td>
		</tr>
		
		<tr>
			<td class="label2">Flickr photo id</td>
			<td class="label3">Voucher Code</td>
		</tr>
		<tr>
			<td id="photo_id" class="image"><?php
									$photo_id  = $row->flickr_id;
									if( $photo_id != "" && $photo_id != "null" ) {
										echo $photo_id;
									}
									else {
										echo "&nbsp";
									}
									echo "</td>";
									?>
			<td class="field2"><?php echo $row->voucherCode; ?>&nbsp;</td>
		</tr>
	</table>


	</td></tr>
	</table> <!-- end table child 1 -->

</td>
<td valign="top">

	<table border="0" cellspacing="10"> <!-- table child 2 -->
	<tr><td valign="top">
	
	<table width="160" cellspacing="0" border="0">
	<caption>DNA</caption>
		<tr>
			<td class="label2">Extraction <img width="15px" height="16px" src="images/question.png" id="extraction" alt="" />
								 <span dojoType="tooltip" connectId="extraction" delay="1" toggle="explode">Lab extraction kept in box number:</span></td>
			<td class="label3">Tube <img width="15px" height="16px" src="images/question.png" id="tube" alt="" />
								 <span dojoType="tooltip" connectId="tube" delay="1" toggle="explode">DNA extraction is in vial number:</span></td>
		</tr>
		<tr>
			<td class="field"><?php echo $row->extraction; ?>&nbsp;</td>
			<td class="field2"><?php echo $row->extractionTube; ?>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" class="label2">Extractor <img width="15px" height="16px" src="images/question.png" id="extractor" alt="" />
								 <span dojoType="tooltip" connectId="extractor" delay="1" toggle="explode">Person that performed the DNA extraction</span></td>
		</tr>
		<tr>
			<td colspan="2" class="field"><?php echo $row->extractor; ?>&nbsp;</td>
		</tr>
		<tr>
			<td class="label">Date</td>
			<td class="field"><?php echo $row->dateExtraction; ?>&nbsp;</td>
		</tr>
	</table>
	
	</td>
	<td>
		<a href="<?php echo $row->voucherImage; ?>" target="_blank"><img class="voucher" src="<?php echo $row->thumbnail; ?>" /></a>
		<?php
			if( $row->voucherImage != "na.gif" ) {
				echo "<div class='eol_button' onclick='send_to_EOL();'><img src='images/eol_button.png' alt='' />Share photo with EOL</div>";
			}

			echo "<script type='text/javascript'>

					var photo_id = $('td#photo_id').html();

					// -------------------------------------------
					$(document).ready(function(){
						$('.eol_button').hover(function(){
								$('.eol_button img')
								// first jump  
								.animate({top:'-12px'}, 200).animate({top:'-4px'}, 200)
								// second jump
								.animate({top:'-7px'}, 100).animate({top:'-4px'}, 100)
								// the last jump
								.animate({top:'-5px'}, 100).animate({top:'-4px'}, 100);
						});
					});

					// -------------------------------------------
					function send_to_EOL() {
						$('.eol_button').fadeOut('slow', function() {
								// Animation complete

								$('.eol_button').delay(200).fadeIn('slow', function() {}).html('Submitted to EOL').fadeTo('slow', 0.5, function() {} );

								// Tell Flickr to add special Tag to photo for EOL, change photo license if needed and add to EOL group pool of photos
								$.getJSON('api/photo_to_eol.php?photo_id=' + photo_id + '&callback=?',
									function(data) {
									});
								});
					}

				  </script>";
		?>
	</td>
	
	</tr>
	<tr><td colspan="2">
	
	<table width="380" cellspacing="0" border="0">
	<caption>Sequence Information</caption>
		<tr>
			<td class="label">Region</td>
			<td class="label">bp <img width="15px" height="16px" src="images/question.png" id="bp" alt="" />
								 <span dojoType="tooltip" connectId="bp" delay="1" toggle="explode">Number of base pairs</span></td>
			<td class="label">Amb. <img width="15px" height="16px" src="images/question.png" id="amb" alt="" />
								   <span dojoType="tooltip" connectId="amb" delay="1" toggle="explode">Number of ambiguous base pairs</span></td>
			<td class="label">Lab.</td>
			<td class="label">Accession</td>
			<td class="label">local Blast</td>
			<td class="label2">ncbi Blast</td>
		</tr>
	<!-- 		Here goes script to get all the sequences for this voucher		 -->
		<?php getSeqs($row->code, $host, $user, $pass, $db, $p_); ?>
		<!-- 		end script -->
	
	</td></tr>
	<tr><td colspan="2">
	
	<table width="380px" cellspacing="0" border="0">
	<caption>Publication and Notes</caption>
		<tr>
			<td class="label2">Published in</td>
			<td class="label3">Notes</td>
		</tr>
		<tr>
			<td class="field"><?php echo $row->publishedIn; ?>&nbsp;</td>
			<td class="field2"><?php echo $row->notes; ?>&nbsp;</td>
		</tr>
	</table>
	
	</td></tr>
	</table><!-- end table child 2 -->

</td></tr>
</table><!-- end big parent table -->

</td>

<td class="sidebar" valign="top">
	<?php
		yahoo_map($row->latitude, $row->longitude);
		echo "<br />";
		echo "<br />";
		make_sidebar(); 
	?>
</td>

</tr>
</table> <!-- end super table -->

</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url);
?>

	<?php
	}
else
	{
	?>
	
	<p>
	<img src="images/warning.png" alt="" /><span class="text">That record could not be located in our database.</span>
	
	<?php
	}
	
// close database connection
mysql_close($connection);
?>

</body>
</html>
