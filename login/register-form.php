<?php
// #################################################################################
// #################################################################################
// Voseq login/register-form.php
// Copyright (c) 2006, PHPSense.com
// All rights reserved.
//
// Modified by Carlos Peña & Tobias Malm
// Mods: added admin-user choice and include() output buffer
//
// Script overview: prints the register form
// #################################################################################
//check admin login session
include'auth-admin.php';
//includes

ob_start();//Hook output buffer - disallows web printing of file info...
include'../conf.php';
ob_end_clean();//Clear output buffer//includes
include'../functions.php';
include'../admin/adfunctions.php';
include'../admin/admarkup-functions.php';

error_reporting (E_ALL ^ E_NOTICE);

// to indicate this is an administrator page
$admin = true;

// process title
$title = $config_sitename;

// loginmodule
$loginmodule = true;

// print html headers
include_once'../includes/header.php';

// print navegation bar
admin_nav();

// header: send bugs to me message
admin_standardHeader($title, $intro_msg);
session_start();

if( isset($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR']) && count($_SESSION['ERRMSG_ARR']) >0 ) {
	echo '<ul  class="err">';
	foreach($_SESSION['ERRMSG_ARR'] as $msg) {
		echo '<li style="text-align:center">',$msg,'</li>'; 
	}
	echo '</ul>';
	unset($_SESSION['ERRMSG_ARR']);
}

?>
<form id="loginForm" name="loginForm" method="post" action="register-exec.php">
  <table width="300" border="0" align="center" cellpadding="2" cellspacing="0">
    <tr>
      <th>First Name </th>
      <td><input name="fname" type="text" class="textfield" id="fname" /></td>
    </tr>
    <tr>
      <th>Last Name </th>
      <td><input name="lname" type="text" class="textfield" id="lname" /></td>
    </tr>
    <tr>
      <th width="124">Login</th>
      <td width="168"><input name="login" type="text" class="textfield" id="login" /></td>
    </tr>
    <tr>
      <th>Password</th>
      <td><input name="password" type="password" class="textfield" id="password" /></td>
    </tr>
    <tr>
      <th>Confirm Password </th>
      <td><input name="cpassword" type="password" class="textfield" id="cpassword" /></td>
    </tr>
	<tr>
     <th>Select admin rights </th>
      <td><input name="admin" type="checkbox" value="1" id="admin" /></td>
    </tr> 
<br />
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="Submit" value="Register new user" /></td>
    </tr>
</table>
</form>
</div> <!-- end content -->

<!-- standard page footer begins -->
<?php
make_footer($date_timezone, $config_sitename, $version, $base_url, $p_);
?>

</body>
</html>
