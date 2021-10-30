<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,51)){exit;}
echo "<div class='grid_12 page_heading'>EDIT TREATMENT PLAN</div>";

//this will unset the patient contact session variables if not pid is currenlty set
//if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_completion();}
//if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){get_patient_completion($pdo,'pid',$_SESSION['pid']);}
if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
	$_SESSION['result_class']!=''){
		if($_SESSION['result_class']=='success_response'){
			echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';	
		}
}
?>
<div class='grid-container '>
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
		include '../dental_includes/search_for_patient_no_session.php';
		if(isset($pid) and $pid!=''){}
		//set tab_name to beused in seaerch form submission
		
if(isset($pid_clean) and $pid_clean!=''){
	$result = check_if_swapped($pdo,'pid',$pid_clean);
	if($result!='good'){
		$swapped="$result and cannot be edited";
		echo "<div class='grid-100 error_response'>$result</div>";
	}
	elseif($result=='good'){$swapped='';}
}


	if(isset($pid) and $pid!=''){
		//look for any unfinished or uninvoiced treatment plans
		//make this start from installation date coz xray format is deiffernemt
		$sql=$error1=$s='';$placeholders=array();
		$sql="select distinct a.when_added, a.tplan_id from tplan_id_generator a where a.pid=:pid order by tplan_id desc";
		$error="Unable to get unfinished treatment plans";
		$placeholders[':pid']=$pid_clean;
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount()>0){//show table ?>
		<div class=tplan_table><div class=tplan_table_caption><?php echo "TREATMENT PLANS FOR: $patient_number - $first_name $middle_name $last_name ";?></div>
		<div class=tplan_table_row2>
			<div class='tplan_created white_text'>Date<br>Created</div><div class='tplan_id  white_text'>Treatment<br>Plan<br>No.</div>
			
				<div class='tplan_procedure3_1 white_text'>Procedure</div><div class='tplan_status3_1 white_text'>Status</div>
				<div class='tplan_last_seen3_1 white_text'>Last<br>Seen</div>
			
			<div class='tplan_select white_text'>Select</div>
		</div>
		</div>
		<!--<div class=tplan_table>
		<div class=tplan_table_row>
		<div class='tplan_created white_text'>Date<br>Created</div><div class='tplan_id  white_text'>Treatment<br>Plan<br>No.</div>
			
				<div class='tplan_procedure3_1 white_text'>Procedure</div><div class='tplan_status3_1 white_text'>Status</div>
				<div class='tplan_last_seen3_1 white_text'>Last<br>Seen</div>
			
			<div class='tplan_select white_text'>Select</div>
			</div> -->
			<div class=tplan_table>
		<?php $token = form_token(); $_SESSION['token_etp1_patient'] = "$token";  
		foreach($s as $row){
			echo "<div class=tplan_table_row>";
				echo "<div class=tplan_created>";htmlout($row['when_added']); echo "</div>";//date created
				echo "<div class=tplan_id>";htmlout($row['tplan_id']); echo "</div>";//tplan id
				//now show the procedure
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select b.treatment_procedure_id, a.name, b.teeth, b.details , case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'
						end as status , b.pay_type, b.invoice_number , b.alias from procedures a, tplan_procedure b where b.tplan_id=:tplan_id and 
					  b.procedure_id=a.id";
				$placeholders2[':tplan_id']=$row['tplan_id'];
				$error2="Unable to get unfinished treatment plan procedure";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);					
				echo  "<div class=tplan_procedure_row3>";//tplan_table_row3"<div class=tplan_procedure_row>";	
				$tplan_finished_flag=true;
				$has_invoice=false;
				foreach($s2 as $row2){
					if($row2['invoice_number'] != ''){$has_invoice=true;}
					//check if alias
					$alias='';
					if($row2['alias']==1){$alias=" -- Alias ";}
				?>
					<!--<div class=tplan_procedure_table>-->
						
						<div class=tplan_table_row>
							<div class=tplan_procedure3><?php htmlout("$row2[name] $alias"); 
								if ($row2['teeth']!=''){echo "<br>Teeth: ";htmlout($row2['teeth']); }
								if ($row2['details']!=''){echo "<br>";htmlout($row2['details']); }
							?></div>
							<div class=tplan_status3><?php 
								if($row2['status'] != "Done"){htmlout($row2['status']);$tplan_finished_flag=false;}
								elseif($row2['status'] == "Done"){echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      Done &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;     ";}
								if($row2['status'] == "Done" and $row2['pay_type'] == 1 and $row2['invoice_number'] == '' ){
									echo "<br>Uninvoiced";
									}
							?></div>
							<div class=tplan_last_seen3><?php
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
				if(!$tplan_finished_flag){
					if($swapped==''){
					//check i f this user can 
					?>
						<input type=button value='Edit' class='edit_tplan button_style button_in_table_cell' />
						<input type="hidden"   value="<?php echo $encrypt->encrypt($row['tplan_id'])."ninye$pid"; ?>" />	
					<?php 
					}
					elseif($swapped!=''){echo "";}
				}
				else{echo "Completed";}
				echo "</div>";//submit
			echo "</div>";//	tplan_table_row
		}
		echo "</div>";//end tplan_table
		}
		else{
			$result_class="error_response";
			$var=html("$pid_clean");
			$result_message="Patient number $var has no treatment plans";
			echo "<div class='$result_class'>$result_message</div>";
		}
	}//end pid if	 
	?>
	
			
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>