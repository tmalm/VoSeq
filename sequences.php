<?php
// #################################################################################
// #################################################################################
// Voseq sequences.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Displays the single sequence view
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
//check login session
include'login/auth.php';
// includes
include 'markup-functions.php';
ob_start();//Hook output buffer - disallows web printing of file info...
include 'conf.php';
ob_end_clean();//Clear output buffer//includes
include 'functions.php';

$title = "Sequences";
$yahoo_map = false;
$admin = false;
$in_includes = false;

// need dojo?
$dojo = true;
$whichDojo[] = 'ComboBox';
// #################################################################################
// Section: if no record ID -> die
// #################################################################################
// check for record ID
if (!isset($_GET['code']) || trim($_GET['code']) == '' || !isset($_GET['geneCode']) || trim($_GET['geneCode']) == '')
	{
	die('Missing record ID!');
	}
// #################################################################################
// Section: If record ID is found - collect data from DB
// #################################################################################
// open database connection
$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
if( function_exists(mysql_set_charset)) {
	mysql_set_charset("utf8");
}


// select database
mysql_select_db($db) or die ('Unable to select database');

// generate and execute query
$code     = $_GET['code'];
$geneCode = $_GET['geneCode'];


$query  = "SELECT code, family, subfamily,
                        tribe, genus, species,
								typeSpecies, voucherImage, thumbnail, timestamp
			  FROM ". $p_ . "vouchers WHERE code = '$code'";

$query1 = "SELECT CHAR_LENGTH(sequences),
                  geneCode,
						labPerson, 
						accession, 
						dateCreation, 
						dateModification, 
						sequences, 
					 (CHAR_LENGTH(sequences) - (CHAR_LENGTH(sequences) - ((CHAR_LENGTH(sequences) - CHAR_LENGTH(REPLACE(sequences, '?', '')) + (CHAR_LENGTH(sequences) - CHAR_LENGTH(REPLACE(sequences, '-', '')))))))
		     FROM ". $p_ . "sequences WHERE code='$code' AND geneCode='$geneCode'";

$query2 = "SELECT primer1, 
                  primer2, 
						primer3, 
						primer4, 
						primer5, 
						primer6
			   FROM ". $p_ . "primers WHERE code='$code' AND geneCode='$geneCode'";

$result  = mysql_query($query)  or die("Error in query: $query.  " . mysql_error());
$result1 = mysql_query($query1) or die("Error in query: $query1. " . mysql_error());
$result2 = mysql_query($query2) or die("Error in query: $query2. " . mysql_error());
	
// get result set as object
$row  = mysql_fetch_object($result);
$row1 = mysql_fetch_array($result1);

