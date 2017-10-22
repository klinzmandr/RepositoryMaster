<?php
error_reporting(E_ERROR);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
$jsonpath = $_SESSION['homeuri'] . 'db/jsonadmintoggle.php';
$admode      = isset($_SESSION['adm']) ? 1 : 0;
          
print <<<htmlScript
<script>
  var admode = $admode;
$(document).ready( function() {
  if (!admode) { $(".admbtn").hide(); }
  $(".ERR").fadeOut(5000);
  $(".confirm").click(function() {
    var r=confirm("This action is irreversable.\\n\\n Confirm action by clicking OK: ");
    return r;    // OK = true, Cancel = false
  	});
  	
// admin req via AJAX
  $("#adm").click(function() {
    var val = "admOff";
    if (!admode) {
      var val = prompt("Please enter the Admin password.");
      if (!val) return false;
      }
    $.post("$jsonpath", { name: "admpw", pw: val },
      function(data, status) {
        if (data == "OKOff") {
          // alert("OK - Data: " + data + ", Status: " + status);
          $(".admbtn").hide();
          $("#reload").submit();
          } 
        else if (data == "OK") {
          // alert("OK - Data: " + data + ", Status: " + status);
          $(".admbtn").show();
          $("#reload").submit();
          } 
        else {
          //alert("FAIL - Data: " + data + ", Status: " + status);
          alert("Invalid password entered");
          // $("#reload").submit();
          }
        });
  });  
});
</script>

<form id="reload" method="post">
<input type="hidden" name="xyz" value="">
</form>

<form id="LIF" method="post">
<input id="AP" type="hidden" name="apw" value="">
</form>

htmlScript;

