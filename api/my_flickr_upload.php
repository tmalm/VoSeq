<?php
// #################################################################################
// #################################################################################
// Voseq api/my_flickr_upload.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Maintenance script, to be run from the command line 
//
// #################################################################################


// #################################################################################
// Section: include functions
// #################################################################################

ob_start();//Hook output buffer - disallows web printing of file info...
include_once'../conf.php';
ob_end_clean();//Clear output buffer//includes

require_once'Phlickr/Api.php';
require_once'Phlickr/Uploader.php';
require_once'Phlickr/Photo.php';

define('UPLOAD_DIRECTORY', '/data/fun/pictures/mas fotos/Estocolmo/2009_04_14');
define('PHOTO_EXTENSION', '.jpg');

// create an api
#$api = new Phlickr_Api($flickr_api_key, $flickr_api_secret, FLICKR_API_TOKEN);
$api = new Phlickr_Api($flickr_api_key, $flickr_api_secret, $flickr_api_token);
// create an uploader
$uploader = new Phlickr_Uploader($api);
// array to keep track of the photo ids as they're uploaded
$photo_ids = array();
// create a DirectoryIterator (part of the Standard PHP Library)
$di = new DirectoryIterator(UPLOAD_DIRECTORY);

// open database connections
$connection = mysql_connect($host, $user, $pass) or die('Unable to connect');
mysql_select_db($db) or die ('Unable to select database');
mysql_query("set names utf8") or die("Error in query: " . mysql_error());

// generate and execute query
$query = "SELECT id, code, genus, species, subspecies, family, subfamily, tribe, subtribe, country, specificLocality, publishedIn, notes, voucherImage FROM ". $p_ . "vouchers WHERE id=5446 ";
#$query = "SELECT id, code, genus, species, subspecies, family, subfamily, tribe, subtribe, country, specificLocality, publishedIn, notes, voucherImage FROM vouchers WHERE id=3691 AND id=5444 and id=5445 and id=5446";
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
	
// if records present
if (mysql_num_rows($result) > 0) {
	while ($row = mysql_fetch_object($result)) {
		$item = "/home/carlosp/data/nsgdb_flickr/pictures/" . $row->voucherImage;
		echo "$item\n";

		if ($item != NULL && $item != 'na.gif') {
			$extension = substr($item, - strlen(PHOTO_EXTENSION));
			if (strtolower($extension) === strtolower(PHOTO_EXTENSION)) {
				print "Uploading $item...\n";
				// upload the photo
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
				$id = $uploader->upload($item, "$row->code $row->genus $row->species $row->subspecies",
											"$country $specificLocality $publishedIn $notes",
											"$row->country,$row->family,$row->subfamily,$row->tribe,$row->subtribe,$row->genus,$row->species,$row->subspecies");

				$query = "UPDATE ". $p_ . "vouchers set flickr_id=\"$id\" where code=\"$row->code\""; 
				echo "\n$query\n";
				mysql_query($query) or die("Error in query: $query. " . mysql_error());

				// create photo_url -- thumbnail
				$photo_url = new Phlickr_Photo($api, $id);
				$my_url = $photo_url->buildImgUrl("m");
				$my_url = str_replace("XXX", $id, $my_url);

				echo "\n";

				$query = "UPDATE ". $p_ . "vouchers set thumbnail=\"$my_url\" where code=\"$row->code\""; 
				echo "\n$query\n";
				mysql_query($query) or die("Error in query: $query. " . mysql_error());

				// create photo_url -- voucherImage
				$my_voucherImage = $photo_url->buildUrl();
				$query = "UPDATE ". $p_ . "vouchers set voucherImage=\"$my_voucherImage\" where code=\"$row->code\""; 
				echo "\n$query\n";
				mysql_query($query) or die("Error in query: $query. " . mysql_error());

				echo "____________________________________________\n";

				mysql_query("update ". $p_ . "vouchers set timestamp=now() where code=\"$row->code\"") or die("Error in query:  " . mysql_error());
				// save the photo's id to an array
				$photo_ids[] = $id;
			}
		}
	}
}


// print out the post-upload edit link.
if (count($photo_ids)) {
    printf("All done! If you care to make some changes:\n%s",
        $uploader->buildEditUrl($photo_ids));
}

?>
