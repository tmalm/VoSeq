<?php
// #################################################################################
// #################################################################################
// Voseq admin/add.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Add and update voucher data in administrator interface
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check admin login session
include'../login/auth-admin.php';

error_reporting (E_ALL ^ E_NOTICE);

// includes
#include '../login/redirect.html';
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes
include 'adfunctions.php'; // administrator functions
include 'admarkup-functions.php';
include '../includes/validate_coords.php';
include '../functions.php';

foreach($_GET as $k => $v) {
	$v = clean_string($v);
	$_GET[$k] = $v[0];
}

// no direct access
if (!$_GET['new'] && !$_POST['submitNew'] && !$_POST['submitNoNew'] &&  !$_GET['code'] ) {
	die( 'Restricted access' );
	}


// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'Tooltip';
$whichDojo[] = 'ComboBox';

// to indicate this is an administrator page
$admin = true;



// #################################################################################
// Section: previous and next links
// #################################################################################
if ( isset($_GET['search']) || trim($_GET['search']) != '') {
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}

// generate and execute query
$id = $_GET['code'];
$query = "SELECT id FROM ". $p_ . "vouchers WHERE code = '$id'";
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
// get result set as object
$row = mysql_fetch_object($result);
$current_id = $row->id;

// get previous and next links from search and search_results tables
$current_id_search_id = $_GET['search'];

// current id of this record in search_results ids
$query_c_id_t_r  = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND record_id='$current_id'";
$result_c_id_t_r = mysql_query($query_c_id_t_r) or die("Error in query: $query_c_id_t_r. " . mysql_error());
$row_c_id_t_r    = mysql_fetch_object($result_c_id_t_r);

$link_current  = $row_c_id_t_r->id;

// link previous
$link_previous = $link_current - 1;
$query_link_previous      = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_previous'";
$result_link_previous     = mysql_query($query_link_previous) or die("Erro in query: $query_link_previous. " . mysql_error());
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
} // end previous and next links




// #################################################################################
// Section: empty form for entering data and creating new record 
// #################################################################################

// form not yet submitted
// display initial form
if ($_GET['new']) {
	// brand new record
	// process title
	$title = $config_sitename;

	// print html headers
	include_once('../includes/header.php');

	// print navegation bar
	admin_nav();

	// begin HTML page content
	echo "<div id=\"content\">";
	?>

	<b>You can create a new record by adding detailed information about it. The required fields are "Code in VoSeq" and "Genus". <br />
	VoSeq will make sure that all codes are unique and will issue an error message if you submit a duplicate code.</b>

	<table border="0" width="960px"> <!-- super table -->
	<tr><td valign="top">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

	<table width="800" border="0"> <!-- big parent table -->
	<tr><td valign="top" width="400">
	<table border="0" cellspacing="10"> <!-- table child 1 -->
	<tr><td>
	
	<table width="350" cellspacing="0" border="0">
	<caption>Specimen name</caption>
		<tr>
			<td class="label">Order</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_orden.js" style="width: 90px;" name="orden" maxListLength="20">
				</select></td>
			<td width="20">&nbsp;</td>
			<td class="label">Subfamily</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_subfamily.js" style="width: 90px;" name="subfamily" maxListLength="20">
				</select></td>
		</tr>
		<tr>
			<td class="label">Family</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_family.js" style="width: 90px;" name="family" maxListLength="20">
				</select></td>
			<td>&nbsp;</td>
			<td class="label">Tribe</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_tribe.js" style="width: 90px;" name="tribe" maxListLength="20">
				</select>
			</td>
		</tr>
		<tr>
			<td class="label">Genus</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_genus.js" style="width: 90px;" name="genus" maxListLength="20">
				</select></td>
			<td>&nbsp;</td>
			<td class="label">Subtribe</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_subtribe.js" style="width: 90px;" name="subtribe" maxListLength="20">
				</select>
			</td>
		</tr>
		<tr>
			<td class="label">Species</td>
			<td class="field">
			<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_species.js" style="width: 90px;" name="species" maxListLength="20">
				</select></td>
			<td>&nbsp;</td>
			<td class="label">Host org.</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_hostorg.js" style="width: 90px;" name="hostorg" maxListLength="20">
				</select>
			</td>
		</tr>
		<tr>
			<td class="label">Subspecies</td>
			<td class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_subspecies.js" style="width: 90px;" name="subspecies" maxListLength="20">
				</select>
			</td>
						<td>&nbsp;</td>
			<td class="label" >Auctor</td>
			<td class="field" ><input size="17" maxlength="250" type="text" name="auctor" /></td>
		</tr>
		<tr>
			
			<td class="label2" colspan="2">Type species?</td>
			<td>&nbsp;</td>
			<td class="field" colspan="2" align="center"><input type="radio" name="typeSpecies" value="1"> Yes<input type="radio" name="typeSpecies" value="2" /> No</td>
		</tr>
	</table>
	
	</td></tr>
	<tr><td>
	
	<table width="350" cellspacing="0" border="0">
	<caption>Locality Information</caption>
		<tr><td colspan="3" class="label2">Country</td></tr>
		<tr><td colspan="3" class="field">
			  <select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_country.js" style="width: 120px;" name="country" maxListLength="20">
				</select>
			 </td>
		</tr>
		
		<tr><td colspan="3" class="label2">Specific Locality</td></tr>
		<tr><td colspan="3" class="field"><input size="30" maxlength="250" type="text" name="specificLocality" /></td></tr>
		
		<tr><td class="label2">Latitude <img src="images/question.png" id="latitude" alt="" /></td>
										<span dojoType="tooltip" connectId="latitude" delay="1" toggle="explode">Enter coordinates in <i><b>decimal numbers</b></i> with up to 6 decimal digits.<br />Example:<br />13&deg;08'N = <b>13.133333</b><br />13&deg;08'S = <b>-13.133333</b></span>

			 <td class="label3">Longitude <img src="images/question.png" id="longitude" alt="" /></td>
										<span dojoType="tooltip" connectId="longitude" delay="1" toggle="explode">Enter coordinates in <i><b>decimal numbers</b></i> with up to 6 decimal digits.<br />Example:<br />69&deg;36'E = <b>69.60</b><br />60&deg;36'W = <b>-69.600000</b></span>

			 <td class="label3">Altitude</td>
		</tr>
		<tr><td class="field"><input size="12" maxlength="250" type="text" name="latitude" /></td>
			 <td class="field2"><input size="12" maxlength="250" type="text" name="longitude" /></td>
			 <td class="field2"><input size="12" maxlength="250" type="text" name="altitude" /></td></tr>
	</table>
	
	</td></tr>
	<tr><td>
	
	<table width="350" cellspacing="0" border="0">
	<caption>Collector Information</caption>
		<tr>
			<td class="code">Code in VoSeq</td>
			<td class="label3">Collector</td>
			<td class="label3">Date (yyyy-mm-dd)</td>
		</tr>
		<tr>
			<td class="field3">
				<input size="12" maxlength="250" type="text" name="code" />
			</td>
			<td class="field2">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_collector.js" style="width: 90px;" name="collector" maxListLength="20">
				</select></td>
			<td class="field2"><input size="12" maxlength="250" type="text" name="dateCollection" /></td>
		</tr>
		
		<tr>
			<td class="label2">Voucher Locality</td>
			<td class="label3">Voucher <img width="15px" height="16px" src="images/question.png" id="voucher" alt="" />
								 <span dojoType="tooltip" connectId="voucher" delay="1" toggle="explode">-Spread?<br /> -Unspread?<br /> -In Slide?</span></td>
			<td class="label3">Voucher Code</td>
		</tr>
		<tr>
			<td class="field"><input size="12" maxlength="250" type="text" name="voucherLocality" /></td>
			<td class="field2"><input size="12" maxlength="250" type="text" name="voucher" /></td>
			<td class="field2"><input size="12" maxlength="250" type="text" name="voucherCode" /></td>
		</tr>
		<tr>
			<td class="label2">Determined by:</td><td class="label3" colspan="2">Sex</td>
		</tr>
		<tr>
			<td class="field"><input size="12" maxlength="250" type="text" name="determinedBy" /></td><td class="field2" colspan="2"><input type="radio" name="sex" value="larva" /> Larva<input type="radio" name="sex" value="male" /> Male<input type="radio" name="sex" value="female" /> Female
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
			<td class="field"><input size="12" maxlength="250" type="text" name="extraction" /></td>
			<td class="field2"><input size="12" maxlength="250" type="text" name="extractionTube" /></td>
		</tr>
		
		<tr>
			<td colspan="2" class="label2">Extractor <img width="15px" height="16px" src="images/question.png" id="extractor" alt="" />
								 <span dojoType="tooltip" connectId="extractor" delay="1" toggle="explode">Person that performed the DNA extraction</span></td>
		</tr>
		<tr>
			<td colspan="2" class="field">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_extractor.js" style="width: 155px;" name="extractor" maxListLength="20">
				</select>
			</td>
		</tr>
		
		<tr>
			<td class="label">Date (yyyy-mm-dd)</td>
			<td class="field"><input size="12" maxlength="250" type="text" name="dateExtraction" /></td>
		</tr>
	</table>
	
	</td>
	<td>
		&nbsp;
	</td>
	
	</tr>
	<tr><td colspan="2">
	
	<table width="160" cellspacing="0" border="0">
	<caption>Publication and Notes</caption>
		<tr>
			<td width="40%" class="label2">Published in</td>
		</tr>
		<tr>
			<td class="field"><textarea rows="5" cols="20" name="publishedIn"></textarea></td>
		</tr>
		<tr>
			<td class="label2">Notes</td>
		</tr>
		<tr>
			<td class="field"><textarea rows="5" cols="20" name="notes"></textarea></td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>
				<input type="submit" name="submitNew" value="Add record" />
			</td>
		</tr>
	</table>
	
	</td></tr>
	</table><!-- end table child 2 -->

	</td></tr>
	</table><!-- end big parent table -->
	
	</td>

	<td class="sidebar" valign="top">
	<?php admin_make_sidebar(); ?>
	</td>

	</tr>
	</table> <!-- end super table -->

	</div> <!-- end content -->
	
	<!-- standard page footer begins -->
	<?php
	make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
	?>
	
	<?php
	}

