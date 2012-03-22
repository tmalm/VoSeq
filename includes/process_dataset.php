<?php
// #################################################################################
// #################################################################################
// Voseq includes/process_dataset.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Processes info from ../create_dataset.php to create dataset
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
//check admin login session
include'../login/auth.php';
// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include_once'../conf.php';
ob_end_clean();//Clear output buffer//includes
include '../functions.php';
include '../markup-functions.php';
include "translation_functions.php";

$charset_count = array();
$errorList = array();
$geneCodes = array();
$positions = array();
$rfs = array();

// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset)) {
	mysql_set_charset("utf8");
}
// #################################################################################
// Section: Functions - clean_item() and show_errors()
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

function show_errors($se_in) {
		// error found
			// print navegation bar
			nav();
			// begin HTML page content
			echo "<div id=\"content_narrow\">";
			echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
					<tr><td valign=\"top\">";
		// print as list
		echo "<img src=\"../images/warning.png\" alt=\"\"> The following errors were encountered:";
		echo '<br>';
		echo '<ul>';
			$se_in = array_unique($se_in);
			$se_in[] = "</br>Please revise your data!"; 
		foreach($se_in AS $item) {
			echo "$item</br>";
		}
		echo "</td>";
		
		echo "<td class='sidebar'>";
		make_sidebar(); 
		echo "</td>";
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		//make footer
		make_footer($date_timezone, $config_sitename, $version, $base_url);
		?></body></html><?php
}

// #################################################################################
// Section: Get code(s) and gene(s)
// #################################################################################

// #################################################################################
// Section: Special mode - set variables
// #################################################################################
// checking to see if special mode is enabled, and in that case just copy the values and fix the by-gene values and proceed to building the dataset
if (isset($_POST['geneCodes2']) && isset($_POST['codes2']) && isset($_POST['gene_by_positions2']) && isset($_POST['gene_positions2'])){
	$format = $_POST['format2']; //echo "format = >$format<</br>";
	$geneCodes = explode(",", $_POST['geneCodes2']);
	$codes = explode(",", $_POST['codes2']); // $codes3 = implode(",",$codes);echo "codes: " . $codes3 ."</br>";
	$taxonadds = explode(",",$_POST['taxonadds2']); //$taxonadds3 = implode(",",$taxonadds);echo "taxonadds: " . $taxonadds3 ."</br>";
	$outgroup = $_POST['outgroup2']; //echo "outgroup: $outgroup</br>";
	$positions = array("special");
	$by_positions = "special";
	$number_of_taxa = $_POST['number_of_taxa'];
	$gene_positions2 = $_POST['gene_positions2'];
	$gene_by_positions = $_POST['gene_by_positions2'];
	foreach ($geneCodes as $genecode2){
		//get charset counts and rfs
		$charset_count[$genecode2] = $_POST[$genecode2];
		$rfsgenename = $genecode2 . "_rfs";
		$rfs[$genecode2] = $_POST[$rfsgenename];

		//get codon positions
		if (!empty($gene_positions2[$genecode2])) {
			$current_gpos = $gene_positions2[$genecode2];
			foreach ( $current_gpos as $k2=> $c2){ //putting choosen codon positions for genes into array in array
				if ($c2 == 'on')	{
					$gene_positions[$genecode2][] =  $k2;
				}
			}
		}
		else { $gene_positions[$genecode2] = array('all'); }
		if (in_array("all", $gene_positions[$genecode2]) || empty($gene_positions[$genecode2])|| in_array("1st", $gene_positions[$genecode2]) && in_array("2nd", $gene_positions[$genecode2]) && in_array("3rd", $gene_positions[$genecode2])) { 
			unset( $gene_positions[$genecode2] ); 
			$gene_positions[$genecode2] = array('all');
		}
		//get partition-by-which-codon-position
		$current_gbypos = $gene_by_positions2[$genecode2];
	}
}

// #################################################################################
// Section: Amino acid mode - set variables
// #################################################################################
// checking to see if Amino acid mode is enabled, and in that case just copy the values and fix the by-gene values and proceed to building the dataset
elseif (isset($_POST['geneCodes2']) && isset($_POST['codes2']) && isset($_POST['genetic_codes'])){
	$format = $_POST['format2']; //echo "format = >$format<</br>";
	$geneCodes = explode(",", $_POST['geneCodes2']);
	$codes = explode(",", $_POST['codes2']); // $codes3 = implode(",",$codes);echo "codes: " . $codes3 ."</br>";
	$taxonadds = explode(",",$_POST['taxonadds2']); //$taxonadds3 = implode(",",$taxonadds);echo "taxonadds: " . $taxonadds3 ."</br>";
	$outgroup = $_POST['outgroup2']; //echo "outgroup: $outgroup</br>";
	$positions = array("aas");
	$by_positions = "asone";
	$number_of_taxa = $_POST['number_of_taxa'];
	$genetic_codes = $_POST['genetic_codes'];
	foreach ($geneCodes as $genecode2){
		//get charset counts and rfs
		$charset_count[$genecode2] = $_POST[$genecode2];
		$rfsgenename = $genecode2 . "_rfs";
		$rfs[$genecode2] = $_POST[$rfsgenename];
	}
}

