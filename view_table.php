<?php
// #################################################################################
// #################################################################################
// Voseq view_table.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Displays filterable and sortable overview list of all
// vouchers with sequence lengths and taxonomic info
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
//check admin login session
include'login/auth.php';

error_reporting (E_ALL ^ E_NOTICE);

// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'conf.php';
ob_end_clean();//Clear output buffer//includes
include 'functions.php'; // administrator functions
include 'markup-functions.php';
include 'includes/validate_coords.php';

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'Tooltip';
$whichDojo[] = 'ComboBox';

// form not yet submitted
// display initial form
// process title
$title = $config_sitename;

// #################################################################################
// Section: Output start
// #################################################################################
// print html headers
include_once('includes/header.php');
// print navegation bar
nav();
// #################################################################################
// Section: Get and fix input variables - taxonsets/sorting etc
// #################################################################################
// inputs
if (isset($_POST['taxonset_list'])){ 
		$taxonset_list = array();
	foreach ( $_POST['taxonset_list'] as $k=> $c) {//loops through checkbox values and adds checked taxa to taxonset list
		if ($c == 'on')	{
			$taxonset_list[] = $k;
		}
	}
}unset($k, $c);

//prepare sorting
if (isset($_POST['sort_by'])){
	$sort_by_array = array();
	$sort_by_x = "no";
	foreach ( $_POST['sort_by'] as $k=> $c) {//loops through checkbox values and adds checked values to sort_by list
		if ($c == 'on')	{
			if ($k == "X") { $sort_by_x = "yes";} 
			else { $sort_by_array[] = $k; }
		}
	}
	$sort_by = implode(", ", $sort_by_array);
}unset($k, $c);
if (! isset($sort_by) || $sort_by =="" ) { 
$sort_by = 'orden, family, subfamily, genus, species'; 
$sort_by_array = explode(", ", $sort_by);
$sort_by_x = "no";}

//prepare mark/unmark of active taxa
if (isset($_POST['mark'])){$markall = "all";}
elseif (isset($_POST['unmark'])){$markall = "none";}
else{ $markall = "off"; }

// open database connections for creating taxa list
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
$result = mysql_query("set names utf8") or die("Error in query: $query. " . mysql_error());
$cquery = "SELECT code, orden, family, subfamily, genus, species, hostorg FROM ". $p_ . "vouchers ORDER BY $sort_by;";
																								//echo "$cquery</br>";
$cresult = mysql_query($cquery) or die("Error in query: $query. " . mysql_error());
// if records present
$codes_array = array();
$code_info_array = array();
if( mysql_num_rows($cresult) > 0 ) {
	while( $crow = mysql_fetch_object($cresult) ) {
		$codes_array[] = $crow->code;
		$code_info_array[] = $crow->code . "%" . $crow->orden . "%". $crow->family . "%". $crow->subfamily . "%". $crow->genus . "%". $crow->species . "%". $crow->hostorg;
	}
}

// fix a genecode table
// open database connections
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
//fix choosen genes
unset($geneCodes);
if (isset($_POST['geneCodes'])){
	foreach ( $_POST['geneCodes'] as $k1=> $c1){ //putting choosen genes into array
		if ($c1 == 'on')	{
			$genes[] =  $k1;
		}
	}
}
if (isset($genes) && trim(implode("", $genes)) == ""){ unset($genes); }

// fix filtering stuff
$filter_array = array("filter", "orden", "family", "subfamily", "genus", "species", "hostorg", "code", "X");
$filter_array_num = array("filter" => "100", "orden" => "1", "family" => "2", "subfamily" =>"3", "genus" => "4", "species" => "5", "hostorg" => "6", "code" => "0", "X" => "101");
	// prepare filtering
