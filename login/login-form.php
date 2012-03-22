<?php
// #################################################################################
// #################################################################################
// Voseq login/login-form.php
// Copyright (c) 2006, PHPSense.com
// All rights reserved.
//
// Modified by Carlos Peña & Tobias Malm
// Mods: inserted output buffer
//
// Script overview: Print login form page
// #################################################################################
	//Start session
	if( session_id() == "" ) {
		session_start();
	}
	
	//Unset the variables stored in session
	unset($_SESSION['SESS_MEMBER_ID']);
	unset($_SESSION['SESS_FIRST_NAME']);
	unset($_SESSION['SESS_LAST_NAME']);
	unset($_SESSION['SESS_ADMIN']);

	ob_start(); //Hook output buffer - disallows web printing of file info...
	include("../conf.php");
	if( $mask_url == "true" ) {
		ob_end_clean();//Clear output buffer
	}
	else {
		ob_clean();
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login Form</title>
<?php
	echo "<link href=\"" . $base_url . "/login/loginmodule.css\" rel=\"stylesheet\" type=\"text/css\" />";
?>
</head>
<body>

<p>&nbsp;</p>
<form id="loginForm" name="loginForm" method="post" action="<?php echo "$base_url/login/login-exec.php"; ?>">
  <table width="300" border="0" align="center" cellpadding="2" cellspacing="0">
    <tr>
      <td width="112"><b>Login</b></td>
      <td width="188"><input name="login" type="text" class="textfield" id="login" /></td>
    </tr>
    <tr>
      <td><b>Password</b></td>
      <td><input name="password" type="password" class="textfield" id="password" /></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="Submit" value="Login" /></td>
    </tr>
  </table>
</form>
</body>
</html>
