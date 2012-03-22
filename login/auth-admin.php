<?php
// #################################################################################
// #################################################################################
// Voseq login/auth-admin.php
// Copyright (c) 2006, PHPSense.com
// All rights reserved.
//
// Modified by Carlos Peña & Tobias Malm
// Mods: modified from auth.php
//
// Script overview: Checks for authenticated admin-session
// #################################################################################
	//Start session
	session_start();
	
	//Check whether the session variable SESS_MEMBER_ID is present or not
	if(!isset($_SESSION['SESS_MEMBER_ID']) || (trim($_SESSION['SESS_MEMBER_ID']) == '')) {
		header("location: ../home.php");
		exit();
		}
	elseif( isset($_SESSION['SESS_ADMIN']) && $_SESSION['SESS_ADMIN'] == '0') {
		header("location: ../login/admin-failed.php");
		exit();
	
	}
?>
