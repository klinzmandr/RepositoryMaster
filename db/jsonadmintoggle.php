<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();

// AJAX server app to set/clear session admin variable
unset($_SESSION['adm']);
$fp = $_SESSION['homepath'] . 'db/userlist.txt';
$lp = $_SESSION['homepath'] . 'db/logger.incl.php';
require_once $lp;
//$fp = 'userlist.txt';
$f = file_get_contents($fp);
$pw = 'admpw:' . $_REQUEST['pw'];
$user = $_SESSION['id'];
if ($pw == "admpw:admOff") {
  unset($_SESION['adm']);
  logger("Admin mode off: $user");
  echo "OKOff";
  }
elseif (preg_match("/$pw/", $f)) {
	$_SESSION['adm'] = "ON";
	logger("Admin mode: $user"); 
	echo "OK";
	}
else { 
  echo "FAIL";
	unset($_SESSION['adm']);
	}

?>