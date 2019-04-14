<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title></title>
</head>
<body>

<h3>USAGE</h3>
<p>This file is to be used to update the index.php file in each directory of the repository.  This is necessary because the index.php file is copied into each directory as it is added by the repository manager.  So, any change to the index.php file itself requires that the new file be propagated into each existing directory of the repository.</p>

<p>Usually, this file will reside in the db sub-folder and copied into the root folder only when it is to be used.  It should then be deleted so it is not inadvertently used.</p>
<h4>Use only once and delete from root directory when done.  Also delete copy of index.php from the db directory.</h4>

<?php
if (preg_match("/db/", $_SERVER['SCRIPT_FILENAME'])) {
  echo "<h3>ERROR</h3>Script is NOT in the root directory.<br>";
  exit;
  }  

if (!isset($_REQUEST['cont'])) {
  echo '<a href="updater.php?cont"><b>CONTINUE</b></a>';
  exit;
  }

$results   = `find . -type d -exec cp index.php {} \; -print`;
//echo '<pre>RESULTS '; print_r($results); echo '</pre>';

$currdir = getcwd() . '/';
$root = rtrim($_SERVER['SCRIPT_FILENAME'], 'updater.php');
$scr = $_SERVER['SCRIPT_FILENAME'];
echo "<hr>RESULTS<br>Script running in: $root<br>";
echo "Current working dir: $currdir<br>";
echo "Script running: $scr<br><br>";
// echo '<pre>'; print_r($_SERVER); echo '</pre>';
echo '<b>Script has recursively copied the local copy of index.php into all listed directories.</b><br>
<ul><pre>
'.$results.'
</pre></ul>
<h3 style="color: red; ">Completion successful.</h3>';

?>
</body>
</html>