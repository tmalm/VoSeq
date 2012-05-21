#!/usr/local/bin/php
<?php
error_reporting(0);
# To upload voucher pics from a directory
# make sure that the file name is the same as the voucher code
# make sure that the voucher info is already in the database

#define your UPLOAD DIRECTORY

ob_start();//Hook output buffer - disallows web printing of file info...
include_once'../conf.php';
ob_end_clean();//Clear output buffer//includes

require_once'../api/phpFlickr/phpFlickr.php';

define('UPLOAD_DIRECTORY', "$local_folder/pictures"); ## need to define it!!!
define('PHOTO_EXTENSION', '.png');

// create api
$f = new phpFlickr($flickr_api_key, $flickr_api_secret);
$f->setToken($flickr_api_token);

// create a DirectoryIterator (part of the Standard PHP Library)
$di = new DirectoryIterator(UPLOAD_DIRECTORY);

// open database connections
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
mysql_query("set names utf8") or die("Error in query: " . mysql_error());

$photos = array();
foreach($di as $file) {
	preg_match("/^(.+)\.png\$/", $file, $matches);
	if( $matches ) {
		$photos[] = $matches[1];
	}
}

// upload pictures in the directory
foreach( $photos as $item ) {
	$query = "SELECT id, code, genus, species, subspecies, family, subfamily, tribe, subtribe, country, specificLocality, publishedIn, notes, voucherImage, latitude, longitude FROM ". $p_ . "vouchers WHERE code = \"$item\"";
	$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());

	while( $row = mysql_fetch_object($result) ) {
		$code = $row->code;
		$genus = $row->genus;
		$species = $row->species;
		$subspecies = $row->subspecies;
		$family = $row->family;
		$subfamily = $row->subfamily;
		$tribe = $row->tribe;
		$subtribe = $row->subtribe;
		$latitude = $row->latitude;
		$longitude = $row->longitude;
		if( $row->country != "" ) {
			$country = "$row->country. ";
		}
		else {
			$country = "";
		}

		if( $row->specificLocality != "" ) {
			$specificLocality = "$row->specificLocality, ";
		}
		else {
			$specificLocality = "";
		}

		if( $row->publishedIn != "" ) {
			$publishedIn = "$row->publishedIn, ";
		}
		else {
			$publishedIn = "";
		}

		if( $row->notes != "" ) {
			$notes = "$row->notes, ";
		}
		else {
			$notes = "";
		}
	}
		
	$file = UPLOAD_DIRECTORY . "/" . $item . PHOTO_EXTENSION;

	$photo_id = $f->sync_upload($file, "$code $genus $species $subspecies", "$country $specificLocality $publishedIn $notes", "$country,$family,$subfamily,$tribe,$subtribe,$genus,$species,$subspecies");

	$info = $f->photos_getInfo($photo_id);
	$my_voucherImage = $info['photo']['urls']['url'][0]['_content'];
	$status = $f->photos_geo_setLocation($photo_id, $latitude, $longitude, "3");
	$sizes = $f->photos_getSizes($photo_id);
	
	/*** create thumbnails ***/
	foreach( $sizes as $i) {
		foreach($i as $k => $v) {
			if($k == "label" && $v == "Small") {
				$my_url = $i['source'];
			}
		}
	}
	$query = "UPDATE ". $p_ . "vouchers set timestamp=now(), thumbnail=\"$my_url\", flickr_id=\"$photo_id\", voucherImage=\"$my_voucherImage\" where code=\"$item\""; 
	echo $query . ";\n";
#mysql_query($query) or die("Error in query: $query. " . mysql_error());
}


?>
