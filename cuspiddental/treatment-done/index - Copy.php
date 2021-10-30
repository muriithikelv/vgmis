<?php
/*if(!isset($_SESSION))
{
session_start();
}*//*
include_once  '../../dental_includes/magicquotes.inc.php'; 
include_once   '../../dental_includes/db.inc.php'; 
include_once   '../../dental_includes/DatabaseSession.class.php';
include_once   '../../dental_includes/access.inc.php';
include_once   '../../dental_includes/encryption.php';
include_once    '../../dental_includes/helpers.inc.php';*/
include_once     '../../dental_includes/includes_file.php';
if(!userIsLoggedIn() or !userHasRole($pdo,20)){

	   ?>
<script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
		<?php
		exit;}
		
echo "<div class='grid_12 page_heading'>TREATMENT DONE</div>";

//this will unset the patient contact session variables if not pid is currenlty set
//if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_completion();}
//if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){get_patient_completion($pdo,'pid',$_SESSION['pid']);}
?>
<div class='grid-container '>
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="#treatment-done";
			 include '../../dental_includes/search_for_patient.php';
			
			if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){
				$pid_bal="pid_".$_SESSION['pid'];
				$_SESSION["$pid_bal"]=array();
				$result=show_pt_statement_brief($pdo,$encrypt->encrypt("$_SESSION[pid]"),$encrypt);
				$data=explode('#',"$result");
				$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
				show_patient_balance($pdo,$_SESSION['pid'],$encrypt);
				
			}
			if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_examination();exit;}
		
 if(isset($_SESSION['tplan_id']) and $_SESSION['tplan_id']!=''){
		//check  date tplan_id was raised
		$tplan_date='';
		$sql=$error1=$s='';$placeholders=array();
		$sql="select when_added from tplan_id_generator where tplan_id=:tplan_id";
		$error="Unable to get tplan date";
		$placeholders[':tplan_id']=$_SESSION['tplan_id'];
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){$tplan_date=html($row['when_added']);}
		
		//check if pre-auth or smart is needed for this patient
		$pre_auth_needed=$smart_needed='';
		$sql=$error1=$s='';$placeholders=array();
		$sql="select pre_auth_needed, smart_needed from covered_company a, patient_details_a b where b.type=a.insurer_id and b.company_covered=a.id
			and b.pid=:pid";
		$error="Unable to check if pre-auth is needed";
		$placeholders[':pid']=$_SESSION['pid'];
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$pre_auth_needed=html($row['pre_auth_needed']);
			$smart_needed=html($row['smart_needed']);
		}
		
		//get procedures for this treatment plan
		$sql=$error=$s='';$placeholders=array();
		$sql="select b.treatment_procedure_id, a.name, b.teeth, b.details ,invoice_number, unauthorised_cost, authorised_cost, quotation_number,
		null,
		case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'	end as status ,
		case b.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points'	end as pay_type, b.treatment_source, b.alias
		from procedures a, tplan_procedure b where b.tplan_id=:tplan_id and 
			  b.procedure_id=a.id order by b.treatment_procedure_id";
		$placeholders[':tplan_id']=$_SESSION['tplan_id'];
		$error="Unable to get unfinished treatment plan procedures";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount()>0){
		//show labs for this patient that are with technician
		get_labs_with_technician($pdo, $_SESSION['pid']);
		echo "<div class='no_padding grid-100' id=undispatched_labs>";
				undispatched_finished_lab_work($pdo,$_SESSION['pid'],$encrypt);
		echo "</div>";	
		//get any invoices that may need approval by admin meaning that they were either rejected or partially approved
		echo "<div class='no_padding grid-100' id='tdone_invoice_admin_approve' >";
				partially_approved_invoices($pdo,$_SESSION['pid'],$encrypt);
		echo "</div>";
		//<a href='' class='link_color' >Recall Date</a> | 
		echo "<div class='grid-100 div_shower44'></div><div class='grid-100 no_padding dialog_with_tab'></div>
		<a href='' class='link_color tdone_appointment' >Book Appointment</a> | <a href='' class='link_color prescribe' >Prescription</a> | 
		<a href='' class='link_color follow_up' >Follow Up</a> | <a href='' class='link_color  lab_request' >Lab Request</a> | 
		 <a href='' class='link_color pt_statement' >Statement</a> |  <a href='' class='link_color treatment_history' >Treatment History</a> | 
		 <a href='' class='link_color tdone-invoice' >Invoices</a> |  <a href='' class='link_color tdone-cadcam' >CADCAM</a>  ";
		 ?>

		<form action="#treatment-done" method="POST"  name="" id="" class="patient_form2">
			<?php $token = form_token(); $_SESSION['token_g2_patient'] = "$token"; ?>
			<input type="hidden" name="token_g2_patient"  value="<?php echo $_SESSION['token_g2_patient']; ?>" />	
		<table class='normal_table ecr1'><caption>Unfinished treatment plans</caption><thead><th class='treat_procedure_count2'></th>
		<th class=treat_procedure2>Procedure</th>
		<th class=treat_payment_method2>Payment<br>Method</th><th class=treat_unaothorised_cost2>Cost</th>
		<th class=treat_auothorised_cost2>Authorised<br>Cost</th>
		<th class='treat_notes_cell2 td_div_holder'>
			<div class='tplan_table 100_height'>
				<div class='tplan_table_row2'>
					<div class='treat_date 100_height'>DATE</div>
					<div class='treat_doctor'>DOCTOR</div>
					<div class='treat_notes'>NOTE</div>
				</div>
			</div>
		</th>
		<th class=treat_status2>STATUS</th><th class=treat_invoice2><?php
				if($_SESSION['insured']=='YES'){echo "Raise<br>Invoice";}
				elseif($_SESSION['insured']=='NO'){echo "Raise<br>Quotation";}
			?></th></thead><tbody>
		<?php				
		$i1=0;
		foreach($s as $row){
			$i1++;
			$class_ts='';
			if($row['treatment_source']==1 or $row['treatment_source']==2){$class_ts=' blue_shade_background';}
					 
			echo "<tr class='has_css_div $class_ts'>";?>
				<td><?php echo "$i1";
					$procedure_number="procedure$i1";
					$treatment_procedure_id=$encrypt->encrypt($row['treatment_procedure_id']);
					 echo "<input type=hidden name=$procedure_number value='$treatment_procedure_id' />";
				?> </td>
				<td><?php htmlout($row['name']); 
					if ($row['teeth']!=''){echo " ";htmlout($row['teeth']); }
					if ($row['details']!=''){echo "<br>";htmlout($row['details']); }
					$alias='';
					if($row['alias']==1){echo " -- Alias ";}
				?></td>
				<td><?php htmlout($row['pay_type']);  ?> </td>
				<td><?php htmlout(number_format($row['unauthorised_cost'],2));  ?> </td>
				<td><?php
					if($row['pay_type']!='Insurance'){echo "N/A";}  
					else{
						if($pre_auth_needed=='YES' or $smart_needed=='YES'){echo $row['authorised_cost'];}
						elseif($pre_auth_needed!='YES' and $smart_needed!='YES'){echo "N/A";}
						}
						//echo $row['authorised_cost'];}
				?> </td>
				
				 <?php
				//now show the procedure doctore notes
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select b.when_added, concat(a.first_name,' ',a.middle_name,' ',a.last_name) as user_name1, b.notes from treatment_procedure_notes b, users a where b.treatment_procedure_id=:treatment_procedure_id
					   and b.doc_id=a.id and  b.notes!='' order by b.id";
				$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
				$error2="Unable to get unfinished  procedure doctor notes";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);					
				echo "<td class='td_div_holder '>";	
					$i2=0;
					foreach($s2 as $row2){ 
						$date1=html($row2['when_added']);
						$user_name=ucfirst(html($row2['user_name1']));
						$notes=html($row2['notes']);
						$i2_class='';
						if($i2==0){$i2_class='no_top_border';}
						
						echo "<div class='tplan_table'>
								<div class='tplan_table_row2 div_in_td_1 $class_ts'>
									<div class='treat_date $i2_class $class_ts'>$date1</div>
									<div class='treat_doctor $i2_class'>$user_name</div>
									<div class='treat_notes $i2_class'>$notes</div>
								</div>
							</div>";	
						$i2++;
					}
				
					if($row['status']!='Done'){
							$note="note$i1";
							?>
							<div class=tplan_table>
							<?php echo "	<div class='tplan_table_row2 $class_ts'>"; ?>
									<div class=treat_date><?php echo date('Y-m-d'); ?></div>
									<div class=treat_doctor><?php htmlout("$_SESSION[logged_in_user_names]" ); ?></div>
									<div class=treat_notes><?php echo "<textarea  rows='' name=$note ></textarea>"; ?></div>
								</div>
							</div>	 
							<?php
					} ?>
				</td>	
				<td><?php
					if($row['status']=='Done'){echo 'Done';}
					else{
						$status="status$i1";
						$not_start_val=$encrypt->encrypt("0");
						$partially_done_val=$encrypt->encrypt("1");
						$done=$encrypt->encrypt("2");
						echo "<select name=$status>";
						if($row['status']=='Not Started'){
							$not_start = " selected ";$partially_done="";
							echo "<option $not_start value='$not_start_val'>Not Started</option>
								<option $partially_done value='$partially_done_val'>Partially Done</option>
								<option value='$done'>Done</option>";
						}
						elseif($row['status']=='Partially Done'){
							$not_start = "  ";$partially_done=" selected ";
							echo "<option $partially_done value='$partially_done_val'>Partially Done</option>
								<option value='$done'>Done</option>";
						}
						
							
						echo "</select>";
					}
				?></td>
				<td><?php
					//this is for invoice
					if($row['invoice_number']!=''){
						$invoice_num=html($row['invoice_number']);
						echo "<input type=button value='$invoice_num' class='invoice_no button_style button_in_table_cell show_invoice' />";
					}
					elseif($row['invoice_number']=='' and $row['pay_type']=="Insurance"){
						$raise_invoice="raise_invoice$i1";
						$change_to_cash="change_to_cash$i1";
						$append_invoice="append_invoice$i1";
						$new=$encrypt->encrypt("new");
						$cash_string1=$cash_string2='';
						if($tplan_date <= '2014-03-07' ){
							$cash_string1=" Raise Invoice";
							$cash_string2= "<br><input class='lipa_cash $raise_invoice' type=checkbox value='$treatment_procedure_id' name=$change_to_cash />Pay Cash";
						}
						echo "<input class='$change_to_cash raise_invoice' type=checkbox value='$new' name=$raise_invoice /> $cash_string1";
						echo "<input type=hidden name=$append_invoice id=$append_invoice value='' />";
						echo "$cash_string2";
						
					}				
					//this is for quotation\
					if($row['quotation_number']!=''){
						$quotation_num=html($row['quotation_number']);
						echo "<input type=button value='$quotation_num' class='quotation_no button_style  button_in_table_cell ' />";
					}
					elseif($row['quotation_number']=='' and $_SESSION['insured']=='NO' ){//$row['pay_type']==2
						$raise_quotation="raise_quotation$i1";
						$append_quotation="append_quotation$i1";
						$new=$encrypt->encrypt("new");
						echo "<input class=raise_quotation type=checkbox value='$new' name=$raise_quotation />";
						echo "<input type=hidden name=$append_quotation id=$append_quotation value='' />";
					}
					
				?></td>
				</tr><!-- end tplan_table_row -->
		<?php }		
		echo "</tbody></table>";
		
		} 
	echo "<div class=clear></div><br>";
	$nisiana=$encrypt->encrypt("$i1");
	echo "<div ><input type=hidden name=nisiana  value='$nisiana' />".show_submit($pdo,'','put_right submit_tdone')."</form></div>"; 
	echo "<div id='append_invoice' ></div>";
	
 exit;
 }
		/*$gen_array=array();
		$sql=$error1=$s='';$placeholders=array();
		$sql="select tplan_id,pid from tplan_id_generator ";
		$error="Unable to get unfinished treatment plans";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){$gen_array[]="$row[tplan_id]-$row[pid]";}
		
		$sql=$error1=$s='';$placeholders=array();
		$sql="select tplan_id,pid from tplan_procedure ";
		$error="Unable to get unfinished treatment plans";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			if (!in_array("$row[tplan_id]-$row[pid]", $gen_array)) {echo "<br>$row[tplan_id]-$row[pid]";}
		}
		*/
		//look for any unfinished or uninvoiced treatment plans
		$sql=$error1=$s='';$placeholders=array();
		$ongoing_tplans_array=array();
		$sql="select distinct a.when_added,a.tplan_id from tplan_id_generator a, tplan_procedure b where a.pid=:pid and a.tplan_id=b.tplan_id and
             ((b.pay_type=1 and b.invoice_number is null) or b.status!=2)";
		$error="Unable to get unfinished treatment plans";
		$placeholders[':pid']=$_SESSION['pid'];
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount()>0){//show table ?>
		<div class=tplan_table><div class=tplan_table_caption>UNFINISHED/UNINVOICED TREATMENT PLANS</div>
		<div class=tplan_table_row2>
			<div class='tplan_created white_text'>Date<br>Created</div><div class='tplan_id  white_text'>Treatment<br>Plan<br>No.</div>
			<div class='tplan_procedure white_text'>Procedure</div><div class='tplan_status white_text'>Status</div>
			<div class='tplan_last_seen white_text'>Last<br>Seen</div><div class='tplan_select white_text'>Select</div>
		</div>
		</div>
		<div class=tplan_table>
		<?php $token = form_token(); $_SESSION['token_g_patient'] = "$token";  
		//$ongoing_tplans_array=array();
		foreach($s as $row){
			echo "<div class=tplan_table_row>";
				echo "<div class=tplan_created>";htmlout($row['when_added']); echo "</div>";//date created
				echo "<div class=tplan_id>";htmlout($row['tplan_id']); echo "</div>";//tplan id
				$ongoing_tplans_array[]=$row['tplan_id'];
				//now show the procedure
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select b.treatment_procedure_id, a.name, b.teeth, b.details , case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'
						end as status , b.pay_type, b.invoice_number ,b.treatment_source, b.alias from procedures a, tplan_procedure b where b.tplan_id=:tplan_id and 
					  b.procedure_id=a.id";
				$placeholders2[':tplan_id']=$row['tplan_id'];
				$error2="Unable to get unfinished treatment plan procedure";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);					
				echo "<div class=tplan_procedure_row>";			
				foreach($s2 as $row2){ 
					//check if the tplan is imported from xrays or tdone and not tplan
					$class_ts='';
					if($row2['treatment_source']==1 or $row2['treatment_source']==2){$class_ts=' blue_shade_background';}
					//check if alias
					$alias='';
					if($row2['alias']==1){$alias=" -- Alias ";}
					?>
					<!--<div class=tplan_procedure_table>-->
						
						<?php echo "<div class='tplan_table_row  $class_ts'>"; ?>
							<div class='tplan_procedure'><?php htmlout("$row2[name] $alias");
								if ($row2['teeth']!=''){echo "<br>Teeth: ";htmlout($row2['teeth']); }
								if ($row2['details']!=''){echo "<br>";htmlout($row2['details']); }
							?></div>
							<div class=tplan_status><?php 
								if($row2['status'] != "Done"){htmlout($row2['status']);}
								elseif($row2['status'] == "Done"){echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      Done &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;     ";}
								if($row2['status'] == "Done" and $row2['pay_type'] == 1 and $row2['invoice_number'] == '' ){
									echo "<br>Uninvoiced";
									}
							?></div>
							<div class=tplan_last_seen><?php
							//get date last seen for this procedure
							$sql3=$error3=$s3='';$placeholders3=array();
							$sql3="select max(id),when_added from treatment_procedure_notes where treatment_procedure_id=:treatment_procedure_id
							              group by treatment_procedure_id";
							$placeholders3[':treatment_procedure_id']=$row2['treatment_procedure_id'];
							$error3="Unable to get unfinished treatment plan procedure last seen recorded date";
							$s3 = select_sql($sql3, $placeholders3, $error3, $pdo);					
							foreach($s3 as $row3){htmlout($row3['when_added']);}							
							 ?></div>
						</div>	 
						<!--</div><!-- end row -->
				<!--	</div><!-- end table-->
					
				<?php }
				echo "</div>";
				
				echo "<div class=tplan_id>"; 
				if($swapped==''){
					?>
						<form action="#treatment-done" method="POST"  name="" id="" class="patient_form2">
							
							<!-- ninye is encrypted tplan_id -->
							<input type="hidden" name="ninye"  value="<?php echo $encrypt->encrypt($row['tplan_id']); ?>" />	
							<input type="hidden" name="token_g_patient"  value="<?php echo $_SESSION['token_g_patient']; ?>" />	
							<?php show_submit($pdo,'','kati_kati'); ?>
						</form>
					<?php 
				}
				elseif($swapped!=''){echo "";}
				echo "</div>";//submit
			echo "</div>";//	tplan_table_row
		}
		echo "</div>";//end tplan_table
		}
		echo "<br>";
		
		//patient treatment history
		get_treatments_done_exclude_ongoing($pdo, $encrypt->encrypt($_SESSION['pid']),$encrypt,$ongoing_tplans_array);
		//look for older swapped patient number
		/*$sql=$error=$s='';$placeholders=array();
		$sql="select old_pid from swapped_patients where new_pid=:pid ";
		$placeholders[':pid']=$_SESSION['pid'];
		$error="Unable to get old patient number for patient";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			echo "<div class='grid-100 label'>TREATMENTS UNDER SWAPPED PATIENT NUMBERS</div>";
			foreach($s as $row){$pid_clean=html($row['old_pid']);}		
			get_treatments_done($pdo, $encrypt->encrypt($pid_clean),$encrypt);
		}*/
		 
	?>
	
		
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>