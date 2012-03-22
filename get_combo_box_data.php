#!/usr/local/bin/php
<?php
include( 'conf.php' );
/*** make dojos .js files ***/

// Initialize default settings
$MYSQL_PATH = '/usr/local/mysql/bin';

$comboName[] = 'orden';
$comboName[] = 'family';
$comboName[] = 'subfamily';
$comboName[] = 'tribe';
$comboName[] = 'subtribe';
$comboName[] = 'genus';
$comboName[] = 'species';
$comboName[] = 'subspecies';
$comboName[] = 'country';
$comboName[] = 'code';
$comboName[] = 'collector';
$comboName[] = 'extractor';

// table sequences
$comboNameSeq[] = 'labPerson';
$comboNameSeq[] = 'geneCode';

// table primers
$comboNamePri[] = 'primer1';
$comboNamePri[] = 'primer2';
$comboNamePri[] = 'primer3';
$comboNamePri[] = 'primer4';
$comboNamePri[] = 'primer5';
$comboNamePri[] = 'primer6';

$cwd = dirname(__FILE__);

// connect to database
$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}


// do table vouchers
foreach ($comboName as $value) {
	$query = "SELECT DISTINCT $value FROM ". $p_ . "vouchers ORDER BY $value ASC";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
	$comboFile = $local_folder . '/dojo_data/comboBoxData_' . $value . '.js';
	if ( file_exists($comboFile) )
		{
		unlink($comboFile);
		}
	$handle = fopen($comboFile, "w");
	fwrite($handle, "[\n");
	
	while( $row = mysql_fetch_object($result) )
		{
		if ( $row->$value == "" )
			{
			continue;
			}
		else
			{
			fwrite($handle, "\t[\"" . $row->$value . "\"],\n");
			}
		}
	fwrite($handle, "]\n");
	echo "$value\n";
	fclose($handle);
	}

// do table sequences
foreach ($comboNameSeq as $value)
	{
	$query = "SELECT DISTINCT $value FROM ". $p_ . "sequences ORDER BY $value ASC";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
	$comboFile = $local_folder . '/dojo_data/comboBoxData_' . $value . '.js';
	if ( file_exists($comboFile) )
		{
		unlink($comboFile);
		}
	$handle = fopen($comboFile, "w");
	fwrite($handle, "[\n");
	
	while( $row = mysql_fetch_object($result) )
		{
		if ( $row->$value == "" )
			{
			continue;
			}
		else
			{
			fwrite($handle, "\t[\"" . $row->$value . "\"],\n");
			}
		}
	fwrite($handle, "]\n");
	echo "$value\n";
	fclose($handle);
	}

// do table primers
foreach ($comboNamePri as $value)
	{
	$query = "SELECT DISTINCT $value FROM ". $p_ . "primers ORDER BY $value ASC";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
	$comboFile = $local_folder . '/dojo_data/comboBoxData_' . $value . '.js';
	if ( file_exists($comboFile) )
		{
		unlink($comboFile);
		}
	$handle = fopen($comboFile, "w");
	fwrite($handle, "[\n");
	
	while( $row = mysql_fetch_object($result) )
		{
		if ( $row->$value == "" )
			{
			continue;
			}
		else
			{
			fwrite($handle, "\t[\"" . $row->$value . "\"],\n");
			}
		}
	fwrite($handle, "]\n");
	echo "$value\n";
	fclose($handle);
	}

?>
