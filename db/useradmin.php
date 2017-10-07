<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
$root = $_SESSION[homepath] . '/db';
//echo "root: $root<br>";

echo '
<html>
 <head>
  <title>User Administration</title>
  <link href="bootstrap.min.css" rel="stylesheet" media="screen">
 </head>
 <body>
';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// create report for users last visit
if ($action == 'list') {
	chdir($root);
//	echo "pwd: ". getcwd();
	if (!file_exists('uselog.txt')) {
		echo '<h3>No log file to process.</h3>';
		exit;
	}
	$contents = file("uselog.txt");
//	echo "<pre> contents "; print_r($contents); echo '</pre>';	
	$lastlogin = array();
	for ($i=0; $i < count($contents) ; $i++) {		
		$flds = explode(';' , $contents[$i]);
//		echo "<pre> flds "; print_r($flds); echo '</pre>';
		if (($flds[3] == '') OR ($flds[3] == 'UserID')) continue;
		// if ($flds[3] == 'UserID') continue;
		if (substr($flds[4],0,13) == 'Login Success') {
			$date = date('F d, Y', strtotime($flds[0]));
//		echo "flds: ".$flds[3]."<br>";
			$lastlogin[$flds[3]][id] = $flds[3];
			$lastlogin[$flds[3]][date] = $date . ' @ ' . $flds[1];
			$lastlogin[$flds[3]][count] +=1 ;
			}
		}
//	echo "<pre> lastlogin "; print_r($lastlogin); echo '</pre>';

	$ucnt = count($lastlogin);
	echo "
  <div class=\"container\">	
	<h2>Last Use Summary&nbsp;&nbsp;&nbsp;<a class=\"btn btn-xs btn-success\" href=\"javascript:self.close();\">CLOSE</a></h2>
	Total unique user ids logged: $ucnt<br>";		
	if ($ucnt == 0) {
		echo "No users noted in log file.<br>";
		echo "<br><a href=\"javascript:self.close();\">CLOSE</a>";
		exit(0);
		}
	asort($lastlogin);
	echo "<table class=\"table\">";
	echo "<tr><th>User ID</th><th>Date @ Time</th></tr>";	
	foreach ($lastlogin as $k) {
		echo "<tr><td width=\"30%\">$k[id]</td><td width=\"30%\">$k[date]</td><td>, count: $k[count]</td></tr>";
		}
	echo "</table>";
	echo "<br><a class=\"btn btn-xs btn-success\" href=\"javascript:self.close();\">CLOSE</a>
	</div>   <!-- container -->";
	exit(0);
	}

//output response
if ($action == 'upd') {
	chdir($root);
// write update back to file
	$content = $_REQUEST['content'];
	if (file_exists('userlist.txt')) file_put_contents("userlist.txt",$content);
	$action = 'form';
	}

if ($action == 'form') {
	chdir($root);
	if (file_exists('userlist.txt')) $contents = file_get_contents('userlist.txt');
	}
	print <<<pagePart1
<div class="container">
<h1>User List Maintenance&nbsp;&nbsp;<a class="btn btn-xs btn-success" href="javascript:self.close();">CLOSE</a></h1>
<p>Apply updates to the following text then submit.</p>
<p>Lines that begin with double slashes (&apos;//&apos;) as well as blank lines are ignored.</p>
<p>User ids are a minimum of 4 characters and are usually the users email address.</p>
<form action="useradmin.php" method="post">
<textarea name="content" rows="20" cols="50">$contents</textarea>
<input type="hidden" name="action" value="upd"><br>
<input type="submit" name="submit" value="submit">
</form>
</div>  <!-- container -->
<hr>

<!-- <a href="index.php">Cancel</a> -->
</body>
</html>

pagePart1;
exit;

?>