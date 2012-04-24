<?php
error_reporting (E_ALL ^ E_NOTICE);

session_start();



$intro_msg = "<h2>This is <b>VoSeq</b>. VoSeq is a database to store voucher and sequence data. 
			    Please send all bug complaints to <br />Carlos Peña (<i>mycalesis@gmail.com</i>) or<br />
				Tobias Malm (<i>tobias.malm@uef.fi</i>)&nbsp;&nbsp;</h2>";


# prefix for tables
$p_ = '';



$mask_url = 'false';

$host = 'localhost';
$user = 'root';
$pass = 'mysqlboriska';
$db   = 'marko_db';

$config_sitename = 'butts';
$version = "1.2.1";
$currentTemplate = 'templates/mytemplate';

$base_url = 'http://localhost/VoSeq';
$local_folder = '/home/carlosp420/data/VoSeq';

$date_timezone = "Europe/Helsinki"; // php5
$php_version = "5";

$flickr_api_key = ""; # Go to http://nymphalidae.utu.fi/cpena/VoSeq to get your keys
$flickr_api_secret = ""; 
$flickr_api_token = ""; 

$yahoo_key = "dj0yJmk9OWg2VmNPaHhvNWZFJmQ9WVdrOVRtNXZabkZoTnpZbWNHbzlNVGN5TkRBeE5qWTJNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD01YQ"; # You need to get an API key for Yahoo! Maps, it's free! This is a test API key

?>