// ============== mover() =====================
function mover() {
  if (isset($_REQUEST['copy'])) {
    $file = $_REQUEST['copy'];
    $new = getcwd() . '/' . $file . '(copy)';
    $old = getcwd() . '/' . $file;
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
    $newname = $_REQUEST['dest'] . '/' . $_REQUEST['file'];
    $oldname = $_SESSION['currpath'] . $_REQUEST['file'];
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
    $rootpath = $_SESSION['homepath'];		
	  //echo "rootpath: $rootpath<br>";
    $dirlist = `find $rootpath -type d -print`;   // get list of ALL folders
    $dirlistarray = array();
    $dirlistarray = preg_split("/\\n/",$dirlist,"-1",1);
    foreach ($dirlistarray as $d) {
      if (preg_match("/db|\.git/i",$d)) continue;   // ignore these
      $finarray[] = $d;
      }
    sort($finarray);
    $len = strlen($rootpath);
    echo 'Select destination folder: 
    <form action="index.php">
    <select onchange="javascript: this.form.submit();" name="dest">
    <option value=""></option>
    <option value="'.rtrim($rootpath,'/').'">HomeFolder</option>';
    foreach ($finarray as $d) {
      $dspname = substr($d, $len);
      if (!strlen($dspname)) continue;
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
			$dn = $_SESSION['currpath'] .  $dirname;
			$dircontents = scandir($dn);
			if (count($dircontents) <= 3) {
			  unlink($dn . "/index.php");
			  if (rmdir($dn)) {
	   			logger("Deleted folder: $dirname");
  				echo "<div class=\"ERR\"><h4 style=\"color: red; \">Folder deleted: $dirname</h4></div>";
			   }
			  else {
	   			logger("Delete folder FAIL: $dirname ");
  				echo "<div class=\"ERR\"><h4 style=\"color: red; \">Folder delete FAIL: $dirname</h4></div>";
			   } 
				return;		
				}
			logger("Delete Failed: folder $dirname not empty");
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">Folder $dirname is not empty!</h4></div>"; 
			}
		return;			 
		}
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
		
// store uploaded file as same as file name supplied
	if ($_SESSION['addfile'] == TRUE) {
		unset($_SESSION['addfile']);		
//echo "store and process uploaded files<br>";
$msg = "";     //initiate the progress message
if (count($_FILES)) {
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

// ================== addfolder request ==========================
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

// ============== addfolder processed ============================
	if ($_SESSION['addfolder'] == TRUE) {
	  unset($_SESSION['addfolder']);
		$folder = ($_REQUEST['folder']);
		if (strlen($folder) == 0) {
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">ERROR: No folder name provided!</h4></div>";
			logger("Add folder: Folder name empty");	
			return; 
			}
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

//========================== lister() =========================	
function lister($in) {				// input is the list of the current folder contents
	$currpath = $_SESSION['currpath'];	
	$cwd = explode('/', $currpath);
	$dcnt = count($cwd) - 2;
	$dname = $cwd[$dcnt];
	$_SESSION['currdir'] = $dname;
	$lourl = $_SESSION['homeuri'] . '?logout';
	$helppath = $_SESSION['homeuri'] . 'db/repohelp.html';
	$archpath = $_SESSION['homeuri'] . 'Archive/index.php';
	$indexurl = $_SESSION['homeuri'] . 'db/logutil.php';
	$utilurl =  $_SESSION['homeuri'] . 'db/useradmin.php';
	$homeurl = $_SESSION['homeuri'] . 'index.php';
	echo '
	<a class="btn btn-success btn-xs" href="'.$lourl.'">LOGOUT</a>&nbsp;&nbsp;
	<a class="btn btn-success btn-xs" href="'.$homeurl.'">Home Folder</a>&nbsp;&nbsp;
	<a class="btn btn-primary btn-xs" href="'.$helppath.'" target="_blank">Help</a>&nbsp;&nbsp;';

	echo '<a id="adm" class="btn btn-danger btn-xs" href="#">Admin</a>&nbsp;&nbsp;';	
		
// show if in Archive folder	
  if (is_dir($_SESSION['homepath'].'Archive'))
    echo '<a class="admbtn btn btn-warning btn-xs" href="'.$archpath.'">Archive</a>&nbsp;&nbsp;';

// show admbtn buttons    
	echo '
	<a class="admbtn btn btn-danger btn-xs" target="_blank" href="'.$utilurl.'?action=list">User Summary</a>&nbsp;&nbsp;	
	<a class="admbtn btn btn-danger btn-xs" target="_blank" href="'.$indexurl.'">Log Utility</a>&nbsp;&nbsp;
	<a class="admbtn btn btn-danger btn-xs" target="_blank" href="'.$utilurl.'?action=form">User Admin</a>&nbsp;&nbsp;'; 

// output links to any external sources defined
  global $links;
  if (count($links)) {
  	echo '<h3>On-line resources</h3>
  <b>Online Links: (opens in a new window)</b><ul>';
    if (count($links)) {
      foreach ($links as $l) { echo $l . '<br>'; }
      }
    echo '</ul>';
    }

// display name of current directory and add folder/file buttons if admbtn
	echo '
	<h3> '.$dname.' Contents:</h3>
  <div class="admbtn">
  <div style="color: red; "><b>Archive Mode Active</b></div>
  <a class="btn btn-danger btn-xs" href="index.php?addfolder=1">Add folder</a>&nbsp;&nbsp;
  <a class="btn btn-danger btn-xs" href="index.php?addfile=1">Add file</a>&nbsp;&nbsp;
  </div>';

// list all FOLDERS in current folder		
	echo "<b><u>Folders:</u></b><br><ul>";
	// echo "currdir: $currdir, root: $root, dname: $dname<br>";
  echo '<div class="row"><div class="col-sm-3">';
 	if (($currpath == $_SESSION['homepath']) OR ($dname == 'Archive')) {
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
  				$urlf = urlencode($f);			
  				echo '<div class="admbtn col-sm-3">
    			<a href="index.php?move='.$urlf.'">Move/</a>
  				<a class="confirm" href="index.php?delete=dir&dname='.$urlf.'">Delete/</a>
  				<a href="#" onclick=\'return getfld("'.$f.'")\'>Rename</a></div>'; 
  			$dnurl = $_SESSION['curruri'] . "$f/index.php";
  			// echo "dnurl: $dnurl<br>";
  			echo '<div class="col-sm-4"><a href="'.$dnurl.'">'.$f.'</a></div></div>'; 
  			}
  		}
    }

// list all the FILES in the current folder
	echo '
  </ul><br><b><u>Files:</u></b><ul>
	<div class="row">
	<div class="admbtn col-sm-3"><b><u>Actions</u></b></div>
	<div class="col-sm-5"><b><u>Name</u></b></div>
	<div class="col-sm-4"><b><u>Date Created</u></b></div>
	</div>'; 

	if (count($in) > 0) {
  	foreach ($in as $f) {
  		if (is_file($f)) {
  			$ft = date('M d,Y H:i:s', filectime($f));
				$newf = urlencode($f);
				echo '
  			<div class="row">
				<div class="admbtn col-sm-3">
				<a href="index.php?move='.$newf.'">Move/</a>
				<a href="index.php?copy='.$newf.'">Copy/</a>
				<a class="confirm" href="index.php?delete=file&fname='.$newf.'">Delete/</a>
				<a href="#" onclick=\'return getfld("'.$f.'")\'>Rename</a></div>';
				$fnurl = $_SESSION['curruri'] . "index.php?dsp=$f";
				//echo "fnurl: $fnurl<br>";
  		  echo '
  		  <div class="col-sm-5">
  			<a href="'.$fnurl.'" target="_blank">'.$f.'</a></div>
  			<div class="col-sm-4">'.$ft.'</div></div>'; 
  			}
  		}		// end foreach for files
    }   // end if
	echo "</ul><br>";
	
// form for admin load rename form and script
// =========== rename js function and form =================
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
logger("Listed folder");
return;
}			// end function 'lister'

// =========== sechk() =======================================
function sechk($dur) {
	if (isset($_REQUEST['Login'])) {  
		// echo "session id length = 0<br>";
		unset($_SESSION['tk']);
		unset($_SESSION['adm']);
		}
	$todnow = time();
	if (isset($_SESSION['tk']) AND ($todnow >= $_SESSION['tk'])) {
		$msg = "Session has expired for " . $SESSION['uid'];
	  $lourl = $_SESSION['homeuri'] . 'index.php';
	  session_unset();
  	session_destroy();
	  logger($msg);
		echo '<h2 style="color: red; ">Session has expired!</h2>
		<h3><a href="'.$lourl.'">Please login</a></h3>';
		exit; 
		}
	else {
		if (isset($_SESSION['tk'])) {			
			$_SESSION['tk'] = $todnow + $dur;    // extend timer by duration 
			}
		}

// login request
  $haystack = array();
	if ($_REQUEST['submit'] == "Login") {
	  unset($_SESSION['id']);
		$needle = $_REQUEST['uid'];
		$usrfile = 'db/userlist.txt'; 				
		if (file_exists($usrfile)) { 
		  $haystack = file($usrfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); }
		else { $haystack[] = $needle; }     // allow anyone if no userfile exists
 
  	if ((in_array($needle, $haystack)) OR (in_array("anyth1ng.goez", $haystack))) {
  		$_SESSION['id'] = $needle; 
  		logger("Login Successful: $needle");
  		$_SESSION['tk'] = time() + $dur;		// session time in seconds
  		$requri = rtrim($_SERVER['REQUEST_URI'], '/');
  		preg_match("/(.*)\/.*$/i", $requri, $matches);
  		$_SESSION['homeuri'] = $matches[1] . '/';   // set home uri for session
  		$_SESSION['homepath'] = getcwd() . '/';	    // set home path for session
  		}			
		else { 
			session_unset();
    	session_destroy();
			logger("Login NOT successful for $needle");
			echo "<div class=\"ERR\"><h4 style=\"color: red; \">Invalid User ID.</h4></div>";
			}
		}
	if (!isset($_SESSION['tk'])) {
    require_once 'db/login.incl.php';
		exit(0);
		}
	}

?>