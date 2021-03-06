<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
date_default_timezone_set('America/Los_Angeles');

// load current path and uri vars
$requri = rtrim($_SERVER['REQUEST_URI'], '/');
preg_match("/(.*)\/.*$/i", $requri, $matches);
$_SESSION['curruri'] = $matches[1] . '/';
$_SESSION['currpath'] = getcwd() . '/';

// use log writer to check if starting at root dir
$loggerpath = $_SESSION['homepath'] . 'db/logger.incl.php';
if (!file_exists($loggerpath)) {
  echo '<h3 style="color: red; "><br>
  Invalid access of repository.</h3>';
  exit;
  }  	

// the following code checks for and displays a file
if (isset($_REQUEST['dsp'])) {
  require_once $loggerpath;
  $viewerpath = $_SESSION['homepath'] . 'db/viewer.incl.php';	
  require_once $viewerpath;
  viewer();         // output of file to view MUST preceed ANY other output.
  }

echo '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>RepoMan 3.0</title>';
  		
$bspaths = $_SESSION['homepath'] . 'db/bspaths.incl.php';
require_once $bspaths;
echo '</head><body>';
require_once $loggerpath;
$inclpath = $_SESSION['homepath'] . 'db/incl.php';
require_once $inclpath;

//echo "<h1>Orgainzation Logo Here</h1>";
// for example:
echo '<img src="https://library.pacwilica.org/PWC_logo_only.jpg" border="0" alt="Logo"><br>';

// load array of external resources links (if any)
$links[] = '<a href="https://www.pacificwildlifecare.org/" target="_blank">PWC Home Page</a>';
$links[] = '<a href="https://apps.pacwilica.org/mbrquery" target="_blank">PWC Mbr Query</a>';
$links[] = '<a href="https://apps.pacwilica.org/charts" target="_blank">PWC Charts</a>';
$links[] = '<a href="https://www.pacificwildlifecare.org/events/" target="_blank">PWC Event Calendar</a>';

// show login form
if (!isset($_REQUEST['uid'])) {
  if (!isset($_SESSION['id'])) {   // but only if there is no current session
    require_once 'db/login.incl.php';
    exit;
    }
  }
  
if (!isset($_SESSION['id']) AND (isset($_REQUEST['uid']))) { 
  $uid = $_REQUEST['uid'];
  login($uid); }     

if (isset($_SESSION['id'])) {         // if valid user id
  mover();                            // do file/dir move/copy/
  adder();														// do file/dir add/upload
  deller();														// do file/dir rename/delete
  
  $contents = scandir(".");						// scan the current directory
  foreach ($contents as $c) { 
    if (!preg_match("/^db$|^\.|^index\..*|^Archive$|.*\.md$/i",$c)) {
      $l[] = $c;                      // create list of dirs and files
      } 
    }
  echo '<ul>';
  lister($l);	                      // and show them
  echo '</ul></body></html>';
  exit(0);
  }
unset($_SESSION['id']);	
$louri = $_SESSION['homeuri'] . 'index.php';
echo "	          						
<a class=\"btn btn-primary\" href=\"$louri\">Login to repository manager</a>";
?>
</body></html>
