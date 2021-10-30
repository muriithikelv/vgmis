<?php
/*if(!isset($_SESSION))
{
session_start();
}/*
include_once  '../../dental_includes/magicquotes.inc.php';
include_once   '../../dental_includes/db.inc.php';
include_once   '../../dental_includes/DatabaseSession.class.php';
include_once   '../../dental_includes/access.inc.php';
include_once   '../../dental_includes/encryption.php';
include_once    '../../dental_includes/helpers.inc.php';*/
include_once     '../../dental_includes/includes_file.php';
//echo "session id is ".$_SESSION['id'];
if(!userIsLoggedIn() or !userHasRole($pdo,12)){ ?> <script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
 <?php exit;
}
$_SESSION['tplan_id']='';
echo "<div class='grid_12 page_heading'>PATIENT CONTACTS</div>";
//get post data for this tab

if(isset($_SESSION['post']) and count($_SESSION['post'])>0){
	$_POST=$_SESSION['post'];
	$_SESSION['post']=array();
}

//this is for doing a patient search
/*
if(isset($_POST['search_by']) and $_POST['search_by']!='' and isset($_POST['token_search_patient']) and
	isset($_SESSION['token_search_patient']) and $_POST['token_search_patient']==$_SESSION['token_search_patient']){
	$_SESSION['token_search_patient']='';
	//search by patient number
	if($_POST['search_by']=="patient_number"){get_patient($pdo,"patient_number",$_POST['search_ciretia']);}

}
*/
//echo "<br>session $_SESSION[token_a1_patinet]<br>post $_POST[token_a1_patinet]<br>";
//print_r($_POST);
//insert or update record
/*
if(isset($_SESSION['token_a1_patinet']) and isset($_POST['token_a1_patinet']) and $_POST['token_a1_patinet']==$_SESSION['token_a1_patinet']){
	$_SESSION['token_a1_patinet']='';
	//perform verifications
	$exit_flag=false;
	echo "38";
		//upload photo
	//$upload=upload_photo($_FILES['image_upload']);
	//echo "$_POST[upload_status]";exit;
	$data=explode("splitter","$_POST[upload_status]");
	if($data[0]=="ERROR"){
		$error_message=html("$data[1]");
		$exit_flag=true;
	}

	//check gender
	if(!$exit_flag and $_POST['gender']!='MALE' and $_POST['gender']!='FEMALE'  ){
		$error_message="Unable to save details as gender is not specified. ";
		$exit_flag=true;
		$gender=html($_POST['gender']);
		$message="sombody tried to input $gender into patient details";
		log_security($pdo,$message);
	}

	//check patient type
	if($_POST['ptype']!=''){
		$ptype=html($encrypt->decrypt($_POST['ptype']));//echo "<br>$ptype is ";exit;
		if(!$exit_flag and !in_array($ptype, $_SESSION['patient_type_array'])){
			$error_message="Unable to save details as patient type is not specified. ";
			$exit_flag=true;
			$message="somebody tried to input $ptype as a patient type into patient details";
			log_security($pdo,$message);
		}
	}

	//check covered compnaycovered_company
	$company_covered=html($encrypt->decrypt($_POST['covered_company']));
	if(!$exit_flag and isset($_POST['covered_company']) and $_POST['covered_company']!=''){

		if(!in_array($company_covered,$_SESSION['covered_company_array'])){
			$error_message="Unable to save details as covered company  is not correctly specified. ";
			$exit_flag=true;
			$message="somebody tried to input $company_covered as a covered compnay into patient details";
			log_security($pdo,$message);
		}
	}

	//check email format
	$email_address=html($_POST['email_address']);
	if(!$exit_flag and isset($_POST['email_address']) and $_POST['email_address']!=''){

		if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
			$error_message="Unable to save details as the email $email_address  is not correctly specified. ";
			$exit_flag=true;
			$message="somebody tried to input $email_address as a email address for a patient in  patient details";
			log_security($pdo,$message);
		}
	}

	//check email format for email address 2
	$email_address_2=html($_POST['email_address_2']);
	if(!$exit_flag and isset($_POST['email_address_2']) and $_POST['email_address_2']!=''){

		if(!filter_var($email_address_2, FILTER_VALIDATE_EMAIL)) {
			$error_message="Unable to save details as the email $email_address_2  is not correctly specified. ";
			$exit_flag=true;
			$message="somebody tried to input $email_address_2 as a email address for a patient in  patient details";
			log_security($pdo,$message);
		}
	}


	//check city
	$city=html($encrypt->decrypt($_POST['city']));
	if(!$exit_flag and isset($_POST['city']) and $_POST['city']!=''){

		if(!in_array($city,$_SESSION['cities_array'])) {
			$error_message="Unable to save details as city  is not correctly specified. ";
			$exit_flag=true;
			$message="somebody tried to input $city as a city for a patient in  patient details";
			log_security($pdo,$message);
		}
	}

	//check date of birth
	if(!$exit_flag and isset($_POST['dob']) and $_POST['dob']!='')	{
		$date='';
		$date=explode('-',$_POST['dob']);
		if(!checkdate( $date[1],$date[2],$date[0] )){
		$dob=html($_POST['dob']);
		$error_message="Unable to save details as date of birth $dob is not in the correct format";
		$exit_flag=true;
		$message="somebody tried to input $dob as date of birth for a patient in  patient details";
		log_security($pdo,$message);
		}
	}

	//check if weight is a proper number
	$weight=html($_POST['weight']);
	if(!$exit_flag and isset($_POST['weight']) and $_POST['weight']!=''){

		if(!ctype_digit($_POST['weight'])){
			//check if it has only 2 decimal places
			$data=explode('.',$_POST['weight']);
			if ( count($data) != 2 ){
			$error_message=" Unable to save details as $weight is not a valid weight number";
			$exit_flag=true;
			$message="somebody tried to input $weight as weight for a patient in  patient details";
			log_security($pdo,$message);
			}
			elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
			$error_message=" Unable to save details as $weight is not a valid weight number";
			$exit_flag=true;
			$message="somebody tried to input $weight as weight for a patient in  patient details";
			log_security($pdo,$message);
			}
		}
	}

	//check relationships for emergency
	$em_relationship=html($encrypt->decrypt($_POST['em_relationship']));
	if(!$exit_flag and isset($_POST['em_relationship']) and $_POST['em_relationship']!=''){

		if(!in_array($em_relationship,$_SESSION['relationship_array'])){
			$error_message="Unable to save details as patient relationship  is not correctly specified. ";
			$exit_flag=true;
			$message="somebody tried to input $em_relationship as a patient relationship into patient details";
			log_security($pdo,$message);
		}
	}

	//check relationships for on behalf form filling
	$other_relationship=html($encrypt->decrypt($_POST['other_relationship']));
	if(!$exit_flag and isset($_POST['other_relationship']) and $_POST['other_relationship']!=''){

		if(!in_array($other_relationship,$_SESSION['relationship_array'])){
			$error_message="Unable to save details as relationship for form filler  is not correctly specified. ";
			$exit_flag=true;
			$message="somebody tried to input $other_relationship as a on behalf relationship into patient details";
			log_security($pdo,$message);
		}
	}

	//check referres
	$referee=html($encrypt->decrypt($_POST['referee']));
	if(!$exit_flag and isset($_POST['referee']) and $_POST['referee']!=''){

		if(!in_array($referee,$_SESSION['referee_array'])){
			$error_message="Unable to save details as patient referrer  is not correctly specified. ";
			$exit_flag=true;
			$message="somebody tried to input $referee as a patient referrrer into patient details";
			log_security($pdo,$message);
		}
	}



	//now insert
	if(!$exit_flag and (!isset($_SESSION['pid']) or $_SESSION['pid']=='')){
		try{
			$pdo->beginTransaction();
			//get photo path if set
			if($_POST['upload_status']!=''){
				$data=explode("splitter","$_POST[upload_status]");
				$photo_path="$data[1]";
			}

			//get patient ID
			$year=date('y');
			$sql=$error=$s='';$placeholders=array();
			$sql="select max(pnum) from patient_details_a where year=:year";
			$error="Unable to get max pnum for year $year";
			$placeholders[':year']="$year";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){foreach($s as $row){$pnum=$row[0] + 1;}}
			else{$pnum=1;}
			$pid="$pnum/$year";

			//now insert into patient_details_a
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_details_a set last_name=:last_name, middle_name=:middle_name, first_name=:first_name, mobile_phone=:mobile_phone,
					biz_phone=:biz_phone, type=:type, patient_number=:patient_number, member_no=:member_no, company_covered=:company_covered, pnum=:pnum,
					year=:year,email_address=:email_address, email_address_2=:email_address_2";
			$error="Unable to add patient new patient";
			$placeholders[':last_name']=$_POST['last_name'];
			$placeholders[':middle_name']=$_POST['middle_name'];
			$placeholders[':first_name']=$_POST['first_name'];
			$placeholders[':mobile_phone']=$_POST['mobile_no'];
			$placeholders[':biz_phone']=$_POST['tel_bix'];
			$placeholders[':type']=$ptype;
			$placeholders[':patient_number']="$pid";
			$placeholders[':member_no']=$_POST['mem_no'];
			$placeholders[':company_covered']=$company_covered;
			$placeholders[':pnum']=$pnum;
			$placeholders[':year']="$year";
			$placeholders[':email_address']="$email_address";
			$placeholders[':email_address_2']="$email_address_2";
			$id = get_insert_id($sql, $placeholders, $error, $pdo);

			//now insert into patient_details_b
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_details_b set id_number=:id_number, address=:address, city=:city, occupation=:occupation,
					em_relationship=:em_relationship, em_phone=:em_phone, behalf_name=:behalf_name, behalf_relationship=:behalf_relationship, when_added=:when_added,
					gender=:gender,	photo_path=:photo_path, pid=:pid, weight=:weight, dob=:dob, referee=:referee, em_contact=:em_contact";
			$error="Unable to add patient new patient";
			$placeholders[':id_number']=$_POST['id_no'];
			$placeholders[':address']=$_POST['address'];
			$placeholders[':city']=$city;
			$placeholders[':weight']=$weight;
			$placeholders[':dob']=$_POST['dob'];
			$placeholders[':referee']=$referee;
			$placeholders[':em_contact']=$_POST['em_contact'];
			$placeholders[':occupation']=$_POST['occupation'];
			$placeholders[':em_relationship']=$em_relationship;
			$placeholders[':em_phone']=$_POST['em_phone'];
			$placeholders[':behalf_name']=$_POST['other_name'];
			$placeholders[':behalf_relationship']=$other_relationship;
			$placeholders[':when_added']=date('Y-m-d');
			$placeholders[':gender']=$_POST['gender'];
			$placeholders[':photo_path']="$photo_path";
			$placeholders[':pid']=$id;
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$success_message=" Patient details saved ";get_patient($pdo,"patient_number","$patient_number");}
			elseif(!$s){$error_message=" Unable to save patient details ";}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){
				//delete photo if set
				if($photo_path!=''){unlink("$path_photo");}
				$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$error_message="   Unable to save patient details  ";
		}
	}

	//now update
	if(!$exit_flag and (isset($_SESSION['pid']) and $_SESSION['pid']!='')){
		try{
			$pdo->beginTransaction();
			//get photo path if set
			if($_POST['upload_status']!=''){
				$data=explode("splitter","$_POST[upload_status]");
				$photo_path="$data[1]";
			}


			//now update into patient_details_a
			$sql=$error=$s='';$placeholders=array();
			$sql="update patient_details_a set last_name=:last_name, middle_name=:middle_name, first_name=:first_name, mobile_phone=:mobile_phone,
					biz_phone=:biz_phone, type=:type,  member_no=:member_no, company_covered=:company_covered,
					email_address=:email_address where pid=:pid";
			$error="Unable to update patient details";
			$placeholders[':last_name']=$_POST['last_name'];
			$placeholders[':middle_name']=$_POST['middle_name'];
			$placeholders[':first_name']=$_POST['first_name'];
			$placeholders[':mobile_phone']=$_POST['mobile_no'];
			$placeholders[':biz_phone']=$_POST['tel_bix'];
			$placeholders[':type']=$ptype;
			$placeholders[':pid']=$_SESSION['pid'];
			$placeholders[':member_no']=$_POST['mem_no'];
			$placeholders[':company_covered']=$company_covered;
			$placeholders[':email_address']="$email_address";
			$s = insert_sql($sql, $placeholders, $error, $pdo);

			//now update patient_details_b
			$sql=$error=$s='';$placeholders=array();
			$sql="update patient_details_b set id_number=:id_number, address=:address, city=:city, occupation=:occupation,
					em_relationship=:em_relationship, em_phone=:em_phone, behalf_name=:behalf_name, behalf_relationship=:behalf_relationship,
					gender=:gender,	photo_path=:photo_path, weight=:weight, dob=:dob, referee=:referee, em_contact=:em_contact where pid=:pid";
			$error="Unable to update patient details";
			$placeholders[':id_number']=$_POST['id_no'];
			$placeholders[':address']=$_POST['address'];
			$placeholders[':city']=$city;
			$placeholders[':weight']=$weight;
			$placeholders[':dob']=$_POST['dob'];
			$placeholders[':referee']=$referee;
			$placeholders[':em_contact']=$_POST['em_contact'];
			$placeholders[':occupation']=$_POST['occupation'];
			$placeholders[':em_relationship']=$em_relationship;
			$placeholders[':em_phone']=$_POST['em_phone'];
			$placeholders[':behalf_name']=$_POST['other_name'];
			$placeholders[':behalf_relationship']=$other_relationship;
			$placeholders[':gender']=$_POST['gender'];
			$placeholders[':photo_path']="$photo_path";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$success_message=" Patient details saved. ";get_patient($pdo,"pid","$_SESSION[pid]");}
			elseif(!$s){$error_message=" Unable to save Patient details ";}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){
				//delete photo if set
				if($photo_path!=''){unlink("$path_photo");}
				$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$error_message="   Unable to save patient details  ";
		}
	}
}
echo "$error_message  <br> $success_message";*/
//this will unset the patient contact session variables if not pid is currenlty set
if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and
	$_SESSION['result_class']!=''){
		if($_SESSION['result_class']=='success_response'){
			echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';
		}
}
if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient();}
?>
<div class=grid-container>

	<div class=grid-100 >
	<div id=pt_contact_shower class='grid-100 pt_contact_shower'></div>
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php';
			$_SESSION['tab_name']="#contacts";
			 include '../../dental_includes/search_for_patient.php';
			 echo "<div class='grid-100 div_shower31'></div>";
			 if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){
				$pid_bal="pid_".$_SESSION['pid'];
				$_SESSION["$pid_bal"]=array();
				$result=show_pt_statement_brief($pdo,$encrypt->encrypt("$_SESSION[pid]"),$encrypt);
				$data=explode('#',"$result");
				$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
				show_patient_balance($pdo,$_SESSION['pid'],$encrypt);

			}
			//if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){show_patient_balance($pdo,$_SESSION['pid'],$encrypt);}
		//#patient-contacts
	?>
	<form action="#patient-contacts" class="patient_form2" method="POST" enctype="multipart/form-data" name="" id="#patient_contacts_form">
	<input type="hidden" name="MAX_FILE_SIZE" value="2097152"/>
			<fieldset><legend>Patient Names</legend>




			<div class='grid-75'>
				<!--first name-->
				<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_a1_patinet'] = "$token";  ?>
	<input type="hidden" name="token_a1_patinet"  value='<?php echo "$_SESSION[token_a1_patinet]"; ?>' />

				<label for="" class="label">First Name </label></div>
				<div class='grid-25'><input type=text name=first_name value='<?php echo $_SESSION['first_name']; ?>' /></div>

				<!--second name-->
				<div class='grid-15'><label for="" class="label">Middle Name </label></div>
				<div class='grid-25'><input type=text name=middle_name value='<?php echo $_SESSION['middle_name']; ?>' /></div>
				<div class=clear></div><br>
				<!--last name-->
				<div class='grid-15'><label for="" class="label">Last Name </label></div>
				<div class='grid-25'><input type=text name=last_name value='<?php echo $_SESSION['last_name']; ?>' /></div>
					<!--gender-->
				<div class='grid-15'><label for="" class="label">Gender</label></div>
				<div class='grid-25'><select name=gender><option></option>
				<?php if($_SESSION['gender']=='MALE'){ $male_selected=" selected ";$female_selected="";}
					  elseif($_SESSION['gender']=='FEMALE'){ $male_selected="";$female_selected=" selected ";}
					  else{ $male_selected="";$female_selected="";}?>
					<option value='MALE' <?php echo $male_selected; ?> >MALE</option><option value='FEMALE' <?php echo $female_selected; ?> >FEMALE</option></select></div>

	<div class=clear></div>	<br>

				<!--card issued-->
				<div class='grid-15'><label for="" class="label">Card Issued</label></div>
				<div class='grid-25'><select name=card_issued><option></option>
				<?php if($_SESSION['card_issued']=='YES'){ $yes_selected=" selected ";$no_selected="";}
					  elseif($_SESSION['card_issued']=='NO'){ $yes_selected="";$no_selected=" selected ";}
					  else{ $no_selected="";$yes_selected="";}?>
					<option value='YES' <?php echo $yes_selected; ?> >YES</option><option value='NO' <?php echo $no_selected; ?> >NO</option></select></div>
					<div class=clear></div>
				</div>

			<!--	</div>-->
			<div class='grid-25'>


				<?php if(!isset($_SESSION['photo_path']) or $_SESSION['photo_path']==''){$_SESSION['photo_path']="../dental-images/profile/patient_photo.png";}?>
				<div class=''><label for="" class="label"><img src='<?php echo "$_SESSION[photo_path]"; ?>' /> </label></div>
				<div class=''><input type=file value='Upload Patient Photo' name=image_upload /></div>

			</div>


		</fieldset>
		<div class=clear></div>
		<!-- patient type-->
		<fieldset><legend>Patient Type</legend>

	        <div class='grid-50 grid-parent'>
				<!--patient type-->
				<div class='grid-30'><label for="" class="label">Patient Type</label></div>
				<div class='grid-70'><select class=ptype name=ptype><option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from insurance_company order by name";
						$error = "Unable to insurance companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							if($_SESSION['type']==$row['id']){echo "<option value='$val' selected>$name</option>";}
							else{echo "<option value='$val'>$name</option>";}
						}

					?>
					</option></select>
				</div>
				<div class=clear></div>	<br>
				<!--compnay covered-->
				<div class='grid-30 alpha'><label for="" class="label">Company Covered</label></div>
				<div class='grid-70 omega'><select class='covered_company ' name=covered_company><option></option>
				<?php
					if(isset($_SESSION['id']) and $_SESSION['id']!=''){
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from covered_company order by name";
						$error = "Unable to covered companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							if($_SESSION['company_covered']==$row['id']){echo "<option value='$val' selected>$name</option>";}
							else{echo "<option value='$val'>$name</option>";}
						}

					}
				?>
				</select></div>
				<div class=clear></div>	<br>
				<!--membership number-->
				<div class='grid-30'><label for="" class="label">Membership Number</label></div>
				<div class='grid-70'><input type=text name=mem_no value='<?php echo $_SESSION['member_no']; ?>' /></div>
				<div class=clear></div><br>
			</div>
			<div class='grid-50 grid-parent' id='family_div' >
				<?php
					//check if guy has family
					if($_SESSION['pid']!=''){
						if($_SESSION['family_id']=='' ){echo "<div class=grid-100><input type=button class='new_family button_style' value='Add to Family Group' /></div>";}
						else{
							$pid2=$encrypt->encrypt($_SESSION['pid']);
							get_pt_family_memebrs($pdo, $pid2, $encrypt);
						}
					}
				?>
			</div>

		</fieldset>
		<!--this will have contacts etc -->
		<fieldset><legend>Other Details</legend>

				<!--ID number-->
				<div class='grid-15'><label for="" class="label">ID No.</label></div>
				<div class='grid-25'><input type=text name=id_no value='<?php echo $_SESSION['id_number']; ?>' /></div>

				<!--home phone-->
				<div class='grid-15'><label for="" class="label">Mobile No.</label></div>
				<div class='grid-15'><input type=text name=mobile_no value='<?php echo $_SESSION['mobile_phone']; ?>' /></div>

				<!--home phone-->
				<!-- <div class='grid-15'><label for="" class="label">Business No..</label></div>
				<div class='grid-15'><input type=text name=tel_bix value='<?php //echo $_SESSION['biz_phone']; ?>' /></div>
				<div class=clear></div><br> -->
				<!--email address 1-->
				<!-- <div class='grid-15'><label for="" class="label">Email Address 1</label></div>
				<div class='grid-25'><input type=text name=email_address value='<?php //echo $_SESSION['email_address']; ?>' /></div>
				 --><!--email address 2-->
				<!-- <div class='grid-15'><label for="" class="label">Email Address 2</label></div>
				<div class='grid-25'><input type=text name=email_address_2 value='<?php //echo $_SESSION['email_address_2']; ?>' /></div>
				<div class=clear></div><br> -->
				<!--address-->
				<!-- <div class='grid-15'><label for="" class="label">Address</label></div>
				<div class='grid-25'><input type=text name=address value='<?php //echo $_SESSION['address']; ?>' /></div>
				 -->
				<!--city-->
				<!--home phone-->
				<div class='grid-15'><label for="" class="label">Residence</label></div>
				<div class='grid-15'><input type=text name=city value='<?php echo $_SESSION['city']; ?>' /></div>

				<!-- <div class='grid-15'><label for="" class="label">Residence</label></div>
				<div class='grid-25'><select name=city><option><option>-->
				<!-- ?php
					$sql=$error=$s='';$placeholders=array();
					$sql = "select id,name from cities order by name";
					$error = "Unable to list cities";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$name=html($row['name']);
						$val=$encrypt->encrypt(html($row['id']));
						if($_SESSION['city']==$row['id']){echo "<option value='$val' selected>$name</option>";}
						else{echo "<option value='$val'>$name</option>";}
					}

				? -->
				<!-- </select></div> -->
				<div class=clear></div><br>
				<!--date of birth-->
				<div class='grid-15'><label for="" class="label">Date of Birth</label></div>
				<div class='grid-25'><input type=text name=dob class=date_picker value='<?php echo $_SESSION['dob']; ?>' /></div>
				<!--weight-->
			<!-- 	<div class='grid-15'><label for="" class="label">Weight(Kg)</label></div>
				<div class='grid-15'><input type=text name=weight value='<?php //echo $_SESSION['weight']; ?>' /></div>
				 -->
				<!--occupation-->
				<div class='grid-15'><label for="" class="label">Occupation</label></div>
				<div class='grid-15'><input type=text name=occupation value='<?php echo $_SESSION['occupation']; ?>' /></div>
				<div class=clear></div><br>
				<!--emergency contact-->
				<div class='grid-15'><label for="" class="label">Emergency Contact</label></div>
				<div class='grid-25'><input type=text name=em_contact value='<?php echo $_SESSION['em_contact']; ?>' /></div>
				<!--emergency relationship-->
				<div class='grid-15'><label for="" class="label">Relationship</label></div>
				<div class='grid-15'><select name=em_relationship><option>
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql = "select id,name from patient_relationships order by name";
					$error = "Unable to list patient relationships";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$name=html($row['name']);
						$val=$encrypt->encrypt(html($row['id']));
						if($_SESSION['em_relationship']==$row['id']){echo "<option value='$val' selected>$name</option>";}
						else{echo "<option value='$val'>$name</option>";}
					}

				?>
				</option></select/></div>

				<!--emregency phone-->
				<div class='grid-15'><label for="" class="label">Phone No.</label></div>
				<div class='grid-15'><input type=text name=em_phone value='<?php echo $_SESSION['em_phone']; ?>' /></div>

				<div class=clear></div><br>
				<!--refered by-->
		<!-- 		<div class='grid-15'><label for="" class="label">Referred by</label></div>
				<div class='grid-15'><select name=referee><option></option>
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql = "select id,name from patient_referrer order by name";
					$error = "Unable to list patient refreres";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$name=html($row['name']);
						$val=$encrypt->encrypt(html($row['id']));
						if($_SESSION['referee']==$row['id']){echo "<option value='$val' selected>$name</option>";}
						else{echo "<option value='$val'>$name</option>";}
						//echo "<option value='$val'>$name</option>";
					}

				?>
				</option></select></div>	 -->

				<div class=clear></div><br>
				<div class=grid-100>If this form is copmleted on behalf of another person what is your relation ship to that person?</div>
				<!--other name-->
				<div class='grid-15'><label for="" class="label">Your Name</label></div>
				<div class='grid-15'><input type=text name=other_name value='<?php echo $_SESSION['behalf_name']; ?>' /></div>
				<!--other relationship-->
				<div class='grid-15'><label for="" class="label">Relationship</label></div>
				<div class='grid-15'><select name=other_relationship><option>
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql = "select id,name from patient_relationships order by name";
					$error = "Unable to list patient relationships";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$name=html($row['name']);
						$val=$encrypt->encrypt(html($row['id']));
						if($_SESSION['behalf_relationship']==$row['id']){echo "<option value='$val' selected>$name</option>";}
						else{echo "<option value='$val'>$name</option>";}
						//echo "<option value='$val'>$name</option>";
					}

				?>
				</option></select/></div>
				<div class=clear></div><br>


				<!-- <input type="submit"  value="Submit"/> -->
				<?php

					if(!isset($swapped) or $swapped==''){
						echo "<div class='grid-25 prefix-15'>	<br>";
						show_submit($pdo,'','');
						echo "</div>";
					}
					elseif(isset($swapped) and $swapped!=''){echo "<div class='grid-100 error_response'>$swapped</div>";}
					//show person who added the record
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select  a.first_name, a.middle_name, a.last_name from users  a , patient_details_a b where b.pid=:pid and b.added_by=a.id";
					$error2="Unable to pending visits";
					$placeholders2[':pid']=$_SESSION['pid'];
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2){
						$added_by=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name] "));
						echo "<div class='prefix-15 grid-85'><label for='' class='label'><br>Record added by: $added_by</div>";
					}
				?>








			<div class=clear></div>


		</fieldset>
	</form>
	</div>
</div>