// #################################################################################
// Section: normal mode - set variables
// #################################################################################
else { //if special mode or Amino acid mode is not enabled, checking and building from the beginning

	if (isset($_POST['geneCodes'])){
		foreach ( $_POST['geneCodes'] as $k1=> $c1){ //putting choosen genes into array
			if ($c1 == 'on')	{
				$geneCodes[] =  $k1;
			}
		}
	}else {$errorList[] = "No genes choosen - Please try again!"; }

	 //input data
	if (trim($_POST['codes']) != ""){
		$raw_codes = explode("\n", $_POST['codes']);
	}else{ unset($raw_codes); }

	$format = $_POST['format'];
	if ( !isset($format) || $format == ''){$errorList[] = "No dataset FORMAT choosen - Please try again!"; }

	$by_positions = $_POST['by_positions'];

	if ($format == "NEXUS" || $format == "TNT" ){
		if (isset($_POST['outgroup']) && trim($_POST['outgroup']) != "") {
			$outgroup = clean_item($_POST['outgroup']);
			$outgroup = trim($outgroup);
			//$outgroup = trim($_POST['outgroup']);
		}else {unset($outgroup);}
	}else {unset($outgroup);}

	$taxonadds = array();
	foreach ( $_POST['taxonadds'] as $k=> $c){
		if ($c == 'on')	{
			$taxonadds[] = $k;
		}
	}

	// if ($format != "FASTA") { removing this - thus allowing for fasta retrieval of certain positions
		if ( isset($_POST['positions'])){
			$positions = array();
			foreach ( $_POST['positions'] as $k2=> $c2){ //putting choosen genes into array
				if ($c2 == 'on')	{
					$positions[] =  $k2;
				}
			}
		}else {$positions = array("all"); }

		// do some test for "position choices"
		if (in_array('aas', $positions)){ // setting up for amino acid partition mode
			unset( $positions ); 
			$positions = array('aas') ;
			$by_positions = "asone";
			}
		elseif (in_array('special', $positions)){ // setting up for special gene codon partition mode
			unset( $positions ); 
			$positions = array('special') ;
		}
		elseif (in_array("all", $positions) || in_array("1st", $positions) && in_array("2nd", $positions) && in_array("3rd", $positions)) { 
			unset( $positions ); 
			$positions = array('all') ;
		}

		elseif ( ! in_array("all", $positions) && count($positions) < 2 && $by_positions !='special') { //if only one codon position choosen - force "asone"
			$by_positions = "asone";
		}
		else {
			if ($by_positions == "123") {
				$errorList[] = "Cannot use the '12+3' partitioning without including all positions!";
			}
		}


	$result = mysql_query("set names utf8") or die("Error in query: $query. " . mysql_error());

	$seq_string = "?"; #this will be a replacement for NULL sequences
	$bp = 0; #number of base pairs

	//check outgroup existance
	if ($format == "NEXUS" || $format == "TNT"){
		if (isset($outgroup)) {
			$queryog = "SELECT code, genus, species, orden, family, subfamily, tribe, subtribe, subspecies, hostorg ". $p_ . "FROM vouchers WHERE code='$outgroup'";
			$resultog = mysql_query($queryog) or die("Error in query: $query. " . mysql_error());
			// if records present
			if( mysql_num_rows($resultog) > 0 ) {
				$outgroup_to_taxa = $outgroup;
				if ($format == "NEXUS"){
					while( $rowog = mysql_fetch_object($resultog) ) {
						$currentcode = $rowog->code;
							foreach ( $_POST['taxonadds'] as $k=> $c){
								if ($c == 'on')	{
									$taxarray[] .=  $rowog->$k;
								}
							}
							$taxon .= implode("_" , $taxarray);
							$replaces = array(" ","________","_______","______","_____","____","___","__");
							$taxon = str_replace($replaces, "_", $taxon);
							$replaces2 = array("(" , ")" , ";" , ",", "=", "?", "\"", "/");
							$taxon = str_replace($replaces2, "", $taxon);
							$taxon = str_replace("-", "_", $taxon);
							$taxon = str_replace($replaces, "_", $taxon);
							$taxon = substr($taxon, 0, 75);
							$taxon = "$taxon ";
							$outgroup = rtrim($taxon);
					}
				}
			}
			else { $errorList[] = "The specified outgroup: <b>$outgroup</b> does not exist in db!";}
		}
	}
	foreach ($geneCodes AS $item) { // building full dataset bp count + setting charset_count[] values and setting reading frames
		//$item = clean_item($item);
		$gCquery = "SELECT geneCode, length, readingframe FROM ". $p_ . "genes WHERE geneCode='$item'";
		$gCresult = mysql_query($gCquery) or die("Error in query: $query. " . mysql_error());
			// if records present
			if( mysql_num_rows($gCresult) > 0 ) {
				while( $row = mysql_fetch_object($gCresult) ) {
					if ($row->length != "0") {
						$bp = $bp + $row->length;
						$charset_count[$item] = $row->length;
						}
					else { 
						if ($format != "FASTA"){
							$errorList[] = "The length of gene <b>$item</b> (in bp's) has not been specified!
											</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											Please do that in the gene edit section!";
						}
					}
					$rf = $row->readingframe;
					$rfs[$item] = $rf ;
					if ( $rf != "1" && $rf != "2" && $rf != "3"){ 
						if ( $by_positions == "123" || ! in_array("all", $positions) || in_array('aas', $positions)) {
							$errors = "Gene $item doesn't have a specified reading frame!
											</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							if ($by_positions =="123") { $errors .="Cannot use 12+3 partioning";}
							elseif (in_array('aas', $positions)) { $errors .="Cannot translate to amino acids";}
							else  { $errors .="Cannot use individual position choices";}
							$errorList[] = $errors;
						}
					}
				}
			}
		else {
		$errorList[] = "Gene <b>$item</b> does not exist in database!</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please add it in the gene edit section!";
		}
	}unset($item);

	// checking taxonset choice
	$taxonset = $_POST['taxonsets'];
	$taxonset_taxa = array();
	if ($taxonset != "Choose taxonset"){
		$TSquery = "SELECT taxonset_list FROM ". $p_ . "taxonsets WHERE taxonset_name='$taxonset'";
		$TSresult = mysql_query($TSquery) or die("Error in query: $TSquery. " . mysql_error());
			// if records present
			
			if( mysql_num_rows($TSresult) > 0 ) {
				while( $TSrow = mysql_fetch_object($TSresult) ) {
					$taxonset_taxa = explode(",", $TSrow->taxonset_list );
				}
			}
		else {$errorList[] = "No taxon set named <b>$taxonset</b> exists in database!";}
	}else {unset($taxonset_taxa);}

	// merging choosen taxon set taxa and input taxa lists
	if (isset($taxonset_taxa) && isset($raw_codes)){$raw_codes = array_merge( $taxonset_taxa, $raw_codes) ;}
	elseif (isset($taxonset_taxa) && ! isset($raw_codes)){$raw_codes = $taxonset_taxa ;}
	elseif (! isset($taxonset_taxa) && isset($raw_codes)){$raw_codes = $raw_codes ;}
	else { $errorList[] = "No taxa are chosen!</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pointless to make a table without taxa..."; }



	$codes = array();
	if (isset($raw_codes)){
	$raw_codes = array_unique($raw_codes); 
	foreach($raw_codes AS $item) {
		if ($item != "") {
			$item = clean_item($item);
			$item = trim($item);
			if (strpos($item, "--") === 0) {$item = str_replace("--","",$item);}
			$cquery = "SELECT code FROM ". $p_ . "vouchers WHERE code='$item'";
			$cresult = mysql_query($cquery) or die("Error in query: $query. " . mysql_error());
			// if records present
			if( mysql_num_rows($cresult) > 0 ) {
				while( $row = mysql_fetch_object($cresult) ) {		
					array_push($codes, $item);
				}
			}
			else {
			$errorList[] = "No voucher named <b>$item</b> exists in database!</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please add it in the voucher section or remove it from taxon set!";
			}
		}
	}unset($item);
	$codes = array_unique($codes);
	}

	// removing taxa from list that have the removal code -- before them
	$codes_to_remove = array();
	if (isset($taxonset_taxa) && isset($raw_codes)){
		$raw_codes_delete = $raw_codes;
		foreach($raw_codes_delete AS $item) {
			if(strpos($item,'--') !== false) {
				$item = clean_item($item);
				$item = trim($item);
				$item2 = str_replace('--','',$item);
				$codes_to_remove[] = $item2;
			}
		}
		$codes = array_diff($codes, $codes_to_remove);
	}unset($item,$item2);

	//setting outgroups as first taxa in list for TNT datasets
	if (isset($outgroup_to_taxa)) {
		unset($codes_wo_og);
		if ( $format == "TNT" && in_array($outgroup_to_taxa, $codes ) ) { 
			$codes_wo_og = array();
			foreach ($codes as $code1) { 
				if ($code1 != $outgroup_to_taxa){
					$codes_wo_og[] = $code1;
				}
			} 
		}
		if (isset($codes_wo_og)){ $codes = $codes_wo_og;}
		if ( ! in_array($outgroup_to_taxa, $codes) ) { 
			// $errorList[] = "The specified outgruop: <b>$outgroup</b> does not exist among the dataset codes!";
			array_unshift( $codes, $outgroup_to_taxa );
		}
	}

	$number_of_taxa = count($codes);
	if ($number_of_taxa == 0) {$errorList[] = "No codes specified! No use creating empty datasets...
												</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												Please go back and add voucher codes to run!"; 
	}
}

