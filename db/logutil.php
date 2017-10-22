<?php 
error_reporting(E_ERROR | E_WARNING | E_PARSE);
date_default_timezone_set('America/Los_Angeles');

echo '<html>
<head>
<title>Log Utility</title>
<link href="bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="container">
<h1>Log Utility</h1>';

//**** Configuration variables
$logfilename = "uselog.txt";		// name of log file
$logfilenamematch = "uselog";	// string to id current and old logs

$advice = "DISABLED";
if (file_exists($logfilename)) $advice = "ENABLED";
echo "User Logging currently " . $advice . "<br>";
print<<<endPage
<div align="left">
<a class="btn btn-xs btn-primary" href="logutil.php?loggeron">Logger ON</a>&nbsp;&nbsp;
<a class="btn btn-xs btn-primary" href="logutil.php?loggeroff">Logger OFF</a>&nbsp;&nbsp;
<a class="btn btn-xs btn-primary" href="uselog.txt" download="uselog.csv">DOWNLOAD FILE</a>&nbsp;&nbsp;
<a class="btn btn-xs btn-warning" href="logutil.php">REFRESH</a>&nbsp;&nbsp;
<a class="btn btn-xs btn-danger" href="javascript:self.close();"><strong>DONE</strong></a>
</div>

endPage;

if (isset($_REQUEST['delete'])) {
	$file = (isset($_REQUEST['delete'])) ? $_REQUEST['delete'] : "";
	if (file_exists($file)) {
		unlink($file);
		}
	}

if (isset($_REQUEST['loggeroff'])) {
	if (file_exists($logfilename)) {
		$yrmodahms = date("YmdHis");
		list($fn,$fnx) = explode(".", $logfilename);
		$newname = $fn . $yrmodahms . ".txt";
		rename ($logfilename, $newname);
		}	
	}
if (isset($_REQUEST['loggeron'])) {
	echo "<br>";	
	if (file_exists('uselog.txt'))
		echo "Logging already on.<br>";
	else {
		$yrmodahms = date("m/d/Y;H:i:s");
		$hdr = "Date;Time;ExpTime;UserID;Action;Folder\n";
		file_put_contents($logfilename,$hdr);		
		$initmsg = "$yrmodahms;;;Log Initiated\n";
		file_put_contents($logfilename,$initmsg, FILE_APPEND);
		if (!chmod($logfilename, 0666)) echo "File mode error<br>";
		}
	}
	
if (isset($_REQUEST['logdisplay'])) {
  $filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '';
	$filetodisplay = (isset($_REQUEST['logdisplay'])) ? $_REQUEST['logdisplay'] : "userlog.txt";
  print <<<filterForm
  <form action="logutil.php" method="post">
  Filter: <input type="text" name="filter" value="$filter">
  <input type="hidden" name="logdisplay" value="$filetodisplay">
  <input type="submit" name="apply" value="Apply">
  </form>
filterForm;

// check file permissions and report if write not permitted
	$perms = fileperms($filetodisplay);
  $info = $perms & 02;    // check for global write permission
	
//	echo "Log display detected<br>";
	if (file_exists($filetodisplay)) {
//		echo "Read file and set up display page<br>";		
		$contents = file($filetodisplay);
		echo "Log file name: $filetodisplay<br>";		
    if ($info == 0) echo "File permissions problem.  Logging DISABLED.<br>";
		echo "<pre>";
		foreach ($contents as $l) {
		  if (strlen($filter)) {
		    if (preg_match("/$filter/i", $l)) echo "$l"; }
			else 
			 echo $l;
			}		
		echo "</pre>";
		}
	}
	
echo "<hr><h2>Existing Log files:</h2>";
$loglist = scandir(".");
$logfilenamematchsize = strlen($logfilenamematch);

//echo "logfilenamematch: $logfilenamematch<br>";
//echo "logfilenamematchsize: $logfilenamematchsize<br>"; 
foreach ($loglist as $f) {
//	if (!strncmp($f,"user",4)) {
//	if (!strncmp($logfilenamematch,$f,$logfilenamematchsize)) {
	if (preg_match("/^$logfilenamematch/i", $f)) {
			//echo "<br>filename: $f, logfilename: $logfilenamematch<br>";
			echo "<a href=\"logutil.php?logdisplay=$f\">View</a>&nbsp;&nbsp;";	
		if (($f == $logfilename)) {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$f<br>";			
			}
		else {
			echo "<a href=\"logutil.php?delete=$f\">Delete</a>&nbsp;&nbsp;" . $f . "<br>";	
			}
		}
	}

?>
</div>  <!-- container -->
</body></html>
