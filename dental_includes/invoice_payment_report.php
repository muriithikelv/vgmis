<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,79)){

	exit;
}
echo "<div class='grid_12 page_heading'>INVOICE PAYMENTS</div>";
?>
<div class='grid-container invoice_payment_content'></div>
<?php 

//get results
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
	<div class='grid-100 insurer_payment_datec'>
		<div class='grid-15 '><label for="" class="label">Search by</label></div>
					<div class='grid-25'>
						<select   id='invoice_pay_report_type'><option></option>
							<option value="invoice">Date invoice raised</option>
							<option value="insurer">Date of insurance payment</option>
						</select>
					</div>
					<br>
	</div>
	<div class=clear></div><br>
	<div class='grid-100 insurer_payment_date'>		
		<form class='patient_form' action="insurnace_payment_date" method="POST" enctype="" name="" id="">
			<?php $token = form_token(); $_SESSION['token_ipr2'] = "$token";  ?>
						<input type="hidden" name="token_ipr2"  value="<?php echo $_SESSION['token_ipr2']; ?>" />
			
					<!--insurer-->
					<div class='grid-15'><label for="" class="label">Select Insurer</label>
						</div>
					<div class='grid-25'><select class=ptype2 name=ptype>
						<?php
							echo "<option value='all'>All Insurers</option>";
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from insurance_company where upper(name)!= 'CASH' order by name";
							$error = "Unable to insurance companies";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								echo "<option value='$val'>$name</option>";
							}
						?>
						</select>
					</div>	
					<!--compnay covered-->
					<div class='grid-15 '><label for="" class="label">Company Covered</label></div>
					<div class='grid-25 '><select class='covered_company covered_company2' name=covered_company>
					<?php 
						echo "<option value='all'>All Companies</option>";
					?>
					</select></div>		

					<!--</select></div>	-->
					<div class=clear></div><br>
					
					<!--date range-->
					<div class=' grid-15'><label for="" class="label">Payments made from</label></div>
					<div class=grid-25><input type=text name=from_date class=date_picker /></div>
					<div class=grid-15><label for="" class="label">to</label></div>
					<div class=grid-25><input type=text name=to_date class=date_picker /></div>
		<!--</div>-->
					<div class=clear></div>
					<br>
					<div class='prefix-55 grid-10'>	<input type="submit"  value="Submit"/></div>

		</form>		
	</div>
	<div class=clear></div>
	
	<div class='grid-100 invoice_date'>		
		<form class='patient_form' action="invoice_payments_report" method="POST" enctype="" name="" id="">
			
				<div class='grid-15 '><label for="" class="label">Search by</label></div>
					<div class='grid-25'>
						<?php $token = form_token(); $_SESSION['token_ipr1'] = "$token";  ?>
						<input type="hidden" name="token_ipr1"  value="<?php echo $_SESSION['token_ipr1']; ?>" />
					
						<select  name=sby class=''><option></option>
							<option value="paid">Paid</option>
							<option value="partially_paid">Partially Paid</option>
							<option value="unpaid">Unpaid</option>
							<option value="paid_and_unpaid">Paid and Unpaid</option>
						</select>
					</div>
					
					<!--show doctor-->
					<div class='grid-15'><label for="" class="label">Select Doctor</label>
					</div>
					<div class='grid-25'><select name=doc>
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
					<div class=clear></div><br>			
					
					<!--insurer-->
					<div class='grid-15'><label for="" class="label">Select Insurer</label>
						</div>
					<div class='grid-25'><select class=ptype2 name=ptype>
						<?php
							echo "<option value='all'>All Insurers</option>";
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from insurance_company where upper(name)!= 'CASH' order by name";
							$error = "Unable to insurance companies";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								echo "<option value='$val'>$name</option>";
							}
							
						
						?>
						</select>
					</div>	
					<!--compnay covered-->
					<div class='grid-15 '><label for="" class="label">Company Covered</label></div>
					<div class='grid-25 '><select class='covered_company covered_company2' name=covered_company>
					<?php 
						echo "<option value='all'>All Companies</option>";
						/*if(isset($_SESSION['id']) and $_SESSION['id']!=''){
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from covered_company order by name";
							$error = "Unable to covered companies";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								//echo "<option value='$val'>$name</option>";
							}					
								//$val=$encrypt->encrypt("all");
								
						}*/
					?>
					</select></div>		

					<!--</select></div>	-->
					<div class=clear></div><br>
					
					<!--date range-->
					<div class=' grid-15'><label for="" class="label">Invoices raised between</label></div>
					<div class=grid-25><input type=text name=from_date class=date_picker /></div>
					<div class=grid-15><label for="" class="label">And</label></div>
					<div class=grid-25><input type=text name=to_date class=date_picker /></div>
		<!--</div>-->
					<div class=clear></div>
					<br>
					<div class='prefix-55 grid-10'>	<input type="submit"  value="Submit"/></div>

		</form>		
	</div>
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>