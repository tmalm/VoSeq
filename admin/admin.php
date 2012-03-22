<?php
// #################################################################################
// #################################################################################
// Voseq admin/admin.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Entry page for administrator interface
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check if conf.php exists. If not, this is a fresh download and needs installation
if( !file_exists("../conf.php") ) {
        header("Location: installation/NoConfFile.php" );
        exit(0);
}


//check admin login session
include'../login/auth-admin.php';
// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes
include'../functions.php';
include'adfunctions.php';
include'admarkup-functions.php';
#include'../login/redirect.html';

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
admin_standardHeader($title, $intro_msg);

// begin HTML page content
echo "<div id=\"content_narrow\">";

// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}


// #################################################################################
// Section: print beginning of page
// #################################################################################
echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
			<tr><td valign=\"top\">";

if( $mask_url == "true" ) {
	echo "<a href='" .$base_url . "/home.php' onclick=\"return redirect('add.php?new=new')\"><b>Add new record</b></a>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<a href='" .$base_url . "/home.php' onclick=\"return redirect('upload_sequences.php')\"><b>Upload batch sequences/vouchers</b></a><br />
		<br />";
	echo "<a href='" .$base_url . "/home.php' onclick=\"return redirect('../login/register-form.php')\"><b>Add new user</b></a>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<a href='" .$base_url . "/home.php' onclick=\"return redirect('add_gene.php')\"><b>Add/edit/view gene information</b></a><br />
		<br />";
	echo "<a href='" .$base_url . "/home.php' onclick=\"return redirect('add_taxonset.php')\"><b>Add/edit/view Taxon sets</b></a><br />";
}
else {
	echo "<a href='" .$base_url . "/admin/add.php?new=new'><b>Add new record</b></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<a href='" .$base_url . "/admin/upload_sequences.php'><b>Upload batch sequences/vouchers</b></a><br /><br />";
	echo "<a href='" .$base_url . "/login/register-form.php'><b>Add new user</b></a>
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<a href='" .$base_url . "/admin/add_gene.php'><b>Add/edit/view gene information</b></a><br /><br />";
	echo "<a href='" .$base_url . "/admin/add_taxonset.php'><b>Add/edit/view Taxon sets</b></a><br />";
}

	// generate and execute query from Vouchers table
	$query = "SELECT id, code, genus, species, extractor, latesteditor, timestamp FROM " . $p_ . "vouchers ORDER BY timestamp DESC LIMIT 0, 10";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
	// if records present
	if (mysql_num_rows($result) > 0) {
		// iterate through result set
		// print article titles
		echo "<h1>Last entries:</h1>\n<ul>";

		$i = 1; // count for tooltips in dojo
		while ($row = mysql_fetch_object($result)) {
			// query from sequences table
			$code    = $row->code;
			$queryS  = "SELECT id, code, geneCode FROM ". $p_ . "sequences WHERE code='$code' order by geneCode";
			$resultS = mysql_query($queryS) or die("Error in query: $queryS. " . mysql_error());
			$count_geneCodes = mysql_num_rows($resultS);
			
			echo "<li><b>";
			
			if( $mask_url == "true" ) {
				echo "<a href='" .$base_url . "/home.php' onclick=\"return redirect('add.php?code=$row->code')\">$row->code</a></b>";
			}
			else {
				echo "<a href='" .$base_url . "/admin/add.php?code=$row->code'>$row->code</a></b>";
			}
			echo " <i>$row->genus $row->species</i>";

			
			//get info about picture
			$queryV  = "SELECT code, voucherImage FROM ". $p_ . "vouchers WHERE code='$code'";
			$resultV = mysql_query($queryV) or die("Error in query: $queryV. " . mysql_error());
			$rowV    = mysql_fetch_object($resultV);
			
			if ($rowV->voucherImage == 'na.gif') {
				if( $mask_url == "true" ) {
					echo " <a href='" .$base_url . "/home.php' onclick=\"return redirect('processPicture.php?code=$rowV->code')\">Picture missing</a>
						<img src=\"images/warning.png\" alt=\"\" />";
				}
				else {
					echo " <a href='" .$base_url . "/admin/processPicture.php?code=$rowV->code'>Picture missing</a>
						<img src=\"images/warning.png\" alt=\"\" />";
				}
			}
			else {
				if( $mask_url == "true" ) {
					echo " <a href=\"" . $base_url . "/home.php\" onclick=\"return redirect('" . $rowV->voucherImage . "');\"><img id=\"see_pic" . $i . "\" class=\"link\" src=\"images/image.png\" /></a>";
					echo "<span dojoType=\"tooltip\" connectId=\"see_pic" . $i . "\" delay=\"1\" toggle=\"explode\">See pic</span>";
					echo "<a href=\"" . $base_url . "/home.php\" onclick=\"return redirect('processPicture.php?code=" . $rowV->code . "');\"><img id=\"change_pic" . $i . "\" class=\"link\" src=\"images/change_pic.png\" /></a>";
					echo "<span dojoType=\"tooltip\" connectId=\"change_pic" . $i . "\" delay=\"1\" toggle=\"explode\">Change picture</span>";
				}
				else {
					echo " <a href=\"" . $rowV->voucherImage . "\"><img id=\"see_pic" . $i . "\" class=\"link\" src=\"images/image.png\" /></a>";
					echo "<span dojoType=\"tooltip\" connectId=\"see_pic" . $i . "\" delay=\"1\" toggle=\"explode\">See pic</span>";
					echo "<a href=\"" . $base_url . "/admin/processPicture.php?code=" . $rowV->code . "\"><img id=\"change_pic" . $i . "\" class=\"link\" src=\"images/change_pic.png\" /></a>";
					echo "<span dojoType=\"tooltip\" connectId=\"change_pic" . $i . "\" delay=\"1\" toggle=\"explode\">Change picture</span>";
				}
			}
			$i ++;

			echo "<ul>";
			if( $count_geneCodes > 0 ) {
				echo "<li>";
			}
			//get list of geneCodes
			while ($rowS = mysql_fetch_object($resultS)) {
				if( $mask_url == "true" ) {
					echo "<a href='" .$base_url. "/home.php' onclick=\"return redirect('listseq.php?code=$rowS->code&amp;geneCode=$rowS->geneCode&amp;id=$rowS->id')\">$rowS->geneCode</a> &nbsp;";
				}
				else {
					echo "<a href='" .$base_url. "/admin/listseq.php?code=$rowS->code&amp;geneCode=$rowS->geneCode&amp;id=$rowS->id'>$rowS->geneCode</a> &nbsp;";
				}
			}
			if( $count_geneCodes > 0 ) {
				echo "</li>";
			}
			// add new sequence
			echo "<li><a href=\"" . $base_url . "/home.php\" onclick=\"return redirect('listseq.php?code=" . $code . "');\"><b>::Add new sequence::</b></a></li>";
				echo "</ul>";
				
			?>
		By <?php 
			if( $row->latesteditor ) {
				$editor = utf8_decode($row->latesteditor);
				echo $editor;
			}
			else {
				echo "Administrator";
			}
			echo ' on '; echo admin_formatDate($row -> timestamp, $date_timezone, $php_version); ?></li>
			
			<?php
			}
		}

	// if no records present
	// display message
	else
		{
		?>
	
		<font size="-1">No records currently available</font>
	
		<?php
		}
	
// close database connection
mysql_close($connection);
?>
</ul>
</td>
<td class="sidebar" valign="top">
	<?php admin_make_sidebar(); ?>
</td>
</tr>
</table>

</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
?>

</center>
</body>
</html>
