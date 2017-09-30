<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
// display the requested file
// ================== viewer ==========================\
function viewer() {
// ref: https://www.cyberciti.biz/faq/php-redirect/
// NOTE: NO OTHER OUTPUT CAN BE CREATED BEFORE THIS CODE IS EXECUTED  
$file = '';
if (isset($_REQUEST['dsp'])) { 
  $file = $_REQUEST['dsp'];
  $metapath = (preg_replace("/(^.*\/)index.*$/","$1",$_SERVER['HTTP_REFERER'])) . $file;
  //$metapath = preg_replace("/(^.*)\/index.*$/","$1/$file",$_SERVER['HTTP_REFERER']);
  $rootpath = getcwd() . '/' . $file;
  //echo "rootpath: $rootpath<br>"; echo "metapath: $metapath<br>";
  if (file_exists($rootpath)) {
    logger("Viewed file: $file");
    //echo "<meta http-equiv=\"refresh\" content=\"0;URL='$metapath'>";
    header("Location: $metapath");
    exit;
    }
  else  {
    echo 'NO FILE FOUND OR INVALID FILE NAME<br><br>
    <a href="javascript: self.close()">CLOSE</a><br>';
    logger("Unable to view file: $file"); 
    exit; 
    }
  }
//echo '<pre>Server '; print_r($_SERVER); echo '</pre>';
//echo "End viewer processing<br>";
}

// anchor confirm script
$archflag = isset($_SESSION['arch']) ? 'true' : 'false';
print <<<htmlScript

<script>
$(document).ready( function() {
  $(".ERR").fadeOut(5000);
  $(".confirm").click(function() {
    var r=confirm("This action is irreversable.\\n\\n Confirm action by clicking OK: ");
    if (r == true) { 
      return true; }
    else { 
      return false;  }
  	});

  $(".adm").click(function() {
    var val = prompt("Please enter the Admin password.");
    if (val.length > 0) {
    // if confirm dialog is canceled it returns false
    	$("#AP").val(val);
    	$("#LIF").submit();
      return true;
    	}
  });
});
</script>

<form id="LIF" mode="get">
<input id="AP" type="hidden" name="apw" value="">
</form>

htmlScript;
// ============== archiver() =================
function archiver() {
  if (isset($_REQUEST['archive'])) {
		$cwd = getcwd() . '/';
		//echo "cwd: $cwd<br>";
		$l = strlen($cwd) - strlen($_SESSION['root']);
		//echo "l: $l<br>";
	  $relpath = ''.substr($cwd, -($l));		
		if ($l <= 0) {
	     $relpath = '';
	   }
		//echo "relpath: $relpath<br>"; 
    $old = $_SESSION['root'].$relpath.$_REQUEST['fname'];
		$new = $_SESSION['root'].'Archive/'.$relpath.$_REQUEST['fname'];
		//echo "old: $old<br>new: $new<br>";
		
		if (isset($_REQUEST['restore'])) {
      $new = preg_replace("/(^.*\/)Archive\/(.*$)/i","$1$2",$old);
  		//echo "Restore of old: $old<br>to new: $new<br>";
		  }

		if (rename($old, $new)) {  
		  echo "<div class=\"ERR\"><h4 style=\"color: red; \">Action Completed</h4></div>";
		  logger("Renamed $old"); }
		else {
		  echo "<div class=\"ERR\"><h4 style=\"color: red; \">Rename request FAILED!<br>
			New name already exists OR all/part of the path name invalid<br>
			NOTE: Check that the folder exists in the Archive.</h4></div>";
			logger("Rename of $old FAILED"); }

  //echo '<pre>Session '; print_r($_SESSION); echo '</pre>';
  //echo '<pre>Server '; print_r($_SERVER); echo '</pre>';
	}  
return;  
}
//=============== deller() ===================
function deller() {
	if (isset($_REQUEST['rename'])) {
		$old = $_REQUEST['oldname'];
		$new = $_REQUEST['newname'];
		if (rename($old, $new)) {  
//			echo "Renamed '$old' to '$new'<br>";
			logger("Renamed $old to $new"); }
		else {
		  echo "<div class=\"ERR\"><h4 style=\"color: red; \">
		  Rename request FAILED!<br>
			New name provided already exists OR path name invalid
		  </h4></div>";
			logger("Rename of $old FAILED"); }
		return;
	}	
	
	if (isset($_REQUEST['delete'])) {
		if ($_REQUEST['delete'] == 'file') {
			$fn = $_REQUEST['fname'];
			if (unlink($fn)) { 
			  echo "<div class=\"ERR\"><h4 style=\"color: red; \">Deleted file $fn.</h4></div>"; 
			  logger("Deleted file $fn");
			  }
			else {
			  echo "<div class=\"ERR\"><h4 style=\"color: red; \">Deleted file $fn. FAILED!</h4></div>"; }
			  logger("Deletion of file $fn FAILED.");
			}
		if ($_REQUEST['delete'] == 'dir') {
			$dirname = urldecode($_REQUEST['dname']);
			$dn = "'" .  $dirname . "'";
			$dircontents = scandir($dirname);
			if (count($dircontents) <= 3) { 
				exec("rm -r $dn",$op,$retval);
				logger("Deleted folder: $dn ");
				return;		
				} 
			//echo "Directory $dirname is not empty<br>";
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">Directory $dirname is not empty!</h4></div>"; 
			}
		return;
		}
	return;
	}
