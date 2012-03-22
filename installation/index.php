<?php
/**
 * Installation files to create conf.php file and MySQL database
 *
 * @file
 */

// Check for PHP 5
if ( !function_exists("version_compare") || version_compare( phpversion(), "5.0.0") < 0 ) {
	// Using PHP4
	$php_version = "4";
}
else {
	// Using PHP5
	$php_version = "5";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>VoSeq - Web Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../favicon.ico" />
<link rel="stylesheet" href="install.css" type="text/css" />
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="config.js"></script>

<script  type="text/javascript">
<!--
function check() {
	// form validation check
	var formValid=false;
	var f = document.form;
	if ( f.DBname.value == '' ) {
		alert('Please enter a Name for your new Database');
		f.DBname.focus();
		formValid=false;
	} else if ( f.siteName.value == '' ) {
		alert('You must enter a name for your domain account.');
		f.siteName.focus();
		formValid=false;
	} else if ( confirm('Are you sure these settings are correct? \nThe system will now attempt to populate a Database with the settings you have supplied')) {
		formValid=true;
	}

	return formValid;
}
//-->
</script>
</head>

<body onload="document.form.DBname.focus();">

<div id="wrapper">
	<div id="header">
		<div id="joomla"><img src="header_install.png" alt="VoSeq Installation" /></div>
	</div>
</div>

<div id="ctr" align="center">
	<form action="install2.php" method="post" name="form" id="form" onsubmit="return check();">
	<div class="install">
		<div id="stepbar">
			<div class="step-off">
				Installation
			</div>
			<div class="step-on">
				step 1
			</div>
			<div class="step-off">
				step 2
			</div>
			<div class="step-off">
				step 3
			</div>
			<div class="step-off">
				step 4
			</div>
			<div class="step-off">
				step 5
			</div>
		</div>
		<div id="right">
			<div class="far-right">
				<input class="button" type="submit" name="next" value="Next >>"/>
  			</div>
	  		<div id="step">
				step 1
	  		</div>
  			<div class="clr"></div>
  			<h1>Fill in the following fields:</h1>
	  		<div class="install-text">
  				<p>Type in the name for your VoSeq instalation. This name is used in email messages so make it something meaningful.</p>
  			</div>
			<div class="install-form">
  				<div class="form-block">
  		 			<table class="content2">
  		  			<tr>
						<td>
							<br />
							<b>Site name:</b>
							<br />
							<input class="inputbox" type="text" name="siteName" size="30" value="" />
						</td>
						<td>
							<i>e.g. mybutterflies</i>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<br />
							<label class="config-label"><b>Enter the absolute folder path:</b></label>
							<div class="mw-help-field-container">
								<span class="mw-help-field-hint">help</span>
								<span class="mw-help-field-data">
									<p>Enter the absolute URL for your site. Leave it if used as stand-alone system. For example: 
										<b>http://localhost/mybutterflies</b>
									</p>
								</span>
							</div>
							<br />
							<input class="inputbox" type="text" name="url" value="<?php 
																					$path_folder = trim($_SERVER['HTTP_REFERER']);
																					$path_folder = preg_replace('/\/?\w+\.php\/?$/i', '', $path_folder);
																					echo preg_replace('/\/?installation\/?$/i', '', $path_folder);
																				   ?>" size="40" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<br />
							<label class="config-label"><b>Enter the path in your server's hard-disk:</b></label>
							<div class="mw-help-field-container">
								<span class="mw-help-field-hint">help</span>
								<span class="mw-help-field-data">
								<?php
									if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
										echo "<p>Enter the absolute path in your server or local computer's hard-disk. For example:
											<b>C:\Program Files\Apache Software Foundation\Apache2.2\htdocs\mysystem</b>
										      </p>";
									}
									else {
										echo "<p>Enter the absolute path in your server or local computer's hard-disk. For example: 
										<b>/home/bobsmith/mysystem</b>
										</p>";
									}
								?>
								</span>
							</div>
							<br />
							<?php
								$pwd = substr(dirname(__FILE__), 0, -13);
								echo "<input class=\"inputbox\" type=\"text\" name=\"local_folder\" value=\"$pwd\" size=\"40\" />";
							?>
						</td>
					</tr>
		  		 	</table>
  				</div>
			</div>
		</div>
		<div class="clr"></div>
	</div>
	</form>
</div>
<div class="clr"></div>
<div class="ctr">
	<a href="http://nymphalidae.utu.fi/cpena/VoSeq_docu.html" target="_blank">VoSeq</a></div>
</body>
</html>
