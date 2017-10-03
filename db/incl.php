<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

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

// ============== mover() =====================
function mover() {
  if (isset($_REQUEST['copy'])) {
    $file = $_REQUEST['copy'];
    $new = getcwd() . '/' . $file . '(copy)';
    $old = getcwd() . '/' . $file;
    echo "<pre>old: $old\nnew: $new</pre>";
    if (file_exists($new)) {
      logger("File $old already exists");
      echo "<div class=\"ERR\"><h4 style=\"color: red; \"><b>Copy failed!</b><br>File copy already exists.</h4></div>";
      }
    else {
      if (copy($old, $new)) {
        logger("Copied $oldname to $newname");
        echo "<div class=\"ERR\"><h4 style=\"color: red; \">Copy request successful!</h4></div>";
        }
      else {
        logger("Copy of $oldname FAILED");
        echo "<div class=\"ERR\"><h4 style=\"color: red; \">Copy request NOT successful!</h4></div>";
        }
      }
    }
    
// do actual move
  if (isset($_REQUEST['mover'])) {
    //echo '<pre>REQUEST '; print_r($_REQUEST); echo '</pre>';
    $newname = $_REQUEST['dest'] . '/' . $_REQUEST['file'];
    $oldname = getcwd() . '/' . $_REQUEST['file'];
    //echo "<pre>oldname: $oldname\nnewname: $newname</pre>";
    if (($newname == $oldname) OR (file_exists($newname))) {
      logger("File $oldname already exists");
      echo "<div class=\"ERR\"><h4 style=\"color: red; \">Move failed! Destination file already exists.</h4></div>";
      }
    else {
      if (rename($oldname, $newname)) {
        logger("Renamed $oldname to $newname");
        echo "<div class=\"ERR\"><h4 style=\"color: red; \">Move request successful!</h4></div>";
        } 
      else {
        logger("Rename of $oldname FAILED");
        echo "<div class=\"ERR\"><h4 style=\"color: red; \">Move request NOT successful!</h4></div>";
        } 
      }
    }

// move requested    
  if (isset($_REQUEST['move'])) {
    $file = $_REQUEST['move'];
    $rootpath = $_SESSION['root'];		
	  //echo "rootpath: $rootpath<br>";
    $dirlist = `find $rootpath -type d -print`;
    $dirlistarray = array();
    $dirlistarray = preg_split("/\\n/",$dirlist,"-1",1);
    //echo '<pre>dirlist '; print_r($dirlist); echo '</pre>';
    //echo '<pre>dirs '; print_r($dirlistarray); echo '</pre>';
    foreach ($dirlistarray as $d) {
      if (preg_match("/db|\.git/i",$d)) continue;   // ignore these
      $finarray[] = $d;
      }
    sort($finarray);
    //echo '<pre>finarray '; print_r($finarray); echo '</pre>';
    $len = strlen($rootpath);
    //echo "len: $len<br>";
    echo 'Select destination folder: 
    <form action="index.php">
    <select onchange="javascript: this.form.submit();" name="dest">
    <option value=""></option>
    <option value="'.rtrim($rootpath,'/').'">HomeFolder</option>';
    foreach ($finarray as $d) {
      $dspname = substr($d, $len);
      if (!strlen($dspname)) continue;
      //echo "d: $d, dspname: $dspname<br>";
      echo '<option value="'.$d.'">'.$dspname.'</option>  '; 
      }
    echo '
    </select>
    <input type="hidden" name="file" value="'.$file.'">
    <input type="hidden" name="mover" value="Apply">
    </form>';
    }
  return;
  }

