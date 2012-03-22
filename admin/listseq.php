<?php
// #################################################################################
// #################################################################################
// Voseq admin/listseq.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: produce a list of gene sequences for a particular voucher
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check admin login session
include'../login/auth-admin.php';
// includes
include 'admarkup-functions.php';
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes
include '../functions.php';

// need dojo?
$dojo = true;

$title = $config_sitename;

// which dojo?
$whichDojo[] = 'ComboBox';

// to indicate this is an administrator page
$admin = true;


// #################################################################################
// if code in URL, show filled fields for vouchers and empty fields for sequences and primers
// #################################################################################
if( $_GET['code'] && !isset($_GET['geneCode']) ) {
	// open database connection
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}
		
	// search for existing record code
	$code = $_GET['code'];
	// query table vouchers
	$queryV  = "SELECT family, subfamily, tribe, genus, species, typeSpecies FROM ". $p_ . "vouchers WHERE code='$code'";
	$resultV = mysql_query($queryV) or die ("Error in query: $queryV. " . mysql_error());
	$rowV    = mysql_fetch_object($resultV);
	
	// query table sequences
// 	$query = "SELECT code, labPerson, sequences, timestamp FROM sequences WHERE code='$code'";
// 	$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
// 	$row = mysql_fetch_object($result);
	
// 	$queryP  = "SELECT code, primer1, primer2, primer3, primer4, primer5 FROM primers WHERE code='$code'";
// 	$resultP = mysql_query($queryP) or die ("Error in query: $queryP. " . mysql_error());
// 	$rowP    = mysql_fetch_object($resultP);

	
		// print html headers
		include_once'../includes/header.php';

		// print navegation bar
		admin_nav();

		// begin HTML page content
		echo "<div id=\"content\">";
		echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		?>
	<img src="images/info.png" alt=""> Add new sequence for record <b><?php echo "$code"; ?></b>
	
<form action="processSeq.php" method="post">
<table width="800px" border="0"> <!-- big parent table -->
<tr><td valign="top">
	<table border="0" cellspacing="10" width="240px"> <!-- table child 1-->
	<tr><td>
		
	<table width="220" cellspacing="0">
		<caption>Specimen name</caption>
			<tr><td class="label">Family</td><td class="field"><?php echo $rowV->family; ?>&nbsp;</td></tr>
			<tr><td class="label">Subfamily</td><td class="field"><?php echo $rowV->subfamily; ?>&nbsp;</td></tr>
			<tr><td class="label">Tribe</td><td class="field"><?php echo $rowV->tribe; ?>&nbsp;</td></tr>
			<tr><td class="label">Genus</td><td class="field"><?php echo $rowV->genus; ?>&nbsp;</td></tr>
			<tr><td class="label">Species</td><td class="field"><?php echo $rowV->species; ?>&nbsp;</td></tr>
		</table>

	</td></tr>
	<tr><td>
		
	<table cellspacing="0" width="220">
	<caption>Sequence</caption>
		<!-- 		INPUT 1 --> 
		<input type="hidden" name="id" value="<?php echo $code; ?>">
			<tr><td class="label">Code</td><td class="field3"><?php echo $code; ?>&nbsp;</td></tr>
			<tr><td class="label">Type species</td><td class="field"><?php if ($rowV->typeSpecies == 2) { echo "No"; } elseif ($rowV->typeSpecies == 1) { echo "Yes"; } else { echo "don't know"; } ?></td></tr>
			<tr>
				<td class="label">Sequence</td><td class="field4" colspan="4">&nbsp;</td>
			</tr>
			<tr><td class="field5" colspan="5">
				 <textarea name="sequences" rows="6" cols="27"></textarea>
				 </td>
			</tr>
		</form>
		</table>

	</td></tr>
	</table> <!-- end table child 1 -->
	
