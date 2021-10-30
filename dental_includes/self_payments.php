<?php
/*if(!isset($_SESSION))
{
session_start();
}*/




if(!userIsLoggedIn() or !userHasRole($pdo,50)){exit;}
echo "<div class='grid_12 page_heading'>NONE INSURANCE PAYMENTS</div>";
?>
<div class='grid-container completion_form'>
<?php	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
				if(isset($_SESSION['pay_id']) and $_SESSION['pay_id']!=''){
					print_receipt($pdo,$encrypt->encrypt($_SESSION['pay_id']), $encrypt);
					$_SESSION['pay_id']='';
					exit;
				}
				
			}
			elseif($_SESSION['result_class']=='bad'){
				echo "<div class='feedback hide_element'></div>";

				$_SESSION['pay_id']=$_SESSION['result_class']=$_SESSION['result_message']='';	
				
				
			}
		}
?>		
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			//$_SESSION['tab_name']="#self_payments";
			 include '../dental_includes/search_for_patient_no_session.php';
			 //echo "pid2 is $_SESSION[pid2] and pid is $_SESSION[pid]";

			 

if(isset($pid) and $pid!=''){

	//echo "$pid is $pid";
	//show_pt_statement($pdo,"$pid",$encrypt)	;
	echo "<div class='grid-100 toa_left_padding'><input type=button class='button_style printment' value='Print Statement' /></div>";
	//show_pt_statement_also_with_swapped_with_balance($pdo,"$pid",$encrypt)	;
	show_pt_statement_also_with_swapped($pdo,$pid,$encrypt);
	


		
	?>
		<div class=grid-100>
			<div class='grid-15 label alpha'>Select Action</div>
			<div class='grid-20 omega'><select class=payment_action><option></option>
									<option value='make_payment'>Record Payment</option>
									<option value='cash_pledge'>Record Payment Pledge</option>
								</select>
								
			</div>
			<div class=clear></div><br>
		</div>
		<div class='grid-100 cash_pledge'>
			<fieldset><legend>Payment Pledge</legend>
				<?php
							if($_SESSION['self_bal'] > 0){ ?>
								
								<form action="" method="POST"  name="" id="" class="patient_form">
									<?php $token = form_token(); $_SESSION['token_cash_plege'] = "$token"; 
										echo "<input type='hidden' name='token_ninye' id='token_ninye' value='$pid' />";
										$balance=$encrypt->encrypt("$_SESSION[self_bal]");
										echo "<input type='hidden' name='token_ninye2' id='token_ninye2' value='$balance' />";
									?>
									<input type="hidden" name="token_cash_plege"  value="<?php echo $_SESSION['token_cash_plege']; ?>" /> 
									
									<!-- check if this pt has any pledge that is already due -->
									<?php
										$sql=$error=$s='';$placeholders=array();
										$sql="select date_to_clear, balance , when_added from balance_clearance_date where pid=:pid and date_to_clear <= now()";					
										$error="Unable to get balance clearanace date";
										$placeholders['pid']=$pid_clean;
										$s = 	select_sql($sql, $placeholders, $error, $pdo);
										foreach($s as $row){
											$date_to_clear=html($row['date_to_clear']);
											$old_bal=number_format(html($row['balance']),2);
											$when_added=html($row['when_added']);
											echo "<div class='grid-100 error_response'>This patient had pledged to clear his cash balance of $old_bal on $date_to_clear</div><br>";
										}
									
								echo "<div class='grid-50 label'>Please specify date when the remaining balance of KES: $_SESSION[self_bal] will be cleared </div>
									<div class='grid-10'><input type=text name=date_clear_bal class=date_picker_no_past /></div>	
									<div class=clear></div><br>
									<div class='grid-50 label '><span class=put_right>Comment</span> </div>
									<div class='grid-40'><textarea name=comment width=100%></textarea></div>
									<div class='grid-10'><input type=submit value=Submit /></form></div>	
									<div class=clear></div>";
							}
							else{echo "<div class='grid-100 label'>This patient has no cash balance</div>";}
							
						
				?>
			</fieldset>
		</div>
		<div class='grid-100 make_payment'>
				<fieldset><legend>Payment</legend>
				<!-- check if this pt has any pledge that is already due -->
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql="select date_to_clear, balance , when_added from balance_clearance_date where pid=:pid and date_to_clear <= now()";					
					$error="Unable to get balance clearanace date";
					$placeholders['pid']=$pid_clean;
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$date_to_clear=html($row['date_to_clear']);
						$old_bal=number_format(html($row['balance']),2);
						$when_added=html($row['when_added']);
						echo "<div class='grid-100 error_response'>This patient had pledged to clear his cash balance of $old_bal on $date_to_clear</div><br>";
					}
				?>
			<form action="" method="POST"  name="" id="" class="patient_form">

			
							<?php $token = form_token(); 
									//$token = "b2f89b01a6c2a413212ae096a73658cd51d0ad52";
								$_SESSION['token_non_ins_pay'] = "$token"; 
								echo "<input type='hidden' name='token_ninye' id='token_ninye' value='$pid' />";
							?>
						<input type="hidden" name="token_non_ins_pay"  value="<?php echo $_SESSION['token_non_ins_pay']; ?>" />
						

				
				<div class=grid-10><label for="" class="label">Amount Paid</label></div>
				<div class='grid-10'><input type=text  name=amount class=self_amount /></div>	
				
				<div class='grid-10 '><label for="" class="label">Payment Type</label></div>
				<div class='grid-15'><?php  
					$sql=$error=$s='';$placeholders=array();
					$sql="select id,name from payment_types where id!=7 and id!=8 and id!=9  order by name";					
					$error="Unable to select payment types";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					echo "<select class='input_in_table_cell payment_type' name=non_ins_payment_type ><option></option>";
					foreach($s as $row){
						$name=html($row['name']);
						$id=$encrypt->encrypt(html($row['id']));
						echo "<option value='$id'>$name</option>";
					}
					echo "</select>"; ?>
				</div>
				
				
				<div class='remove-inside-padding  '>
					<!-- credit transfer-->
					<div class='credit_transfer grid-55'>
						
					</div>		
				
					<!-- cheque number-->
					<div class='cheque_number'>
						<div class='grid-15 prefix-5'><label for="" class="label">Cheque Number</label></div>
						<div class='grid-25'><input type=text name=cheque_number /></div>	
					</div>
					
					<!-- mpesa number-->
					<div class='mpesa_number'>
						<div class='grid-15 prefix-5'><label for="" class="label">Mpesa Tx. Number</label></div>
						<div class='grid-25'><input type=text name=mpesa_number /></div>	
					</div>

					<!-- visa number-->
					<div class='visa_number'>
						<div class=clear></div><br>
						<div class='grid-10 label'>Bank Name</div>
						<div class='grid-25'><select name=bank_name><option></option>
						<?php
							//now show current visa banks
							$sql=$error=$s='';$placeholders=array();
							$sql="select id,name from visa_banks where listed=0 order by name";
							$error="Unable to select visa banks";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){
									$name=html($row['name']);
									$val=$encrypt->encrypt(html($row['id']));//
									echo "<option value=$val>$name</option>";
							}
								
						?>
						</select></div>
						<div class='grid-15 '><label for="" class="label">VISA Tx. Number</label></div>
						<div class='grid-25'><input type=text name=visa_number /></div>	
					</div>	

					<!-- waiver reason-->
					<div class='waiver_reason'>
						<div class='grid-15 prefix-5'><label for="" class="label">Waiver Reason.</label></div>
						<div class='grid-25'><textarea name=waiver_reason width='100%'></textarea></div>	
					</div>				
				</div>
				
				<div class=clear></div><br>		
				<div class=' next_payment_div grid-100 no_padding'>
						
				</div>

			<div class='grid-100'><input type=submit  value='Submit Payment' /></div>
			</form>
				</fieldset>
		</div>	
<?php
	}
?>
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>