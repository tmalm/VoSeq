<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
// #################################################################################
// #################################################################################
// Voseq login/register-success.php
// Copyright (c) 2006, PHPSense.com
// All rights reserved.
//
// Modified by Carlos Peña & Tobias Malm
// Mods: inserted include() output buffer and admin-link
//
// Script overview: Print register success page
// #################################################################################
//check admin login session
include'auth-admin.php';
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-9" />
<title>Registration Successful</title>
<link href="loginmodule.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>Registration Successful</h1>
<?php
include '../conf.php';
include 'redirect.html';
		echo "<p><a href='" . $base_url . "/home.html'  onclick=\"return redirect('login-form.php');\" >Click here</a> to login to your account,</p>";
		echo "<p><a href='" . $base_url . "/home.html'  onclick=\"return redirect('../admin/admin.php');\" >or here</a> to continue the administrative work.</p>";
//<p><a href="login-form.php">Click here</a> to login to your account,</p>
//<p><a href="../admin/admin.php">or here</a> to continue the administrative work.</p>
?>
</body>
</html>
