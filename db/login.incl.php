<?php
print <<<formPage

<script>
function validatekfld() {
// keystring?
ks = new String(document.kapform.uid.value);
if (ks.length < 4) {
   alert("Please enter valid user ID.");
   return false; }
   }

</script>
<form action="index.php" method="post" name="kapform" onsubmit="return validatekfld();">
Enter user ID:&nbsp;<input autofocus type="text" name="uid">&nbsp;
<input name="submit" type="submit" value="Login"></td></tr>
</form>

<h4 style="width: 400px">Use of this facility is restricted to authorized users of Pacific Wildlife Care, Morro Bay, CA. For information go to the <a href="http://www.pacificwildlifecare.org/">Pacific Wildlife Care web site</a> or send an email to info@pacificwildlifecare.org</h4>  
<br><br><a class="btn btn-xs btn-danger" href="index.php?logout">RESET SESSION</a>

</body>
formPage;

?>