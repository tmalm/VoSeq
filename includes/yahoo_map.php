<?php
// #################################################################################
// #################################################################################
// Voseq includes/yahoo_map.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Function printing a yahoo map with specified coords on the 
// ../story.php web page
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
function yahoo_map($d_lat, $d_long) {
	if ($d_lat != NULL AND $d_long != NULL) {
	    echo "<div id=\"map\" style=\"width: 200px; height: 200px\"></div>";
		echo "<script type=\"text/javascript\">
				var map = new YMap(document.getElementById(\"map\"), YAHOO_MAP_HYB);
				var myImage = new YImage();
				myImage.src = \"images/redtack.png\";
				myImage.size = new YSize(32,32);";
		echo "var center = new YGeoPoint($d_lat,$d_long);";
		echo   "map.drawZoomAndCenter(center, 13);
				map.addZoomShort();
				map.removeZoomScale();";
		echo "var point = new YGeoPoint($d_lat,$d_long);";
		echo "var marker = new YMarker(point, myImage);";
		echo "map.addOverlay(marker);";
		echo "</script>";
	}
}
/*
function make_center($filename) {
	$lines = file($filename);
	$d_lats  = array();
	$d_longs = array();
	foreach ($lines as $line) {
		$d_coord = explode(",", $line);
		array_push($d_lats, $d_coord[0]);
		array_push($d_longs, rtrim($d_coord[1]));
	}
	$divisor = count($d_lats);
	$sum = array_sum($d_lats);
	$d_lat = $sum/$divisor;

	$divisor = count($d_longs);
	$sum = array_sum($d_longs);
	$d_long = $sum/$divisor;

	echo "var center = new YGeoPoint($d_lat,$d_long);";

	make_markers($lines);
}

function make_markers($lines) {
	$count = 0;

	foreach ($lines as $line) {
		$d_coord = explode(",", $line);
		$d_lat = $d_coord[0];
		$d_long = rtrim($d_coord[1]);

		echo "var point"  . $count . " = new YGeoPoint($d_lat,$d_long);";
		echo "var marker" . $count . " = new YMarker(point". $count . ", myImage);";
		echo "map.addOverlay(marker" . $count . ");";

		$count++;
	}
}*/
?>
