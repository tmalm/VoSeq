<?php
// #################################################################################
// #################################################################################
// Voseq admin/add_taxonset.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Add taxonsets, sets of vouchers
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

//check admin login session
include'../login/auth-admin.php';

error_reporting (E_ALL); // ^ E_NOTICE);

// includes
include '../login/redirect.html';
ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes
include 'adfunctions.php'; // administrator functions
include 'admarkup-functions.php';
include '../includes/validate_coords.php';

// need dojo?
$dojo = true;

// which dojo?
$whichDojo[] = 'Tooltip';
$whichDojo[] = 'ComboBox';

// to indicate this is an administrator page
$admin = true;


// #################################################################################
// Section: sanitize strings
// #################################################################################
function clean_item ($item) {
	$item = stripslashes($item);
	$item = str_replace("'", "", $item);
	$item = str_replace('"', "", $item);
	$item = str_replace(',', "", $item);
	$item = preg_replace('/^\s+/', '', $item);
	$item = preg_replace('/^\t+/', '', $item);
	$item = preg_replace('/\s+$/', '', $item);
	$item = strtolower($item);
	return $item;
}
// previous and next links
if ( isset($_GET['search']) || trim($_GET['search']) != '') {
	// open database connection
	$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}


	// generate and execute query
	$id = $_GET['taxonset_name'];
	$query = "SELECT taxonset_id FROM ". $p_ . "taxonsets WHERE taxonset_id = '$id'";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	// get result set as object
	$row = mysql_fetch_object($result);
	$current_id = $row->id;

	// get previous and next links from search and search_results tables
	$current_id_search_id = $_GET['search'];

	// current id of this record in search_results ids
	$query_c_id_t_r  = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND record_id='$current_id'";
	$result_c_id_t_r = mysql_query($query_c_id_t_r) or die("Error in query: $query_c_id_t_r. " . mysql_error());
	$row_c_id_t_r    = mysql_fetch_object($result_c_id_t_r);

	$link_current  = $row_c_id_t_r->id;

	// link previous
	$link_previous = $link_current - 1;
	$query_link_previous      = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_previous'";
	$result_link_previous     = mysql_query($query_link_previous) or die("Erro in query: $query_link_previous. " . mysql_error());
	$row_result_link_previous = mysql_fetch_object($result_link_previous);

	if ($row_result_link_previous) {
		$query_lp  = "SELECT record_id FROM ". $p_ . "search_results WHERE id='$link_previous'";
		$result_lp = mysql_query($query_lp) or die("Error in query: $query_lp. " . mysql_error());
		$row_lp    = mysql_fetch_object($result_lp);
		$previous  = $row_lp->record_id;
		$query_lpcode  = "SELECT taxonset_name FROM ". $p_ . "taxonsets WHERE taxonset_id='$previous'";
		$result_lpcode = mysql_query($query_lpcode) or die("Error in query: $query_lpcode. " . mysql_error());
		$row_lpcode    = mysql_fetch_object($result_lpcode);
		$prevgeneCode      = $row_lpcode->taxonset_name;
	}
	else {
		$link_previous = false;
	}

	// link next
	$link_next = $link_current + 1;
	$query_link_next  = "SELECT id FROM ". $p_ . "search_results WHERE search_id='$current_id_search_id' AND id='$link_next'";
	$result_link_next = mysql_query($query_link_next) or die("Error in query: $query_link_next. " . mysql_error());
	$row_result_link_next = mysql_fetch_object($result_link_next);

	if ($row_result_link_next) {
		$query_ln  = "SELECT record_id FROM ". $p_ . "search_results WHERE id='$link_next'";
		$result_ln = mysql_query($query_ln) or die("Error in query: $query_ln. " . mysql_error());
		$row_ln    = mysql_fetch_object($result_ln);
		$next      = $row_ln->record_id;
		$query_lncode  = "SELECT taxonset_name FROM ". $p_ . "taxonsets WHERE taxonset_id='$next'";
		$result_lncode = mysql_query($query_lncode) or die("Error in query: $query_lncode. " . mysql_error());
		$row_lncode    = mysql_fetch_object($result_lncode);
		$nextgeneCode      = $row_lncode->taxonset_name;
	}
	else {
		$link_next = false;
	}
} // end previous and next links



//form not yet submitted
// display form for submitting voucher list
// form not yet submitted
// display initial form


// #################################################################################
// Section: brand new record list input
// #################################################################################
if ($_GET['list']) {
	// process title
	$title = $config_sitename;

	// print html headers
	include_once('../includes/header.php');

	// print navegation bar
	admin_nav();
		// begin HTML page content
	echo "<div id=\"content_wide\">";
	?>

<table border="0"> <!-- super table -->
<tr>
	<td valign="top">
		<table width="1000px" border="0"> <!-- big parent table -->
		<tr>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<td valign="top">
			<table border="0" cellspacing="10"> <!-- table child 1 -->
			<tr>
			<td>
			<table width="500" cellspacing="0" border="0">
			<caption>Dataset information</caption>
				<tr>
					<td class="label">Taxonset name</td>
					<td class="field">
						<input size="28" maxlength="250" type="text" name="taxonset_name" value="<?php if(isset($taxonset_name)){ echo "$taxonset_name";} else { echo "";} ?>"></td>
						</select></td>
					<td class="label3">Taxonset creator</td>
					<td class="field2">
						<input size="28" maxlength="250" type="text" name="taxonset_creator" value="<?php if(isset($taxonset_creator)){ echo "$taxonset_creator";} else { echo "";} ?>"></td>
						</select></td> 
				</tr>
				<tr>
					<td class="label">Description</td>
					<td class="field" colspan = "4">
						<input size="80" maxlength="500" type="text" name="taxonset_description" value= "<?php if(isset($taxonset_description)){ echo "$taxonset_description";} else { echo "";} ?>"/>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="6" align="left">
						<input type="submit" class="add" style=" font-size: 10pt;" name="submitNew" value="Add dataset" />
					</td>
				</tr>	
				<tr>
					<td colspan=6 align="center">Add a list of codes:<br />
					tA1<br />
					tA2<br />
					and so on...</br>
					<textarea rows="25" cols="40" wrap='off' name="codes" >
					</textarea>
					</td>
				</tr>
				<tr>
					<td>
						<input type="hidden" name="new_list" value="new_list" />
					</td>
				</tr>
			</table>
			</td>
			</tr>
			</table>
			</td>
		</form>
			<td class="sidebar" valign="top">
				<?php admin_make_sidebar(); 
				?>
			</td>
		</tr>
		</table> <!-- end super table -->
	</td>
</tr>
</table>
		</div> <!-- end content -->

		<!-- standard page footer begins -->
		<?php
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
}