//======== adder() ==========================================
function adder() {
	if (isset($_REQUEST['addfile'])) { 
		$_SESSION['addfile'] = TRUE;
print <<<formPage
<div class="form-group">
<form class="form-inline" role="form" action="index.php" method="post" enctype="multipart/form-data">
<b>Select file(s):</b><input type="file" name="files[]" class="file" id="file" multiple>
<!-- <input type="submit" name="submit" value="Submit" /> -->
</form>
</div>   <!-- form-group -->
formPage;
		logger("Add file requested");
		return;
		}
// add folder form
	if (isset($_REQUEST['addfolder'])) { 
		$_SESSION['addfolder'] = TRUE;
print <<<folderPage
<form action="index.php" method="post">Enter folder name: 
<input autofocus size=25 type="text" name="folder" id="folder" />&nbsp;&nbsp;&nbsp;
<input type="submit" name="submit" value="Submit" />
</form>
folderPage;
		logger("Add folder requested");
		return;
		}
		
// store uploaded file as same as file name supplied
	if ($_SESSION['addfile'] == TRUE) {
		unset($_SESSION['addfile']);		
//echo "store and process uploaded files<br>";
$msg = "";     //initiate the progress message
if (count($_FILES)) {
//  echo '<pre> file '; print_r($_FILES); echo '</pre>';
  for ($i = 0; $i<count($_FILES["files"]["name"]); $i++) {
    $filen = $_FILES["files"]["name"][$i];
    if (file_exists($filen)) {
      $msg .= "<b>ERROR: </b>File $filen already exists.  Upload ignored!<br>";
      logger("<b>ERROR: </b>File $filen already exists.  Upload ignored!");
      continue;
      }
    if ($_FILES["files"]["error"][$i] > 0) {
    	$msg .= "Error " . $_FILES["files"]["error"][$i] . "on upload of $filen<br>";
    	logger("Error " . $_FILES["files"]["error"][$i] . "on upload of $filen");
    	continue;
    	}
  //  echo "i: $i<br>, name: " . $_FILES["files"]["name"][$i] . '<br>';
  //  echo "tmp_name: " . $_FILES["files"]["tmp_name"][$i] . "<br />";
  //  echo "Size: " . ($_FILES["files"]["size"][$i] / 1024) . " Kb<br>=====<br>";
   	if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $_FILES["files"]["name"][$i])) {
      $msg .= "File# ".($i+1)." ($filen) uploaded successfully<br>"; 	  
      logger("File# ".($i+1)." ($filen) uploaded successfully"); 	  
   	  }
    }
  } // close of add file
}
if (strlen($msg) > 0) echo "<div class=\"ERR\">$msg</div>"; 

	if ($_SESSION['addfolder'] == TRUE) {
		$folder = ($_REQUEST['folder']);
		if (strlen($_REQUEST['folder']) == 0) {
			unset($_SESSION['addfolder']); 
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">ERROR: No folder name provided!</h4></div>";
			logger("Add folder: Folder name empty");	return; }
		if (is_dir($_REQUEST['folder'])) {
			unset($_SESSION['addfolder']); echo "Duplicate name<br>"; return; }
		mkdir($_REQUEST['folder']);
		copy("./index.php", $_REQUEST['folder'] . "/index.php");
		$logmsg = "Created folder: " . $_REQUEST['folder'];
		logger($logmsg);
		unset($_SESSION['addfolder']);
		return;
		} // close of add folder
	}  // close of function