//=============== deller() ===================
function deller() {
	if (isset($_REQUEST['rename'])) {
		$old = $_REQUEST['oldname'];
		$new = $_REQUEST['newname'];
		if (file_exists($new)) {
		  echo "<div class=\"ERR\"><h4 style=\"color: red; \">
  		  Rename request failed. Name already exits</h4></div>";  
  		logger("Rename $old failed"); 
  		}
		else {
  		if (rename($old, $new)) {
  		  echo "<div class=\"ERR\"><h4 style=\"color: red; \">
  		  Rename request completed.</h4></div>";  
  			logger("Renamed $old to $new"); 
  			}
  		else {
  		  echo "<div class=\"ERR\"><h4 style=\"color: red; \">
  		  Rename request FAILED!<br>
  			New name provided already exists OR path name invalid
  		  </h4></div>";
  			logger("Rename of $old FAILED"); 
  			}
  		return;
    }
  }
    
	if (isset($_REQUEST['delete'])) {
		if ($_REQUEST['delete'] == 'file') {
			$fn = $_REQUEST['fname'];
			if (unlink($fn)) { 
			  echo "<div class=\"ERR\"><h4 style=\"color: red; \">Deleted file $fn.</h4></div>"; 
			  logger("Deleted file $fn");
			  }
			else {
			  echo "<div class=\"ERR\"><h4 style=\"color: red; \">Deleted file $fn. FAILED!</h4></div>";
			  logger("Deletion of file $fn FAILED."); }
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
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">Folder $dirname is not empty!</h4></div>"; 
			}
		return;
		}
	return;
	}
//=================== adder() ====================
function adder() {
	if (isset($_REQUEST['addfile'])) { 
		$_SESSION['addfile'] = TRUE;
print <<<formPage
<div class="form-group">
<form class="form-inline" role="form" action="index.php" method="post" enctype="multipart/form-data">
<b>Select file(s):</b><input type="file" name="files[]" class="file" id="file" multiple>
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
<form action="index.php" method="post"><b>Enter folder name:</b> 
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
    } // file count
  } // if file count > 0
} // close of add file
if (strlen($msg) > 0) echo "<div class=\"ERR\">$msg</div>"; 

	if ($_SESSION['addfolder'] == TRUE) {
	  unset($_SESSION['addfolder']);
		$folder = ($_REQUEST['folder']);
		echo "folder: $folder<br>";
		if (strlen($folder) == 0) {
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">ERROR: No folder name provided!</h4></div>";
			logger("Add folder: Folder name empty");	
			return; 
			}
		echo "folder: $folder<br>";
		if (is_dir($folder)) {
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">ERROR: Folder name $folder duplicated.</h4></div>";
			logger("Add folder failed. Duplicate name $folder"); 
			return; 
			}
		mkdir($folder);
		copy("./index.php", $folder . "/index.php");
		echo "<div class=\"ERR\"><h4 style=\"color: red; \">New folder $folder created.</h4></div>";
		$logmsg = "Created folder: " . $folder;
		logger($logmsg);
		return;
		} // close of add folder
	}  // close of function