if ( isset($_POST['filter_by']) && trim($_POST['filter_text']) != "" && $_POST['filter_by'] != "filter" || $_POST['filter_by'] == "X"){
	if (in_array ($_POST['filter_by'],  $filter_array)){
	$filter_by_name = $filter_by = $_POST['filter_by'];
	if ($filter_array_num[$filter_by] < 10 && $filter_array_num[$filter_by] != ""){
		$filter_by = $filter_array_num[$filter_by];
	} 
	elseif ($filter_by == "X") { // do nothing - keep $filter_by as X
		unset($_POST['filter_text']);
	}
	else { unset ($filter_by); 
	}
	$filter_text = trim($_POST['filter_text']);
	}
}
//prepare filtering by gene
if (isset($_POST['filter_by_gene']) && $_POST['filter_by_gene'] !== "filter") {
	$filter_by_gene_name = $filter_by_gene = $_POST['filter_by_gene'];
	if (isset($_POST['filter_gene_text']) && trim($_POST['filter_gene_text']) != "" && is_numeric($_POST['filter_gene_text']) ) 
		{$filter_gene_text = trim($_POST['filter_gene_text']);}
	else {unset($filter_gene_text);}
	unset($filter_by, $filter_text);
}else { unset ($filter_by_gene); }
//fixing sort_by_genes
if (isset($_POST['sort_by_genes'])) {
	foreach ( $_POST['sort_by_genes'] as $k1=> $c1){ //putting choosen genes into array
		if ($c1 == 'on')	{
			$sort_by_genes[] =  $k1;
		}
	}
	$seqLarray = array();
	foreach ($sort_by_genes as $sbg) {
		$querySBG = "SELECT code, sequences FROM ". $p_ . "sequences WHERE geneCode='$sbg' AND length(sequences)>0 ORDER BY length(sequences)";
		$resultSBG = mysql_query($querySBG) or die("Error in query: $querySBG. " . mysql_error());
		// if records present
		if( mysql_num_rows($resultSBG) > 0 ) {
			while( $row = mysql_fetch_object($resultSBG) ) {
				
				if (! in_array($row->code, $seqLarray)) {$seqLarray[] = $row->code;}
			}
		}
	}
	$sbg_yes = $sbg_no = array();
	foreach($code_info_array as $line) {
		$cia_cols = explode ("%", $line);
		if (in_array($cia_cols[0], $seqLarray)){ $sbg_yes[] = $line;}
		else { $sbg_no[] = $line;}
	}
	$code_info_array = array_merge($sbg_yes, $sbg_no);
}else {$sort_by_genes = array();}

// fixing sorting by marked taxa
if ($sort_by_x == "yes"){
	if (isset($taxonset_list) || $taxonset_list != ""){
		$cia_yes= $cia_no = array();
		foreach($code_info_array as $line) {
			$cia_cols = explode ("%", $line);
			if (in_array($cia_cols[0], $taxonset_list)){ $cia_yes[] = $line;}
			elseif (isset($seqLarray_unique) && in_array($cia_cols[0], $seqLarray_unique)){$cia_genesort[] = $line;}
			else { $cia_no[] = $line;}
		}
		 if (isset($cia_genesort)) {$code_info_array = array_merge($cia_yes, $cia_genesort, $cia_no);}
		 else {$code_info_array = array_merge($cia_yes, $cia_no);}
	}
}unset($line);
// #################################################################################
// Section: Continue output
// #################################################################################
// begin HTML page content
echo "<div id=\"content\">";
?>

