<?php
// #################################################################################
// #################################################################################
// Voseq login/admin-failed.php
// Copyright (c) 2006, PHPSense.com
// All rights reserved.
//
// Modified by Carlos Peña & Tobias Malm
// Mods: added admin-session, sha1 - encryption for passwords and include() buffer
//
// Script overview: runs the login info against database and sets new session
// #################################################################################
//Start session
session_start();

//Include database connection details
ob_start();//Hook output buffer - disallows web printing of file info...
require_once'../conf.php';
ob_end_clean();//Clear output buffer//includes


//Array to store validation errors
$errmsg_arr = array();

//Validation error flag
$errflag = false;

//Connect to mysql server
@$connection = mysql_connect($host, $user, $pass) or die('Unable to connect MySQL');

//Select database
mysql_select_db($db) or die('Unable to select database; <b>You might need to check your conf.php file </b>');
if( function_exists(mysql_set_charset) ) {
	mysql_set_charset("utf8");
}


//Function to sanitize values received from the form. Prevents SQL injection
function clean($str) {
	$str = @trim($str);
	if(get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	return mysql_real_escape_string($str);
}

//Sanitize the POST values
$login = clean($_POST['login']);
$password = clean($_POST['password']);

//Input Validations
if($login == '') {
	$errmsg_arr[] = 'Login ID missing';
	$errflag = true;
}
if($password == '') {
	$errmsg_arr[] = 'Password missing';
	$errflag = true;
}

//If there are input validations, redirect back to the login form
if($errflag) {
	$_SESSION['ERRMSG_ARR'] = $errmsg_arr;
	session_write_close();
	header("location: login-form.php");
	exit(0);
}

//Create query
$query = "SELECT * FROM ". $p_ . "members WHERE login='$login' AND passwd='".sha1($_POST['password'])."'";
$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());

//Check whether the query was successful or not
if( mysql_num_rows($result) == 1 ) {
	//Login Successful
	session_regenerate_id();
	$member = mysql_fetch_assoc($result);
	$_SESSION['SESS_MEMBER_ID'] = $member['member_id'];
	$_SESSION['SESS_FIRST_NAME'] = $member['firstname'];
	$_SESSION['SESS_LAST_NAME'] = $member['lastname'];
	$_SESSION['SESS_ADMIN'] = $member['admin'];
	session_write_close();
	header("location: ../index.php");
	exit(0);
}
else {
	//Login failed
	header("location: login-failed.php");
	exit(0);
}

?>
