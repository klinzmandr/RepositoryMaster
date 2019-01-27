<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
//============= logger() =====================================	
function logger($status) {
// no logging if log file does not exist !!!
	$logpath = $_SESSION['homepath'] . 'db/uselog.txt';
	$info = 0;
	if (file_exists($logpath)) $perms = fileperms($logpath);
  $info = $perms & 02;    // check for global write permission
	if (file_exists($logpath) AND ($info > 0)) { 
    //echo "fileperm: $info";
		$TOD = date("m/d/y;H:i:s");
		$rcd =  "$TOD;$sessexp;".$_SESSION['id'].";$status;".$_SESSION['currdir']."\n";
		file_put_contents($logpath, $rcd, FILE_APPEND);
		}	
	return;
	}
?>