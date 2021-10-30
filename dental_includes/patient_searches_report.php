<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,117)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT CONTACTS SEARCH REPORT</div>"; ?>
<div class="grid-100 margin_top ">
<?php   
//show appointments
if( isset($_POST['token_pcs1']) and isset($_SESSION['token_pcs1']) and $_SESSION['token_pcs1']==$_POST['token_pcs1']){
	$_SESSION['token_pcs1']='';
	$exit_flag=false;
	//check if user is set
	if(!$exit_flag and !isset($_POST['user']) or 	$_POST['user']==''){
		$error_class='error_response';
		$message="No search user specified. ";
		$exit_flag=true;
	}
	//check if dates are set
	if(!$exit_flag and !isset($_POST['from_date']) or 	$_POST['from_date']=='' or !isset($_POST['to_date']) or $_POST['to_date']==''){
		$error_class='error_response';
		$message="Please specify the date range. ";
		$exit_flag=true;
	}
	if(!$exit_flag){
		//get report
		$sql=$error=$s=$group_id='';$placeholders=array();
		$user_criteria='';
		if($_POST['user']!='all'){
			$user_id=$encrypt->decrypt("$_POST[user]");
			$user_criteria = " and users.id=:user_id";	
			$placeholders[':user_id']=$user_id;
		}

		
		
		$sql="select patient_contact_searches.when_added, users.first_name as user_fname, users.middle_name as user_mname, 
			users.last_name as user_lname, 
			patient_details_a.first_name, patient_details_a.middle_name, patient_details_a.last_name, patient_details_a.patient_number,
			insurance_company.name
			
			from patient_contact_searches join users on patient_contact_searches.user_id=users.id $user_criteria
			join patient_details_a on patient_details_a.pid=patient_contact_searches.pid
			left join insurance_company on insurance_company.id=patient_details_a.type
			
			where date(patient_contact_searches.when_added)>=:from_date and date(patient_contact_searches.when_added)<=:to_date
			";
		$error="Unable to generate patient sarch report";
		$placeholders[':from_date']=$_POST['from_date'];
		$placeholders[':to_date']=$_POST['to_date'];
		$s = 	select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['to_date']);
			$count=$hours=$minutes=$seconds=0;
			$for_user='';
			foreach($s as $row){
				$user_name=ucfirst(html("$row[user_fname] $row[user_mname] $row[user_lname]"));
				if($_POST['user'] != 'all') {$for_user="for $user_name";}
				$pt_name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
				$patient_type=html("$row[name]");
				$date=html("$row[when_added]");
				$patient_number=html($row['patient_number']);
				if($count==0){
					echo "<table class='normal_table'><caption>Patient Contact Searches $for_user between $from_date and $to_date</caption><thead>
			              <tr><th class='ptc_count'></th><th class=ptc_date>SEARCH DATE</th><th class=ptc_name>SEARCHED BY</th>
						  <th class=ptc_ptnum>PATIENT<br>NUMBER</th><th class=ptc_name>PATIENT NAME</th><th class=ptc_type>PATIENT TYPE</th>
						  </tr></thead><tbody>";
				}
				$count++;
				echo "<tr ><td>$count</td><td>$date</td><td>$user_name</td><td>$patient_number</td><td>$pt_name</td><td>$patient_type</td></tr>";
				
			}
			echo "</tbody></table>";
			exit;
		}
		else{
			echo "<div class=grid-100><label class=label>There are no search records for the selected criteria</label></div>";
			echo "<br><br>";
		}	
	
	}
}
if(isset($error_class) and $error_class!='' and isset($message) and $message!=''){echo "<div class='grid-100 $error_class'>$message</div>";}
		
?>	
	<form class='' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_pcs1'] = "$token";  ?>
		<input type="hidden" name="token_pcs1"  value="<?php echo $_SESSION['token_pcs1']; ?>" />
		<div class=grid-15><label class=label>Select User</label></div>
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, first_name, middle_name, last_name from users order by first_name";
				$error2="Unable to get users";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='grid-30'><select name=user ><option></option>";
					foreach($s2 as $row2){
						$name=html("$row2[first_name] $row2[middle_name] $row2[last_name] ");
						$var=$encrypt->encrypt($row2['id']);
						echo "<option value=$var>$name</option>";
					}
					echo "<option value='all'>ALL Users</opiton>";
				echo "</select></div>"; ?>
		<div class=clear></div><br>	
		<div class='grid-15'><label for="user" class="label">Patients searched between this date </label></div>
		<div class='grid-10 '><input type=text name=from_date class=date_picker /></div>
		<div class='grid-10'><label for="user" class="label">and this date</label></div>
		<div class='grid-10 '><input type=text name=to_date class=date_picker /></div>
		
		
		<div class=clear></div><br>	
		<div class='prefix-35 grid-10'><input type=submit  value=Submit /></div>
	</form>

</div>