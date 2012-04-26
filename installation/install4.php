<?php
// include
require_once ('functions.php');

function docheck() {
	$check_masking_url = getParam( $_POST, 'mask_url', '');

	$check_local_folder = getParam( $_POST, 'local_folder', '');

	$check_url = getParam( $_POST, 'url', '');

	$checksiteName = getParam( $_POST, 'siteName', '');
	$a = clean_string($checksiteName);
	$checksiteName = $a[0];

	$checkdatabase_host = getParam( $_POST, 'host', '');
	$a = clean_string($checkdatabase_host);
	$checkdatabase_host = $a[0];

	$checkdatabase_name = getParam( $_POST, 'db', '');
	$a = clean_string($checkdatabase_name);
	$checkdatabase_name = $a[0];

	$checkdatabase_prefix = getParam( $_POST, 'prefix', '');
	$a = clean_string($checkdatabase_prefix);
	$checkdatabase_prefix = $a[0];

	$checkdatabase_username = getParam( $_POST, 'user', '');
	$a = clean_string($checkdatabase_username);
	$checkdatabase_username = $a[0];

	$checkdatabase_password = getParam( $_POST, 'pass', '');
	$a = clean_string($checkdatabase_password);
	$checkdatabase_password = $a[0];

	$a = explode(" ", $_POST['admin_name']);
	$a = clean_string($a[0]);
	$checkadmin_name = $a[0];

	$a = explode(" ", $_POST['admin_name']);
	$a = clean_string($a[1]);
	$checkadmin_lastname = $a[0];

	$checkadmin_login = getParam( $_POST, 'admin_login', '');
	$a = clean_string($checkadmin_login);
	$checkadmin_login = $a[0];

	$checkadmin_password1 = getParam( $_POST, 'admin_password1', '');
	$a = clean_string($checkadmin_password1);
	$checkadmin_password1 = $a[0];


	$checkadmin_password2 = getParam( $_POST, 'admin_password2', '');
	$a = clean_string($checkadmin_password2);
	$checkadmin_password2 = $a[0];

	if ( $checkadmin_password1 != $checkadmin_password2 ) {
		echo "<html><head><title>Error</title><link rel=\"stylesheet\" href=\"install.css\" type=\"text/css\" /></head><body><div class=\"error\"><h2><img src=\"error.png\" alt=\"\" /> The two passwords that you entered do not match, please try again</h2></div></body></html>";
		exit;
	}
	if ( $check_masking_url == "" ||
		 $checksiteName == "" ||
		 $checkdatabase_host == "" || 
		 $checkdatabase_name == "" ||
		 $checkdatabase_username == "" ||
		 $checkdatabase_password == "" ||
		 $checkadmin_name == "" ||
		 $checkadmin_login == "" ||
		 $checkadmin_password1 == "" ||
		 $checkadmin_password2 == ""
		 ) {
		echo "<html><head><title>Error</title><link rel=\"stylesheet\" href=\"install.css\" type=\"text/css\" /></head><body><div class=\"error\"><h2><img src=\"warning.png\" alt=\"\" />You entered invalid or empty details, please try again</h2></div></body></html>";
		exit;
	}

	return array("mask_url" => $check_masking_url,
				 "host" => $checkdatabase_host,
				 "user" => $checkdatabase_username, 
				 "pass" => $checkdatabase_password,
				 "db"   => $checkdatabase_name,
				 "prefix"   => $checkdatabase_prefix,

				 "config_sitename" => $checksiteName,
				 
				 "url" => $check_url,

				 "admin_name" => $checkadmin_name, 
				 "local_folder" => $check_local_folder,

				 "admin_name" => $checkadmin_name, 
				 "admin_lastname" => $checkadmin_lastname, 
				 "admin_login" => $checkadmin_login,
				 "admin_password" => $checkadmin_password1);
}

$variables = docheck();

