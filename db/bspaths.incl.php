<?php
// establish the paths for the db files
//echo "include loaded<br>";
$bscsspath  = 'db/bootstrap.min.css';
$ficsspath  = 'db/fileinput.min.css';
$jqpath     = 'db/jquery.js'; 
$bsjspath   = 'db/bootstrap.min.js';
$fijspath   = 'db/fileinput.min.js';

if (isset($_SESSION['homeuri'])) { // adjust if not root folder
  $bsroot = rtrim($_SESSION['homeuri'], 'index.php');
	$bscsspath = $bsroot . $bscsspath;
	$ficsspath = $bsroot . $ficsspath;
	$jqpath = $bsroot . $jqpath;
	$bsjspath = $bsroot . $bsjspath;
	$fijspath = $bsroot . $fijspath;
  }
// output to page
echo '
<link href="'.$bscsspath.'" rel="stylesheet" media="all" type="text/css" >
<link href="'.$ficsspath.'" rel="stylesheet" media="all" type="text/css" >
<script src="'.$jqpath.'"></script>
<script src="'.$bsjspath.'"></script>
<script src="'.$fijspath.'"></script>';

?>