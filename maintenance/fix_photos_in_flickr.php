#!/usr/local/bin/php
<?php
error_reporting(0);
/*  To fix issues in the photos hosted in Flickr
 * Updates a photo description replacing see our database link to something else 
 * specially useful when your database is not public, so viewers will not get an
 * "No permission to see this folder" message                                    */

ob_start();//Hook output buffer - disallows web printing of file info...
include_once'../conf.php';
ob_end_clean();//Clear output buffer//includes

require_once'../api/phpFlickr/phpFlickr.php';

// create api
$f = new phpFlickr($flickr_api_key, $flickr_api_secret);
$f->setToken($flickr_api_token);

$new_description = "http://nymphalidae.utu.fi/story.php?code=";

// open database connections
$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
mysql_query("set names utf8") or die("Error in query: " . mysql_error());

$photos = array();
#$query = "SELECT id, flickr_id, code, genus, species, subspecies, country, specificLocality, publishedIn, notes from vouchers WHERE flickr_id is not null AND flickr_id != '' AND id  > 5400 order by id asc";
$query = "SELECT id, flickr_id, code, genus, species, subspecies, country, specificLocality, publishedIn, notes, latitude, longitude FROM ". $p_ . "vouchers WHERE flickr_id is not null AND flickr_id != '' AND timestamp > '2012-02-18' order by id asc";
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());

while( $row = mysql_fetch_object($result) ) {
	if( $row->longitude != "" ) {
		$longitude = $row->longitude;
	}
	else {
		$longitude = "";
	}

	if( $row->latitude != "" ) {
		$latitude = $row->latitude;
	}
	else {
		$latitude = "";
	}

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

	$photos[] = array('photo_id' => $row->flickr_id,
					  'country' =>  $country,
					  'specificLocality' => $specificLocality,
					  'publishedIn' => $publishedIn,
					  'code' => $row->code,
					  'notes' => $notes,
					  'genus' => $row->genus,
					  'species' => $row->species,
					  'subspecies' => $row->subspecies,
					  'id' => $row->id,
					  'latitude' => $latitude,
					  'longitude' => $longitude,
					  );
}

foreach($photos as $photo) {
	$lat = $photo['latitude'];
	$long = $photo['longitude'];

	$photo_id = $photo['photo_id'];
	$country = $photo['country'];
	$specificLocality = $photo['specificLocality'];
	$publishedIn = $photo['publishedIn'];
	$code = $photo['code'];
	$notes = $photo['notes'];
	$genus = $photo['genus'];
	$species = $photo['species'];
	$subspecies = $photo['subspecies'];
	$id = $photo['id'];

	$title = "$code $genus $species $subspecies";

	$newDescription = "$country $specificLocality $publishedIn $notes " . "<a href=\"$new_description" . $code . "\">see in our database</a>";
	$f->photos_setMeta($photo_id, $title, $newDescription);
	echo "\nDoing id=$id ". $photo_id . " " . $title . "\t" . $newDescription;

	// upload coordinates
	if( $lat != "" && $long != "" ) {
		$accuracy = "3";
		$f->photos_geo_setLocation($photo_id, $lat, $long, $accuracy);
#print_r($f);
		echo "\n\t\tuploading coordinates $lat $long";
	}
}

#$latitude = $row->latitude;
#$longitude = $row->longitude;
#$info = $f->photos_getInfo($photo_id);
#$my_voucherImage = $info['photo']['urls']['url'][0]['_content'];
#$status = $f->photos_geo_setLocation($photo_id, $latitude, $longitude, "3");
#$sizes = $f->photos_getSizes($photo_id);
#
#
?>
