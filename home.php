<?php
// #################################################################################
// #################################################################################
// Voseq home.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: The (redirect) home page for url masking
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include 'conf.php';
if( $mask_url == "true" ) {
	ob_end_clean();//Clear output buffer//includes
}
else {
	ob_clean();//Clear output buffer//includes
}
// #################################################################################
// Section: Changing url for masking
// #################################################################################
if( $mask_url == "true" ) {
	echo "<HTML>";
	echo "<HEAD>
			<META NAME='description' CONTENT='" . $base_url ."/home.php'>
			<META NAME='keywords' CONTENT=''>
			<title>" . $config_sitename . "</title>
		</HEAD>
		<frameset border='0' rows='100%,*' frameborder='no' marginleft='0' margintop='0' marginright='0' marginbottom='0'>
			<frame src='" . $base_url . "/login/login-form.php' scrolling='auto' frameborder='no' border='0' noresize>
			<frame topmargin='0' marginwidth='0' scrolling='no' marginheight='0' frameborder='no' border='0' noresize>
		</frameset>
		</HTML>";
}
else {
	include("login/login-form.php");
}
?>
