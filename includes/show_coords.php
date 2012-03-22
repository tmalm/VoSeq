<?php
// #################################################################################
// #################################################################################
// Voseq includes/show_coords.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: convert decimal degrees to sexagesimal coordinates for showing
// #################################################################################
// #################################################################################
// Section: convert latitude decimal degrees to sexagesimal coordinates for showing
// #################################################################################
function show_latitude ($coord) {
	if ($coord != NULL) {
		if ($coord < 0) {
			$south = true;
		}
		else {
			$north = true;
		}
		
		$pieces = explode(".", $coord);
		$my_coord = abs($pieces[0]) . "&deg;";
		$my_min = round( ($coord - $pieces[0])*60 );
		if( abs($my_min) < 10 ) {
			$my_coord .= "0" . abs($my_min) . "'";
		}
		else {
			$my_coord .= abs($my_min) . "'";
		}
		if( isset($south) ) {
			$my_coord .= " S";
		}
		if( isset($north) ) {
			$my_coord .= " N";
		}

		echo $my_coord;
	}
}

// #################################################################################
// Section: convert longitude decimal degrees to sexagesimal coordinates for showing
// #################################################################################
function show_longitude ($coord) {
	if ($coord != NULL) {
		if ($coord < 0) {
			$west = true;
		}
		else {
			$east = true;
		}
		
		$pieces = explode(".", $coord);
		$my_coord = abs($pieces[0]) . "&deg;";
		$my_min = round( ($coord - $pieces[0])*60 );
		if( abs($my_min) < 10 ) {
			$my_coord .= "0" . abs($my_min) . "'";
		}
		else {
			$my_coord .= abs($my_min) . "'";
		}
		if( isset($west) ) {
			$my_coord .= " W";
		}
		if( isset($east) ) {
			$my_coord .= " E";
		}

		echo $my_coord;
	}
}
?>
