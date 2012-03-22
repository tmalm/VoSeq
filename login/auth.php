<?php
// #################################################################################
// #################################################################################
// Voseq login/auth.php
// Copyright (c) 2006, PHPSense.com
// All rights reserved.
//
// Script overview: Checks for authenticated session
// #################################################################################
	// Check if there is any session going on
	if( session_id() == "" ) {
		//Start session
		session_start();
	}
	
	//Check whether the session variable SESS_MEMBER_ID is present or not
	if(!isset($_SESSION['SESS_MEMBER_ID']) || (trim($_SESSION['SESS_MEMBER_ID']) == '')) {
		header("location: home.php");
		header ('Content-Length: 0');
		exit();
	}
?>