// #################################################################################
// Section: form not yet submitted
//			display initial form
//			brand new record
// #################################################################################
elseif ($_GET['new'] || $_POST['sort'] || $_POST['mark'] || $_POST['unmark']) {
	// process title
	$title = $config_sitename;

	// print html headers
	include_once('../includes/header.php');

	// print navegation bar
	admin_nav();
	// inputs
	if (isset($_POST['taxonset_name'])){ $taxonset_name = $_POST['taxonset_name'];}
	if (isset($_POST['taxonset_creator'])){ $taxonset_creator = $_POST['taxonset_creator'];}
	if (isset($_POST['taxonset_description'])){ $taxonset_description = $_POST['taxonset_description'];}
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
	if (! isset($sort_by) || $sort_by == "" ) { $sort_by = 'code'; $sort_by_array[] = 'code';}
																					//echo "filter_by: " . $_POST['filter_by'] . " filter_text: " . $_POST['filter_text'] . "</br>";
	
	//prepare mark/unmark of active taxa
	if (isset($_POST['mark'])){$markall = "all";}
	elseif (isset($_POST['unmark'])){$markall = "none";}
	else{ $markall = "off"; }
	
	// open database connections for creating taxa list
	@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
	mysql_select_db($db) or die ('Unable to select database');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}
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
		else { unset ($filter_by); }
		$filter_text = trim($_POST['filter_text']);
		}
		//else { unset($filter_by, $filter_text);}
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
			$cia_yes = $cia_genesort = $cia_no = array();
			foreach($code_info_array as $line) {
				$cia_cols = explode ("%", $line);
				if (in_array($cia_cols[0], $taxonset_list)){ $cia_yes[] = $line;}
				elseif (isset($seqLarray_unique) && in_array($cia_cols[0], $seqLarray_unique)){$cia_genesort[] = $line;}
				else { $cia_no[] = $line;}
			}
			//print_r($cia_yes);echo"</br>";print_r($cia_no);echo"</br>";
			$code_info_array = array_merge($cia_yes, $cia_genesort, $cia_no);
		}
	}unset($line);

	// begin HTML page content
	echo "<div id=\"content_wide\">";
	?>

	<table border="0" width="1000px"> <!-- super table -->
	<tr>
		<td valign="top">
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<table width="800" border="0"> <!-- big parent table -->
			<tr>
				<td valign="top">
					<table border="0" cellspacing="0"> <!-- table child 1 -->
					<tr>
						<td>
							<table width="700" cellspacing="0" border="0">
							<caption>Dataset information</caption>
							<tr>
								<td class="label">Taxonset name</td>
								<td class="field">
									<input size="28" maxlength="250" type="text" name="taxonset_name" value="<?php if(isset($taxonset_name)){ echo "$taxonset_name";} else { echo "";} ?>">
								</td>
								<td class="label3">
									Taxonset creator
								</td>
								<td class="field2">
									<input size="28" maxlength="250" type="text" name="taxonset_creator" value="<?php if(isset($taxonset_creator)){ echo "$taxonset_creator";} else { echo "";} ?>">
								</td>
							</tr>
							<tr>
								<td class="label">Description</td>
								<td class="field" colspan = "4">
										<input size="120" maxlength="500" type="text" name="taxonset_description" value= "<?php if(isset($taxonset_description)){ echo "$taxonset_description";} else { echo "";} ?>"/>
								</td>
							</tr>
							</table>
							<br />
							<table cellspacing="0" cellpadding="0">
							<tr>
								<td class="label1">Show seqs: </td>
								<td class="field1" colspan="4">
									<?php // fix the genecode checkbox field
										$genetable = "<table>";
										$i = 0;
											foreach ($geneCodes_array as $gene) {
												$i = $i +1;
												$genetable .= "<td><input type=\"checkbox\" name=\"geneCodes[$gene]\"";
												if (isset($genes)) {if (in_array($gene, $genes)){ $genetable .= " checked "; }}
												$genetable .= ">$gene</td>"; 
												if ($i == 6) {
													$genetable .= "</tr>";
													$i = 0;
												}
											}
											unset ($gene);
											echo "$genetable</table>";
									?>
								</td>
							</tr>
							<tr>
								<td class="label">Filter names:</td>
								<!-- creating the filter regular fields dropdown box and filter text field -->
								<td class="field" colspan="2">
									<select name="filter_by" size="1" style=" BORDER-BOTTOM: outset; BORDER-LEFT: 
										outset; BORDER-RIGHT: outset; BORDER-TOP: outset; FONT-FAMILY: 
										Arial; FONT-SIZE: 14px"> 
										<?php  
										foreach ($filter_array as $fv) { 
											$filter_table .= "<option "; 
											if ( $fv == $filter_by_name){ $filter_table .= "selected ";}
											if ( $fv == "orden" ){ $filter_table .= "value=\"$fv\">Order</option>";}
											elseif ( $fv == "hostorg" ){ $filter_table .= "value=\"$fv\">Host org.</option>";}
											else { 
												$fvu = ucfirst($fv);
												$filter_table .= "value=\"$fv\">$fvu</option>"; 
											}
										}
										echo $filter_table; 
										unset ($filter_table); 
										?>
									</select>
									Enter filter string here:
									<input colspan="2" size="28" maxlength="250" type="text" name="filter_text" value="<?php if(isset($filter_text)){ echo "$filter_text";} else { echo "";} ?>">
								</td>
							</tr>
							<tr>
								<td class="label">Sort by:</td>
								<td class="field" colspan="4">
									<input type="checkbox" name="sort_by[orden]" <?php if (isset($sort_by_array) && in_array("orden", $sort_by_array)) { echo " checked";} ?>>Order
									<input type="checkbox" name="sort_by[family]" <?php if (isset($sort_by_array) && in_array("family", $sort_by_array)) { echo " checked";} ?>>Family
									<input type="checkbox" name="sort_by[subfamily]" <?php if (isset($sort_by_array) && in_array("subfamily", $sort_by_array)) { echo " checked";} ?>>Subfamily
									<input type="checkbox" name="sort_by[genus]" <?php if (isset($sort_by_array) && in_array("genus", $sort_by_array)) { echo " checked";} ?>>Genus
									<input type="checkbox" name="sort_by[species]" <?php if (isset($sort_by_array) && in_array("species", $sort_by_array)) { echo " checked";} ?>>Species
									<input type="checkbox" name="sort_by[hostorg]" <?php if (isset($sort_by_array) && in_array("hostorg", $sort_by_array)) { echo " checked";} ?>>Host org.
									<input type="checkbox" name="sort_by[code]" <?php if (isset($sort_by_array) && in_array("code", $sort_by_array)) { echo " checked";} ?>>Code
									<input type="checkbox" name="sort_by[X]" <?php if ($sort_by_x == "yes") { echo " checked";} ?>>X&nbsp;
								<?php // fix the genecode checkbox sort field
								if (isset($genes)) {
									$genetable = "<table>";
									$i = 0;
									foreach ($genes as $gene) {
										$i = $i +1;
										$genetable .= "<td><input type=\"checkbox\" name=\"sort_by_genes[$gene]\"";
										if (isset($genes)) {if (in_array($gene, $sort_by_genes)){ $genetable .= " checked "; }}
										$genetable .= ">$gene</td>"; 
										if ($i == 6) {
											$genetable .= "</tr>";
											$i = 0;
											}
									}unset ($gene);
									$genetable .= "</table>";
									echo $genetable ;
								}
								?>
								</td>
							</tr>
			<?php  
			if (isset($genes)) { //creating the filter genes dropdown box and filter text field
				$filter_table = "<tr>
									<td class='label'>Filter gene:</td>
									<td class='field' colspan='2'><select name=\"filter_by_gene\" size=\"1\" style=\" BORDER-BOTTOM: outset; BORDER-LEFT: 
											outset; BORDER-RIGHT: outset; BORDER-TOP: outset; FONT-FAMILY: 
											Arial; FONT-SIZE: 14px\">" ;
				$genesforfilt = $genes;
				array_unshift($genesforfilt,"filter");
				foreach ($genesforfilt as $g) {
					$filter_table .= "<option "; 
					if ( $g == $filter_by_gene_name){ $filter_table .= "selected ";}
					$gu = ucfirst($g);
					$filter_table .= "value=\"$g\">$gu</option>";
				}
				echo $filter_table; ?>
				</select>
				Enter mininum number of bp here: <input colspan="2" size="18" maxlength="150" type="text" name="filter_gene_text" value="<?php if(isset($filter_gene_text)){ echo "$filter_gene_text";} else { echo "";} ?>">
				</td>
				</tr>
			<?php
			}
			unset ($filter_table); 
			?>
							<tr>
								<td></td>
								<td class="field4" style="padding: 2px;">
									<input type="submit" style=" font-size: 7pt;" name="mark" value="Mark all" />
									<input type="submit" style=" font-size: 7pt;" name="unmark" value="Unmark all" />
									<input type="submit" style=" font-size: 10pt;" name="sort" value="Filter/Sort" />
								</td>
								<td class="field">
									<input type="submit" class="add" style=" font-size: 10pt;" name="submitNew" value="Add dataset" />
								</td>
							</tr>
							</table>

							<table cellpadding="15">
							<tr>
								<td align="left" valign="top">
								<?php
						$table = "\n<table border='0' frame='below' cellspacing='0'>";
						$table .= "\n<tr>";
						$table .= "<td style=\"width: 5px;\" class='label4'>X</td>
						 <td style=\"width: 150px;\" class='label5'>Code</td>
						 <td style=\"width: 150px;\" class='label5'>Order</td>
						 <td style=\"width: 150px;\" class='label5'>Family</td>
						 <td style=\"width: 150px;\" class='label5'>Subfamily</td>
						 <td style=\"width: 150px;\" class='label5'>Genus</td>
						 <td style=\"width: 150px;\" class='label5'>Species</td>
						 <td style=\"width: 150px;\" class='label5'>Host org.</td>";
						//insert choosen gene info's
						if (isset($genes)) {
							foreach ($genes as $gene) {
								$table .= "<td class='label5'>" . ucfirst($gene) . "</td>";
							}
						}
						$table .= "</tr>";
						echo $table; unset ($table);
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
						else { 
							$filt = "1";
						}
						// if filtered to show
						if ( $filt !== FALSE) { 
							foreach ($line_cols as $col) {
								if ($col == $line_cols[0]) {
									$table .= "<td class='field' "; 	
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";} 
									$table .= "><input type=\"checkbox\" name=\"taxonset_list[$col]\"";
									if ( $markall != "none") {if (isset($taxonset_list) && in_array($col, $taxonset_list) || $markall == "all" ){ $table .= " checked ";}}
									$table .= "></td>";
									$table .= "<td class='field2' ";
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";} 
									$table .= "><a href=" . $base_url . "/home.php onclick=\"return redirect('../story.php?code=$col');\">$col</a></td>";
								}
								else {
									$table .= "<td class='field2' ";
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";}
									$table .= ">$col</td>";
								}
							}
							//insert choosen gene info's
							if (isset($genes)) {
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
							if ($i == 0) {
								$i = 1;
							} 
							else {
								$i = 0;
							}
						}
						else { // if filtered to hide
							if (isset($taxonset_list) && in_array($line_cols[0], $taxonset_list)){ 
								$table .= "<input type=\"checkbox\" name=\"taxonset_list[$line_cols[0]]\" checked style=\"display:none;\"";
							}
						}
						
						//$table .= "\n</tr>";
					}	
				echo "$table"; ?>
								  </td>
								  </tr>
								  </table>
	
								</td>
							</tr>
							</table><!-- end table child 2 -->
						</td>
					</tr>
					</table><!-- end table child 1-->
				</td>
			</tr>
			</table>
			</form>
		</td>
		<td valign="top" class="sidebar">
			<?php admin_make_sidebar(); // includes td and /td already
			?>
		</td>
	</tr>
	</table> <!-- end super table -->
