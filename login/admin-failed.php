<!-- 
// #################################################################################
// #################################################################################
// Voseq login/admin-failed.php
// Copyright (c) 2006, PHPSense.com
// All rights reserved.
//
// Modified by Carlos Peña & Tobias Malm
// Mods: modified from acces-denied.php
//
// Script overview: Print admin-failed page
// #################################################################################
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
//check login session
include'auth.php';
//includes
include'../conf.php';
include'redirect.html';
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Login Failed</title>
<link href="loginmodule.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>Login Failed </h1>
<p align="center">&nbsp;</p>
<h2 align="center" class="err">Your account does not have any administrative rights!<br /><br />

   <?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('../index.php');" title="This link takes you back to the homepage">Please use the database as a standard user</a><br/>
   <?php echo "<a href='" .$base_url . "/home.php'" ?> onclick="return redirect('../login/login-form.php');" title="This link takes you back to the login screen">Or go to login screen and log on as an administrator</a></h4>
</body>
</html>
