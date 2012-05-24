<?php
include_once("../conf.php");


$connection = mysql_connect($host, $user, $pass) or die("unable to connect");
mysql_select_db($db) or die ("unable to select database");
mysql_query("set names utf8");

$query = "alter table ". $p_ . "vouchers add column determinedBy varchar(255)";
$query .= " default null after flickr_id";
mysql_query($query) or die ("Error in query: $query. " . mysql_error());
echo "Upgrading table vouchers\n";




$query = "alter table ". $p_ . "vouchers add column auctor varchar(255)";
$query .= " default null after determinedBy";

mysql_query($query) or die ("Error in query: $query. " . mysql_error());
echo "Upgrading table vouchers\n";



$query = "alter table ". $p_ . "sequences add column notes varchar(255)";
$query .= " default null after dateModification";

mysql_query($query) or die ("Error in query: $query. " . mysql_error());
echo "Upgrading table sequences\n";
?>
