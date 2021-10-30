<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,93)){exit;}
echo "<div class='grid_12 page_heading'>DRUGS PRESCRIPTIONS</div>";
?>
<div class=grid-container>
<div class='grid-100 div_shower44'></div>
<?php 

//get results
if(isset($_POST['token_dsr1']) and 	$_POST['token_dsr1']!='' and $_POST['token_dsr1']==$_SESSION['token_dsr1']){
		$_SESSION['token_dsr1']='';
		$exit_flag=false;
		$sql=$error=$s='';$placeholders=array();

		//check if doctor selected
		if(!$exit_flag and !isset($_POST['doc']) or $_POST['doc']=='' ){	
				$result_class="error_response";
				$result_message="Please specify a search criteria for the doctor";
				$exit_flag=true;
		}
		
		//check if date is selcted
		if(!$exit_flag and !isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']==''  ){	
				$result_class="error_response";
				$result_message="Please specify the date range for the search criteria";
				$exit_flag=true;
		}	
		
		//check if prescription type is selected
		if(!$exit_flag and !isset($_POST['prescription_type']) or $_POST['prescription_type']=='' ){	
				$result_class="error_response";
				$result_message="Please specify a search criteria for the prescription type";
				$exit_flag=true;
		}

		//check if drug tyepe is  selected
		if(!$exit_flag and !isset($_POST['drugs']) or $_POST['drugs']=='' ){	
				$result_class="error_response";
				$result_message="Please specify a search criteria for the drug";
				$exit_flag=true;
		}		
		
				
		if(!$exit_flag){
		$from_date=html($_POST['from_date']);
		$to_date=html($_POST['to_date']);
		$doctor=$insurer=$company=$balance=$prescription=$drug='';
		$total_cost=$total_paid=0;
		$doc_var=" all doctors ";
		$prescripiton_var='';
		//doctor criteria
		if($_POST['doc']!='all'){
			$doc_id=$encrypt->decrypt($_POST['doc']);
			$doctor = " and b.created_by=:doc_id ";
			$placeholders[':doc_id']=$doc_id;
			
		}
		
		//drug criteria
		if($_POST['drugs']!='all'){
			$drug_id=$encrypt->decrypt($_POST['drugs']);
			$drug = " and b.drug_id=:drug_id ";
			$placeholders[':drug_id']=$drug_id;
			
		}		
		
		//prescription type criteria
		if($_POST['prescription_type']!='all'){
			$prescription_type=$encrypt->decrypt($_POST['prescription_type']);
			$prescription=" and b.pay_type=:pay_type ";
			$placeholders[':pay_type']=$prescription_type;
			
		}			
		
			$sql="select a.name, b.when_added, case b.pay_type when '2' then 'Sold' when '0' then 'Prescribed' end as pay_type,
				b.prescription_number, b.cost,concat(c.first_name,' ',c.middle_name,' ',c.last_name) as patient_names, 
				concat(d.first_name,' ',d.middle_name,' ',d.last_name) as doctor_names ,c.pid
				from prescriptions as b join drugs as a on b.drug_id=a.id and b.when_added >=:from_date and b.when_added <=:to_date $doctor
				$drug $prescription
				join patient_details_a as c on c.pid= b.pid 
				join users as d on b.created_by=d.id order by b.id
				";
			$placeholders[':from_date']=$_POST['from_date'];	
			$placeholders[':to_date']=$_POST['to_date'];
			$s = select_sql($sql, $placeholders, $error, $pdo);	
			if($s->rowCount() > 0){
				$i=$total=0;
				$var='';
				foreach($s as $row){
					$pid_enc=$encrypt->encrypt($row['pid']);
					$cost='';
					$when_added=html("$row[when_added]");
					$patient=ucfirst(html($row['patient_names']));
					$doctor=ucfirst(html($row['doctor_names']));
					//$patient_number=html("$row[patient_number]");
					$drug=html("$row[name]");
					if($row['pay_type']!='Sold'){$cost='';}
					else{
						$total = $total + html($row['cost']);
						$cost=number_format(html($row['cost']),2);
					}
					$prescription_type=html($row['pay_type']);
					$prescription_number=html($row['prescription_number']);
					if($i==0){
						if($_POST['doc']!='all'){$doc_var=" Dr. $doctor ";}
						$caption=strtoupper("drugs prescribed for $doc_var between $from_date and $to_date");
						echo "<br><br><div class='grid-100 no_padding dialog_with_tab'></div>
						<table class='normal_table'><caption>$caption</caption><thead>
						<tr ><th class=dsr1_ount></th>
						<th class=dsr1_date>DATE</th><th class=dsr1_doc>DOCTOR</th><th class=dsr1_tx>TRANSACTION TYPE</th>
						<th class=dsr1_pt>PATIENT NAME</th><th class=dsr1_drug>DRUG</th><th class=dsr1_cost>COST</th>
						<th class=dsr1_presc_num>PRESCRIPTION NUMBER</th>
						</tr>
						</tr></thead><tbody>";						
					}
					$i++;
					echo"<tr ><td>$i</td><td>$when_added</td><td>$doctor</td><td>$prescription_type</td><td><input type=hidden value='$pid_enc' /><a href='' class='link_color pt_statement_swapped2' >$patient</a> </td>
							<td>$drug</td><td>$cost</td><td><input type=button class='input_in_table_cell button_style show_prescription' value='$prescription_number' /></td></tr>";
				}
				if($total > 0){	echo "<tr class=total_background><td colspan=6>Total value of drugs sold</td><td>".number_format($total,2)."</td><td>&nbsp;</td></tr>";}
				echo "</tbody></table>";
			}
			else{echo "<label  class=label>There are no prescriptions for the selected criteria</label>";}
			exit;
		}//end do if exit flag is not true
		if($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}
		
		
}	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
		<br>	
			
	<form action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_dsr1'] = "$token";  ?>
					<input type="hidden" name="token_dsr1"  value="<?php echo $_SESSION['token_dsr1']; ?>" />
					
				<!--show doctor-->
				<div class='grid-15'><label for="" class="label">Select Doctor</label>
				</div>
				<div class='grid-30'><select name=doc>
					<option value='all'>ALL Doctors</option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,first_name, middle_name, last_name from users where user_type=1 order by first_name";
						$error = "Unable to get doctors";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name] "));
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
						
					
					?>
					</select>
				</div>	
						
				<!--show drugs-->
				<div class='grid-15'><label for="" class="label">Select Drug</label>
				</div>
				<div class='grid-30'><select name=drugs>
					<option value='all'>ALL Drugs</option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from drugs order by name";
						$error = "Unable to get drugs";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html("$row[name]");
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
					?>
					</select>
				</div>	
				<div class=clear></div><br>

				<!--prescription type-->
				<div class='grid-15'><label for="" class="label">Select Prescription Type</label>
				</div>
				<?php
					$sold_drugs=$encrypt->encrypt("2");
					$prescribed_drugs=$encrypt->encrypt("0");
				
				echo "<div class='grid-30'><select name=prescription_type>
					<option value='all'>ALL Prescription Types</option>
					<option value='$sold_drugs'>Drugs Sold</option>
					<option value='$prescribed_drugs'>Drugs Prescribed</option>
					</select>";
					?>
				</div>					
				
				<!--date range-->
				<div class='grid-15'><label for="" class="label">From this date</label></div>
				<div class='grid-10'><input type=text name=from_date class=date_picker /></div>	
				<div class='grid-10'><label for="" class="label">To this date</label></div>
				<div class='grid-10'><input type=text name=to_date class=date_picker /></div>	
				
				<div class='grid-10'>	<input type="submit"  value="Submit"/></div>

	</form>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>