<table border="0" width="960px"> <!-- super table -->
<tr><td valign="top">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table width="900" border="0"> <!-- big parent table -->
<tr><td valign="top">
	<table border="0" cellspacing="10"> <!-- table child 1 -->
	<tr><td>
	<table width="800px" cellspacing="0" border="0">
	<caption>Taxon informaton</caption>
	</table>
	<table width="800px" cellspacing="0" cellpadding="0" border="0">
		<td class="label1">Show seqs: </td><td class="field1" colspan="4">
			<?php // fix the genecode checkbox field
				$genetable = "";
				 $i = 0;
					foreach ($geneCodes_array as $gene) {
						$i = $i +1;
						$genetable .= "<input type=\"checkbox\" name=\"geneCodes[$gene]\"";
						if (isset($genes)) {if (in_array($gene, $genes)){ $genetable .= " checked "; }}
						$genetable .= ">$gene"; 
						if ($i == 8) {
							$genetable .= "<br />";
							$i = 0;
							}
					}unset ($gene);
					echo "$genetable" ;?>
		</td>
		</tr>
			<td class="label">Filter names:</td>
			<!-- creating the filter regular fields dropdown box and filter text field -->
			<td class="field"><select name="filter_by" size="1" style=" BORDER-BOTTOM: outset; BORDER-LEFT: 
			outset; BORDER-RIGHT: outset; BORDER-TOP: outset; FONT-FAMILY: 
			Arial; FONT-SIZE: 14px"> 
			<?php  
			foreach ($filter_array as $fv){ 
				$filter_table .= "<option "; 
				if ( $fv == $filter_by_name){ $filter_table .= "selected ";}
				if ( $fv == "orden" ){ $filter_table .= "value=\"$fv\">Order</option>";}
				elseif ( $fv == "hostorg" ){ $filter_table .= "value=\"$fv\">Host org.</option>";}
				else { 
				$fvu = ucfirst($fv);
				$filter_table .= "value=\"$fv\">$fvu</option>"; 
				}
			}
			echo $filter_table; unset ($filter_table); ?>
			</select>
			 Enter filter string here: <input colspan="2" size="28" maxlength="250" type="text" name="filter_text" value="<?php if(isset($filter_text)){ echo "$filter_text";} else { echo "";} ?>"></td>
			<td class="field2"><input type="submit" style=" font-size: 10pt;" name="sort" value="Filter/Sort" /></td>
		</tr>
			<td class="label">Sort by:</td>
			<td class="field" colspan="4">
			<input type="checkbox" name="sort_by[orden]" <?php if (isset($sort_by_array) && in_array("orden", $sort_by_array)) { echo " checked";} ?>>Order
			<input type="checkbox" name="sort_by[family]" <?php if (isset($sort_by_array) && in_array("family", $sort_by_array)) { echo " checked";} ?>>Family
			<input type="checkbox" name="sort_by[subfamily]" <?php if (isset($sort_by_array) && in_array("subfamily", $sort_by_array)) { echo " checked";} ?>>Subfamily
			<input type="checkbox" name="sort_by[genus]" <?php if (isset($sort_by_array) && in_array("genus", $sort_by_array)) { echo " checked";} ?>>Genus
			<input type="checkbox" name="sort_by[species]" <?php if (isset($sort_by_array) && in_array("species", $sort_by_array)) { echo " checked";} ?>>Species
			<input type="checkbox" name="sort_by[hostorg]" <?php if (isset($sort_by_array) && in_array("hostorg", $sort_by_array)) { echo " checked";} ?>>Host org.
			<input type="checkbox" name="sort_by[code]" <?php if (isset($sort_by_array) && in_array("code", $sort_by_array)) { echo " checked";} ?>>Code
			<input type="checkbox" name="sort_by[X]" <?php if ($sort_by_x == "yes") { echo " checked";} ?>>X
			<?php // fix the genecode checkbox sort field
			if (isset($genes)){
				$genetable = "<table>";
				$i = 0;
				foreach ($genes as $gene) {
					$i = $i +1;
					$genetable .= "<td style=\"color: #6D929B;font: bold 12px 'Trebuchet MS', Verdana, Arial, Helvetica, sans-serif;\">
									<input type=\"checkbox\" name=\"sort_by_genes[$gene]\"";
					if (isset($genes)) {if (in_array($gene, $sort_by_genes)){ $genetable .= " checked "; }}
					$genetable .= ">$gene</td>"; 
					if ($i == 6) {
						$genetable .= "</tr>";
						$i = 0;
						}
				}unset ($gene);
				$genetable .= "</table>";
				echo $genetable ;
			}?>
		</td>
		</tr>
			<?php  
			if (isset($genes)) { //creating the filter genes dropdown box and filter text field
			$filter_table = "<tr><td class='label'>Filter gene:</td><td class='field' colspan='2'><select name=\"filter_by_gene\" size=\"1\" style=\" BORDER-BOTTOM: outset; BORDER-LEFT: 
			outset; BORDER-RIGHT: outset; BORDER-TOP: outset; FONT-FAMILY: 
			Arial; FONT-SIZE: 14px\">" ;
			$genesforfilt = $genes;
			array_unshift($genesforfilt,"filter");
			foreach ($genesforfilt as $g){
				$filter_table .= "<option "; 
				if ( $g == $filter_by_gene_name){ $filter_table .= "selected ";}
				$gu = ucfirst($g);
				$filter_table .= "value=\"$g\">$gu</option>";
			}echo $filter_table; ?>
			</select>
			 Enter min number of bp here: <input colspan="2" size="18" maxlength="150" type="text" name="filter_gene_text" value="<?php if(isset($filter_gene_text)){ echo "$filter_gene_text";} else { echo "";} ?>">
			 </td>
			</tr>
			<?php
			}
			 unset ($filter_table); ?>
		<tr>
			<td><input type="submit" style=" font-size: 7pt;" name="mark" value="Mark all" /></td>
			<td><input type="submit" style=" font-size: 7pt;" name="unmark" value="Unmark all" /></td>
		</tr>
	</table>
	<table cellpadding="15">
			<td align="left" valign="top">
			<?php
						$table = "\n<table border='0' frame='below' cellspacing='0'>";
						$table .= "\n<tr>";
						$table .= "<td style=\"width: 5px;\" class='label1'>X</td>
						 <td style=\"width: 150px;\" class='label1'>Code</td>
						 <td style=\"width: 150px;\" class='label1'>Order</td>
						 <td style=\"width: 150px;\" class='label1'>Family</td>
						 <td style=\"width: 150px;\" class='label1'>Subfamily</td>
						 <td style=\"width: 150px;\" class='label1'>Genus</td>
						 <td style=\"width: 150px;\" class='label1'>Species</td>
						 <td style=\"width: 150px;\" class='label4'>Host org.</td>";
						//insert choosen gene info's
						if (isset($genes)){
							foreach ($genes as $gene){
								$table .= "<td class='label5'>" . ucfirst($gene) . "</td>";
							}
						}
						$table .="</tr>";
						// creating the table with filtering
					$i = 0;
					foreach($code_info_array as $line) {
						$table .= "<tr>";
						$line_cols = explode("%", $line); 
						// find filtered values
						if (isset($filter_by_gene_name)) { // for gene filtering
							$query1 = "SELECT code, sequences FROM ". $p_ . "sequences WHERE code='$line_cols[0]' AND geneCode='$filter_by_gene_name'";
							$result1 = mysql_query($query1) or die("Error in query: $query1. " . mysql_error());
							if( mysql_num_rows($result1) > 0 ) {
								while( $row1 = mysql_fetch_object($result1) ) { 
									$seqlen = strlen(str_replace("?" , "" , $row1->sequences));
									if ( $seqlen >= $filter_gene_text ){$filt = "1";} else {$filt = FALSE; }
								}
							}else {$filt = FALSE;} 
						}
						// for X filtering and others
						elseif ($filter_by == "X"){ if (isset($taxonset_list) && in_array($line_cols[0], $taxonset_list)){ $filt = "1";} else {$filt = FALSE; }}
						elseif (isset($filter_by) || isset($filter_text)) { $filt = stripos( $line_cols[$filter_by], $filter_text );}
						else { $filt = "1";}
						// if filtered to show
						if ( $filt !== FALSE) { 
							$j = 0;
							foreach ($line_cols as $col){
								if ($col == $line_cols[0]){
									$table .= "<td class='field4' "; 	
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";} 
									$table .= "><input type=\"checkbox\" name=\"taxonset_list[$col]\"";
									if ( $markall != "none") {if (isset($taxonset_list) && in_array($col, $taxonset_list) || $markall == "all" ){ $table .= " checked ";}}
									$table .= "></td>";
									$table .= "<td class='field4' ";
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";} 

									if( $mask_url == "true" ) {
										$table .= "><a href=\"" . $base_url . "/home.php\" onclick=\"return redirect('story.php?code=$col');\">$col</a></td>";
									}
									else {
										$table .= "><a href=\"" . $base_url . "/story.php?code=$col\">$col</a></td>";
									}

									$j = $j + 1;
								}
								elseif ( $j == "6" ) {    # this will close the table at the host org cell
									$table .= "<td class='field5' ";
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";}
									$table .= ">$col</td>";
								}else {
									$table .= "<td class='field4' ";
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";}
									$table .= ">$col</td>";
									$j = $j + 1;
								}
							}
							//insert choosen gene info's
							if (isset($genes)){
								foreach ($genes as $gene){
									$query1 = "SELECT sequences FROM ". $p_ . "sequences WHERE code='$line_cols[0]' AND geneCode='$gene'";
									$result1 = mysql_query($query1) or die("Error in query: $query1. " . mysql_error());
									if( mysql_num_rows($result1) > 0 ) {
										while( $row1 = mysql_fetch_object($result1) ) {
											$table .= "<td class='field2' align=\"center\"";
											if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";}
											$table .= ">" . strlen(str_replace("?" , "" , $row1->sequences)) . "</td>";
										}
									}
									else { 
										$table .= "<td class='field2' align=\"center\"";
										if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";}
										$table .= ">-</td>";
									}
								}
							}
							if ($i == 0) {$i = 1;} else {$i = 0;};
						}
						else { // if filtered to hide
							if (isset($taxonset_list) && in_array($line_cols[0], $taxonset_list)){ 
								$table .= "<input type=\"checkbox\" name=\"taxonset_list[$line_cols[0]]\" checked style=\"display:none;\"";
							}
						}
						
						//$table .= "\n</tr>";
					}	
				echo "$table"; ?>
			<!-- <textarea name="geneCodes" rows="14"></textarea> -->
		</td>
	</tr>
	</table>
	
	</td></tr>
	</table><!-- end table child 2 -->
</form>
</td></tr>
</table><!-- end big parent table -->

</td>
<td class="sidebar">
<?php make_sidebar(); 
?>
</td>

</tr>
</table> <!-- end super table -->
</table>
</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url);
?>
	
</body>
</html>