// #################################################################################
// Section: Print output
// #################################################################################
// print details
if ($row || $row1)
	{
	// print beginning of html page -- headers
	include_once'includes/header.php';
	nav();
	
	// begin HTML page content
	echo "<div id=\"content\">";
	echo "<table border=\"0\" width=\"960px\"> <!-- super table -->
				<tr><td valign=\"top\">";
	?>
	
	<h1><?php echo $row -> code; ?></h1>
	
<table width="800px" border="0"> <!-- big parent table -->
<tr><td valign="top">
	<table border="0" cellspacing="10" width="240px"> <!-- table child 1-->
	<tr><td>
		
	<table width="220" cellspacing="0">
	<caption>Specimen name</caption>
		<tr><td class="label">Family</td><td class="field"><?php echo $row->family; ?>&nbsp;</td></tr>
		<tr><td class="label">Subfamily</td><td class="field"><?php echo $row->subfamily; ?>&nbsp;</td></tr>
		<tr><td class="label">Tribe</td><td class="field"><?php echo $row->tribe; ?>&nbsp;</td></tr>
		<tr><td class="label">Genus</td><td class="field"><?php echo $row->genus; ?>&nbsp;</td></tr>
		<tr><td class="label">Species</td><td class="field"><?php echo $row->species; ?>&nbsp;</td></tr>
	</table>
	
	</td></tr>
	<tr><td>
		
	<table cellspacing="0" width="220">
	<caption>Sequence</caption>
		<tr><td class="label">Code</td><td width="80px" class="field3"><?php echo $row->code; ?>&nbsp;</td></tr>
		<tr><td class="label">Type species</td><td class="field">
		<?php if ($row->typeSpecies == '2') { echo "No"; } elseif ($row->typeSpecies == '1') { echo "Yes"; } else { echo "don't know"; } ?>&nbsp;</td></tr>
		<tr>
			<td class="label">Sequence</td><td class="field4" colspan="4">&nbsp;</td>
		</tr>
		<tr><td class="field5" colspan="5"><textarea  cols="27" rows="10" wrap="soft" readonly="yes">
				<?php
				$wrapped_sequence = wordwrap($row1['6'], 25, "\n", 1);
				echo $wrapped_sequence;
				?>
			 &nbsp;</textarea></td></tr>
		<tr><td class="label">No of bp</td>
			 <td class="field"><?php echo $row1['0']; ?></td>
			 <td class="label3">Amb.</td>
			 <td class="field2"><?php echo $row1['7']; ?></td>
			 <td width="30">&nbsp;</td>
		</tr>
		</table>
	
	</td></tr>
	</table> <!-- end table child 1 -->
	
</td>
<td valign="top">

	<table border="0" cellspacing="10"> <!-- table child 2 -->
	<tr><td valign="top">
	
	<table width="200" cellspacing="0" border="0">
	<caption>Lab Work</caption>
		<tr><td class="label">Lab Person</td><td class="field"><?php echo $row1['2']; ?>&nbsp;</td></tr>
		<tr><td class="label">Gene Code</td><td class="field3"><?php echo $row1['1']; ?>&nbsp;</td></tr>
		<tr><td class="label">Accession</td><td class="field">
		<a href="http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Search&amp;db=nucleotide&amp;term=<?php echo $row1['3']; ?>[accn]&amp;doptcmdl=GenBank" target="_blank"><?php echo $row1['3']; ?></a>&nbsp;</td></tr>
		<tr><td class="label">Date Creation</td><td class="field"><?php echo $row1['4']; ?>&nbsp;</td></tr>
		<tr><td class="label">Date Modification</td><td class="field"><?php echo $row1['5']; ?>&nbsp;</td></tr>
	</table>

	</td>
	<td width="200px">
		<a href="<?php echo $row->voucherImage; ?>"><img class="voucher" src="<?php echo $row->thumbnail; ?>" alt="" width="200px" /></a>
	</td>
	
	</tr>
	<tr><td colspan="2">
	
	<table width="160" cellspacing="0" border="0">
	<caption>Primers Used</caption>
		<tr><td class="label2">In alphabetical order</td></tr>
		
		<?php
		if (mysql_num_rows($result2) > 0)
			{
			while ($row2 = mysql_fetch_assoc($result2))
				{
 				$a = array();
				$i = 0;
				foreach ($row2 as $val)
					{
					if ($val != '')
						{
						$a[$i] = $val;
						$i++;
						}
					else
						{
						continue;
						}
					}
				sort($a); // $a is my array containing all my used primers, print now
				foreach ($a as $value)
					{
					echo "\n\t\t\t<tr><td align=\"right\" class=\"field\">" . $value . "&nbsp;</td></tr>";
					}
				}
			}
		else
			{
			echo "<tr><td class=\"field\">&nbsp;</td></tr>";
			}
		?>
	</table>
		
	</td></tr>
	</table><!-- end table child 2 -->

</td></tr>
</table><!-- end big parent table -->

</td>
	
	<td class="sidebar">
	<?php
	make_sidebar();
	echo "</td>";
	
	echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
	make_footer($date_timezone, $config_sitename, $version, $base_url);
	}
else
	{
	// print beginning of html page -- headers
	include_once('includes/header.php');
	nav();
	
	// begin HTML page content
	echo "<div id=\"content_narrow\">";
	echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
	?>
	<p>
	<img src="images/warning.png" alt="" /> That record could not be located in the database.
	
	</td>
	<?php
	make_sidebar(); // includes td and /td already
	echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
	make_footer($date_timezone, $config_sitename, $version, $base_url);
	}
	
// close database connection
mysql_close($connection);
?>


</body>
</html>
