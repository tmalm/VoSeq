<?php
// #################################################################################
// #################################################################################
// Voseq login/member-index.php
// Copyright (c) 2006, PHPSense.com
// All rights reserved.
// #################################################################################
	require_once'auth.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Member Index</title>
<link href="loginmodule.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>Welcome <?php echo $_SESSION['SESS_FIRST_NAME'];?></h1>
<a href="member-profile.php">My Profile</a> | <a href="logout.php">Logout</a>
<p>This is a password protected area only accessible to members. </p>
</body>
</html>