// Check for PHP 5
if ( !function_exists("version_compare") || version_compare( phpversion(), "5.0.0") < 0 ) {
	// Using PHP4
	$php_version = "4";
}
else {
	// Using PHP5
	$php_version = "5";
}

// get an easier variable for the table's prefix
$p_  = $variables['prefix'];

// if everything is ok, create conf.php file
$string = "<?php" . "\n" . "error_reporting (E_ALL ^ E_NOTICE);\n
session_start();\n
\n
\$intro_msg = \"<h2>This is <b>VoSeq</b>. VoSeq is a database to store voucher and sequence data. 
			    Please send all bug complaints to <br />Carlos Peña (<i>mycalesis@gmail.com</i>) or<br />
				Tobias Malm (<i>tobias.malm@uef.fi</i>)&nbsp;&nbsp;</h2>\";
\n
# prefix for tables
\$p_ = '" . $p_ . "';\n
\n
\$mask_url = '" . $variables['mask_url']. "';\n
\$host = '" . $variables['host']. "';
\$user = '" . $variables['user']. "';
\$pass = '" . $variables['pass']. "';
\$db   = '" . $variables['db']  . "';\n
\$config_sitename = '" . $variables['config_sitename'] . "';
\$version = \"1.2.1\";
\$currentTemplate = 'templates/mytemplate';\n
\$base_url = '" . $variables['url']. "';
\$local_folder = '" . $variables['local_folder']. "';\n";

if($php_version == "5") {
	$string .= "\n\$date_timezone = \"Europe/Helsinki\"; // php5";
	$string .= "\n\$php_version = \"5\";";
}
elseif($php_version == "4") {
	$string .= "\nputenv(\"TZ=\$date_timezone\"); // php4";
	$string .= "\n\$php_version = \"4\";";
}

$string .= "\n
\$flickr_api_key = \"\"; # Go to http://nymphalidae.utu.fi/cpena/VoSeq to get your keys
\$flickr_api_secret = \"\"; 
\$flickr_api_token = \"\"; 

\$yahoo_key = \"dj0yJmk9OWg2VmNPaHhvNWZFJmQ9WVdrOVRtNXZabkZoTnpZbWNHbzlNVGN5TkRBeE5qWTJNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD01YQ\"; # You need to get an API key for Yahoo! Maps, it's free! This is a test API key

?>
";	

# This is a Windows Server
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	$filename = $variables['local_folder'] . "\\" . "conf.php";
	$handle = fopen($filename, "w");
	if(is_writable($filename)) {
		fwrite($handle, $string);
		fclose($handle);

		// make directory for story dojo_data for autocomplete dropboxes (combobox)
		$dojo_data_folder = $variables['local_folder'] . "\\dojo_data";
		if( !file_exists($dojo_data_folder) ) {
			mkdir($dojo_data_folder, 0777);
		}
	}
	else {
		$error[] = "I cannot create the file <code>conf.php</code> in your folder ". $variables['local_folder'] . ". Please set the needed permissions for your folder (write permissions by 'others'). And try again!";
	}
}
else {
	$filename = $variables['local_folder'] . "/" . "conf.php";
	$handle = fopen($filename, "w");
	if(is_writable($filename)) {
		fwrite($handle, $string);
		fclose($handle);

		// chmod to 700 otherwise all php scripts will be terminated in some comercial servers
		chmod('../conf.php', 0700);
		//chmod('conf.php', '0700');

		// make directory for story dojo_data for autocomplete dropboxes (combobox)
		$dojo_data_folder = $variables['local_folder'] . "/dojo_data";
		if( !file_exists($dojo_data_folder) ) {
			mkdir($dojo_data_folder, 0777);
		}
	}
	else {
		$error[] = "I cannot create the file <code>conf.php</code> in your folder ". $variables['local_folder'] . ". Please set the needed permissions for your folder (write permissions by 'others'). And try again!";
	}
}