</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
?>
	
	<?php
	}
elseif ($_POST['submitNew'])
	{
	// set up error list array
	$errorList = array();
	
	//validate text input fields
	if (trim($_POST['taxonset_name']) == '')
		{
		$errorList[] = "Invalid entry: <b>Taxon set name</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You must name the taxon set to proceed!";
		}

	$taxonset_name      = trim($_POST['taxonset_name']);
	$taxonset_description = trim($_POST['taxonset_description']);
	$taxonset_creator     = trim($_POST['taxonset_creator']);
	if (isset($_POST['taxonset_list'])) {$taxonset_list     = $_POST['taxonset_list'];}
	
		if (isset($_POST['new_list']) && $_POST['new_list'] == "new_list") { // if submitted list of vouchers
			 //input data
			if (trim($_POST['new_list']) != ""){
				$raw_codes = explode("\n", $_POST['codes']);
			}else{ unset($raw_codes); $errorList[] = "No taxa are chosen</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please choose some at least to make a taxon set!"; }
			$codes = array();
			if (isset($raw_codes)){
				$raw_codes = array_unique($raw_codes); 
				foreach($raw_codes AS $item) {
					if ($item != "") {
						$item = clean_item($item);
						$item = trim($item);
							// open database connections for checking taxa list
						$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
						mysql_select_db($db) or die ('Unable to select database');
						$cquery = "SELECT code FROM ". $p_ . "vouchers WHERE code='$item'";
						$cresult = mysql_query($cquery) or die("Error in query: $query. " . mysql_error());
						// if records present
						if( mysql_num_rows($cresult) > 0 ) {
							while( $row = mysql_fetch_object($cresult) ) {		
							array_push($codes, $row->code);
							}
						}
						else {
						$errorList[] = "No voucher named <b>$item</b> exists in database!</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please add it in the voucher section or remove it from taxon set!";
						}
					}
				}unset($item);
				$taxonset_list = array_unique($codes);
			}
		}
	elseif ($taxonset_list == ""){ // if no fields are checked
			$errorList[] = "No taxa are chosen</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please choose some at least to make a taxon set!";
		}
	else { // if submitted checked fields
		$taxonset_list = array();
		foreach ( $_POST['taxonset_list'] as $k=> $c) {//loops through checkbox values and adds checked taxa to taxonset list
			if ($c == 'on')	{
				$taxonset_list[] = $k;
			}
			}
	}unset($k, $c);
	// creates "," delimitated taxon list in string format
	if (isset($taxonset_list)) {$taxonset_list = implode("," , $taxonset_list);}

		// check for errors
		// if none found ...
		if (sizeof($errorList) == 0 )
			{
			// open database connection
			$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
			//select database
			mysql_select_db($db) or die ('Unable to content');
			if( function_exists(mysql_set_charset) ) {
				mysql_set_charset("utf8");
			}

			
			// check for duplicate taxonset_name
			$querygCode = "SELECT * FROM ". $p_ . "taxonsets WHERE taxonset_name='$taxonset_name'";
			$resultgCode = mysql_query($querygCode) or die ("Error in query: $querygCode. " . mysql_error());
			if (mysql_num_rows($resultgCode) > 0)
				{
				// process title
				$title = "$config_sitename - Error, duplicate taxon set name";
				
				// print html headers
				include_once('../includes/header.php');
				admin_nav();
				
				// begin HTML page content
				echo "<div id=\"content_wide\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
						<tr><td valign=\"top\">";
				echo "<img src=\"../images/warning.png\" alt=\"\">
							The taxon set name you entered is already preoccupied.<br />There wouldn't be practical with two taxon set with the same name!.<br />Please click \"Go back\" in your browser and enter a different name.</td>";
				echo "<td class='sidebar' valign='top'>";
				admin_make_sidebar();
				echo "</td>";
				echo "</tr>
						</table> <!-- end super table -->
						</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				echo "\n</body>\n</html>";
				exit();
				}
			else
				{	

				// generate and execute query
				$gquery = "INSERT INTO ". $p_ . "taxonsets(taxonset_name, taxonset_creator, taxonset_description, taxonset_list) VALUES ('$taxonset_name', '$taxonset_creator', '$taxonset_description', '$taxonset_list')";
			
				$gresult = mysql_query($gquery) or die ("Error in query: $query. " . mysql_error());
				
				// process title
				$title = "$config_sitename - Taxon set " . $taxonset_name . " created";

				// print html headers
				include_once('../includes/header.php');

				// print navegation bar
				admin_nav();

				// begin HTML page content
				echo "<div id=\"content_wide\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
						<tr><td valign=\"top\">";
				// print result
				echo "<span class=\"title\"><img src=\"images/success.png\" alt=\"\"> Taxon set creation of:</br>";  
				echo "</b><a href='$base_url/home.php' onclick=\"return redirect('add_taxonset.php?taxonset_name=$taxonset_name');\">$taxonset_name</a></b>";
				echo " was successful!</span>";
				}

				echo "<td class='sidebar' valign='top'>";
				admin_make_sidebar();
				echo "</td>";
				echo "</tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
			}
		else
			{
			// error found
			
			// get title
			$title = "$config_sitename - Error, missing info";
			
			// print html headers
			include_once('../includes/header.php');
			admin_nav();
			
			// begin HTML page content
			echo "<div id=\"content_wide\">";
			echo "<table border=\"0\" width=\"1000px\"> <!-- super table -->
					<tr><td valign=\"top\">";
			echo "<img src=\"../images/warning.png\" alt=\"\"> The following errors were encountered:";
			echo '<br>';
			echo '<ul>';
			for ($x=0; $x<sizeof($errorList); $x++)
				{
				echo "<li>$errorList[$x]";
				}
			echo "</ul></td>";
			echo "<td class='sidebar' valign='top'>";
			admin_make_sidebar();
			echo "</td></tr>
					</table> <!-- end super table -->
					</div> <!-- end content -->";
			make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
			}
		}

