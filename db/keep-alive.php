<?php

session_start();

$sv = $_SESSION['tracker'] . '+kick';
$_SESSION['tracker'] = $sv; 

exit;
?>