// create database
// open database connections
@$connection = mysql_connect($variables['host'], $variables['user'], $variables['pass']) or die('Unable to connect');
$query  = "CREATE DATABASE IF NOT EXISTS " . $variables['db'];
mysql_query($query) or die("Error in query: $query. " . mysql_error());
mysql_select_db($variables['db']);

// create tables
$query = "DROP TABLE IF EXISTS " . $p_ . "genes;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "CREATE TABLE " . $p_. "genes ( id smallint(5) unsigned NOT NULL auto_increment, geneCode varchar(255) default NULL, length smallint(4) default NULL, description varchar(255) default NULL, readingframe tinyint(4) default NULL, notes text default NULL, timestamp datetime NOT NULL default '0000-00-00 00:00:00', PRIMARY KEY  (id), UNIQUE KEY geneCode (geneCode)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

$query = "DROP TABLE IF EXISTS ". $p_ . "members;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "CREATE TABLE ". $p_ . "members ( member_id int(11) unsigned NOT NULL auto_increment, firstname varchar(100) default NULL, lastname varchar(100) default NULL, login varchar(100) NOT NULL default '', passwd varchar(100) NOT NULL default '', admin tinyint(1) NOT NULL default 0, PRIMARY KEY  (member_id)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

$query = "DROP TABLE IF EXISTS " . $p_ . "primers;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "CREATE TABLE " . $p_ . "primers ( id smallint(5) unsigned NOT NULL auto_increment, code varchar(255) NOT NULL default '', geneCode varchar(255) default NULL, primer1 varchar(255) default NULL, primer2 varchar(255) default NULL, primer3 varchar(255) default NULL, primer4 varchar(255) default NULL, primer5 varchar(255) default NULL, primer6 varchar(255) default NULL, timestamp datetime NOT NULL default '0000-00-00 00:00:00', PRIMARY KEY  (id)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

$query = "DROP TABLE IF EXISTS ". $p_ . "search;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "CREATE TABLE ". $p_ . "search ( id int(11) unsigned NOT NULL auto_increment, timestamp datetime NOT NULL default '0000-00-00 00:00:00', PRIMARY KEY  (id)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

$query = "DROP TABLE IF EXISTS " . $p_ . "search_results;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "CREATE TABLE " . $p_ . "search_results ( id int(11) unsigned NOT NULL auto_increment, search_id int(11) unsigned NOT NULL default 0, record_id int(11) unsigned NOT NULL default 0, timestamp datetime NOT NULL default '0000-00-00 00:00:00', PRIMARY KEY  (id)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

$query = "DROP TABLE IF EXISTS ". $p_ . "sequences;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "CREATE TABLE ". $p_ . "sequences ( code varchar(255) NOT NULL default '', geneCode varchar(255) default NULL, sequences text, accession varchar(255) default NULL, labPerson varchar(255) default NULL, dateCreation date default NULL, dateModification date default NULL, timestamp datetime NOT NULL default '0000-00-00 00:00:00', id smallint(5) unsigned NOT NULL auto_increment, genbank tinyint(1) default NULL, PRIMARY KEY  (id)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

$query = "DROP TABLE IF EXISTS ". $p_ . "taxonsets;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "CREATE TABLE ". $p_ . "taxonsets ( taxonset_name varchar(45) default NULL, taxonset_creator varchar(75) default NULL, taxonset_description varchar(100) default NULL, taxonset_list text, taxonset_id int(11) NOT NULL auto_increment, PRIMARY KEY  (taxonset_id), UNIQUE KEY id_taxonsets_UNIQUE (taxonset_id)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

$query = "INSERT into ". $p_ . "taxonsets (taxonset_name, taxonset_creator, taxonset_description, taxonset_list, taxonset_id) values
			('Template_taxonset', 'administrator', 'a taxonset of two sample taxa', 'template_1,template_2', '1')";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

$query = "DROP TABLE IF EXISTS " . $p_ . "vouchers;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "CREATE TABLE " . $p_ . "vouchers ( code varchar(255) NOT NULL default '', orden varchar(255) default NULL, family varchar(255) default NULL, subfamily varchar(255) default NULL, tribe varchar(255) default NULL, subtribe varchar(255) default NULL, genus varchar(255) default NULL, species varchar(255) default NULL, subspecies varchar(255) default NULL, country varchar(255) default NULL, specificLocality varchar(255) default NULL, typeSpecies tinyint(1) default NULL, latitude decimal(12,6) default NULL, longitude decimal(12,6) default NULL, altitude varchar(255) default NULL, collector varchar(255) default NULL, dateCollection varchar(30) default NULL, voucherImage varchar(250) NOT NULL default 'na.gif', thumbnail varchar(250) NOT NULL default 'na.gif', extraction smallint(5) default NULL, dateExtraction date default NULL, extractor varchar(255) default NULL, voucherLocality varchar(255) default NULL, publishedIn text, notes text, edits text, latesteditor text, hostorg text, sex varchar(255) default NULL, extractionTube smallint(5) default NULL, voucher varchar(255) default NULL, voucherCode varchar(255) default NULL, flickr_id varchar(255) default NULL, timestamp datetime NOT NULL default '0000-00-00 00:00:00', id smallint(5) unsigned NOT NULL auto_increment, PRIMARY KEY  (id), UNIQUE KEY code (code)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

// upload sample data
$query = "insert into ". $p_ . "vouchers (orden, family, subfamily, tribe, genus, species, subspecies, country, specificLocality, latitude, longitude, altitude, collector, dateCollection, dateExtraction, extractor, voucherLocality, sex, code, timestamp, voucherImage, thumbnail, flickr_id) values
('Lepidoptera', 'Nymphalidae', 'Satyrinae', 'Morphini', 'Morpho', 'achilles', 'agamedes', 'PERU', 'Junin, Aldea', '-10.9', '-74.92', '600-700m', 'J.J. Ramirez', '2003-08-24', '2008-07-17', 'Niklas Wahlberg', 'Wahlberg coll', null, 'template_2', now(), 'http://flickr.com/photos/37256239@N03/3431241828/', 'http://farm4.static.flickr.com/3657/3431241828_d0ef182da6_m.jpg', '3431241828'),
('Lepidoptera', 'Nymphalidae', 'Satyrinae', 'Satyrini', 'Harjesia', 'blanda', null, 'PERU', 'Madre de Dios', '-13.13333', '-69.6', '300m', 'Carlos Pena', '2003-09-26', '2004-07-19', 'Carlos Pena', 'MUSM, Lima', null, 'template_1', now(), 'http://flickr.com/photos/37256239@N03/3431227006/', 'http://farm4.static.flickr.com/3571/3431227006_0753a8d184_m.jpg', '3431227006')";
mysql_query($query) or die("Error in query: $query. " . mysql_error());
$query = "insert into " . $p_ . "sequences (code, geneCode, sequences, accession, labPerson, dateCreation, dateModification, timestamp) values
('template_1', 'template_COI', ' TGAGCAGGAATAGTAGGAACATCCCTTAGTTTAATTATTCGAATAGAATTAGGAAACCCAGGATTTTTAATTGGAGATGATCAAATTTATAATACAATTGTTACAGCTCATGCTTTTATTATAATTTTTTTTATAGTAATACCTATTATAATTGGAGGATTTGGTAATTGATTAGTACCATTAATATTAGGAGCTCCTGATATAGCTTTCCCACGTTTAAATAATATAAGATTTTGATTACTTCCCCCATCTTTAATTTTATTAATTTCAAGTAGTATTGTAAAAAATGGAGTTGGAACAGGATGAACAGTTTACCCCCCTCTTTCCACTAATATTGCTCATAGAGGATCTTCTGTTGATTTAGCCATTTTTTCACTTCATTTAGCTGGAATTTCTTCAATTTTAGGAGCCATTAATTTTATTACAACAATTATTAATATACGAATTAATAATATATCTTATGATCAAATACCCCTATTTATTTGAGCTGTTGGAATTACAGCTCTTCTTTTACTTCTTTCTTTACCTGTTTTAGCTGGAGCTATTACTATACTTTTAACAGATCGAAATTTAAATACATCATTTTTTGATCCTGCAGGAGGAGGAGATCCTATTTTATATCAACATTTATTTTGATTTTTTGGT', 'DQ338800', 'Carlos Pena', '2007-09-23', now(), now()),

('template_2', 'template_COI', 'TAAAGATATTGGAaCCTTATATTTTATTTTTGGAATTTGAGCCGGTATAATTGGCACATCCCTAAGTCTTATTATTCGAACTGAATTAGGAAATCCTAGTTTTTTAATTGGAGATGATCAAATTTATAATACCATTGTAACAGCTCATGCTTTTATTATAATTTTTTTTATAGTTATGCCAATTATAATTGGAGGATTTGGTAATTGACTTGTACCATTAATATTAGGAGCTCCAGATATAGCTTTCCCCCGAATAAATAATATAAGATTTTGATTATTACCTCCATCCTTAATTCTTTTAATTTCAAGTAGAATTGTAGAAAATGGGGCAGGAACTGGATGAACAGTTTACCCCCCACTTTCATCTAATATTGCTCATAGAGGAGCTTCAGTGGATTTAGCTATTTTTTCTTTACATTTAGCTGGAATTTCCTCTATCTTAGGGGCTATTAATTTTATTACTACAATTATTAATATACGAATTAATAATATATCTTATGATCAAATACCTTTATTTGTATGGGCAGTAGGAATTACAGCATTACTTCTCTTACTATCTTTACCAGTTTTAGCTGGAGCTATTACTATGCTTTTAACGGATCGAAATCTTAATACCTCATTTTTTGATCCCGCAGGAGGAGGAGATCCAATTCTTTATCAACATTTATTTTGATTTTTTGG??????????????????????????????????????????????????aTAtTATcTCtCAaGAAAGtGGTAAAAAAGAAACTTTTGGTTGCTTAGGTATAATTTATGCTATATTAGCTATTGGATTATTAGGATTTATTGTTTGAGCTCATCACATATTTACTGTAGGAATAGATATTGATACTCGAGCTTATTTTACTTCAGCTACTATAATTATTGCTGTACCTACTGGTATTAAAATTTTTAGTTGACTTGCAACTTTACATGGAACTCAAATTAATTATAGACCTTCAATACTTTGAGGTTTAGGATTTATTTTCTTATTTACTGTTGGAGGTTTAACTGGAGTTATTTTAGCTAATTCTTCAATTGATATTGCCTTACATGATACATATTATGTTGTTGCCCATTTCCATTATGTTTTATCTATAGGAGCAGTATTTGCTATTTTTGGAGGATTTGTTCATTGATACCCTCTTTTTTCAGGATTAATTTTAAATCCTTATTTACTAAAAATTCAATTTATTTCAATATTTATTGGAGTTAACCTAACTTTTTTTCCCCAACATTTTCTAGGATTAGCCGGGATACCTCGACGATACTCAGATTATCCTGATAGATTTTTATCTTGAAATATTATTTCTTCCCTAGGATCATATATTTCTTTAATTTCAATAATATTAATTATTATTATTATTTGAGAATCTATAATTTATCAACGAATTATTTTATTTCCATTTAATATACCTTCCTCAATTGAATGATACCAAAATCTCCCTCCTGCTGAACATTCATATAATGAAT','JN696159', null, '2011-09-20', now(), now());";
mysql_query($query) or die("Error in query: $query. " . mysql_error());


$query = "insert into ". $p_ . "genes (geneCode, length, description, readingframe, notes) values
							('template_COI', '1487', 'cytochrome c oxidase subunit I (COI)', '1', '');";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

// create administator user
$query = "insert into ". $p_ . "members (firstname, lastname, login, passwd, admin) values ('";
$query .= $variables['admin_name'] . "', '". $variables['admin_lastname'] . "', '". $variables['admin_login']. "', '". sha1($variables['admin_password']). "', '1');";
mysql_query($query) or die("Error in query: $query. " . mysql_error());

// check permisions for folders
exec('chown www-data:www-data ' . $variables['local_folder']);
exec('chmod 777 -R ' . $variables['local_folder']);
if( substr(sprintf('%o', fileperms("../dojo")), -4) != "0777" ) {
	$error[] = "Please change permisions for your folders and all its files. It is necessary 'write permissions' by User, Group and Others:<br /> <code>chmod -R 777 ". $variables['local_folder'] . "/</code>";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>VoSeq - Web Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../favicon.ico" />
<link rel="stylesheet" href="install.css" type="text/css" />
</head>

<body>

	
<div id="wrapper">
	<div id="header">
		<div id="joomla"><img src="header_install.png" alt="Installation" /></div>

	</div>
</div>
<div id="ctr" align="center">
	<?php
	// if error, try again, so try this same file
	if( !isset($error) ) {
		echo "<form action=\"install5.php\" method=\"post\" name=\"form\" id=\"form\" onsubmit=\"return check();\">";
	}

	?>
	<div class="install">
		<div id="stepbar">
			<div class="step-off">
				Installation
			</div>
			<div class="step-off">
				step 1
			</div>
			<div class="step-off">
				step 2
			</div>
			<div class="step-off">
				step 3
			</div>
			<div class="step-on">
				step 4
			</div>
			<div class="step-off">
				step 5
			</div>
		</div>
		<div id="right">
			<div class="far-right">
				<?php
					if( !isset($error) ) {
						echo "<input class='button' type='submit' name='next' value='Next >>' />";
					}
				?>
  			</div>
	  		<div id="step">

	  			step 4
	  		</div>
  			<div class="clr"></div>
			<?php
				if( isset($error) ) {
					if( count($error) > 0 ) {
						echo "<ul>";
						foreach($error as $item) {
							echo "<li><h3>". $item ."</h3></li>";
						}
						echo "</ul>";
					}
					echo "After setting the right permissions for the folder go back to resume the installation process
							<form><input type='button' value='Go back' onclick='history.go(-1)'></form>
							<br />";
				}
			?>
	  		<div class="install-text">
				<div class="form-block">
					<p>Administrator <b><?php echo $variables['admin_name'] . " " . $variables['admin_lastname']; ?></b> login details</p>
					<p><b>Login: <?php echo $variables['admin_login']; ?></b><br />
					<b>Password: ************</b></p>
				</div>
				<br />
				<div class="form-block">
					I have included some sample data in your database.
				</div>
				<br />
				<!-- <div class="form-block">
					You need to create a file named <b>conf.php</b> and copy the text below into it. Thus, this file will contain all
					your configuration. You need to put it in the base folder as your wiki installation (the same directory as <code>home.php</code>).
				</div>
				-->
			</div>
				<br />
		</div>
		<div class="clr"></div>
	</div>
	<?php
		echo "<input type=\"hidden\" name=\"url\" value=\"". $variables['url']. "\" />\n";
		echo "<input type=\"hidden\" name=\"local_folder\" value=\"". $variables['local_folder']. "\" />\n";

		if( !isset($error) ) {
			echo "</form>";
		}
	?>	
</div>
<div class="clr"></div>
<div class="ctr">
	<a href="http://nymphalidae.utu.fi/cpena/VoSeq_docu.html" target="_blank">VoSeq</a></div>

</body>
</html>
