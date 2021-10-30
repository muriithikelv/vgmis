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
//include_once     '../../dental_includes/includes_file.php';
if(!userIsLoggedIn() or !userHasRole($pdo,54)){exit;}
echo "<div class='grid_12 page_heading'>XRAY REFERRALS</div>";

	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';
				//show receipt
				if(isset($_SESSION['pay_id']) and $_SESSION['pay_id']!=''){
					print_receipt($pdo,$encrypt->encrypt($_SESSION['pay_id']), $encrypt);
					$_SESSION['pay_id']='';
					exit;
				}
				//show invoice
				if(isset($_SESSION['inv_no']) and $_SESSION['inv_no']!=''){
					display_invoice($pdo,$_SESSION['inv_no']);
					$_SESSION['inv_no']='';
					exit;
				}
			}
			elseif($_SESSION['result_class']=='bad'){
				echo "<div class='feedback hide_element'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
		}


?>
<div class=grid-container>
	<div class=grid-100 >
	<div class='feedback hide_element'></div>

	<form action="#xray_refs" class="patient_form" method="POST"  name="" >
	
			<fieldset><legend>Patient Details</legend>
		
				<!--first name-->
				<div class='grid-10'>
					<?php $token = form_token(); $_SESSION['token_xr1'] = "$token";  ?>
	<input type="hidden" name="token_xr1"  value='<?php echo "$_SESSION[token_xr1]"; ?>' />
		
				<label for="" class="label">First Name </label></div>
				<div class='grid-30'><input type=text name=first_name  /></div>
				
				<!--second name-->
				<div class='prefix-5 grid-15'><label for="" class="label">Middle Name </label></div>
				<div class='grid-30'><input type=text name=middle_name  /></div>
				<div class=clear></div><br>		
				<!--last name-->
				<div class='grid-10'><label for="" class="label">Last Name </label></div>
				<div class='grid-30'><input type=text name=last_name  /></div>
				<!--phone number-->
				<div class='prefix-5 grid-15'><label for="" class="label">Mobile No.</label></div>
				<div class='grid-30'><input type=text name=mobile_no  /></div>
				<div class=clear></div><br>				
				
				<!--patient type-->
				<div class='grid-10'><label for="" class="label">Patient Type</label></div>
				<div class='grid-30'><select class=ptype name=ptype><option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from insurance_company order by name";
						$error = "Unable to insurance companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
					
					?>
					</option></select>
				</div>
				<!--compnay covered-->
				<div class=' prefix-5 grid-15 '><label for="" class="label">Company Covered</label></div>
				<div class='grid-30 '><select class=covered_company name=covered_company><option></option>
				<?php 
				
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from covered_company order by name";
						$error = "Unable to covered companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}					
					
					
				?>
				</select></div>
				<div class=clear></div><br>		
				<!--membership number-->
				<div class='prefix-45 grid-15'><label for="" class="label">Membership Number</label></div>
				<div class='grid-30'><input type=text name=mem_no  /></div>				
				<div class=clear></div
				<!--refering docotr-->
				<div class='grid-10 '><label for="" class="label">Referred by</label></div>
				<div class='grid-30 '><select name=ref_doc><option></option>
				<?php 
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,referrer_name from xray_refering_doc order by referrer_name";
						$error = "Unable to xray ref docs";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['referrer_name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}					
				?>
				</select></div>
				<div class=clear></div><br>	

			<div class='heading_bg grid-100'>X-RAYS TO PERFORM</div>
				<div class=grid-30><label for="" class="label">X-RAY TYPE</label></div>
				
				<div class=grid-10><label for="" class="label">Cost</label></div>
				
				<div class=clear></div>
				<!-- <div class='grid-100'> -->
				
				<?php 
					//get xray types
					$sql=$error=$s='';$placeholders=array();
					$sql="select id,name from procedures where type=2";
					$error="Unable to get xray types";
					$s = select_sql($sql, $placeholders, $error, $pdo);	
					$count=1;
					foreach($s as $row){
						$xray_id=$encrypt->encrypt($row['id']);
						$xray_name=html($row['name']);?>
						
								<div class='grid-25 '><label for="" class="label"><?php echo "$xray_name"; ?></label></div>
								<div class='grid-5'><input class='select_xray_ref2' type=checkbox name='<?php echo "xrays$count"; ?>' value='<?php echo "$xray_id"; ?>' /></div>	
							<div class='grid-15 '><input type=text name='<?php echo "xray_cost$count"; ?>' class=xray_ref_cost disabled  /></div>	
						
						<div class='grid-15'></div>
						<div class=grid-45></div>
						<div class=clear></div>
						<div class='grid-45 grid-parent xray_tooth'><!-- 30 -->
							<div class='grid-100 teeth_div'>
								<div class='teeth_row'>
									<div class='hover  teeth_heading_cell'>Upper Right - 1x
										<div class='teeth_body'>
										<?php
										$i2=8;
										$teeth_specified="teeth_specified$count"."[]";
										while($i2 >= 1){
											$number="1$i2";
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number'>$number<br><input  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2--;
										}	?>
										</div>
									</div>
									<div class='hover teeth_heading_cell'>Upper Left - 2x
										<div class='teeth_body'>
										<?php
										$i2=1;
										while($i2 <= 8){
											$number="2$i2";
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number'>$number<br><input class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2++;
										}	?>
										</div>
									</div>							
								</div>
								<!-- second row -->
								<div class='teeth_row'>
									<div class='hover  no_padding teeth_heading_cell'>Lower Right - 4x
										<div class='teeth_body'>
										<?php
										$i2=8;
										while($i2 >= 1){
											$number="4$i2";
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number'>$number<br><input  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2--;
										}	?>
										</div>
									</div>
									<div class='hover  no_padding teeth_heading_cell'>Lower Left - 3x
										<div class='teeth_body'>
										<?php
										$i2=1;
										while($i2 <= 8){
											$number="3$i2";
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number'>$number<br><input  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2++;
										}	?>
										</div>
									</div>							
								</div>						
							
							</div>						
						</div><!-- end the 30 -->
						
						<div class=clear></div><br>
						<?php 
						$count++;
					}	
					$nimeana=$encrypt->encrypt($count);
					echo "<input type=hidden name=nimeana value=$nimeana />";
				?>
				
				<div class=clear></div>
				<!--payments-->
		<div class=' prefix-20 grid-10'><label for="" class="label">Total Cost</label></div>
		<div class='grid-10 xray_ref_cost_total label'></div>	
		<div class=clear></div><br>
		<div class='prefix-20 grid-10 '><label for="" class="label">Payment Type</label></div>
		<div class='grid-10'><?php  
			$sql=$error=$s='';$placeholders=array();
			$sql="select id,name from payment_types where   id!=8 and id!=6 and id!=9 and id!=10 order by name";					
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
				<div class='prefix-30 grid-15 label'>Bank Name</div>
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
				<div class=clear></div><br>
				<div class='prefix-30 grid-15 '><label for="" class="label">VISA Tx. Number</label></div>
				<div class='grid-25'><input type=text name=visa_number /></div>	
			</div>	

			
		</div>
		
		<div class=clear></div><br>		


	<div class='prefix-30 grid-10'><input type=submit  value='Submit' /></div>		
		</fieldset>
		<div class=clear></div>
		<!-- patient type-->


	</form>	
	</div>
</div>