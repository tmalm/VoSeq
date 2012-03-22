<?php
// #################################################################################
// #################################################################################
// Voseq includes/validate_coords.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: validate lat and long coords via validate.php
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
include'validate.php';
// #################################################################################
// Section: validate latitude
// #################################################################################
function validate_lat($lat) {
	unset($valid_lat);
	$options_lat = array("decimal" => ".",
					     "min"     => -89.999999,
					     "max"     =>  89.999999);

	$validate_lat = new Validate();
	if ($validate_lat->number("$lat", $options_lat)) {
		// "Valid number\n";
		return true;
	}
	else {
		// "Invalid number\n";
		return false;
	}
}

// #################################################################################
// Section: validate longitude
// #################################################################################
function validate_long($long) {
	unset($valid_long);
	$options_long = array("decimal" => ".",
					      "min"     => -179.999999,
					      "max"     =>  179.999999);

	$validate_long = new Validate();
	if ($validate_long->number("$long", $options_long)) {
		// "Valid number\n";
		return true;
	}
	else {
		// "Invalid number\n";
		return false;
	}
}

?>
