<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>JSON Testing</title>
</head>
<body>
<?php
session_start();

echo '<pre>SESSION '; print_r($_SESSION); echo '</pre>';

$_SESSION['flag'] = "xxx";

//echo "session flag: " . print_r($_SESSION['flag']);

?>

<script src="jquery.js"></script>
<script>
$(document).ready(function(){
  $("#2").click(function() {
    //alert("button 2 clicked");
    window.location.reload();
    });
  $("#1").click(function() {
    var val = prompt("Please enter the Admin password.");
    if (!val) return false;
    if (val.length == 0) val = "x";
      $.post("jsonadmin.php", { name: "admpw", pw: val },
        function(data, status) {
          if (data != "FAIL") {
            // alert("OK Data: " + data + "\nStatus: " + status);
            window.location.reload(); 
            } 
          else { 
            // alert("FAIL Data: " + data + "\nStatus: " + status);
            alert("Invalid password entered");
            window.location.reload(); 
            }
        });
  });
});
</script>

<h3>Test script #4</h3>
<p>Test put method that sends and returns data to be used in page.</p>
<p>This also sets a session variable and asks the reponse to return its value to check if the session carries through the AJAX call page.  It seems to based on this script.</p>
<div id="div1"><h2>Let jQuery AJAX Change This Text</h2>

<button id="1">Enter password</button><br>
<button id="2">Refresh to show session vars</button>
</div>
</body>
</html>