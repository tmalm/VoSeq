<html>
<head>
</head>

<body>
<?php
function getParam( $arr, $name, $def=null ) {
	$return = null;
	if (isset( $arr[$name] ))
		{
		if (is_string( $arr[$name] ))
			{
			$arr[$name] = trim( $arr[$name] );
			$arr[$name] = strip_tags( $arr[$name] );
			}
		if (!get_magic_quotes_gpc())
			{
			$arr[$name] = addslashes( $arr[$name] );
			}
		return $arr[$name];
		}
	else
		{
		return $def;
		}
}

$_POST['siteName'] = '';
$a = getParam( $_POST, 'siteName', '' );
echo "aaa$a" . "aaa";

?>

</body>
</html>