// #################################################################################
// Section:  record to update
//			 get values to prefill fields
// #################################################################################
elseif (!$_POST['submitNoNew'] && $_GET['taxonset_name'] || $_POST['sort2'] || $_POST['unmark2'] || $_POST['mark2']){
	if ($_GET['taxonset_name']){$taxonset_name1 = $_GET['taxonset_name'];}
	else {$taxonset_name1 = $_POST['taxonset_name1'];}
	
	@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}


	if ($_GET['taxonset_name']){
		// gets info from db
		$query1  = "SELECT taxonset_id, taxonset_name, taxonset_creator, taxonset_description, taxonset_list FROM ". $p_ . "taxonsets WHERE taxonset_name='$taxonset_name1'";
		$result1 = mysql_query($query1) or die ("Error in query: $query1. " . mysql_error());
		$row1    = mysql_fetch_object($result1);
		$taxonset_name = $row1->taxonset_name;
		$taxonset_creator = $row1->taxonset_creator;
		$taxonset_description = $row1->taxonset_description;
		$taxonset_list = $row1->taxonset_list;
		$taxonset_list = explode(",", $row1->taxonset_list);
		$taxonset_id = $row1->taxonset_id;
	}
	else {
		// gets info from $_POST
		$taxonset_name      = trim($_POST['taxonset_name']);
		$taxonset_description = trim($_POST['taxonset_description']);
		$taxonset_creator     = trim($_POST['taxonset_creator']);
		$taxonset_id = $_POST['taxonset_id'];
		$taxonset_name1 = $_POST['taxonset_name1'];
		if (isset($_POST['taxonset_list'])){ 
			$taxonset_list = array();
			foreach ( $_POST['taxonset_list'] as $k=> $c) {//loops through checkbox values and adds checked taxa to taxonset list
				if ($c == 'on')	{
					$taxonset_list[] = $k;
				}
			}
		}unset($k, $c);
	}
	
	//prepare mark/unmark of active taxa
	if (isset($_POST['mark2'])){$markall = "all";}
	elseif (isset($_POST['unmark2'])){$markall = "none";}
	else{ $markall = "off"; }
	
	// get title
	$title = "$config_sitename - Edit " . $taxonset_name1;
				
	// print html headers
	include_once('../includes/header.php');
	admin_nav();

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
	}
	unset($k, $c);
	if (! isset($sort_by) || $sort_by =="") { 
		$sort_by = 'orden, family, subfamily, genus, species'; 
		$sort_by_array = explode(", ", $sort_by);
		$sort_by_x = "yes";
	}
	// open database connections for creating taxa list
	$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
	mysql_select_db($db) or die ('Unable to select database');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}

	$cquery = "SELECT code, orden, family, subfamily, genus, species, hostorg FROM ". $p_ . "vouchers ORDER BY $sort_by";
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
		//else { unset($filter_by, $filter_text);}
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
			$querySBG = "SELECT code, sequences FROM " . $p_ . "sequences WHERE geneCode='$sbg' AND length(sequences)>0 ORDER BY length(sequences)";
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
			$cia_yes = $cia_genesort = $cia_no = array();
			foreach($code_info_array as $line) {
				$cia_cols = explode ("%", $line);
				if (in_array($cia_cols[0], $taxonset_list)){ $cia_yes[] = $line;}
				elseif (isset($seqLarray_unique) && in_array($cia_cols[0], $seqLarray_unique)){$cia_genesort[] = $line;}
				else { $cia_no[] = $line;}
			}
			//print_r($cia_yes);echo"</br>";print_r($cia_no);echo"</br>";
			$code_info_array = array_merge($cia_yes, $cia_genesort, $cia_no);
		}
	}unset($line);

	
	// begin HTML page content
	echo "<div id=\"content_wide\">";
	
	?>
	
	<!-- 	show previous and next links -->
	<?php
	echo "<h1>" . "$taxonset_name1" . "</h1>";
	echo "<table border=\"0\" width=\"1000px\"> <!-- super table -->
			<tr><td valign=\"top\">";
			
	if ( isset($_GET['search']) || trim($_GET['search']) != '')
		{
		if ($link_previous)
			{ ?>
			<span dojoType="tooltip" connectId="previous" delay="1" toggle="explode">Previous</span>
			<?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_taxonset.php?taxonset_name=<?php echo $prevtaxonset_name; ?>&amp;search=<?php echo $current_id_search_id; ?>');"><img src="images/leftarrow.png" class="link" alt="previous" id="previous" /></a>&nbsp;&nbsp;
			<?php
			}
		else
			{
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		
		if ($link_next)
			{ ?>
			<span dojoType="tooltip" connectId="next" delay="1" toggle="explode">Next</span>
			<?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_taxonset.php?taxonset_name=<?php echo $nexttaxonset_name; ?>&amp;search=<?php echo $current_id_search_id; ?>');"><img src="images/rightarrow.png" class="link" alt="next" id="next" /></a>
			<?php
			}
		else
			{
			echo "&nbsp;";
			}
		}
	?>
	

<table border="0" width="1000px"> <!-- super table -->
<tr><td valign="top">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<table width="800" border="0"> <!-- big parent table -->
<tr><td valign="top">
	<table border="0" cellspacing="10"> <!-- table child 1 -->
	<tr><td><input class="delete" type="submit" name="delete_taxonset" value="Delete me" /><!-- Delete this sequence! --></td></tr>
	<table width="700" cellspacing="0" border="0">
	<caption>Dataset information</caption>
		<tr>
			<!-- 	input id of this record also, useful for changing the code -->
			<input type="hidden" name="taxonset_id" value="<?php echo $taxonset_id; ?>" />
			<input type="hidden" name="taxonset_name1" value="<?php echo $taxonset_name1; ?>" />
			<!-- 	end input id -->
			<td class="label">Taxonset name</td>
			<td class="field">
				<input size="28" maxlength="250" type="text" name="taxonset_name" value="<?php echo $taxonset_name;?>"></td>
				</select></td>
			<td class="label3">Taxonset creator</td>
			<td class="field2">
				<input size="28" maxlength="250" type="text" name="taxonset_creator" value="<?php echo $taxonset_creator;?>"></td>
				</select></td> 
		</tr>
		<tr>
			<td class="label">Description</td>
			<td class="field" colspan = "4">
					<input size="120" maxlength="500" type="text" name="taxonset_description" value= "<?php echo $taxonset_description;?>"/>
				</select></td>
			</tr>
		<tr>
	</table>
	<br />
	<table cellpadding="0" cellspacing="0">
			<td class="label1">Show seqs: </td><td class="field1" colspan="6">
			<?php // fix the genecode checkbox field
				$genetable = "<table cellspacing='0' cellpadding='0'>";
				 $i = 0;
					foreach ($geneCodes_array as $gene) {
						$i = $i +1;
						$genetable .= "<td><input type=\"checkbox\" name=\"geneCodes[$gene]\"";
						if (isset($genes)) {if (in_array($gene, $genes)){ $genetable .= " checked "; }}
						$genetable .= ">$gene</td>"; 
						if ($i == 6) {
							$genetable .= "</tr>";
							$i = 0;
							}
					}unset ($gene);
					echo "$genetable</table>" ;?>
		</td>
		</tr>
		<td class="label">Filter names:</td>
			<!-- creating the filter regular fields dropdown box and filter text field -->
			<td class="field4"><select name="filter_by" size="1" style=" BORDER-BOTTOM: outset; BORDER-LEFT: 
			outset; BORDER-RIGHT: outset; BORDER-TOP: outset; FONT-FAMILY: 
			Arial; FONT-SIZE: 14px"> 
			<?php // creating the filter dropdown box and filter text field
			foreach ($filter_array as $fv){ 
				$filter_table .= "<option "; 
				if ( $fv == $filter_by_name){ $filter_table .= "selected ";}
				if ( $fv == "orden" ){ $filter_table .= "value=\"$fv\">Order</option>";}
				elseif ( $fv == "hostorg" ){ $filter_table .= "value=\"$fv\">Host org.</option>";}
				else { 
				$fvu = ucfirst($fv);
				$filter_table .= "value=\"$fv\">$fvu</option>"; 
				}
			} echo $filter_table; unset ($filter_table); ?>
			</select>
			</td>
			<td class="field">
				Enter filter string here: <input colspan="2" size="28" maxlength="250" type="text" name="filter_text" value="<?php if(isset($filter_text)){ echo "$filter_text";} else { echo "";} ?>"></td>
		</tr>
	</table>
	<br />
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="label1">Sort by:</td>
			<td class="field1" colspan="4">
				<input type="checkbox" name="sort_by[orden]" <?php if (isset($sort_by_array) && in_array("orden", $sort_by_array)) { echo " checked";} ?>>Order
				<input type="checkbox" name="sort_by[family]" <?php if (isset($sort_by_array) && in_array("family", $sort_by_array)) { echo " checked";} ?>>Family
				<input type="checkbox" name="sort_by[subfamily]" <?php if (isset($sort_by_array) && in_array("subfamily", $sort_by_array)) { echo " checked";} ?>>Subfamily
				<input type="checkbox" name="sort_by[genus]" <?php if (isset($sort_by_array) && in_array("genus", $sort_by_array)) { echo " checked";} ?>>Genus
				<input type="checkbox" name="sort_by[species]" <?php if (isset($sort_by_array) && in_array("species", $sort_by_array)) { echo " checked";} ?>>Species
				<input type="checkbox" name="sort_by[hostorg]" <?php if (isset($sort_by_array) && in_array("hostorg", $sort_by_array)) { echo " checked";} ?>>Host org.
				<input type="checkbox" name="sort_by[code]" <?php if (isset($sort_by_array) && in_array("code", $sort_by_array)) { echo " checked";} ?>>Code
				<input type="checkbox" name="sort_by[X]" <?php if ($sort_by_x == "yes") { echo " checked";} ?>>X&nbsp;
			<?php // fix the genecode checkbox sort field
				if (isset($genes)) {
					$genetable = "<table><tr>";
					$i = 0;
					foreach ($genes as $gene) {
						$i = $i +1;
						$genetable .= "<td><input type=\"checkbox\" name=\"sort_by_genes[$gene]\"";
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
				$filter_table = "<tr>
									<td class='label'>Filter gene:</td>
									<td class='field' colspan='2'>
										<select name=\"filter_by_gene\" size=\"1\" style=\" BORDER-BOTTOM: outset; BORDER-LEFT: 
												outset; BORDER-RIGHT: outset; BORDER-TOP: outset; FONT-FAMILY: 
												Arial; FONT-SIZE: 14px\">" ;
													$genesforfilt = $genes;
													array_unshift($genesforfilt,"filter");
													foreach ($genesforfilt as $g) {
														$filter_table .= "<option "; 
														if ( $g == $filter_by_gene_name){ $filter_table .= "selected ";}
															$gu = ucfirst($g);
															$filter_table .= "value=\"$g\">$gu</option>";
													}
													echo $filter_table; ?>
										</select>
					Enter minimum number of bp here: <input  size="18" maxlength="150" type="text" name="filter_gene_text" value="<?php if(isset($filter_gene_text)){ echo "$filter_gene_text";} else { echo "";} ?>">
				</td>
			</tr>
			<?php
			}
			 unset ($filter_table); ?>
		</tr>
			<td></td>
			<td class="field" rowspan="2"><input type="submit" style=" font-size: 7pt;" name="mark2" value="Mark all" />
			<input type="submit" style=" font-size: 7pt;" name="unmark2" value="Unmark all" />
			<input type="submit" style=" font-size: 10pt;" name="sort2" value="Filter/Sort" /></td>
			<td class="field2"><input type="submit" class="add" style=" font-size: 10pt;" name="submitNoNew" value="Update dataset" /></td>
	</table>
	<br />
	<table cellspacing="0" cellpadding="0">
			<td align="left" valign="top">
			<?php
						$table = "\n<table border='0' frame='below' cellspacing='0'>";
						$table .= "\n<tr>";
						$table .= "<td style=\"width: 5px;\" class='label4'>X</td>
						 <td style=\"width: 150px;\" class='label5'>Code</td>
						 <td style=\"width: 150px;\" class='label5'>Order</td>
						 <td style=\"width: 150px;\" class='label5'>Family</td>
						 <td style=\"width: 150px;\" class='label5'>Subfamily</td>
						 <td style=\"width: 150px;\" class='label5'>Genus</td>
						 <td style=\"width: 150px;\" class='label5'>Species</td>
						 <td style=\"width: 150px;\" class='label5'>Host org.</td>";
						 //insert choosen gene info's
						if (isset($genes)){
							foreach ($genes as $gene){
								$table .= "<td class='label5'>" . ucfirst($gene) . "</td>";
							}
						}
						$table .="</tr>";
						// creating table with filtering
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
									if ( $seqlen > $filter_gene_text ){$filt = "1";} else {$filt = FALSE; }
								}
							}else {$filt = FALSE;} 
						}
						// for X filtering and others
						elseif ($filter_by == "X"){ if (isset($taxonset_list) && in_array($line_cols[0], $taxonset_list)){ $filt = "1";} else {$filt = FALSE; }}
						elseif (isset($filter_by) || isset($filter_text)) { $filt = stripos( $line_cols[$filter_by], $filter_text );}
						else { $filt = "1";}
						// if filtered to show
						if ( $filt !== FALSE) {
							foreach ($line_cols as $col){
								if ($col == $line_cols[0]){
														$table .= "<td class='field' "; 	
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";} 
									$table .= "><input type=\"checkbox\" name=\"taxonset_list[$col]\"";
									if ( $markall != "none") {if (isset($taxonset_list) && in_array($col, $taxonset_list) || $markall == "all" ){ $table .= " checked ";}}
									$table .= "></td>";
									$table .= "<td class='field2' ";
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";} 
									$table .= "><a href=" . $base_url . "/home.php onclick=\"return redirect('../story.php?code=$col');\">$col</a></td>";
								}
								else {
									$table .= "<td class='field2' ";
									if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";}
									$table .= ">$col</td>";
								}
							}
							//insert choosen gene info's
							if (isset($genes)) {
								foreach ($genes as $gene) {
									$query1 = "SELECT sequences FROM ". $p_ . "sequences WHERE code='$line_cols[0]' AND geneCode='$gene'";
									$result1 = mysql_query($query1) or die("Error in query: $query1. " . mysql_error());
									if( mysql_num_rows($result1) > 0 ) {
										while( $row1 = mysql_fetch_object($result1) ) {
											$table .= "<td class=\"field2\" align=\"center\"";
											if ($i == 1){ $table .= "style=\"background-color: #FFF8C6;\"";}
											$table .= ">" . strlen(str_replace("?" , "" , $row1->sequences)) . "</td>";
										}
									}
									else { 
										$table .= "<td class=\"field2\" align=\"center\" ";
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
<td class="sidebar" valign="top">
	<?php admin_make_sidebar(); // includes td and /td already ?>
</td>

</tr>
</table>
</table> <!-- end super table -->

</div> <!-- end content -->

<?php
// close database connection
mysql_close($connection);

make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);

}
elseif ($_POST['submitNoNew'])
	{
	// set up error list array
	$errorList = array();
	
	//validate text input fields
	if (trim($_POST['taxonset_name']) == '')
		{
		$errorList[] = "Invalid entry: <b>Taxon set name</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You must specify a name to proceed!";
		}
	$id1       = $_POST['taxonset_id'];
	$taxonset_name1      = trim($_POST['taxonset_name']);
	$taxonset_description = trim($_POST['taxonset_description']);
	$taxonset_creator     = trim($_POST['taxonset_creator']);
	$taxonset_list     = $_POST['taxonset_list'];
	
	//echo "$id1,	$geneCode1, $description, $length</br>";
	if ($taxonset_list == "" || !isset($taxonset_list)){
			$errorList[] = "No taxa are chosen</b></br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please choose some at least to make a taxon set!";
		}
	else {
		$taxonset_list = array();
		foreach ( $_POST['taxonset_list'] as $k=> $c) {//loops through checkbox values and adds checked taxa to taxonset list
			if ($c == 'on')	{
				$taxonset_list[] = $k;
			}
		}
	}unset($k, $c);
	// creates "," delimitated taxon list in string format
	if (isset($taxonset_list)){$taxonset_list = implode("," , $taxonset_list);}
	else {$errorList[] = "No taxa selected...";}

		
	// check for errors
	// if none found ...
	if (sizeof($errorList) == 0 )
		{
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}

		
		// check if submitted code is meant to replace old one
		// get old code
		$queryOldCode = "SELECT taxonset_name FROM ". $p_ . "taxonsets WHERE taxonset_id='$id1'";
		$resultOldCode = mysql_query($queryOldCode) or die ("Error in query: $queryOldCode. " . mysql_error());
		$rowOldCode    = mysql_fetch_object($resultOldCode);
		$oldCode = $rowOldCode->taxonset_name;
		// get new code
		$new_Code = $taxonset_name1;
		//  if new code != old code
		if ($oldCode != $newCode)
			{
			// check for duplicate
			$queryCode1 = "SELECT taxonset_name FROM ". $p_ . "taxonsets WHERE taxonset_name='$newCode'";
			$resultCode1 = mysql_query($queryCode1) or die ("Error in query: $queryCode1. " . mysql_error());
			if (mysql_num_rows($resultCode1) > 0)
				{
				// get title
				$title = "$config_sitename - Error, duplicate taxon set name";
				
				// print html headers
				include_once('../includes/header.php');
				admin_nav();
				
				// begin HTML page content
				echo "<div id=\"content_wide\">";
				echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
						<tr><td valign=\"top\">";
				echo "<img src=\"../images/warning.png\" alt=\"\">
						The taxon set <b>name</b> ($newCode) you entered is already preoccupied.<br />
						There can't be two taxon sets with the same name!.<br /><br />
						Please click \"Go back\" in your browser and enter a different code.</span>
						</td>";
				echo "<td class='sidebar' valign='top'>";
				admin_make_sidebar(); // includes td and /td already
				echo "</td>";
				echo "</tr>
					  </table> <!-- end super table -->
					  </div> <!-- end content -->";
				make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				exit();
				}
			}
		// generate and execute query UPDATE
		$query = "UPDATE ". $p_ . "taxonsets SET taxonset_name='$taxonset_name1', taxonset_creator='$taxonset_creator', taxonset_description='$taxonset_description', taxonset_list='$taxonset_list' WHERE taxonset_id='$id1'";

		$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
		
		// get title
		$title = "$config_sitename - Record " . $taxonset_name1 . " updated";
				
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
				
		// begin HTML page content
		echo "<div id=\"content_wide\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
			echo "<span class=\"title\"><img src=\"images/success.png\" alt=\"\"> Taxon set update of:</br>";  
			echo "</b><a href='$base_url/home.php' onclick=\"return redirect('add_taxonset.php?taxonset_name=$taxonset_name1');\">$taxonset_name1</a></b>";
			echo " was successful!</span>";
		echo "</td>";
		echo "<td class='sidebar' valign='top'>";
		admin_make_sidebar(); // includes td and /td already
		echo "</td>";
		echo "</tr>
			  </table> <!-- end super table -->
			  </div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
				
		mysql_close($connection);
		}
	else
		{
		// error found
		
		// get title
		$title = "$config_sitenae - Error";
				
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
				
		// begin HTML page content
		echo "<div id=\"content_wide\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";

		// print as list
		echo "<img src=\"../images/warning.png\" alt=\"\">The following errors were encountered:";
		echo '<br>';
		echo '<ul>';
		for ($x=0; $x<sizeof($errorList); $x++)
			{
			echo "<li>$errorList[$x]";
			}
		echo "</ul></td>";
		echo "<td class='sidebar' valign='top'>";
		admin_make_sidebar(); // includes td and /td already
		echo "</td>";
		echo "</tr>
			  </table> <!-- end super table -->
			  </div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		}
	}

// #################################################################################
// Section:  
//			Delete this sequence record by using its ID
// #################################################################################
elseif( $_POST['delete_taxonset'] ) {
	// set up error list array
	$errorList = array();
	//validate text input fields
	if (trim($_POST['taxonset_id']) == '') {
		$errorList[] = "Invalid entry: No saved taxon set choosen for deletion";
		}
	if (trim($_POST['taxonset_name']) == '') {
		$errorList[] = "Invalid entry: <b>Taxonset name</b>";
		}

	// check for errors
	// if none found ...
	if (sizeof($errorList) == 0 ) {
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		//select database
		mysql_select_db($db) or die ('Unable to content');
		if( function_exists(mysql_set_charset) ) {
			mysql_set_charset("utf8");
		}

		// get values
		$id = trim($_POST['taxonset_id']);
		$geneCodeDel = $_POST['taxonset_name'];
		//delete sequence data
		$query = "DELETE FROM ". $p_ . "taxonsets WHERE taxonset_id='$id'";
		$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());

		// begin HTML page content
			// get title
		$title = "$config_sitename - Taxon set deleted";
		// print html headers
		include_once('../includes/header.php');
		admin_nav();
		
		echo "<div id=\"content_wide\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		
		// success mesg
		echo "<img src=\"images/success.png\" alt=\"\"><br />Taxon set:<br /><b> $geneCodeDel</b> was successfuly deleted</td>";
		echo "<td class='sidebar' valign='top'>";
		admin_make_sidebar(); // includes td and /td already
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
		echo "</body>
				</html>";
	}
	else {
		echo "error";
	}
}

