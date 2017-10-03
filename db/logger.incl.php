<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
//============= logger() =====================================	
function logger($status) {
// no logging if log file does not exist !!!
	$logpath = $_SESSION['root'] . 'db/uselog.txt';
	if (file_exists($logpath)) { 
		$TOD = date("m/d/y;H:i:s");
		if (isset($_SESSION['tk'])) { $sessexp = date("H:i:s", $_SESSION['tk']); }
		$rcd =  "$TOD;$sessexp;".$_SESSION['id'].";$status;".$_SESSION['currdir']."\n";
		file_put_contents($logpath, $rcd, FILE_APPEND);
		}	
	return;
	}
?>