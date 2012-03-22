<?php
/**
 * Template used when there is a fresh install and the conf.php file needs to be created
 *
 * @file
 */

if( !isset($version) ) {
	if( file_exists("../changelog.txt") ) {
		$changelog = file_get_contents("../changelog.txt");
		preg_match_all("/version \d+\.\d+.\d+/i", $changelog, $matches);
		$version = $matches[0][0];
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
	<head>
		<title>VoSeq <?php if( isset($version) ) { echo htmlspecialchars($version); } ?></title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<style type='text/css' media='screen'>
			html, body {
				color: #000;
				background-color: #fff;
				font-family: sans-serif;
				text-align: center;
			}

			h1 {
				font-size: 30px;
				color: blue;
			}
			h2 {
				font-size: 20px;
				color: blue;
			}
			h3 {
				font-size: 15px;
				color: black;
			}

		</style>
	</head>
	<body>
		<img src="../images/logo.gif" alt='The database logo' />

		<h1>VoSeq</h1>
		<h2>a database to store voucher and sequence data</h2>
		<h3><?php echo htmlspecialchars( $version ) ?></h3>
		<div class='error'>
		<p>Configuration file: "conf.php" not found.</p>
		<p>
			Please <a href="index.php">install the software</a> first.
		</p>
		<p>
			Also, so can read the <a href="http://nymphalidae.utu.fi/cpena/VoSeq_docu.html">documentation here</a>.
		</p>
		<p>
			You can find a test installation with sample data <a href="http://nymphalidae.net/VoSeq">here</a>.
		</p>

		</div>
	</body>
</html>
