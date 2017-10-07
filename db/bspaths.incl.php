<?php
// establish the paths for the db files
$bsroot = $_SESSION['homeuri'];     // uri used since server is sending
//echo "include loaded<br>";
$bscsspath  = $bsroot . 'db/bootstrap.min.css';
$ficsspath  = $bsroot . 'db/fileinput.min.css';
$jqpath     = $bsroot . 'db/jquery.js'; 
$bsjspath   = $bsroot . 'db/bootstrap.min.js';
$fijspath   = $bsroot . 'db/fileinput.min.js';

// output to page
echo '
<link href="'.$bscsspath.'" rel="stylesheet" media="all" type="text/css" >
<link href="'.$ficsspath.'" rel="stylesheet" media="all" type="text/css" >
<script src="'.$jqpath.'"></script>
<script src="'.$bsjspath.'"></script>
<script src="'.$fijspath.'"></script>';

?>