<?php
// #################################################################################
// #################################################################################
// Voseq create_genbank_fasta_file.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Script for input to GeneBank FASTA file creation
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

// print beginning of html page -- headers
include_once'includes/header.php';
nav();
// #################################################################################
// Section: Quering DB for genecodes and taxonsets
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

<form action="includes/make_fasta_genbank.php" method="post">
<h1>Create GenBank FASTA file</h1>
<table border="0" width="800px" cellpadding="0px" cellspacing="5"> <!-- super table -->
	<caption>Enter the required info to make yourself a FASTA file to be submitted to GenBank:</caption>
	<tr>
		<td align="left" class="label4">
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
	<tr>
		<td class="label4">
			...and/or a list of codes:
		</td>
		<td class="field1">
			For example:<br />
			&nbsp;&nbsp;&nbsp;tA1<br />
			&nbsp;&nbsp;&nbsp;S077<br />
			&nbsp;&nbsp;&nbsp;and so on...
		</td>
		<td>
		</td>
		<td class="label4" align="left">
			Check to select your Gene codes:<br />
			<br />
			<!-- COI<br />
			EF1a<br />
			and so on... -->
		</td>
		<td class="field1" align="left" valign="top">
			<?php $i = 0;
					foreach ($geneCodes_array as $genes) {
						$i = $i +1;
						echo "<input type=\"checkbox\" name=\"geneCodes[$genes]\">$genes&nbsp;&nbsp;&nbsp;"; 
						if ($i == 4) {
							echo "</br>";
							$i = 0;
							}
					} ?>
			<!-- <textarea name="geneCodes" rows="14"></textarea> -->
		</td>
	</tr>
	<tr>
		<td>
		</td>
		<td align="center">
			<textarea name="codes" rows="14"></textarea>
		</td>
	</tr>
	<tr>
		<td>
		</td>
		<td>
			<input type="submit" name="make_table" value="Make genBank table" />
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
