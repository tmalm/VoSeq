<?php
// #################################################################################
// #################################################################################
// Voseq api/photo_to_eol.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: sends a photo to EOL pool in Flickr by
//					adding a "machine tag" in the Flickr page for this voucher photo
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################
include_once('../functions.php');

foreach($_GET as $k=>$v) {
	$v = clean_string($v);
	$_GET[$k] = $v[0];
}

$photo_id = $_GET['photo_id'];

if( $photo_id == "" ) {
	echo '{ "stat": "failed to get photo_id" }';
	exit(0);
}

ob_start();
include('../conf.php');
ob_end_clean();

require_once('phpFlickr/phpFlickr.php');

$f = new phpFlickr($flickr_api_key, $flickr_api_secret);
$f->setToken($flickr_api_token);


// #################################################################################
// Section: get metadata from MySQL for this voucher photo
// #################################################################################
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect MySQL');
mysql_select_db($db) or die('Unable to select database');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset('utf8');
}
$query = "SELECT family, genus, species, subspecies, latitude, longitude FROM ". $p_ . "vouchers where flickr_id = '$photo_id'";
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
while( $row = mysql_fetch_object($result) ) {
	if( $row->family != "" ) {
		$family = $row->family;
	}
	else {
		$family = "";
	}

	if( $row->genus != "" ) {
		$genus = $row->genus;
	}
	else {
		$genus = "";
	}

	if( $row->species != "" ) {
		$species = $row->species;
	}
	else {
		$species = "";
	}

	if( $row->subspecies != "" ) {
		$subspecies = $row->subspecies;
	}
	else {
		$subspecies = "";
	}

	if( $row->latitude != "" ) {
		$latitude = $row->latitude;
	}
	else {
		$latitude = "";
	}

	if( $row->longitude != "" ) {
		$longitude = $row->longitude;
	}
	else {
		$longitude = "";
	}
}

if( $genus != "" && $species != "" && $subspecies != "" ) {
	$tags = "\"taxonomy:trinomial=$genus $species $subspecies\"";
}
elseif( $genus != "" && $species != "" && $subspecies == "" ) {
	$tags = "\"taxonomy:binomial=$genus $species\"";
}
elseif( $genus != "" && $species == "" && $subspecies == "" ) {
	$tags = "taxonomy:genus=$genus";
}
elseif( $genus == "" && $species == "" && $subspecies == "" && $family != "" ) {
	$tags = "taxonomy:family=$family";
}
else {
	echo '{ "stat": "failed to obtain species name" }';
	exit(0);
}

if( $latitude != "" && $longitude != "" ) {
	$tags .= "," . "geo:lat=" . $latitude . "," . "geo:lon=" . $longitude;
}


// #################################################################################
// Section: adding machine tags to photo in Flickr for EOL 
// #################################################################################
$f->photos_addTags($photo_id, $tags);
if( $f->parsed_response['stat'] != "ok" ) {
	echo '{ "stat": "failed to add machine tags" }';
	exit(0);
}


// #################################################################################
// Section: set license for being accepted by EOL 
//			if license is not allowed by EOL
// #################################################################################
$license = $f->photos_getInfo($photo_id);
$license = $license['photo']['license'];
# <license id="0"> means None (All rights reserved)
if( $license == "0" ) {
	# change it to <license id="4"> "Attribution License" url="http://creativecommons.org/licenses/by/2.0/"
	$f->photos_licenses_setLicense($photo_id, "4");
	if( $f->parsed_response['stat'] != "ok" ) {
		echo '{ "stat": "failed to set license: Attribution License" }';
		exit(0);
	}
}


// #################################################################################
// Section: add photo to EOL group, which has the id = 806927@20
// #################################################################################
$f->groups_pools_add($photo_id, "806927@N20");
if( $f->parsed_response['stat'] != "ok" ) {
	echo '{ "stat": "failed to add to EOL group" }';
	exit(0);
}

echo '{ "stat": "ok" }';
?>
