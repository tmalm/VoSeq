<?php
// #################################################################################
// #################################################################################
// Voseq blast_new.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Script for local BLASTing of new sequences
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
//check admin login session
include'login/auth.php';

error_reporting (E_ALL ^ E_NOTICE);

// includes
#include 'login/redirect.html';
ob_start();//Hook output buffer - disallows web printing of file info...
include'conf.php';
ob_end_clean();//Clear output buffer//includes
include 'functions.php'; // administrator functions
include 'markup-functions.php';

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'Tooltip';
$whichDojo[] = 'ComboBox';

// to indicate this is an administrator page
$admin = false;

// #################################################################################
// Section: Query the DB for genes
// #################################################################################
// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}

$gCquery = "SELECT geneCode FROM ". $p_ . "genes ORDER BY geneCode";
$gCresult = mysql_query($gCquery) or die("Error in query: $query. " . mysql_error());
// if records present
$geneCodes_array = array();
if( mysql_num_rows($gCresult) > 0 ) {
	while( $row = mysql_fetch_object($gCresult) ) {
		$geneCodes_array[] = $row->geneCode;
	}
}
// #################################################################################
// Section: Output
// #################################################################################
// process title
$title = $config_sitename;

// print html headers
include_once('includes/header.php');

// print navegation bar
nav();

// begin HTML page content
echo "<div id=\"content\">";
?>

<table cellpadding="0px" cellspacing="0px" border="0" width="960px"> <!-- super table -->
<td valign="top">
	<form action="includes/blast_locally_new.php" method="post">

<table cellpadding="0px" cellspacing="0px" width="700" border="0"> <!-- big parent table -->
<td valign="top">
	<table border="0" cellspacing="10px" cellpadding="0px"> <!-- table child 1 -->
	<td>
		<table width="600" cellspacing="0px" cellpadding="0px" border="0"><!-- end table child 2 -->
		<caption>Enter name and sequence to blast as well as gene code if you like</caption>
			<tr>
				<td class="label">Name</td>
				<td class="field" colspan="1">
					<input size="80" maxlength="500" type="text" name="name" />
			</tr><tr>
			<td class="label" valign="top">Sequence</td>
			<td align="top" class="field" colspan="1">
				<textarea name="new_sequence" rows="14" cols="55">
				</textarea>
			</td></tr><tr>
			<td class="label" valign="top">Genes</td>
			<td class="field" align="left" valign="top" colspan="3">
			<table cellspacing="0px" cellpadding="0px" border="0px"><!-- end table genecodes -->
				<?php $i = 0;
						foreach ($geneCodes_array as $genes) {
							$i = $i +1;
							echo "<td class=";
							if ($i == 1) {
								echo "'field'>";
								}
							else {echo "'field2'>";}
							echo "<input type=\"checkbox\" name=\"geneCodes[$genes]\">$genes&nbsp;&nbsp;&nbsp;</td>"; 
							if ($i == 4) {
								echo "<tr>";
								$i = 0;
								}
						} ?>
			</table><!-- end table genecodes -->
			</td></tr>
			<td><input type="submit" name="submitNew" value="Blast" /></td>
		</table><!-- end table child 2 -->
	</table><!-- end table child 1 -->
</table><!-- end big parent table -->

</td>
<td class="sidebar" valign="top">
<?php  make_sidebar(); // includes td and /td already
?>
</td>


</table> <!-- end super table -->

</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url);
?>
</BODY>
</HTML>
	
