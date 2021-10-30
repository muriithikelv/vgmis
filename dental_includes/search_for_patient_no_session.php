<?php
if(isset($_POST['search_by']) and $_POST['search_by']!='' and isset($_POST['token_search_patient_no_session']) and 
			isset($_SESSION['token_search_patient_no_session']) and $_POST['token_search_patient_no_session']==$_SESSION['token_search_patient_no_session']){
		//	$result=get_patient_no_session($pdo,$_POST['search_by'],$_POST['search_ciretia']);
		//this will get a patient's contact details
	//	function get_patient($pdo,$criteria,$patient_number) {// include 'db.inc.php';
			$criteria=$_POST['search_by'];
			$patient_number=$_POST['search_ciretia'];
			//get patient details a
			$sql=$error=$s='';$placeholders=array();	
			if($criteria=="patient_number"){$sql="select * from patient_details_a where patient_number=:patient_number ";}//and internal_patient=0
			elseif($criteria=="pid"){$sql="select * from patient_details_a where pid=:patient_number";}
						//by patient names
			elseif($_POST['search_by']=='first_name' or $_POST['search_by']=='middle_name' or $_POST['search_by']=='last_name'){	
				$result=get_pt_name2($_POST['search_by'],$_POST['search_ciretia'],$pdo,$encrypt,'token_search_patient_no_session','search_by','patient_number','search_ciretia');
				if($result=="2"){echo "<div class='error_response'>No such patient</div>";}
				else{
					echo "9 $result";
					exit;
				}
				
			}
			if($sql!=''){
				$placeholders[':patient_number']="$patient_number";
				$error="Error: Unable to get patient details a";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount()>0){
					foreach($s as $row){
						$last_name=ucfirst(html($row['last_name']));
						$middle_name=ucfirst(html($row['middle_name']));
						$first_name=ucfirst(html($row['first_name']));
						$type=html($row['type']);
						$patient_number=html($row['patient_number']);
						$pid_clean=html($row['pid']);
						$pid=$encrypt->encrypt(html($row['pid']));
						
						$member_no=html($row['member_no']);
						$company_covered=html($row['company_covered']);
						$family_id=html($row['family_id']);
						$family_title=html($row['family_title']);
						$insurance_cover_role=html($row['insurance_cover_role']);
						
					}
					//get company_covered_name and type_name
					$company_covered_name=$type_name='';
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select name from covered_company where id=:covered_company";
					$placeholders2[':covered_company']=$company_covered;
					$error2="Error: Unable to get covered company name ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2 ){$company_covered_name=html($row2['name']);}
					
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select name from insurance_company where id=:type";
					$placeholders2[':type']=$type;
					$error2="Error: Unable to get insurance company name ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2 ){$type_name=html($row2['name']);}	
				}
				else{ echo "<div class='error_response'>No such patient</div>";}
			}			
}


?>

<form class='' action='' method="POST"  name="" id="">
	<div class='grid-15'>
		<?php $token = form_token(); $_SESSION['token_search_patient_no_session'] = "$token";  ?>
		<input type="hidden" name="token_search_patient_no_session"  value="<?php echo $_SESSION['token_search_patient_no_session']; ?>" />
		<label for="" class="label">Search Patient by</label>
	</div>
	<div class='grid-15'>
		<select name=search_by><option></option>
			<option value=patient_number>Patient Number</option>
			<option value=first_name>First Name</option>
			<option value=middle_name>Middle Name</option>
			<option value=last_name>Last Name</option>
		</select>
	</div>
	<div class='grid-25'><input type=text name=search_ciretia  /></div>
	<div class='grid-35 show_spin'><input class='find_pt1' type=submit value="Find"  /></div>
	
</form>
<div class=clear></div><br>
<?php
if(isset($pid) and $pid!=''){
	$result = show_pt_statement_brief($pdo,$encrypt->encrypt("$pid_clean"),$encrypt);
	$data=explode('#',"$result");
	$previous_cash_bal='';
	$previous_cash_bal=show_pt_statement_brief_also_with_swapped_with_balance($pdo,$encrypt->encrypt("$pid_clean"),$encrypt);
	
echo "<table>
	<thead>
	<tr><th>Patient Number</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Patient Type</th><th>Company Covered</th>
	<th>INSURANCE BALANCE</th><th>SELF BALANCE</th><th>POINTS BALANCE</th><th>cover limit</th><th>cover expiry</th></tr></thead>
	<tbody><td>$patient_number</td><td>$first_name</td><td>$middle_name</td><td>$last_name</td>
	<td>$type_name</td><td>$company_covered_name</td><td>$data[0] </td><td>$data[1] $previous_cash_bal</td><td>$data[2]</td><td>limit</td><td>expiry</td></tbody></table>";
	//if($_SESSION['insurance_mismatch_error'] != ''){echo "<div class='error_response'>$_SESSION[insurance_mismatch_error]</div>";}
}

?>