</td>
<td valign="top">

	<table border="0" cellspacing="10"> <!-- table child 2 -->
	<tr><td valign="top">
	
	<table width="200" cellspacing="0" border="0">
	<caption>Lab Work</caption>
		<tr><td class="label">Lab Person</td>
			 <td class="field">
			 	<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_labPerson.js" style="width: 94px;" name="labPerson"
					maxListLength="15">
				</select></td>
		</tr>
		<tr><td class="label">Gene Code</td>
			 <td class="field3">
				<select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_geneCode.js" style="width: 94px;" name="geneCode" maxListLength="15">
				</select></td>
		</tr>
		<tr><td class="label">Accession</td>
			 <td class="field">
			 	<input size="10" maxlength="250" type="text" name="accession"></td>
		</tr>
		<tr><td class="label">Date Creation<br />(yyyy-mm-dd)</td>
			 <td class="field"><input size="10" maxlength="250" type="text" name="dateCreation"></td></tr>
	</table>

	</td>
	<td width="200px">
		&nbsp;
	</td>
	
	</tr>
	<tr><td colspan="2">
	
	<table width="160" cellspacing="0" border="0">
	<caption>Primers Used</caption><!-- need to improve this primer part! -->
		<tr><td class="label2">Up to 6 primers</td></tr>
		<tr>
			<td class="field"><select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_primer1.js" style="width: 94px;" name="primer1" maxListLength="15">
				</select></td>
		</tr>
		<tr>
			<td class="field"><select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_primer2.js" style="width: 94px;" name="primer2" maxListLength="15">
				</select></td>
		</tr>
		<tr>
			<td class="field"><select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_primer3.js" style="width: 94px;" name="primer3" maxListLength="15">
				</select></td>
		</tr>
		<tr>
			<td class="field"><select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_primer4.js" style="width: 94px;" name="primer4" maxListLength="15">
				</select></td>
		</tr>
		<tr>
			<td class="field"><select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_primer5.js" style="width: 94px;" name="primer5" maxListLength="15">
				</select></td>
		</tr>
		<tr>
			<td class="field"><select dojoType="ComboBox" value="nada"
					dataUrl="../dojo_data/comboBoxData_primer6.js" style="width: 94px;" name="primer6" maxListLength="15">
				</select></td>
		</tr>
		<tr>
			<td><input type="submit" name="submit" value="Add Sequence"></td></tr>
	
	</table>
		
	</td></tr>
	</table><!-- end table child 2 -->

</td></tr>
</table><!-- end big parent table -->
</form>

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

// #################################################################################
// Section: show prefilled data for voucher
// #################################################################################
elseif ($_GET['code'] && $_GET['geneCode'] && $_GET['id'])
	{
	// open database connection
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}
	mysql_select_db($db) or die ('Unable to content');
		
	// search for existing record code
	$id       = $_GET['id'];
	$code     = $_GET['code'];
	$geneCode = $_GET['geneCode'];
		
	// query table vouchers
	$queryV  = "SELECT family, subfamily, tribe, genus, species, typeSpecies FROM ". $p_ . "vouchers WHERE code='$code'";
	$resultV = mysql_query($queryV) or die ("Error in query: $queryV. " . mysql_error());
	$rowV    = mysql_fetch_object($resultV);
	
	// query table sequences
	$query = "SELECT CHAR_LENGTH(sequences),
						geneCode,
						labPerson,
						sequences,
						accession,
						dateCreation,
						dateModification,
						timestamp,
						(2*CHAR_LENGTH(sequences) - CHAR_LENGTH(REPLACE(sequences, '?', '')) - CHAR_LENGTH(REPLACE(sequences, '-', '')))
				FROM ". $p_ . "sequences WHERE id='$id'";
	$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
	$row = mysql_fetch_array($result);
	
	// query table primers
	$queryP  = "SELECT code, primer1, primer2, primer3, primer4, primer5, primer6 FROM ". $p_ . "primers WHERE code='$code' AND geneCode='$geneCode'";
	$resultP = mysql_query($queryP) or die ("Error in query: $queryP. " . mysql_error());
	$rowP    = mysql_fetch_object($resultP);
	
	
	// print html headers
	include_once'../includes/header.php';

	// print navegation bar
	admin_nav();

	// begin HTML page content
	echo "<div id=\"content\">";
	echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
				<tr><td valign=\"top\">";
	?>
	<img src="images/info.png" alt=""> Update sequence for record <b><?php echo "$code"; ?></b>

<form action="processSeq.php" method="post">
	<br />
	<input class="delete" type="submit" name="delete_seq" value="Delete me" /><!-- Delete this sequence! -->
		
