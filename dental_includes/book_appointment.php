<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,45)){exit;}
echo "<div class='grid_12 page_heading'>BOOK APPOINTMENT</div>";
?>
<div class=grid-container>

<?php
//print_r($_POST);
if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
			elseif($_SESSION['result_class']=='bad'){
		//		echo "<div class='feedback'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
		}
//this will clear the current form
if(isset($_POST['clear_form']) and isset($_POST['token_search_patient_appoint2']) and 
	isset($_SESSION['token_search_patient_appoint2']) and $_POST['token_search_patient_appoint2']==$_SESSION['token_search_patient_appoint2']){
	$_SESSION['pid']=$_SESSION['unregistered_patient_name']=$_SESSION['unregistered_patient_phone']='';
	
}

//this will find unregistered patient
if(isset($_POST['patient_name']) and $_POST['patient_name']!='' and isset($_POST['token_search_patient_appoint4']) and 
	isset($_SESSION['token_search_patient_appoint4']) and $_POST['token_search_patient_appoint4']==$_SESSION['token_search_patient_appoint4']){
	$_SESSION['token_search_patient_appoint4']='';
	$_SESSION['unregistered_patient_name']=html($_POST['patient_name']);
	$_SESSION['unregistered_patient_phone']=$_POST['phone'];	
	
}
	
	
//this will find registered patient
if(isset($_POST['search_by']) and $_POST['search_by']!='' and isset($_POST['token_search_patient_appoint1']) and 
	isset($_SESSION['token_search_patient_appoint1']) and $_POST['token_search_patient_appoint1']==$_SESSION['token_search_patient_appoint1']){
	$_SESSION['token_search_patient_appoint1']='';
		$result=get_patient($pdo,$_POST['search_by'],$_POST['search_ciretia']);
		$data=explode("#","$result");
		if($data[0]=="bad"){echo "<div class=' error_response'>$data[1]</div>";}
		
}
/*	if((isset($_SESSION['pid']) and $_SESSION['pid']!='') or (isset($_SESSION['unregistered_patient_name']) and $_SESSION['unregistered_patient_name']!='') ){
		 if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){show_patient_balance($pdo,$_SESSION['pid']);}
		 if(isset($_SESSION['unregistered_patient_name']) and $_SESSION['unregistered_patient_name']!=''){
			echo "<div class=clear></div><div class=grid-100>
				<label class=small_heading>Unregistered Patient Name: $_SESSION[unregistered_patient_name]</label></div>
				<div class=clear></div><br>";
		}*/
		 ?>
		<div class='grid-15'><label for="" class="label">Select appointment Date</label></div>
		<div class='grid-10'><input type=text id=appointment_date class='date_picker_no_past appointment_date_date'/></div>
		<!--<div class='grid-25'><input type=button value="Book Appointment" class='button_style book_appointment' /></div>-->
		<div class="no_padding">
		<!--	<form class='' action='' method="POST" > -->
			<!--	<?php //$token = form_token(); $_SESSION['token_search_patient_appoint2'] = "$token";  ?>
				<input type="hidden" name="token_search_patient_appoint2"  value="<?php echo $_SESSION['token_search_patient_appoint2']; ?>" />	
		<!--		<input type=submit value="New Patient Search" name=clear_form />
			</form> -->
		</div>	
		<div class=clear></div><br>
		<div class='grid-100' id=appointment_div ></div>
		<div class='grid-100' id=appointment_div2 ></div>
	<?php 
//		exit;
//	} ?>
	
	
	
	<!--	<div class=grid-100>
			<div class='grid-15'><label for="" class="label">Select Patient Type</label></div>
			<div class='grid-15'><select class=appointment_patient_type >
				<option></option>
				<option value=registered>Registered</option>
				<option value=unregistered>Un-registered</option>
				</select>
			</div>
		</div>
		<!--<div class=clear></div></br>
		<div class='grid-100' id=appointment_patient_search ></div>
	<!--</form>-->
	
	
	
</div>