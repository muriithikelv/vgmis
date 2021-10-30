<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
//this is for doing a patient search
//echo "a $_POST[token_search_patient]<br>
 //b $_SESSION[token_search_patient]";
if(isset($_POST['search_by']) and $_POST['search_by']!='' and isset($_POST['token_search_patient']) and 
	isset($_SESSION['token_search_patient']) and $_POST['token_search_patient']==$_SESSION['token_search_patient']){
	$_SESSION['token_search_patient']='';
	$_SESSION['tplan_id']='';
		//call search function
	//	echo "-- $_POST[search_by] --";
		if($_POST['search_by']=='patient_number'){
			$result=get_patient($pdo,$_POST['search_by'],$_POST['search_ciretia']);
			$data=explode("#","$result");
		//	if($data[0]=="bad"){echo "<div class='grid-100 error_message'>$data[1]</data> ";}
		}
		elseif($_POST['search_by']=='first_name' or $_POST['search_by']=='middle_name' or $_POST['search_by']=='last_name'){
			get_pt_name($_POST['search_by'],$_POST['search_ciretia'],$pdo,$encrypt);
		}
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
//show error when serach yields no patient
if(isset($_SESSION['no_patient_found']) and $_SESSION['no_patient_found']!=''){
echo "<div class='grid-100 error_response'>$_SESSION[no_patient_found]</div> ";
$_SESSION['no_patient_found']='';
}


?>
<form class='search_form' action='<?php echo $_SESSION['tab_name']; ?>' method="POST"  name="" id="">
	<div class='grid-15'>
		<?php $token = form_token(); $_SESSION['token_search_patient'] = "$token";  ?>
		<input type="hidden" name="token_search_patient"  value="<?php echo $_SESSION['token_search_patient']; ?>" />
		<label for="" class="label">Search Patient by</label>
	</div>
	<div class='grid-15'>
		<select class=sc name=search_by><option></option>
			<option value=patient_number>Patient Number</option>
			<option value=first_name>First Name</option>
			<option value=middle_name>Middle Name</option>
			<option value=last_name>Last Name</option>
			<option value=mobile_number>Mobile Number</option>
			<option value=business_number>Business Number</option>
		</select>
	</div>
	<div class='grid-25'>
		<?php
			if($_SESSION['tab_name']=="#contacts"){echo "<input type=hidden name=ptc />";}
		?>
	<input type=text name=search_ciretia class=sv /></div>
	<div class='grid-35 show_spin'><input class=sb type=submit value="Find"  /></div>
	
</form>
<?php
if($_SESSION['tab_name']=="#contacts"){
	?>
	<div class="no_padding">
		<form class='clear_form' action='<?php echo "$_SESSION[tab_name]"; ?>' method="POST" >
			<?php $token = form_token(); $_SESSION['token_search_patient_cf'] = "$token";  ?>
			<input type="hidden" name="token_search_patient_cf"  value="<?php echo $_SESSION['token_search_patient_cf']; ?>" />
			<input type=submit value="Clear Form" name=clear_form />
		</form>
	</div>
	<?php
}
if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
	if($result!='good'){
		$swapped="$result and cannot be edited";
		echo "<div class='grid-100 error_response'>$result</div>";
	}
	elseif($result=='good'){$swapped='';}
}
			
?>
<div class=clear></div>