#!/usr/local/bin/php
<?php
# To upload update voucher info in their photo page in Flickr
# it only uploads title and description

require_once('../conf.php');

## change this line ##
$base_url = "http://nymphalidae.utu.fi";

# timestamp argument to do updates. OPTIONAL
# this is the time of the last update
$timestamp = "2012-02-19";

require_once('../api/phpFlickr/phpFlickr.php');

// create api
$f = new phpFlickr($flickr_api_key, $flickr_api_secret);
$f->setToken($flickr_api_token);

// open database connections
$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
mysql_query("set names utf8") or die("Error in query: " . mysql_error());

# get list of voucherImages from db
$query = "SELECT flickr_id FROM ". $p_ . "vouchers WHERE voucherImage is not null AND voucherImage != 'na.gif' AND voucherImage != 'null'";
if($timestamp) {
	$query .= " AND timestamp > '$timestamp' order by timestamp";
}
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
$photos = array();
while( $row = mysql_fetch_object($result) ) {
	$photos[] = $row->flickr_id;
}

// upload pictures in the directory
foreach( $photos as $photo_id ) {
	$query = "SELECT id, code, genus, species, subspecies, family, subfamily, tribe, subtribe, country, specificLocality, publishedIn, notes, 
					voucherImage, latitude, longitude FROM ". $p_ . "vouchers WHERE flickr_id = \"$photo_id\"";
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
		
	echo "$code $genus $species $subspecies $photo_id\n";
	$title = "$code $genus $species $subspecies";
	$description = "$country $specificLocality $publishedIn $notes <a href=\"$base_url/story.php?code=$code\">$base_url/story.php?code=$code</a>";
	$f->photos_setMeta($photo_id, $title, $description); 
	$f->photos_geo_setLocation($photo_id, $latitude, $longitude, "3");
}


?>
