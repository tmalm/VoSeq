<?php
// #################################################################################
// #################################################################################
// Voseq create_table.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Input for creation of (xml) tables
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
//check login session
include'login/auth.php';
// includes
include'markup-functions.php';
ob_start();//Hook output buffer - disallows web printing of file info...
include 'conf.php';
ob_end_clean();//Clear output buffer//includes
#include('functions.php');
#include('includes/yahoo_map.php');
#include('includes/show_coords.php');

// #################################################################################
// Section: Query DB for genecodes and taxonsets
// #################################################################################
// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}

$gCquery = "SELECT geneCode FROM " . $p_ . "genes ORDER BY geneCode";
$gCresult = mysql_query($gCquery) or die("Error in query: $query. " . mysql_error());
// if records present
$geneCodes_array = array();
if( mysql_num_rows($gCresult) > 0 ) {
	while( $row = mysql_fetch_object($gCresult) ) {
		$geneCodes_array[] = $row->geneCode;
	}
}
//create dataset list
$Tsquery = "SELECT taxonset_name FROM " . $p_ . "taxonsets ORDER BY taxonset_name";
$Tsresult = mysql_query($Tsquery) or die ("Error in query: $Tsquery. " . mysql_error());
// if records present
$taxonsets = array();
if( mysql_num_rows($gCresult) > 0 ) {
	while( $rowTs = mysql_fetch_object($Tsresult) ) {
		$taxonsets[] = $rowTs->taxonset_name;
	}
}
else {unset($taxonsets);}

// print beginning of html page -- headers
include_once'includes/header.php';
nav();

// #################################################################################
// Section: Output
// #################################################################################
// begin HTML page content
echo "<div id=\"content\">";
?>

<b>You can create a MS Excel table with specimen codes, genus and species names, genes used in analysis along with their accession numbers. <br /> Instead of
typing your specimen codes in the text area below, you could select a Taxonset (provided that it has been set <a href="admin/add_taxonset.php">here</a>).<br />
This table will be ready to attach to a manuscript for publication.</b>


<form action="includes/make_table.php" method="post">
<table border="0" width="960px" cellpadding="5px"> <!-- super table --> 
	<tr>
		<td valign="top" colspan="2">
			<h1>Create table:</h1>
			<table border="0" width="800px" cellspacing="0" cellpadding="0px">
				<caption>Enter the required info to make yourself a table in MS Excel format</caption>
				<tr>
					<td class="label"> Voucher info: </td>
					<td class="field">
						<input type="checkbox" name="tableadds[code]"checked>Code
						<input type="checkbox" name="tableadds[orden]">Order
						<input type="checkbox" name="tableadds[family]">Family
						<input type="checkbox" name="tableadds[subfamily]">Subfamily
						<input type="checkbox" name="tableadds[tribe]">Tribe
						<input type="checkbox" name="tableadds[subtribe]">Subtribe <br />
						<input type="checkbox" name="tableadds[genus]"checked>Genus
						<input type="checkbox" name="tableadds[species]"checked>Species
						<input type="checkbox" name="tableadds[subspecies]">Subspecies
						<input type="checkbox" name="tableadds[auctor]">Auctor
						<input type="checkbox" name="tableadds[hostorg]">Host org.
					</td>
				</tr>
				<tr>
					<td class="label"> Locality and collector info: </td>
					<td class="field">
						<input type="checkbox" name="tableadds[country]">Country
						<input type="checkbox" name="tableadds[specificLocality]">Locality
						<input type="checkbox" name="tableadds[collector]">Collector
						<input type="checkbox" name="tableadds[dateCollection]">Coll. date
						<input type="checkbox" name="tableadds[determinedBy]">Determined by</br>
						<input type="checkbox" name="tableadds[altitude]">Altitude
						<input type="checkbox" name="tableadds[latitude]">Latitude
						<input type="checkbox" name="tableadds[longitude]">Longitude
					</td>
				</tr>
				<tr>
					<td class="label"> Choose what gene info to display: </td>
					<td class="field">
						<input type="radio" name="geneinfo" value="nobp" checked>Number of bases
						<input type="radio" name="geneinfo" value="accno" >Accession number
						<input type="radio" name="geneinfo" value="x-" >X/- (exist/empty)
					</td>
				</tr>
				<tr>
					<td class="label">
						Choose your field delimitor:
					</td>
					<td class="field">
						<input type="radio" name="field_delimitor" value='comma' >comma  
						<input type="radio" name="field_delimitor" value='tab' checked>tab   <br />
						&nbsp;&nbsp;<b>Display missing sequence beginnings/ends with star(*)?:</b>
						<input type="radio" name="star" value='star'>  
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" colspan="2">
			<table border="0" cellspacing="5px">
				<tr>
					<td class="label4">
						Choose ready-made taxonset
					</td>
					<td class="field1">
						<select name="taxonsets" size="1" style=" BORDER-BOTTOM: outset; BORDER-LEFT: 
						outset; BORDER-RIGHT: outset; BORDER-TOP: outset; FONT-FAMILY: 
						Arial; FONT-SIZE: 12px"> 
						<option selected value="Choose taxonset">Choose taxonset</option> 

						<?php  // create a pulldown-list with all taxon set names in the db
						if (isset($taxonsets)) {
							foreach ($taxonsets as $taxonset){ echo "<option value=\"$taxonset\">$taxonset</option> ";}
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label4">
						...and/or a list of codes,<br />
						one code per line:
					</td>
					<td class="field1">
						For example:<br />
						&nbsp;&nbsp;&nbsp;&nbsp;tA1<br />
						&nbsp;&nbsp;&nbsp;&nbsp;S077<br />
						&nbsp;&nbsp;&nbsp;&nbsp;and so on...
					</td>
					<td>
						&nbsp;
					</td>
					<td class="label4">
						Check to select your Gene codes:
					</td>
					<td class="field1">
						<?php $i = 0;
							foreach ($geneCodes_array as $genes) {
								$i = $i +1;
								echo "<input type=\"checkbox\" name=\"geneCodes[$genes]\">$genes&nbsp;&nbsp;&nbsp;"; 
								if ($i == 4) {
									echo "</br>";
									$i = 0;
								}
							} 
						?>
					</td>
				</tr>
				<tr>
					<td> &nbsp; </td>
					<td class="field1">
						<textarea name="codes" rows="14"></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" name="make_table" value="Create table" />
		</td>
	</tr>
</table>

</form>

</div> <!-- end content -->

<?php
make_footer($date_timezone, $config_sitename, $version, $base_url);
?>
</body>
</html>