// #################################################################################
// Section: upload data to MySQL
// #################################################################################
elseif ($_POST['submitNew']) {
	// set up error list array
	$errorList = array();
	
	//validate text input fields
	if (trim($_POST['code']) == '')
		{
		$errorList[] = "Invalid entry: <b>Code</b>";
		}
	
	if (trim($_POST['genus']) == '')
		{
		$errorList[] = "Invalid entry: <b>Genus</b>";
		}

	if( $_POST['latitude'] != NULL && !validate_lat($_POST['latitude'])) {
		echo $valid_lat;
		$errorList[] = "Enter latitude coordinates in <i><b>decimal numbers</b></i> with up to 6 decimal digits.
					    <br />Example:<br />13&deg;08'N = <b>13.133333</b><br />13&deg;08'S = <b>-13.133333</b>";
	}

	if( $_POST['longitude'] != NULL && !validate_long($_POST['longitude'])) {
		$errorList[] = "Enter longitude coordinates in <i><b>decimal numbers</b></i> with up to 6 decimal digits.
					    <br />Example:<br />13&deg;08'N = <b>13.133333</b><br />13&deg;08'S = <b>-13.133333</b>";
	}
		
	#  The utf8_encode might be causing problems, C. Pena 2012-02-02
	$code              = $_POST['code'];
	$extractor         = $_POST['extractor'];
	#$extractor         = utf8_encode($_POST['extractor']);
	$genus             = $_POST['genus'];
	$orden             = $_POST['orden'];
	$family            = $_POST['family'];
	$subfamily         = $_POST['subfamily'];
	$tribe             = $_POST['tribe'];
	$subtribe          = $_POST['subtribe'];
	$species           = $_POST['species'];
	$subspecies        = $_POST['subspecies'];
	$auctor            = $_POST['auctor'];
	$typeSpecies       = $_POST['typeSpecies'];
	#if ($typeSpecies != "1" || $typeSpecies != "2") {$typeSpecies = "0";}
	$country           = $_POST['country'];
	$specificLocality  = $_POST['specificLocality'];
	#$specificLocality  = utf8_encode($_POST['specificLocality']);
	$latitude          = $_POST['latitude'];
	$longitude         = $_POST['longitude'];
	$altitude          = $_POST['altitude'];
	$collector         = $_POST['collector'];
	#$collector         = utf8_encode($_POST['collector']);
	$dateCollection    = $_POST['dateCollection'];
	$voucherLocality   = $_POST['voucherLocality'];
	$voucher           = $_POST['voucher'];
	$determinedBy      = $_POST['determinedBy'];
	$sex               = $_POST['sex'];
	$hostorg           = $_POST['hostorg'];
	#$hostorg           = utf8_encode($_POST['hostorg']);
	$voucherCode       = $_POST['voucherCode'];
	$extraction        = $_POST['extraction'];
	$extractionTube    = $_POST['extractionTube'];
	$dateExtraction    = $_POST['dateExtraction'];

	if( $dateExtraction == "" ) {
		unset($dateExtraction);
	}
	else {
		$dElist = explode("-", $dateExtraction);
		if( count($dElist) != 3 ) {
			$errorList[] = "The Extraction date is not following standard.
					    <br />Please add it accordingly: YYYY-MM-DD";
		}
		elseif( checkdate($dElist[1],$dElist[2],$dElist[0]) == FALSE ) {
			$errorList[] = "The Extraction date is not following standard.
					    <br />Please add it accordingly: YYYY-MM-DD";
		}
	}

	$publishedIn       = $_POST['publishedIn'];
	#$publishedIn       = utf8_encode($_POST['publishedIn']);
	$notes             = $_POST['notes'];
	#$notes             = utf8_encode($_POST['notes']);
	$latesteditor      = $_SESSION['SESS_FIRST_NAME']. " ". $_SESSION['SESS_LAST_NAME'];
	#$latesteditor      = utf8_encode($_SESSION['SESS_FIRST_NAME']. " ". $_SESSION['SESS_LAST_NAME']);
	// check for errors
	// if none found ...
	if (sizeof($errorList) == 0 )
		{
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}
		
		// check for duplicate code
		$queryCode = "SELECT * FROM ". $p_ . "vouchers WHERE code='$code'";
		$resultCode = mysql_query($queryCode) or die ("Error in query: $queryCode. " . mysql_error());
		if (mysql_num_rows($resultCode) > 0)
			{
			// process title
			$title = "$config_sitename - Error, duplicate code";
			
			// print html headers
			include_once('../includes/header.php');
			admin_nav();
			
			// begin HTML page content
			echo "<div id=\"content_narrow\">";
			echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
			echo "<img src=\"../images/warning.png\" alt=\"\">
						The record's <b>code</b> you entered is already preoccupied.<br />There can't be two records with the same code!.<br />Please click \"Go back\" in your browser and enter a different code.</td>";
			echo "<td class=\"sidebar\" valign=\"top\">";
			admin_make_sidebar();
			echo "</td>";
			echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
			make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
			echo "\n</body>\n</html>";
			exit();
			}
		else
			{	
			//setting the edits add values
			if( function_exists(mysql_set_charset) ) {
				mysql_set_charset("utf8");
			}
			$editsadd = "Added by ". $latesteditor ." on ";
			mysql_query("time for add-list");
			$querytime = "SELECT NOW()";
			$resulttime = mysql_query($querytime) or die ("Error in query: $querytime. " . mysql_error());
			$rowtime    = mysql_result($resulttime,0);
			$editsadd = $editsadd . $rowtime;
			$editsadd = $editsadd; 

			// avoid SQL injection
			$code = mysql_real_escape_string($code);
			$extractor = mysql_real_escape_string($extractor);
			$genus = mysql_real_escape_string($genus);
			$orden = mysql_real_escape_string($orden);
			$family = mysql_real_escape_string($family);
			$subfamily = mysql_real_escape_string($subfamily);
			$tribe = mysql_real_escape_string($tribe);
			$subtribe = mysql_real_escape_string($subtribe);
			$species = mysql_real_escape_string($species);
			$subspecies = mysql_real_escape_string($subspecies);
			$auctor = mysql_real_escape_string($auctor);
			$typeSpecies = mysql_real_escape_string($typeSpecies);
			$country = mysql_real_escape_string($country);
			$specificLocality = mysql_real_escape_string($specificLocality);
			$altitude = mysql_real_escape_string($altitude);
			$collector = mysql_real_escape_string($collector);
			$dateCollection = mysql_real_escape_string($dateCollection);
			$voucherLocality = mysql_real_escape_string($voucherLocality);
			$voucher = mysql_real_escape_string($voucher);
			$determinedBy = mysql_real_escape_string($determinedBy);
			$sex = mysql_real_escape_string($sex);
			$hostorg = mysql_real_escape_string($hostorg);
			$voucherCode = mysql_real_escape_string($voucherCode);
			$extraction = mysql_real_escape_string($extraction);
			$extractionTube = mysql_real_escape_string($extractionTube);
			if (isset($dateExtraction)){$dateExtraction = mysql_real_escape_string($dateExtraction);}
			$publishedIn = mysql_real_escape_string($publishedIn);
			$notes = mysql_real_escape_string($notes);
			$latitude = mysql_real_escape_string($latitude);
			$longitude = mysql_real_escape_string($longitude);
			$editsadd = mysql_real_escape_string($editsadd);
			$latesteditor = mysql_real_escape_string($latesteditor);
			
			// generate and execute query
			$query = "INSERT INTO ". $p_ . "vouchers(code, extractor, genus, orden, family, subfamily, tribe, subtribe, species, subspecies, auctor, country, specificLocality, altitude, collector, dateCollection, voucherLocality, voucher, determinedBy, sex, hostorg, voucherCode, extraction, extractionTube, publishedIn, notes, ";
			if (isset($dateExtraction)){$query .= "dateExtraction, "; }
			if ($latitude != NULL ) { 
				$query .= "latitude, ";
			}
			if ($longitude != NULL) {
				$query .= "longitude, ";
			}
			$query .= "typeSpecies, timestamp, edits, latesteditor) VALUES ('$code', '$extractor', '$genus', '$orden', '$family', '$subfamily', '$tribe', '$subtribe', '$species', '$subspecies', '$auctor', '$country', '$specificLocality', '$altitude', '$collector', '$dateCollection', '$voucherLocality', '$voucher', '$determinedBy','$sex', '$hostorg', '$voucherCode', '$extraction', '$extractionTube', '$publishedIn', '$notes', ";
			if (isset($dateExtraction)){$query .= "'$dateExtraction', "; }
			if ($latitude != NULL) {
				$query .= "\"$latitude\", ";
			}
			if ($longitude != NULL) {
				$query .= "\"$longitude\", ";
			}
			if( !isset($typeSpecies) || $typeSpecies == "" ) {
				$query .= "NULL,";
			}
			else {
				$query .= "'$typeSpecies',";
			}
			$query .= "NOW(), '$editsadd', '$latesteditor' )";
		
			$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
			
			// process title
			$title = "$config_sitename - Record " . $code . " created";

			// print html headers
			include_once('../includes/header.php');

			// print navegation bar
			admin_nav();

			// begin HTML page content
			echo "<div id=\"content_narrow\">";
			echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
			// print result
			echo "<span class=\"title\"><img src=\"images/success.png\" alt=\"\"> Record creation was successful!</span>";
			}
		
		?>
				Do you want to:
				<ol>
				<li>Upload a picture for record of code <b><?php echo "$code"; ?></b>:
				<!-- 		upload file -->
				<table>
				<tr><td>
				<form enctype="multipart/form-data" action="processfile.php?code=<?php echo "$code"; ?>" method="post">
    			<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
    			<input name="userfile" type="file" size="40" /><br />
    			<input type="submit" name="submit" value="Upload" />
				</form>
				</td>
				</tr>
				</table></li>
				<li>Enter sequences for record of code <b><?php echo "$code"; ?></b>: <?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('listseq.php?code=<?php echo "$code"; ?>');">Add Sequences</a></li>
				<li><?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add.php?new=new');">Add another new record</a>.</li>
				<li><?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('admin.php');">Go back to the main menu</a>.</li>
				</ol>
				</td>
		<?php
			echo "<td class=\"sidebar\" valign=\"top\">";
			admin_make_sidebar();
			echo "</td>";
			echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
			make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		}
	else
		{
		// error found
		
		// get title
		$title = "$config_sitename - Error, missing info";
		
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
		
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		echo "<img src=\"../images/warning.png\" alt=\"\"> The following errors were encountered:";
		echo '<br>';
		echo '<ul>';
		for ($x=0; $x<sizeof($errorList); $x++)
			{
			echo "<li>$errorList[$x]";
			}
		echo '</ul>
				You need to fill up at least two fields: code and genus!';
		echo "</td>";
		echo "<td class=\"sidebar\" valign=\"top\">";
			admin_make_sidebar();
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		}
	}

