<?php
session_start();
$index = isset($_SESSION['homeuri']) ? ($_SESSION['homeuri'] . 'index.php ') : '../index.php';
// echo "index: $index<br>";
$lotype = isset($_REQUEST['lo']) ? $_REQUEST['lo'] : 'to';
// echo "lo: $lotype<br>";

$loggerpath = isset($_SESSION['homepath']) ? ($_SESSION['homepath'] . 'db/logger.incl.php') : 'logger.incl.php';
// echo "loggerpath: $loggerpath<br>";
require_once $loggerpath;

// logout or timeout reset requested
if ($lotype == 'lo') { logger("Logged out");	}
else { logger("Timed out"); }

session_unset();
session_destroy();

?>
<!DOCTYPE html>
<html>
<head>
<title>Session Expired</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="10; URL='<?=$index?>' "/>

<link rel="stylesheet" href="bootstrap.min.css">
</head>

<body>
<div class="container">
  <h1>Session Expired</h1>
  <p>Your session has been terminated either by logging out or the session timer expired.</p>
  <p>Click the following button to log into the application.</p>
  <a class="btn btn-success" href="<?=$index?>">Restart application</a>
</div>
</body>
</html>