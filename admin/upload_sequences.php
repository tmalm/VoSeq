<?php
// #################################################################################
// #################################################################################
// Voseq admin/upload_sequences.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: upload into MySQL user sequences
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check admin login session
include'../login/auth-admin.php';
// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes
include'../functions.php';
include'adfunctions.php';
include'admarkup-functions.php';
include'../login/redirect.html';

// to indicate this is an administrator page
$admin = true;

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'ComboBox';
$whichDojo[] = 'Tooltip';

// process title
$title = $config_sitename;

// print html headers
include_once'../includes/header.php';

// print navegation bar
admin_nav();

// header: send bugs to me message
//admin_standardHeader($title);

// open database connections
$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');

// select database
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}

	echo "<div id=\"content\">";
	echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
			<tr><td valign=\"top\">";
echo "<h1>";
$seqvsvouch = $_POST['seqvsvouch'];
if ($seqvsvouch != 'vouch'){$seqvsvouch = 'seq';}
if ($seqvsvouch == 'vouch') {echo "Voucher upload";} else { echo "Sequence upload";} 
?> 
</h1><ul>
<form action="upload_sequences.php" method="post">
<input type="hidden" name="seqvsvouch" value=<?php if ($seqvsvouch == 'vouch') {echo "'seq'";} else { echo "'vouch'";} ?> >
<input type="submit" name="submit" value=<?php if ($seqvsvouch == 'vouch') {echo "'Upload sequences instead'";} else { echo "'Upload vouchers instead'";} ?>>
</form>
<div id="rest1" align="center"> <!-- begin rest of page -->
<span class="text">
<table columns=2>
	<ul>
	<?php if ($seqvsvouch == 'vouch') { ?>
	<li>You can upload batches of voucher data.</li>  
	<li>Copy <u>tab deliminated</u> data, with <u>one row for each voucher</u>, into the field below - see pre-printed example</li>
	<li>The <u>first line</u> should have the field names (code, family, genus etc.). Code and genus must be listed for completion.</li>
	<li>This is only for adding data - not updating! If data is already present - the new data will be discarded (but listed as error)</li> 
	</ul>
	<td><u>Please edit your field headers names accordingly (not necessarily all or in that order):</u></br>
	Code Order Family Subfamily Tribe Subtribe Genus Species Subspecies Auctor Hostorg Typespecies Country Locality Collector Coll.date<br />
	Longitide Latitude Altitude  Vouchercode Voucher Voucherlocality determined.by Sex Extraction Extractor Extractiontube Extr.date Publ.in Notes<br /> </td>
<?php $example_input = "Something like this should be fine:\nCode	Order	Family	Genus	Species	Collector	Coll.date	Longitude	Latitude\ntA1	Hymenoptera	Tenthredinidae	Tenthredo	arcuata	Tobias Malm	2011-05-01	13.1111	12.1111\ntA2	Diptera	Syrphidae	Volucella	sp.	Tobias Malm	2010-06-01	10.1111	8.2222"; 
}
else { ?>
	<li>You can upload batches of sequence data.</li>  
	<li>Copy <u>tab deliminated</u> data, with <u>one row per sequence</u> (including code), into the field below - see pre-printed example</li>
	<li>The <u>first line</u> should have the field names (code, gene, primers etc.). Gene, code and sequence must be listed for completion.</li>
	<li>This is only for adding data - not updating! If data is already present - the new data will be discarded (but listed as error)</li> 
	</ul>
	<td><u>Please edit your field headers names accordingly (not necessarily all or in that order):</u></br>
	Code Genecode Sequences Laborator Accession Primer1 Primer2 Primer3 Primer4 Primer5 Primer6 <br /> </td>
<?php $example_input = "Something like this should be fine:\nCode	genecode	Laborator	Primer1	Primer2	Sequences\n10	COI	Tobias Malm	fly-Ci-J-1514	A2590	ATGATGATGATGATGATGATGATGATG\n11	CAD	Tobias Malm	743nFi	1028Ri	GTGTGAGTGGTAGTGGTAGTGGTA";
}
//output input field
?>
<br />
		<form action="process_upload_sequences.php" method="post">
		<table border="0" width="960px" cellpadding="5px"> <!-- super table -->
		<td>
			<input type="submit" name="submit" value="Process data">
		</td>
		<tr>
			<td align='center' width="100%" class="label4">Your input data: copy your tab-delimited data here</td>
		</tr>
		<tr>
			<td class="field1"><textarea rows="35" cols="125" wrap='off' name="input_data"><?php echo $example_input; ?></textarea></td>
		</tr>
			<tr><input type="hidden" name="format" value="<?php echo $format; ?>">
			<tr><input type="hidden" name="seqorvouch" value="<?php echo $seqvsvouch; ?>">
	</tr>
</table>
		</form>
<br />
</table>

</span>

</ul>

</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
?>

</center>
</body>
</html>