// #################################################################################
// Section: prefilled form for updating voucher data
// #################################################################################
elseif (!$_POST['submitNoNew'] && $_GET['code']) {
	// record to update
	// get values to prefill fields
	$code1 = $_GET['code'];
	@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}

	// check for duplicate code
	$query1  = "SELECT id, code, extractor, genus, orden, family, subfamily, tribe, subtribe, species, subspecies, auctor, typeSpecies, country, specificLocality, latitude, longitude, altitude, collector, dateCollection, voucherLocality, voucher, determinedBy, sex, hostorg, voucherCode, extraction, extractionTube, dateExtraction, publishedIn, notes, edits, voucherImage, thumbnail FROM ". $p_ . "vouchers WHERE code='$code1'";
	$result1 = mysql_query($query1) or die ("Error in query: $query1. " . mysql_error());
	$row1    = mysql_fetch_object($result1);
	
	// get title
	$title = "$config_sitename - Edit " . $code1;
				
	// print html headers
	include_once('../includes/header.php');
	admin_nav();
				
	// begin HTML page content
	echo "<div id=\"content\">";
	
	?>
	
<!-- 	show previous and next links -->
	<?php
	echo "<h1>" . "$code1" . "</h1>";
	echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
			<tr><td valign=\"top\">";
			
	if ( isset($_GET['search']) || trim($_GET['search']) != '')
		{
		if ($link_previous)
			{ ?>
			<span dojoType="tooltip" connectId="previous" delay="1" toggle="explode">Previous</span>
			<?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add.php?code=<?php echo $prevCode; ?>&amp;search=<?php echo $current_id_search_id; ?>');"><img src="images/leftarrow.png" class="link" alt="previous" id="previous" /></a>&nbsp;&nbsp;
			<?php
			}
		else
			{
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		
		if ($link_next)
			{ ?>
			<span dojoType="tooltip" connectId="next" delay="1" toggle="explode">Next</span>
			<?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add.php?code=<?php echo $nextCode; ?>&amp;search=<?php echo $current_id_search_id; ?>');"><img src="images/rightarrow.png" class="link" alt="next" id="next" /></a>
			<?php
			}
		else
			{
			echo "&nbsp;";
			}
		}
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<table width="760px" border="0">
		<!-- big parent table -->
		<tr>
			<td valign="top" width="400">
				<table border="0" cellspacing="10">
					<!-- table child 1 -->
					<tr>
						<td>
							<table width="350px" cellspacing="0" border="0">
								<!-- 	input id of this record also, useful for changing the code -->
								<input type="hidden" name="id" value="<?php echo $row1->id; ?>" />
								<!-- 	end input id -->
								<caption>Specimen name</caption>
								<tr>
									<td class="label">Order</td><td class="field"><input size="12" maxlength="250" type="text" name="orden" value="<?php echo $row1->orden; ?>" /></td>
									<td width="20">&nbsp;</td>
									<td class="label">Subfamily</td><td class="field"><input size="12" maxlength="250" type="text" name="subfamily" value="<?php echo $row1->subfamily; ?>" /></td>
								</tr>
								<tr>
									<td class="label">Family</td><td class="field"><input size="12" maxlength="250" type="text" name="family" value="<?php echo $row1->family; ?>" /></td>
									<td width="20">&nbsp;</td>
									<td class="label">Tribe</td><td class="field"><input size="12" maxlength="250" type="text" name="tribe" value="<?php echo $row1->tribe; ?>" /></td>
								</tr>
								<tr>
									<td class="label">Genus</td><td class="field"><input size="12" maxlength="250" type="text" name="genus" value="<?php echo $row1->genus; ?>" /></td>
									<td width="20">&nbsp;</td>
									<td class="label">Subtribe</td><td class="field"><input size="12" maxlength="250" type="text" name="subtribe" value="<?php echo $row1->subtribe; ?>" /></td>
								</tr>
								<tr>
									<td class="label">Species</td><td class="field"><input size="12" maxlength="250" type="text" name="species" value="<?php echo $row1->species; ?>" /></td>
									<td width="20">&nbsp;</td>
									<td class="label">Host org.</td><td class="field"><input size="12" maxlength="250" type="text" name="hostorg" value="<?php echo $row1->hostorg; ?>" /></td>
								</tr>
								<tr>
									<td class="label">Subspecies</td><td class="field"><input size="12" maxlength="250" type="text" name="subspecies" value="<?php echo $row1->subspecies; ?>" /></td>
									<td width="20">&nbsp;</td>
									<td class="label">Auctor</td><td class="field"><input size="12" maxlength="250" type="text" name="auctor" value="<?php echo $row1->auctor; ?>" /></td>
								</tr>
								<tr>
									<td class="label2" colspan="2" >Type species?</td><td width="20">&nbsp;</td><td class="field" colspan="2" align="center"><input type="radio" name="typeSpecies" value="1" <?php if ($row1->typeSpecies == 1) { echo "checked"; } ?> /> Yes<input type="radio" name="typeSpecies" value="2" <?php if ($row1->typeSpecies == 2) { echo "checked"; } ?> /> No</td>
								</tr>
							</table>

						</td>
					</tr>
					<tr>
						<td>

							<table width="350px" cellspacing="0" border="0">
								<caption>Locality Information</caption>
								<tr>
									<td colspan="3" class="label2">Country</td>
								</tr>
								<tr>
									<td colspan="3" class="field"><input size="40" maxlength="250" type="text" name="country" value="<?php echo $row1->country; ?>" /></td></tr>
								<tr>
									<td colspan="3" class="label2">Specific Locality</td></tr>
								<tr>
									<td colspan="3" class="field"><input size="40" maxlength="250" type="text" name="specificLocality" value="<?php echo $row1->specificLocality; ?>" /></td></tr>
								<tr>
									<td class="label2">Latitude <img src="images/question.png" id="latitude" alt="" /></td>
									<span dojoType="tooltip" connectId="latitude" delay="1" toggle="explode">Enter coordinates in <i><b>decimal numbers</b></i> with up to 6 decimal digits.<br />Example:<br />13&deg;08'N = <b>13.133333</b><br />13&deg;08'S = <b>-13.133333</b></span>
									<td class="label3">Longitude <img src="images/question.png" id="longitude" alt="" /></td>
									<span dojoType="tooltip" connectId="longitude" delay="1" toggle="explode">Enter coordinates in <i><b>decimal numbers</b></i> with up to 6 decimal digits.<br />Example:<br />69&deg;36'E = <b>69.60</b><br />60&deg;36'W = <b>-69.600000</b></span>
									<td class="label3">Altitude</td>
								</tr>
								<tr>
									<td class="field"><input size="12" maxlength="250" type="text" name="latitude" value="<?php echo $row1->latitude; ?>" /></td>
									<td class="field2"><input size="12" maxlength="250" type="text" name="longitude" value="<?php echo $row1->longitude; ?>" /></td>
									<td class="field2"><input size="12" maxlength="250" type="text" name="altitude" value="<?php echo $row1->altitude; ?>" /></td>
								</tr>
							</table>
								
						</td>
					</tr>
					<tr>
						<td>
	
							<table width="350px" cellspacing="0" border="0">
							<caption>Collector Information</caption>
								<tr>
									<td class="code">Code in VoSeq</td>
									<td class="label3">Collector</td>
									<td class="label3">Date (yyyy-mm-dd)</td>
								</tr>
								<tr>
									<td class="field3"><input size="12" maxlength="250" type="text" name="code" value="<?php echo $row1->code; ?>" />
														<input type="hidden" name="old_code" value="<?php echo $row1->code; ?>" />
									</td>
									<td class="field2"><input size="12" maxlength="250" type="text" name="collector" value="<?php echo $row1->collector; ?>" /></td>
									<td class="field2"><input size="12" maxlength="250" type="text" name="dateCollection" value="<?php echo $row1->dateCollection; ?>" /></td>
								</tr>
								<tr>
									<td class="label2">Voucher Locality</td>
										<td class="label3">Voucher <img width="15px" height="16px" src="images/question.png" id="voucher" alt="" />
															<span dojoType="tooltip" connectId="voucher" delay="1" toggle="explode">-Spread?<br /> -Unspread?<br /> -In Slide?</span></td>
									<td class="label3">Voucher Code</td>
								</tr>
								<tr>
									<td class="field"><input size="12" maxlength="250" type="text" name="voucherLocality" value="<?php echo $row1->voucherLocality; ?>"/></td>
									<td class="field2"><input size="12" maxlength="250" type="text" name="voucher" value="<?php echo $row1->voucher; ?>" /></td>
									<td class="field2"><input size="12" maxlength="250" type="text" name="voucherCode" value="<?php echo $row1->voucherCode; ?>"/></td>
								</tr>
								<tr>
									<td class="label2">Determined by:</td><td class="label3" colspan="2">Sex</td>
								</tr>
								<tr>
									<td class="field"><input size="12" maxlength="250" type="text" name="determinedBy" value="<?php echo $row1->determinedBy; ?>" /></td>
									<td class="field2" colspan="2"><input type="radio" name="sex" value="larva" <?php if ($row1->sex == 'larva') echo "checked"; ?>/> Larva<input type="radio" name="sex" value="male" <?php if ($row1->sex == 'male') echo "checked"; ?>/> Male<input type="radio" name="sex" value="female" <?php if ($row1->sex == 'female') echo "checked"; ?>/> Female
								</tr>
							</table>

						</td>
					</tr>
				</table>
					<!-- end table child 1 -->

			</td>
			<td valign="top">

				<table border="0" cellspacing="10"> 
				<!-- table child 2 -->
				<tr>
					<td valign="top">
	
							<table width="160px" cellspacing="0" border="0">
							<caption>DNA</caption>
								<tr>
									<td class="label2">Extraction <img width="15px" height="16px" src="images/question.png" id="extraction" alt="" />
														<span dojoType="tooltip" connectId="extraction" delay="1" toggle="explode">Lab extraction kept in box number:</span></td>
									<td class="label3">Tube <img width="15px" height="16px" src="images/question.png" id="tube" alt="" />
														<span dojoType="tooltip" connectId="tube" delay="1" toggle="explode">DNA extraction is in vial number:</span></td>
								</tr>
								<tr>
									<td class="field"><input size="7" maxlength="250" type="text" name="extraction" value="<?php echo $row1->extraction; ?>" /></td>
									<td class="field2"><input size="11" maxlength="250" type="text" name="extractionTube" value="<?php echo $row1->extractionTube; ?>" /></td>
								</tr>
		
								<tr>
									<td colspan="2" class="label2">Extractor <img width="15px" height="16px" src="images/question.png" id="extractor" alt="" />
														<span dojoType="tooltip" connectId="extractor" delay="1" toggle="explode">Person that performed the DNA extraction</span></td>
								</tr>
								<tr>
									<td colspan="2" class="field"><input size="26" maxlength="250" type="text" name="extractor" value="<?php echo $row1->extractor; ?>" /></td>
								</tr>
								
								<tr>
									<td class="label">Date (yyyy-mm-dd)</td>
									<td class="field"><input size="12" maxlength="250" type="text" name="dateExtraction" value="<?php echo $row1->dateExtraction; ?>" /></td>
								</tr>
							</table>

					</td>
					<td width="200px">
						<a href="<?php echo $row1->voucherImage; ?>"><img class="voucher" src="<?php echo $row1->thumbnail; ?>" alt="" /></a>
					</td>
	
				</tr>
				<tr>
					<td colspan="2">
	
						<table width="380px" cellspacing="0" border="0">
						<caption>Publication and Notes</caption>
							<tr>
								<td width="40%" class="label2">Published in</td>
								<td class="label3">Notes</td>
							</tr>
							<tr>
								<td class="field"><textarea rows="5" cols="20" name="publishedIn"><?php echo $row1->publishedIn; ?></textarea></td>
								<td class="field2"><textarea rows="5" cols="20" name="notes"><?php echo $row1->notes; ?></textarea></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2">

									<table width="380px" cellspacing="0" border="0">
										<caption>Record History</caption>
											<tr>
												<td width="40%" class="label2">Record history</td>
											</tr>
											<tr>
												<td class="field"><textarea rows="5" cols="45" name="edits"><?php echo $row1->edits; ?></textarea></td>
											</tr>
											<tr>

												<td>
													<input type="submit" name="submitNoNew" value="Update record" />
												</td>
											</tr>
									</table>

								</td>
							</tr>
						</table><!--end table child 2 -->

					</td>
				</tr>
				</table>
				<!-- end table child 2 -->
				
			</td>
		</tr>
	</table>
		<!-- end big parent table -->
	</form>
</td>
<td class="sidebar" valign="top">
	<?php admin_make_sidebar();  ?>
</td>
</tr>
</table> <!-- end super table -->

</div> <!-- end content -->

<?php
// close database connection
mysql_close($connection);

make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);

	}