// #################################################################################
// Section:  
//			direct access - view gene table
// #################################################################################
elseif (!$_GET['new'] && !$_POST['submitNew'] && !$_POST['submitNoNew'] &&  !$_GET['taxonset_name'] && !$_POST['sort']&& !$_POST['sort2'] && !$_POST['mark'] && !$_POST['unmark'] && !$_POST['mark2'] && !$_POST['unmark2']) {
	// get title
	$title = "$config_sitename - Taxon set list";
				
	// print html headers
	include_once('../includes/header.php');
	admin_nav();
				
	// begin HTML page content
	echo "<div id=\"content_wide\">";
	echo "<table border=\"0\" width=\"1000px\"> <!-- super table -->
			<tr>
				<td valign=\"top\" style=\"text-align: left;\">";
					echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_taxonset.php?new=new');">
						<b>Add Taxon set by browsing</b></a><br /><br /> <?php
					echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_taxonset.php?list=list');">
						<b> Add Taxon set with voucher list</b></a>
						  <br />
						  <br />
	<?php
	// print as list
	// open database connection
	@$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		
	//select database
	mysql_select_db($db) or die ('Unable to content');
	if( function_exists(mysql_set_charset) ) {
		mysql_set_charset("utf8");
	}

	// generate and execute query from genes table
	$query = "SELECT taxonset_id, taxonset_name, taxonset_creator, taxonset_description, taxonset_list 
				FROM ". $p_ . "taxonsets ORDER BY taxonset_name";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
	// if records present
	if (mysql_num_rows($result) > 0) {
		// iterate through result set
		// print article titles
		echo "<h1>Existing taxon sets:</h1>\n";
		
		echo "<b>Taxonsets is a way to make a list of taxa that are being used for a specific project or analysis. A Taxonset is just a list of voucher codes. By having Taxonsets, you can quickly create datasets for them.</b>";

		echo "<ul>";

		$i = 1; // count for tooltips in dojo
		while ($row = mysql_fetch_object($result)) {
			// query from sequences table
			?>
			<li><b>
			<?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('add_taxonset.php?taxonset_name=<?php echo $row -> taxonset_name; ?>');"><?php echo $row -> taxonset_name; ?></a></b>
			<i><?php 
				$numtax = count(explode("," , $row->taxonset_list));
				echo $numtax . " taxa";
				if ($row->taxonset_description != ""){echo ', ' . $row->taxonset_description ;}				
				if ($row->taxonset_creator != ""){echo ' - by: ' . $row->taxonset_creator ;} ?>
			</i>
			<?php
		}

		echo "</ul>";
	}

	// if no records present
	// display message
	else {
		?>
		<b>Taxonsets is a way to make a list of taxa that are being used for a specific project or analysis. A Taxonset is just a list of voucher codes. By having Taxonsets, you can quickly create datasets for them.</b>	

		<br />
		<br />

		<font size="-1">No Taxonsets currently available</font>
	
		<?php
	}
	
	// close database connection
	mysql_close($connection);
	?>
				</td>
				<td class="sidebar" valign="top">
					<?php
					admin_make_sidebar(); // includes td and /td already
		echo "</td>";
	echo "</tr>";
	echo "</table> <!-- end super table -->
		  </div> <!-- end content -->";
		make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
	}
else {
	echo "<div id=\"rest1\"><img src=\"images/warning.png\" alt=\"\" /><span class=\"text\"> Some kind of error ocurred, but I do not know what it is, please try again!</span></div>";
}
?>
	
</body>
</html>
