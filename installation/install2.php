<?php
// include
require_once ('functions.php');

function docheck() {
	$checksiteName = getParam( $_POST, 'siteName', '');
	$a = clean_string($checksiteName);
	$checksiteName = $a[0];

	$check_url = getParam( $_POST, 'url', '');

	$check_local_folder = getParam( $_POST, 'local_folder', '');

	if ( $checksiteName == '' || $check_url == '' || $check_local_folder == '' ) {
		echo "<html><head><title>Error</title><link rel=\"stylesheet\" href=\"install.css\" type=\"text/css\" /></head>";
		echo "<body><div class=\"error\"><h2><img src=\"warning.png\" alt=\"\" />You entered invalid or empty details, please try again</h2></div></body></html>";
		exit;
	}
	return array("siteName" => $checksiteName,
				 "url" => $check_url,
				 "local_folder" => $check_local_folder
			);
}

$variables = docheck();

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

</head>


<body>

<div id="wrapper">
	<div id="header">
		<div id="joomla"><img src="header_install.png" alt="Installation" /></div>
	</div>
</div>

<div id="ctr" align="center">
	<form action="install3.php" method="post" name="form" id="form" onsubmit="return check();">
	<div class="install">
		<div id="stepbar">
			<div class="step-off">
				Installation
			</div>
			<div class="step-off">
				step 1
			</div>
			<div class="step-on">
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

	  			step 2
	  		</div>
  			<div class="clr"></div>
  			<h1>Connect to database:</h1>
	  		<div class="install-text">
				This database needs an installation of the <a href="http://www.mysql.com/" class="external text" rel="nofollow" target="_blank">MySQL</a>
				database system:
				<ul>
					<li> <a href="http://www.mysql.com/" class="external text" rel="nofollow" target="_blank">MySQL</a> is the primary target for this database.  <a href="http://www.php.net/manual/en/mysql.installation.php" class="external text" rel="nofollow" target="_blank">How to compile PHP with MySQL support</a>.</li>
				</ul>

  				<p>MySQL settings:</p>
  			</div>
			<div class="install-form">
  				<div class="form-block">
  		 			<table class="content2" border="0">
  		  			<tr>
						<td colspan="2">
							<br />
							<label class="config-label"><b>Database host:</b></label>
							<div class="mw-help-field-container"> 
								<span class="mw-help-field-hint">help</span> 
								<span class="mw-help-field-data">
									<p>If your database server is on different server, enter the host name or IP address here.</p>
									<p>If you are using shared web hosting, your hosting provider should give you the correct host name in their documentation.</p>
									<p>If you are installing on a Windows server and using MySQL, using "localhost" may not work for the server name.
									   If it does not, try "127.0.0.1" for the local IP address.</p>
								</span> 
							</div> 
							<br />
							<input class="inputbox" type="text" name="database_host" value="localhost" size="30"/></td>
					</tr>
  		  			<tr>
						<td>
							<br />
							<label class="config-label"><b>Database name:</b></label>
							<div class="mw-help-field-container"> 
								<span class="mw-help-field-hint">help</span> 
								<span class="mw-help-field-data">
									<p>Choose a name that identifies your VoSeq installation. It should not contain spaces.</p>
									<p>If you are using shared web hosting, your hosting provider should give you a specific database name to use or let you 
										create databases via a control panel.</p>
								</span> 
							</div> 
							<br />
							<input class="inputbox" type="text" name="database_name" value="my_db" size="30" /></td>
					</tr>
  		  			<tr>
						<td>
							<br />
							<label class="config-label"><b>Prefix for tables:</b></label>
							<div class="mw-help-field-container"> 
								<span class="mw-help-field-hint">help</span> 
								<span class="mw-help-field-data">
									<p>Choose a prefix to be used for your database's tables. In this way you can host several installations of VoSeq in one
									   MySQL database by having different prefixes. This is particularly useful in comercial servers that provide MySQL databases 
									   by paying a fee.</p>
								</span> 
							</div> 
							<br />
							<input class="inputbox" type="text" name="prefix" value="voseq_" size="30" /></td>
					</tr>
					

					<tr>
						<td>
							<br />
							<label class="config-label"><b>Database username:</b></label>
							<div class="mw-help-field-container"> 
								<span class="mw-help-field-hint">help</span> 
								<span class="mw-help-field-data">
									<p>Enter the username that will be used to connect to the database during the installation process. This is not the username
									of the VoSeq account; this is the username for accessing your MySQL database.</p>
								</span> 
							</div> 
							<br />
							<input class="inputbox" type="text" name="database_username" value="root" size="30" /></td>
					</tr>
	
					<tr>
						<td>
							<br />
							<label class="config-label"><b>Database password:</b></label>
							<div class="mw-help-field-container"> 
								<span class="mw-help-field-hint">help</span> 
								<span class="mw-help-field-data">
									<p>Enter the password that will be used to connect to the database during the installation process. This is not the password
									for the VoSeq account; this is the password for accessing your MySQL database.</p>
								</span> 
							</div> 
							<br />
							<input class="inputbox" type="password" name="database_password" value="" size="30" /></td>
					</tr>
					
		  		 	</table>
  				</div>
			</div>
		</div>
		<div class="clr"></div>
	</div>
	<?php
		foreach($variables as $k => $v) {
			echo "<input type=\"hidden\" name=\"$k\" value=\"$v\" />\n";
		}
	?>
	</form>
</div>
<div class="clr"></div>
<div class="ctr">
	<a href="http://nymphalidae.utu.fi/cpena/VoSeq_docu.html" target="_blank">VoSeq</a></div>

</body>
</html>