//=========== lister() =================	
function lister($in) {				// input is the list of the current folder contents
	$root = $_SESSION['root'];
	$currdir = getcwd() . '/';
	// set curr URL path
	$_SESSION['currpath'] = preg_replace("/(^.*\/)(index.*$)/","$1",$_SERVER['REQUEST_URI']);	
	$cwd = explode('/', $currdir);
	$dcnt = count($cwd) - 2;
	$dname = $cwd[$dcnt];
	$_SESSION['currdir'] = $dname;
	$lourl = (isset($_SESSION['homeuri'])) ? 
    $_SESSION['homeuri'] . "?logout" : "index.php?logout";
	$helppath = rtrim($_SESSION['homeuri'],'index.php') . 'db/repohelp.html';
	$archpath = rtrim($_SESSION['homeuri'],'index.php') . 'Archive';
	$indexurl = rtrim($_SESSION['homeuri'], '/index.php') . '/db/logutil.php';
	$utilurl =  rtrim($_SESSION['homeuri'], '/index.php') . '/db/useradmin.php';
	echo '<a class="btn btn-success btn-xs" href="'.$lourl.'">LOGOUT</a>&nbsp;&nbsp;';
	echo '<a class="btn btn-success btn-xs" href="'.$_SESSION['homeuri'].'\">Home Folder</a>&nbsp;&nbsp;';
	echo "<a class=\"btn btn-primary btn-xs\" href=\"$helppath\" target=\"_blank\">Help</a>&nbsp;&nbsp;";
	if ($_SESSION['adm'] == "ON") { 	// show admin buttons
	  echo "<a class=\"btn btn-danger btn-xs\" href=\"$archpath\">Archive</a>&nbsp;&nbsp;";
		echo "<a class=\"btn btn-danger btn-xs\" target=\"_blank\" href=\"".$utilurl."?action=list \">User Summary</a>&nbsp;&nbsp;";		
		echo "<a class=\"btn btn-danger btn-xs\" target=\"_blank\" href=\"$indexurl\">Log Utility</a>&nbsp;&nbsp;";
		echo "<a class=\"btn btn-danger btn-xs\" target=\"_blank\" href=\"".$utilurl."?action=form \">User Admin</a>&nbsp;&nbsp;
		";
		}
	else {
		echo "<a class=\"adm btn btn-danger btn-xs\" href=\"#\">Admin</a>&nbsp;&nbsp;";
		}

//echo '<pre>Session '; print_r($_SESSION); echo '</pre>';
//echo '<pre>Server '; print_r($_SERVER); echo '</pre>';

	echo '<h3>On-line resources</h3>
<b>Online Links: (opens in a new window)</b><ul>';
  if (count($GLOBALS['links'])) {
    foreach ($GLOBALS['links'] as $l) { echo $l . '<br>'; }
    }
	echo "</ul>
	<h3> $dname Contents:</h3>";
	if (preg_match('/\/Archive\//', $_SERVER['REQUEST_URI'])) 
    echo "<div style=\"color: red; \"><b>Archive Mode Active</b></div>";
	if ($_SESSION['adm'] == "ON") { 	// show add folder & file links
		echo "<a class=\"btn btn-danger btn-xs\" href=\"index.php?addfolder=1\">Add folder</a>";
		echo "&nbsp;&nbsp;<a class=\"btn btn-danger btn-xs\" href=\"index.php?addfile=1\">Add file</a><br>";
		}
		
	echo "<b><u>Folders:</u></b><br><ul>";
	// echo "currdir: $currdir, root: $root, dname: $dname<br>";
  echo '<div class="row"><div class="col-sm-3">';
 	if (($currdir == $root) OR ($dname == 'Archive')) {
	  // echo "root OR archive<br>"; 
	  }
  else {
    //echo "NOT root NOR archive<br>";
    echo '<a href="../index.php">Parent Folder</a>';
    }

  echo '</div></div>'; 
	if (count($in) > 0) {
  	foreach ($in as $f) {  
  		if (is_dir($f)) {
  			echo '<div class="row">';
  			if ($_SESSION['adm'] == 'ON') {
  				$urlf = urlencode($f);			
  				echo "<div class=\"col-sm-3\">";
  				if (preg_match("/Archive/", $_SESSION['currpath']))  
    				echo "<a class=\"confirm\" href=\"index.php?archive=dir&fname=$urlf&restore\">Restore</a>/";
    			else 
    				echo "<a class=\"confirm\" href=\"index.php?archive=dir&fname=$urlf\">Archive</a>/";
  				echo "
  				<a class=\"confirm\" href=\"index.php?delete=dir&dname=$urlf\">Delete</a>/
  				<a href=\"#\" onclick=\"return getfld('$f')\">Rename</a></div>"; 
  				}
  			$dnurl = $_SESSION['currpath'] .  "$f";
  			// echo "dnurl: $dnurl<br>";
  			echo "<div class=\"col-sm-4\"><a href=\"$dnurl\">$f</a></div></div>"; 
  			}
  		}
    }

	echo "</ul><br><b><u>Files:</u></b><ul>";
	if ($_SESSION['adm'] == 'ON') {
		echo "<div class=\"row\">
		<div class=\"col-sm-3\"><b><u>Actions</u></b></div>
		<div class=\"col-sm-5\"><b><u>Name</u></b></div>
		<div class=\"col-sm-4\"><b><u>Date Created</u></b></div>
		</div>"; }
	else {
		echo "<div class=\"row\">
		<div class=\"col-sm-6\"><b><u>Name</u></b></div>
		<div class=\"col-sm-4\"><b><u>Date Created</u></b></div></div>"; }
	if (count($in) > 0) {
	  //echo '<pre>files list '; print_r($in); echo '</pre>';
  	foreach ($in as $f) {
			//echo '<pre>filename: '; print_r($f); echo '</pre>';
  		if (is_file($f)) {
  			$ft = date('M d,Y H:i:s', filectime($f));
  			echo "<div class=\"row\">";
  		if ($_SESSION['adm'] == 'ON') {
				$newf = urlencode($f);
				echo "
				<div class=\"col-sm-3\">";
				if (preg_match("/Archive/", $_SESSION['currpath'])) 
				  echo "<a class=\"confirm\" href=\"index.php?archive=file&fname=$newf&restore\">Restore</a> / ";
				else 
				  echo "<a class=\"confirm\" href=\"index.php?archive=file&fname=$newf\">Archive</a> / ";
				echo "
				<a class=\"confirm\" href=\"index.php?delete=file&fname=$newf\">Delete</a> /
				<a href=\"#\" onclick=\"return getfld('$f')\">Rename</a></div>";
				}
				$fnurl = $_SESSION['currpath'] .  "index.php?dsp=$f";
				//echo "fnurl: $fnurl<br>";
  		  echo "
  			<div class=\"col-sm-5\">
  			<a href=\"$fnurl\" target=\"_blank\">$f</a></div>
  			<div class=\"col-sm-4\">$ft</div></div>"; 
  			}
  		}		// end foreach for files
    }   // end if
	echo "</ul><br>";
	
// form for admin load rename form and script
// =========== rename js function and form =================
	if ($_SESSION['adm'] == 'ON') {
	echo '		
<script>
function getfld(OName) {
var inval = OName;
//	if prompt dialog is canceled it exits the script
var val = prompt("Please enter a NEW name (including the file extension if needed):",inval);
if (val.length > 0) {
		document.getElementById("HF1").value = inval;
		document.getElementById("HF2").value = val;
		document.forms["NameForm"].submit();
  	return true;
	}
alert("Rename action cancelled");
return false;
}
</script>

<!-- define form to submit WITHOUT a submit field defined -->
<form method="post" name="NameForm">
<input type="hidden" id="HF1" name="oldname" value="">
<input type="hidden" id="HF2" name="newname" value="">
<input type="hidden" name="rename" value="rename">
</form>';
	}
	logger("Listed folder");
	return;
	}			// end function 'lister'
//=========== sechk() =======================================
function sechk($dur) {
	if (strlen($_REQUEST['uid']) != 0) { 
		// echo "session id length = 0<br>";
		unset($_SESSION['tk']);
		unset($_SESSION['adm']);
		}
	$todnow = time();
	if (isset($_SESSION['tk']) AND ($todnow >= $_SESSION['tk'])) {
		unset($_SESSION['tk']);
		unset($_SESSION['adm']);
		$msg = "Session has expired for " . $SESSION['uid'];
		logger($msg);
		if (strlen($_REQUEST['uid']) == 0) 
			echo "<h2>Please login</h2>";
		}
	else {
		if (isset($_SESSION['tk'])) {			
			$_SESSION['tk'] = $todnow + $dur;
			}
		}
// admin button clicked
	if (isset($_REQUEST['apw'])) {
		$fp = $_SESSION['root'] . 'db/userlist.txt';
		$f = file_get_contents($fp);
		$pw = 'admpw:' . $_REQUEST['apw'];
//		echo "pw: $pw<br>";
		if (preg_match("/$pw/", $f)) {
			$_SESSION['adm'] = "ON"; 
			logger("Admin Mode Enabled");
//			echo "Admin Mode Enabled<br>";
			}
		else { 
			logger("Admin Mode request failed.");
			unset($_SESSION['adm']);
			}
		}
		
// login requested
  $haystack = array();
	if ($_REQUEST['submit'] == "Login") {
		$needle = $_REQUEST['uid'];
	  unset($_SESSION['id']);
		$usrfile = 'db/userlist.txt'; 				
		if (file_exists($usrfile)) { 
		  $haystack = file($usrfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); }
		else { $haystack[] = $needle; }     // allow anyone if no userfile exists
 
  	if ((in_array($needle, $haystack)) OR (in_array("anyth1ng.goez", $haystack))) {
  		$_SESSION['id'] = $_REQUEST['uid']; 
  		logger("Login Successful");
  		$_SESSION['tk'] = time() + $dur;		// session time in seconds
  		$_SESSION['root'] = getcwd() . '/';	// set root dir for utility session
  		$_SESSION['homeuri'] = $_SERVER['REQUEST_URI'];	// set home URL for session
  		}			
		else { 
			unset($_SESSION['tk']);
			unset($_SESSION['root']);
			unset($_SESSION['id']);
			$u = $_REQUEST['uid'];
			logger("Login NOT successful for $u");
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">Invalid User ID.</h4></div>";
			}
		}
	if (!isset($_SESSION['tk'])) {
    require_once 'db/login.incl.php';
		exit(0);
		}
	}
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