<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
// display the requested file
// ================== viewer ==========================\
function viewer() {
// ref: https://www.cyberciti.biz/faq/php-redirect/
// NOTE: NO OTHER OUTPUT CAN BE CREATED BEFORE THIS CODE IS EXECUTED  
$file = $_REQUEST['dsp'];
$metapath = $_SESSION['curruri'] . $file;
$rootpath = $_SESSION['currpath'] . $file;
//echo "rootpath: $rootpath<br>"; echo "metapath: $metapath<br>";
if (file_exists($rootpath)) {
  logger("Viewed file: $file");
  header("Location: $metapath");
  exit;
  }
else  {
  echo 'NO FILE FOUND OR INVALID FILE NAME<br><br>';
  echo "rootpath: $rootpath<br>"; echo "metapath: $metapath<br>";
  echo '<a href="javascript: self.close()">CLOSE</a><br>';
  logger("Unable to view file: $file"); 
  exit; 
  }
//echo '<pre>Server '; print_r($_SERVER); echo '</pre>';
}  //echo "End viewer processing<br>";

?>