elseif ($_POST['submitNoNew'])
	{
	// set up error list array
	$errorList = array();
	
	//validate text input fields
	if (trim($_POST['code']) == '')
		{
		$errorList[] = "Invalid entry: <b>Code</b>";
		}
	
	if (trim($_POST['genus']) == '')
		{
		$errorList[] = "Invalid entry: <b>Genus</b>";
		}

	if( $_POST['latitude'] != NULL && !validate_lat($_POST['latitude'])) {
		echo $valid_lat;
		$errorList[] = "Enter latitude coordinates in <i><b>decimal numbers</b></i> with up to 6 decimal digits.
					    <br />Example:<br />13&deg;08'N = <b>13.133333</b><br />13&deg;08'S = <b>-13.133333</b>";
	}

	if( $_POST['longitude'] != NULL && !validate_long($_POST['longitude'])) {
		$errorList[] = "Enter longitude coordinates in <i><b>decimal numbers</b></i> with up to 6 decimal digits.
					    <br />Example:<br />13&deg;08'N = <b>13.133333</b><br />13&deg;08'S = <b>-13.133333</b>";
	}

	$id1       = $_POST['id'];
	$code1     = $_POST['code'];
	$old_code  = $_POST['old_code'];
	$extractor = $_POST['extractor'];
	$genus     = $_POST['genus'];
	$orden            = $_POST['orden'];
	$family            = $_POST['family'];
	$subfamily         = $_POST['subfamily'];
	$tribe             = $_POST['tribe'];
	$subtribe          = $_POST['subtribe'];
	$species           = $_POST['species'];
	$subspecies        = $_POST['subspecies'];
	$auctor           = $_POST['auctor'];
	$typeSpecies       = $_POST['typeSpecies'];
		//if ($typeSpecies != '1' && $typeSpecies != '2') {$typeSpecies = '0';}
	$country           = $_POST['country'];
	$specificLocality  = $_POST['specificLocality'];
	$latitude          = $_POST['latitude'];
	$longitude         = $_POST['longitude'];
	$altitude          = $_POST['altitude'];
	$collector         = $_POST['collector'];
	$dateCollection    = $_POST['dateCollection'];
	$voucherLocality   = $_POST['voucherLocality'];
	$voucher           = $_POST['voucher'];
	$determinedBy      = $_POST['determinedBy'];
	$sex               = $_POST['sex'];
	$hostorg           = $_POST['hostorg'];
	$voucherCode       = $_POST['voucherCode'];
	$extraction        = $_POST['extraction'];
	$extractionTube    = $_POST['extractionTube'];
	$dateExtraction    = $_POST['dateExtraction'];

	if( $dateExtraction == "" ) {
		unset($dateExtraction);
	}
	else {
		$dElist = explode("-", $dateExtraction);
		if( count($dElist) != 3 ) {
			$errorList[] = "The Extraction date is not following standard.
				    <br />Please add it accordingly: YYYY-MM-DD";
		}
		elseif( checkdate($dElist[1],$dElist[2],$dElist[0]) == FALSE ) {
			$errorList[] = "The Extraction date is not following standard.
					    <br />Please add it accordingly: YYYY-MM-DD";
		}
	}

	$publishedIn       = $_POST['publishedIn'];
	$notes             = $_POST['notes'];
	$latesteditor      = $_SESSION['SESS_FIRST_NAME']. " ". $_SESSION['SESS_LAST_NAME'];
		
	// check for errors
	// if none found ...
	if (sizeof($errorList) == 0 )
		{
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}
		
		// check if submitted code is meant to replace old one
		// get old code
		$queryOldCode = "SELECT code FROM ". $p_ . "vouchers WHERE id='$id1'";
		$resultOldCode = mysql_query($queryOldCode) or die ("Error in query: $queryOldCode. " . mysql_error());
		$rowOldCode    = mysql_fetch_object($resultOldCode);
		$oldCode = $rowOldCode->code;
		// get new code
		$newCode = $code1;
		//  if new code != old code
		if ($oldCode != $newCode)
			{
			// check for duplicate
			$queryCode1 = "SELECT code FROM ". $p_ . "vouchers WHERE code='$newCode'";
			$resultCode1 = mysql_query($queryCode1) or die ("Error in query: $queryCode1. " . mysql_error());
			if (mysql_num_rows($resultCode1) > 0)
				{
				// get title
				$title = "$config_sitename - Error, duplicate code";
				
				// print html headers
				include_once('../includes/header.php');
				admin_nav();
				
				// begin HTML page content
				echo "<div id=\"content_narrow\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
						<tr><td valign=\"top\">";
				echo "<img src=\"../images/warning.png\" alt=\"\">
						The record's <b>code</b> you entered is already preoccupied.<br />
						There can't be two records with the same code!.<br /><br />
						Please click \"Go back\" in your browser and enter a different code.</span>
						</td>";
				echo "<td class=\"sidebar\" valign=\"top\">";
				admin_make_sidebar(); 
				echo "</td>";
				echo "</tr>
					  </table> <!-- end super table -->
					  </div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				exit();
				}
			}
			
		//checking which values are updated and fixing edit list
			$querycompare  = "SELECT id, code, extractor, genus, orden, family, subfamily, tribe, subtribe, species, subspecies, typeSpecies, country, specificLocality, latitude, longitude, altitude, collector, dateCollection, voucherLocality, voucher, determinedBy, sex, hostorg, voucherCode, extraction, extractionTube, dateExtraction, publishedIn, notes FROM ". $p_ . "vouchers WHERE code='$code1'";
			$resultcompare = mysql_query($querycompare) or die ("Error in query: $querycompare. " . mysql_error());
			$rowcompare    = mysql_fetch_object($resultcompare);
			$edvalues = '';
			$edcount = '0';
				if ($id1 != $rowcompare->id) {$edvalues = $edvalues . ", id1"; $edcount = $edcount + 1; }
				if ($code1 != $rowcompare->code) {$edvalues = $edvalues . ", code"; $edcount = $edcount + 1; }
				if ($extractor != $rowcompare->extractor) {$edvalues = $edvalues . ", extractor"; $edcount = $edcount + 1; }
				if ($genus != $rowcompare->genus) {$edvalues = $edvalues . ", genus"; $edcount = $edcount + 1; }
				if ($orden != $rowcompare->orden) {$edvalues = $edvalues . ", orden"; $edcount = $edcount + 1; }
				if ($family != $rowcompare->family) {$edvalues = $edvalues . ", family"; $edcount = $edcount + 1; }
				if ($subfamily != $rowcompare->subfamily) {$edvalues = $edvalues . ", subfamily"; $edcount = $edcount + 1; }
				if ($tribe != $rowcompare->tribe) {$edvalues = $edvalues . ", tribe"; $edcount = $edcount + 1; }
				if ($subtribe != $rowcompare->subtribe) {$edvalues = $edvalues . ", subtribe"; $edcount = $edcount + 1; }
				if ($species != $rowcompare->species) {$edvalues = $edvalues . ", species" ; $edcount = $edcount + 1; }
				if ($subspecies != $rowcompare->subspecies) {$edvalues = $edvalues . ", subspecies" ; $edcount = $edcount + 1; }
				if ($auctor != $rowcompare->auctor) {$edvalues = $edvalues . ", auctor" ; $edcount = $edcount + 1; }
				if ($typeSpecies != $rowcompare->typeSpecies ){ if ( $typeSpecies == '1' || $typeSpecies == '2') {$edvalues = $edvalues . ", type species" ; $edcount = $edcount + 1; }}
				if ($country != $rowcompare->country) {$edvalues = $edvalues . ", country" ; $edcount = $edcount + 1; }
				if ($specificLocality != $rowcompare->specificLocality) {$edvalues = $edvalues . ", specific locality" ; $edcount = $edcount + 1; }
				if ($latitude != $rowcompare->latitude) {$edvalues = $edvalues . ", latitude" ; $edcount = $edcount + 1; }
				if ($longitude != $rowcompare->longitude) {$edvalues = $edvalues . ", longitude" ; $edcount = $edcount + 1; }
				if ($altitude != $rowcompare->altitude) {$edvalues = $edvalues . ", altitude" ; $edcount = $edcount + 1; }
				if ($collector != $rowcompare->collector) {$edvalues = $edvalues . ", collector" ; $edcount = $edcount + 1; }
				if ($dateCollection != $rowcompare->dateCollection) {$edvalues = $edvalues . ", collection date" ; $edcount = $edcount + 1; }
				if ($voucherLocality != $rowcompare->voucherLocality) {$edvalues = $edvalues . ", voucher locality" ; $edcount = $edcount + 1; }
				if ($voucher != $rowcompare->voucher) {$edvalues = $edvalues . ", voucher" ; $edcount = $edcount + 1; }
				if ($determinedBy != $rowcompare->determinedBy) {$edvalues = $edvalues . ", determined by" ; $edcount = $edcount + 1; }
				if ($sex != $rowcompare->sex ){ if ( $sex == 'male' || $sex == 'female' || $sex == 'larva') {$edvalues = $edvalues . ", sex" ; $edcount = $edcount + 1; }}
				if ($hostorg != $rowcompare->hostorg) {$edvalues = $edvalues . ", host org." ; $edcount = $edcount + 1; }
				if ($voucherCode != $rowcompare->voucherCode) {$edvalues = $edvalues . ", voucher code" ; $edcount = $edcount + 1; }
				if ($extraction != $rowcompare->extraction) {$edvalues = $edvalues . ", extraction" ; $edcount = $edcount + 1; }
				if ($extractionTube != $rowcompare->extractionTube) {$edvalues = $edvalues . ", extraction tube" ; $edcount = $edcount + 1; }
				if (isset($dateExtraction) && $dateExtraction != $rowcompare->dateExtraction) {$edvalues = $edvalues . ", Extraction date" ; $edcount = $edcount + 1; }
				if ($publishedIn != $rowcompare->publishedIn) {$edvalues = $edvalues . ", Published in" ; $edcount = $edcount + 1; }
				if ($notes != $rowcompare->notes) {$edvalues = $edvalues . ", notes" ; $edcount = $edcount + 1; }
						//fix edvalues-string
						$edvalues = preg_replace('/, /', '', $edvalues, 1);
						$edvalues = ucfirst($edvalues);
							//setting the edits update values
							$editsed = $edvalues . " edited by ". $_SESSION['SESS_FIRST_NAME']. " ". $_SESSION['SESS_LAST_NAME'] ." on ";
							mysql_query("time for add-list");
							$querytime = "SELECT NOW()";
							$resulttime = mysql_query($querytime) or die ("Error in query: $querytime. " . mysql_error());
							$rowtime    = mysql_result($resulttime,0);
							$editsed = $editsed . $rowtime;
							$queryed = "SELECT edits FROM ". $p_ . "vouchers WHERE id='$id1'";
							$resulted = mysql_query($queryed) or die ("Error in query: $querytime. " . mysql_error());
								//check for empty edits field
								if (mysql_num_rows($resulted) > 0) {
								$rowed    = mysql_result($resulted,0);
									//check for number of edits
									if ($edcount != '0') { 
									$editsed = $editsed . "\n" . $rowed ;
									}
									else { $editsed = $rowed ; }
								}
		if ($edcount != '0') {

		// avoid SQL injection
		$code1 = mysql_real_escape_string($code1);
		$extractor = mysql_real_escape_string($extractor);
		$genus = mysql_real_escape_string($genus);
		$orden = mysql_real_escape_string($orden);
		$family = mysql_real_escape_string($family);
		$subfamily = mysql_real_escape_string($subfamily);
		$tribe = mysql_real_escape_string($tribe);
		$subtribe = mysql_real_escape_string($subtribe);
		$species = mysql_real_escape_string($species);
		$subspecies = mysql_real_escape_string($subspecies);
		$auctor = mysql_real_escape_string($auctor);
		$typeSpecies = mysql_real_escape_string($typeSpecies);
		$country = mysql_real_escape_string($country);
		$latitude = mysql_real_escape_string($latitude);
		$longitude = mysql_real_escape_string($longitude);
		$altitude = mysql_real_escape_string($altitude);
		$collector = mysql_real_escape_string($collector);
		$dateCollection = mysql_real_escape_string($dateCollection);
		$voucherLocality = mysql_real_escape_string($voucherLocality);
		$voucher = mysql_real_escape_string($voucher);
		$determinedBy = mysql_real_escape_string($determinedBy);
		$sex = mysql_real_escape_string($sex);
		$hostorg = mysql_real_escape_string($hostorg);
		$voucherCode = mysql_real_escape_string($voucherCode);
		$extraction = mysql_real_escape_string($extraction);
		$extractionTube = mysql_real_escape_string($extractionTube);
		if (isset($dateExtraction)){$dateExtraction = mysql_real_escape_string($dateExtraction);}
		$publishedIn = mysql_real_escape_string($publishedIn);
		$notes = mysql_real_escape_string($notes);
		$editsed = mysql_real_escape_string($editsed);
		$latesteditor = mysql_real_escape_string($latesteditor);
		$id1 = mysql_real_escape_string($id1);

		// generate and execute query UPDATE
		$query = "UPDATE ". $p_ . "vouchers SET code='$code1', extractor='$extractor', genus='$genus', orden='$orden',family='$family', subfamily='$subfamily', tribe='$tribe', subtribe='$subtribe', species='$species', subspecies='$subspecies', auctor='$auctor', country='$country', specificLocality='$specificLocality', ";
		if ($latitude == NULL) {
			$query .= "latitude=NULL, ";
		}
		else {
			$query .= "latitude=\"$latitude\", ";
		}
		if ($longitude == NULL) {
			$query .= "longitude=NULL, ";
		}
		else {
			$query .= "longitude=\"$longitude\", ";
		}
		if( $typeSpecies == "0" || $typeSpecies == "1" || $typeSpecies == "2" ) {
			$query .= " typeSpecies='$typeSpecies', ";
		}
		if (isset($dateExtraction)) {$query .= "dateExtraction='$dateExtraction', ";}
		$query .= "altitude='$altitude', collector='$collector', dateCollection='$dateCollection', voucherLocality='$voucherLocality', voucher='$voucher', determinedBy='$determinedBy', sex='$sex', hostorg='$hostorg', voucherCode='$voucherCode', extraction='$extraction', extractionTube='$extractionTube',  publishedIn='$publishedIn', notes='$notes', timestamp=NOW(), edits='$editsed', latesteditor='$latesteditor' WHERE id='$id1'";

		$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());


		// update sequences table and primer tables
		if( $old_code != $code1 ) {
			$query = "UPDATE sequences set code='$code1' WHERE code='$old_code'";
			$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());

			$query = "UPDATE primers set code='$code1' WHERE code='$old_code'";
			$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
		}
		
		// get title
		$title = "$config_sitename - Record " . $code1 . " updated";
				
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
				
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		echo "<img src=\"images/success.png\" alt=\"\"> Record update was successful!";
		?>
		Do you want to:
		<ol>
		<li>Upload a picture for record of code <b><?php echo "$code1"; ?></b>:
		<!-- 		upload file -->
		<table>
			<tr><td>
			<form enctype="multipart/form-data" action="processfile.php?code=<?php echo "$code1"; ?>" method="post">
  			<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
   		<input name="userfile" type="file" size="40" /><br />
  			<input type="submit" name="submit" value="Upload" />
			</form>
			</td>
			</tr>
		</table></li>
		<li>Enter sequences for record of code <b><?php echo "$code1"; ?></b>: <?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('listseq.php?code=<?php echo "$code1"; ?>');">Add Sequences</a></li>
		<li><?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add.php?new=new');">Add a new record</a></li>
		<li><?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('admin.php');">Go back to the main menu</a>.</span></li>
		</ol>
		<?php
		echo "</td>";
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); 
		echo "</td>";
		echo "</tr>
			  </table> <!-- end super table -->
			  </div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				
		mysql_close($connection);
		}
		else { 
		header("location:add.php?code=$oldCode"); 
		//echo "<meta http-equiv='refresh' url=''>";
		}
		}
	else
		{
		// error found
		
		// get title
		$title = "$config_sitenae - Error";
				
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
				
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";

		// print as list
		echo "<img src=\"../images/warning.png\" alt=\"\">The following errors were encountered:";
		echo '<br>';
		echo '<ul>';
		for ($x=0; $x<sizeof($errorList); $x++)
			{
			echo "<li>$errorList[$x]";
			}
		echo '</ul>
				Don\'t forget to fill up at least two fields: code and genus';
		echo "</td>";
		echo "<td class=\"sidebar\" valign=\"top\">";
		admin_make_sidebar(); 
		echo "</td>";
		echo "</tr>
			  </table> <!-- end super table -->
			  </div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		}
	}
else
	{
	echo "<div id=\"rest1\"><img src=\"images/warning.png\" alt=\"\" /><span class=\"text\"> Some kind of error ocurred, but I do not know what it is, please try again!</span></div>";
	}
	?>
	
</body>
</html>
