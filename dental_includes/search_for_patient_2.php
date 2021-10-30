<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
//this is for doing a patient search
//echo "a $_POST[token_search_patient]<br>
 //b $_SESSION[token_search_patient]";
if(isset($_POST['search_by']) and $_POST['search_by']!='' and isset($_POST['token_search_patient2']) and 
	isset($_SESSION['token_search_patient2']) and $_POST['token_search_patient2']==$_SESSION['token_search_patient2']){
	//$_SESSION['token_search_patient']='';
	$_SESSION['tplan_id']='';
		//call search function
	//	echo "ddd";
		$result=get_patient($pdo,$_POST['search_by'],$_POST['search_ciretia']);
		$data=explode("#","$result");
		if($data[0]=="bad"){$error_message=" $data[1] ";}
		//if($_SESSION['tab_name']=="#treatment-done"){$_SESSION['tplan_id']='';}
}


//echo "<br>3 tab name is ".$_SESSION['tab_name']." and session[pid] is ".$_SESSION['pid'];
//this will clear the current form
if(isset($_POST['clear_form'])){
	//clear_patient();
	//echo "amd  clearing";
	//clear form completion form
	if($_SESSION['tab_name']=='#completion'){clear_patient_completion();}
	
}
?>
<!--<form class='' action='' method="POST"  name="" id="">-->

<div>
	<div class='grid-15'>
		<?php //$token = form_token(); $_SESSION['token_search_patient2'] = "$token";  ?>
		<!--<input type="hidden" name="token_search_patient2"  value="<?php // echo $_SESSION['token_search_patient2']; ?>" /> -->
		<label for="" class="label">Search Patient by</label>
	</div>
	<div class='grid-15'>
		<select name=search_by class=search_by ><option></option>
			<option value=patient_number>Patient Number</option>
			<option value=first_name>First Name</option>
			<option value=middle_name>Middle Name</option>
			<option value=last_name>Last Name</option>
		</select>
	</div>
	<div class='grid-25'><input type=text name=search_ciretia class=search_ciretia  /></div>
	<!--<div class='grid-5'><input  value="Find" type=button class='button_style search_patient_2'  /></div>
	<div class='grid-30 show_spin'></div>-->
</div>
<!--</form>-->
<!--
<div class="no_padding">
	<form class='clear_form' action='<?php echo "$_SESSION[tab_name]"; ?>' method="POST" >
		<input type=submit value="Clear Form" name=clear_form />
	</form>
</div>-->
<div class=clear></div>