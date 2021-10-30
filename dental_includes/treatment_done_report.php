<?php
if(!userIsLoggedIn() or !userHasRole($pdo,89)){exit;}
echo "<div class='grid_12 page_heading'>TREATMENT DONE DETAILS REPORT</div>";
?>
<div class=grid-container>
<?php
	include '../dental_includes/search_for_patient_no_session.php';
	
if(isset($pid_clean) and $pid_clean!=''){
	/*function get_treatments_done($pdo, $pid_clean){
		//get the patients names
		$sql=$error=$s='';$placeholders=array();
		$sql="select first_name,middle_name,last_name, patient_number from patient_details_a where pid=:pid ";
		$placeholders[':pid']=$pid_clean;
		$error="Unable to get patient names for patient";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		
		foreach($s as $row){
						$last_name=ucfirst(html($row['last_name']));
						$middle_name=ucfirst(html($row['middle_name']));
						$first_name=ucfirst(html($row['first_name']));
						$patient_number=html($row['patient_number']);
						
					}
		//get procedures for this treatment plan
		$sql=$error=$s='';$placeholders=array();
		$sql="select b.treatment_procedure_id, a.name, b.teeth, b.details ,invoice_number, unauthorised_cost, authorised_cost, quotation_number,
		null,
		case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'	end as status ,
		case b.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points'	end as pay_type, b.date_procedure_added,
		concat(c.first_name,' ',c.middle_name,' ',c.last_name) as doctor_name
		from tplan_procedure as b join procedures as a on b.procedure_id=a.id 
		left join users as c on c.id=b.created_by where b.pid=:pid order by b.treatment_procedure_id";
		$placeholders[':pid']=$pid_clean;
		$error="Unable to get treatments for patient";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount()>0){  
				
				echo "<br><br>	<table class='normal_table ecr1'><caption>Treatments Done for $first_name $middle_name $last_name - $patient_number</caption><thead><th class='treat_procedure_date3'>DATE<br>PLANNED</th>
					<th class=treat_planned_by3>PLANNED BY</th>
					<th class=treat_procedure3>PROCEDURE</th>
					<th class=treat_payment_method3>PAYMENT<br>METHOD</th><th class=treat_unaothorised_cost3>COST</th>
					<th class=treat_auothorised_cost3>AUTHORISED<br>COST</th>
					<th class='treat_notes_cell3 td_div_holder'>
						<div class='tplan_table 100_height'>
							<div class='tplan_table_row2'>
								<div class='treat_date 100_height'>DATE</div>
								<div class='treat_doctor'>DOCTOR</div>
								<div class='treat_notes'>NOTE</div>
							</div>
						</div>
					</th>
					<th class=treat_status3>STATUS</th></thead><tbody>";
			$i1=0;
			foreach($s as $row){
				$i1++;
				echo "<tr class=has_css_div>";?>
					<td><?php htmlout($row['date_procedure_added']);?> </td>
					<td><?php ucfirst(htmlout("$row[doctor_name]"));?></td>
					<td><?php 
							if($row['name']=='X-Ray'){htmlout("$row[details] $row[teeth]");}
							else {
								htmlout("$row[name] $row[teeth]");
								if ($row['details']!=''){echo "<br>";htmlout($row['details']); }
							}
					?></td>
					<td><?php htmlout($row['pay_type']);  ?> </td>
					<td><?php htmlout(number_format($row['unauthorised_cost'],2));  ?> </td>
					<td><?php
						if($row['pay_type']!='Insurance'){echo "N/A";}  
						elseif($row['authorised_cost']==''){echo "Un-Authorised";}
						elseif($row['authorised_cost']!=''){htmlout(number_format($row['authorised_cost']));}
							//echo $row['authorised_cost'];}
					?> </td>
					
					 <?php
					//now show the procedure doctore notes
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select b.when_added, concat(a.first_name,' ',a.middle_name,' ',a.last_name) as user_name1, b.notes from treatment_procedure_notes b, users a where b.treatment_procedure_id=:treatment_procedure_id
						   and b.doc_id=a.id order by b.id";
					$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
					$error2="Unable to get unfinished  procedure doctor notes";
					$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);					
					echo "<td class=td_div_holder>";	
						$i2=0;
						foreach($s2 as $row2){ 
							$date1=html($row2['when_added']);
							$user_name=ucfirst(html($row2['user_name1']));
							$notes=html($row2['notes']);
							$i2_class='';
							if($i2==0){$i2_class='no_top_border';}
							
							echo "<div class='tplan_table'>
									<div class='tplan_table_row2 div_in_td_1'>
										<div class='treat_date $i2_class'>$date1</div>
										<div class='treat_doctor $i2_class'>$user_name</div>
										<div class='treat_notes $i2_class'>$notes</div>
									</div>
								</div>";	
							$i2++;
						}
					?>
					</td>	
					<td><?php htmlout($row['status']);?></td>

					</tr><!-- end tplan_table_row -->
			<?php }		
			echo "</tbody></table>";
		
		} 
		//look for older swapped patient number
		$sql=$error=$s='';$placeholders=array();
		$sql="select old_pid from swapped_patients where new_pid=:pid ";
		$placeholders[':pid']=$pid_clean;
		$error="Unable to get old patient number for patient";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			foreach($s as $row){$pid_clean=html($row['old_pid']);}		
			//get_treatments_done($pdo, $pid_clean);
			get_treatments_done($pdo, $pid,  $encrypt);
		}
	}*/
	get_treatments_done($pdo, $pid,  $encrypt);
}	
?>	
</div>