<?php
// #################################################################################
// #################################################################################
// Voseq create_dataset.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Script for input to dataset creation
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

// print beginning of html page -- headers
include_once'includes/header.php';
nav();
// #################################################################################
// Section: Quering DB for genecodes and taxon sets
// #################################################################################
// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
$result = mysql_query("set names utf8") or die("Error in query: $query. " . mysql_error());
$gCquery = "SELECT geneCode FROM ". $p_ . "genes ORDER BY geneCode";
$gCresult = mysql_query($gCquery) or die("Error in query: $query. " . mysql_error());
// if records present
$geneCodes_array = array();
if( mysql_num_rows($gCresult) > 0 ) {
	while( $row = mysql_fetch_object($gCresult) ) {
		$geneCodes_array[] = $row->geneCode;
	}
}
// create dataset list
$Tsquery = "SELECT taxonset_name FROM ". $p_ . "taxonsets ORDER BY taxonset_name";
$Tsresult = mysql_query($Tsquery) or die ("Error in query: $Tsquery. " . mysql_error());
// if records present
$taxonsets = array();
if( mysql_num_rows($gCresult) > 0 ) {
	while( $rowTs = mysql_fetch_object($Tsresult) ) {
		$taxonsets[] = $rowTs->taxonset_name;
	}
}
else {unset($taxonsets);}
// #################################################################################
// Section: Output
// #################################################################################
// begin HTML page content
echo "<div id=\"content\">";
?>


<form action="includes/process_dataset.php" method="post">
<table border="0" width="960px" cellpadding="5px"> <!-- super table -->
	<tr>
		<td valign="top" colspan="2">
			<h1>Create dataset</h1>
			<table border="0" width="800px" cellspacing="0" cellpadding="0">
				<caption>Enter the required info to make yourself a ready-to-run dataset</caption>
				<tr>
					<td class="label">Choose file format:</td>
					<td class="field">
						<input type="radio" name="format" value="TNT">TNT format<br />
						<input type="radio" name="format" value="NEXUS">NEXUS format<br />
						<input type="radio" name="format" value="PHYLIP">PHYLIP format<br />
						<input type="radio" name="format" value="FASTA" checked>Unaligned FASTA format
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<br />
						Outgroup (code, for NEXUS and TNT): <input type="text" name="outgroup" size="10">
					</td>
				</tr>
				<tr>
					<td class="label">Choose codon positions to use<br /><br /> (Override priority: <br />Amino Acids->Special->All->1st&#151;2nd,3rd):</td>
					<td class="field">
						<input type="checkbox" name="positions[all]" checked>all 
						<input type="checkbox" name="positions[1st]">1st 
						<input type="checkbox" name="positions[2nd]">2nd 
						<input type="checkbox" name="positions[3rd]">3rd 
						<input type="checkbox" name="positions[special]">Special
						<input type="checkbox" name="positions[aas]">Amino acids<br />
						Partition by (positions):
						<input type="radio" name="by_positions" value="asone" checked>as one 
						<input type="radio" name="by_positions" value="each">each  
						<input type="radio" name="by_positions" value="123">1st&#151;2nd, 3rd<br />
						<img src="images/warning.png" /> Warning! your dataset will not necessarily be properly aligned!
					</td>
				</tr>
				<tr>
					<td class="label">
						What info do you want in the taxon names?
					</td>
					<td class="field">
						<input type="checkbox" name="taxonadds[code]"checked>Code
						<input type="checkbox" name="taxonadds[orden]">Order
						<input type="checkbox" name="taxonadds[family]">Family
						<input type="checkbox" name="taxonadds[subfamily]">Subfamily
						<input type="checkbox" name="taxonadds[tribe]">Tribe
						<input type="checkbox" name="taxonadds[subtribe]">Subtribe

						<br />

						<input type="checkbox" name="taxonadds[genus]"checked>Genus
						<input type="checkbox" name="taxonadds[species]"checked>Species
						<input type="checkbox" name="taxonadds[subspecies]">Subspecies
			
						<input type="checkbox" name="taxonadds[hostorg]">Host org.
						<input type="checkbox" name="taxonadds[genecode]">Gene code 
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
						if (isset($taxonsets)){
						foreach ($taxonsets as $taxonset){ echo "<option value=\"$taxonset\">$taxonset</option> ";}
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label4">
						...and/or a list of codes:<br />
						(with a -- sign before taxon names<br />
						to disable them from the taxon set)<br />
					</td>
					<td class="field1">
						One code per line, <br />for example: <br />
						&nbsp;&nbsp;&nbsp;tA1<br />
						&nbsp;&nbsp;&nbsp;--S077<br />
						&nbsp;&nbsp;&nbsp;and so on...<br />
					</td>
					<td>
					</td>
					<td class="label4">
						Check to select your Gene codes:
					</td>
					<td class="field1">
						<?php $i = 0;
								foreach ($geneCodes_array as $genes) {
									$i = $i +1;
									echo "<input type=\"checkbox\" name=\"geneCodes[$genes]\" />$genes"; 
									if ($i == 4) {
										echo "<br />";
										$i = 0;
									}
								} 
						?>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<textarea name="codes" rows="10">
						</textarea>
					</td>
				</tr>
				<tr>
					<td>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td align="Left" valign="top">
						<input type="submit" name="process_dataset" value="Create dataset" />
					</td>
				</tr>
			</table>
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
