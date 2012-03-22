<?php
// include
require_once ('functions.php');

function docheck() {
	$url = getParam( $_POST, 'url', '');
	$local_folder = getParam( $_POST, 'local_folder', '');
	
	return array("url" => $url, "local_folder" => $local_folder);
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
</head>

<body>

	
<div id="wrapper">
	<div id="header">
		<div id="joomla"><img src="header_install.png" alt="Installation" /></div>

	</div>
</div>
<div id="ctr" align="center">
	<div class="install">
		<div id="stepbar">
			<div class="step-off">
				Installation
			</div>
			<div class="step-off">
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
			<div class="step-on">
				step 5
			</div>
		</div>
		<div id="right">
			<div class="far-right">
				<input class="button" type="button" name="runSite" value="View Site" onClick="window.location.href='<?php echo $variables['url']; ?>' "/>
  			</div>
	  		<div id="step">

	  			step 5
	  		</div>
  			<div class="clr"></div>
			<?php
				if( isset($error) ) {
					if( count($error) > 0 ) {
						echo "<ul>";
						foreach($error as $item) {
							echo "<li><h3>". $item ."</h3></li>";
						}
						echo "</ul>";
					}
				}
			?>

  			<h1>Congratulations! Your VoSeq system has been created.</h1>
				<input class="button" type="button" name="runSite" value="View Site" onClick="window.location.href='<?php echo $variables['url']; ?>' "/>
			
	  		<div class="install-text">
 
			</div>
		</div>
		<div class="clr"></div>
	</div>
</div>
<div class="clr"></div>
<div class="ctr">
	<a href="http://nymphalidae.utu.fi/cpena/VoSeq_docu.html" target="_blank">VoSeq</a></div>

</body>
</html>