// #################################################################################
// Section: check for error and if none proceed with building dataset
// #################################################################################
if (sizeof($errorList) != 0 ){
	$title = "$config_sitename: Dataset Error";
	// print html headers
	$admin = false;
	$in_includes = true;
	include_once 'header.php';
	//print errors
	show_errors($errorList);
}
else{ 
// #################################################################################
// Section: Start building dataset or specials or amino acid choice outputs
// #################################################################################

// #################################################################################
// Section: Creating special mode choice output
// #################################################################################
	if ( in_array("special", $positions) && !isset($gene_positions2) && !isset($gene_by_positions2) ){
			$title = "$config_sitename: Dataset Special";
		// print html headers
		$admin = false;
		$in_includes = true;
		include_once 'header.php';
		// print navegation bar
		nav();
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\" colspan=\"2\"><h1>Create special dataset</h1>
				<p>Enter the required info to make yourself a ready-to-run dataset in $format format:<br />";
		// print as list
		echo '<form action="process_dataset.php" method="post">';
		echo "Choose wanted codon positions for the separate genes ('all' will override other choices):";
		echo '<br><ul></td></tr>';
		foreach ($geneCodes as $genes) {
			echo "<td>Gene $genes: </td><td>";
			echo "<input type=\"checkbox\" name=\"gene_positions2[$genes][all]\" checked>all&nbsp;&nbsp;&nbsp;";
			echo "<input type=\"checkbox\" name=\"gene_positions2[$genes][1st]\" >1st&nbsp;&nbsp;&nbsp;";
			echo "<input type=\"checkbox\" name=\"gene_positions2[$genes][2nd]\" >2nd&nbsp;&nbsp;&nbsp;";
			echo "<input type=\"checkbox\" name=\"gene_positions2[$genes][3rd]\" >3rd&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<input type=\"radio\" name=\"gene_by_positions2[$genes]\" value=\"asone\" checked>as one&nbsp;&nbsp;&nbsp;";
			echo "<input type=\"radio\" name=\"gene_by_positions2[$genes]\" value=\"each\">each&nbsp;&nbsp;&nbsp;";
			echo "</td></tr>";
		}
		//keeping old values
		$geneCodes2 = implode(",", $geneCodes);
		$codes2 = implode(",", $codes);
		$positions2 = implode(",", $positions);
		$taxonadds2 = implode(",", $taxonadds);
		?>
			<input type="hidden" name="format2" value="<?php echo $format; ?>" >
			<input type="hidden" name="geneCodes2" value="<?php echo $geneCodes2; ?>" >
			<input type="hidden" name="codes2" value="<?php echo $codes2; ?>" >
			<input type="hidden" name="taxonadds2" value="<?php echo $taxonadds2; ?>" >
			<?php if (isset($outgroup)) { ?> <input type="hidden" name="outgroup2" value="<?php echo $outgroup; ?>" > <?php } ?>
			<input type="hidden" name="number_of_taxa" value="<?php echo $number_of_taxa; ?>" >
			<?php foreach ($geneCodes as $genes){
				$value = $charset_count[$genes];
				$rfs2 = $rfs[$genes];
				$rfsgenename = $genes . "_rfs";
				echo "<input type='hidden' name='$genes' value='$value' >";
				echo "<input type='hidden' name='$rfsgenename' value='$rfs2' >";
			}unset($genes, $value); 
			?>
			</td></tr><tr><td><input type="submit" name="process_dataset" value="Continue dataset creation" /></td></tr>
			</form>
			</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->
		<?php
		//make footer
		make_footer($date_timezone, $config_sitename, $version, $base_url);
		echo '</body></html>';
	}

	// end specials choose list --------------------------------------------------------------------------------------------

// #################################################################################
// Section: Creating amino acid choice output
// #################################################################################
	elseif ( in_array("aas", $positions) && !isset($genetic_codes) ){
			$title = "$config_sitename: Amino Acid Dataset";
		// print html headers
		$admin = false;
		$in_includes = true;
		include_once 'header.php';
		// print navegation bar
		nav();
		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\" colspan=\"2\"><h1>Create Amino acid dataset</h1>
				<p>Enter the required info to make yourself a ready-to-run dataset in $format format:<br />";
		// print as list
		echo '<form action="process_dataset.php" method="post">';
		echo "Choose genetic code for translation:";
		echo '<br><ul></td></tr>';
		foreach ($geneCodes as $genes) {
			echo "<td>Gene $genes: </td><td>";
			?>
			<select name=<?php echo "genetic_codes[$genes]";?> size="1" style=" BORDER-BOTTOM: outset; BORDER-LEFT: 
			outset; BORDER-RIGHT: outset; BORDER-TOP: outset; FONT-FAMILY: 
			Arial; FONT-SIZE: 12px"> 
			  <!-- create a pulldown-list with all taxon set names in the db -->
			    <option value=1 selected>Standard
                <option value=2>Vertebrate Mitochondrial
                <option value=3>Yeast Mitochondrial
                <option value=4>Mold, Protozoan and Coelenterate Mitochondrial. Mycoplasma, Spiroplasma
                <option value=5>Invertebrate Mitochondrial
                <option value=6>Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear
                <option value=9>Echinoderm Mitochondrial
                <option value=10>Euplotid Nuclear
                <option value=11>Bacterial and Plant Plastid
                <option value=12>Alternative Yeast Nuclear
                <option value=13>Ascidian Mitochondrial
                <option value=14>Flatworm Mitochondrial
                <option value=15>Blepharisma Macronuclear
                <option value=16>Chlorophycean Mitochondrial
                <option value=21>Trematode Mitochondrial
                <option value=22>Scenedesmus obliquus mitochondrial
                <option value=23>Thraustochytrium mitochondrial code
				</select></td></tr>
				<?php
		}
		//keeping old values
		$geneCodes2 = implode(",", $geneCodes);
		$codes2 = implode(",", $codes);
		$positions2 = implode(",", $positions);
		$taxonadds2 = implode(",", $taxonadds);
		?>
			<input type="hidden" name="format2" value="<?php echo $format; ?>" >
			<input type="hidden" name="geneCodes2" value="<?php echo $geneCodes2; ?>" >
			<input type="hidden" name="codes2" value="<?php echo $codes2; ?>" >
			<input type="hidden" name="taxonadds2" value="<?php echo $taxonadds2; ?>" >
			<?php if (isset($outgroup)) { ?> <input type="hidden" name="outgroup2" value="<?php echo $outgroup; ?>" > <?php } ?>
			<input type="hidden" name="number_of_taxa" value="<?php echo $number_of_taxa; ?>" >
			<?php foreach ($geneCodes as $genes){
				$value = $charset_count[$genes];
				$rfs2 = $rfs[$genes];
				$rfsgenename = $genes . "_rfs";
				echo "<input type='hidden' name='$genes' value='$value' >";
				echo "<input type='hidden' name='$rfsgenename' value='$rfs2' >";
			}unset($genes, $value); 
			?>
			</td></tr><tr><td><input type="submit" name="process_dataset" value="Continue dataset creation" /></td></tr>
			</form>
			</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->
		<?php
		//make footer
		make_footer($date_timezone, $config_sitename, $version, $base_url);
		echo '</body></html>';
	}
	// end aa translation code choose list --------------------------------------------------------------------------------------------
	// #################################################################################
	// Section: Build dataset and adding choosen name-extensions
	// #################################################################################
	else{
	$num_genes = 0;
	$output_lines = "";
	$taxout_array = array();
	$seqout_array = array();
	foreach ($geneCodes AS $geneCode) {
		$num_genes = $num_genes + 1;
		
		foreach ($codes AS $item) {
			$item = clean_item($item);
			$query = "SELECT code, genus, species, orden, family, subfamily, tribe, subtribe, subspecies, hostorg FROM ". $p_ . "vouchers WHERE code='$item'";
			$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
			// if records present
			if( mysql_num_rows($result) > 0 ) {
				while( $row = mysql_fetch_object($result) ) {
					$seq = "?";
					$currentcode = $row->code;
					// #################################################################################
					// Section: Name builder
					// #################################################################################
					$taxon = "";
					$taxarray = array();
						if( $format == "FASTA" ) {	$taxon .= ">";	}
						//foreach ( $_POST['taxonadds'] as $k=> $c){
						//	if ($c == 'on')	{
						foreach ($taxonadds as $k){
							if ($k == genecode) { if ($format == "NEXUS"){$taxarray[] .=  "[$geneCode]";} else {$taxarray[] .=  "$geneCode";	}}
								else {	$taxarray[] .=  $row->$k;	}
						}
						//	}
						//}
						$taxon .= implode("_" , $taxarray);
						$replaces = array(" ","________","_______","______","_____","____","___","__" );
						$taxon = str_replace($replaces, "_", $taxon);
						$taxon = str_replace(">_", ">", $taxon);
						if( $format != "FASTA" ) {
							$replaces2 = array("(" , ")" , ";" , ",", "=", "?", "\"", "/");
							$taxon = str_replace($replaces2, "", $taxon);
							$taxon = str_replace("-", "_", $taxon);
							$taxon = str_replace($replaces, "_", $taxon);
							if ( $format == "NEXUS" ) { 
								//$taxon = substr($taxon, 0, 75); $taxon = "'$taxon'";
								$taxon = substr(str_pad($taxon, 55, " "), 0, 55);
							}
							else {
								$taxon = substr(str_pad($taxon, 55, " "), 0, 55);
							}
						}
						// if (in_array("geneCode", $_POST['taxonadds'])) {$taxon = str_replace("_[", "[", $taxon);}
						if (in_array("geneCode", $taxonadds)) {$taxon = str_replace("_[", "[", $taxon);}
						if ($format == "PHYLIP" && $num_genes > 1) {	$taxon = str_pad("", 55, " ");	}
						if ($format != "FASTA" ) { $taxon = "$taxon ";}
						$taxout_array[$geneCode][$item] = $taxon;
						
					// #################################################################################
					// Section: Sequence builder
					// #################################################################################
					$query_b = "SELECT sequences FROM ". $p_ . "sequences WHERE code='$row->code' AND geneCode='$geneCode'";
					$result_b = mysql_query($query_b) or die("Error in query: $query_b. " . mysql_error());
					// if records present
					if( mysql_num_rows($result_b) > 0 ) {
						while( $row_b = mysql_fetch_object($result_b) ) {
							$seq = $row_b->sequences;
							//if( $format == "FASTA" ) { // do nothing - just present raw sequences
									//$seq = "\n" . $seq;
									$seqout_array[$geneCode][$item] = $seq;
							// }
							//else {						// do something
								if ($format!="FASTA" && strlen($charset_count[$geneCode] < strlen($seq))){ // checking for too long sequence
									$errorList[] = "The $geneCode sequence of $item is longer (". strlen($seq) . ">" . $charset_count[$geneCode] .")that the specified gene length!
									</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please edit gene length or check the sequence";
								}
								elseif ( in_array("aas", $positions) &&  isset($genetic_codes)) {
									// translating amino acid sequence and replacing ? and saces with X
									if ($rfs[$geneCode] == "2") { $seq = substr($seq,1);}
									elseif ($rfs[$geneCode] == "3") { $seq = substr($seq,2);}
									$seq = translate_DNA_to_protein($seq,$genetic_codes[$geneCode]);
									$seq = str_replace("?", "X", $seq);
									$seq = str_replace(" ", "X", $seq);
									$seqout_array[$geneCode][$item] = $seq;
									//$seqout_1 = translate_DNA_to_protein($seq,$genetic_codes[$geneCode]);
									//echo ">$item</br>$seqout_1</br>";
								}
								else {
									if ( isset($gene_positions)){ $positions = $gene_positions[$geneCode];} // if special mode
									// create choosen codon position sequences
									if (! in_array("all", $positions)) {
										if ($rfs[$geneCode] == "1") { $num_nuc = "1";}
										elseif ($rfs[$geneCode] == "2") { $num_nuc = "3";}
										elseif ($rfs[$geneCode] == "3") { $num_nuc = "2";}
										else { $num_nuc = "1";}
										$pos_array = array();
										$sequence_array = preg_split('#(?<=.)(?=.)#s', $seq); // making sequence/nucleotide array
										foreach ($sequence_array as $nuc){
											$pos_array[$num_nuc][] = $nuc;
											if ($num_nuc == 1 || $num_nuc == 2) { $pos_array[12][] = $nuc;}
											if ($num_nuc == 1 || $num_nuc == 3) { $pos_array[13][] = $nuc;}
											if ($num_nuc == 2 || $num_nuc == 3) { $pos_array[23][] = $nuc;}
											if ($num_nuc == 3 ) { $num_nuc = 1;}
											else {$num_nuc = $num_nuc + 1;}
											array_shift($sequence_array);
											}
										if ( in_array("1st", $positions) && ! in_array("2nd", $positions) && ! in_array("3rd", $positions)) { $seqout_array[$geneCode][$item] = implode($pos_array[1]);}
										elseif (! in_array("1st", $positions) && in_array("2nd", $positions) && ! in_array("3rd", $positions)) { $seqout_array[$geneCode][$item] = implode($pos_array[2]);}
										elseif (! in_array("1st", $positions) && ! in_array("2nd", $positions) && in_array("3rd", $positions)) { $seqout_array[$geneCode][$item] = implode($pos_array[3]);}
										elseif ( in_array("1st", $positions) && in_array("2nd", $positions) && ! in_array("3rd", $positions)) { $seqout_array[$geneCode][$item] = implode($pos_array[12]);}
										elseif ( in_array("1st", $positions) && ! in_array("2nd", $positions) &&  in_array("3rd", $positions)) { $seqout_array[$geneCode][$item] = implode($pos_array[13]);}
										elseif (! in_array("1st", $positions) && in_array("2nd", $positions) &&  in_array("3rd", $positions)) { $seqout_array[$geneCode][$item] = implode($pos_array[23]);}
										else { $seqout_array[$geneCode][$item] = $seq; }
									}
									else { $seqout_array[$geneCode][$item] = $seq; }
									// padding string for aligned datasets
									//$seq = str_pad($seq, $charset_count[$geneCode], "?");
								}
							// }
						}
					}
					else {
						if( $format == "FASTA" ) {
							$seq = "\n";
						}
						else {
						if (in_array("aas", $positions)) { $seq = "X"; }
						else { $seq = "?"; }
						$seqout_array[$geneCode][$item] = $seq;
						//$seq = str_pad($seq, $charset_count[$geneCode], "?");
						}
						}
						//$output_lines .= $seq . "\n";
				}
			}
		}
	}unset($item);
	
	// #################################################################################
	// Section: setting bp numbers for partitions if needed
	// #################################################################################
	if ($format != "FASTA" ) { //&& ! in_array("all", $positions)) {
		unset ($charset_count);
		if (isset($seqout_array)){
			foreach ($seqout_array as $g => $s) { 
				if (in_array("aas", $positions)) {$charset_count[$g] = 0; }
				foreach ($s as $n) {
						if (strlen($n) > $charset_count[$g] ) { $charset_count[$g] = strlen($n);}
				}
			} unset($s, $g, $n);
		$bp = array_sum($charset_count);
		}
	}
	// #################################################################################
	// Section: check for error and if none proceed with building dataset
	// #################################################################################
	if (sizeof($errorList) != 0 ){
		$title = "$config_sitename: Dataset Error";
		// print html headers
		$admin = false;
		$in_includes = true;
		include_once 'header.php';
		//print errors
		show_errors($errorList);
	}
	else{ //start building dataset
	
	// #################################################################################
	// Section: Build output - intro lines
	// #################################################################################
	if( $format == "TNT" ) {  // creating intro lines
		if (in_array("aas", $positions)) {$output = "nstates dna;\nxread\n$bp $number_of_taxa\n";}
		else {$output = "nstates prot;\nxread\n$bp $number_of_taxa\n";}
	}
	elseif( $format == "PHYLIP" ) {
		$output = "$number_of_taxa $bp\n";
		$phy_partitions = array();
	}
	elseif( $format == "FASTA" ) {
		$output = "";
	}
	else {
		$which_nex_partitions = array();
		$nex_partitions = array();
		if (in_array("aas", $positions)) {$output = "#NEXUS\n\nBEGIN DATA;\nDIMENSIONS NTAX=$number_of_taxa NCHAR=$bp;\nFORMAT INTERLEAVE DATATYPE=PROTEIN MISSING=X GAP=-;\nMATRIX\n";}
		else {$output = "#NEXUS\n\nBEGIN DATA;\nDIMENSIONS NTAX=$number_of_taxa NCHAR=$bp;\nFORMAT INTERLEAVE DATATYPE=DNA MISSING=? GAP=-;\nMATRIX\n";}
	}
	// #################################################################################
	// Section: Build output - sequence blocks
	// #################################################################################
	foreach ($geneCodes as $geCo){   // creating sequence blocks / gene
		if( $format == "TNT" ) {
			if (in_array("aas", $positions)) {$output .= "\n&[PROTEIN]\n";}
			else{ $output .= "\n&[dna]\n"; }
		}
		elseif( $format == "FASTA" ) {	
			$output .= ">$geCo\n--------------\n";
		}
		elseif ( $format == "NEXUS" ) {
			$output .= "\n[$geCo]\n";
		}
		else {}
		foreach ($codes AS $item) {
			$item = clean_item($item);
			$output .= $taxout_array[$geCo][$item];
			if ($format != "FASTA"){
				if (in_array("aas", $positions)) { $output .= str_pad($seqout_array[$geCo][$item], $charset_count[$geCo], "X") . "\n"; }
				else { $output .= str_pad($seqout_array[$geCo][$item], $charset_count[$geCo], "?") . "\n"; }
			}
			else { $output .= "\n" . $seqout_array[$geCo][$item] . "\n"; }
		}
	}
	// #################################################################################
	// Section: Build output - partition specifying block
	// #################################################################################
	//creating NEXUS and PHYLIP partition-file blocks (for NEXUS only blocks for codon position partitioned
	if( $format == "PHYLIP" || $format == "NEXUS") {
		$phybp = "1";
		foreach ($geneCodes as $gCPHY){  
			if (isset($gene_positions) && isset($gene_by_positions)){ // if special mode
				$positions = $gene_positions[$gCPHY]; 
				$by_positions = $gene_by_positions[$gCPHY];
			}
			if (count($positions) == '1' && !in_array("all", $positions)) {
				$by_positions = "asone";
			}
			//setting frequency of codon positions
			if ( in_array("all", $positions)) { $p_jumps = "\\3" ;}
			else {  if (count($positions) > 1) {$p_jumps = "\\" . count($positions);} else { $p_jumps = ""; } }
			//setting partitionname ending for "all" sequences
			$part_name_end = "";
			if (in_array("1st", $positions)) { $part_name_end .= "1";}
			if (in_array("2nd", $positions)) { $part_name_end .= "2";}
			if (in_array("3rd", $positions)) { $part_name_end .= "3";}
			//setting readingframes
			if ($rfs[$gCPHY] == "2") { 
				if (in_array("all", $positions)) {
					$p_pos1 = "1";$p_pos2 = "2";$p_pos3 = "0";
				} 
				else { 
					if ( ! in_array("1st", $positions)) { $p_pos2 = "1";$p_pos3 = "0";}
					elseif ( ! in_array("2nd", $positions)) { $p_pos1 = "1";$p_pos3 = "0";}
					elseif ( ! in_array("3rd", $positions)) { $p_pos1 = "0";$p_pos2 = "1";}
				}
			}
			elseif ($rfs[$gCPHY] == "3") {
				if (in_array("all", $positions)) {
					$p_pos1 = "2";$p_pos2 = "0";$p_pos3 = "1";
				}
				else { 
					if ( ! in_array("1st", $positions)) { $p_pos2 = "0";$p_pos3 = "1";}
					elseif ( ! in_array("2nd", $positions)) { $p_pos1 = "1";$p_pos3 = "0";}
					elseif ( ! in_array("3rd", $positions)) { $p_pos1 = "1";$p_pos2 = "0";}
				}
			}
			else { 
				if (in_array("all", $positions)) {
					$p_pos1 = "0";$p_pos2 = "1";$p_pos3 = "2";
				}
				else { 
					if ( ! in_array("1st", $positions)) { $p_pos2 = "0";$p_pos3 = "1";}
					elseif ( ! in_array("2nd", $positions)) { $p_pos1 = "0";$p_pos3 = "1";}
					elseif ( ! in_array("3rd", $positions)) { $p_pos1 = "0";$p_pos2 = "1";}
				}
			}
			// set end of gene
			$phybp_end = $phybp + $charset_count[$gCPHY] - 1; 
			// always includes "asone" GENE partitions for NEXUS (with name of specified codon position/s)
			if ( in_array("all", $positions) || in_array("aas", $positions)){
				$nex_partitions[] = "\tcharset $gCPHY = $phybp-$phybp_end;";
				$nex_all_partitions[] = $gCPHY;
				if ($by_positions != "each" && $by_positions !="123") {
					$which_nex_partitions[] = $gCPHY;
				}
			}
			else{
				$nex_partitions[] = "\tcharset $gCPHY" . "_pos$part_name_end" . " = $phybp-$phybp_end;";
				$nex_all_partitions[] = $gCPHY . "_pos$part_name_end";
				if ($by_positions != "each" && $by_positions !="123") {
					$which_nex_partitions[] = $gCPHY . "_pos$part_name_end";
				}
			}
			// and include "asone" GENE partition for special
			// if ( isset($gene_by_positions) && isset($gene_positions) && $by_positions == "asone"){
				// if ( in_array("all", $positions)) {$which_nex_partitions[] = $gCPHY;}
				// else {
					//$nex_partitions[] = "\tcharset $gCPHY" . "_pos$part_name_end" . " = $phybp-$phybp_end;";
					// $which_nex_partitions[] = $gCPHY . "_pos$part_name_end";
				// }
			// }
			if ($by_positions == "asone" ){ //for simple gene partitions
				if ( in_array("all", $positions)){
					$phy_partitions[] = "DNA, $gCPHY = $phybp-$phybp_end";
				}
				elseif ( in_array("aas", $positions)){
					$phy_partitions[] = "AA, $gCPHY = $phybp-$phybp_end";
				}
				else{
					$phy_partitions[] = "DNA, $gCPHY". "_pos$part_name_end = $phybp-$phybp_end";
				}
			}
			elseif ( $by_positions == "each" ){ // for single codon positon partitions
				foreach ( $positions  as $act_pos ) {
					if ( $act_pos == "1st" || $act_pos == "all" ){
						$pos1_start = $phybp + $p_pos1;
						$phy_partitions[] = "DNA, " . $gCPHY . "_pos1 = " . $pos1_start . "-" . $phybp_end .  $p_jumps ;
						$nex_partitions[] = "\tcharset " . $gCPHY . "_pos1 = " . $pos1_start . "-" . $phybp_end .  $p_jumps . ";" ;
						$which_nex_partitions[] = $gCPHY . "_pos1";
					}
					if ( $act_pos == "all" || $act_pos == "2nd" ){
						$pos2_start = $phybp + $p_pos2;
						$phy_partitions[] = "DNA, " . $gCPHY . "_pos2 = " . $pos2_start . "-" . $phybp_end .  $p_jumps ;
						$nex_partitions[] = "\tcharset " . $gCPHY . "_pos2 = " . $pos2_start . "-" . $phybp_end .  $p_jumps . ";" ;
						$which_nex_partitions[] = $gCPHY . "_pos2";
					}
					if ( $act_pos == "all" || $act_pos == "3rd" ){
						$pos3_start = $phybp + $p_pos3;
						$phy_partitions[] = "DNA, " . $gCPHY . "_pos3 = " . $pos3_start . "-" . $phybp_end .  $p_jumps ;
						$nex_partitions[] = "\tcharset " . $gCPHY . "_pos3 = " . $pos3_start . "-" . $phybp_end .  $p_jumps . ";" ;
						$which_nex_partitions[] = $gCPHY . "_pos3";
					}
					}
				}
			else { //partion 1 with codon 1+2 and partition2 with codon 3
				$pos1_start = $phybp + $p_pos1; $pos2_start = $phybp + $p_pos2; $pos3_start = $phybp + $p_pos3;
				$phy_partitions[] = "DNA, " . $gCPHY . "_pos12 = " . $pos1_start . "-" . $phybp_end . "\\3, " . $pos2_start . "-" . $phybp_end . "\\3"; 
				$phy_partitions[] = "DNA, " . $gCPHY . "_pos3 = " . $pos3_start . "-" . $phybp_end . "\\3";
				$which_nex_partitions[] = $gCPHY . "_pos12";
					if ( $pos1_start < $pos2_start ) { $pos12_start = $pos2_start; } else { $pos12_start = $pos1_start; }
				$nex_partitions[] = "\tcharset " . $gCPHY . "_pos12 = " . $pos1_start . "-" . $phybp_end . "\\3 " . $pos2_start . "-" . $phybp_end . "\\3;";
				$nex_partitions[] = "\tcharset " . $gCPHY . "_pos3 = " . $pos3_start . "-" . $phybp_end . "\\3;";
				$which_nex_partitions[] = $gCPHY . "_pos3";
			}
		$phybp = $phybp + $charset_count[$gCPHY];
		}
	}

	// #################################################################################
	// Section: Build output - end lines and end info
	// #################################################################################
	if( $format == "TNT" ) { // creating end block of file
		$output .= ";\nproc/;";
	}
	elseif( $format == "FASTA" ) {
		$output .= "";
	}
	elseif( $format == "PHYLIP" ) {
		$output .= "";
		$phy_partitions = implode("\n", $phy_partitions);
	}
	else {
		$output .= ";\nEND;\n\n";
		
		$output .= "begin mrbayes;\n";
		
		$i = 1;
		$a = 0;
		$b = 0;
		$output .= implode("\n", $nex_partitions);
		$output_genes = implode(", ", $geneCodes);
		$output_genes = implode(", ", $nex_all_partitions);
		$output .= "\npartition GENES = " . count($nex_all_partitions). ": $output_genes;";
		
		if ( $by_positions != "asone" && !isset($gene_by_positions) && !isset($gene_positions)){
			$output_parts = implode(", ", $which_nex_partitions);
			$output .= "\npartition CODONPOSITIONS = " . count($which_nex_partitions) . ": $output_parts;";
			$output .= "\n\nset partition = CODONPOSITIONS;\n";
		}
		elseif ( isset($gene_by_positions) && isset($gene_positions) && $which_nex_partitions != $nex_all_partitions){
			$output_parts = implode(", ", $which_nex_partitions);
			$output .= "\npartition SPECIAL = " . count($which_nex_partitions) . ": $output_parts;";
			$output .= "\n\nset partition = SPECIAL;\n";
		}
		else {
			$output .= "\n\nset partition = GENES;\n";
		}
		$output .= "\nset autoclose=yes;\n";
		if (isset($outgroup)){ $output .= "outgroup $outgroup;\n"; }
		if (in_array("aas", $positions)) {
			$output .= "prset applyto=(all) aamodelpr=mixed ratepr=variable brlensp=unconstrained:Exp(100.0);\n";
			$output .= "lset applyto=(all) rates=gamma [invgamma];\nunlink shape=(all) statefreq=(all) revmat=(all) [pinvar=(all)];\n";
		}
		else {
			$output .= "prset applyto=(all) ratepr=variable brlensp=unconstrained:Exp(100.0);\n";
			$output .= "lset applyto=(all) nst=6 rates=gamma [invgamma];\nunlink shape=(all) statefreq=(all) revmat=(all) tratio=(all) [pinvar=(all)];\n";
		}
		$output .= "mcmc ngen=10000000 printfreq=1000 samplefreq=1000 nchains=4 nruns=2 savebrlens=yes [temp=0.11];\nsump burnin = 2500;\nsumt burnin = 2500;\nend;" ;
	}

	// #################################################################################
	// Section: HTML output and download links
	// #################################################################################
	// print html headers
	$admin = false;
	$in_includes = true;
	include_once 'header.php';
	// print navigation bar
	nav();
	$output2 = $output;
	if ( $format == "PHYLIP" && count($geneCodes) < 2 && $by_positions == "asone") { unset($phy_partitions); }


	// begin HTML page content
	echo "<div id=\"content\">";
	?>
			<form action="dataset_to_file.php" method="post">
			<table border="0" width="960px" cellpadding="5px"> <!-- super table -->
			<tr>
				<td align='center' width="100%" class="label4">Your dataset: copy/edit/create file</td>
			</tr>
			<tr>
				<td class="field1"><textarea rows="35" cols="125" wrap='off' name="dataset"><?php echo $output2; ?></textarea></td>
			</tr>
				<tr><input type="hidden" name="format" value="<?php echo $format; ?>">
			<td>
			<input type="submit" name="submit" value="Create file">
			<?php if ( $format == "PHYLIP" && isset($phy_partitions)) {
				?><!--<input type="hidden" name="phy_partitions" value="< ?php echo $phy_partitions; ?>"> -->
				<tr>
				<td class="field"><textarea rows="10" cols="40" wrap='off' name="phy_partitions" ><?php echo $phy_partitions; ?></textarea></td>
				</tr><tr>
				<td><input type="submit" name="phy_parts" value="Create PHYLIP partitions file"><?php } ?>
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
	<?php
	}
	}
}
?>