<table width="800px" border="0"> <!-- big parent table -->
<tr><td valign="top">
	<table border="0" cellspacing="10" width="240px"> <!-- table child 1-->
	<tr><td>
		
	<table width="220" cellspacing="0">
		<caption>Specimen name</caption>
			<tr><td class="label">Family</td><td class="field"><?php echo $rowV->family; ?>&nbsp;</td></tr>
			<tr><td class="label">Subfamily</td><td class="field"><?php echo $rowV->subfamily; ?>&nbsp;</td></tr>
			<tr><td class="label">Tribe</td><td class="field"><?php echo $rowV->tribe; ?>&nbsp;</td></tr>
			<tr><td class="label">Genus</td><td class="field"><?php echo $rowV->genus; ?>&nbsp;</td></tr>
			<tr><td class="label">Species</td><td class="field"><?php echo $rowV->species; ?>&nbsp;</td></tr>
		</table>

	</td></tr>
	<tr><td>
		
	<table cellspacing="0" width="220">
	<caption>Sequence</caption>
		<!-- 		INPUT 1 --> 
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="hidden" name="code" value="<?php echo $code; ?>">

		<!-- 		to denote that this info should be updated, not inserted -->
		<input type="hidden" name="update" value="update">
	
			<tr>
				<td class="label">Code</td>
				<td class="field3" colspan="3"><?php echo $code; ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="label">Type species</td>
				<td class="field" colspan="3"><?php if ($rowV->typeSpecies == 2) { echo "No"; } elseif ($rowV->typeSpecies == 1) { echo "Yes"; } else { echo "don't know"; } ?></td>
			</tr>
			<tr>
				<td class="label">Sequence</td>
				<td class="field4" colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td class="field5" colspan="4">
					<textarea name="sequences" rows="8" cols="27"><?php
																	$wrapped_sequence = wordwrap($row['3'], 25, "\n", 1);
																	echo $wrapped_sequence;
																	?></textarea>
				 </td>
			</tr>
			<tr>
				<td class="label">No of bp</td>
				<td class="field"><?php echo $row['0']; ?></td>
				<td class="label3">Amb.</td>
				<td class="field2"><?php echo $row['8']; ?></td>
			</tr>
		</form>
		</table>
	
	</td></tr>
	</table> <!-- end table child 1 -->
	
</td>
<td valign="top">

	<table border="0" cellspacing="10"> <!-- table child 2 -->
	<tr><td valign="top">
	
	<table width="200" cellspacing="0" border="0">
	<caption>Lab Work</caption>
		<tr><td class="label">Lab Person</td>
			 <td class="field">
			 	<input size="14" maxlength="250" type="text" name="labPerson" value="<?php echo $row['2']; ?>">
			</td>
		</tr>
		<tr><td class="label">Gene Code</td>
			 <td class="field3">
				<input size="14" maxlength="250" type="text" name="geneCode" value="<?php echo $row['1']; ?>"></td>
		</tr>
		<tr><td class="label">Accession</td>
			 <td class="field">
			 	<input size="14" maxlength="250" type="text" name="accession" value="<?php echo $row['4']; ?>"></td>
		</tr>
		<tr><td class="label">Date Creation<br />(yyyy-mm-dd)</td>
			 <td class="field"><input size="14" maxlength="250" type="text" name="dateCreation" value="<?php echo $row['5']; ?>"></td></tr>
		<tr><td class="label">Date Modification<br />(yyyy-mm-dd)</td>
			 <td class="field"><?php echo $row['6']; ?></td></tr>
	</table>

	</td>
	<td width="200px">
		&nbsp;
	</td>
	
	</tr>
	<tr><td colspan="2">
	
	<table width="160" cellspacing="0" border="0">
	<caption>Primers Used</caption><!-- need to improve this primer part! -->
		<tr><td class="label2">Up to 6 primers</td></tr>
		<tr>
			<td class="field"><input size="14" maxlength="250" type="text" name="primer1" value="<?php if(isset($rowP->primer1)) { echo $rowP->primer1; } ?>"></td>
		</tr>
		<tr>
			<td class="field"><input size="14" maxlength="250" type="text" name="primer2" value="<?php if(isset($rowP->primer2)) { echo $rowP->primer2; } ?>"></td>
		</tr>
		<tr>
			<td class="field"><input size="14" maxlength="250" type="text" name="primer3" value="<?php if(isset($rowP->primer3)) { echo $rowP->primer3; } ?>"></td>
		</tr>
		<tr>
			<td class="field"><input size="14" maxlength="250" type="text" name="primer4" value="<?php if(isset($rowP->primer4)) { echo $rowP->primer4; } ?>"></td>
		</tr>
		<tr>
			<td class="field"><input size="14" maxlength="250" type="text" name="primer5" value="<?php if(isset($rowP->primer5)) { echo $rowP->primer5; } ?>"></td>
		</tr>
		<tr>
			<td class="field"><input size="14" maxlength="250" type="text" name="primer6" value="<?php if(isset($rowP->primer6)) { echo $rowP->primer6; } ?>"></td>
		</tr>
		<tr>
			<td><input type="submit" name="submit" value="Update Sequence"></td>
		</tr>
	
	</table>
		
	</td></tr>
	</table><!-- end table child 2 -->

</td></tr>
</table><!-- end big parent table -->
</form>

</td>

	<?php
	echo "<td class=\"sidebar\" valign=\"top\">";
	admin_make_sidebar(); // includes td and /td already
	echo "</td>";
	echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
	}
else
	{
	echo "Error, comes from?";
	}

?>
	

</body>
</html>