//=========== lister() =================	
function lister($in) {				// input is the list of the current folder contents
	$root = $_SESSION['root'];
	$currdir = getcwd() . '/';
	// get curr URL from server
	$currpath = preg_replace("/(^.*\/)(index.*$)/","$1",$_SERVER['REQUEST_URI']);	
	$cwd = explode('/', $currdir);
	$dcnt = count($cwd) - 2;
	$dname = $cwd[$dcnt];
	$_SESSION['currdir'] = $dname;
	$lourl = (isset($_SESSION['homeuri'])) ? 
    $_SESSION['homeuri'] . "?logout" : "index.php?logout";
	$helppath = rtrim($_SESSION['homeuri'],'index.php') . 'db/repohelp.html';
	$archpath = rtrim($_SESSION['homeuri'],'index.php') . 'Archive';
	$indexurl = rtrim($_SESSION['homeuri'], 'index.php') . 'db/logutil.php';
	$utilurl =  rtrim($_SESSION['homeuri'], 'index.php') . 'db/useradmin.php';
	echo '<a class="btn btn-success btn-xs" href="'.$lourl.'">LOGOUT</a>&nbsp;&nbsp;';
	echo '<a class="btn btn-success btn-xs" href="'.$_SESSION['homeuri'].'\">Home Folder</a>&nbsp;&nbsp;';
	echo "<a class=\"btn btn-primary btn-xs\" href=\"$helppath\" target=\"_blank\">Help</a>&nbsp;&nbsp;";

// show additional buttons if admin mode has been enabled	
	if ($_SESSION['adm'] == "ON") { 	// show admin buttons
	  echo '<a class="btn btn-danger btn-xs" href="'.$archpath.'">Archive</a>&nbsp;&nbsp;';
		echo '<a class="btn btn-danger btn-xs" target="_blank" href="'.$utilurl.'?action=list">User Summary</a>&nbsp;&nbsp;';		
		echo '<a class="btn btn-danger btn-xs" target="_blank" href="'.$indexurl.'">Log Utility</a>&nbsp;&nbsp;';
		echo '<a class="btn btn-danger btn-xs" target="_blank" href="'.$utilurl.'?action=form \">User Admin</a>&nbsp;&nbsp;'; }
	else {
		echo '<a class="adm btn btn-danger btn-xs" href="#">Admin</a>&nbsp;&nbsp;';	}

//echo '<pre>Session '; print_r($_SESSION); echo '</pre>';
//echo '<pre>Server '; print_r($_SERVER); echo '</pre>';

// output links to any external sources defined
	echo '<h3>On-line resources</h3>
<b>Online Links: (opens in a new window)</b><ul>';
  if (count($GLOBALS['links'])) {
    foreach ($GLOBALS['links'] as $l) { echo $l . '<br>'; }
    }

// display name of current directory and admin mode buttons if enabled
	echo "</ul><h3> $dname Contents:</h3>";
	if (preg_match('/\/Archive\//', $_SERVER['REQUEST_URI'])) 
    echo "<div style=\"color: red; \"><b>Archive Mode Active</b></div>";
	if ($_SESSION['adm'] == "ON") { 	// show add folder & file links
		echo "<a class=\"btn btn-danger btn-xs\" href=\"index.php?addfolder=1\">Add folder</a>";
		echo "&nbsp;&nbsp;<a class=\"btn btn-danger btn-xs\" href=\"index.php?addfile=1\">Add file</a><br>";
		}

// list all directories in current folder		
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
    			echo "<a class=\"confirm\" href=\"index.php?move=$urlf\">Move</a>/";
  				echo "
  				<a class=\"confirm\" href=\"index.php?delete=dir&dname=$urlf\">Delete</a>/
  				<a href=\"#\" onclick=\"return getfld('$f')\">Rename</a></div>"; 
  				}
  			$dnurl = $currpath .  "$f";
  			// echo "dnurl: $dnurl<br>";
  			echo "<div class=\"col-sm-4\"><a href=\"$dnurl\">$f</a></div></div>"; 
  			}
  		}
    }

// list all the files in the current folder
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
				echo "
				<a class=\"confirm\" href=\"index.php?move=$newf\">Move/</a>";
				echo "<a href=\"index.php?copy=$newf\">Copy/</a>";
				echo "<a class=\"confirm\" href=\"index.php?delete=file&fname=$newf\">Delete/</a>";
				echo "<a href=\"#\" onclick=\"return getfld('$f')\">Rename</a></div>";
				}
				$fnurl = $currpath . "index.php?dsp=$f";
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
	print <<<scriptPart1
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
<form method="post" name="NameForm" action="index.php">
<input type="hidden" id="HF1" name="oldname" value="">
<input type="hidden" id="HF2" name="newname" value="">
<input type="hidden" name="rename" value="rename">
</form>
scriptPart1;
	}
	logger("Listed folder");
	return;
	}			// end function 'lister'
// =========== sechk() =======================================
function sechk($dur) {
	if (strlen($_REQUEST['uid']) != 0) { 
		// echo "session id length = 0<br>";
		unset($_SESSION['tk']);
		unset($_SESSION['adm']);
		}
	$todnow = time();
	if (isset($_SESSION['tk']) AND ($todnow >= $_SESSION['tk'])) {
		//echo '<pre>SERVER '; print_r($_SERVER); echo '</pre>';
		//echo '<pre>SESSION '; print_r($_SESSION); echo '</pre>';
		unset($_SESSION['tk']);
		unset($_SESSION['adm']);
		$msg = "Session has expired for " . $SESSION['uid'];
		logger($msg);
		if (strlen($_REQUEST['uid']) == 0) {
		  $lourl = $_SESSION['homeuri'];
		  //echo "lourl: $lourl<br>";
		  session_unset();
    	session_destroy();
			echo '<h2 style="color: red; ">Session has expired!</h2>
			<h3><a href="'.$lourl.'">Please login</a></h3>';
			exit; }
		}
	else {
		if (isset($_SESSION['tk'])) {			
			$_SESSION['tk'] = $todnow + $dur;
			}
		}

// admin mode request
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
		
// login request
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

?>