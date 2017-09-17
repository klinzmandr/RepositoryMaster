<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
date_default_timezone_set('America/Los_Angeles');

$bspaths = isset($_SESSION['root'])?
  $_SESSION['root'] . 'db/bspaths.incl.php' : './db/bspaths.incl.php';
require_once $bspaths;
require_once $inclpath;

viewer();         // output of file to view MUST preceed ANY other output.

echo '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>MBBF Board Repository</title>
</head><body>';

//echo "<h1>Orgainzation Logo Here</h1>";
// for example:
echo '<img src="http://morrobaybirdfestival.net/wp-content/uploads/2016/08/LOGO3.png" border="0" alt="MBBF logo"><br>';

// load array for external resources (if any)
$GLOBALS['links'][] = '<a href="http://google.com/" target="_blank">Google</a>';
$GLOBALS['links'][] = '<a href="http://yahoo.com/" target="_blank">Yahoo</a>';

// logout or session reset requested
if (isset($_REQUEST['logout'])) {
	logger("Logged out");	
	echo "<div class=\"ERR\"><h4 style=\"color: red; \">Logged out.</h4></div>";
	session_unset();
	session_destroy();
	// echo '<pre> session '; print_r($_SESSION); echo '</pre>';
	}

sechk(30*60);												// check and/or set session
archiver();                         // do archive request
adder();														// do file and/or dir add
deller();														// do file upload, rename and or dir delete
// viewer();                           // display the requested file

$contents = scandir(".");						// scan the current directory
foreach ($contents as $c) { 
  if (!preg_match("/^db{1}|^\.|^index\..*|^Archive$|.*\.md$/i",$c)) {
    $l[] = $c;                      // create list of dirs and files
    } 
  }
echo '<ul>';
lister($l);			          						// and show them

/*
echo "<hr>Debug Info: dump of array name and value pairs<br><pre>";
echo "Parameters: ";print_r($_REQUEST);
echo "Session: ";print_r($_SESSION);
echo "Server: ";print_r($_SERVER);
echo "</pre><hr>";
*/

echo '</ul></body></html>';
exit(0);
?>
