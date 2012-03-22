#!/usr/local/bin/php
<?php
# thumbnail dimensions
define('THMBWIDTH', 200);
define('THMBHEIGHT', 200);

function strip_ext($name) {
	$ext = strrchr($name, '.');
	if($ext !== false)
		{
      $name = substr($name, 0, -strlen($ext));
      }
   return $name;
}

$list = 'list';
$all_lines = file($list);

$cwd = dirname(__FILE__);

foreach( $all_lines as $value )
	{
	$voucherImage = rtrim($value);
	$voucherImage_basename = strip_ext($voucherImage);
	
	echo "$voucherImage\n\n\n";
	$thumbnail = strtolower($voucherImage);
	$thumbnail = strip_ext($thumbnail) . "_tn.jpg";
	
	// check for pngs
	$mime = getimagesize($voucherImage);
	if( $mime[mime] != 'image/jpeg' )
		{
		echo "no es jpeg";
		exec( 'convert ' . $voucherImage . ' ' . $voucherImage_basename . '.jpg');
		$voucherImage = $voucherImage_basename . '.jpg';
		}
	
	/*** create thumbnails ***/
	// get image data
	$src = $voucherImage;
	list($width, $height, $type, $attr) = getimagesize($src);
	$source = imagecreatefromjpeg($src);

	$lowest = min(THMBWIDTH / $width, THMBHEIGHT / $height);
	if( $lowest < 1 )
		{
		// get new sizes
		$smallwidth = floor($lowest*$width);
		$smallheight = floor($lowest*$height);
		
		// create destination image
		$tmp = imagecreatetruecolor($smallwidth, $smallheight);

		// resize
		imagecopyresampled($tmp, $source, 0, 0, 0, 0, $smallwidth, $smallheight, $width, $height);
	
		// output
		imagejpeg($tmp, $thumbnail);
		}
	
	} 
/*


// to indicate this is an administrator page
$admin = true;

// check for record ID
if ((!isset($_GET['code']) || trim($_GET['code']) == ''))
	{
	die('Missing record ID!');
	}
else
	{
 	$a = "{$_FILES['userfile']['name']}";
	$b = trim($a);
	$b = strtolower($b);
	if ($b == '')
		{
		// process title
		$title = "NSG's db-Upload failed";

		// print html headers
		include_once('../includes/header.php');

		// print navegation bar
		nav();

		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		echo "<img src=\"../images/warning.png\" alt=\"\"> File <b>did not</b> upload.<br />
				Check the file size. File must be less than 2MB. Or maybe your filename is not correct.";
		echo "</td>";
		make_sidebar();
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer();
		}
	else
		{
		// check this name having .jpg extension
		$b = strip_ext($b) . ".jpg";
		$destination = $GLOBAL['local_folder'] . "/pictures/" . $b;
		if( file_exists($destination) )
			{
			$error = true; // another picture already exists with that name, replace then
			}
		else
			{ $error = false; }
		
		// process title
		$title = "NSG's db-Picture uploaded ";

		// print html headers
		include_once('../includes/header.php');

		// print navegation bar
		nav();

		// begin HTML page content
		echo "<div id=\"content_narrow\">";
		echo "<table border=\"0\" width=\"850px\"> <!-- super table -->
				<tr><td valign=\"top\">";
		
		$subject = $b;
		while( $error == true ) // rename file to avoid overwriting
 			{
			$subject = basename($subject, ".jpg");
			$pattern = '/_[0123456789]*$/';
			preg_match($pattern, $subject, $matches);
			if( $matches )
				{
				$count = strlen($matches[0]);
				$sufix = substr($matches[0], 1);
				$sufix = $sufix + 1;
				$sufix = "_" . $sufix;
			
				$subject = substr($subject, 0, -$count);
				$subject = $subject . $sufix . ".jpg";
				$destination = $GLOBAL['local_folder'] . "/pictures/" . $subject;
				}
			else
				{
				$sufix = "_1";
				$subject = $subject . $sufix . ".jpg";
				$destination = $GLOBAL['local_folder'] . "/pictures/" . $subject;
				}
			if( file_exists($destination) ) { $error = true; } else { $error = false; }
 			}
		$temp_file = $_FILES['userfile']['tmp_name'];
		move_uploaded_file($temp_file, $destination);
			
		// check for other files than jpeg format
		$file = $destination;
		$file_mime = getimagesize($file);
		if( $file_mime[mime] != 'image/jpeg' || $file_mime[mime] == 'image/png' || $file_mime[mime] == 'image/bmp' )
			{
			$temp_file = $file . '_tmp';
			exec('cp ' . $file . ' ' . $temp_file);
			$file_origen = escapeshellarg($temp_file);
			$file_target = escapeshellarg($file);
			exec('convert ' . $file_origen . ' ' . $file_target);
			unlink($temp_file);
			$subject = basename($file);
			}
					echo "$subject<br />";
		
		/*** create thumbnails ***/ /*
		$my_thumb = basename($subject, ".jpg");
		// prepare names to database
		$my_thumb_to_db = $my_thumb . "_tn.jpg";
		$my_filename_to_db = $subject;
		$my_thumb = "../pictures/thumbnails/" . $my_thumb . "_tn.jpg";

		// get image data
		$src = "../pictures/" . $subject;
		list($width, $height, $type, $attr) = getimagesize($src);
		$source = imagecreatefromjpeg($src);

		$lowest = min(THMBWIDTH / $width, THMBHEIGHT / $height);
		if( $lowest < 1 )
			{
			// get new sizes
			$smallwidth = floor($lowest*$width);
			$smallheight = floor($lowest*$height);
			
			// create destination image
			$tmp = imagecreatetruecolor($smallwidth, $smallheight);

			// resize
			imagecopyresampled($tmp, $source, 0, 0, 0, 0, $smallwidth, $smallheight, $width, $height);
	
			// output
			imagejpeg($tmp, $my_thumb);
			
			$thumb_created = true;
			}
		// prepare names to database
		if( $thumb_created )
			{
			$voucherImage = $my_filename_to_db;
			$thumbnail = $my_thumb_to_db;
			}
		else
			{
			$voucherImage = $my_filename_to_db;
			$thumbnail = $my_filename_to_db;
			}
			
		echo "<img src=\"images/success.png\" alt=\"\" /> File is valid and was sucessfuly uploaded.<br />" . $subject . " (" . $_FILES['userfile']['size'] . ")";
	
		// open database connection
		$connection = mysql_connect($host, $user, $pass) or die ('Unable to connect!');
		// select database
		mysql_select_db($db) or die ('Unable to select database');
		// generate and execute query
		$code = $_GET['code'];
		
		$query = "UPDATE vouchers SET voucherImage='$voucherImage', thumbnail='$thumbnail' WHERE code='$code'";
		$result = mysql_query($query) or die("Error in query: $query. " . mysql_error());
		
		// show links to add sequences for this same record
		?>
		<br /><br />
		Do you want to:
				<ol>
				<li>Enter sequences for record of code <b><?php echo "$code"; ?></b>: <a href="listseq.php?code=<?php echo "$code"; ?>">Add Sequences</a></li>
				<li><a href="admin.php">Go back to the main menu</a>.</li>
				</ol>
		<?php
		echo "</td>";
		make_sidebar();
		echo "</tr>
				</table> <!-- end super table -->
				</div> <!-- end content -->";
		make_footer();
			}
		}	
	*/
?>