<?php
// #################################################################################
// #################################################################################
// Voseq admin/uploadseqs.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: process and upload batch of sequences submitted by user
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

include ('admarkup-functions.php');
include_once('../includes/adHeader.php');

admin_nav();
standardHeader();

// includes
include('../conf.php');
include('../functions.php');

// open database connections
$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');

// select database
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}


?>

<div id="rest1"> <!-- begin rest of page -->
<span class="text">

	<ul>
	<li>You can upload groups of sequences by having them as a text file delimited by commas.</li>
	<li>Put your sequences into a Excel sheet and then export it as a text file delimited by commas.</li>
	<li>The first line should have the field names.</li>
	<li>You must not have single nor double quotation marks to delimit fields, you should have commas instead.</li>
	</ul>
	
Something like this should be fine:
<br />
<br />
<img src="images/uploadsample.png" alt="Upload Sample" />
<br />
<br />
Select the fields you will be uploading:
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<select name="field1">
<option value="none" selected="selected">None</option>
<option value="code">Code</option>
<option value="geneCode">Gene code</option>
<option value="sequences">Sequence</option>
<option value="accession">Accession Number</option>
<option value="labPerson">Lab Person</option>
<option value="dateCreation">Date Creation</option>
</select>
<br />
<select name="field2">
<option value="none" selected="selected">None</option>
<option value="code">Code</option>
<option value="geneCode">Gene code</option>
<option value="sequences">Sequence</option>
<option value="accession">Accession Number</option>
<option value="labPerson">Lab Person</option>
<option value="dateCreation">Date Creation</option>
</select>
<br />
<select name="field3">
<option value="none" selected="selected">None</option>
<option value="code">Code</option>
<option value="geneCode">Gene code</option>
<option value="sequences">Sequence</option>
<option value="accession">Accession Number</option>
<option value="labPerson">Lab Person</option>
<option value="dateCreation">Date Creation</option>
</select>
<br />
<select name="field4">
<option value="none" selected="selected">None</option>
<option value="code">Code</option>
<option value="geneCode">Gene code</option>
<option value="sequences">Sequence</option>
<option value="accession">Accession Number</option>
<option value="labPerson">Lab Person</option>
<option value="dateCreation">Date Creation</option>
</select>
<br />
<select name="field5">
<option value="none" selected="selected">None</option>
<option value="code">Code</option>
<option value="geneCode">Gene code</option>
<option value="sequences">Sequence</option>
<option value="accession">Accession Number</option>
<option value="labPerson">Lab Person</option>
<option value="dateCreation">Date Creation</option>
</select>
<br />
<select name="field6">
<option value="none" selected="selected">None</option>
<option value="code">Code</option>
<option value="geneCode">Gene code</option>
<option value="sequences">Sequence</option>
<option value="accession">Accession Number</option>
<option value="labPerson">Lab Person</option>
<option value="dateCreation">Date Creation</option>
</select>
<br />
<br />
Enter you file:
<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    			<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
    			<input name="userfile" type="file" /><br />
    			<input type="Submit" name="submit" value="Upload" />
</form>
</form>

</span>
<?php
	print_r($_POST);
	$a = "{$_FILES['userfile']['name']}";
	echo "$a";
	$b = trim($a);
	if ($b == '')
		{
// 		echo "<div id=\"rest1\"><img src=\"../images/warning.png\" alt=\"\"> File <b>did not</b> 	successfully upload. Check the file size. File must be less than 2MB. Or maybe your filename is not correct.</div>";
		print "";
		}
	else
		{
		$cwd = getcwd();
		$destination = "$cwd" . "/uploads/" . $_FILES['userfile']['name'];
		$temp_file = $_FILES['userfile']['tmp_name'];
		move_uploaded_file($temp_file, $destination);
		}
		
	// generate and execute query from Vouchers table
	$query = "SELECT code, genus, species, extractor, timestamp FROM ". $p_ . "vouchers ORDER BY timestamp DESC LIMIT 0, 5";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
	// if records present
	if (mysql_num_rows($result) > 0)
		{
		// iterate through result set
		// print article titles
		echo "<p class=\"title\">Last entries:</p>\n<ul>";
		while ($row = mysql_fetch_object($result))
			{
			// query from sequences table
			$code    = $row->code;
			$queryS  = "SELECT code, geneCode FROM ". $p_ . "sequences WHERE code='$code'";
			$resultS = mysql_query($queryS) or die("Error in query: $queryS. " . mysql_error());
			?>
			
			<li><b><a href="add.php?code=<?php echo $row->code; ?>"><?php echo $row->code; ?></a></b>
			<i><?php echo $row->genus; echo ' ' . $row->species; ?></i>
			
			<?php
			//get info about picture
			$queryV  = "SELECT code, voucherImage FROM ". $p_ . "vouchers WHERE code='$code'";
			$resultV = mysql_query($queryV) or die("Error in query: $queryV. " . mysql_error());
			$rowV    = mysql_fetch_object($resultV);
			if ($rowV->voucherImage == 'na.gif')
				{
				?>
				<a href="processPicture.php?code=<?php echo $rowV->code; ?>">Picture missing</a><img src="images/warning.png" alt="" />
				<?php
				}
			else
				{
				echo "<a href=\"../pictures/" . $rowV->voucherImage . "\"><img class=\"link\" src=\"images/image.png\" alt=\"\" /></a>";
				}
				?>
			<ul>
			<?php
			//get list of geneCodes
			while ($rowS = mysql_fetch_object($resultS))
				{
				?>
				<li><a href="listseq.php?code=<?php echo $rowS->code; ?>&amp;geneCode=<?php echo $rowS->geneCode; ?>"> <?php echo $rowS->geneCode; ?></a></li>
				<?php
				}
				// add new sequence
				echo "<li><a href=\"listseq.php?code=" . $code . "\"><b>::Add new sequence::</b></a></li>";
				echo "</ul>";
				
			?>
			By <?php echo $row->extractor.' on '; echo admin_formatDate($row -> timestamp); ?></li>
			
			<?php
			}
		}

	// if no records present
	// display message
	else
		{
		?>
	
		<font size="-1">No press releases currently available</font>
	
		<?php
		}
	
// close database connection
mysql_close($connection);
?>
</ul>

<!-- standard page footer begins -->
<div id="footer-800" >
	<?php include_once( '../includes/footer.php' ); ?>
</div>

</div><!-- end rest -->

</body>
</html>
