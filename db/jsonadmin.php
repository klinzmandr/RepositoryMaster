<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();

// AJAX server app to set/clear session admin variable
unset($_SESSION['adm']);
//$fp = $_SESSION['homepath'] . 'db/userlist.txt';
$fp = 'userlist.txt';
$f = file_get_contents($fp);
$pw = 'admpw:' . $_REQUEST['pw'];
$user = $_SESSION['id'];
if (preg_match("/$pw/", $f)) {
	$_SESSION['adm'] = "ON"; 
	echo "pw: $pw OK";
	}
else { 
  echo "FAIL";
	unset($_SESSION['adm']);
	}

?>