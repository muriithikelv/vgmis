<?php 
if(isset($error_message) and $error_message!=''){echo "<div class='error_response'>";htmlout("ERROR: $error_message");echo "</div>";$error_message='';}
elseif(isset($success_message) and $success_message!=''){echo "<div class='success_response'>";htmlout("$success_message");echo"</div>";$success_message='';}

?>