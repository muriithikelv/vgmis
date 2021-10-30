<?php
/*if(!isset($_SESSION))
{
session_start();
}*/

if(!userIsLoggedIn() or !userHasRole($pdo,118)){exit;}
echo "<div class='grid_12 page_heading'>UNAUTHORISE INVOICE</div>";

//this will unset the patient contact session variables if not pid is currenlty set
//if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_completion();}
//if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){get_patient_completion($pdo,'pid',$_SESSION['pid']);}
?>
<div class='grid-container '>
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
		include '../dental_includes/search_for_patient_no_session.php';
		if(isset($pid) and $pid!=''){}
		//set tab_name to beused in seaerch form submission
	
	//unauthorise invoice	
	if(isset($_POST['token_ui1']) and isset($_SESSION['token_ui1']) and $_POST['token_ui1']==$_SESSION['token_ui1']){
		$result=$encrypt->decrypt($_POST['unauthorise_invoice']);
		$data=explode('#',"$result");
		$invoice_id=$data[0];
		$invoice_number='';
		
		//get invoice_number
		$sql=$error=$s='';$placeholders=array();
		$sql="select invoice_number from unique_invoice_number_generator where id=:invoice_id";
		$error="Unable to unauthorise invoice number";
		$placeholders[':invoice_id']=$invoice_id;
		$s = select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){$invoice_number=html($row['invoice_number']);}
		if($invoice_number!=''){
			try{
					$pdo->beginTransaction();
					//unauthorise
					$sql=$error=$s='';$placeholders=array();
				//	$sql="update invoice_authorisation set authorisation_received=null , smart_run = null, amount_authorised=null ,
				//		  comments=null, smart_amount=null where invoice_id=:invoice_id";
					$sql="delete from invoice_authorisation where invoice_id=:invoice_id";
					$error="Unable to unauthorise invoice";
					$placeholders[':invoice_id']=$invoice_id;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					
					//unauthorise cost tonull
					$sql=$error=$s='';$placeholders=array();
					$sql="update tplan_procedure set authorised_cost=null where invoice_id=:invoice_id";
					$error="Unable to unauthorise invoice cost";
					$placeholders[':invoice_id']=$invoice_id;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					
					if($s){$tx_result = $pdo->commit();}
					elseif(!$s){$pdo->rollBack();$tx_result=false;}
					if($tx_result){echo "<div class='success_response'>Authorization/SMART status for invoice $invoice_number has been reset</div>";}
				}
				catch (PDOException $e)
				{
				$pdo->rollBack();
				//$message="bad#Unable to edit Lab Technicians  ";
				}
		}
	}

	
	if(isset($pid) and $pid!=''){
		//look for any unfinished or uninvoiced treatment plans
		//make this start from installation date coz xray format is deiffernemt
		$sql=$error1=$s='';$placeholders=array();
		$sql="select  min(a.date_invoiced),sum(unauthorised_cost),sum(authorised_cost) ,a.invoice_id, a.invoice_number from tplan_procedure a 
			where a.pid=:pid and invoice_id > 0 group by invoice_id order by invoice_id ";
		$error="Unable to get invoices";
		$placeholders[':pid']=$pid_clean;
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount()>0){//show table ?>
		<div class=invoice_table><div class=tplan_table_caption><?php echo "INVOICES FOR: $patient_number - $first_name $middle_name $last_name ";?></div>
		<div class=invoice_table_row2>
			<div class='invoice_created white_text'>DATE<br>RAISED</div><div class='invoice_id  white_text'>INVOICE No.</div>
			<div class='invoice_id  white_text'>BILLED COST</div><div class='invoice_id  white_text'>AUTHORISED<br>COST</div>
				<div class='invoice_procedure3_1 white_text'>TREATMENT PROCEDURE</div><div class='invoice_status3_1 white_text'>STATUS</div>
				<div class='invoice_last_seen3_1 white_text'>LAST<br>SEEN</div><div class='invoice_select white_text'>SELECT</div>
		</div>
		</div>

		
		<div class=invoice_table>
		<?php $token = form_token(); $_SESSION['token_ui1'] = "$token";  
		foreach($s as $row){
				//deduct any co_payment
				$invoice_id=html($row['invoice_id']);
				$co_payment=0;
				
				//check if the entry is for an aliased invoice
				$is_invoice_aliased=0;
				$aliased='';
				if(  $row['invoice_id'] > 0){
						$is_invoice_aliased = is_invoice_id__alias($pdo,$row['invoice_id']);
						if($is_invoice_aliased == 1){$aliased="<br>Alias";}
				}
			
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select amount from co_payment where invoice_number=:invoice_number";
				$error2="Unable to get co-payment";
				$placeholders2[':invoice_number']=$row['invoice_id'];
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
				foreach($s2 as $row2){$co_payment=html($row2['amount']);}
				
				$billed_cost=number_format((html($row[1]) - $co_payment),2);
				if($row[2]!=''){$authorised_cost=number_format(html($row[2]) - $co_payment,2);}
				else{$authorised_cost=$row[2];}
				$invoice_number=html($row['invoice_number']);
				
				//get invoice status
				$invoice_status='';
				$invoice_status=get_invoice_status($row['invoice_id'],$pdo);
				//this will make unpaid invoices that were raised before 6th march migration night editable
				/*if(("$invoice_status" == 'Pre-auth sent' or "$invoice_status" == 'Authorised' or 
					"$invoice_status" == 'SMART checked')   and "$row[0]" <= '2014-03-06'){*/
				$invoice_status=str_replace(array('\r','\n'), '', "$invoice_status");
				if ( (strpos($invoice_status,'Authorised') !== false or strpos($invoice_status,'Pre-auth sent') !== false
					or strpos($invoice_status,'SMART checked') !== false) and "$row[0]" <= '2014-03-06') {
					$invoice_status='';
				}
				echo "<div class=invoice_table_row>";
				echo "<div class=invoice_created>";htmlout($row[0]); echo "</div>";//date created
				echo "<div class='invoice_id  padding_kidogo'>$invoice_number $aliased </div>";//tplan id
				echo "<div class='invoice_id padding_kidogo'>$billed_cost</div>";
				echo "<div class='invoice_id padding_kidogo'>$authorised_cost</div>";
				//now show the procedure
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select b.treatment_procedure_id, a.name, b.teeth, b.details , case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'
						end as status  from procedures a, tplan_procedure b where b.invoice_id=:invoice_id and 
					  b.procedure_id=a.id";
				$placeholders2[':invoice_id']=$row['invoice_id'];
				$error2="Unable to get invoice procedures";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);					
				echo  "<div class=invoice_procedure_row3>";//tplan_table_row3"<div class=tplan_procedure_row>";	
				//$tplan_finished_flag=true;
				//$has_invoice=false;
				foreach($s2 as $row2){
					//if($row2['invoice_number'] != ''){$has_invoice=true;}
				?>
					<!--<div class=tplan_procedure_table>-->
						
						<div class=invoice_table_row>
							<div class=invoice_procedure3><?php htmlout($row2['name']);
								if ($row2['teeth']!=''){echo "<br>Teeth: ";htmlout($row2['teeth']); }
								if ($row2['details']!=''){echo "<br>";htmlout($row2['details']); }
							?></div>
							<div class=invoice_status3><?php htmlout($row2['status']);
							//	if($row2['status'] != "Done"){htmlout($row2['status']);}///$tplan_finished_flag=false;}
							//	elseif($row2['status'] == "Done"){echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      Done &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;     ";}

							?></div>
							<div class=invoice_last_seen3><?php
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
				
				echo "<div class='invoice_id'>";
				if($invoice_status==''){
					echo "&nbsp;";
				}//check if it has been authorised
				else{
					//htmlout("$invoice_status");
					$pos = strpos("$invoice_status", "Authorised");
					if ($pos === false) { //not authorised
						$pos2 = strpos("$invoice_status", "SMART");
						if ($pos2 === false) { //not smart
							htmlout("$invoice_status");
						}
						else{ //has smart
							$token_value=form_token();
							$token = "".$invoice_id."#"."$token_value";
							$token=$encrypt->encrypt($token);
							?>
								<form action="" class='' method="post" name="" id="">
									<input type="hidden" name="token_ui1"  value='<?php echo "$_SESSION[token_ui1]"; ?>' />
									<input type="hidden" name="unauthorise_invoice"  value='<?php echo "$token"; ?>' />
									<input type=submit value='Unauthorize' />
								</form>	
							<?php
						}
					} else { //has authorised
							$token_value=form_token();
							$token = "".$invoice_id."#"."$token_value";
							$token=$encrypt->encrypt($token);
							?>
								<form action="" class='' method="post" name="" id="">
									<input type="hidden" name="token_ui1"  value='<?php echo "$_SESSION[token_ui1]"; ?>' />
									<input type="hidden" name="unauthorise_invoice"  value='<?php echo "$token"; ?>' />
									<input type=submit value='Unauthorize' />
								</form>	
							<?php
						
					}
				}
				echo "</div>";//submit
			echo "</div>";//	tplan_table_row
		}
		echo "</div>";//end tplan_table
		}
		else{
			$result_class="error_response";
			$var=html("$patient_number");
			$result_message="Patient number $var has no invoices";
			echo "<div class='$result_class'>$result_message</div>";
		}
	}//end pid if	 
	?>
	
			
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>