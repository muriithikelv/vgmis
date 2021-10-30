<?php
/*
include_once  '../../dental_includes/magicquotes.inc.php';
include_once   '../../dental_includes/db.inc.php';
include_once   '../../dental_includes/DatabaseSession.class.php';
include_once   '../../dental_includes/access.inc.php';
include_once   '../../dental_includes/encryption.php';
include_once    '../../dental_includes/helpers.inc.php';*/
include_once     '../../dental_includes/includes_file2.php';
//include_once     '../../dental_includes/includes_file.php';
$encrypt = new Encryption();
//$session = new dbSession($pdo);
/*
if(!isset($_SESSION))
{
session_start();
}*/
  //logout due to inactivity

if(isset($_POST['xdf']) and $_POST['xdf']=='xdf'){
//echo "".time()." -- $_SESSION[LAST_ACTIVITY]";
	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) { //900
		// last request was more than 30 minutes ago
		session_unset();     // unset $_SESSION variable for the run-time
		session_destroy();   // destroy session data in storage
			echo "xminutesx";
			exit;
	}

}

if(!userIsLoggedIn() ){
 /* ?>
	<script type="text/javascript">
		localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
		window.location = window.location.href;
	</script>
 <?php	*/

	exit;
}


if(isset($_POST['get_company']) and $_POST['get_company']!=''){
	//get companies covered by this ptype
	$sql=$error=$s='';$placeholders=array();
	$sql="select id,name from covered_company where insurer_id=:insurer_id  and listed=0 order by name";//and insured='YES'
	$error="Unable to get covered companies";
	$placeholders[':insurer_id']=$encrypt->decrypt($_POST['get_company']);
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
	echo "<option></option>";
	foreach($s as $row){
		$name=html($row['name']);
		$val=$encrypt->encrypt(html($row['id']));
		echo "<option value='$val'>$name</option>";
	}
	}
	if($s->rowCount() > 0){	echo "<option></option>";}
}

//get cash patient type
if(isset($_POST['get_cash_for_non_insured']) and $_POST['get_cash_for_non_insured']=='yes'){
	echo $_SESSION['cash_type_coded'];
	//ensure patient type 3 ni cash
}



//this for checking if procedurew is in points program
elseif(isset($_POST['check_procedure_in_points']) and $_POST['check_procedure_in_points']!=''){
	$procedure_id=$encrypt->decrypt($_POST['check_procedure_in_points']);
	//check if procedure is in points program
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select points from procedures_in_points_scheme where procedure_id=:procedure_id";
	$placeholders2['procedure_id']=$procedure_id;
	$error2="Unable to check if procedure is in points program";
	$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
	if($s2->rowCount() > 0){
		$points_pay=$encrypt->encrypt("3");
		echo "yes#<option value='$points_pay'>Points</option>";
	}
	else{
		$points_pay=$encrypt->encrypt("3");
		echo "no#<option value='$points_pay'>Points</option>";
	}
}

//this for checking if procedurew can be payed by insurance or not
elseif(isset($_POST['check_procedure_payment_method']) and $_POST['check_procedure_payment_method']!=''){
	$procedure_id=$encrypt->decrypt($_POST['check_procedure_payment_method']);
	//check if procedure is not covered by the insurer at all
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select id from procedures_not_insured where insurer_id=:insurer_id and procedure_id=:procedure_id";
	$placeholders2['insurer_id']=$_SESSION['type'];
	$placeholders2['procedure_id']=$procedure_id;
	$error2="Unable to check if procedure is covered by insurer";
	$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
	if($s2->rowCount() > 0){
		echo "not_covered#";
	}
	else{
		//if it sis covered by insurer chec if the incusrer covered it for the patienets corporate
		$sql2=$error2=$s2='';$placeholders2=array();
		$sql2="select id from procedures_not_covered where insurer_id=:insurer_id and procedure_not_covered=:procedure_not_covered and company_id=:company_id";
		$placeholders2['insurer_id']=$_SESSION['type'];
		$placeholders2['procedure_not_covered']=$procedure_id;
		$placeholders2['company_id']=$_SESSION['company_covered'];
		$error2="Unable to check if procedure is covered by insurer";
		$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
		if($s2->rowCount() > 0){
			echo "not_covered#";
		}
		else{
			echo "covered#";
			if($_SESSION['insured'] == 'YES' and !$_SESSION['ins_suspend']){
				$invoice_pay=$encrypt->encrypt("1");
				echo "<option value='$invoice_pay'>Insurance</option>";
			}
		}
	}
}

//this for checking if amount paid has cleared balance or not
elseif(isset($_POST['pay_type1']) and $_POST['pay_type1']!='' and isset($_POST['amount1']) and $_POST['amount1']!='' and
	 isset($_POST['token_ninye1']) and $_POST['token_ninye1']!=''){
	$pay_type=$encrypt->decrypt($_POST['pay_type1']);
	if($pay_type==2 or $pay_type==3 or $pay_type==4 or $pay_type==5 or $pay_type==10 ){
		//now get patient self balance
		$result=show_pt_statement_brief($pdo,$_POST['token_ninye1'],$encrypt);
		$result=str_replace(",", "", "$result");
		$amount=str_replace(",", "", $_POST['amount1']);

		$data=explode('#',"$result");
		//if($data[1] == 0 or $data[1] < 0){echo "bad#no_balance";}
		if($data[1] > 0 and ($data[1] - $amount) > 0){

			echo "good<div class='grid-50 label'>Please specify date when the remaining balance of KES: ".number_format($data[1] - $amount,2)." will be cleared </div>
				<div class='grid-10'><input type=text name=date_clear_bal class=date_picker_no_past /></div>
				<div class=clear></div><br>
				<div class='grid-50 label '><span class=put_right>Comment</span> </div>
									<div class='grid-40'><textarea name=comment width=100%></textarea></div>
									<div class=clear></div>";

		}
		else{echo "no";}
		exit;
	}
	echo "no";
}

//this is for adding a cadcam blocks stock
elseif(isset($_SESSION['token_cs11']) and 	isset($_POST['token_cs11']) and $_POST['token_cs11']==$_SESSION['token_cs11'] and
userHasRole($pdo,69)){
	$exit_flag=false;
	$quantity=$_POST['stock_in'];
	$block_id=$_POST['ninye'];
	$n=count($block_id);
	$i=0;
	//echo "n is $n";
	try{
	$pdo->beginTransaction();

		while($i < $n){
			if($quantity[$i]==''){
				$i++;
				continue;
			}

			//check if quantity is valid integer
			if(!ctype_digit($quantity[$i])){
				$var=html("$quantity[$i]");
				$message="bad#Unable to save details as quantity $var is not a valid integer. ";
				$exit_flag=true;
				break;
			}

			//now insert
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into blocks_stock_in set block_id=:block_id, quantity=:quantity, when_added=now(), added_by=:added_by";
			$error="Unable to get covered companies";
			$placeholders[':block_id']=$encrypt->decrypt($block_id[$i]);
			$placeholders[':quantity']=$quantity[$i];
			$placeholders[':added_by']=$_SESSION['id'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			$i++;
		}
		if(!$exit_flag){
				$tx_result = $pdo->commit();
				$message="good#stock details saved. ";
		}
		elseif($exit_flag){
			$pdo->rollBack();
			//$message="ba#Patient disease details saved. ";
		}

	}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$message="bad#Unable to save patient disease details  ";
	}
	echo "$message";


}

//this is for cadcam referrals
elseif(isset($_GET['cmr']) and $_GET['cmr']!='' and userHasRole($pdo,55)){
	//echo $_GET['cms'];
	echo "<div class=grid-container>";
	echo "<div class='grid_12 page_heading'>CADCAM REFERRALS</div>";
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and
	$_SESSION['result_class']!=''){
		if($_SESSION['result_class']!='bad'){
			echo "<div class='$_SESSION[result_class]'>$_SESSION[result_message]</div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';
		}
		/*elseif($_SESSION['result_class']=='bad'){
			echo "<div class='feedback hide_element'></div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';
		}*/
	}
	//echo "<div class='grid_12 page_heading'>CADCAM BLOCKS USAGE</div>";
	echo "<div class='feedback hide_element'></div>";
   $id=$encrypt->decrypt($_GET['cmr']);
 //  echo "id is $id";
	//show form
	?>
		<form class='patient_form' action="" method="POST" enctype="" name="" id="">
		<fieldset><legend>Patient Details</legend>

		<?php $token = form_token(); $_SESSION['token_cmr1'] = "$token";  ?>
		<input type="hidden" name="token_cmr1"  value="<?php echo $_SESSION['token_cmr1']; ?>" />
				<!--first name-->
				<div class='grid-10'>


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
						$sql = "select id,name from insurance_company where listed=0 order by name";
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

					/*	$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from covered_company order by name";
						$error = "Unable to covered companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}	*/


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
						$sql = "select id,name from cadcam_referrer where listed=0 order by name";
						$error = "Unable to get cadcam refs";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
				?>
				</select></div>
				<div class=clear></div><br>
		</fieldset>
	<fieldset><legend>CADCAM BLOCKS USAGE</legend>
		<br>
		<!-- manufacturer -->
		<!--<fieldset><legend>Manufacturer</legend>-->
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, name from cadcam_types where id=:id";
				$error2="Unable to get manufacturers for cadcam";
				$placeholders2[':id']=$id;
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0){

					$i=0;
					foreach($s2 as $row2){
						$name=html($row2['name']);
						echo "<table class='cadcam_type_table2'><caption>Stock usage for $name</caption>";
						echo "<thead><tr><th class='type_class2'>BLOCK DETAILS</th><th class=stock_class2>AVAILLABLE</th><th class=stock_class2>USED</th></tr></thead>";
						//get type
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
						//$sql3="select id, name from cadcam_types where listed=0 and level=1 order by name";

						$error3="Unable to get size for cadcam";
						$placeholders3[':parent_id']=$row2['id'];
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							$name=html($row3['name']);
							echo "<thead><tr><th>TYPE: $name</th><th></th><th></th></tr></thead>";
							//get size
							$sql4=$error4=$s4='';$placeholders4=array();
							$sql4="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
							$error4="Unable to get type for cadcam";
							$placeholders4[':parent_id']=$row3['id'];
							$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
							foreach($s4 as $row4){
								$name=html($row4['name']);
								echo "<thead><tr><th class='padding_size'>SIZE: $name </th><th ></th><th></th></tr></thead>";
								//get shade
								$sql5=$error5=$s5='';$placeholders5=array();
								//$sql5="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
								$sql5="SELECT cadcam_types.id, cadcam_types.name, b.quantity_in,c.quantity_out, (b.quantity_in - ifnull( c.quantity_out, 0)) as stock_left
										from cadcam_types  LEFT JOIN( select block_id, ifnull( sum( blocks_stock_in.quantity ) , 0 ) as quantity_in
										from blocks_stock_in group by block_id ) as b on  cadcam_types.id=b.block_id
										left join (select block_id, ifnull(sum(quantity),0) as quantity_out from blocks_stock_out group by block_id) as c on cadcam_types.id=c.block_id
										WHERE listed =0 AND parent_id =:parent_id";
								$error5="Unable to get shade for cadcam";
								$placeholders5[':parent_id']=$row4['id'];
								$s5 = 	select_sql($sql5, $placeholders5, $error5, $pdo);
								foreach($s5 as $row5){
									$name=html($row5['name']);
									$var=$encrypt->encrypt($row5['id']);
									if($row5['stock_left']==''){$row5['stock_left']=0;}
									$balance=number_format(html($row5['stock_left']),2);
									echo "<tr><td class='padding_shade'>$name</td><td>$balance</td><td><input name=stock_in[] type=text  /><input type=hidden name=ninye[] value=$var /></tr>";
								}//end shade
								//echo "</tbody>";
							}//end type
						}//end size
						echo "</table>";
						echo "<div class='grid-5'><label class=label>Cost</label></div>
								<div class='grid-10'><input type=text name=cost /></div>
								";?>
						<div class='grid-15 '><label for="" class="label">Payment Type</label></div>
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

							<div class='grid-10'><input type=submit value=Submit /></div>
						</div>
						<div class=clear></div>
						<?php
					}//end manufacturer
				}


			?>
		<!--</fieldset>-->

		<div class=clear></div>


	</fieldset>
	</form>
	</div>
	<?php
}

//add cadcamstock
elseif(isset($_GET['cms']) and $_GET['cms']!='' and userHasRole($pdo,69)){
	//echo $_GET['cms'];
	echo "<div class='grid_12 page_heading'>CADCAM BLOCKS STOCK IN</div>";
	echo "<div class='feedback hide_element'></div>";
   $id=$encrypt->decrypt($_GET['cms']);
 //  echo "id is $id";
	//show form
	?>
		<form class='patient_form' action="#block_stock_in" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_cs11'] = "$token";  ?>
		<input type="hidden" name="token_cs11"  value="<?php echo $_SESSION['token_cs11']; ?>" />
		<!-- manufacturer -->
		<!--<fieldset><legend>Manufacturer</legend>-->
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, name from cadcam_types where id=:id";
				$error2="Unable to get manufacturers for cadcam";
				$placeholders2[':id']=$id;
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0){

					$i=0;
					foreach($s2 as $row2){
						$name=html($row2['name']);
						echo "<table class='cadcam_type_table'><caption>Stock input for $name</caption>";
						echo "<thead><tr><th class='type_class'>BLOCK DETAILS</th><th class=stock_class>STOCK IN</th></tr></thead>";
						//get type
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
						$error3="Unable to get size for cadcam";
						$placeholders3[':parent_id']=$row2['id'];
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							$name=html($row3['name']);
							echo "<thead><tr><th >TYPE: $name</th><th ></th></tr></thead>";
							//get size
							$sql4=$error4=$s4='';$placeholders4=array();
							$sql4="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
							$error4="Unable to get type for cadcam";
							$placeholders4[':parent_id']=$row3['id'];
							$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
							foreach($s4 as $row4){
								$name=html($row4['name']);
								echo "<thead><tr><th class='padding_size'>SIZE: $name </th><th></th></tr></thead>";
								//get shade
								$sql5=$error5=$s5='';$placeholders5=array();
								$sql5="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
								$error5="Unable to get shade for cadcam";
								$placeholders5[':parent_id']=$row4['id'];
								$s5 = 	select_sql($sql5, $placeholders5, $error5, $pdo);
								foreach($s5 as $row5){
									$name=html($row5['name']);
									$var=$encrypt->encrypt($row5['id']);
									echo "<tr><td class='padding_shade'>$name</td><td><input name=stock_in[] type=text  /><input type=hidden name=ninye[] value=$var /></td></tr>";
								}//end shade
								//echo "</tbody>";
							}//end type
						}//end size
						echo "</table>";
					}//end manufacturer
				}


			?>
		<!--</fieldset>-->

		<div class=clear></div>
		<input type=submit value=Submit />
	</form>
	<?php
}

//record cadcam stock usage
elseif(isset($_SESSION['token_cs12']) and 	isset($_POST['token_cs12']) and $_POST['token_cs12']==$_SESSION['token_cs12'] and
userHasRole($pdo,69)){
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	if(!$exit_flag){
		$quantity=$_POST['stock_in'];
		$block_id=$_POST['ninye'];
		$n=count($block_id);
		$i=0;
		try{
		$pdo->beginTransaction();
			//check if cost is set and is valid
			if(!$exit_flag and !isset($_POST['cost']) or 	$_POST['cost']==''){
				$message="bad#stock_usage#Unable to save details as cost is not set. ";
				$exit_flag=true;
			}


			if(!$exit_flag and isset($_POST['cost']) and 	$_POST['cost']!=''){
				//remove commas
				$amount=str_replace(",", "", $_POST['cost']);
					//check if amount is integer
				if(!ctype_digit($amount)){//echo "ooooo $unit_price[$i] ";
					//check if it has only 2 decimal places
					$data=explode('.',$amount);
					$invalid_amount=html("$amount");
					if ( count($data) != 2 ){
						$message="bad#stock_usage#Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;

					}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#stock_usage#Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;

					}
				}


			}

			//create group number
			if(!$exit_flag){
					$sql=$error=$s=$group_id='';$placeholders=array();
					$sql="insert into blocks_used_group_number_generator set  added_by=:added_by, when_added=now(), cost=:cost, user_type=1,
							user_id=:user_id";
					$error="Unable to generate block used group number";
					$placeholders[':added_by']=$_SESSION['id'];
					$placeholders[':cost']=$amount;
					$placeholders[':user_id']=$_SESSION['pid'];
					$group_id = 	get_insert_id($sql, $placeholders, $error, $pdo);
			}


			if(!$exit_flag and $group_id!=''){


				while($i < $n){
					if($quantity[$i]==''){
						$i++;
						continue;
					}

					//check if quantity is valid integer
					if(!ctype_digit($quantity[$i])){
						$var=html("$quantity[$i]");
						$message="bad#stock_usage#Unable to save details as quantity $var is not a valid integer. ";
						$exit_flag=true;
						break;
					}

					$clean_block_id=$encrypt->decrypt($block_id[$i]);
					$balance=0;

					//check if stock is adequate
					$sql5=$error5=$s5='';$placeholders5=array();
					$sql5="SELECT cadcam_types.id, cadcam_types.name, b.quantity_in,c.quantity_out, b.quantity_in - ifnull(c.quantity_out,0) as stock_left
											from cadcam_types  LEFT JOIN( select block_id, ifnull( sum( blocks_stock_in.quantity ) , 0 ) as quantity_in
											from blocks_stock_in group by block_id ) as b on  cadcam_types.id=b.block_id
											left join (select block_id, sum(quantity) as quantity_out from blocks_stock_out group by block_id) as c on cadcam_types.id=c.block_id
											WHERE  cadcam_types.id=:block_id";
					/*$sql5="SELECT ifnull( sum( blocks_stock_in.quantity ) , 0 ) - ifnull( sum( blocks_stock_out.quantity ) , 0 ) as stock_left
						FROM cadcam_types LEFT JOIN blocks_stock_in ON cadcam_types.id = blocks_stock_in.block_id
						LEFT JOIN blocks_stock_out ON cadcam_types.id = blocks_stock_out.block_id
						WHERE cadcam_types.id=:block_id
						GROUP BY cadcam_types.id ";*/
					$error5="Unable to get stock left for cadcam block";
					$placeholders5[':block_id']=$clean_block_id;
					$s5 = 	select_sql($sql5, $placeholders5, $error5, $pdo);
					foreach($s5 as $row5){$balance=html($row5['stock_left']);}

					if($quantity[$i] > $balance){
						$var=html("$quantity[$i]");
						$message="bad#stock_usage#Unable to save details as quantity $var exceeds availlable stock of $balance. ";
						$exit_flag=true;
						break;
					}

					//now insert
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into blocks_stock_out set block_id=:block_id, quantity=:quantity, group_number=:group_number";
					$error="Unable to record block usage";
					$placeholders[':block_id']=$clean_block_id;
					$placeholders[':quantity']=$quantity[$i];
					$placeholders[':group_number']=$group_id;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);

					$i++;
				}
			}
			if(!$exit_flag and $group_id!=''){
					$tx_result = $pdo->commit();
					$message="good#stock_usage";
					$_SESSION['result_class']='success_response';
					$_SESSION['result_message']='stock details saved.';
			}
			elseif($exit_flag){
				$pdo->rollBack();
				//$message="ba#Patient disease details saved. ";
			}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#Unable to save patient disease details  ";
		}
	}
	echo "$message";


}

//use cadcam stock
elseif(isset($_GET['cmsu']) and $_GET['cmsu']!='' and userHasRole($pdo,69)){
	//echo $_GET['cms'];
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and
	$_SESSION['result_class']!=''){
		if($_SESSION['result_class']!='bad'){
			echo "<div class='$_SESSION[result_class]'>$_SESSION[result_message]</div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';
		}
		/*elseif($_SESSION['result_class']=='bad'){
			echo "<div class='feedback hide_element'></div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';
		}*/
	}
	echo "<div class='grid_12 page_heading'>CADCAM BLOCKS USAGE</div>";
	echo "<div class='feedback hide_element'></div>";
   $id=$encrypt->decrypt($_GET['cmsu']);
 //  echo "id is $id";
	//show form
	?>
		<form class='patient_form' action="#block_stock_out" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_cs12'] = "$token";  ?>
		<input type="hidden" name="token_cs12"  value="<?php echo $_SESSION['token_cs12']; ?>" />
		<!-- manufacturer -->
		<!--<fieldset><legend>Manufacturer</legend>-->
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, name from cadcam_types where id=:id";
				$error2="Unable to get manufacturers for cadcam";
				$placeholders2[':id']=$id;
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0){

					$i=0;
					foreach($s2 as $row2){
						$name=html($row2['name']);
						echo "<table class='cadcam_type_table2'><caption>Stock usage for $name</caption>";
						echo "<thead><tr><th class='type_class2'>BLOCK DETAILS</th><th class=stock_class2>AVAILLABLE</th><th class=stock_class2>USED</th></tr></thead>";
						//get type
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
						//$sql3="select id, name from cadcam_types where listed=0 and level=1 order by name";

						$error3="Unable to get size for cadcam";
						$placeholders3[':parent_id']=$row2['id'];
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							$name=html($row3['name']);
							echo "<thead><tr><th>TYPE: $name</th><th></th><th></th></tr></thead>";
							//get size
							$sql4=$error4=$s4='';$placeholders4=array();
							$sql4="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
							$error4="Unable to get type for cadcam";
							$placeholders4[':parent_id']=$row3['id'];
							$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
							foreach($s4 as $row4){
								$name=html($row4['name']);
								echo "<thead><tr><th class='padding_size'>SIZE: $name </th><th ></th><th></th></tr></thead>";
								//get shade
								$sql5=$error5=$s5='';$placeholders5=array();
								//$sql5="select id, name from cadcam_types where listed=0 and parent_id=:parent_id order by name";
								$sql5="SELECT cadcam_types.id, cadcam_types.name, b.quantity_in,c.quantity_out, (b.quantity_in - ifnull( c.quantity_out, 0)) as stock_left
										from cadcam_types  LEFT JOIN( select block_id, ifnull( sum( blocks_stock_in.quantity ) , 0 ) as quantity_in
										from blocks_stock_in group by block_id ) as b on  cadcam_types.id=b.block_id
										left join (select block_id, ifnull(sum(quantity),0) as quantity_out from blocks_stock_out group by block_id) as c on cadcam_types.id=c.block_id
										WHERE listed =0 AND parent_id =:parent_id";
								$error5="Unable to get shade for cadcam";
								$placeholders5[':parent_id']=$row4['id'];
								$s5 = 	select_sql($sql5, $placeholders5, $error5, $pdo);
								foreach($s5 as $row5){
									$name=html($row5['name']);
									$var=$encrypt->encrypt($row5['id']);
									if($row5['stock_left']==''){$balance=0;}
									else{$balance=number_format(html($row5['stock_left']),2);}
									echo "<tr><td class='padding_shade'>$name</td><td>$balance</td><td><input name=stock_in[] type=text  /><input type=hidden name=ninye[] value=$var /></tr>";
								}//end shade
								//echo "</tbody>";
							}//end type
						}//end size
						echo "</table>";
						/*echo "<div class='grid-5 prefix-85'><label class=label>Cost</label></div>
								<div class='grid-10'><input type=text name=cost /><br><br>
								<input type=submit value=Submit /></div>";*/
						echo "<div class='grid-5 prefix-85'><br><input type=hidden name=cost value=0 />
								<input type=submit value=Submit /></div>";
					}//end manufacturer
				}


			?>
		<!--</fieldset>-->

		<div class=clear></div>

	</form>
	<?php
}

//show cadcam in tdone
elseif(isset($_POST['tdone_cadcam']) and $_POST['tdone_cadcam']!='' and userHasRole($pdo,20)){
		//echo "<div id=cadcam_tabs>
		//echo "<input type=button class=test1 value=test1 />";
		echo "<div class='grid-100 no_padding' id=cadcam_tabs2><ul class=test3>";

		$sql2=$error2=$s2='';$placeholders2=array();
		$sql2="select id, name from cadcam_types where listed=0 and level = 1 order by name";
		$error2="Unable to get manufacturers for cadcam";
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		foreach($s2 as $row2){
			$menu_name=html("$row2[name]");
			$var=urlencode($encrypt->encrypt($row2['id']));
			echo 	"<li ><a class='tab_link' href='dental_b/?cmsu=$var' >$menu_name</a></li>";

		}

		echo "</ul>
	</div>";
}

//get price of procedure for pt in tlpan
elseif(isset($_POST['get_ins_price']) and $_POST['get_ins_price']!=''){
	//echo "- $_POST[selec] -";
	$procedure_id=$encrypt->decrypt($_POST['get_ins_price']);
	if($_POST['selec']!='Points'){
		//echo "xno_pointsx";
		$price='';
		if($_POST['selec']=='Insurance'){
			$sql=$error=$s='';$placeholders=array();
			$sql="select price from insurer_procedure_price where insurer_id=:insurer_id and procedure_id=:procedure_id";
			$error="Unable to get price for procedure";
			$placeholders[':insurer_id']=$_SESSION['type'];
			$placeholders[':procedure_id']=$procedure_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			//echo "count_ins_is".$s->rowCount()."x";
			if(0 + $s->rowCount() > 0){foreach($s as $row){$price="good#".html($row['price']);}}
		}
		elseif($_POST['selec']=='Self' or $price==''){//get price from master
		$sql=$error=$s='';$placeholders=array();
		$sql="select cost from procedures where id=:procedure_id";
		$error="Unable to get price for procedure";
		$placeholders[':procedure_id']=$procedure_id;
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		//echo "count_self_is".$s->rowCount()."x";
		if(0 + $s->rowCount() > 0){foreach($s as $row){$price="good#".html($row['cost']);}}
		}
	}
	elseif($_POST['selec']=='Points'){
//check if points are enough for the treatment
$sql=$error=$s='';$placeholders=array();
$sql="select  points from procedures_in_points_scheme a where procedure_id=:procedure_id";
$placeholders[':procedure_id']=$procedure_id;
$error="Error: Unable to procedure points";
$s = 	select_sql($sql, $placeholders, $error, $pdo);
if($s->rowCount() > 0){
	foreach($s as $row){$price="good#".html($row['points']);}
}
else{$price= "bad#This procedure is not in the loyalty points program. ";}
}
	echo "$price";

}

//get price of xray for pt in exam
elseif(isset($_POST['get_xray_ins_price']) and $_POST['get_xray_ins_price']!=''){
	$xray_id=$encrypt->decrypt($_POST['get_xray_ins_price']);
	$price='';
	if($_POST['selec']!='Points'){
						//get cash equivalent  from insurer price table first
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="select  price from insurer_procedure_price where procedure_id=:procedure_id and insurer_id=:insurer_id";
						$error3="Unable to get procedure cost";
						$placeholders3[':procedure_id']=$xray_id;
						$placeholders3[':insurer_id']=$_SESSION['type'];
						$s3 = select_sql($sql3, $placeholders3, $error3, $pdo);
						if($s3->rowCount() > 0){foreach($s3 as $row3){$price="good#".number_format(html($row3['price']));}}
						else{//check for cost in master table
						$sql31=$error31=$s31='';$placeholders31=array();
						$sql31="select  cost from procedures where id=:procedure_id";
						$error31="Unable to get procedure cost";
						$placeholders31[':procedure_id']=$xray_id;
						$s31 = select_sql($sql31, $placeholders31, $error31, $pdo);
						if($s31->rowCount() > 0){foreach($s31 as $row31){$price="good#".number_format(html($row31['cost']));}}
						}
	}
	elseif($_POST['selec']=='Points'){
		//check if points are enough for the treatment
		$sql=$error=$s='';$placeholders=array();
		$sql="select  points from procedures_in_points_scheme a where procedure_id=:procedure_id";
		$placeholders[':procedure_id']=$xray_id;
		$error="Error: Unable to procedure points";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			foreach($s as $row){$price="good#".html($row['points']);}
		}
		else{$price= "bad#This procedure is not in the loyalty points program. ";}
	}

	echo "$price";

}

elseif(isset($_POST['get_company2']) and $_POST['get_company2']!=''){
	//get companies covered by this ptype
	$sql=$error=$s='';$placeholders=array();
	$sql="select id,name from covered_company where insurer_id=:insurer_id and insured='YES' order by name";
	$error="Unable to get covered companies";
	$placeholders[':insurer_id']=$encrypt->decrypt($_POST['get_company2']);
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
	//echo "<option ></option>";
	echo "<option value='all'>ALL Companies</option>";
	foreach($s as $row){
		$name=html($row['name']);
		$val=$encrypt->encrypt(html($row['id']));
		echo "<option value='$val'>$name</option>";
	}
	}
	if($s->rowCount() > 0){	echo "<option></option>";}
	if( $_POST['get_company2']=='all'){	echo "<option value='all'>ALL</option>";}
}

//this will clear the pid2 session variable
elseif(isset($_POST['clear_pid2']) and $_POST['clear_pid2']=='yes'){
	$_SESSION['pid2']='';
	echo "done";
}

//this will geta any unbilled xray after submitting a tratment plan
elseif(isset($_POST['get_unbilled_xray']) and $_POST['get_unbilled_xray']=='unbilled_xray'){
			$sql=$error=$s='';$placeholders=array();
			$sql="select date_taken,xrays_done,id,cost,
					case pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points'	end as pay_type	, teeth
					from xray_holder where pid=:pid";
			$error="Unable to get xrays done that have not tplan yet";
			$placeholders['pid']=$_SESSION['pid'];
			$s2 = select_sql($sql, $placeholders, $error, $pdo);
			if($s2->rowCount()>0){

					echo "<table class='unbilled_xrays normal_table'><caption>Unbilled X-rays</caption>
					<tr><th class=unbilled_date>Date of X-ray</th>
					<th class=unbilled_procedure>X-rays Done</th><th class=unbilled_pay_type>Payment Method</th>
					<th class=unbilled_cost>Cost</th><th class=unbilled_select>Add to Treatment Plan</th></tr>
					<tr>";
					foreach($s2 as $row){
						/*//get x-ray names
						$xrays=explode(',',$row['xrays_done']);
						$i=0;
						$n=count($xrays);
						$xrays_done='';
						while($i <$n){
							$name=html($_SESSION['xray_names_array'][$xrays[$i]]);
							if($i==0){$xrays_done="$name";}
							else{$xrays_done="$xrays_done<br> $name";}
							$i++;
						}*/
						$xrays_done=html("$row[xrays_done] $row[teeth]");

						$date=html($row['date_taken']);
						$cost=html(number_format($row['cost'],2));
						$pay_type=html($row['pay_type']);
						$id=$encrypt->encrypt($row['id']);
						echo "

							<td>$date</td>
							<td>$xrays_done</td>
							<td>$pay_type</td>
							<td>$cost</td>
							<td><input class=add_xray_to_tplan type=checkbox name=xrays[] value=$id /></td>
						</tr>";

					}
					echo "</table>";

			}
}

//this will add new manufacturer text box to
elseif(isset($_POST['add_manufacturer']) and $_POST['add_manufacturer']!='' and   userHasRole($pdo,67)){
	if($_POST['add_manufacturer']=='Add Manufacturer'){
		echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_manufacturer[]  /></div>";
	}
	elseif($_POST['add_manufacturer']=='Add Size'){
		echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_size[]  /></div>";
	}
	elseif($_POST['add_manufacturer']=='Add Type'){
		echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_type[]  /></div>";
	}
	elseif($_POST['add_manufacturer']=='Add Shade'){
		echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_shade[]  /></div>";
	}
}

//this will set re-appoint id when making a re-appointment
elseif(isset($_POST['appointment_id']) and $_POST['appointment_id']!='' and   userHasRole($pdo,80)){
	$_SESSION['re_appoint_id']=$encrypt->decrypt("$_POST[appointment_id]");
	//echo $_SESSION['re_appoint_id'];
}

//this will set appoint id when making an appointment for school holidays
elseif(isset($_POST['appointment_id2']) and $_POST['appointment_id2']!='' and   userHasRole($pdo,122)){
	$_SESSION['appoint_id2']=$encrypt->decrypt("$_POST[appointment_id2]");

}

//this will set the new appointment time for re-appoint id when making a re-appointment
elseif(isset($_POST['new_appointment']) and $_POST['new_appointment']!='' and   userHasRole($pdo,80)){
	$_SESSION['new_appointment2']=$encrypt->decrypt("$_POST[new_appointment]");
	//echo "$_SESSION[new_appointment2]  -  783";

}

//now update curernt appointments
elseif( isset($_POST['token_eap2']) and isset($_SESSION['token_eap2']) and $_SESSION['token_eap2']==$_POST['token_eap2']
and userHasRole($pdo,80)){
	$exit_flag=false;
	$appointments=$_POST['status'];
	$i=0;
	$n=count($appointments);
	try{
			$pdo->beginTransaction();
			while($i < $n){
				$result=$encrypt->decrypt("$appointments[$i]");
				$data=explode('#',"$result");
				$status="$data[0]";
				$appointment_id=$data[1];
				if($data[2]=='yes'){$registered=" registered_patient_appointments ";}
				elseif($data[2]=='NO'){$registered=" unregistered_patient_appointments ";}
				$sql=$error=$s='';$placeholders=array();
				$sql="update $registered set status=:status where id=:id";
				$error="Unable to update appointment";
				$placeholders[':status']="$status";
				$placeholders[':id']=$appointment_id;
				$s = insert_sql($sql, $placeholders, $error, $pdo);
				//echo "-- $result --";
				//if it's re-appointed check if the new-appointment-id is set otherwise don't commit
				if($status == "RE-APPOINTED"){
					if($data[2]=='yes'){// check for registerd patient
						$sql1=$error1=$s1='';$placeholders1=array();
						$sql1="select new_appointment_id, concat(first_name,' ', middle_name,' ', last_name) as names from registered_patient_appointments  as a
							join patient_details_a as b on a.pid=b.pid and a.id=:id";
						$error1="Unable to check if new  appointment is set for re-appointment";
						$placeholders1[':id']=$appointment_id;
					}
					elseif($data[2]=='NO'){ //check for unregistered
						$sql1=$error1=$s1='';$placeholders1=array();
						$sql1="select new_appointment_id, concat(first_name,' ', middle_name,' ', last_name) as names from unregistered_patient_appointments  as a
							join unregistered_patients as b on a.pid=b.id and a.id=:id";
						$error1="Unable to check if new  appointment is set for re-appointment";
						$placeholders1[':id']=$appointment_id;
					}

					$s1 = select_sql($sql1, $placeholders1, $error1, $pdo);
					foreach($s1 as $row){
						$names=ucfirst(html("$row[names]"));
						if($row['new_appointment_id'] == 0){$exit_flag = true;}
					}
					if($exit_flag){
						$message="bad#Unable to save changes as no new appointment has been scheduled for $names ";
						break;
					}
				}
				$i++;
			}
			if(!$exit_flag){
					$pdo->commit();
					$message = "good#Changes saved ";
			}
			else{$pdo->rollBack();}
	}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$message="bad#Unable to save patient disease details  ";
	}
	echo "$message";
}

//send message from patient list
elseif(isset($_POST['token_rpd1']) and 	$_POST['token_rpd1']!='' and $_POST['token_rpd1']==$_SESSION['token_rpd1']
	and userHasRole($pdo,90)){

		$exit_flag=false;
		$i=$n=0;
		$message=$all_messages='';
		//check if email address is selected
		if(!isset($_POST['send_email']) or $_POST['send_email']==''){
			$message="bad#No email address has been selected";
			$exit_flag=true;
		}
		//check if subject is set
		if(!$exit_flag and !isset($_POST['email_subject']) or $_POST['email_subject']==''){
			$message="bad#No subject has been specified for the email";
			$exit_flag=true;
		}
		//check if body is set
		if(!$exit_flag and !isset($_POST['email_text']) or $_POST['email_text']==''){
			$message="bad#No text has been specified for the email body";
			$exit_flag=true;
		}
		if(!$exit_flag){
			$data=$_POST['send_email'];
			$n=count($data);
			while($i < $n){
			//echo "$i --";
				//$var=html("$balance#$patient#$pnum#$email1#$email2");
				$result1=$encrypt->decrypt("$data[$i]");
				$result=explode('@@',"$result1");
				$patient_name="$result[0]";
				$patient_no="$result[1]";
				$email1="$result[2]";
				$email2=html("$result[3]");
				$pid="$result[4]";
				//get insurer and corprate if any
				$sql=$error=$s='';$placeholders=array();
				$sql="select insurance_company.name,covered_company.name
						from patient_details_a left join insurance_company on patient_details_a.type=insurance_company.id
						left join covered_company on patient_details_a.company_covered=covered_company.id
						where pid=:pid";
				$placeholders['pid']=$pid;
				$error="unable to get patient type";
				$s = select_sql($sql, $placeholders, $error, $pdo);
				$company=$insurer='';
				foreach($s as $row){
					$insurer=html("$row[0]");
					if($row[1]!=''){$company=html(" - $row[1]");}
				}
				$smtp_host='mail.molars.co.ke';
				$smtp_username='molars';
				$smtp_password='uO1ynN79m2';
				$from_email_address='test@molars.co.ke';
				$from_name='test user';
				$to_email_name="$patient_name";
				$subject=html($_POST['email_subject']);
				$body="Dear $patient_name<br><br>".html($_POST['email_text'])."<br><br>
						Regards,<br>
						Molars Dental Clinic";


				// Clear all addresses and attachments for next loop
			//	echo "$email2 -- $result1 ";


				//send email 1
				if($email1!=''){
					$mail->ClearAllRecipients();
					$mail->ClearReplyTos();
					$mail->ClearAttachments();
					$send_status=send_email($mail, $email1,$to_email_name, $subject, $body, $pid);
					if($send_status!='good'){
						if($message==''){
							$message="bad#<table class=normal_table><caption>the following email addresses are not correctly formated</caption><thead><tr><th>PATIENT NUMBER</th><th>ERROR</th></tr></thead><tbody>";
							$message="$message<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";
						}
						else{$message="$message<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";}
					}
				}

				//send email 2
				if($email2!=''){
			//		echo "$email2 44-- ";
					$mail->ClearAllRecipients();
					$mail->ClearReplyTos();
					$mail->ClearAttachments();
					$send_status=send_email($mail, $email2,$to_email_name, $subject, $body, $pid);
					//echo "send status is $send_status";
					if($send_status!='good'){
						if($message==''){
							$message="bad#<table class=normal_table><thead><tr><th>PATIENT NUMBER</th><th>ERROR</th></tr></thead><tbody>";
							$message="$message<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";
						}
						else{$message="$message<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";}
					}
					elseif($send_status=='good'){$all_messages='good';}

				}

				$i++;
			}
		}
		elseif($exit_flag){echo $message;exit;}
		if($message=='' and $all_messages=='good'){echo "good#All messages sent";}
		elseif($message!=''){$message="$message</tbody></table>";
			echo "$message";
		}
}

//send statement in email
elseif(isset($_POST['token_cbr2']) and 	$_POST['token_cbr2']!='' and $_POST['token_cbr2']==$_SESSION['token_cbr2']
	and userHasRole($pdo,85)){
	$i=$n=0;
	$data=$_POST['send_email'];
	$n=count($data);
	if($n==0){echo "ERROR: No email address has been selected";exit;}
	$fail_error='';
	$today=date('Y-m-d');
	while($i < $n){
		//echo "$i --";
		//$var=html("$balance#$patient#$pnum#$email1#$email2");
		$result1=$encrypt->decrypt("$data[$i]");
		$result=explode('#',"$result1");
		$balance=number_format($result[0],2);
		$patient_name="$result[1]";
		$patient_no="$result[2]";
		$email1="$result[3]";
		$email2="$result[4]";
		$pid="$result[5]";
		//get insurer and corprate if any
		$sql=$error=$s='';$placeholders=array();
		$sql="select insurance_company.name,covered_company.name
				from patient_details_a left join insurance_company on patient_details_a.type=insurance_company.id
				left join covered_company on patient_details_a.company_covered=covered_company.id
				where pid=:pid";
		$placeholders['pid']=$pid;
		$error="unable to get patient type";
		$s = select_sql($sql, $placeholders, $error, $pdo);
		$company=$insurer='';
		foreach($s as $row){
			$insurer=html("$row[0]");
			if($row[1]!=''){$company=html(" - $row[1]");}
		}

		$to_email_address="$email1";
		$to_email_address2="$email2";
		$to_email_name="$patient_name";
		$subject='Molars Dental Clinic - Balance Statement';

		$balance_statement = return_pt_statement_for_email($pdo,$encrypt->encrypt("$pid"),$encrypt);

		$body="Dear $patient_name<br>
				<br>Trust you are well.<br>
				<br>You have an outstanding cash balance of $balance.<br>
				<br>Please find below your balance statement as at $today.<br>
				<br>$balance_statement <br>
				<br>If you feel there is an error in the balance statement or for any enquiry please don't hesitate to contact our credit controller via <a href='mailto:creditcontrol@molars.co.ke'>creditcontrol@molars.co.ke</a>.<br>
				<br>Otherwise, please make arrangements to clear the same to enable us to serve you better.<br>
				<br>Payment now can be done via MPESA Paybill Number, 922551, for convenience and ease of payment.<br><br>



				Regards,<br>
				Molars Dental Practice<br>
				Tel: 0751856900<br>
				Email: <a href='mailto:creditcontrol@molars.co.ke'>creditcontrol@molars.co.ke</a><br>
				Website: <a href='http://www.molars.co.ke'>www.molars.co.ke</a><br>
				Electricity House 3rd Floor, Harambee Ave<br>";
		echo "$body";//exit;
		//send email 1
		if($email1!=''){
			$to_email_address="$email1";
			//$send_status=send_email($mail, $smtp_host, $smtp_username, $smtp_password, $from_email_address, $from_name, $to_email_address,$to_email_name, $subject, $body, $pid, $to_email_address2);
			$send_status=send_email($mail, $to_email_address,$to_email_name, $subject, $body, $pid);

			if($send_status!='good'){
				if($fail_error==''){
					$fail_error="<table class=normal_table><thead><tr><th>PATIENT NUMBER</th><th>ERROR</th></tr></thead><tbody>";
					$fail_error="$fail_error<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";
				}
				else{$fail_error="$fail_error<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";}
			}
			else{
				//log sender
				$sql21=$error21=$s21='';$placeholders21=array();
				$sql21="insert into balance_email_log set user_id=:user_id, when_sent=now(), email_sent_to=:email_sent_to, pid=:pid  ";
				$error21="Unable to get doc list";
				$placeholders21['user_id']=$_SESSION['id'];
				$placeholders21['email_sent_to']="$to_email_address";
				$placeholders21['pid']=$pid;
				$s21 = 	insert_sql($sql21, $placeholders21, $error21, $pdo);
			}

		}
		//send email 2
		if($email2!=''){
			$to_email_address="$email2";
			//$send_status=send_email($mail, $smtp_host, $smtp_username, $smtp_password, $from_email_address, $from_name, $to_email_address,$to_email_name, $subject, $body, $pid, $to_email_address2);
			$send_status=send_email($mail, $to_email_address,$to_email_name, $subject, $body, $pid);

			if($send_status!='good'){
				if($fail_error==''){
					$fail_error="<table class=normal_table><thead><tr><th>PATIENT NUMBER</th><th>ERROR</th></tr></thead><tbody>";
					$fail_error="$fail_error<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";
				}
				else{$fail_error="$fail_error<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";}
			}
			else{
				//log sender
				$sql21=$error21=$s21='';$placeholders21=array();
				$sql21="insert into balance_email_log set user_id=:user_id, when_sent=now(), email_sent_to=:email_sent_to, pid=:pid  ";
				$error21="Unable to get doc list";
				$placeholders21['user_id']=$_SESSION['id'];
				$placeholders21['email_sent_to']="$to_email_address";
				$placeholders21['pid']=$pid;
				$s21 = 	insert_sql($sql21, $placeholders21, $error21, $pdo);
			}
		}
		$i++;
	}
}


//send pdf balance for cash guys
//this is no longer used
elseif(isset($_POST['token_cbr2x']) and 	$_POST['token_cbr2x']!='' and $_POST['token_cbr2x']==$_SESSION['token_cbr2x']
	and userHasRole($pdo,85)){


		class PDF extends FPDF
			{

				var $B;
				var $I;
				var $U;
				var $HREF;

				function PDF($orientation='P', $unit='mm', $size='A4')
				{
					// Call parent constructor
					$this->FPDF($orientation,$unit,$size);
					// Initialization
					$this->B = 0;
					$this->I = 0;
					$this->U = 0;
					$this->HREF = '';
				}

				function WriteHTML($html)
				{
					// HTML parser
					$html = str_replace("\n",' ',$html);
					$a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
					foreach($a as $i=>$e)
					{
						if($i%2==0)
						{
							// Text
							if($this->HREF)
								$this->PutLink($this->HREF,$e);
							else
								$this->Write(5,$e);
						}
						else
						{
							// Tag
							if($e[0]=='/')
								$this->CloseTag(strtoupper(substr($e,1)));
							else
							{
								// Extract attributes
								$a2 = explode(' ',$e);
								$tag = strtoupper(array_shift($a2));
								$attr = array();
								foreach($a2 as $v)
								{
									if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
										$attr[strtoupper($a3[1])] = $a3[2];
								}
								$this->OpenTag($tag,$attr);
							}
						}
					}
				}

				function OpenTag($tag, $attr)
				{
					// Opening tag
					if($tag=='B' || $tag=='I' || $tag=='U')
						$this->SetStyle($tag,true);
					if($tag=='A')
						$this->HREF = $attr['HREF'];
					if($tag=='BR')
						$this->Ln(5);
				}

				function CloseTag($tag)
				{
					// Closing tag
					if($tag=='B' || $tag=='I' || $tag=='U')
						$this->SetStyle($tag,false);
					if($tag=='A')
						$this->HREF = '';
				}

				function SetStyle($tag, $enable)
				{
					// Modify style and select corresponding font
					$this->$tag += ($enable ? 1 : -1);
					$style = '';
					foreach(array('B', 'I', 'U') as $s)
					{
						if($this->$s>0)
							$style .= $s;
					}
					$this->SetFont('',$style);
				}

				function PutLink($URL, $txt)
				{
					// Put a hyperlink
					$this->SetTextColor(0,0,255);
					$this->SetStyle('U',true);
					$this->Write(5,$txt,$URL);
					$this->SetStyle('U',false);
					$this->SetTextColor(0);
				}


			// Colored table
			function FancyTable($header, $transaction_array, $pdo, $patient_header, $contacts)
			{
				$this->SetTextColor(15,22,30);
				$this->SetFont('Arial','B',12);
				$this->Cell(0,8 ,'BALANCE STATEMENT','0',1,'C',false);
				$this->Cell(0,8 ,'','0',1,'L',false);
				$this->SetFillColor(255);
				//$this->SetTextColor(237,243,254);
				$this->SetFont('Arial','',10);
				//put header details
				$current_y = $this->GetY();
				$current_x = $this->GetX();
				$this->Cell(87,8 ,"",'0',0,'L',false);
				$current_y2 = $this->GetY();
				$current_x2 = $this->GetX();
				$this->SetXY($current_x , $current_y);
				$this->Write(8,"$patient_header");
				$this->SetXY($current_x2 , $current_y2);

				//empty middle cell
				$this->Cell(125,8 ,'','0',0,'L',false);
				$this->SetFillColor(255);
				//now print contacts
				$current_y = $this->GetY();
				$current_x = $this->GetX();
				$this->Cell(50,8 ,"",'0',0,'L',false);
				$this->SetXY($current_x , $current_y);
				$current_y2 = $this->GetY();
				$current_x2 = $this->GetX();
				$this->WriteHTML($contacts);
				//$this->MultiCell(55,8 ,"$contacts",'0',1,'L',false);
				$this->SetXY($current_x2 , $current_y2 + 30);
				$this->Ln();

				$ins_debit=$ins_credit=$self_debit=$self_credit=$points_credit=$points_debit=0;
				// Header
				$w = array(20, 58, 32, 32,30,30,30,30);
				$new_line=0;
				for($i=0;$i<count($header);$i++){
					//date , transaction
					//
						//date and description Colors, line width and bold font
						$this->SetFillColor(15,22,30); //#0F161E
						$this->SetTextColor(255);
						$this->SetDrawColor(21,33,47);#15212F;
						$this->SetLineWidth(.3);
						$this->SetFont('Arial','B',12);
					if($i ==0 or $i==4){	$wrapped="$header[$i]";}
					elseif($i ==1){$wrapped=wordwrap("$header[$i]", 21, "\n                 ", true);}
					elseif($i==2){$wrapped=wordwrap("$header[$i]", 11, "\n                                                                  ", true);}
					elseif($i==3){$wrapped=wordwrap("$header[$i]", 11, "\n                                                                                             ", true);}
					elseif($i==5){$wrapped=wordwrap("$header[$i]", 11, "\n                                                                                             ", true);}
					elseif($i==6){$wrapped=wordwrap("$header[$i]", 11, "\n                                                                                                                                                                            ", true);}
					elseif($i==7){$wrapped=wordwrap("$header[$i]", 11, "\n                                                                                                                                                                                                     ", true);}
					/*$current_y = $this->GetY();
					$current_x = $this->GetX();
					if($i==7){$new_line=1;}
					$this->MultiCell($w[$i],7,$header[$i],1,$new_line,'L',true);
					$this->SetXY($current_x + $w[$i], $current_y);
					*/
					//$this->Cell($w[$i],14 ,"$header[$i]",'1',$new_line,'L',$fill);
					$current_y = $this->GetY();
					$current_x = $this->GetX();
					$this->Cell($w[$i],14 ,'','1',0,'L',true);
					$this->SetXY($current_x , $current_y);
					$this->Write(7,"$wrapped");
					$this->SetXY($current_x + $w[$i], $current_y);

				}
				$this->Ln(13);
				// Color and font restoration
				$this->SetFillColor(224,235,255);
				$this->SetTextColor(0);
				$this->SetFont('Arial','',9);
				// Data
				$fill = true;
				$this->SetFillColor(237,243,254); //#0F161E
				$this->SetTextColor(0);
				foreach($transaction_array as $row)
				{
						$fill_text=false;
						$date=html($row['when_added']);
						$description=html($row['description']);
						$amount_value=html($row['amount_value']);
						$invoice_id=html($row['invoice_id']);
						$tx_type=html($row['tx_type']);
						$unauthorised_cost=html($row['unauthorised_cost']);
						$authorised_cost=html($row['authorised_cost']);
						$payment_type=html($row['payment_type']);
						$ceil_var=html($row['ceil_var']);
					//	$length=strlen($description);
						$wrapped=wordwrap("$description", 38, "\n                       ", true);
						$cell_height  = $ceil_var * 8;

					//	if($length > 38) {$fill_text=true;}
						//if($amount_value != 3000)continue;
						//payments
						if($tx_type==1 ){
							$data=explode('end',"$date");


							//echo "$description $donor_name<br>";
							//echo substr_count( $wrapped, "\n" );
							//echo "$wrapped\n";
							//$var="$var<tr><td >$data[0]</td><td bgcolor='#121923' color='#B0B3B6'>$description $donor_name</td>";
							//date and description

							$this->Cell($w[0],$cell_height ,"$data[0]",'1',0,'L',$fill);
							$current_y = $this->GetY();
							$current_x = $this->GetX();
							$this->Cell($w[1],$cell_height ,'','1',0,'L',$fill);
							$this->SetXY($current_x , $current_y);
						//	$this->Cell($w[1],6,"$description $donor_name",'1',0,'L',$fill);
							//$this->MultiCell($w[1],6,"$wrapped",'LTR',0,'L',$fill);
							//$this->SetTextColor(0,0,255);
							$this->Write(8,"$wrapped");
							//$this->Write($cell_height, "$description $donor_name");
							$this->SetXY($current_x + $w[1], $current_y);
							//check if it is insurance payment
							if($invoice_id!=0){
							//	$var="$var <td >&nbsp;</td><td >".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
								//invoice color
								//$this->SetFillColor(160,209,224); //#0F161E
								//$this->SetTextColor(0);
								$this->Cell($w[2],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[3],$cell_height,number_format($amount_value,2),'1',0,'L',$fill);
								//cash color
								//$this->SetFillColor(147,179,183); //#93B3B7
								//$this->SetTextColor(0);
								$this->Cell($w[4],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
								//points color
								//$this->SetFillColor(15,22,30); //#0F161E
								//$this->SetTextColor(255);
								$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
								$ins_credit = $ins_credit + $amount_value;
							}
							elseif($invoice_id==0){
								//check if points or self
								if($payment_type!='Points'){
									//$var="$var <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									//invoice color
									//$this->SetFillColor(160,209,224); //#0F161E
									//$this->SetTextColor(0);
									$this->Cell($w[2],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
									//cash color
									//$this->SetFillColor(147,179,183); //#93B3B7
									//$this->SetTextColor(0);
									$this->Cell($w[4],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[5],$cell_height,number_format($amount_value,2),'1',0,'L',$fill);
									//points color
									//$this->SetFillColor(15,22,30); //#0F161E
									//$this->SetTextColor(255);
									$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);

									$self_credit = $self_credit + $amount_value;
								}
								/*elseif($payment_type=='Points'){
									echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td></tr>";
									$points_debit = $points_debit + $authorised_cost;
								}*/
							}
						}

						//treatments
						elseif($tx_type==2){
							//check if it is insurance payment and treatment is invoiced
							if($payment_type=='Insurance' and $invoice_id == 0){continue;}
							//$var="$var <tr><td >$date</td><td  >$description</td>";
							$this->Cell($w[0],$cell_height ,"$date",'1',0,'L',$fill);
							$current_y = $this->GetY();
							$current_x = $this->GetX();
							$this->Cell($w[1],$cell_height ,'','1',0,'L',$fill);
							$this->SetXY($current_x , $current_y);
							$this->Write(8,"$wrapped");
							$this->SetXY($current_x + $w[1], $current_y);
							//check if it is insurance payment and treatment is invoiced
							if($payment_type=='Insurance'){
								//check if authorised cost==unauthorised_cost
								if($authorised_cost==''){
									//$var="$var <td >Un-authorised</td><td >&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$this->Cell($w[2],$cell_height,'Un-authorised','1',0,'L',$fill);
									$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[4],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
									//$ins_debit = $ins_debit + $unauthorised_cost;
								}
								elseif($unauthorised_cost!=$authorised_cost){
									//$var="$var <td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>".number_format(($unauthorised_cost - $authorised_cost),2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$this->Cell($w[2],$cell_height,number_format($authorised_cost,2),'1',0,'L',$fill);
									$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[4],$cell_height,number_format(($unauthorised_cost - $authorised_cost),2),'1',0,'L',$fill);
									$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
									$ins_debit = $ins_debit + $authorised_cost;
									$self_debit = $self_debit + $unauthorised_cost - $authorised_cost;
								}
								elseif($unauthorised_cost==$authorised_cost){
									//$var="$var <td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$this->Cell($w[2],$cell_height,number_format($authorised_cost,2),'1',0,'L',$fill);
									$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[4],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
									$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
									$ins_debit = $ins_debit + $authorised_cost;
								}
							}
							elseif($payment_type=='Self'){
								//$var="$var <td>&nbsp;</td><td>&nbsp;</td><td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
								$this->Cell($w[2],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[4],$cell_height,number_format($authorised_cost,2),'1',0,'L',$fill);
								$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
								$self_debit = $self_debit + $authorised_cost;
							}
							elseif($payment_type=='Points'){
								//$var="$var <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td></tr>";
								$this->Cell($w[2],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[4],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
								$this->Cell($w[6],$cell_height,number_format($authorised_cost,2),'1',0,'L',$fill);
								$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
								$points_debit = $points_debit + $authorised_cost;
							}
						}

						//prescription
						elseif($tx_type==3){
							//$var="$var <tr><td  >$date</td><td  >$description</td>";
							//$var="$var <td>&nbsp;</td><td >&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$this->Cell($w[0],$cell_height ,"$date",'1',0,'L',$fill);
							$current_y = $this->GetY();
							$current_x = $this->GetX();
							$this->Cell($w[1],$cell_height ,'','1',0,'L',$fill);
							$this->SetXY($current_x , $current_y);
							$this->Write(8,"$wrapped");
							$this->SetXY($current_x + $w[1], $current_y);

							$this->Cell($w[2],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[4],$cell_height,number_format($amount_value,2),'1',0,'L',$fill);
							$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
							$self_debit = $self_debit + $amount_value;
						}

						//points
						elseif($tx_type==4){
							//$var="$var <tr><td  >$date</td><td  >$description</td>";
							//$var="$var <td >&nbsp;</td><td >&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td></tr>";
							$this->Cell($w[0],$cell_height ,"$date",'1',0,'L',$fill);
							$current_y = $this->GetY();
							$current_x = $this->GetX();
							$this->Cell($w[1],$cell_height ,'','1',0,'L',$fill);
							$this->SetXY($current_x , $current_y);
							$this->Write(8,"$wrapped");
							$this->SetXY($current_x + $w[1], $current_y);

							$this->Cell($w[2],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[4],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[7],$cell_height,number_format($amount_value,2),'1',0,'L',$fill);
							$points_credit = $points_credit + $amount_value;
						}

						//credit trasnfered
						elseif($tx_type==5){
							//$var="$var <tr><td >$date</td><td  >$description</td>";
							//$var="$var <td >&nbsp;</td><td >&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$this->Cell($w[0],$cell_height ,"$date",'1',0,'L',$fill);
							$current_y = $this->GetY();
							$current_x = $this->GetX();
							$this->Cell($w[1],$cell_height ,'','1',0,'L',$fill);
							$this->SetXY($current_x , $current_y);
							$this->Write(8,"$wrapped");
							$this->SetXY($current_x + $w[1], $current_y);

							$this->Cell($w[2],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[3],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[4],$cell_height,number_format($amount_value,2),'1',0,'L',$fill);
							$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
							$self_debit = $self_debit + $amount_value;
						}

						//co-payment
						elseif($tx_type==6){
							//$var="$var <tr><td >$date</td><td >$description</td>";
							//$var="$var <td >&nbsp;</td><td >".number_format($amount_value,2)."</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$this->Cell($w[0],$cell_height ,"$date",'1',0,'L',$fill);
							$current_y = $this->GetY();
							$current_x = $this->GetX();
							$this->Cell($w[1],$cell_height ,'','1',0,'L',$fill);
							$this->SetXY($current_x , $current_y);
							$this->Write(8,"$wrapped");
							$this->SetXY($current_x + $w[1], $current_y);

							$this->Cell($w[2],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[3],$cell_height,number_format($amount_value,2),'1',0,'L',$fill);
							$this->Cell($w[4],$cell_height,number_format($amount_value,2),'1',0,'L',$fill);
							$this->Cell($w[5],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[6],$cell_height,'','1',0,'L',$fill);
							$this->Cell($w[7],$cell_height,'','1',0,'L',$fill);
							$self_debit = $self_debit + $amount_value;
							$ins_credit = $ins_credit + $amount_value;
						}
						$this->Ln();
						$fill=!$fill;

				}
				//show totals
					//$var="$var <tr class='totals'><td  >TOTALS</td><td class=bal_ins>".number_format($ins_debit,2)."</td><td>".number_format($ins_credit,2)."</td>
					//<td id=self_bal1>".number_format($self_debit,2)."</td><td>".number_format($self_credit,2)."</td>
					//<td id=points_bal1>".number_format($points_debit,2)."</td><td>".number_format($points_credit,2)."</td></tr>";
					$this->Cell($w[0] + $w[1],8 ,'TOTALS','1',0,'L',$fill);
					$this->Cell($w[2],8,number_format($ins_debit,2),'1',0,'L',$fill);
					$this->Cell($w[3],8,number_format($ins_credit,2),'1',0,'L',$fill);
					$this->Cell($w[4],8,number_format($self_debit,2),'1',0,'L',$fill);
					$this->Cell($w[5],8,number_format($self_credit,2),'1',0,'L',$fill);
					$this->Cell($w[6],8,number_format($points_debit,2),'1',0,'L',$fill);
					$this->Cell($w[7],8,number_format($points_credit,2),'1',0,'L',$fill);
					$this->Ln();
					$ins_bal= $ins_debit -$ins_credit ;
					$self_bal= $self_debit - $self_credit;
					$points_bal= $points_debit - $points_credit;

					if($ins_bal!=''){$ins_bal=number_format($ins_bal,2);}
					if($self_bal!=''){$self_bal=number_format($self_bal,2);}
					if($points_bal!=''){$points_bal=number_format($points_bal,2);}

					//$var="$var <tr id='totals2'><td >BALANCE</td><td colspan=2 class=bal_ins>$ins_bal</td><td colspan=2 id=self_bal2>$self_bal</td><td colspan=2 id=points_bal2>$points_bal</td></tr>";
					$this->SetFillColor(15,22,30); //#0F161E
					$this->SetTextColor(255);
					$this->SetDrawColor(21,33,47);#15212F;
					$this->SetLineWidth(.3);
					$this->SetFont('Arial','',12);
					$this->Cell($w[0] + $w[1],8 ,'BALANCE','1',0,'L',true);
					$this->Cell($w[2] + $w[3],8,"$ins_bal",'1',0,'L',true);
					$this->Cell($w[4] + $w[5],8,"$self_bal",'1',0,'L',true);
					$this->Cell($w[6] + $w[7],8,"$points_bal",'1',0,'L',true);
					$this->Ln();
				// Closing line
				$this->Cell(array_sum($w),0,'','T');
			}
		}
		//check if email body and subject are set
		//if($_POST['email_subject']==''){echo "ERROR: Please specify the subject of the email";exit;}
		//if($_POST['email_body']==''){echo "ERROR: Please specify the body of the email";exit;}
		$i=$n=0;
		$data=$_POST['send_email'];
		$n=count($data);
		if($n==0){echo "ERROR: No email address has been selected";exit;}
		$pdf_height=297;
		$fail_error='';
		$today=date('Y-m-d');
		$header = array('DATE', 'TRANSACTION DESCRIPTION', 'INSURANCE DEBIT', 'INSURANCE CREDIT',
			'CASH DEBIT', 'CASH CREDIT', 'POINTS DEBIT', 'POINTS CREDIT');
		//echo "sending";
		while($i < $n){
		//echo "$i --";
			//$var=html("$balance#$patient#$pnum#$email1#$email2");
			$result1=$encrypt->decrypt("$data[$i]");
			$result=explode('#',"$result1");
			$balance=number_format($result[0],2);
			$patient_name="$result[1]";
			$patient_no="$result[2]";
			$email1="$result[3]";
			$email2="$result[4]";
			$pid="$result[5]";
			//get insurer and corprate if any
			$sql=$error=$s='';$placeholders=array();
			$sql="select insurance_company.name,covered_company.name
					from patient_details_a left join insurance_company on patient_details_a.type=insurance_company.id
					left join covered_company on patient_details_a.company_covered=covered_company.id
					where pid=:pid";
			$placeholders['pid']=$pid;
			$error="unable to get patient type";
			$s = select_sql($sql, $placeholders, $error, $pdo);
			$company=$insurer='';
			foreach($s as $row){
				$insurer=html("$row[0]");
				if($row[1]!=''){$company=html(" - $row[1]");}
			}

			$to_email_address="$email1";
			$to_email_address2="$email2";
			$to_email_name="$patient_name";
			$subject='Molars Dental Clinic - Balance Statement';
			$body="Dear $patient_name<br><br>Please find attached your balance statement as at $today.<br><br>
					You have an outstanding cash balance of $balance.<br><br>
					Please make arrangements to clear the same to enable us serve you better.<br><br>
					For any enquiry please don't hesitate to contact us.<br><br>
					Regards,<br>
					Molars Dental Clinic";

			// Column headings

			// Data loading
			//$return1=email_pt_statement($pdo,$pid,$encrypt);
			//$return2=explode('#',"$return1");
			//echo "pid is $pid";
			//$transaction_array=array()
			$transaction_array=email_pt_statement($pdo,$pid,$encrypt);
			$ceil_total=$transaction_array[count($transaction_array) - 1]['ceil_var'];
			array_pop($transaction_array);
			//echo "ceil total is $ceil_total";
			//print_r($transaction_array[count($transaction_array) - 1]);
			$today=date('Y-m-d');
			if(($ceil_total * 8) > 250){$pdf_height=$ceil_total * 8;}
			$pdf = new PDF('P','mm',array(282,$pdf_height));//$ceil_total * 8
			$pdf->SetFont('Arial','',14);
			$pdf->AddPage();

			$patient_header="PATIENT NAME: $patient_name\nPATIENT NUMBER: $patient_no\nPATIENT TYPE: $insurer $company\nSTATEMENT DATE: $today";
			$space_filler1="                                                                                                                                                                                                                                         ";
			$space_filler2="                                                                                                                                                                                                                                 ";
			$space_filler3="                                                                                                                                                                                                                       ";
			$space_filler4="                                                                                                                                                                                                                                           ";
			$space_filler5="                                                                                                                                                                                                                               ";

			$contacts="CUSPID DENTAL\n$space_filler1 3rd Flr Commonwealth House\n$space_filler2 Moi Avenue City Centre\n$space_filler3 Tel: 020 242 8104\n$space_filler4 Mobile: 0702974551\n$space_filler5 Email: <a href='mailto:info@cuspiddental.co.ke'>info@cuspiddental.co.ke</a>";

			$pdf->FancyTable($header,$transaction_array, $pdo, $patient_header, $contacts);
			$pdf->Output("pdfs/$patient_name".".pdf");//"pdfs/$patient_name".".pdf"
			// Clear all addresses and attachments for next loop
			$mail->ClearAllRecipients();
				$mail->ClearReplyTos();
				$mail->ClearAttachments();
					$mail->AddAttachment("pdfs/$patient_name".".pdf");
			//send email 1
			if($email1!=''){
				$to_email_address="$email1";
				//$send_status=send_email($mail, $smtp_host, $smtp_username, $smtp_password, $from_email_address, $from_name, $to_email_address,$to_email_name, $subject, $body, $pid, $to_email_address2);
				$send_status=send_email($mail, $to_email_address,$to_email_name, $subject, $body, $pid);

				if($send_status!='good'){
					if($fail_error==''){
						$fail_error="<table class=normal_table><thead><tr><th>PATIENT NUMBER</th><th>ERROR</th></tr></thead><tbody>";
						$fail_error="$fail_error<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";
					}
					else{$fail_error="$fail_error<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";}
				}
			}
			//send email 2
			if($email2!=''){
				$to_email_address="$email2";
				//$send_status=send_email($mail, $smtp_host, $smtp_username, $smtp_password, $from_email_address, $from_name, $to_email_address,$to_email_name, $subject, $body, $pid, $to_email_address2);
				$send_status=send_email($mail, $to_email_address,$to_email_name, $subject, $body, $pid);

				if($send_status!='good'){
					if($fail_error==''){
						$fail_error="<table class=normal_table><thead><tr><th>PATIENT NUMBER</th><th>ERROR</th></tr></thead><tbody>";
						$fail_error="$fail_error<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";
					}
					else{$fail_error="$fail_error<tr><td>$patient_no</td><td>".html($send_status)."</td></tr>";}
				}
			}
			$i++;
		}
		if($fail_error==''){echo "All messages sent";}
		else{$fail_error="$fail_error</tbody></table>";
			echo "$fail_error";
		}
}

//this will submit doc id for re-appointment of appointments so the r-appointment is finifshed here
elseif(isset($_SESSION['token_set_re_app1']) and 	isset($_POST['token_set_re_app1']) and
$_POST['token_set_re_app1']==$_SESSION['token_set_re_app1'] and userHasRole($pdo,80)){
	$exit_flag=false;
	if($_POST['doctor']==''){
		$exit_flag=true;
		$message="bad#re_appointment#Please select the doctor for the re-appointment";
	}
	if(!$exit_flag){
		try{
			$pdo->beginTransaction();
				//get appointment_id and wheter it's registered or unregistered
				//echo "xx  $_SESSION[re_appoint_id]  xx ";

				$data=explode('#',"$_SESSION[re_appoint_id]");
				$old_appointment_id=$data[1];
				if($data[2]=='yes'){$registered=" registered_patient_appointments ";}
				elseif($data[2]=='NO'){$registered=" unregistered_patient_appointments ";}
				$pid=$data[3];
				$doc_id=$encrypt->decrypt($_POST['doctor']);
			//	echo "$_SESSION[new_appointment2] -- 1437";
				$data=explode('#',$_SESSION['new_appointment2']);
				$rank=$data[0];
				$min=$data[1];
				$surgical_unit=$data[2];
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into $registered set when_added=now(),
						doc_id=:doc_id,
						pid=:pid,
						appointment_date=:appointment_date,
						shour=:shour,
						smin=:smin,
						rank=:rank,
						am_pm=:am_pm,
						surgical_unit=:surgical_unit,
						added_by=:added_by";
				$error="Unable to get add appointment";
				$placeholders[':doc_id']=$doc_id;
				$placeholders[':added_by']=$_SESSION['id'];
				$placeholders[':pid']=$pid;
				$placeholders[':appointment_date']=$_SESSION['appointment_date'];
				if($rank > 12){
						$hour=$rank - 12;
						$am_pm="PM";
				}
				else{
					$hour = $rank;
					$am_pm="AM";
				}
				$placeholders[':shour']=$hour;
				$placeholders[':smin']=$min;
				$placeholders[':rank']=$rank;
				$placeholders[':am_pm']=$am_pm;
				$placeholders[':surgical_unit']=$surgical_unit;
				$new_appoint_id = 	get_insert_id($sql, $placeholders, $error, $pdo);

				//put the new_appoint_id into the old re-appointed appointment
				$sql=$error=$s='';$placeholders=array();
				$sql="update $registered set new_appointment_id=:new_appointment_id , status=:status where id=:old_id";
				$error="Unable to update old appointment id";
				$placeholders[':new_appointment_id']=$new_appoint_id;
				$placeholders[':status']='RE-APPOINTED';
				$placeholders[':old_id']=$old_appointment_id;
				$s = insert_sql($sql, $placeholders, $error, $pdo);
				if($s){
					$pdo->commit();
					$message = "good#appointment_re_appointed#$_SESSION[appointment_date]#New Appointment created";
				}
				else{$pdo->rollBack();}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#Unable to save patient disease details  ";
		}

	}
	echo "$message";
}

//this will set doctor for re-appointment
elseif(isset($_POST['get_re_appoint_doc']) and $_POST['get_re_appoint_doc']!='' and userHasRole($pdo,80) ){
//echo "$_SESSION[new_appointment2] -- $_SESSION[id] -- $_SESSION[logged_in_user_names] -- 1497";
	?>

	<div id=registered_appointment>
			<div class='feedback hide_element'></div>
			<form class='patient_form check_selected_patient' action="" method="post" name="" id="">
				<?php $token = form_token(); $_SESSION['token_set_re_app1'] = "$token";  ?>
				<input type="hidden" name="token_set_re_app1"  value="<?php echo $_SESSION['token_set_re_app1']; ?>" />

				<?php
				//check if re-appointment and select old doctor
					if(isset($_SESSION['re_appoint_id']) and $_SESSION['re_appoint_id']!=''){
						$d1=explode('#',"$_SESSION[re_appoint_id]");
						$appointment_table1='';
						if("$d1[2]" == 'NO'){$appointment_table1='unregistered_patient_appointments';}//unregistered
						elseif("$d1[2]" == 'yes'){$appointment_table1='registered_patient_appointments';}//registered

						$sql=$error=$s='';$placeholders=array();
						$sql="select doc_id from $appointment_table1 where id=:id";
						$placeholders[':id']=$d1[1];
						$error="Unable to originial appointment doctor";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){$old_doc_id=$row['doc_id'];}
					}

				//select doctor
				$sql=$error=$s='';$placeholders=array();
				$sql="select id,first_name, middle_name,last_name from users where user_type=1 and status='active'";
				$error="Unable to get list of doctors";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				echo "<div class='grid-20'><label for='' class='label'>Select Doctor</label></div>";
				echo "<div class='grid-25'><select class=appointment_doctor name=doctor><option></option>";
					foreach($s as $row){
						$selected='';
						if($old_doc_id == $row['id']){$selected = 'selected';}
						$doctor_name=html("$row[first_name] $row[middle_name] $row[last_name]");
						$doc_id=$encrypt->encrypt("$row[id]");
						echo "<option value='$doc_id' $selected>$doctor_name</option>";
					}
				echo "</select></div>";
				echo "<div class=clear></div><br>";?>
				<div class='prefix-20 show_doc_appointments grid-80'>
					<?php
					if(isset($_SESSION['re_appoint_id']) and $_SESSION['re_appoint_id']!=''){
						$doc_id=$old_doc_id;
						$date_of_appointment=html($_SESSION['appointment_date']);
						//get docotor appointmateds for unregisterad
						$sql=$error=$s='';$placeholders=array();
						$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names, b.shour, b.smin, b.rank ,b.am_pm , c.first_name,c.middle_name,c.last_name
							from unregistered_patient_appointments as b join unregistered_patients as a on b.pid=a.id and b.doc_id=:doc_id
							and b.appointment_date=:appointment_date
							join users as c on c.id=b.doc_id";
						$error="Unable to get unregisterd apponitments for doctors";
						$placeholders['doc_id']=$doc_id;
						$placeholders['appointment_date']=$_SESSION['appointment_date'];
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						$appoint_array=array();
						foreach($s as $row){
							$doctor_name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
							$patient_name=ucfirst(html($row['names']));
							$time=html("$row[shour]:$row[smin] $row[am_pm]");
							$rank=html($row['rank']);
							$smin=html($row['smin']);
							$appoint_array[]=array('doctor'=>"$doctor_name", 'patient_name'=>"$patient_name", 'time'=>"$time", 'rank'=>"$rank", 'smin'=>"$smin");
						}

						//get docotor appointmateds for registerad
						$sql=$error=$s='';$placeholders=array();
						$sql="select a.first_name,a.middle_name,a.last_name, b.shour, b.smin, b.rank ,b.am_pm , c.first_name,c.middle_name,c.last_name
							from registered_patient_appointments as b join patient_details_a as a on b.pid=a.pid and b.doc_id=:doc_id
							and b.appointment_date=:appointment_date
							join users as c on c.id=b.doc_id";
						$error="Unable to get registerd apponitments for doctors";
						$placeholders['doc_id']=$doc_id;
						$placeholders['appointment_date']=$_SESSION['appointment_date'];
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$doctor_name=ucfirst(html("$row[7] $row[8] $row[9]"));
							$patient_name=ucfirst(html("$row[0] $row[1] $row[2]"));
							$time=html("$row[shour]:$row[smin] $row[am_pm]");
							$rank=html($row['rank']);
							$smin=html($row['smin']);
							$appoint_array[]=array('doctor'=>"$doctor_name", 'patient_name'=>"$patient_name", 'time'=>"$time", 'rank'=>"$rank",  'smin'=>"$smin");
						}

						if(count($appoint_array) > 0){
							foreach ($appoint_array as $key => $row) {
								$rank1[$key]  = $row['rank'];
								$smin1[$key]  = $row['smin'];
							}

							// Sort the data with when_added
							array_multisort($rank1, SORT_ASC,$smin1, SORT_ASC, $appoint_array);
							$i=0;
							foreach($appoint_array as $row){
								if($i==0){
									echo "<table class='normal_table'><caption>Appointments for Dr. $doctor_name on $date_of_appointment</caption><thead>
										<tr><th class='ds_count'></th><th class='ds_pname'>PATIENT NAME</th><th class='ds_time'>TIME</th></tr></thead><tbody>";
								}
								$i++;
								echo "<tr><td>$i</td><td>$row[patient_name]</td><td>$row[time]</td></tr>";

							}
							echo "</tbody></table>";
						}
						else{echo "<div class='label'>There are no appointments for the doctor on the day</div>";}
					}
					?>
				</div>
				<div class=clear></div><br>
				<div class='prefix-20 grid-15'><input class='' type=submit value='Book Appointment' /></div></form><?php
	echo "</div>";//this is for registered_appointment div
	echo "<div id=unregistered_appointment></div>";
//echo "$_SESSION[new_appointment2]  -- $_SESSION[id] -- $_SESSION[logged_in_user_names] --  1526";
}

//this will show size for selected manufacurer
elseif(isset($_POST['upper_category']) and $_POST['upper_category']!='' and   userHasRole($pdo,67)){
	//get element and it's level
	$item_id=$encrypt->decrypt($_POST['upper_category']);
	$sql=$error=$s='';$placeholders=array();
	$sql="select level from cadcam_types where id=:id";
	$error="Unable to determine level of cadcam category";
	$placeholders[':id']=$item_id;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){$level=$row['level'];}

	if($level==1){//this will show sizes
			$sql=$error=$s='';$placeholders=array();
			$sql="select id, name, listed from cadcam_types where parent_id=:parent_id";
			$error="Unable to determine next level of cadcam category";
			$placeholders[':parent_id']=$item_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$i=0;
			foreach($s as $row){
				$name=html($row['name']);
				$var=$encrypt->encrypt($row['id']);
				$checked='';
				if($row['listed']==1){$checked=" checked ";}
				if($i==0){
					echo "<div class='grid-50'><label class=label>TYPE</label></div>
						<div class='grid-10'><label class=label>UNLIST</label></div><br>";
				}
				echo "<div class='prefix-15 grid-50'><input type=text name=old_size[] value=$name /></div>";
				echo "<div class='grid-10'><input type=checkbox name=unlist_size[] $checked value=$var /><input type=hidden name=old_size2[] value=$var /></div>";
				echo "<div class=clear></div>";
				$i++;
			}
		//echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_manufacturer[]  /></div>";
	}

}

//this will show shade for selected type
elseif(isset($_POST['upper_category6']) and $_POST['upper_category6']!='' and   userHasRole($pdo,67)){
	//get element and it's level
	$item_id=$encrypt->decrypt($_POST['upper_category6']);
	/*$sql=$error=$s='';$placeholders=array();
	$sql="select level from cadcam_types where id=:id";
	$error="Unable to determine level of cadcam category";
	$placeholders[':id']=$item_id;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){$level=$row['level'];}*/

	//if($level==1){//this will show sizes
			$sql=$error=$s='';$placeholders=array();
			$sql="select id, name, listed from cadcam_types where parent_id=:parent_id";
			$error="Unable to determine next level of cadcam category";
			$placeholders[':parent_id']=$item_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$i=0;
			foreach($s as $row){
				$name=html($row['name']);
				$var=$encrypt->encrypt($row['id']);
				$checked='';
				if($row['listed']==1){$checked=" checked ";}
				if($i==0){
					echo "<div class='grid-50'><label class=label>SHADE</label></div>
						<div class='grid-10'><label class=label>UNLIST</label></div><br>";
				}
				echo "<div class='prefix-15 grid-50'><input type=text name=old_shade[] value=$name /></div>";
				echo "<div class='grid-10'><input type=checkbox name=unlist_shade[] $checked value=$var /><input type=hidden name=old_shade2[] value=$var /></div>";
				echo "<div class=clear></div>";
				$i++;
			}
		//echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_manufacturer[]  /></div>";
	//}

}

//this will show type for selected size
elseif(isset($_POST['upper_category3']) and $_POST['upper_category3']!='' and   userHasRole($pdo,67)){
	//get element and it's level
	$item_id=$encrypt->decrypt($_POST['upper_category3']);
	/*$sql=$error=$s='';$placeholders=array();
	$sql="select level from cadcam_types where id=:id";
	$error="Unable to determine level of cadcam category";
	$placeholders[':id']=$item_id;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){$level=$row['level'];}*/

	//if($level==1){//this will show sizes
			$sql=$error=$s='';$placeholders=array();
			$sql="select id, name, listed from cadcam_types where parent_id=:parent_id";
			$error="Unable to determine next level of cadcam category";
			$placeholders[':parent_id']=$item_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$i=0;
			foreach($s as $row){
				$name=html($row['name']);
				$var=$encrypt->encrypt($row['id']);
				$checked='';
				if($row['listed']==1){$checked=" checked ";}
				if($i==0){
					echo "<div class='grid-50'><label class=label>SIZE</label></div>
						<div class='grid-10'><label class=label>UNLIST</label></div><br>";
				}
				echo "<div class='prefix-15 grid-50'><input type=text name=old_type[] value=$name /></div>";
				echo "<div class='grid-10'><input type=checkbox name=unlist_type[] $checked value=$var /><input type=hidden name=old_type2[] value=$var /></div>";
				echo "<div class=clear></div>";
				$i++;
			}
		//echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_manufacturer[]  /></div>";
	//}

}

//this will show size for selected manufacurer in level 3
elseif(isset($_POST['upper_category2']) and $_POST['upper_category2']!='' and   userHasRole($pdo,67)){
	//get element and it's level
	$item_id=$encrypt->decrypt($_POST['upper_category2']);
	/*$sql=$error=$s='';$placeholders=array();
	$sql="select level from cadcam_types where id=:id";
	$error="Unable to determine level of cadcam category";
	$placeholders[':id']=$item_id;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){$level=$row['level'];}*/

	//if($level==1){//this will show sizes
			$sql=$error=$s='';$placeholders=array();
			$sql="select id, name  from cadcam_types where listed=0 and parent_id=:parent_id";
			$error="Unable to determine next level of cadcam category";
			$placeholders[':parent_id']=$item_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$i=0;
			echo "<option></option>";
			foreach($s as $row){
				$name=html($row['name']);
				$var=$encrypt->encrypt($row['id']);
				echo "<option value=$var>$name</option>";
				$i++;
			}
		//echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_manufacturer[]  /></div>";
	//}

}

//this will show size for selected manufacurer in level 4
elseif(isset($_POST['upper_category4']) and $_POST['upper_category4']!='' and   userHasRole($pdo,67)){
	//get element and it's level
	$item_id=$encrypt->decrypt($_POST['upper_category4']);

	//if($level==1){//this will show sizes
			$sql=$error=$s='';$placeholders=array();
			$sql="select id, name  from cadcam_types where listed=0 and parent_id=:parent_id";
			$error="Unable to determine next level of cadcam category";
			$placeholders[':parent_id']=$item_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$i=0;
			echo "<option></option>";
			foreach($s as $row){
				$name=html($row['name']);
				$var=$encrypt->encrypt($row['id']);
				echo "<option value=$var>$name</option>";
				$i++;
			}
		//echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_manufacturer[]  /></div>";
	//}

}

//this will show type for selected size in level 4
elseif(isset($_POST['upper_category5']) and $_POST['upper_category5']!='' and   userHasRole($pdo,67)){
	//get element and it's level
	$item_id=$encrypt->decrypt($_POST['upper_category5']);

	//if($level==1){//this will show sizes
			$sql=$error=$s='';$placeholders=array();
			$sql="select id, name  from cadcam_types where listed=0 and parent_id=:parent_id";
			$error="Unable to determine next level of cadcam category";
			$placeholders[':parent_id']=$item_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$i=0;
			echo "<option></option>";
			foreach($s as $row){
				$name=html($row['name']);
				$var=$encrypt->encrypt($row['id']);
				echo "<option value=$var>$name</option>";
				$i++;
			}
		//echo "<div class=clear></div><br><div class='prefix-15 grid-50'><input type=text name=new_manufacturer[]  /></div>";
	//}

}

//this will return treatments to be aliaesd
elseif(isset($_POST['gap2']) and $_POST['gap2']!='' and isset($_POST['element_sel']) and $_POST['element_sel']!='' and
	isset($_POST['tplan_name']) and $_POST['tplan_name']!='' and   userHasRole($pdo,113)){
	$gap2=$encrypt->decrypt($_POST['gap2']);
	$tplan_id=explode('ta',$gap2);//this has the tplan procedure numebr to be aliaesd it's not procedure id
	$treatment_name=html($_POST['tplan_name']);//procedure name to be alised
	$current_procedure=explode('procedure',$_POST['element_sel']);//this has the tplan procedure numebr that willact as an alias it is not procedure id
	$val=$encrypt->encrypt("$current_procedure[1]#$tplan_id[1]");
	echo "<input type=checkbox name=aliasest[] value=$val /> $treatment_name <br>";


}

//this will determine oif teeth need to be specified for a xray
elseif(isset($_POST['xray_type']) and $_POST['xray_type']!='' and   userHasRole($pdo,18)){
	$sql=$error=$s='';$placeholders=array();
	$sql="select all_teeth from procedures where id=:xray_id";
	$error="Unable to determine if xray needs for teeth to be specified";
	$placeholders[':xray_id']=$encrypt->decrypt($_POST['xray_type']);
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		if($row['all_teeth']=='yes'){echo "show_teeth";}
		elseif($row['all_teeth']=='no'){echo "do_not_show_teeth";}
	}
}

//this will determine oif teeth need to be specified for a xray in edit tplan
elseif(isset($_POST['add_procedure3']) and $_POST['add_procedure3']!='' and   userHasRole($pdo,51)){
	$sql=$error=$s='';$placeholders=array();
	$xray_id=$encrypt->decrypt($_POST['add_procedure3']);
	$sql="select all_teeth from teeth_and_xray_types where id=:xray_id";
	$error="Unable to determine if xray needs for teeth to be specified";
	$placeholders[':xray_id']=$xray_id;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		if($row['all_teeth']=='yes'){echo "show_teeth";}
		elseif($row['all_teeth']=='no'){echo "do_not_show_teeth";}
	}
}


//this will determine oif teeth need to be specified for a procedure
elseif(isset($_POST['add_procedure']) and $_POST['add_procedure']!='' and   userHasRole($pdo,17)){
	$sql=$error=$s='';$placeholders=array();
	$procedure_id=$encrypt->decrypt($_POST['add_procedure']);
	$sql="select all_teeth from procedures where id=:procedure_id";
	$error="Unable to determine if procedure needs for teeth to be specified";
	$placeholders[':procedure_id']=$procedure_id;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		if($row['all_teeth']=='yes'){echo "show_teeth";}
		elseif($row['all_teeth']=='no'){echo "do_not_show_teeth";}
	}
	/*// if xray get xray types
	if($procedure_id == 1){
		$sql=$error=$s='';$placeholders=array();
		//$procedure_id=$encrypt->decrypt($_POST['add_procedure']);
		$sql="select id,name from teeth_and_xray_types";
		$error="Unable to get xray types";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			echo "ninye<option></option>";
			foreach($s as $row){
				$xray_id=$encrypt->encrypt($row['id']);
				$xray_name=html($row['name']);
				echo "<option value=$xray_id>$xray_name</option>";
			}
			echo "</select>";
		}
		else{echo "ninyenone";}
	}
	else{echo "ninyenone";}*/
}

//this will be used to determine if teeth need to be specified for a procedure or not in edit tplan
elseif(isset($_POST['add_procedure2']) and $_POST['add_procedure2']!='' and   userHasRole($pdo,51)){
	$sql=$error=$s='';$placeholders=array();
	$procedure_id=$encrypt->decrypt($_POST['add_procedure2']);
	$data=explode('#',"$procedure_id");
	//echo "data[0]-$data[0]  and data[1]-$data[1]";
	if(!isset($data[1])){
		$sql="select all_teeth from procedures where id=:procedure_id";
		$error="Unable to determine if procedure needs for teeth to be specified";
		$placeholders[':procedure_id']=$procedure_id;
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			if($row['all_teeth']=='yes'){echo "show_teeth";}
			elseif($row['all_teeth']=='no'){echo "do_not_show_teeth";}
		}
	}
	// if xray get xray types
	elseif(isset($data[1])){
			$sql=$error=$s='';$placeholders=array();
			//$procedure_id=$encrypt->decrypt($_POST['add_procedure']);
			$sql="select id,name,all_teeth from teeth_and_xray_types where id=:xray_id";
			$error="Unable to get xray teeth specification";
			$placeholders[':xray_id']=$data[1];
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			foreach($s as $row){
				if($row['all_teeth']=='yes'){echo "show_teeth";}
				elseif($row['all_teeth']=='no'){echo "do_not_show_teeth";}
			}
	}
}


//this will add extra procedure in treatment plan
elseif(isset($_POST['extra_procedure']) and $_POST['extra_procedure']!='' ){
				//show procedures
				$i = $_POST['extra_procedure'] + 1;
				echo "<div class='grid-100 tplan_procedures hover '>";
					echo "<div class='grid-5 procedure_count'>$i<input type=hidden name=nisiana[] /></div>";
					echo "<div class='grid-45 grid-parent'>";
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name,id,all_teeth from procedures where listed=0 order by name";
						$error2="Unable to get prodcedures";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						if($s2->rowCount()>0){
							echo "<select name=procedure$i class='input_in_table_cell select_procedure' ><option></option>";
							foreach($s2 as $row2){
								$procedure=html($row2['name']);
								$val2=$encrypt->encrypt(html($row2['id']));
								echo "<option value='$val2'>$procedure</option>";
							}
							echo "</select>";
						}
					else{echo "&nbsp;";}?>
					<div class='grid-100 teeth_div '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body2'>
								<?php
								$i2=8;
								$teeth_specified="teeth_specified$i"."[]";
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
								<div class='teeth_body2'>
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
								<div class='teeth_body2'>
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
								<div class='teeth_body2'>
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

					<?php
					//check if this patient type is insured or not
					$insured='NO';
					$sql=$error=$s='';$placeholders=array();
					$sql="select insured from covered_company where id=:covered_company";
					$error="Unable to check if the company is insured";
					$placeholders['covered_company']=$_SESSION['company_covered'];
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$insured=html($row['insured']);}

					echo "</div>";
					echo "<div class='grid-25'><textarea   rows='' name=details$i ></textarea></div>";
					echo "<div class='grid-15'>";
						$invoice_pay=$encrypt->encrypt("1");
						$cash_pay=$encrypt->encrypt("2");
						$points_pay=$encrypt->encrypt("3");
						echo "<select name=pay_method$i class='input_in_table_cell pay_method' ><option></option>";
						if($insured == 'YES' and !$_SESSION['ins_suspend']){echo "<option value='$invoice_pay'>Insurance</option>";}
						echo "<option value='$cash_pay'>Self</option><option value='$points_pay'>Points</option>";
								/*
								<option value='$invoice_pay'>Insurance</option>
								<option value='$cash_pay'>Self</option>
								<option value='$points_pay'>Points</option>";*/
						echo "</select>";
					echo "</div>";
					echo "<div class='grid-10'><input type=text class=tplan_cost  name=cost$i />";
					if(userHasRole($pdo,113)){
						$alias_val=$encrypt->encrypt("ta$i");
						//echo "Aliased <input disabled class=tplan_alias type=checkbox name=tplan_alias$i value=$alias_val />";
					}
					echo "</div>";
					//echo "<div class='grid-10'><input type=text class=tplan_discount  name=discount$i /></div>";
				echo "</div>";
				echo "<div class=clear></div>";

}

//this will set the tab id to be submitted to for patient tabs
elseif(isset($_POST['get_patient_balance']) and $_POST['get_patient_balance']=='yes'){
	show_patient_balance($pdo,'a');
	//echo "set";
}

//this is for adding a new manucturer or editing current ones
elseif(isset($_SESSION['token_cs1']) and 	isset($_POST['token_cs1']) and $_POST['token_cs1']==$_SESSION['token_cs1'] and userHasRole($pdo,67)){
		$exit_flag=false;
		try{
			$pdo->beginTransaction();

			//now add new manuacturers
			$new_manuf=$_POST['new_manufacturer'];
			$n=count($new_manuf);
			$i=0;
			while($i < $n){
				if($new_manuf[$i]==''){$i++;continue;}
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into cadcam_types set name=:name, level=1 , parent_id=0";
				$error="Unable to add manufacturer";
				$placeholders[':name']=$new_manuf[$i];
				$id = get_insert_id($sql, $placeholders, $error, $pdo);

				//now append item code
				$sql=$error=$s='';$placeholders=array();
				$sql="update cadcam_types set code=:code where id=:id";
				$error="Unable to add new manufacturer 2";
				$placeholders[':id']=$id;
				$placeholders[':code']="$id";
				$s = insert_sql($sql, $placeholders, $error, $pdo);
				$i++;
			}

			//now add new sizes
			$new_size=$_POST['new_size'];
			$n=count($new_size);
			$i=0;
			while($i < $n){
				if($new_size[$i]==''){$i++;continue;}
				if($_POST['manufacurer_l2']==''){
					$message="bad#Please specify the Manufacturer before adding new Size";
					$exit_flag=true;
					break;
				}
				$parent_id=$encrypt->decrypt($_POST['manufacurer_l2']);
				//$size_parent=$parent_id;
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into cadcam_types set name=:name, level=2 , parent_id=:parent_id";
				$error="Unable to add new size";
				$placeholders[':name']=$new_size[$i];
				$placeholders[':parent_id']=$parent_id;
				$id = get_insert_id($sql, $placeholders, $error, $pdo);

				//now append item code
				$sql=$error=$s='';$placeholders=array();
				$sql="update cadcam_types set code=:code where id=:id";
				$error="Unable to add new size 2";
				$placeholders[':id']=$id;
				$placeholders[':code']="$parent_id-$id";
				$s = insert_sql($sql, $placeholders, $error, $pdo);

				$i++;
			}

			//now add new type
			$new_type=$_POST['new_type'];
			$n=count($new_type);
			$i=0;
			while($i < $n){
				if($new_type[$i]==''){$i++;continue;}
				if($_POST['manufacurer_l3']=='' or $_POST['size_l3']==''){
					$message="bad#Please specify the Manufacturer and Size before adding new Type";
					$exit_flag=true;
					break;
				}
				$parent_id=$encrypt->decrypt($_POST['size_l3']);
				$manuf_id=$encrypt->decrypt($_POST['manufacurer_l3']);

				$sql=$error=$s='';$placeholders=array();
				$sql="insert into cadcam_types set name=:name, level=3 , parent_id=:parent_id";
				$error="Unable to add new type";
				$placeholders[':name']=$new_type[$i];
				$placeholders[':parent_id']=$parent_id;
				$id = get_insert_id($sql, $placeholders, $error, $pdo);

				//now append item code
				$sql=$error=$s='';$placeholders=array();
				$sql="update cadcam_types set code=:code where id=:id";
				$error="Unable to add new type 2";
				$placeholders[':id']=$id;
				$placeholders[':code']="$manuf_id-$parent_id-$id";
				$s = insert_sql($sql, $placeholders, $error, $pdo);

				$i++;
			}

			//now add new shade
			$new_shade=$_POST['new_shade'];
			$n=count($new_shade);
			$i=0;
			while($i < $n){
				if($new_shade[$i]==''){$i++;continue;}
				if($_POST['manufacurer_l4']=='' or $_POST['size_l4']=='' or $_POST['type_l4']==''){
					$message="bad#Please specify the Manufacturer , Size and Type before adding new Shade";
					$exit_flag=true;
					break;
				}
				$parent_id=$encrypt->decrypt($_POST['type_l4']);
				$size_id=$encrypt->decrypt($_POST['size_l4']);
				$manuf_id=$encrypt->decrypt($_POST['manufacurer_l4']);

				$sql=$error=$s='';$placeholders=array();
				$sql="insert into cadcam_types set name=:name, level=4 , parent_id=:parent_id";
				$error="Unable to add new shade";
				$placeholders[':name']=$new_shade[$i];
				$placeholders[':parent_id']=$parent_id;
				$id = get_insert_id($sql, $placeholders, $error, $pdo);

				//now append item code
				$sql=$error=$s='';$placeholders=array();
				$sql="update cadcam_types set code=:code where id=:id";
				$error="Unable to add new new_shade 2";
				$placeholders[':id']=$id;
				$placeholders[':code']="$manuf_id-$size_id-$parent_id-$id";
				$s = insert_sql($sql, $placeholders, $error, $pdo);

				$i++;
			}

			//handle old manufacture
			if(isset($_POST['old_manufacturer'])){
				//now update old manufcaturer
				$old_manuf=$_POST['old_manufacturer'];
				$old_manuf2=$_POST['old_manufacturer2'];
				$n=count($old_manuf);
				$i=0;
				while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_types set name=:name where id=:id";
					$error="Unable to update manufacturer 1";
					$placeholders[':name']=$old_manuf[$i];
					$placeholders[':id']=$encrypt->decrypt($old_manuf2[$i]);
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					$i++;
				}

				//now unlist old manufcaturer

					//first list all of them
					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_types set listed=0 where level=1";
					$error="Unable to update listed manufacturer 1";
					$s = insert_sql($sql, $placeholders, $error, $pdo);

				if(isset($_POST['unlist'])){
					$unlist=$_POST['unlist'];
					$n=count($unlist);
					$i=0;
					while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update cadcam_types set listed=1  where id=:id";
						$error="Unable to update manufacturer 2";
						$placeholders[':id']=$encrypt->decrypt($unlist[$i]);
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						$i++;
					}
				}
			}

			//handle old size
			if(isset($_POST['old_size'])){
				//now update old old_size
				$old_size=$_POST['old_size'];
				$old_size2=$_POST['old_size2'];
				$n=count($old_size);
				$i=0;
				while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_types set name=:name where id=:id";
					$error="Unable to update old_size 1";
					$placeholders[':name']=$old_size[$i];
					$placeholders[':id']=$encrypt->decrypt($old_size2[$i]);
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					$i++;
				}

				//now unlist old old_size
					//first list all of them
					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_types set listed=0 where parent_id=:parent";
					$error="Unable to update listed old size 1";
					$placeholders[':parent']=$encrypt->decrypt($_POST['manufacurer_l2']);
					$s = insert_sql($sql, $placeholders, $error, $pdo);

				if(isset($_POST['unlist_size'])){
					$unlist=$_POST['unlist_size'];
					$n=count($unlist);
					$i=0;
					while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update cadcam_types set listed=1  where id=:id";
						$error="Unable to update size 2";
						$placeholders[':id']=$encrypt->decrypt($unlist[$i]);
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						$i++;
					}
				}
			}

			//handle old type
			if(isset($_POST['old_type'])){
				//now update old old_type
				$old_type=$_POST['old_type'];
				$old_type2=$_POST['old_type2'];
				$n=count($old_type);
				$i=0;
				while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_types set name=:name where id=:id";
					$error="Unable to update old_type 1";
					$placeholders[':name']=$old_type[$i];
					$placeholders[':id']=$encrypt->decrypt($old_type2[$i]);
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					$i++;
				}

				//now unlist old old_type
					//first list all of them
					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_types set listed=0 where parent_id=:parent";
					$error="Unable to update listed old old_type 1";
					$placeholders[':parent']=$encrypt->decrypt($_POST['size_l3']);
					$s = insert_sql($sql, $placeholders, $error, $pdo);

				if(isset($_POST['unlist_type'])){
					$unlist=$_POST['unlist_type'];
					$n=count($unlist);
					$i=0;
					while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update cadcam_types set listed=1  where id=:id";
						$error="Unable to update old_type 2";
						$placeholders[':id']=$encrypt->decrypt($unlist[$i]);
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						$i++;
					}
				}
			}

			//handle old shade
			if(isset($_POST['old_shade'])){
				//now update old old_shade
				$old_shade=$_POST['old_shade'];
				$old_shade2=$_POST['old_shade2'];
				$n=count($old_shade);
				$i=0;
				while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_types set name=:name where id=:id";
					$error="Unable to update old_shade 1";
					$placeholders[':name']=$old_shade[$i];
					$placeholders[':id']=$encrypt->decrypt($old_shade2[$i]);
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					$i++;
				}

				//now unlist old old_shade
					//first list all of them
					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_types set listed=0 where parent_id=:parent";
					$error="Unable to update listed old old_shade 1";
					$placeholders[':parent']=$encrypt->decrypt($_POST['type_l4']);
					$s = insert_sql($sql, $placeholders, $error, $pdo);

					$unlist=$_POST['unlist_shade'];
					$n=count($unlist);
					$i=0;
					while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update cadcam_types set listed=1  where id=:id";
						$error="Unable to update old_shade 2";
						$placeholders[':id']=$encrypt->decrypt($unlist[$i]);
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						$i++;
					}
			}

			if(!$exit_flag){
					$tx_result = $pdo->commit();
					$message="good#cadcam#CADCAM details saved. ";
			}
			elseif($exit_flag){
				$pdo->rollBack();
				//$message="ba#Patient disease details saved. ";
			}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#Unable to save patient disease details  ";
		}
		$data=explode('#',"$message");
		if("$data[0]"=='good'){
			$_SESSION['result_class']='success_response';
			$_SESSION['result_message']="$data[2]";
		}
		echo "$message";
}

//this is for submitting patient diseases
elseif(isset($_SESSION['token_1e_patinet']) and 	isset($_POST['token_1e_patinet']) and $_POST['token_1e_patinet']==$_SESSION['token_1e_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,16)){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag ){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	global $exit_flag;

//sleep(5);
	function check_yes_no($parameter){
		//check bleeding
		global $pdo, $message;
		if("$parameter" !='yes' and "$parameter" !='no'  ){
			$message="bad#Unable to save details as some parameters may not be properly set. Please recheck the Yes/No values";
			$var=html("$parameter");
			$security_log="sombody tried to input $var into patient diseases for a yes no value";
			log_security($pdo,$security_log);
			return true;
		}
		else{return false;}
	}

	if(!$exit_flag and isset($_POST['bleeding'])) {$exit_flag=check_yes_no($_POST['bleeding']);} else {$_POST['bleeding']='';}
	if(!$exit_flag and isset($_POST['drug'])) {$exit_flag=check_yes_no($_POST['drug']);} else {$_POST['drug']='';}
	if(!$exit_flag and isset($_POST['Neurological'])) {$exit_flag=check_yes_no($_POST['Neurological']);} else {$_POST['Neurological']='';}
	if(!$exit_flag and isset($_POST['HIV'])) {$exit_flag=check_yes_no($_POST['HIV']);} else {$_POST['HIV']='';}
	if(!$exit_flag and isset($_POST['Diabetes'])) {$exit_flag=check_yes_no($_POST['Diabetes']);} else {$_POST['Diabetes']='';}
	if(!$exit_flag and isset($_POST['Osteoporosis'])) {$exit_flag=check_yes_no($_POST['Osteoporosis']);} else {$_POST['Osteoporosis']='';}
	if(!$exit_flag and isset($_POST['anemia'])) {$exit_flag=check_yes_no($_POST['anemia']);} else {$_POST['anemia']='';}
	if(!$exit_flag and isset($_POST['dry'])) {$exit_flag=check_yes_no($_POST['dry']);} else {$_POST['dry']='';}
	if(!$exit_flag and isset($_POST['Persistents'])) {$exit_flag=check_yes_no($_POST['Persistents']);} else {$_POST['Persistents']='';}
	if(!$exit_flag and isset($_POST['arthritis'])) {$exit_flag=check_yes_no($_POST['arthritis']);} else {$_POST['arthritis']='';}
	if(!$exit_flag and isset($_POST['Eating'])) {$exit_flag=check_yes_no($_POST['Eating']);} else {$_POST['Eating']='';}
	if(!$exit_flag and isset($_POST['Respiratory'])) {$exit_flag=check_yes_no($_POST['Respiratory']);} else {$_POST['Respiratory']='';}
	if(!$exit_flag and isset($_POST['rarthritis'])) {$exit_flag=check_yes_no($_POST['rarthritis']);} else {$_POST['rarthritis']='';}
	if(!$exit_flag and isset($_POST['Epilepsy'])) {$exit_flag=check_yes_no($_POST['Epilepsy']);} else {$_POST['Epilepsy']='';}
	if(!$exit_flag and isset($_POST['Severe'])) {$exit_flag=check_yes_no($_POST['Severe']);} else {$_POST['Severe']='';}
	if(!$exit_flag and isset($_POST['asthma'])) {$exit_flag=check_yes_no($_POST['asthma']);} else {$_POST['asthma']='';}

	if(!$exit_flag and isset($_POST['Fainting'])) {$exit_flag=check_yes_no($_POST['Fainting']);} else {$_POST['Fainting']='';}
	if(!$exit_flag and isset($_POST['weight'])) {$exit_flag=check_yes_no($_POST['weight']);} else {$_POST['weight']='';}
	if(!$exit_flag and isset($_POST['transfusion'])) {$exit_flag=check_yes_no($_POST['transfusion']);} else {$_POST['transfusion']='';}
	if(!$exit_flag and isset($_POST['reflux'])) {$exit_flag=check_yes_no($_POST['reflux']);} else {$_POST['reflux']='';}
	if(!$exit_flag and isset($_POST['Sexually'])) {$exit_flag=check_yes_no($_POST['Sexually']);} else {$_POST['Sexually']='';}
	if(!$exit_flag and isset($_POST['chemotherapy'])) {$exit_flag=check_yes_no($_POST['chemotherapy']);} else {$_POST['chemotherapy']='';}
	if(!$exit_flag and isset($_POST['Glaucoma'])) {$exit_flag=check_yes_no($_POST['Glaucoma']);} else {$_POST['Glaucoma']='';}
	if(!$exit_flag and isset($_POST['Sinus'])) {$exit_flag=check_yes_no($_POST['Sinus']);} else {$_POST['Sinus']='';}
	if(!$exit_flag and isset($_POST['Chronic'])) {$exit_flag=check_yes_no($_POST['Chronic']);} else {$_POST['Chronic']='';}
	if(!$exit_flag and isset($_POST['Hemophilia'])) {$exit_flag=check_yes_no($_POST['Hemophilia']);} else {$_POST['Hemophilia']='';}
	if(!$exit_flag and isset($_POST['Sleep'])) {$exit_flag=check_yes_no($_POST['Sleep']);} else {$_POST['Sleep']='';}
	if(!$exit_flag and isset($_POST['Persistent'])) {$exit_flag=check_yes_no($_POST['Persistent']);} else {$_POST['Persistent']='';}
	if(!$exit_flag and isset($_POST['Hepatitis'])) {$exit_flag=check_yes_no($_POST['Hepatitis']);} else {$_POST['Hepatitis']='';}
	if(!$exit_flag and isset($_POST['Sores'])) {$exit_flag=check_yes_no($_POST['Sores']);} else {$_POST['Sores']='';}
	if(!$exit_flag and isset($_POST['Cardiovascular'])) {$exit_flag=check_yes_no($_POST['Cardiovascular']);} else {$_POST['Cardiovascular']='';}
	if(!$exit_flag and isset($_POST['Recurent'])) {$exit_flag=check_yes_no($_POST['Recurent']);} else {$_POST['Recurent']='';}
	if(!$exit_flag and isset($_POST['Kidney'])) {$exit_flag=check_yes_no($_POST['Kidney']);} else {$_POST['Kidney']='';}
	if(!$exit_flag and isset($_POST['Low'])) {$exit_flag=check_yes_no($_POST['Low']);} else {$_POST['Low']='';}
	if(!$exit_flag and isset($_POST['Malnutrition'])) {$exit_flag=check_yes_no($_POST['Malnutrition']);} else {$_POST['Malnutrition']='';}
	if(!$exit_flag and isset($_POST['Migraines'])) {$exit_flag=check_yes_no($_POST['Migraines']);} else {$_POST['Migraines']='';}
	if(!$exit_flag and isset($_POST['Night'])) {$exit_flag=check_yes_no($_POST['Night']);} else {$_POST['Night']='';}
	if(!$exit_flag and isset($_POST['Mental'])) {$exit_flag=check_yes_no($_POST['Mental']);} else {$_POST['Mental']='';}
	if(!$exit_flag and isset($_POST['Stroke'])) {$exit_flag=check_yes_no($_POST['Stroke']);} else {$_POST['Stroke']='';}
	if(!$exit_flag and isset($_POST['Systematic'])) {$exit_flag=check_yes_no($_POST['Systematic']);} else {$_POST['Systematic']='';}
	if(!$exit_flag and isset($_POST['Thyroid'])) {$exit_flag=check_yes_no($_POST['Thyroid']);} else {$_POST['Thyroid']='';}
	if(!$exit_flag and isset($_POST['Tuberculosis'])) {$exit_flag=check_yes_no($_POST['Tuberculosis']);} else {$_POST['Tuberculosis']='';}
	if(!$exit_flag and isset($_POST['Ulcers'])) {$exit_flag=check_yes_no($_POST['Ulcers']);} else {$_POST['Ulcers']='';}
	if(!$exit_flag and isset($_POST['urination'])) {$exit_flag=check_yes_no($_POST['urination']);} else {$_POST['urination']='';}
//empty of needed
//empty the unset ones
if(!isset($_POST['bleeding']))  {$_POST['bleeding']='';}
	if(!isset($_POST['drug'])) {$_POST['drug']='';}
	if(!isset($_POST['Neurological'])) {$_POST['Neurological']='';}
	if(!isset($_POST['HIV']))  {$_POST['HIV']='';}
	if(!isset($_POST['Diabetes'])) {$_POST['Diabetes']='';}
	if(!isset($_POST['Osteoporosis'])) {$_POST['Osteoporosis']='';}
	if(!isset($_POST['anemia'])) {$_POST['anemia']='';}
	if(!isset($_POST['dry']))  {$_POST['dry']='';}
	if(!isset($_POST['Persistents']))  {$_POST['Persistents']='';}
	if(!isset($_POST['arthritis']))  {$_POST['arthritis']='';}
	if(!isset($_POST['Eating']))  {$_POST['Eating']='';}
	if(!isset($_POST['Respiratory'])) {$_POST['Respiratory']='';}
	if(!isset($_POST['rarthritis'])) {$_POST['rarthritis']='';}
	if(!isset($_POST['Epilepsy']))  {$_POST['Epilepsy']='';}
	if(!isset($_POST['Severe']))  {$_POST['Severe']='';}
	if(!isset($_POST['asthma']))  {$_POST['asthma']='';}

	if(!isset($_POST['Fainting']))  {$_POST['Fainting']='';}
	if(!isset($_POST['weight']))  {$_POST['weight']='';}
	if(!isset($_POST['transfusion']))  {$_POST['transfusion']='';}
	if(!isset($_POST['reflux'])) {$_POST['reflux']='';}
	if(!isset($_POST['Sexually']))  {$_POST['Sexually']='';}
	if(!isset($_POST['chemotherapy'])) {$_POST['chemotherapy']='';}
	if(!isset($_POST['Glaucoma']))  {$_POST['Glaucoma']='';}
	if(!isset($_POST['Sinus']))  {$_POST['Sinus']='';}
	if(!isset($_POST['Chronic']))  {$_POST['Chronic']='';}
	if(!isset($_POST['Hemophilia']))  {$_POST['Hemophilia']='';}
	if(!isset($_POST['Sleep'])) {$_POST['Sleep']='';}
	if(!isset($_POST['Persistent']))  {$_POST['Persistent']='';}
	if(!isset($_POST['Hepatitis']))  {$_POST['Hepatitis']='';}
	if(!isset($_POST['Sores']))  {$_POST['Sores']='';}
	if(!isset($_POST['Cardiovascular']))  {$_POST['Cardiovascular']='';}
	if(!isset($_POST['Recurent']))  {$_POST['Recurent']='';}
	if(!isset($_POST['Kidney']))  {$_POST['Kidney']='';}
	if(!isset($_POST['Low'])){$_POST['Low']='';}
	if(!isset($_POST['Malnutrition']))  {$_POST['Malnutrition']='';}
	if(!isset($_POST['Migraines']))  {$_POST['Migraines']='';}
	if(!isset($_POST['Night']))  {$_POST['Night']='';}
	if(!isset($_POST['Mental']))  {$_POST['Mental']='';}
	if(!isset($_POST['Stroke']))  {$_POST['Stroke']='';}
	if(!isset($_POST['Systematic'])) {$_POST['Systematic']='';}
	if(!isset($_POST['Thyroid']))  {$_POST['Thyroid']='';}
	if(!isset($_POST['Tuberculosis']))  {$_POST['Tuberculosis']='';}
	if(!isset($_POST['Ulcers']))  {$_POST['Ulcers']='';}
	if(!isset($_POST['urination']))  {$_POST['urination']='';}

	//chreck cardiovascular
	if(!$exit_flag and isset($_POST['Angina']) and $_POST['Angina']!='Angina'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Angina']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Angina'])){$_POST['Angina']='';}
	if(!$exit_flag and isset($_POST['Arteriosclerosis']) and $_POST['Arteriosclerosis']!='Arteriosclerosis'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Arteriosclerosis']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Arteriosclerosis'])){$_POST['Arteriosclerosis']='';}
	if(!$exit_flag and isset($_POST['Artificial']) and $_POST['Artificial']!='Artificial heart valves'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Artificial']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Artificial'])){$_POST['Artificial']='';}
	if(!$exit_flag and isset($_POST['Coronary']) and $_POST['Coronary']!='Coronary insufficiency'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Coronary']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Coronary'])){$_POST['Coronary']='';}
	if(!$exit_flag and isset($_POST['occlusion']) and $_POST['occlusion']!='Coronary occlusion'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['occlusion']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['occlusion'])){$_POST['occlusion']='';}
	if(!$exit_flag and isset($_POST['Damaged']) and $_POST['Damaged']!='Damaged heart valves'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Damaged']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Damaged'])){$_POST['Damaged']='';}
	if(!$exit_flag and isset($_POST['heart_attack']) and $_POST['heart_attack']!='Heart attack'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['heart_attack']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['heart_attack'])){$_POST['heart_attack']='';}
	if(!$exit_flag and isset($_POST['murmur']) and $_POST['murmur']!='Heart murmur'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['murmur']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['murmur'])){$_POST['murmur']='';}
	if(!$exit_flag and isset($_POST['Inborn']) and $_POST['Inborn']!='Inborn heart defects'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Inborn']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Inborn'])){$_POST['Inborn']='';}
	if(!$exit_flag and isset($_POST['Mitral']) and $_POST['Mitral']!='Mitral valve prolapse'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Mitral']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Mitral'])){$_POST['Mitral']='';}
	if(!$exit_flag and isset($_POST['Pacemaker']) and $_POST['Pacemaker']!='Pacemaker'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Pacemaker']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Pacemaker'])){$_POST['Pacemaker']='';}
	if(!$exit_flag and isset($_POST['Rheumatic']) and $_POST['Rheumatic']!='Rheumatic heart disease'){
			$message="bad#Unable to save details as some Cartdiovascular disease details may not be properly set.
			Please recheck the cardiovascular disease section values";
			$var=html($_POST['Rheumatic']);
			$security_log="sombody tried to input $var into patient diseases cardiovascular";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Rheumatic'])){$_POST['Rheumatic']='';}

	//diabetes type
	if(!$exit_flag and isset($_POST['Type']) and $_POST['Type']!='I'  and $_POST['Type']!='II'){
			$message="bad#Unable to save details as some Diabetes details may not be properly set.
			Please recheck the Diabetes section values";
			$var=html($_POST['Type']);
			$security_log="sombody tried to input $var into patient diseases diabetes";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['Type'])){$_POST['Type']='';}

	//respiratoty problems
	if(!$exit_flag and isset($_POST['yes']) and $_POST['yes']!='Emphysema'  and $_POST['yes']!='Bronchitis, etc'){
			$message="bad#Unable to save details as some Diabetes details may not be properly set.
			Please recheck the Diabetes section values";
			$var=html($_POST['yes']);
			$security_log="sombody tried to input $var into patient diseases respiratoty problems";
			log_security($pdo,$security_log);
	}
	elseif(!$exit_flag and !isset($_POST['yes'])){$_POST['yes']='';}

	if(!$exit_flag){
		try{
			$pdo->beginTransaction();

			//now delete old record
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from patient_disease where pid=:pid";
			$error="Unable to update patient disease form";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			//print_r($_POST);
			//now update with new details
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_disease set
				bleeding=:bleeding,
			  aids=:aids,
			  anaemia=:anaemia,
			  arthritis=:arthritis,
			  rarthritis=:rarthritis,
			  asthma=:asthma,
			  transfusion=:transfusion,
			  tdate=:tdate,
			  cancer=:cancer,
			  chronic=:chronic,
			  diarea=:diarea,
			  cardio_disease=:cardio_disease,
			  angina =:angina,
			  arteriosclerosis =:arteriosclerosis,
			  hvalves =:hvalves,
			  cinsuff =:cinsuff,
			  cocclus =:cocclus,
			  dhvalve =:dhvalve,
			  hattack =:hattack,
			  hmurmur =:hmurmur,

			  inborn =:inborn,
			  prolapse =:prolapse,
			  pacemaker =:pacemaker,
			  rhdisease =:rhdisease,
			  drug=:drug,
			  diab1 =:diab1,
			  diabetes=:diabetes,
			  dry=:dry,
			  eating=:eating,
			  especify =:especify,
			  epilepsy=:epilepsy,
			  faint=:faint,
			  reflux=:reflux,
			  glaucoma=:glaucoma,
			  hemophilia=:hemophilia,
			  hepatitis=:hepatitis,
			  recurent=:recurent,
			  rtype =:rtype,
			  kidney=:kidney,
			  low_blood=:low_blood,

			  malnutrition=:malnutrition,
			  migrain=:migrain,
			  night_sweat=:night_sweat,
			  mental=:mental,
			  mspecify =:mspecify,
			  neuro=:neuro,
			  nspecify =:nspecify,
			  osteoporosis=:osteoporosis,
			  swollen=:swollen,
			  rproblems=:rproblems,
			  emphysema =:emphysema,
			  headaches=:headaches,
			  wloss=:wloss,
			  std=:std,
			  sinus=:sinus,
			  sleep=:sleep,
			  sores=:sores,
			  stroke=:stroke,
			  systematic=:systematic,
			  thyroid=:thyroid,

			  tb=:tb,
			  ulcers=:ulcers,
			  urination=:urination,
			  other=:other,
			  pid =:pid,
			  when_added=now()
			  ";//66
			$error="Unable to update patient completion form";
			$placeholders[':bleeding']=$_POST['bleeding'];
			$placeholders[':aids']=$_POST['HIV'];
			$placeholders[':anaemia']=$_POST['anemia'];
			$placeholders[':arthritis']=$_POST['arthritis'];
			$placeholders[':rarthritis']=$_POST['rarthritis'];
			$placeholders[':asthma']=$_POST['asthma'];
			$placeholders[':transfusion']=$_POST['transfusion'];
			$placeholders[':tdate']=$_POST['blood'];
			$placeholders[':cancer']=$_POST['chemotherapy'];
			$placeholders[':chronic']=$_POST['Chronic'];
			$placeholders[':diarea']=$_POST['Persistent'];
			$placeholders[':cardio_disease']=$_POST['Cardiovascular'];
			$placeholders[':angina']=$_POST['Angina'];
			$placeholders[':arteriosclerosis']=$_POST['Arteriosclerosis'];
			$placeholders[':hvalves']=$_POST['Artificial'];
			$placeholders[':cinsuff']=$_POST['Coronary'];
			$placeholders[':cocclus']=$_POST['occlusion'];
			$placeholders[':dhvalve']=$_POST['Damaged'];
			$placeholders[':hattack']=$_POST['heart_attack'];
			$placeholders[':hmurmur']=$_POST['murmur'];

		//	$placeholders[':blood_pressure']=$_POST['xxx'];
			$placeholders[':inborn']=$_POST['Inborn'];
			$placeholders[':prolapse']=$_POST['Mitral'];
			$placeholders[':pacemaker']=$_POST['Pacemaker'];
			$placeholders[':rhdisease']=$_POST['Rheumatic'];
			$placeholders[':drug']=$_POST['drug'];
			$placeholders[':diab1']=$_POST['Diabetes'];
			$placeholders[':diabetes']=$_POST['Type'];
			$placeholders[':dry']=$_POST['dry'];
			$placeholders[':eating']=$_POST['Eating'];
			$placeholders[':especify']=$_POST['disorder'];
			$placeholders[':epilepsy']=$_POST['Epilepsy'];
			$placeholders[':faint']=$_POST['Fainting'];
			$placeholders[':reflux']=$_POST['reflux'];
			$placeholders[':glaucoma']=$_POST['Glaucoma'];
			$placeholders[':hemophilia']=$_POST['Hemophilia'];
			$placeholders[':hepatitis']=$_POST['Hepatitis'];
			$placeholders[':recurent']=$_POST['Recurent'];
			$placeholders[':rtype']=$_POST['infections'];
			$placeholders[':kidney']=$_POST['Kidney'];
			$placeholders[':low_blood']=$_POST['Low'];

			$placeholders[':malnutrition']=$_POST['Malnutrition'];
			$placeholders[':migrain']=$_POST['Migraines'];
			$placeholders[':night_sweat']=$_POST['Night'];
			$placeholders[':mental']=$_POST['Mental'];
			$placeholders[':mspecify']=$_POST['mental_disorder'];
			$placeholders[':neuro']=$_POST['Neurological'];
			$placeholders[':nspecify']=$_POST['neuro'];
			$placeholders[':osteoporosis']=$_POST['Osteoporosis'];
			$placeholders[':swollen']=$_POST['Persistents'];
			$placeholders[':rproblems']=$_POST['Respiratory'];
			$placeholders[':emphysema']=$_POST['yes'];
			$placeholders[':headaches']=$_POST['Severe'];
			$placeholders[':wloss']=$_POST['weight'];
			$placeholders[':std']=$_POST['Sexually'];
			$placeholders[':sinus']=$_POST['Sinus'];
			$placeholders[':sleep']=$_POST['Sleep'];
			$placeholders[':sores']=$_POST['Sores'];
			$placeholders[':stroke']=$_POST['Stroke'];
			$placeholders[':systematic']=$_POST['Systematic'];
			$placeholders[':thyroid']=$_POST['Thyroid'];

			$placeholders[':tb']=$_POST['Tuberculosis'];
			$placeholders[':ulcers']=$_POST['Ulcers'];
			$placeholders[':urination']=$_POST['urination'];
			$placeholders[':other']=$_POST['other'];
			$placeholders[':pid']=$_SESSION['pid'];
			//$placeholders[':when_added']=now();
			//print_r($placeholders);
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#Patient disease details saved. ";}
			elseif(!$s){$message="bad#Unable to save Patient disease details ";}

			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save patient disease details  ";
		}
	}
		echo "$message";

}


//this is for submitting medical patient details
elseif(isset($_SESSION['token_1c_patinet']) and 	isset($_POST['token_1c_patinet']) and $_POST['token_1c_patinet']==$_SESSION['token_1c_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,14)){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	global $exit_flag;

//sleep(5);
	function check_yes_no($parameter){
		//check bleeding
		global $pdo, $message;
		if("$parameter" !='yes' and "$parameter" !='no'  ){
			$message="bad#Unable to save details as some parameters may not be properly set. Please recheck the Yes/No values";
			$var=html("$parameter");
			$security_log="sombody tried to input $var into patient_medical for a yes no value";
			log_security($pdo,$security_log);
			return true;
		}
		else{return false;}
	}

	if(!$exit_flag and isset($_POST['good_health'])) {$exit_flag=check_yes_no($_POST['good_health']);} else {$_POST['good_health']='';}
	if(!$exit_flag and isset($_POST['change'])) {$exit_flag=check_yes_no($_POST['change']);} else {$_POST['change']='';}
	if(!$exit_flag and isset($_POST['tubercolosis'])) {$exit_flag=check_yes_no($_POST['tubercolosis']);} else {$_POST['tubercolosis']='';}
	if(!$exit_flag and isset($_POST['Persistent'])) {$exit_flag=check_yes_no($_POST['Persistent']);} else {$_POST['Persistent']='';}
	if(!$exit_flag and isset($_POST['blood'])) {$exit_flag=check_yes_no($_POST['blood']);} else {$_POST['blood']='';}
	if(!$exit_flag and isset($_POST['care'])) {$exit_flag=check_yes_no($_POST['care']);} else {$_POST['care']='';}if(!$exit_flag and isset($_POST['good_health'])) {$exit_flag=check_yes_no($_POST['good_health']);} else {$_POST['good_health']='';}
	if(!$exit_flag and isset($_POST['hospitalized'])) {$exit_flag=check_yes_no($_POST['hospitalized']);} else {$_POST['hospitalized']='';}
	if(!$exit_flag and isset($_POST['prescription'])) {$exit_flag=check_yes_no($_POST['prescription']);} else {$_POST['prescription']='';}
	if(!$exit_flag and isset($_POST['diet'])) {$exit_flag=check_yes_no($_POST['diet']);} else {$_POST['diet']='';}
	if(!$exit_flag and isset($_POST['drink'])) {$exit_flag=check_yes_no($_POST['drink']);} else {$_POST['drink']='';}
	if(!$exit_flag and isset($_POST['alcohol'])) {$exit_flag=check_yes_no($_POST['alcohol']);} else {$_POST['alcohol']='';}
	if(!$exit_flag and isset($_POST['treatment'])) {$exit_flag=check_yes_no($_POST['treatment']);} else {$_POST['treatment']='';}
	if(!$exit_flag and isset($_POST['substances'])) {$exit_flag=check_yes_no($_POST['substances']);} else {$_POST['substances']='';}
	if(!$exit_flag and isset($_POST['tobacco'])) {$exit_flag=check_yes_no($_POST['tobacco']);} else {$_POST['tobacco']='';}
	if(!$exit_flag and isset($_POST['contact'])) {$exit_flag=check_yes_no($_POST['contact']);} else {$_POST['contact']='';}
	if(!$exit_flag and isset($_POST['anaethesia'])) {$exit_flag=check_yes_no($_POST['anaethesia']);} else {$_POST['anaethesia']='';}
	if(!$exit_flag and isset($_POST['asprin'])) {$exit_flag=check_yes_no($_POST['asprin']);} else {$_POST['asprin']='';}
	if(!$exit_flag and isset($_POST['antibiotics'])) {$exit_flag=check_yes_no($_POST['antibiotics']);} else {$_POST['antibiotics']='';}
	if(!$exit_flag and isset($_POST['sedatives'])) {$exit_flag=check_yes_no($_POST['sedatives']);} else {$_POST['sedatives']='';}
	if(!$exit_flag and isset($_POST['sulfa'])) {$exit_flag=check_yes_no($_POST['sulfa']);} else {$_POST['sulfa']='';}
	if(!$exit_flag and isset($_POST['narcotics'])) {$exit_flag=check_yes_no($_POST['narcotics']);} else {$_POST['narcotics']='';}
	if(!$exit_flag and isset($_POST['Latex'])) {$exit_flag=check_yes_no($_POST['Latex']);} else {$_POST['Latex']='';}
	if(!$exit_flag and isset($_POST['iodine'])) {$exit_flag=check_yes_no($_POST['iodine']);} else {$_POST['iodine']='';}
	if(!$exit_flag and isset($_POST['fever'])) {$exit_flag=check_yes_no($_POST['fever']);} else {$_POST['fever']='';}
	if(!$exit_flag and isset($_POST['animals'])) {$exit_flag=check_yes_no($_POST['animals']);} else {$_POST['animals']='';}
	if(!$exit_flag and isset($_POST['food'])) {$exit_flag=check_yes_no($_POST['food']);} else {$_POST['food']='';}
	if(!$exit_flag and isset($_POST['other'])) {$exit_flag=check_yes_no($_POST['other']);} else {$_POST['other']='';}



	//empty the unset ones
	if(!isset($_POST['good_health']))  {$_POST['good_health']='';}
	if(!isset($_POST['change'])) {$_POST['change']='';}
	if(!isset($_POST['tubercolosis'])) {$_POST['tubercolosis']='';}
	if(!isset($_POST['Persistent'])) {$_POST['Persistent']='';}
	if(!isset($_POST['blood']))  {$_POST['blood']='';}
	if(!isset($_POST['care']))  {$_POST['care']='';}
	if(!isset($_POST['hospitalized']))  {$_POST['hospitalized']='';}
	if(!isset($_POST['prescription']))  {$_POST['prescription']='';}
	if(!isset($_POST['diet'])) {$_POST['diet']='';}
	if(!isset($_POST['drink']))  {$_POST['drink']='';}
	if(!isset($_POST['alcohol']))  {$_POST['alcohol']='';}
	if(!isset($_POST['treatment']))  {$_POST['treatment']='';}
	if(!isset($_POST['substances']))  {$_POST['substances']='';}
	if(!isset($_POST['tobacco'])) {$_POST['tobacco']='';}
	if(!isset($_POST['contact']))  {$_POST['contact']='';}
	if(!isset($_POST['anaethesia'])) {$_POST['anaethesia']='';}
	if(!isset($_POST['asprin']))  {$_POST['asprin']='';}
	if(!isset($_POST['antibiotics'])) {$_POST['antibiotics']='';}
	if(!isset($_POST['sedatives']))  {$_POST['sedatives']='';}
	if(!isset($_POST['sulfa']))  {$_POST['sulfa']='';}
	if(!isset($_POST['narcotics']))  {$_POST['narcotics']='';}
	if(!isset($_POST['Latex']))  {$_POST['Latex']='';}
	if(!isset($_POST['iodine']))  {$_POST['iodine']='';}
	if(!isset($_POST['fever']))  {$_POST['fever']='';}
	if(!isset($_POST['animals']))  {$_POST['animals']='';}
	if(!isset($_POST['food']))  {$_POST['food']='';}
	if(!isset($_POST['other']))  {$_POST['other']='';}
	if(!isset($_POST['how']))  {$_POST['how']='';}
	if(!isset($_POST['blood_groups']))  {$_POST['blood_groups']='';}

	//chreck opeartion date isa  date
	if(!$exit_flag and isset($_POST['date_last_exam']) and $_POST['date_last_exam']!='')	{
		$date='';
		$date=explode('-',$_POST['date_last_exam']);
		if(!checkdate( $date[1],$date[2],$date[0] )){
		$date_last_exam=html($_POST['date_last_exam']);
		$message="bad#Unable to save details as date of last examination $date_last_exam is not in the correct format";
		$exit_flag=true;
		$security_log="somebody tried to input $date_last_exam as date of last examintaion for patient_medical";
		log_security($pdo,$security_log);
		}
	}

	if(!$exit_flag){
		try{
			$pdo->beginTransaction();

			//now delete old record
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from patient_medical where pid=:pid";
			$error="Unable to update patient medical form";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			//print_r($_POST);
			//now update with new details
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_medical set
					care_yes_no=:care_yes_no,
					cblood=:cblood,
					when_added=now(),
					good_health=:good_health,
					care=:care,
					illness=:illness,
					medicine=:medicine,
					prescribed=:prescribed,
					natural1=:natural,
					diet=:diet,
					alcoholic=:alcoholic,
					l24=:l24,
					lmonth=:lmonth,
					ndrinks=:ndrinks,
					nyrs=:nyrs,
					adependent=:adependent,
					treatment=:treatment,
					substance_yes_no=:substance_yes_no,
					substances=:substances,
					frequency=:frequency,
					years=:years,
					tobacco=:tobacco,
					pid=:pid,
					change1=:change,
					tb=:tb,
					persistent=:persistent,
					ldate=:ldate,
					pname=:pname,
					pphone=:pphone,
					paddress=:paddress,
					illnes_yes_no=:illnes_yes_no,
					stoping=:stoping,
					lenses=:lenses,
					anaethesia=:anaethesia,
					Asprin=:Asprin,
					penicilin=:penicilin,
					sedatives=:sedatives,
					sulfa=:sulfa,
					codeine=:codeine,
					latex=:latex,
					iodine=:iodine,
					hay=:hay,
					animals=:animals,
					food=:food,
					food_specify=:food_specify,
					other=:other,
					other_specify=:other_specify,
					bgroup=:bgroup,
					counter=:Counter";
			$error="Unable to update medical patient form";
			$placeholders[':good_health']=$_POST['good_health'];
			$placeholders[':change']=$_POST['change'];
			$placeholders[':tb']=$_POST['tubercolosis'];
			$placeholders[':persistent']=$_POST['Persistent'];
			$placeholders[':cblood']=$_POST['blood'];
			$placeholders[':care_yes_no']=$_POST['care'];
			$placeholders[':care']=$_POST['pcare'];
			$placeholders[':ldate']=$_POST['date_last_exam'];
			$placeholders[':pname']=$_POST['pname'];
			$placeholders[':pphone']=$_POST['pphone'];
			$placeholders[':paddress']=$_POST['paddress'];
			$placeholders[':illnes_yes_no']=$_POST['hospitalized'];
			$placeholders[':illness']=$_POST['operation'];
			$placeholders[':medicine']=$_POST['prescription'];
			$placeholders[':prescribed']=$_POST['prescribed'];
			$placeholders[':Counter']=$_POST['Counter'];
			$placeholders[':natural']=$_POST['herbal'];
			$placeholders[':diet']=$_POST['diet'];
			$placeholders[':alcoholic']=$_POST['drink'];
			$placeholders[':l24']=$_POST['l24'];
			$placeholders[':lmonth']=$_POST['month'];
			$placeholders[':ndrinks']=$_POST['day'];
			$placeholders[':nyrs']=$_POST['years1'];
			$placeholders[':adependent']=$_POST['alcohol'];
			$placeholders[':treatment']=$_POST['treatment'];
			$placeholders[':substance_yes_no']=$_POST['substances'];
			$placeholders[':substances']=$_POST['list'];
			$placeholders[':frequency']=$_POST['frequency'];
			$placeholders[':years']=$_POST['years2'];
			$placeholders[':tobacco']=$_POST['tobacco'];
			$placeholders[':stoping']=$_POST['how'];
			$placeholders[':lenses']=$_POST['contact'];
			$placeholders[':bgroup']=$_POST['blood_groups'];
			$placeholders[':anaethesia']=$_POST['anaethesia'];
			$placeholders[':Asprin']=$_POST['asprin'];
			$placeholders[':penicilin']=$_POST['antibiotics'];
			$placeholders[':sedatives']=$_POST['sedatives'];
			$placeholders[':sulfa']=$_POST['sulfa'];
			$placeholders[':codeine']=$_POST['narcotics'];
			$placeholders[':latex']=$_POST['Latex'];
			$placeholders[':iodine']=$_POST['iodine'];
			$placeholders[':hay']=$_POST['fever'];
			$placeholders[':animals']=$_POST['animals'];
			$placeholders[':food']=$_POST['food'];
			$placeholders[':food_specify']=$_POST['food_specify'];
			$placeholders[':other']=$_POST['other'];
			$placeholders[':other_specify']=$_POST['other_specify'];
			//$placeholders[':type']=$_POST['pregnant'];
			$placeholders[':pid']=$_SESSION['pid'];
			//$placeholders[':when_added']=now();
			//print_r($placeholders);
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#Patient details saved. ";}
			elseif(!$s){$message="bad#Unable to save patient details ";}

			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save patient details  ";
		}
	}
		echo "$message";

}


//this is for submitting  patient examination
elseif(isset($_SESSION['token_g_patinet']) and 	isset($_POST['token_g_patinet']) and $_POST['token_g_patinet']==$_SESSION['token_g_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,18)){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag ){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	global $exit_flag;

//sleep(5);
	function check_yes_no($parameter){
		global $pdo, $message;
		if("$parameter" !='yes' and "$parameter" !='no'  ){
			$message="bad#Unable to save details as some parameters may not be properly set. Please recheck the Yes/No values";
			$var=html("$parameter");
			$security_log="sombody tried to input $var into on_examination for a yes no value";
			log_security($pdo,$security_log);

			return true;
		}
		else{return false;}
	}

	if(!$exit_flag and isset($_POST['swelling'])) {$exit_flag=check_yes_no($_POST['swelling']);} else {$_POST['swelling']='';}
	if(!$exit_flag and isset($_POST['lymph'])) {$exit_flag=check_yes_no($_POST['lymph']);} else {$_POST['lymph']='';}
	if(!$exit_flag and isset($_POST['pocket'])) {$exit_flag=check_yes_no($_POST['pocket']);} else {$_POST['pocket']='';}
	if(!$exit_flag and isset($_POST['bone'])) {$exit_flag=check_yes_no($_POST['bone']);} else {$_POST['bone']='';}
	if(!$exit_flag and isset($_POST['ging'])) {$exit_flag=check_yes_no($_POST['ging']);} else {$_POST['ging']='';}
	if(!$exit_flag and isset($_POST['per'])) {$exit_flag=check_yes_no($_POST['per']);} else {$_POST['per']='';}
	if(!$exit_flag and isset($_POST['ulcers'])) {$exit_flag=check_yes_no($_POST['ulcers']);} else {$_POST['ulcers']='';}
	//check psecifiy
	if(!$exit_flag and $_POST['per']!='' and isset($_POST['pspecify']) and $_POST['pspecify'] !='slight' and $_POST['pspecify'] !='moderate' and $_POST['pspecify'] !='severe'   ){
		$message="bad#Unable to save details as Periodontis is not corretcly specified";
		$var=html($_POST['pspecify']);
		$security_log="sombody tried to input $var for periodontis psecification in on_examination";
		log_security($pdo,$security_log);
		$exit_flag=true;
	}
	//check oh
	if(!$exit_flag and isset($_POST['oh']) and $_POST['oh'] !='good' and $_POST['oh'] !='fair' and $_POST['oh'] !='poor'   ){
		$message="bad#Unable to save details as OH is not corretcly specified";
		$var=html($_POST['oh']);
		$security_log="sombody tried to input $var for OH in on_examination";
		log_security($pdo,$security_log);
		$exit_flag=true;
	}
	//check dentition
	if(!$exit_flag and isset($_POST['dentition'])  and $_POST['dentition'] !='adult' and $_POST['dentition'] !='mixed' and $_POST['dentition'] !='pedo'   ){
		$message="bad#Unable to save details as dentition is not corretcly specified";
		$var=html($_POST['dentition']);
		$security_log="sombody tried to input $var for dentition  in on_examination";
		log_security($pdo,$security_log);
		$exit_flag=true;
	}

	//now check if teeth specified are correct
	function check_teeth($teeth){
		global $pdo, $exit_flag,$encrypt;
		$meno='';
		$n2=count($teeth);
		$i2=0;
		while($i2 < $n2){
			if($i2==0){$meno=$encrypt->decrypt($teeth[$i2]);}
			else{$meno="$meno,".$encrypt->decrypt($teeth[$i2]);}
			if (!in_array($encrypt->decrypt($teeth[$i2]), $_SESSION['meno_yote'])) {
				$message="bad#Unable to save details as some teeth values for dentition are not correctly set";
				$var=html($encrypt->decrypt($teeth[$i2]));
				$security_log="sombody tried to input $var into on_examination for teeth value under dentition";
				log_security($pdo,$security_log);
				$exit_flag=true;
				break;
			}
			$i2++;
		}
		return "$meno";
	}//end function

	if(!$exit_flag and isset($_POST['adult_missing'])){$_POST['adult_missing']=check_teeth($_POST['adult_missing']);}
	if(!$exit_flag and isset($_POST['adult_roots'])){$_POST['adult_roots']=check_teeth($_POST['adult_roots']);}
	if(!$exit_flag and isset($_POST['adult_occlusal'])){$_POST['adult_occlusal']=check_teeth($_POST['adult_occlusal']);}
	if(!$exit_flag and isset($_POST['adult_docclusal'])){$_POST['adult_docclusal']=check_teeth($_POST['adult_docclusal']);}
	if(!$exit_flag and isset($_POST['adult_mocclusal'])){$_POST['adult_mocclusal']=check_teeth($_POST['adult_mocclusal']);}
	if(!$exit_flag and isset($_POST['adult_root'])){$_POST['adult_root']=check_teeth($_POST['adult_root']);}
	if(!$exit_flag and isset($_POST['adult_cervical'])){$_POST['adult_cervical']=check_teeth($_POST['adult_cervical']);}
	if(!$exit_flag and isset($_POST['adult_crown'])){$_POST['adult_crown']=check_teeth($_POST['adult_crown']);}
	if(!$exit_flag and isset($_POST['adult_implant'])){$_POST['adult_implant']=check_teeth($_POST['adult_implant']);}
	if(!$exit_flag and isset($_POST['adult_danturv'])){$_POST['adult_danturv']=check_teeth($_POST['adult_danturv']);}
	if(!$exit_flag and isset($_POST['adult_bridge'])){$_POST['adult_bridge']=check_teeth($_POST['adult_bridge']);}
	if(!$exit_flag and isset($_POST['adult_rcanal'])){$_POST['adult_rcanal']=check_teeth($_POST['adult_rcanal']);}
	if(!$exit_flag and isset($_POST['adult_amalgam'])){$_POST['adult_amalgam']=check_teeth($_POST['adult_amalgam']);}
	if(!$exit_flag and isset($_POST['adult_composite'])){$_POST['adult_composite']=check_teeth($_POST['adult_composite']);}
	if(!$exit_flag and isset($_POST['adult_gic'])){$_POST['adult_gic']=check_teeth($_POST['adult_gic']);}
	if(!$exit_flag and isset($_POST['pedo_missing_teeth'])){$_POST['pedo_missing_teeth']=check_teeth($_POST['pedo_missing_teeth']);}
	if(!$exit_flag and isset($_POST['pedo_roots'])){$_POST['pedo_roots']=check_teeth($_POST['pedo_roots']);}
	if(!$exit_flag and isset($_POST['pedo_occlusal'])){$_POST['pedo_occlusal']=check_teeth($_POST['pedo_occlusal']);}
	if(!$exit_flag and isset($_POST['pedo_distal_occlusal'])){$_POST['pedo_distal_occlusal']=check_teeth($_POST['pedo_distal_occlusal']);}
	if(!$exit_flag and isset($_POST['pedo_mesial_occlusal'])){$_POST['pedo_mesial_occlusal']=check_teeth($_POST['pedo_mesial_occlusal']);}
	if(!$exit_flag and isset($_POST['pedo_root_carious'])){$_POST['pedo_root_carious']=check_teeth($_POST['pedo_root_carious']);}
	if(!$exit_flag and isset($_POST['pedo_cervical'])){$_POST['pedo_cervical']=check_teeth($_POST['pedo_cervical']);}
	if(!$exit_flag and isset($_POST['pedo_crown'])){$_POST['pedo_crown']=check_teeth($_POST['pedo_crown']);}
	if(!$exit_flag and isset($_POST['pedo_implant'])){$_POST['pedo_implant']=check_teeth($_POST['pedo_implant']);}
	if(!$exit_flag and isset($_POST['pedo_denture'])){$_POST['pedo_denture']=check_teeth($_POST['pedo_denture']);}
	if(!$exit_flag and isset($_POST['pedo_bridge'])){$_POST['pedo_bridge']=check_teeth($_POST['pedo_bridge']);}
	if(!$exit_flag and isset($_POST['pedo_root_canal'])){$_POST['pedo_root_canal']=check_teeth($_POST['pedo_root_canal']);}
	if(!$exit_flag and isset($_POST['pedo_amalgam'])){$_POST['pedo_amalgam']=check_teeth($_POST['pedo_amalgam']);}
	if(!$exit_flag and isset($_POST['pedo_composite'])){$_POST['pedo_composite']=check_teeth($_POST['pedo_composite']);}
	if(!$exit_flag and isset($_POST['pedo_gic'])){$_POST['pedo_gic']=check_teeth($_POST['pedo_gic']);}
	/* if(!empty($_FILES)){
		$files = $_FILES["xray_image"];

		foreach($files as $count => $f){
			$name = $f['name'][$count];
			$error = $f['error'][$count];
			$tmp = $f['tmp_name'][$count];

			$fname = md5(time().rand());
			$name = $fname.$name;
			$path = 'Uploads/'.$name;

			move_uploaded_file($name, $path);
		}
	} */
	//check xrays
	$pay_type_array=$xray_done_name_array=$xray_teeth_array=$amount_array=$xray_type_array=$xray_comment_array=$xray_description_array=array();
	if(!$exit_flag and isset($_POST['ninye']) and $_POST['ninye']!=''){ //ninye is the number of xrays done
		//get xray types
		$sql=$error=$s='';$placeholders=array();
		$sql="select id , all_teeth, name from procedures where type=2";
		$error="Unable to get xray types";
		$s = select_sql($sql, $placeholders, $error, $pdo);
		$xray_id=$xray_all_teeth=$xray_name1=array();
		foreach($s as $row){
			$xray_id["$row[id]"]=$row['id'];
			$xray_all_teeth["$row[id]"]=$row['all_teeth'];
			$xray_name1["$row[id]"]=html($row['name']);
		}

		$count=$encrypt->decrypt($_POST['ninye']);
		$i=1;
		while($i <= $count){
			$teeth_xray=$xray_done_name='';
			//continue if fields are empty
			if((!isset($_POST["xrays$i"]) or  $_POST["xrays$i"]=='') and (!isset($_POST["pay_type$i"]) or  $_POST["pay_type$i"]=='') and
				(!isset($_POST["xray_cost$i"]) or  $_POST["xray_cost$i"]=='')){
					$i++;
					continue;
			}

			//check if amount is set without xray
			if($_POST["xray_cost$i"]!='' and !isset($_POST["xrays$i"])){
					$message="bad#Unable to save details as X-ray cost is given but no xray has been selected";
					$exit_flag=true;
					break;
			}

			//check if xray is set without amount
			if($_POST["xray_cost$i"]=='' and isset($_POST["xrays$i"])){
					$message="bad#Unable to save details as X-ray cost is not specified but an X-ray has been selected";
					$exit_flag=true;
					break;
			}

			//check if xray payment method is set
			if($_POST["pay_type$i"]=='' and $_POST["xray_cost$i"]!=''){
					$message="bad#Unable to save details as X-ray cost is specified but a payment method is not set";
					$exit_flag=true;
					break;
			}

			//check if xray cost is set
			if($_POST["pay_type$i"]!='' and $_POST["xray_cost$i"]==''){
					$message="bad#Unable to save details as payment method is secified but no X-ray cost is set";
					$exit_flag=true;
					break;
			}

			$xray_type=$encrypt->decrypt($_POST["xrays$i"]);
			//check if xray is valid tyoe
			if (!in_array($xray_type, $xray_id)) {
				$message="bad#Unable to save details as some x-ray values are not correctly set";
				$var=html("$xray_type");
				$security_log="sombody tried to input $var into on_examination for xray types";
				log_security($pdo,$security_log);
				$exit_flag=true;
				break;
			}
			//check if teeth are specified
			elseif (in_array($xray_type, $xray_id)) {
				$xray_done_name="$xray_name1[$xray_type]";
				if(isset($_POST["teeth_specified$i"]) and $xray_all_teeth["$xray_type"] == 'yes'){
					$xt=$_POST["teeth_specified$i"];
					$nt=count($xt);
					$ni=0;

					while($ni < $nt){
						if($ni == 0){$teeth_xray=$encrypt->decrypt("$xt[$ni]");}
						else{$teeth_xray="$teeth_xray, ".$encrypt->decrypt("$xt[$ni]");}
						$ni++;
					}
				}
				elseif(!isset($_POST["teeth_specified$i"]) and $xray_all_teeth["$xray_type"] == 'yes'){
					$message="bad#Unable to save details as no tooth has been specified for $xray_name1[$xray_type]";
					$exit_flag=true;
					break;
				}
			}

			//check amount
			//remove commas
			$amount=str_replace(",", "", $_POST["xray_cost$i"]);
				//check if amount is integer
			if(!ctype_digit($amount)){//echo "ooooo $unit_price[$i] ";
				//check if it has only 2 decimal places
				$data=explode('.',$amount);
				$invalid_amount=html("$amount");
				if ( count($data) != 2 ){

				$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
				$exit_flag=true;
				break;
				}
				elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
				$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
				$exit_flag=true;
				break;
				}
			}

			//check if pay type is valid
			$pay_type=$encrypt->decrypt($_POST["pay_type$i"]);
			if($pay_type!=1 and $pay_type!=2 and $pay_type!=3){
				$message="bad#Unable to save details as payment method is not correctly set. ";
				$exit_flag=true;
				break;
			}
			$xray_done_name_array[]="$xray_done_name";
			$pay_type_array[]=$pay_type;
			$amount_array[]=$amount;
			$xray_type_array[]=$xray_type;
			$xray_comment_array[]=$_POST["xray_comment$i"];
			$xray_description_array[]=$_POST["xray_description$i"];
			/* $name[]=$_POST["xray_image$i"]; */
			$xray_teeth_array[]="$teeth_xray";
			$i++;
		}
	}



	//set field to empty if they are not set
	if(!$exit_flag and !isset($_POST['swell_specify'])){$_POST['swell_specify']='';}
	if(!$exit_flag and !isset($_POST['lymph_specify'])){$_POST['lymph_specify']='';}
	if(!$exit_flag and !isset($_POST['lips'])){$_POST['lips']='';}
	if(!$exit_flag and !isset($_POST['other'])){$_POST['other']='';}
	if(!$exit_flag and !isset($_POST['uspecify'])){$_POST['uspecify']='';}
	if(!$exit_flag and !isset($_POST['pockspec'])){$_POST['pockspec']='';}
	if(!$exit_flag and !isset($_POST['bspecify'])){$_POST['bspecify']='';}
	if(!$exit_flag and !isset($_POST['pspecify'])){$_POST['pspecify']='';}
	if(!$exit_flag and !isset($_POST['oh'])){$_POST['oh']='';}
	if(!$exit_flag and !isset($_POST['dentition'])){$_POST['dentition']='';}
	if(!$exit_flag and !isset($_POST['orth'])){$_POST['orth']='';}
	if(!$exit_flag and !isset($_POST['otherprob'])){$_POST['otherprob']='';}
	if(!$exit_flag and !isset($_POST['adult_missing'])){$_POST['adult_missing']='';}
	if(!$exit_flag and !isset($_POST['adult_roots'])){$_POST['adult_roots']='';}
	if(!$exit_flag and !isset($_POST['adult_occlusal'])){$_POST['adult_occlusal']='';}
	if(!$exit_flag and !isset($_POST['adult_docclusal'])){$_POST['adult_docclusal']='';}
	if(!$exit_flag and !isset($_POST['adult_mocclusal'])){$_POST['adult_mocclusal']='';}
	if(!$exit_flag and !isset($_POST['adult_root'])){$_POST['adult_root']='';}
	if(!$exit_flag and !isset($_POST['adult_cervical'])){$_POST['adult_cervical']='';}
	if(!$exit_flag and !isset($_POST['adult_crown'])){$_POST['adult_crown']='';}
	if(!$exit_flag and !isset($_POST['adult_implant'])){$_POST['adult_implant']='';}
	if(!$exit_flag and !isset($_POST['adult_danturv'])){$_POST['adult_danturv']='';}
	if(!$exit_flag and !isset($_POST['adult_bridge'])){$_POST['adult_bridge']='';}
	if(!$exit_flag and !isset($_POST['adult_rcanal'])){$_POST['adult_rcanal']='';}
	if(!$exit_flag and !isset($_POST['adult_amalgam'])){$_POST['adult_amalgam']='';}
	if(!$exit_flag and !isset($_POST['adult_composite'])){$_POST['adult_composite']='';}
	if(!$exit_flag and !isset($_POST['adult_gic'])){$_POST['adult_gic']='';}
	if(!$exit_flag and !isset($_POST['pedo_missing_teeth'])){$_POST['pedo_missing_teeth']='';}
	if(!$exit_flag and !isset($_POST['pedo_roots'])){$_POST['pedo_roots']='';}
	if(!$exit_flag and !isset($_POST['pedo_occlusal'])){$_POST['pedo_occlusal']='';}
	if(!$exit_flag and !isset($_POST['pedo_distal_occlusal'])){$_POST['pedo_distal_occlusal']='';}
	if(!$exit_flag and !isset($_POST['pedo_mesial_occlusal'])){$_POST['pedo_mesial_occlusal']='';}
	if(!$exit_flag and !isset($_POST['pedo_root_carious'])){$_POST['pedo_root_carious']='';}
	if(!$exit_flag and !isset($_POST['pedo_cervical'])){$_POST['pedo_cervical']='';}
	if(!$exit_flag and !isset($_POST['pedo_crown'])){$_POST['pedo_crown']='';}
	if(!$exit_flag and !isset($_POST['pedo_implant'])){$_POST['pedo_implant']='';}
	if(!$exit_flag and !isset($_POST['pedo_denture'])){$_POST['pedo_denture']='';}
	if(!$exit_flag and !isset($_POST['pedo_bridge'])){$_POST['pedo_bridge']='';}
	if(!$exit_flag and !isset($_POST['pedo_root_canal'])){$_POST['pedo_root_canal']='';}
	if(!$exit_flag and !isset($_POST['pedo_amalgam'])){$_POST['pedo_amalgam']='';}
	if(!$exit_flag and !isset($_POST['pedo_composite'])){$_POST['pedo_composite']='';}
	if(!$exit_flag and !isset($_POST['pedo_gic'])){$_POST['pedo_gic']='';}


	if(!$exit_flag){
		try{
			$pdo->beginTransaction();

			//now delete old record
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from on_examination where pid=:pid";
			$error="Unable to update on_examination form";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			//print_r($_POST);
			//now update with new details
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into on_examination set
					swelling=:swelling,
					swell_specify=:swell_specify,
					lymph=:lymph,
					lymph_specify=:lymph_specify,
					lips=:lips,
					other=:other,
					oh=:oh,
					ulcers=:ulcers,
					uspecify=:uspecify,
					pocket=:pocket,
					pockspec=:pockspec,
					bone=:bone,
					bspecify=:bspecify,
					ging=:ging,
					per=:per,
					pspecify=:pspecify,
					dentition=:dentition,

					adult_missing=:adult_missing,
					adult_occlusal=:adult_occlusal,
					adult_docclusal=:adult_docclusal,
					adult_mocclusal=:adult_mocclusal,
					adult_root=:adult_root,
					adult_cervical=:adult_cervical,
					adult_crown=:adult_crown,
					adult_implant=:adult_implant,
					adult_danturv=:adult_danturv,
					adult_bridge=:adult_bridge,
					adult_rcanal=:adult_rcanal,
					adult_amalgam=:adult_amalgam,
					adult_composite=:adult_composite,
					adult_gic=:adult_gic,
					orth=:orth,
					otherprob=:otherprob,
					doc_id=:doc_id,
					pid=:pid,
					when_added=now(),

					adult_roots=:adult_roots,
					mixed_missing_teeth=:mixed_missing_teeth,
					mixed_roots=:mixed_roots,
					mixed_occlusal=:mixed_occlusal,
					mixed_distal_occlusal=:mixed_distal_occlusal,
					mixed_mesial_occlusal=:mixed_mesial_occlusal,
					mixed_root_carious=:mixed_root_carious,
					mixed_cervical=:mixed_cervical,
					mixed_crown=:mixed_crown,
					mixed_implant=:mixed_implant,
					mixed_denture=:mixed_denture,
					mixed_bridge=:mixed_bridge,
					mixed_root_canal=:mixed_root_canal,
					mixed_amalgam=:mixed_amalgam,
					mixed_composite=:mixed_composite,
					mixed_gic=:mixed_gic,
					pedo_missing_teeth=:pedo_missing_teeth,
					pedo_gic=:pedo_gic,
					pedo_roots=:pedo_roots,
					pedo_occlusal=:pedo_occlusal,
					pedo_distal_occlusal=:pedo_distal_occlusal,
					pedo_mesial_occlusal=:pedo_mesial_occlusal,
					pedo_root_carious=:pedo_root_carious,
					pedo_cervical=:pedo_cervical,
					pedo_crown=:pedo_crown,
					pedo_implant=:pedo_implant,
					pedo_denture=:pedo_denture,
					pedo_bridge=:pedo_bridge,
					pedo_root_canal=:pedo_root_canal,
					pedo_amalgam=:pedo_amalgam,
					pedo_composite=:pedo_composite
					";
			$error="Unable to update on_examination patient form";
					$placeholders['swelling']=$_POST['swelling'];
					$placeholders['swell_specify']=$_POST['swell_specify'];
					$placeholders['lymph']=$_POST['lymph'];
					$placeholders['lymph_specify']=$_POST['lymph_specify'];
					$placeholders['lips']=$_POST['lips'];
					$placeholders['other']=$_POST['other'];
					$placeholders['oh']=$_POST['oh'];
					$placeholders['ulcers']=$_POST['ulcers'];
					$placeholders['uspecify']=$_POST['uspecify'];
					$placeholders['pocket']=$_POST['pocket'];
					$placeholders['pockspec']=$_POST['pockspec'];
					$placeholders['bone']=$_POST['bone'];
					$placeholders['bspecify']=$_POST['bspecify'];
					$placeholders['ging']=$_POST['ging'];
					$placeholders['per']=$_POST['per'];
					$placeholders['pspecify']=$_POST['pspecify'];
					$placeholders['dentition']=$_POST['dentition'];

					$placeholders['adult_missing']=$_POST['adult_missing'];
					$placeholders['adult_occlusal']=$_POST['adult_occlusal'];
					$placeholders['adult_docclusal']=$_POST['adult_docclusal'];
					$placeholders['adult_mocclusal']=$_POST['adult_mocclusal'];
					$placeholders['adult_root']=$_POST['adult_root'];
					$placeholders['adult_cervical']=$_POST['adult_cervical'];
					$placeholders['adult_crown']=$_POST['adult_crown'];
					$placeholders['adult_implant']=$_POST['adult_implant'];
					$placeholders['adult_danturv']=$_POST['adult_danturv'];
					$placeholders['adult_bridge']=$_POST['adult_bridge'];
					$placeholders['adult_rcanal']=$_POST['adult_rcanal'];
					$placeholders['adult_amalgam']=$_POST['adult_amalgam'];
					$placeholders['adult_composite']=$_POST['adult_composite'];
					$placeholders['adult_gic']=$_POST['adult_gic'];
					$placeholders['orth']=$_POST['orth'];
					$placeholders['otherprob']=$_POST['otherprob'];
					$placeholders['doc_id']=$_SESSION['id'];
					$placeholders['pid']=$_SESSION['pid'];
					$placeholders['adult_roots']=$_POST['adult_roots'];
					$placeholders['mixed_missing_teeth']=$_POST['mixed_missing_teeth'];
					$placeholders['mixed_roots']=$_POST['mixed_roots'];
					$placeholders['mixed_occlusal']=$_POST['mixed_occlusal'];
					$placeholders['mixed_distal_occlusal']=$_POST['mixed_distal_occlusal'];
					$placeholders['mixed_mesial_occlusal']=$_POST['mixed_mesial_occlusal'];
					$placeholders['mixed_root_carious']=$_POST['mixed_root_carious'];
					$placeholders['mixed_cervical']=$_POST['mixed_cervical'];
					$placeholders['mixed_crown']=$_POST['mixed_crown'];
					$placeholders['mixed_implant']=$_POST['mixed_implant'];
					$placeholders['mixed_denture']=$_POST['mixed_denture'];
					$placeholders['mixed_bridge']=$_POST['mixed_bridge'];
					$placeholders['mixed_root_canal']=$_POST['mixed_root_canal'];
					$placeholders['mixed_amalgam']=$_POST['mixed_amalgam'];
					$placeholders['mixed_composite']=$_POST['mixed_composite'];
					$placeholders['mixed_gic']=$_POST['mixed_gic'];
					$placeholders['pedo_missing_teeth']=$_POST['pedo_missing_teeth'];
					$placeholders['pedo_gic']=$_POST['pedo_gic'];
					$placeholders['pedo_roots']=$_POST['pedo_roots'];
					$placeholders['pedo_occlusal']=$_POST['pedo_occlusal'];
					$placeholders['pedo_distal_occlusal']=$_POST['pedo_distal_occlusal'];
					$placeholders['pedo_mesial_occlusal']=$_POST['pedo_mesial_occlusal'];
					$placeholders['pedo_root_carious']=$_POST['pedo_root_carious'];
					$placeholders['pedo_cervical']=$_POST['pedo_cervical'];
					$placeholders['pedo_crown']=$_POST['pedo_crown'];
					$placeholders['pedo_implant']=$_POST['pedo_implant'];
					$placeholders['pedo_denture']=$_POST['pedo_denture'];
					$placeholders['pedo_bridge']=$_POST['pedo_bridge'];
					$placeholders['pedo_root_canal']=$_POST['pedo_root_canal'];
					$placeholders['pedo_amalgam']=$_POST['pedo_amalgam'];
					$placeholders['pedo_composite']=$_POST['pedo_composite'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

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

			//now insert xrays





			$num_xrays=count($xray_done_name_array);
			$ni=0;
			while($ni < $num_xrays){
				//insert for invoice
				if($pay_type_array[$ni]==1){
					if($pre_auth_needed=='YES' or $smart_needed=='YES'){$authorised_cost=NULL;}
					elseif($pre_auth_needed!='YES' and $smart_needed!='YES'){$authorised_cost=$amount_array[$ni];}


					$sql=$error=$s='';$placeholders=array();
					$sql="insert into xray_holder set
							pid=:pid,
							doc_id=:doc_id,
							date_taken=now(),
							xrays_done=:xrays,
							cost=:xray_cost,
							pay_type=:pay_type,
							status=2,
							teeth=:teeth,
							xray_comments=:xray_comments,

							xray_description=:xray_description,
							authorised_cost=:authorised_cost";

					$error="Unable to add xray to tplan procedure";
							$placeholders['pid']=$_SESSION['pid'];
							$placeholders['doc_id']=$_SESSION['id'];
							$placeholders['pay_type']=$pay_type_array[$ni];
							$placeholders['xray_cost']=$amount_array[$ni];
							$placeholders['xrays']=$xray_type_array[$ni];// $xray_teeth_array[$ni]";
							$placeholders['teeth']="$xray_teeth_array[$ni]";
							$placeholders['xray_comments']="$xray_comment_array[$ni]";
						   /*  $placehoders['xray_image']="$name[$ni]"; */
							$placeholders['xray_description']="$xray_description_array[$ni]";
							$placeholders['authorised_cost']=$authorised_cost;
					$xray_holder_id = 	get_insert_id($sql, $placeholders, $error, $pdo);
				}
				elseif($pay_type_array[$ni]!=1){

					$sql=$error=$s='';$placeholders=array();
					$sql="insert into xray_holder set
							pid=:pid,
							doc_id=:doc_id,
							date_taken=now(),
							xrays_done=:xrays,
							cost=:xray_cost,
							pay_type=:pay_type,
							status=2,
							teeth=:teeth,
							xray_comments=:xray_comments,
							xray_description=:xray_description,
							authorised_cost=:authorised_cost";
					$error="Unable to add xray to tplan procedure";
							$placeholders['pid']=$_SESSION['pid'];
							$placeholders['doc_id']=$_SESSION['id'];
							$placeholders['pay_type']=$pay_type_array[$ni];
							$placeholders['xray_cost']=$amount_array[$ni];
							$placeholders['xrays']=$xray_type_array[$ni];// $xray_teeth_array[$ni]";
							$placeholders['teeth']="$xray_teeth_array[$ni]";
							$placeholders['xray_comments']="$xray_comment_array[$ni]";
							/* $placehoders['xray_image']="$name[$ni]"; */
							$placeholders['xray_description']="$xray_description_array[$ni]";
							$placeholders['authorised_cost']=$amount_array[$ni];
					$xray_holder_id = 	get_insert_id($sql, $placeholders, $error, $pdo);
				}
				//insert into tplan_xray_count

					/*$sql=$error=$s='';$placeholders=array();
					$sql="insert into tplan_xray_count set
							xray_id=:xray_id,
							teeth=:teeth,
							xray_holder_id=:xray_holder_id";
					$error="Unable to add xray to tplan xray count";
							$placeholders['xray_id']=$xray_type_array[$ni];
							$placeholders['xray_holder_id']=$xray_holder_id;
							$placeholders['teeth']="$xray_teeth_array[$ni]";
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					*/
				$ni++;

			}


			$tx_result = $pdo->commit();
			if($tx_result){$message="good#on-examination#Patient details saved. ";}
			elseif(!$tx_result){$message="bad#Unable to save patient details ";}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save patient details  ";
		}
	}

		$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
	//echo "$message";
		echo "$message";

}

//this is for  insurance payments
elseif(isset($_POST['token_inv_pay2']) and isset($_SESSION['token_inv_pay2']) and $_POST['token_inv_pay2']==$_SESSION['token_inv_pay2']
	and userHasRole($pdo,49)){
	//	echo "bad#exit $_POST[token_inv_pay2]";exit;
		//check if token exists
		$sql=$error=$s='';$placeholders=array();
		$sql="select id from check_token where token_val=:token_val";
		$error="Unable to get token val";
		$placeholders['token_val']=$_POST['token_inv_pay2'];
		$s = select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			//update count time
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into check_token set token_val=:token_val, when_added=now()";
			$error="Unable to add 2 token val";
			$placeholders['token_val']=$_POST['token_inv_pay2'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			//echo "bad#token";

		}
		else{
		$_SESSION['token_inv_pay2']='';
		unset($_SESSION['token_inv_pay2']);
	$payment_amount=$_POST['invoice_payment'];
	$ninye=$_POST['ninye'];
	//$treatment_procedure
	$n=count($ninye);
	$i=$total=0;
	$exit_flag=false;
			//check if amount is set
			if(!isset($_POST['amount']) or $_POST['amount']==''){
				$exit_flag=true;
				$message="bad#Please specify the amount paid.";
			}

			//check if pay type is set
			if(!$exit_flag and (!isset($_POST['non_ins_payment_type']) or $_POST['non_ins_payment_type']=='')){
				$exit_flag=true;
				$message="bad#Please specify the payment type.";
			}

			//check if amount is > 0
			if(!$exit_flag and $_POST['amount']==0){
				$exit_flag=true;
				$message="bad#The amount paid must be greater than zero!!!";
			}

			//check if amount is avlaid number
			if(!$exit_flag){
				//remove commas
				$amount=str_replace(",", "", $_POST["amount"]);
				if(!ctype_digit($amount)){
					//check if it has only 2 decimal places
					$data=explode('.',$amount);
					$invalid_value=html($amount);
					if ( count($data) != 2 ){

					$message="bad#Amount specified, $invalid_value is not a valid number. ";
					$exit_flag=true;
					}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
					$message="bad#Amount specified, $invalid_value is not a valid number. ";
					$exit_flag=true;
					}
				}
			}

			//check that payment type is set correctly
			if(!$exit_flag){
				$pay_type=$encrypt->decrypt($_POST['non_ins_payment_type']);
				//ensure pay type is valid option
				if(!$exit_flag and $pay_type != 2  and $pay_type != 3  and $pay_type != 4  and $pay_type != 5   and $pay_type != 9){
					$exit_flag=true;
					$message="bad#Please specify the pay type.";
				}
				//cheque_number
				if(!$exit_flag and $pay_type==3 and (!isset($_POST['cheque_number']) or $_POST['cheque_number']=='')){
					$exit_flag=true;
					$message="bad#Please specify the cheque number.";
				}

				//mpesa_number
				if(!$exit_flag and $pay_type==4 and (!isset($_POST['mpesa_number']) or $_POST['mpesa_number']=='')){
					$exit_flag=true;
					$message="bad#Please specify the Mpesa transaction number.";
				}

				//visa_number
				if(!$exit_flag and $pay_type==5 and (!isset($_POST['visa_number']) or $_POST['visa_number']=='')){
					$exit_flag=true;
					$message="bad#Please specify the VISA transaction number.";
				}

				/*//waiver
				if(!$exit_flag and $pay_type==6 and (!isset($_POST['waiver_reason']) or $_POST['waiver_reason']=='')){
					$exit_flag=true;
					$message="bad#Please specify the reason for giving the payment waiver.";
				}	*/

				//EFT
				if(!$exit_flag and $pay_type==9 and (!isset($_POST['eft_number']) or $_POST['eft_number']=='')){
					$exit_flag=true;
					$message="bad#Please specify the EFT transaction number.";
				}
			}

			//check if totals match
				if(!$exit_flag){
					while($i < $n){
						$data1 = $encrypt->decrypt($ninye[$i]);
						$data=explode('#',$data1);
						//echo "- $data -";
						//$treatment_procedure_id=$data[0];
						$invoice_id=$data[0];
						$balance=$data[1];
						$pid=$data[2];
					//	echo "- $treatment_procedure_id - $invoice_id = $balance - $pid -";
						if(!$exit_flag and $payment_amount[$i]=='') {
							$i++;
							continue;
						}
						//check if amount paid is integer
						elseif(!$exit_flag and $payment_amount[$i]!=''){
							$invalid_amount=html("$payment_amount[$i]");
							//remove commas
							$payment_amount[$i]=str_replace(",", "", $payment_amount[$i]);
								//check if amount is integer
							if(!ctype_digit($payment_amount[$i])){//echo "ooooo $unit_price[$i] ";
								//check if it has only 2 decimal places
								$data=explode('.',$payment_amount[$i]);

								if ( count($data) != 2 ){

								$message="bad#Unable to make payments as $invalid_amount is not a valid payment. ";
								$exit_flag=true;
								break;
								}
								elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
								$message="bad#Unable to make payments as $invalid_amount is not a valid payment. ";
								$exit_flag=true;
								break;
								}
							}
						}
						//check if amount paid is more than required
					//	print_r( $_SESSION['balance_lab']);
						if(!$exit_flag and $payment_amount[$i] > $balance){
							$message="bad#Unable to make payments as $invalid_amount is greater than amount due of ".number_format($balance,2);
							$exit_flag=true;
							break;
						}


							$total = $total + $payment_amount[$i];

						$i++;
					}


					//check if totals match
					if(!$exit_flag and $total != $amount){
						$message="bad#You have specified the total value of the payment as ".number_format(html($_POST['amount']),2)." but you want to
						pay invoices worth ".number_format($total,2)." !!!";
						$exit_flag=true;
					}
				}

	try{
			$pdo->beginTransaction();
			$i=0;
			$receipt_number='';
			while($i < $n){
				$data1 = $encrypt->decrypt($ninye[$i]);
				$data=explode('#',$data1);
				//echo "- $data -";
				//$treatment_procedure_id=$data[0];
				$invoice_id=$data[0];
				$balance=$data[1];
				$pid=$data[2];
			//	echo "- $treatment_procedure_id - $invoice_id = $balance - $pid -";
				if(!$exit_flag and $payment_amount[$i]=='') {
					$i++;
					continue;
				}
				//check if amount paid is integer
				/*elseif(!$exit_flag and $payment_amount[$i]!=''){
					$invalid_amount=html("$payment_amount[$i]");
					//remove commas
					$payment_amount[$i]=str_replace(",", "", $payment_amount[$i]);
						//check if amount is integer
					if(!ctype_digit($payment_amount[$i])){//echo "ooooo $unit_price[$i] ";
						//check if it has only 2 decimal places
						$data=explode('.',$payment_amount[$i]);

						if ( count($data) != 2 ){

						$message="bad#Unable to make payments as $invalid_amount is not a valid payment. ";
						$exit_flag=true;
						break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#Unable to make payments as $invalid_amount is not a valid payment. ";
						$exit_flag=true;
						break;
						}
					}
				}*/
				$payment_amount[$i]=str_replace(",", "", $payment_amount[$i]);
				//check if amount paid is more than required
			//	print_r( $_SESSION['balance_lab']);
				if(!$exit_flag and $payment_amount[$i] > $balance){
					$message="bad#Unable to make payments as $invalid_amount is greater than amount due of ".number_format($balance,2);
					$exit_flag=true;
					break;
				}



				//now insert payment

				if(!$exit_flag){
					//insert into invoice receipt generator
					$id=0;
					//first get receipt number for non insured payment
					$sql=$error=$s='';$placeholders=array();
					$sql="select max(receipt_num) from invoice_receipt_generator";
					$error="Unable to get  insured receipt number";
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$id=$row[0] + 1;}
					if($id == 0){$id = 1;}

					$sql=$error=$s='';$placeholders=array();
					$sql = "insert into invoice_receipt_generator set  receipt_num=:receipt_num";
					$error = "Unable to get receipt_number";
					$placeholders[':receipt_num']=$id ;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					if($receipt_number==''){$receipt_number="RI$id-".date('m/y');$receipt_num_id=$id;}

					//insert into payments table
					$sql=$error=$s='';$placeholders=array();
					$sql = "insert into payments set when_added=now(), receipt_num=:receipt_num, amount=:amount, pay_type=:pay_type,
													pid=:pid, tx_number=:tx_number, invoice_id=:invoice_id,
													receipt_num_id=:receipt_num_id, created_by=:created_by";
					$error = "Unable to make insurance payment";
					$placeholders[':receipt_num']="$receipt_number" ;
					$placeholders[':amount']=$payment_amount[$i];
					$placeholders[':pay_type']=$pay_type ;
					$placeholders[':pid']=$pid;
					if($pay_type==2){$placeholders[':tx_number']='';}
					elseif($pay_type==3){$placeholders[':tx_number']=$_POST['cheque_number'];}
					elseif($pay_type==4){$placeholders[':tx_number']=$_POST['mpesa_number'];}
					elseif($pay_type==5){$placeholders[':tx_number']=$_POST['visa_number'];}
					elseif($pay_type==9){$placeholders[':tx_number']=$_POST['eft_number'];}
					$placeholders[':invoice_id']=$invoice_id ;
					$placeholders[':receipt_num_id']=$receipt_num_id ;
					$placeholders[':created_by']=$_SESSION['id'];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					$total = $total + $payment_amount[$i];
				}
				//update pt_balances
				$pid_encrypt2=$encrypt->encrypt($pid);
				$result=show_pt_statement_brief($pdo,$pid_encrypt2,$encrypt);
				$i++;
			}


			/*//check if totals match
			if(!$exit_flag and $total != $amount){
				$message="bad#You have specified the total value of the payment as ".number_format(html($_POST['amount']),2)." but you want to
				pay invoices worth ".number_format($total,2)." !!!";
				$exit_flag=true;
			}*/

			if(!$exit_flag){
				$tx_result = $pdo->commit();
				if($tx_result){
					$message="good#invoice-payments#Payments saved";
					//insert token into table and check if unique
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into check_token set token_val=:token_val, when_added=now()";
					$error="Unable to add token val";
					$placeholders['token_val']=$_POST['token_inv_pay2'];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);

				}
				elseif(!$tx_result){$message="bad#Unable to save payments";}
			}
			else{$pdo->rollBack();}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save payments";
		}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){
			$_SESSION['result_class']='success_response';
			$_SESSION['result_message']="$data[2]";
			$pid_bal="pid_$pid";
			$_SESSION["$pid_bal"]=array();
			$result=show_pt_statement_brief($pdo,$encrypt->encrypt("$pid"),$encrypt);
			$data=explode('#',"$result");
			$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
		}
	echo "$message";
	} //end else for token check
}

//this is for paying labs
elseif(isset($_POST['token_lab_pay2']) and isset($_SESSION['token_lab_pay2']) and $_POST['token_lab_pay2']==$_SESSION['token_lab_pay2']
	and userHasRole($pdo,33)){
	//$_SESSION['token_lab_pay2']='';
	/*print_r($_SESSION['balance_lab']);
	echo "$_SESSION[balance_lab][0][1635]";
	foreach($_SESSION['balance_lab'] as $balance_lab_array ){
		echo "$balance_lab_array[1635]";
	}*/
	$amount=$_POST['lab_paymnet'];
	$lab=$_POST['ninye'];
	$n=count($lab);
	$i=$total=0;
	$exit_flag=false;
	if(!isset($_POST['total_amount']) and $_POST['total_amount']==''){
		echo "bad#The total value of the payment must be specified. ";
		exit;
	}
	$invalid_amount=html($_POST['total_amount']);
	//check if total is a a valid number
	$_POST['total_amount']=str_replace(",", "", $_POST['total_amount']);
		//check if amount is integer

	if(!ctype_digit($_POST['total_amount'])){//echo "ooooo $unit_price[$i] ";
		//check if it has only 2 decimal places
		$data=explode('.',$_POST['total_amount']);

		if ( count($data) != 2 ){
			echo "bad#The total value of the payment,  $invalid_amount is not a valid payment. ";
			exit;
		}
		elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
			echo "bad#The total value of the payment,  $invalid_amount is not a valid payment. ";
			exit;
		}
	}
	try{
			$pdo->beginTransaction();

			while($i < $n){
				$lab_id = $encrypt->decrypt($lab[$i]);
				if(!$exit_flag and $amount[$i]=='') {
					$i++;
					continue;
				}
				//check if amount paid is integer
				elseif(!$exit_flag and $amount[$i]!=''){
					$invalid_amount=html("$amount[$i]");
					//remove commas
					$amount[$i]=str_replace(",", "", $amount[$i]);
						//check if amount is integer
					if(!ctype_digit($amount[$i])){//echo "ooooo $unit_price[$i] ";
						//check if it has only 2 decimal places
						$data=explode('.',$amount[$i]);

						if ( count($data) != 2 ){

						$message="bad#Unable to make payments as $invalid_amount is not a valid payment. ";
						$exit_flag=true;
						break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#Unable to make payments as $invalid_amount is not a valid payment. ";
						$exit_flag=true;
						break;
						}
					}
				}
				//check if amount paid is more than required
			//	print_r( $_SESSION['balance_lab']);
				if(!$exit_flag and $amount[$i] > $_SESSION['balance_lab'][$lab_id]){
					$message="bad#Unable to make payments as $invalid_amount is greater than amount due of ".number_format($_SESSION['balance_lab'][$lab_id],2);
					$exit_flag=true;
					break;
				}

				if(!$exit_flag and !isset($_POST['receipt_number'])){$_POST['receipt_number']='';}

				//now insert payment
				if(!$exit_flag){

					$sql=$error=$s='';$placeholders=array();
					$sql = "insert into lab_payments set lab_id=:lab_id,
													amount_paid=:amount_paid,
													user_id=:user_id,
													receipt_number=:receipt_number,
													date_of_payment=now()";
					$error = "Unable to receive lab";
					$placeholders[':lab_id']=$lab_id ;
					$placeholders[':amount_paid']=$amount[$i];
					$placeholders[':user_id']=$_SESSION['id'];
					$placeholders[':receipt_number']=$_POST['receipt_number'] ;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					$total = $total + $amount[$i];
				}
				$i++;
			}


			//check if totals match
			if(!$exit_flag and $total != $_POST['total_amount']){
				$message="bad#You have specified the total value of the payment as ".number_format(html($_POST['total_amount']),2)." but you want to
				pay lab work worth ".number_format($total,2)." !!!";
				$exit_flag=true;
			}

			if(!$exit_flag){
				$tx_result = $pdo->commit();
				if($tx_result){$message="good#lab-payments#Payments saved";$_SESSION['balance_lab']=array();}
				elseif(!$tx_result){$message="bad#Unable to save payments";}
			}
			else{$pdo->rollBack();}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save payments";
		}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
	echo "$message";
}

//this is for submitting  lab work
elseif(isset($_SESSION['token_add_lab']) and 	isset($_POST['token_add_lab']) and
	$_POST['token_add_lab']==$_SESSION['token_add_lab'] and userHasRole($pdo,29))
	{
	//$_SESSION['token_f_patient']=''
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag){
		$pid_c=$encrypt->decrypt($_POST['token_ninye']);
		$result = check_if_swapped($pdo,'pid',$pid_c);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	global $exit_flag;

	//check if this user is a doctor
	if(!$exit_flag and $_SESSION['logged_in_user_type']!=1){
		$exit_flag=true;
		$message="bad#Only a doctor can request for lab work.";
	}

	//check if technician exists
	if(!$exit_flag and $_POST['technician']==''){
		$exit_flag=true;
		$message="bad#Please select a technician.";
	}
	get_technician_exists($pdo);
	if (!$exit_flag){
		$_POST['technician']=$encrypt->decrypt($_POST['technician']);
		if(!in_array($_POST['technician'], $_SESSION['technician_array'])) {
		$message="bad#Please select a valid technician ";
		$var=html($_POST['technician']);
		$security_log="sombody tried to input $var into lab work as technician";
		log_security($pdo,$security_log);
		$exit_flag=true;
		}
	}

	//check bleaching trays
	if(!$exit_flag and isset($_POST['bleach']) and $_POST['bleach']!=''){
		$_POST['bleach']=$encrypt->decrypt($_POST['bleach']);
		//echo "bleach is $_POST[bleach]";
		if($_POST['bleach']!='.035 Std. Bleach Tray'  and $_POST['bleach']!='.060 Bruxers'){
				$message="bad#Unable to save lab work as bleaching trays are not correctly set";
				$var=html($_POST['bleach']);
				$security_log="sombody tried to input $var into lab work as bleaching trays";
				log_security($pdo,$security_log);
				$exit_flag=true;
		}
	}

	//check night gurads
	if(!$exit_flag and isset($_POST['night']) and $_POST['night']!=''){
		$_POST['night']=$encrypt->decrypt($_POST['night']);
	//	echo "bleach is $_POST[bleach]";
		if($_POST['night']!='articulated'  and $_POST['night']!='non-articulated'){
				$message="bad#Unable to save lab work as night gurads are not correctly set";
				$var=html($_POST['night']);
				$security_log="sombody tried to input $var into lab work as night gurads";
				log_security($pdo,$security_log);
				$exit_flag=true;
		}
	}

	//check fluoride
	if(!$exit_flag and isset($_POST['fluoride']) and $_POST['fluoride']!=''){
		$_POST['fluoride']=$encrypt->decrypt($_POST['fluoride']);
		//echo "bleach is $_POST[bleach]";
		if($_POST['fluoride']!='standard'){
				$message="bad#Unable to save lab work as fluoride is not correctly set";
				$var=html($_POST['fluoride']);
				$security_log="sombody tried to input $var into lab work as fluoride";
				log_security($pdo,$security_log);
				$exit_flag=true;
		}
	}

	//check mouth gurads
	if(!$exit_flag and isset($_POST['mouth']) and $_POST['mouth']!=''){
		$_POST['mouth']=$encrypt->decrypt($_POST['mouth']);
		//echo "bleach is $_POST[bleach]";
		if($_POST['mouth']!='non-articulated'  and $_POST['mouth']!='articulated'){
				$message="bad#Unable to save lab work as mouth guard is not correctly set";
				$var=html($_POST['mouth']);
				$security_log="sombody tried to input $var into lab work as mouth gurad";
				log_security($pdo,$security_log);
				$exit_flag=true;
		}
	}

	//check trays
	$trays=$_POST['trays'];
	$n2=count($trays);
	$i2=0;

	while($i2 < $n2){
		if($trays[$i2]==''){$i2++;continue;}
		else{
			if(!ctype_digit($trays[$i2])){
				$message="bad#Unable to save lab work as tray numbers are not correctly set. Please ensure only numbers are used";
				$exit_flag=true;
				break;
			}
		}
		$i2++;
	}

	//now check if teeth specified are correct
	function check_teeth_lab($teeth,$teeth_type){
		global $pdo, $exit_flag,$encrypt;
		$meno='';
		$n2=count($teeth);
		$i2=0;
		while($i2 < $n2){
			if($i2==0){$meno=$encrypt->decrypt($teeth[$i2]);}
			else{$meno="$meno,".$encrypt->decrypt($teeth[$i2]);}
			if (!in_array($encrypt->decrypt($teeth[$i2]), $_SESSION['meno_yote'])) {
				$message="bad#Unable to save lab work as as some teeth values for $teeth_type are not correctly set";
				$var=html($encrypt->decrypt($teeth[$i2]));
				$security_log="sombody tried to input $var into lab work for $teeth_type";
				log_security($pdo,$security_log);
				$exit_flag=true;
				break;
			}
			$i2++;
		}
		return "$meno";
	}//end function
	if(!$exit_flag and isset($_POST['crowns']) and $_POST['crowns']!=''){
		$_POST['crowns'] = check_teeth_lab($_POST['crowns'],"crowns");
	}
	if(!$exit_flag and isset($_POST['bridge']) and $_POST['bridge']!=''){
		$_POST['bridge'] = check_teeth_lab($_POST['bridge'],"bridge");
	}

	//check full denture
	if(!$exit_flag and isset($_POST['full_denture']) and $_POST['full_denture']!=''){
		$_POST['full_denture']=$encrypt->decrypt($_POST['full_denture']);
		//echo "bleach is $_POST[bleach]";
		if($_POST['full_denture']!='full_denture'){
				$message="bad#Unable to save lab work as full denture is not correctly set";
				$var=html($_POST['full_denture']);
				$security_log="sombody tried to input $var into lab work as full denture";
				log_security($pdo,$security_log);
				$exit_flag=true;
		}
	}


	//check cost is a valid number
	if(!$exit_flag and $_POST['amount']==''){
		$exit_flag=true;
		$message="bad#Please specify the cost for the lab work.";
	}
	if(!$exit_flag and $_POST['amount']!=''){
		//remove commas
		$_POST['amount']=str_replace(",", "", $_POST['amount']);
			//check if amount is integer
		if(!ctype_digit($_POST['amount'])){//echo "ooooo $unit_price[$i] ";
			//check if it has only 2 decimal places
			$data=explode('.',$_POST['amount']);
			$invalid_amount=html("$_POST[amount]");
			if ( count($data) != 2 ){

			$message="bad#Unable to save lab work as cost $invalid_amount is not a valid number. ";
			$exit_flag=true;
			}
			elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
			$message="bad#Unable to save lab work as cost $invalid_amount is not a valid number. ";
			$exit_flag=true;
			}
		}
	}

	//check date of return
	if(!$exit_flag and $_POST['date_required']==''){
		$exit_flag=true;
		$message="bad#Please specify the return date for the lab work.";
	}
	if(!$exit_flag and isset($_POST['date_required']) and $_POST['date_required']!='')	{
		$date='';
		$date=explode('-',$_POST['date_required']);
		if(!checkdate( $date[1],$date[2],$date[0] )){
		$dob=html($_POST['date_required']);
		$error_message="Unable to save b work as return date $dob is not in the correct format";
		$exit_flag=true;
		$message="somebody tried to input $dob as return date for lab work";
		log_security($pdo,$message);
		}
	}

	//empty the unset ones
	if(!isset($_POST['bleach']))  {$_POST['bleach']='';}
	if(!isset($_POST['night']))  {$_POST['night']='';}
	if(!isset($_POST['fluoride']))  {$_POST['fluoride']='';}
	if(!isset($_POST['mouth']))  {$_POST['mouth']='';}
	if(!isset($_POST['trays']))  {$_POST['trays']='';}
	if(!isset($_POST['crowns']))  {$_POST['crowns']='';}
	if(!isset($_POST['bridge']))  {$_POST['bridge']='';}
	if(!isset($_POST['ortho']))  {$_POST['ortho']='';}
	if(!isset($_POST['postcore']))  {$_POST['postcore']='';}
	if(!isset($_POST['full_denture']))  {$_POST['full_denture']='';}
	if(!isset($_POST['partial_denture']))  {$_POST['partial_denture']='';}
	if(!isset($_POST['shade']))  {$_POST['shade']='';}
	if(!isset($_POST['description']))  {$_POST['description']='';}
	//now insert
	if(!$exit_flag){
		try{
			$pdo->beginTransaction();

			$sql=$error=$s='';$placeholders=array();
			$sql="insert into labs set when_added=now(),
			pid=:pid,
			doc_id=:doc_id,
			technician=:technician,
			bleach=:bleach,
			night=:night,
			fluoride=:fluoride,
			mouth=:mouth,
			description=:description,
			shade=:shade,
			crowns=:crowns,
			bridge=:bridge,
			ortho=:ortho,
			post_core=:post_core,
			full_denture=:full_denture,
			partial_denture=:partial_denture,
			amount=:amount,
			date_required=:date_required";
			$error="Unable to update labs";
			$placeholders[':pid']=$encrypt->decrypt($_POST['token_ninye']);
			$placeholders[':doc_id']=$_SESSION['id'];
			$placeholders[':technician']=$_POST['technician'];
			$placeholders[':bleach']=$_POST['bleach'];
			$placeholders[':night']=$_POST['night'];
			$placeholders[':fluoride']=$_POST['fluoride'];
			$placeholders[':mouth']=$_POST['mouth'];
			$placeholders[':description']=$_POST['description'];
			$placeholders[':shade']=$_POST['shade'];
			$placeholders[':crowns']=$_POST['crowns'];
			$placeholders[':bridge']=$_POST['bridge'];
			$placeholders[':ortho']=$_POST['ortho'];
			$placeholders[':post_core']=$_POST['postcore'];
			$placeholders[':full_denture']=$_POST['full_denture'];
			$placeholders[':partial_denture']=$_POST['partial_denture'];
			$placeholders[':amount']=$_POST['amount'];
			$placeholders[':date_required']=$_POST['date_required'];
			$s = 	get_insert_id($sql, $placeholders, $error, $pdo);

			//now insert trays
			$trays=$_POST['trays'];
			$n2=count($trays);
			$i2=0;

			while($i2 < $n2){
				if($trays[$i2]==''){$i2++;continue;}
				else{
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="insert into lab_trays set
					lab_id=:lab_id,
					tray_number=:tray_number";
					$error2="Unable to update lab_trays";
					$placeholders2[':lab_id']=$s;
					$placeholders2[':tray_number']=$trays[$i2];
					$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
				}
				$i2++;
			}

			if($s){$message="good#lab_work#Lab work saved. ";}
			elseif(!$s){$message="bad#Unable to save lab work ";}

			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save lab work  ";
		}
	}
			$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]#$s";
		}
	echo "$message";
}

//this is for viewwing a lab
elseif(isset($_POST['view_lab']) and $_POST['view_lab']!='')
	{
	display_lab($pdo, $_POST['view_lab']);
	}

//dispcth finished labs to patient in treatment done
if(isset($_POST['token_patient_work2']) and $_POST['token_patient_work2']!=''
	and $_POST['token_patient_work2']==$_SESSION['token_patient_work2'] and userHasRole($pdo,20)){
	//$_SESSION['token_patient_work2']='';
	try{
			$pdo->beginTransaction();
			// receive trays
			$lab=$_POST['dispatched'];
			$n=count($lab);
			$i=0;
			while($i < $n){
				$sql=$error=$s='';$placeholders=array();
				$sql = "update labs set date_lab_given_to_patient=now() where lab_id=:lab_id";
				$error = "Unable to dispatch finished lab work to patient";
				$placeholders[':lab_id']=$encrypt->decrypt($lab[$i]);
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);
				$i++;
			}

			$tx_result = $pdo->commit();
			if($tx_result){echo "good#Lab work dispatched</div>";}
			elseif(!$tx_result){echo "<div class='grid-100 feedback error_response'>Unable to dispatch lab work</div>";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//echo "<div class='grid-100 feedback error_response'>Unable to dispatch lab work</div>";
		}
}

//this will refresh finished undispatched labs in treatment done
elseif(isset($_POST['undispatched_finished_labs']) and $_POST['undispatched_finished_labs']!=''
	and userHasRole($pdo,20)){
	undispatched_finished_lab_work($pdo,$_SESSION['pid'],$encrypt);
}

//this will refresh finished undispatched labs in treatment done
elseif(isset($_POST['lab_request']) and $_POST['lab_request']!=''
	and userHasRole($pdo,20)){
	$pid=$encrypt->encrypt($_SESSION['pid']);
	lab_prescription($pid,$encrypt,$pdo,'#lab_form_tdone');
}

//this will check if a user has any role
elseif(isset($_POST['check_for_roles']) and $_POST['check_for_roles']!='' and userHasRole($pdo,44)){
			//echo $_SESSION['user_set_privilege'];
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select role_id from user_roles where user_id=:user_id";
			$error2="Unable to check for user role ";
			$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
			if($s2->rowCount() > 0){echo "has_role";
			}
			else{echo "no_role";
			}

}

//this will check if a user has any individual privileges
elseif(isset($_POST['check_for_individual_privileges']) and $_POST['check_for_individual_privileges']!='' and userHasRole($pdo,44)){
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select menu_id from privileges where user_id=:user_id";
			$error2="Unable to check for user privileges ";
			$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
			if($s2->rowCount() > 0){echo "has_privilege";
			}
			else{echo "no_privilege";
			}
}

//this is for adding/editing a role
elseif(isset($_SESSION['token_role2']) and 	isset($_POST['token_role2']) and $_POST['token_role2']==$_SESSION['token_role2'] and userHasRole($pdo,43))
	{
	//$_SESSION['token_f_patient']=''
	$exit_flag=false;
	$user_priv_array = array();
	//global $exit_flag;

			//get parent menus and insert them if they are missing so that sub-menus will still be availlable afetr assignment
			function insert_parent_menus($menu_id,$role_id,$user_priv_array,$pdo){
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select parent_id from menus where id=:menu_id";
				$error2="Unable to get menu parent ";
				$placeholders2[':menu_id']=$menu_id;
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
				foreach($s2 as $row2){
					if($row2['parent_id']!=0){
						//check if menu _id is in array
						$sql3=$error3=$s3='';$placeholders2=array();
						$sql3="select menu_id from role_privileges where role_id=:role_id and menu_id=:menu_id";
						$error3="Unable to get role privileges ";
						$placeholders3[':role_id']=$role_id;
						$placeholders3[':menu_id']=$row2['parent_id'];
						$s3 = select_sql($sql3, $placeholders3, $error3, $pdo);
						if($s3->rowCount() > 0){insert_parent_menus($row2['parent_id'],$role_id,$user_priv_array,$pdo);}
						//if(in_array($row2['parent_id'], $user_priv_array)){insert_parent_menus($row2['parent_id'],$role_id,$user_priv_array,$pdo);}
						else{
							$sql=$error=$s='';$placeholders=array();
							$sql="insert into role_privileges set role_id=:role_id, menu_id=:menu_id";
							$error="Unable to add parent menu  ";
							$placeholders[':menu_id']=$row2['parent_id'];
							$placeholders[':role_id']=$role_id;
							$s = 	insert_sql($sql, $placeholders, $error, $pdo);
							$user_priv_array[]=$row2['parent_id'];
							insert_parent_menus($row2['parent_id'],$role_id,$user_priv_array,$pdo);
						}
					}
				}
			}

	//empty the unset ones
	if(!isset($_POST['description']))  {$_POST['description']='';}

	//check if role name is set
	if(!isset($_POST['role_name']) or $_POST['role_name']=='' ){
		$exit_flag=true;
		$message="bad#The role name must be specified";
	}
	if(!$exit_flag){
		//start transaction
		try{
			$pdo->beginTransaction();

			//now do insert for existing role
		if($_SESSION['role_id']!=''){


			//update role name and privilege
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="update roles set name=:name , description=:description where id=:role_id";
			$error2="Unable to update role name";
			$placeholders2[':name']=$_POST['role_name'];
			$placeholders2[':description']=$_POST['description'];
			$placeholders2[':role_id']=$_SESSION['role_id'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//now update role privileges
			//first remove current roles
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="delete from role_privileges where  role_id=:role_id";
			$error2="Unable to delete privileges for role";
			$placeholders2[':role_id']=$_SESSION['role_id'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//deleet role sub menu privileges for same page
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="delete from role_sub_privileges where  role_id=:role_id";
			$error2="Unable to delete sub privileges ";
			$placeholders2[':role_id']=$_SESSION['role_id'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//now add the new privileges for the role
			$n=0;
			if(isset($_POST['privileges'])){
				$privilege=$_POST['privileges'];
				$n=count($privilege);
			}
			$i=0;
			while($i < $n){
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="insert into role_privileges set role_id=:role_id , menu_id=:menu_id";
				$error2="Unable to update role privileges";
				$menu_item=$encrypt->decrypt($privilege[$i]);
				$placeholders2[':menu_id']=$menu_item;
				$placeholders2[':role_id']=$_SESSION['role_id'];
				$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
				//now insert parent menus
				$user_priv_array[]=$menu_item;
				insert_parent_menus($menu_item,$_SESSION['role_id'],$user_priv_array,$pdo);
				$i++;
			}

			//now add the new role sub menu same page privileges
			$n=0;
			if(isset($_POST['sub_privileges'])){
				$privilege=$_POST['sub_privileges'];
				$n=count($privilege);
			}
			$i=0;
			while($i < $n){
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="insert into role_sub_privileges set parent_menu_id=:parent_menu_id , sub_menu_id=:sub_menu_id, role_id=:role_id";
				$error2="Unable to update role sub menu privileges";
				$data1=$encrypt->decrypt($privilege[$i]);
				$data=explode('#',"$data1");
				$parent_menu_id=$data[1];
				$sub_menu_id=$data[0];
				$placeholders2[':parent_menu_id']=$parent_menu_id;
				$placeholders2[':sub_menu_id']=$sub_menu_id;
				$placeholders2[':role_id']=$_SESSION['role_id'];
				$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

				//insert parent menus as well
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select menu_id from role_privileges where role_id=:role_id and menu_id=:menu_id";
				$error2="Unable to get role privileges ";
				$placeholders2[':role_id']=$_SESSION['role_id'];
				$placeholders2[':menu_id']=$parent_menu_id;
				$error2="Unable to select user privileges ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0 ){}
				else{
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="insert into role_privileges set menu_id=:menu_id , role_id=:role_id";
					$error2="Unable to update role privileges";
					$placeholders2[':menu_id']=$parent_menu_id;
					$placeholders2[':role_id']=$_SESSION['role_id'];
					$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
				}

				$user_priv_array[]=$parent_menu_id;
				insert_parent_menus($parent_menu_id,$_SESSION['role_id'],$user_priv_array,$pdo);
				$i++;
			}
		}
		//now do insert for new role
		elseif($_SESSION['role_id']==''){
			//check if the role name exists
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select name from roles where upper(name)=:name ";
			$error2="Unable to check if role name exists";
			$placeholders2[':name']=strtoupper($_POST['role_name']);
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
			if($s2->rowCount() > 0){
				$name=html($_POST['role_name']);
				$exit_flag=true;
				$message="bad#Role, $name already exists";
			}
			else{
				//insert role name and privilege
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="insert into roles set name=:name , description=:description ";
				$error2="Unable to insert role name";
				$placeholders2[':name']=$_POST['role_name'];
				$placeholders2[':description']=$_POST['description'];
				$role_id = 	get_insert_id($sql2, $placeholders2, $error2, $pdo);

				//now add the new privileges for the role
				$n=0;
				if(isset($_POST['privileges'])){
					$privilege=$_POST['privileges'];
					$n=count($privilege);
				}
				$i=0;
				while($i < $n){
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="insert into role_privileges set role_id=:role_id , menu_id=:menu_id";
					$error2="Unable to update role privileges";
					$menu_item=$encrypt->decrypt($privilege[$i]);
					$placeholders2[':menu_id']=$menu_item;
					$placeholders2[':role_id']=$role_id;
					$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
					//now insert parent menus
					$user_priv_array[]=$menu_item;
					insert_parent_menus($menu_item,$_SESSION['role_id'],$user_priv_array,$pdo);
					$i++;
				}

			//now add the new role sub menu same page privileges
			$n=0;
			if(isset($_POST['sub_privileges'])){
				$privilege=$_POST['sub_privileges'];
				$n=count($privilege);
			}
			$i=0;
			while($i < $n){
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="insert into role_sub_privileges set parent_menu_id=:parent_menu_id , sub_menu_id=:sub_menu_id, role_id=:role_id";
				$error2="Unable to update role sub menu privileges";
				$data1=$encrypt->decrypt($privilege[$i]);
				$data=explode('#',"$data1");
				$parent_menu_id=$data[1];
				$sub_menu_id=$data[0];
				$placeholders2[':parent_menu_id']=$parent_menu_id;
				$placeholders2[':sub_menu_id']=$sub_menu_id;
				$placeholders2[':role_id']=$role_id;
				$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

				//insert parent menus as well
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select menu_id from role_privileges where role_id=:role_id and menu_id=:menu_id";
				$error2="Unable to get role privileges ";
				$placeholders2[':role_id']=$role_id;
				$placeholders2[':menu_id']=$parent_menu_id;
				$error2="Unable to select user privileges ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0 ){}
				else{
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="insert into role_privileges set menu_id=:menu_id , role_id=:role_id";
					$error2="Unable to update role privileges";
					$placeholders2[':menu_id']=$parent_menu_id;
					$placeholders2[':role_id']=$role_id;
					$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
				}

				$user_priv_array[]=$parent_menu_id;
				insert_parent_menus($parent_menu_id,$role_id,$user_priv_array,$pdo);
				$i++;
			}
			}
		}



				if(!$exit_flag){
					$tx_result = $pdo->commit();
					if($tx_result){$message="good#roles#Role privileges saved. ";}
					//elseif(!$tx_result){//$message="bad#Unable to save role privileges ";}
				}
				else{$pdo->rollBack();}

			}
			catch (PDOException $e)
			{
			$pdo->rollBack();
			//$message="bad#Unable to save role privileges ";
			}
	}
		$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='error_response';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo "$message";

}


//this is for adding/editing a user roles/ privileges
elseif(isset($_SESSION['token_privilege2']) and 	isset($_POST['token_privilege2']) and $_POST['token_privilege2']==$_SESSION['token_privilege2']
	and userHasRole($pdo,44))
	{
	//$_SESSION['token_f_patient']=''
	$exit_flag=false;
	$user_priv_array = array();
	global  $user_priv_array;



	if(!$exit_flag){
		//start transaction
		try{
			$pdo->beginTransaction();


			//get parent menus and insert them if they are missing so that sub-menus will still be availlable afetr assignment
			function insert_parent_menus($menu_id,$user_id,$user_priv_array,$pdo){
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select parent_id from menus where id=:menu_id";
				$error2="Unable to get menu parent ";
				$placeholders2[':menu_id']=$menu_id;
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
				foreach($s2 as $row2){
					if($row2['parent_id']!=0){
						//check if menu _id is in array
						$sql3=$error3=$s3='';$placeholders2=array();
						$sql3="select menu_id from privileges where user_id=:user_id and menu_id=:menu_id";
						$error3="Unable to get user privileges ";
						$placeholders3[':user_id']=$user_id;
						$placeholders3[':menu_id']=$row2['parent_id'];
						$s3 = select_sql($sql3, $placeholders3, $error3, $pdo);
						if($s3->rowCount() > 0){insert_parent_menus($row2['parent_id'],$user_id,$user_priv_array,$pdo);}
						else{
							$sql=$error=$s='';$placeholders=array();
							$sql="insert into privileges set user_id=:user_id, menu_id=:menu_id";
							$error="Unable to add parent menu  ";
							$placeholders[':menu_id']=$row2['parent_id'];
							$placeholders[':user_id']=$user_id;
							$s = 	insert_sql($sql, $placeholders, $error, $pdo);
							$user_priv_array[]=$row2['parent_id'];
							insert_parent_menus($row2['parent_id'],$user_id,$user_priv_array,$pdo);
						}
					}
				}
			}



			//check if we are inserting roles and begin by deleting individial privikges
		if(isset($_POST['ninye_role'])){
			//deleet individual privileges to avoid conflict
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="delete from privileges where  user_id=:user_id";
			$error2="Unable to delete user privileges ";
			$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//deleet user sub menu privileges for same page
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="delete from sub_privileges where  user_id=:user_id";
			$error2="Unable to delete sub privileges ";
			$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//deleet user roles also as they may have changed
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="delete from user_roles where  user_id=:user_id";
			$error2="Unable to delete role privileges ";
			$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//now add the new role privileges
			$role=$_POST['roles'];
			$n=count($role);
			$i=0;
			while($i < $n){
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="insert into user_roles set role_id=:role_id , user_id=:user_id";
				$error2="Unable to update user role privileges";
				$placeholders2[':role_id']=$encrypt->decrypt($role[$i]);
				$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
				$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

				$i++;
			}
		}
		//check if we are inserting privileges and begin by deleting user roles for this guy
		elseif(isset($_POST['ninye_privilege']) ){
			//deleet user privileges individually as they may have changed
			//echo"deleting for user ==$_SESSION[user_set_privilege]==$_POST[privileges]xxx";
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="delete from privileges where  user_id=:user_id";
			$error2="Unable to delete user privileges ";
			$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//deleet user roles  for this guy to avoid conflict
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="delete from user_roles where  user_id=:user_id";
			$error2="Unable to delete role privileges ";
			$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//deleet user sub menu privileges for same page
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="delete from sub_privileges where  user_id=:user_id";
			$error2="Unable to delete sub privileges ";
			$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
			$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

			//now add the new individual privileges
			$n=0;
			if(isset($_POST['privileges'])){
				$privilege=$_POST['privileges'];
				$n=count($privilege);
			}
			$i=0;
			while($i < $n){
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="insert into privileges set menu_id=:menu_id , user_id=:user_id";
				$error2="Unable to update user individual privileges";
				$menu_item=$encrypt->decrypt($privilege[$i]);
				$placeholders2[':menu_id']=$menu_item;
				$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
				$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
				//insert parent menus as well
				$user_priv_array[]=$menu_item;
				insert_parent_menus($menu_item,$_SESSION['user_set_privilege'],$user_priv_array,$pdo);
				$i++;
			}

			//now add the new sub menu same page privileges
			$n=0;
			if(isset($_POST['sub_privileges'])){
				$privilege=$_POST['sub_privileges'];
				$n=count($privilege);
			}

			$i=0;
			while($i < $n){
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="insert into sub_privileges set parent_menu_id=:parent_menu_id , sub_menu_id=:sub_menu_id, user_id=:user_id";
				$error2="Unable to update user sub menu privileges";
				$data1=$encrypt->decrypt($privilege[$i]);
				$data=explode('#',"$data1");
				$parent_menu_id=$data[1];
				$sub_menu_id=$data[0];
				$placeholders2[':parent_menu_id']=$parent_menu_id;
				$placeholders2[':sub_menu_id']=$sub_menu_id;
				$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
				$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

				//insert parent menus as well
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select menu_id from privileges where user_id=:user_id and menu_id=:menu_id";
				$error2="Unable to get user privileges ";
				$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
				$placeholders2[':menu_id']=$parent_menu_id;
				$error2="Unable to delete user privileges ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0 ){}
				else{
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="insert into privileges set menu_id=:menu_id , user_id=:user_id";
					$error2="Unable to update user individual privileges";
					$placeholders2[':menu_id']=$parent_menu_id;
					$placeholders2[':user_id']=$_SESSION['user_set_privilege'];
					$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
				}

				$user_priv_array[]=$parent_menu_id;
				insert_parent_menus($parent_menu_id,$_SESSION['user_set_privilege'],$user_priv_array,$pdo);
				$i++;
			}
		}

				if(!$exit_flag){
					$tx_result = $pdo->commit();
					if($tx_result){$message="good#user_privileges#User privileges saved. ";}
					//elseif(!$tx_result){//$message="bad#Unable to save role privileges ";}
				}
				else{$pdo->rollBack();}

			}
			catch (PDOException $e)
			{
			$pdo->rollBack();
			$message="bad#Unable to save user privileges ";
			}
	}
		$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='error_response';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo "$message";

}

//this is for submitting  a user
elseif(isset($_SESSION['token_add_user2']) and 	isset($_POST['token_add_user2']) and
	$_POST['token_add_user2']==$_SESSION['token_add_user2'] and userHasRole($pdo,25))
	{
	//$_SESSION['token_f_patient']=''
	$exit_flag=false;
	global $exit_flag;
	$status="active";
	$password_reset='';

	//get action type
	$action=$encrypt->decrypt("$_POST[to_do]");
	if($action=='add_user'){

		//check if user_name exists
		if(!$exit_flag and isset($_POST['user_login_name']) and $_POST['user_login_name']!=''){
			$sql=$error1=$s='';$placeholders=array();
			$sql="select upper(user_name) from users where upper(user_name)=:user_name";
			$error="Unable to checkif user name already exists";
			$placeholders[':user_name']=strtoupper($_POST['user_login_name']);
			$s = select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){
				$exit_flag=true;
				$message="bad#Login name specified is already in use";
			}
		}
		//set sql
		$sql_var="insert into users set
				user_name=:user_name,
				password=:password,
					status=:status,
				first_name=:first_name,
				address=:address,
				middle_name=:middle_name,
				last_name=:last_name,
				gender=:gender,
				home_phone=:home_phone,
				mobile_number=:mobile_number,
					email_address=:email_address,
				photo_image=:photo_image,
				user_type=:user_type,
				when_added=now(),
				reset_password=1
				";


		//check password if they match
		if(!$exit_flag and (!isset($_POST['user_password1']) or $_POST['user_password1']=='') or
			(!isset($_POST['user_password2']) or $_POST['user_password2']=='')){
			$exit_flag=true;
			$message="bad#User's password must be specified";
		}
		if(!$exit_flag and $_POST['user_password1']!=$_POST['user_password2']){
			$exit_flag=true;
			$message="bad#Passwords given do not match";
		}
		//check password complexity
		/*if(!password_complexity($_POST['user_password1'])){
			$message="bad#$_SESSION[password_complexity_error]";
			$_SESSION['password_complexity_error']='';
		}*/
	}
	elseif($action=='edit_user' and !$exit_flag){
		//check if user_name exists
		if(!$exit_flag and isset($_POST['user_login_name']) and $_POST['user_login_name']!=''){
			$sql=$error1=$s='';$placeholders=array();
			$sql="select upper(user_name) from users where upper(user_name)=:user_name and id!=:id";
			$error="Unable to checkif user name already exists";
			$placeholders[':user_name']=strtoupper($_POST['user_login_name']);
			$placeholders[':id']=$_SESSION['user_login_id'];
			$s = select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){
				$exit_flag=true;
				$message="bad#Login name specified is already in use";
			}
		}
		//check password reset
		$password_variable=$password_reset=$password_change_date_variable='';
		if(!$exit_flag and isset($_POST['reset_password']) and $_POST['reset_password']=='reset'){
			$password_reset=hash_hmac('sha1', $_SESSION['user_login_name'], $salt);
			$password_variable=" password=:password_reset, reset_password=1,";
			$today=date('Y-m-d');
			$date = date_create("$today");
			date_sub($date, date_interval_create_from_date_string('91 days'));
			$password_change_date=date_format($date, 'Y-m-d');
			$password_change_date_variable=" date_of_last_password_change='$password_change_date',";
		}
		//check account lock
		if(!$exit_flag and isset($_POST['lock_account']) and $_POST['lock_account']=='lock_account'){
			$status="locked";
		}
		//set sql
		$sql_var="update users set
				user_name=:user_name,
				$password_variable
				$password_change_date_variable
					status=:status,
				first_name=:first_name,
				address=:address,
				middle_name=:middle_name,
				last_name=:last_name,
				gender=:gender,
				home_phone=:home_phone,
				mobile_number=:mobile_number,
					email_address=:email_address,
				photo_image=:photo_image,
				user_type=:user_type
				where id=:id
				";

	}

	//check first name
	if(!$exit_flag and (!isset($_POST['first_name']) or $_POST['first_name']=='')) {
		$exit_flag=true;
		$message="bad#User's first name must be specified";
	}
	//check user type
	if(!$exit_flag and (!isset($_POST['user_type']) or $_POST['user_type']=='')) {
		$exit_flag=true;
		$message="bad#The user type must be specified";
	}
	//check login name
	if(!$exit_flag and (!isset($_POST['user_login_name']) or $_POST['user_login_name']=='')) {
		$exit_flag=true;
		$message="bad#User's login name must be specified";
	}


	//empty the unset ones
	if(!isset($_POST['middle_name']))  {$_POST['middle_name']='';}
	if(!isset($_POST['last_name'])) {$_POST['last_name']='';}
	if(!isset($_POST['gender']))  {$_POST['gender']='';}
	if(!isset($_POST['address']))  {$_POST['address']='';}
	if(!isset($_POST['user_mobile_no']))  {$_POST['user_mobile_no']='';}
	if(!isset($_POST['user_home_phone']))  {$_POST['user_home_phone']='';}
	if(!isset($_POST['user_email_address']))  {$_POST['user_email_address']='';}
	/* if(!isset($_FILES['image_upload']['name']))  {$_FILES['image_upload']['name']='';} */


	if(!$exit_flag){
		try{
			$pdo->beginTransaction();

			//now delete old record
			/*$sql=$error=$s='';$placeholders=array();
			$sql="delete from patient_dental where pid=:pid";
			$error="Unable to update patient dental form";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);	*/
			//print_r($_POST);
			//now update with new details

			$sql=$error=$s='';$placeholders=array();
			$sql="$sql_var";
			$error="Unable to update user details";
			$placeholders[':user_type']=$encrypt->decrypt("$_POST[user_type]");
			$placeholders[':address']=$_POST['address'];
			$placeholders[':user_name']=$_POST['user_login_name'];

			if($action=='add_user'){
			$placeholders[':password']= hash_hmac('sha1', $_POST['user_password1'], $salt);
			}
			elseif($action=='edit_user'){
				if($password_reset!=''){
					$placeholders[':password_reset']= "$password_reset";

				}
			$placeholders[':id']= $_SESSION['user_login_id'];
			}

			 //upload photo

			 $name = $_FILES['image_upload']['name'];
			 $tmpName = $_FILES['image_upload']['tmp_name'];
			 $errors = $_FILES['image_upload']['error'];

			 // create folder path
			 $fname = md5(time().rand());
			 $name = $fname.$name;
			 $path = "Uploads/".$fname;
			 if($errors == 0){
				move_uploaded_file($tmpName, $path);
			}else{
				echo 'Unable to uplaod file.';
			}

			$placeholders[':status']="$status";
			$placeholders[':first_name']=$_POST['first_name'];
			$placeholders[':middle_name']=$_POST['middle_name'];
			$placeholders[':last_name']=$_POST['last_name'];
			$placeholders[':gender']=$_POST['gender'];
			$placeholders[':home_phone']=$_POST['user_home_phone'];
			$placeholders[':mobile_number']=$_POST['user_mobile_no'];
			$placeholders[':photo_image']=$_FILES['image_upload']['name'];
			$placeholders[':email_address']=$_POST['user_email_address'];

			$s = 	insert_sql($sql, $placeholders, $error,$pdo);
			if($s){$message="good#add_user#User details saved. ";}
			elseif(!$s){$message="bad#Unable to save user details ";}

			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save user details  ";
		}
	}
		$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='error_response';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo "$message";

}

//this will insert new company
elseif(isset($_POST['token2'])  and isset($_SESSION['token2']) and $_SESSION['token2'] == $_POST['token2']){

	//$_SESSION['token2']='';
	//save entries
	$i=0;
	$n=1;
	//
	//$insured_yes_no=html($_POST['insured_yes_no']);
	$insured_yes_no=$_POST['insured_yes_no'];
	$comp_name=html($_POST['employer_name']);
	$ins_id[$i]=$_POST['ins_name'];
	//$ins_id=$_POST['ins_name'];
	$pre_auth_needed[$i]=$_POST['pre_auth'];
	$smart_needed[$i]=$_POST['smart_check'];
	$co_pay_type[$i]=$_POST['co_pay'];
	$co_pay_val[$i]=$_POST['co_pay_value'];
	$start_cover[$i]=$_POST['start_date'];
	$end_cover[$i]=$_POST['end_date'];
	$cover_type[$i]=$_POST['cover_type'];
	$cover_limit[$i]=$_POST['cover_limit'];

	$exit_flag=false;
	try{
		$pdo->beginTransaction();
			while($i < $n){
					if($ins_id[$i]=='' and $insured_yes_no[$i]=='NO'){$ins_id[$i]=$encrypt->encrypt("3");}
					//echo "$insured_yes_no -- $insured_yes_no[$i]";exit;
					//check if employer is et
					//isset($_POST['employer_name']) and $_POST['employer_name']!='' and
					if($comp_name==''){$message = "bad# Employer not specified";
														$exit_flag=true;
														break;
					}
					//check in insurer is set
					$insured_yes_no[$i]=html("$insured_yes_no[$i]");
					if($insured_yes_no[$i]=='YES' and $ins_id[$i]==''){$message = "bad# This patient type is insured but no insurer has been specified";
														$exit_flag=true;
														break;
						}
					//now check cover limit

					if(isset($cover_limit[$i]) and $cover_limit[$i]!=''){
						if($cover_limit[$i]!='UNLIMITED'){
							$cover_limit[$i]=str_replace(",", "", "$cover_limit[$i]");
							if( !ctype_digit($cover_limit[$i])){
								//check if it has only 2 decimal places
								$data=explode('.',$cover_limit[$i]);
								if ( count($data) != 2 ){
									$cover_limit[$i]=html("$cover_limit[$i]");
									$message="bad# Unable to save changes as $cover_limit[$i] is not a valid number ";
									$exit_flag=true;
									break;
								}
								elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
									$cover_limit[$i]=html("$cover_limit[$i]");
									$message="bad# Unable to save changes as $cover_limit[$i] is not a valid number ";
									$exit_flag=true;
									break;
								}
							}
						}
					}
					else{$cover_limit[$i]='';}

					//now check start and end date
					$data=explode("-",$start_cover[$i]);
					if(isset($start_cover[$i]) and $start_cover[$i]!='' and !checkdate($data[1], $data[2], $data[0])){
							$start_cover[$i]=html("$start_cover[$i]");
							$message="bad# Unable to save changes as $start_cover[$i] is not a valid date ";
							$exit_flag=true;
							break;
						}
					$data=explode("-",$end_cover[$i]);
					if(isset($end_cover[$i]) and $end_cover[$i]!='' and !checkdate($data[1], $data[2], $data[0])){
							$end_cover[$i]=html("$end_cover[$i]");
							$message="bad# Unable to save changes as $end_cover[$i] is not a valid date ";
							$exit_flag=true;
							break;
						}
					//this will ensure that insurance is not empty
					/*if($ins_id[$i]==''){
						//check if pre-auth is set
						$error_message = " Unable to add new corprate as no insurer has been specified";
														$exit_flag=true;
														break;

					}*/

				//ensure all fields are correctly set
					if($ins_id[$i]==''){
						//check if pre-auth is set
						if($pre_auth_needed[$i]=='YES'){$message = "bad# Unable to add new employer, Pre-Auth needed has been set to YES for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if smart is set
						if($smart_needed[$i]=='YES'){$message = "bad# Unable to add new employer, Smart Check Needed  has been set to YES for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if co_pay is set
						if($co_pay_type[$i]!=''){		$co_pay=html("$co_pay_type[$i]");
														$message = "bad# Unable to add new employer, Co-Pay Type has been set to $co_pay for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if co_pay_val is set
						if($co_pay_val[$i]!=''){$co_pay_amount=html("$co_pay_val[$i]");
												$message = "bad# Unable to add new employer, Co-Pay Value has been set to $co_pay_amount for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if start_cover is set
						if($start_cover[$i]!=''){$start=html("$start_cover[$i]");
						$message = "bad# Unable to add new employer, Start Cover has been set to $start for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if end cover is set
						if($end_cover[$i]!=''){$end=html("$end_cover[$i]");
						$message = "bad# Unable to add new employer, End Cover has been set to $end for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if cover_type is set
						if($cover_type[$i]!=''){$cover_t=html("$cover_type[$i]");
													$message = "bad# Unable to add new employer, Cover Type has been set to $cover_t for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if cover limit
						if($cover_limit[$i]!=''){$cover_l=html("$cover_limit[$i]");
													$message = "bad# Unable to add new employer, Cover Limit has been set to $cover_l for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}

					}
					//this ios for when insurer is specified
					elseif($ins_id[$i]!='' and $encrypt->decrypt("$ins_id[$i]")!=3){//i.e not cash
						//check if pre-auth is set
						if($pre_auth_needed[$i]==''){$message = "bad# Unable to add new corprate, Pre-Auth needed has not been set  for $comp_name yet
														the company is insured";
														$exit_flag=true;
														$message1="an attempt has been made to make pre-auth needed empty for $comp_name in table covered_company";
														log_security($pdo,$message1);
														break;
						}
						//check if smart is set
						if($smart_needed[$i]==''){$message = "bad# Unable to add new corprate, Smart Check needed has not been set  for $comp_name yet
														the company is insured";
														$exit_flag=true;
														$message1="an attempt has been made to make smart check run needed empty for $comp_name in table covered_company";
														log_security($pdo,$message1);
														break;
						}
						//check if co_pay is set
						if($co_pay_type[$i]!='' and $co_pay_val[$i]==''){		$co_pay=html("$co_pay_type[$i]");
														$message = "bad# Unable to add new corprate, Co-Pay Type has been set to $co_pay for $comp_name but
														but no corresponding value has been set";
														$exit_flag=true;
														break;
						}
						//check if co_value is set
						if($co_pay_type[$i]=='' and $co_pay_val[$i]!=''){		$co_pay_amount=html("$co_pay_val[$i]");
														$message = "bad# Unable to add new corprate, Co-Pay Value  has been set to $co_pay_amount for $comp_name but
														but no corresponding Co-Pay Type  has been set";
														$exit_flag=true;
														break;
						}
						//check if start_cover is set
						if($start_cover[$i]==''){$start=html("$start_cover[$i]");
						$message = "bad# Unable to add new corprate, as Start Cover date has not been set  for $comp_name though the company is insured";
														$exit_flag=true;
														break;
						}
						//check if end cover is set
						if($end_cover[$i]==''){$end=html("$end_cover[$i]");
						$message = "bad# Unable to add new corprate, as End Cover date has not been set  for $comp_name though the company is insured";
														$exit_flag=true;
														break;
						}
						if($end_cover[$i] < $start_cover[$i]){$end=html("$end_cover[$i]");$start=html("$start_cover[$i]");
						$message = "bad# Unable to add new corprate, the end cover date of $end is before the start cover date of $start  for $comp_name.";
														$exit_flag=true;
														break;
						}
						//check if cover_type is set
						if($cover_type[$i]==''){$cover_t=html("$cover_type[$i]");
													$message = "bad# Unable to add new corprate, as Cover Type has not been set for $comp_name.";
														$exit_flag=true;
														break;
						}
						//check if cover limit
						if($cover_limit[$i]==''){$cover_l=html("$cover_limit[$i]");
													$message = "bad# Unable to add new corprate, as Cover Limit has not been set  for $comp_name";
														$exit_flag=true;
														break;
						}

					}
					// start by validating input
					//check i fvalue for co_pay is valid number
					//remove commas if they were used for formating
					$co_pay_val[$i]=str_replace(",", "", "$co_pay_val[$i]");
					if(isset($co_pay_val[$i]) and $co_pay_val[$i]!='' and !ctype_digit($co_pay_val[$i])){
						//check if it has only 2 decimal places
						$data=explode('.',$co_pay_val[$i]);
						if ( count($data) != 2 ){
							$co_pay_val[$i]=html("$co_pay_val[$i]");
							$message="bad# Unable to add new corprate as $co_pay_val[$i] is not a valid number ";
							$exit_flag=true;
							break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$co_pay_val[$i]=html("$co_pay_val[$i]");
							$message="bad# Unable to add new corprate as $co_pay_val[$i] is not a valid number ";
							$exit_flag=true;
							break;
						}
					}





					if(isset($ins_id[$i]) and $ins_id[$i]!=''){
						//decrypt insurance compnay id and check that exist
						//echo "$ins_id[$i]--".$encrypt->decrypt("$ins_id[$i]");
						$ins_id[$i]=$encrypt->decrypt("$ins_id[$i]");
						//echo "xxxx--$ins_id[$i]";
						$sql=$error=$s='';$placeholders=array();
						$sql="select id from insurance_company where id=:id";
						$error="Unable to check if insurance company exists";
						$placeholders[':id']=$ins_id[$i];
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						if((0 + $s->rowCount()) ==  0){
									$message="bad# Unable to add new corprate, this error has been logged";
									//call function to log this activity
									$message1="an update of $ins_id[$i] was attemped into covered_company table for column insurer_id";
									log_security($pdo,$message1);
									$exit_flag=true;
									break;
						}
					}

					//check if similar record already exixts
					$sql=$error=$s='';$placeholders=array();
					$sql="select name from covered_company where upper(name)=:name";
					$error="Unable to edit insured companies";
					$placeholders[':name']=strtoupper("$comp_name");
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					if($s->rowCount() > 0){
									$message="bad# Unable to add new corprate, as $comp_name already exists";
									$exit_flag=true;
									break;
					}

					//set insurer to 0 if the patient type is not insured
				//	if($insured_yes_no[$i]=='NO'){$ins_id[$i]=0;}

					//now insert new company
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into covered_company set  name=:name, insurer_id=:ins_id, 	co_pay_type=:co_pay_type ,	value=:value ,	pre_auth_needed=:pre_auth,
						smart_needed=:smart_needed, 	start_cover=:start_cover, 	end_cover=:end_cover, 	cover_type=:cover_type,
						cover_limit=:cover_limit, insured=:insured_yes_no ";
					$error="Unable to edit insured companies";
					$placeholders[':ins_id']=$ins_id[$i];
					$placeholders[':insured_yes_no']="$insured_yes_no[$i]";
					$placeholders[':co_pay_type']="$co_pay_type[$i]";
					$placeholders[':value']="$co_pay_val[$i]";
					$placeholders[':pre_auth']="$pre_auth_needed[$i]";
					$placeholders[':smart_needed']="$smart_needed[$i]";
					$placeholders[':start_cover']="$start_cover[$i]";
					$placeholders[':end_cover']="$end_cover[$i]";
					$placeholders[':cover_limit']="$cover_limit[$i]";
					$placeholders[':cover_type']="$cover_type[$i]";
					$placeholders[':name']="$comp_name";
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					if(!$s ){break;$error="Unable to add new employer";}
					$i++;
			}


			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			if($tx_result){$message="good# New corprate $comp_name has been added  ";}
			//elseif(!$tx_result){$error_message="   Unable to edit Insured Companies  ";}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="   Unable to add new corprate   ";
	}
	get_covered_company($pdo);
	echo "$message";
}



//edit CADCAM REFERERS
elseif( isset($_SESSION['token_cadref_2']) and isset($_POST['token_cadref_2'])
	and $_SESSION['token_cadref_2']==$_POST['token_cadref_2'] and userHasRole($pdo,76)){
	//$_SESSION['token2']='';
	//save entries
	$n=count($_POST['ninye']);
	$tech_id=$_POST['ninye'];
	$tech_name=$_POST['old_tech'];
	$tech_email=$_POST['old_email'];
	$tech_tel=$_POST['old_tel'];
	$i=0;
	$exit_flag=false;
	try{
		$pdo->beginTransaction();
			while($i < $n){
				if($tech_name[$i]==''){
					$exit_flag=true;
					$message="bad#Referrer name must be specified for all entries";
					break;
				}
				//check email format
				$email_address=html("$tech_email[$i]");
				if(!$exit_flag and $email_address!=''){
					if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
						$message="bad#Unable to save details as the email $email_address  is not correctly specified. ";
						$exit_flag=true;
						break;
					}
				}


					$sql=$error=$s='';$placeholders=array();
					$sql="update cadcam_referrer set name=:name , telephone=:tel, email_address=:email where id=:id";
					$error="Unable to edit cadcam referrer";
					$placeholders[':name']="$tech_name[$i]";
					$placeholders[':tel']="$tech_tel[$i]";
					$placeholders[':email']="$tech_email[$i]";
					$placeholders[':id']=$encrypt->decrypt($tech_id[$i]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					if(!$s and $exit_flag){$exit_flag=false;}
					$i++;
			}

				//now unlist entries
			if(!$exit_flag and isset($_POST['del'])){
				$n=count($_POST['del']);
				$tech_id=$_POST['del'];
				$i=0;
				while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update  cadcam_referrer set listed=1  where id=:id";
						$error="Unable to unlist cadcam referer";
						$placeholders[':id']=$encrypt->decrypt($tech_id[$i]);
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);
						if(!$s and $exit_flag){$exit_flag=false;}
						$i++;
				}
			}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$message="good#Changes saved  ";}
			//elseif(!$tx_result){}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$message="bad#Unable to edit Lab Technicians  ";
	}
		echo "$message";
}

//edit lab technicinas
elseif( isset($_SESSION['token_technician_2']) and isset($_POST['token_technician_2'])
	and $_SESSION['token_technician_2']==$_POST['token_technician_2'] and userHasRole($pdo,26)){
	//$_SESSION['token2']='';
	//save entries
	$n=count($_POST['ninye']);
	$tech_id=$_POST['ninye'];
	$tech_name=$_POST['old_tech'];
	$tech_email=$_POST['old_email'];
	$tech_tel=$_POST['old_tel'];
	$i=0;
	$exit_flag=false;
	try{
		$pdo->beginTransaction();
			while($i < $n){
				if($tech_name[$i]==''){
					$exit_flag=true;
					$message="bad#Technician name must be specified for all entries";
					break;
				}
				//check email format
				$email_address=html("$tech_email[$i]");
				if(!$exit_flag and $email_address!=''){
					if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
						$message="bad#Unable to save details as the email $email_address  is not correctly specified. ";
						$exit_flag=true;
						break;
					}
				}


					$sql=$error=$s='';$placeholders=array();
					$sql="update lab_technicians set technician_name=:name , telephone=:tel, email_address=:email where id=:id";
					$error="Unable to edit lab technicians";
					$placeholders[':name']="$tech_name[$i]";
					$placeholders[':tel']="$tech_tel[$i]";
					$placeholders[':email']="$tech_email[$i]";
					$placeholders[':id']=$encrypt->decrypt($tech_id[$i]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					if(!$s and $exit_flag){$exit_flag=false;}
					$i++;
			}

				//now delete entries
			if(!$exit_flag and isset($_POST['del'])){
				$n=count($_POST['del']);
				$tech_id=$_POST['del'];
				$i=0;
				while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update lab_technicians set listed=1  where id=:id";
						$error="Unable to unlist lab technician";
						$placeholders[':id']=$encrypt->decrypt($tech_id[$i]);
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);
						if(!$s and $exit_flag){$exit_flag=false;}
						$i++;
				}
			}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$message="good#Lab Technicians Edited  ";}
			//elseif(!$tx_result){}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$message="bad#Unable to edit Lab Technicians  ";
	}
		echo "$message";
}

//get drug selling price
elseif(isset($_POST['drug_id'])  and $_POST['drug_id']!='' and userHasRole($pdo,20)){
		$drug_id=$encrypt->decrypt("$_POST[drug_id]");
	//	echo "uu $drug_id --  $_POST[drug_id]";
		$sql=$error=$s='';$placeholders=array();
		$sql="select selling_price from drugs where id=:id";
		$error="Unable to get drug selling price";
		$placeholders[':id']=$drug_id;
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$row['selling_price']=html($row['selling_price']);
			echo "<span class=white_span>@".number_format($row['selling_price'],2)."</span>";
		}
}

//get drug stock availlable
elseif(isset($_POST['drug_id_quantity'])  and $_POST['drug_id_quantity']!='' and userHasRole($pdo,20)){
		$drug_id=$encrypt->decrypt("$_POST[drug_id_quantity]");
	//	echo "uu $drug_id --  $_POST[drug_id]";
		$sql=$error=$s='';$placeholders=array();
		$sql="select quantity from drugs where id=:id";
		$error="Unable to get drug quantity";
		$placeholders[':id']=$drug_id;
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$row['quantity']=html($row['quantity']);
			if($row['quantity']==0){$string="<span class=red_span>Not in stock</span>";}
			else{$string = "<span class=white_span>".number_format($row['quantity']). " in stock</span>";}
			echo "$string";

		}
}

/*
//check if timer has expired
elseif(isset($_POST['timer_expire'])  and $_POST['timer_expire']!='' ){
		if(!isset($_SESSION['LAST_ACTIVITY'])){echo "unset";}
		else{echo "set";}
}*/

//add cadcam referrer
elseif(isset($_SESSION['token_cadref_1']) and isset($_POST['token_cadref_1'])  and
 $_SESSION['token_cadref_1']==$_POST['token_cadref_1'] and userHasRole($pdo,76)){
			//$_SESSION['token']='';
	$exit_flag=false;
	if(!isset($_POST['tech_name']) or $_POST['tech_name']==''){
		$exit_flag=true;
		$message="bad#Referrer name must be specified";
	}
	//check email format
	$email_address=html($_POST['email_address']);
	if(!$exit_flag and isset($_POST['email_address']) and $_POST['email_address']!=''){
		if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
			$message="bad#Unable to save details as the email $email_address  is not correctly specified. ";
			$exit_flag=true;
		}
	}


	//empty the unset ones
	if(!isset($_POST['email_address']))  {$_POST['email_address']='';}
	if(!isset($_POST['telephone_no'])) {$_POST['telephone_no']='';}

	//check thata the referrer is not entered twice
	if(!$exit_flag){
		$sql=$error=$s='';$placeholders=array();
		$sql="select name from cadcam_referrer where upper(name)=:name";
		$error="Unable to get cadcam referrer name";
		$placeholders[':name']=strtoupper($_POST['tech_name']);
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount()>0){
			$name=html($_POST['tech_name']);
			$message="bad#Unable to add CADCAM referrer $name as that referrrer already exists";
		}
		else{
			//insert referrer value
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into cadcam_referrer set name=:name, telephone=:telephone, email_address=:email";
			$error="Unable to add cadcam referrer";
			$placeholders[':name']=$_POST['tech_name'];
			$placeholders[':telephone']=$_POST['telephone_no'];
			$placeholders[':email']=$_POST['email_address'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#add_cadcam_referrer#CADCAM referrer  added ";}
				elseif(!$s){$message="bad#Unable to add CADCAM referrer ";}
		}
	}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
	echo "$message";
}

//add lab technician
elseif(isset($_SESSION['token_technician_1']) and isset($_POST['token_technician_1'])  and
 $_SESSION['token_technician_1']==$_POST['token_technician_1'] and userHasRole($pdo,26)){
			//$_SESSION['token']='';
	$exit_flag=false;
	if(!isset($_POST['tech_name']) or $_POST['tech_name']==''){
		$exit_flag=true;
		$message="bad#Technician name must be specified";
	}
	//check email format
	$email_address=html($_POST['email_address']);
	if(!$exit_flag and isset($_POST['email_address']) and $_POST['email_address']!=''){
		if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
			$message="bad#Unable to save details as the email $email_address  is not correctly specified. ";
			$exit_flag=true;
		}
	}


	//empty the unset ones
	if(!isset($_POST['email_address']))  {$_POST['email_address']='';}
	if(!isset($_POST['telephone_no'])) {$_POST['telephone_no']='';}

	//check thata the technician is not entered twice
	if(!$exit_flag){
		$sql=$error=$s='';$placeholders=array();
		$sql="select technician_name from lab_technicians where upper(technician_name)=:name";
		$error="Unable to get technician name";
		$placeholders[':name']=strtoupper($_POST['tech_name']);
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount()>0){
			$name=html($_POST['tech_name']);
			$message="bad#Unable to add lab techician $name as that name already exists";
		}
		else{
			//insert lab technician value
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into lab_technicians set technician_name=:name, telephone=:telephone, email_address=:email";
			$error="Unable to add lab technician";
			$placeholders[':name']=$_POST['tech_name'];
			$placeholders[':telephone']=$_POST['telephone_no'];
			$placeholders[':email']=$_POST['email_address'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#add_technician#Lab Technician  added ";}
				elseif(!$s){$message="bad#Unable to add Lab Technician ";}
		}
	}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
	echo "$message";
}

//edit xray referrer
elseif( isset($_SESSION['token_xray_ref_2']) and isset($_POST['token_xray_ref_2'])
	and $_SESSION['token_xray_ref_2']==$_POST['token_xray_ref_2'] and userHasRole($pdo,27)){
	//$_SESSION['token2']='';
	//save entries
	$n=count($_POST['ninye']);
	$ref_id=$_POST['ninye'];
	$ref_name=$_POST['ref_name'];
	$ref_email=$_POST['email_address'];
	$ref_tel=$_POST['telephone_no'];
	$i=0;
	$exit_flag=false;
	try{
		$pdo->beginTransaction();

				if($ref_name==''){
					$exit_flag=true;
					$message="bad#Refferer name must be specified";

				}
				//check email format
				$email_address=html("$ref_email");
				if(!$exit_flag and $email_address!=''){
					if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
						$message="bad#Unable to save details as the email $email_address  is not correctly specified. ";
						$exit_flag=true;

					}
				}

				if(!$exit_flag ){
					$sql=$error=$s='';$placeholders=array();
					$sql="update xray_refering_doc set referrer_name=:name , telephone=:tel, email_address=:email where id=:id";
					$error="Unable to edit xray referrers";
					$placeholders[':name']="$ref_name";
					$placeholders[':tel']="$ref_tel";
					$placeholders[':email']="$ref_email";
					$placeholders[':id']=$encrypt->decrypt("$ref_id");
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					if(!$s and $exit_flag){$exit_flag=false;}
				}


				//now unlist entries
			if(!$exit_flag and isset($_POST['del'])){
				$ref_id=$_POST['del'];


						$sql=$error=$s='';$placeholders=array();
						$sql="update xray_refering_doc set listed=1 where id=:id";
						$error="Unable to unlist xray referrers";
						$placeholders[':id']=$encrypt->decrypt("$ref_id");
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);
						if(!$s and $exit_flag){$exit_flag=false;}


			}
							//now list entries
			if(!$exit_flag and !isset($_POST['del'])){

						$sql=$error=$s='';$placeholders=array();
						$sql="update xray_refering_doc set listed=0 where id=:id";
						$error="Unable to unlist xray referrers";
						$placeholders[':id']=$encrypt->decrypt("$ref_id");
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);
						if(!$s and $exit_flag){$exit_flag=false;}


			}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$message="good#X-ray referrers Edited  ";}
			//elseif(!$tx_result){}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$message="bad#Unable to edit Lab Technicians  ";
	}
		//$_SESSION['result_class']=$_SESSION['result_message']='bad';
		echo "$message";
}

//this is for discharging a pt from waiting list
elseif(isset($_POST['token_allocate8']) and isset($_SESSION['token_allocate8']) and $_POST['token_allocate8']==$_SESSION['token_allocate8']
	and userHasRole($pdo,48)){
			//dischrage atreatment
			if(isset($_POST['discharge_patient']) and  $_POST['discharge_patient']!=''){
				$allocation_id=$encrypt->decrypt($_POST['discharge_patient']);
				$sql=$error=$s='';$placeholders=array();
				$sql="update patient_allocations set  discharge_time=now() where id=:allocation_id";
				$error="Unable to discharge patient";
				$placeholders[':allocation_id']=$allocation_id;
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);

				if($s){$message='good#patient_allocation#Patient discharged';}
				elseif(!$s){$message='bad#Unable to discharge patient';}
				$_SESSION['result_class']='success_response';
				$_SESSION['result_message']="Patient discharged";
				echo "$message";
			}

}

//this is for going to patient contacts from allocations
elseif(isset($_POST['goto_pt_contact']) and  $_POST['goto_pt_contact']!='' and userHasRole($pdo,48)){
			//get pid
			$_SESSION['tplan_id']='';
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid from patient_details_a where patient_number=:patient_number";
				$error="Unable to get pid";
				$placeholders[':patient_number']=$encrypt->decrypt($_POST['goto_pt_contact']);
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$_SESSION['pid']=html($row['pid']);
					get_patient($pdo,'pid',$_SESSION['pid']);
					echo 'good#patient_contacts';
				}

}

//this is for going to patient contacts from surgery reports
elseif(isset($_POST['goto_pt_contact2']) and  $_POST['goto_pt_contact2']!='' and userHasRole($pdo,104)){
			//get pid
			$_SESSION['tplan_id']='';
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid from patient_details_a where patient_number=:patient_number";
				$error="Unable to get pid";
				$placeholders[':patient_number']=$_POST['goto_pt_contact2'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$_SESSION['pid']=html($row['pid']);
					get_patient($pdo,'pid',$_SESSION['pid']);
					echo 'good#patient_contacts';
				}

}

//this is for going to tdone from surgery repots
elseif(isset($_POST['goto_tdone2']) and  $_POST['goto_tdone2']!='' and userHasRole($pdo,104)){
			//get pid
			$_SESSION['tplan_id']='';
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid from patient_details_a where patient_number=:patient_number";
				$error="Unable to get pid";
				$placeholders[':patient_number']=$_POST['goto_tdone2'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$_SESSION['pid']=html($row['pid']);
					get_patient($pdo,'pid',$_SESSION['pid']);
					echo 'good#treatment_done';
				}

}

//this is for going to tdone from allocations
elseif(isset($_POST['goto_tdone']) and  $_POST['goto_tdone']!='' and userHasRole($pdo,48)){
			//get pid
			$_SESSION['tplan_id']='';
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid from patient_details_a where patient_number=:patient_number";
				$error="Unable to get pid";
				$placeholders[':patient_number']=$encrypt->decrypt($_POST['goto_tdone']);
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$_SESSION['pid']=html($row['pid']);
					get_patient($pdo,'pid',$_SESSION['pid']);
					echo 'good#treatment_done';
				}

}

//this is for starting a treatment for waiting list
elseif(isset($_POST['token_allocate4']) and isset($_SESSION['token_allocate4']) and $_POST['token_allocate4']==$_SESSION['token_allocate4']
	and userHasRole($pdo,48)){
			//start a atreatment
			$_SESSION['tplan_id']='';
			if(isset($_POST['start_treatment']) and  $_POST['start_treatment']!=''){
				$allocation_id=$encrypt->decrypt($_POST['start_treatment']);
				$sql=$error=$s='';$placeholders=array();
				$sql="update patient_allocations set time_start_treatment=now(), treatment_status=1 where id=:allocation_id";
				$error="Unable to start waiting list treatment";
				$placeholders[':allocation_id']=$allocation_id;
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);

				if($s){$message='good#go_to_examination';}
				elseif(!$s){$message='bad#Unable to update waiting list';}

				//get pid since we will go on examination tab
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid,patient_type from  patient_allocations where  id=:allocation_id";
				$error="Unable to get patient id to start waiting list treatment";
				$placeholders[':allocation_id']=$allocation_id;
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() == 1){
					foreach($s as $row){
						//set allocation id in session to be used as shortcut in tdone
						$_SESSION['alloc_id_short_cut']=html("$_POST[start_treatment]");

						//update appointments for registerd patiends
						if($row['patient_type']==1){
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="update registered_patient_appointments  set status='SEEN' where pid=:pid and appointment_date=curdate()";
							$error2="Unable to update apointment status";
							$placeholders2[':pid']=$row['pid'];
							$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

							$_SESSION['pid']=$row['pid'];
							$_SESSION['get_tab_id']=6;//this is where the on examination tab is located
							get_patient($pdo,"pid",$_SESSION['pid']);
						}

						//update appointments for unregistersd
						elseif($row['patient_type']==0){
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="update unregistered_patient_appointments  set status='SEEN' where pid=:pid and appointment_date=curdate()";
							$error2="Unable to update apointment status";
							$placeholders2[':pid']=$row['pid'];
							$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
							$_SESSION['pid']='';
						}

					}
				}
				else{$_SESSION['pid']='';}
				echo "$message";
			}

			//suspend or put a treatment on hold
			if(isset($_POST['treatment_status']) and  $_POST['treatment_status']!='' and
				isset($_POST['hold_finish']) and  $_POST['hold_finish']!='' ){
				$allocation_id=$encrypt->decrypt($_POST['treatment_status']);
				//transfer pt to another surgery
				if($_POST['hold_finish']=='transfer' ){
					if($_POST['transfer_list']==''){$message='bad#Please select the surgery to transfer the patient to.';}
					else{
						$exit_flag=false;
						try{
							$pdo->beginTransaction();
							//get allocation details
							$sql=$error=$s='';$placeholders=array();
							$sql="select pid, patient_type from patient_allocations where id=:allocation_id";
							$error="Unable to get allocation details ";
							$placeholders[':allocation_id']=$allocation_id;
							$s = 	select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){
								$pid=html($row['pid']);
								$patient_type=html($row['patient_type']);
							}

							//discharge the patient
							$sql=$error=$s='';$placeholders=array();
							$sql="update patient_allocations set treatment_finish=now(), treatment_status=3 , discharge_time=now() where id=:allocation_id";
							$error="Unable to edit treatment status in waiting list ";
							$placeholders[':allocation_id']=$allocation_id;
							$s = 	insert_sql($sql, $placeholders, $error, $pdo);

							//perform insert
							//get points per minute
							$points_per_minute='';
							$sql=$error=$s='';$placeholders=array();
							$sql="select points from points_per_time";
							$error="Unable to get points per time";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){
								$points_per_minute=html($row['points']);
							}
							//check if points per minutre has a value
							if(!$exit_flag and $points_per_minute==''){
								$exit_flag=true;
								$message="bad#Unable to get points per minute";
							}
							if(!$exit_flag){
								$sql=$error=$s='';$placeholders=array();
								$sql="insert into patient_allocations set surgery_id=:surgery_id, pid=:pid, time_allocated=current_timestamp(),
									patient_type=:patient_type,points_per_min=:points_per_min,previous_allocation=:previous_allocation";
								$error="Unable to get referrer name";
								$placeholders[':surgery_id']=$encrypt->decrypt($_POST['transfer_list']);
								$placeholders[':points_per_min']=$points_per_minute;
								$placeholders[':pid']=$pid;
								$placeholders['patient_type']=$patient_type;
								$placeholders['previous_allocation']=$allocation_id;
								$s = 	select_sql($sql, $placeholders, $error, $pdo);
								if($s){

									//calculate balance if not set before
									if($patient_type==1){
										$pid_bal="pid_$pid";
										if(!isset($_SESSION["$pid_bal"])){
											$_SESSION["$pid_bal"]=array();
											$enc_pid=$encrypt->encrypt("$pid");
											$result=show_pt_statement_brief($pdo,$enc_pid,$encrypt);
											$data=explode('#',"$result");
											$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
										}
									}

								}
								elseif(!$s){$message='bad#Unable to allocate patient';}
							}


							if($s){$tx_result = $pdo->commit();}
							elseif(!$s){$pdo->rollBack();$tx_result=false;}
							if($tx_result){$message='good#patient_allocation#Patient allocated';}
						}
						catch (PDOException $e)
						{
						$pdo->rollBack();
						//$message="bad#Unable to edit Lab Technicians  ";
						}
					}
				}

				#finish or suspend treattment
				else{
					$sql=$error=$s='';$placeholders=array();
					if($_POST['hold_finish']=='hold'){$sql="update patient_allocations set pause_treatment=now(), treatment_status=2 where id=:allocation_id";}
					elseif($_POST['hold_finish']=='finish'){$sql="update patient_allocations set treatment_finish=now(), treatment_status=3 where id=:allocation_id";}
					$error="Unable to edit treatment status in waiting list ";
					$placeholders[':allocation_id']=$allocation_id;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);

					if($s){$message='good#patient_allocation#Waiting list updated';}
					elseif(!$s){$message='bad#Unable to update waiting list';}
				}
				$data=explode('#',"$message");
				if($data[0]=='bad'){$_SESSION['result_class']='bad';
									$_SESSION['result_message']="$data[1]";
				}
				if($data[0]=='good'){$_SESSION['result_class']='success_response';
									$_SESSION['result_message']="$data[2]";
				}
				echo "$message";
			}

			//resume a atreatment
			if(isset($_POST['resume_treatment']) and  $_POST['resume_treatment']!=''){
				$allocation_id=$encrypt->decrypt($_POST['resume_treatment']);
				$sql=$error=$s='';$placeholders=array();
				$sql="update patient_allocations set resume_treatment=now(), treatment_status=1 where id=:allocation_id";
				$error="Unable to resume waiting list treatment";
				$placeholders[':allocation_id']=$allocation_id;
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);

				if($s){$message='good#go_to_examination';}
				elseif(!$s){$message='bad#Unable to resume waiting list treatment';}

				//get pid since we will go on examination tab
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid from  patient_allocations where patient_type=1 and id=:allocation_id";
				$error="Unable to get patient id to start waiting list treatment";
				$placeholders[':allocation_id']=$allocation_id;
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() == 1){
					foreach($s as $row){
						$_SESSION['pid']=$row['pid'];
						$_SESSION['get_tab_id']=6;//this is where the on examination tab is located
						get_patient($pdo,"pid",$_SESSION['pid']);
					}
				}
				else{$_SESSION['pid']='';}
				echo "$message";
			}

}


//this is for adding patient to waitng list
elseif(isset($_POST['token_allocate3']) and isset($_SESSION['token_allocate3']) and $_POST['token_allocate3']==$_SESSION['token_allocate3']
	and userHasRole($pdo,48)){
	//print_r($_POST);
	$exit_flag=false;
			//check edit type is set
			if(!$exit_flag and (!isset($_POST['edit_type']) or $_POST['edit_type']=='')){
				$exit_flag=true;
				$message="bad#Please select the action type to perform.";
			}
			//check if patient is set
			if(!$exit_flag and (!isset($_POST['allocated_patient']) or $_POST['allocated_patient']=='')){
				$exit_flag=true;
				$message="bad#Please select the patient to edit.";
			}
			//check surgery is set
			if(!$exit_flag and $_POST['edit_type']=='change_surgery' and (!isset($_POST['allocate_surgery']) or $_POST['allocate_surgery']=='')){
				$exit_flag=true;
				$message="bad#Please select the surgery to allocate the patient to.";
			}
			//now perform update action
			if(!$exit_flag){
				$criteria=$_POST['edit_type'];
				$sql=$error=$s='';$placeholders=array();
				if($criteria=="patient_left"){
					$sql="update patient_allocations set patient_left=1, discharge_time=now() where id=:id";
					$placeholders[':id']=$encrypt->decrypt($_POST['allocated_patient']);
				}
				elseif($criteria=="change_surgery"  ){
					$sql="update patient_allocations set surgery_id=:surgery_id where id=:id";
					$placeholders[':id']=$encrypt->decrypt($_POST['allocated_patient']);
					$placeholders[':surgery_id']=$encrypt->decrypt($_POST['allocate_surgery']);
				}
				elseif($criteria=="remove_patient"  ){
					$sql="delete from  patient_allocations  where id=:id";
					$placeholders[':id']=$encrypt->decrypt($_POST['allocated_patient']);
				}
				//elseif($criteria=="pid"){$sql="select * from patient_details_a where pid=:patient_number";}

				$error="Error: Unable to edit waiting list";
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);
				if($s){$message='good#patient_allocation#Waiting list updated';}
				elseif(!$s){$message='bad#Unable to update waiting list';}
				$data=explode('#',"$message");
				if($data[0]=='bad'){$_SESSION['result_class']='bad';
									$_SESSION['result_message']="$data[1]";
				}
				if($data[0]=='good'){$_SESSION['result_class']='success_response';
									$_SESSION['result_message']="$data[2]";
				}
			}
			echo $message;
}

//this is for adding patient to waitng list
elseif(isset($_POST['token_allocate1']) and isset($_SESSION['token_allocate1']) and $_POST['token_allocate1']==$_SESSION['token_allocate1']
	and userHasRole($pdo,48)){

	//print_r($_POST);
	$exit_flag=false;
	$tplan_visit_id=0;
	$result='';
			//check if selectefd  patient is set
			if(!$exit_flag and  isset($_POST['selected_patient']) and $_POST['selected_patient']!=''){
				$searched_patient_pid=$encrypt->decrypt($_POST['selected_patient']);
				$result=1;
				//echo "kk";
			}
			//check surgery is set
			if(!$exit_flag and (!isset($_POST['allocate_surgery']) or $_POST['allocate_surgery']=='')){
				$exit_flag=true;
				$message="bad#Please select the surgery to allocate the patient to.";
			}
			//check patient type is set
			if(!$exit_flag and $_POST['patient_type']=='' and $_POST['patient_type']!='registered' and $_POST['patient_type']!='unregistered'){
				$exit_flag=true;
				$message="bad#Please select the patient type";
			}
			if(!$exit_flag and $_POST['patient_type']=='registered' and (!isset($_POST['selected_patient']) or $_POST['selected_patient']=='')){
				$result  = check_if_patient_exists($_POST['search_by'], $_POST['search_ciretia'],$pdo,$encrypt);
				$data = explode('#',$result);
				$result=$data[0];
				if(isset($data[1])){$searched_patient_pid=$data[1];}
			}

			//check if the registered patient has been swapped
			if(!$exit_flag and isset($searched_patient_pid) and $searched_patient_pid!=''){
				$resultx = check_if_swapped($pdo,'pid',$searched_patient_pid);
				if($resultx!='good'){
					$exit_flag=true;
					$message="bad#$resultx and cannot be edited.";
				}
			}

			//check if unregisterd is selected and patient selected
			if(!$exit_flag and  $_POST['patient_type']=='unregistered' and isset($_POST['unregistered_patient']) and $_POST['unregistered_patient']==''){
				$exit_flag=true;
				$message="bad#Please select the unregistered patient to allocate";
			}
			if(!$exit_flag and $_POST['patient_type']=='unregistered' and $_POST['unregistered_patient']!=''){
				$result=1;
			}
	if(!$exit_flag){
		//if one patient is found then do submit
		if(!$exit_flag and  $result == 1){
			//check if this patient is already allocated and has not been discharged or has not left
			if(!$exit_flag and $_POST['patient_type']=='registered'){
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid from patient_allocations where pid=:pid and patient_type=1 and date(time_allocated)=curdate() and
						date(discharge_time)='0000-00-00'";
				$error="Unable to check if patient is already allocated";
				$placeholders[':pid']=$searched_patient_pid;
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() > 0){
					$exit_flag=true;
					$message="bad#That  patient has already been allocated.";
				}

				//check if the pt has a tplan with pending appointments
				if(!$exit_flag and $_POST['patient_type']=='registered'){
					$sql=$error=$s='';$placeholders=array();
					$sql="select id from tplan_visits where pid=:pid and visits_remaining >0 order by id asc limit 1 ";
					$error="Unable to check if patient has pending tplan appointments";
					$placeholders[':pid']=$searched_patient_pid;
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$tplan_visit_id=html($row['id']);}
				}
			}

			//check if this patient is already allocated and has not been discharged or has not left
			if(!$exit_flag and $_POST['patient_type']=='unregistered'){
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid from patient_allocations where pid=:pid and patient_type=0 and date(time_allocated)=curdate() and
						date(discharge_time)='0000-00-00'";
				$error="Unable to check if patient is already allocated";
				$placeholders[':pid']=$encrypt->decrypt($_POST['unregistered_patient']);
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() > 0){
					$exit_flag=true;
					$message="bad#That  patient has already been allocated.";
				}
			}
			//perform insert
			if(!$exit_flag){

				//now insert

					//get points per minute
					$points_per_minute='';
					$sql=$error=$s='';$placeholders=array();
					$sql="select points from points_per_time";
					$error="Unable to get points per time";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$points_per_minute=html($row['points']);
					}
					//check if points per minutre has a value
					if(!$exit_flag and $points_per_minute==''){
						$exit_flag=true;
						$message="bad#Unable to get points per minute";
					}
					if(!$exit_flag){
						try{
							$pdo->beginTransaction();
							//deduct tplan visist
							$visits='';
							if($tplan_visit_id > 0){
								$sql=$error=$s='';$placeholders=array();
								$sql="update tplan_visits set visits_remaining = (visits_remaining -1) where id=:id";
								$placeholders[':id']=$tplan_visit_id;
								$error="Unable to update tplan visits";
								$s = 	insert_sql($sql, $placeholders, $error, $pdo);

								//check if the pt has any pending planned visits
								$sql2=$error2=$s2='';$placeholders2=array();
								$sql2="select  coalesce(sum(visits_planned),0) as visits_planeed, coalesce(sum(visits_remaining),0) as visits_remaining from tplan_visits where pid=:pid and visits_remaining >0";
								$error2="Unable to pending visits";
								$placeholders2[':pid']=$searched_patient_pid;
								$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
								foreach($s2 as $row2){
									if($row2['visits_planeed']>0 and $row2['visits_remaining']>0){
										$var=$row2['visits_planeed'] - $row2['visits_remaining'] ;
										$visits=html("Visit $var of $row2[visits_planeed]");

									}
								}
							}

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into patient_allocations set surgery_id=:surgery_id, pid=:pid, time_allocated=current_timestamp(),
								patient_type=:patient_type,		 points_per_min=:points_per_min,tplan_visit_id=:tplan_visit_id,visit=:visit,added_by=:added_by, expedite=:expedite";
							$error="Unable to get referrer name";
							$placeholders[':surgery_id']=$encrypt->decrypt($_POST['allocate_surgery']);
							$placeholders[':points_per_min']=$points_per_minute;
							$placeholders[':tplan_visit_id']=$tplan_visit_id;
							$placeholders[':visit']="$visits";

							$placeholders[':added_by']=$_SESSION['id'];

							//check pid
							if($_POST['patient_type']=='registered'){
								$placeholders[':pid']=$searched_patient_pid;
								$placeholders[':expedite']=$_POST['expedite_reason_registered'];
							}
							elseif($_POST['patient_type']=='unregistered'){
								$placeholders[':pid']=$encrypt->decrypt($_POST['unregistered_patient']);
								$placeholders[':expedite']=$_POST['expedite_reason_unregistered'];
							}
							//check patient type
							if($_POST['patient_type']=='registered'){$placeholders[':patient_type']=1;}
							elseif($_POST['patient_type']=='unregistered'){$placeholders[':patient_type']=0;}
							$s = 	select_sql($sql, $placeholders, $error, $pdo);
							if($s){
								$message='good#patient_allocation#Patient allocated';
								//calculate balance if not set before
								if(isset($searched_patient_pid) and $searched_patient_pid!=''){
									$pid_bal="pid_$searched_patient_pid";
									if(!isset($_SESSION["$pid_bal"])){
										$_SESSION["$pid_bal"]=array();
										$enc_pid=$encrypt->encrypt("$searched_patient_pid");
										$result=show_pt_statement_brief($pdo,$enc_pid,$encrypt);
										$data=explode('#',"$result");
										$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
									}
								}

							}
							$tx_result = $pdo->commit();
						}
						catch (PDOException $e)
						{
						$pdo->rollBack();
						//$message="bad#Unable to save patient disease details  ";
						}
						if(!$tx_result){$message='bad#Unable to allocate patient';}
					}

			}
		}
		elseif(!$exit_flag and $result == 2){$message= "bad#No such patient";}
		else{
			$message="bad#Please specify the patient to allocate";
		}
	}		$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo $message;

}

//this is for setting cash pledge only
elseif(isset($_POST['token_cash_plege']) and isset($_SESSION['token_cash_plege']) and $_POST['token_cash_plege']==$_SESSION['token_cash_plege']
	and userHasRole($pdo,50)){
	$exit_flag=false;
	if(!isset($_POST['date_clear_bal']) or $_POST['date_clear_bal']==''){
				$exit_flag=true;
				$message="bad#Please when the balance will be cleared.";
			}


				//check if pid is set
				if(!$exit_flag and (!isset($_POST['token_ninye']) or $_POST['token_ninye']=='')){
					$exit_flag=true;
					$message="bad#Please retry the transaction or contact support.";
				}

				//check if balance is set
				if(!$exit_flag and (!isset($_POST['token_ninye2']) or $_POST['token_ninye2']=='')){
					$exit_flag=true;
					$message="bad#Please retry the transaction or contact support.";
				}

				//check if amount is avlaid number
				if(!$exit_flag){
					//remove commas
					$bal=$encrypt->decrypt("$_POST[token_ninye2]");
					$amount=str_replace(",", "", $bal);
					if(!ctype_digit($amount)){
						//check if it has only 2 decimal places
						$data=explode('.',$amount);
						$invalid_value=html($amount);
						if ( count($data) != 2 ){

						$message="bad#Please retry the transaction or contact support.";
						$exit_flag=true;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#Please retry the transaction or contact support.";
						$exit_flag=true;
						}
					}
				}

			if(!$exit_flag){
				try{
				$pdo->beginTransaction();
					//get old balabce record if any
					$pid=$encrypt->decrypt("$_POST[token_ninye]");
					$sql=$error=$s='';$placeholders=array();
					//$sql="delete from  balance_clearance_date  where pid=:pid";
					$sql="select when_added, pid, date_to_clear, added_by , balance, comments from  balance_clearance_date  where pid=:pid";
					$error="Unable to get old pleges";
					$placeholders[':pid']=$pid;
					$s = select_sql($sql, $placeholders, $error, $pdo);

					//insert that into comments table
					foreach($s as $row){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into cash_pledge_comments  set pid=:pid,when_added=:when_added,
							pledge_date=:pledge_date, added_by=:added_by, balance=:balance, comments=:comments";
						$error="Unable to record old balance pledge";
						$placeholders[':pid']=$row['pid'];
						$placeholders[':when_added']=$row['when_added'];
						$placeholders[':pledge_date']=$row['date_to_clear'];
						$placeholders[':added_by']=$row['added_by'];
						$placeholders[':balance']=$row['balance'];
						$placeholders[':comments']=$row['comments'];
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}

					//delete old balabce record if any
					$sql=$error=$s='';$placeholders=array();
					$sql="delete from  balance_clearance_date  where pid=:pid";
					$error="Unable to get old pleges";
					$placeholders[':pid']=$pid;
					$s = insert_sql($sql, $placeholders, $error, $pdo);

					$sql=$error=$s='';$placeholders=array();
					$sql="insert into balance_clearance_date  set when_added=now(),
						date_to_clear=:date_to_clear, pid=:pid, added_by=:added_by ,balance=:balance, comments=:comments";
					$error="Unable to record date balance will be cleared";
					$placeholders[':date_to_clear']=$_POST['date_clear_bal'];
					$placeholders[':pid']=$pid;
					$placeholders[':added_by']=$_SESSION['id'];
					$placeholders[':balance']=$amount;
					$placeholders[':comments']=$_POST['comment'];
					$s = insert_sql($sql, $placeholders, $error, $pdo);

					if($s){$tx_result = $pdo->commit();}
					elseif(!$s){$pdo->rollBack();$tx_result=false;}
					if($tx_result){$message="good#self_payment#Cash pledge has been set";}
				}
				catch (PDOException $e)
				{
				$pdo->rollBack();
				//$message="bad#Unable to edit Lab Technicians  ";
				}
			}
				$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){
			$_SESSION['result_class']='success_response';
			$_SESSION['result_message']="$data[2]";
		}
		echo $message;

}

//this is for submittintg none insured payments
elseif(isset($_POST['token_non_ins_pay']) and isset($_SESSION['token_non_ins_pay']) and $_POST['token_non_ins_pay']==$_SESSION['token_non_ins_pay']
	and userHasRole($pdo,50)){
		//check if token exists
		$sql=$error=$s='';$placeholders=array();
		$sql="select id from check_token where token_val=:token_val";
		$error="Unable to get token val";
		$placeholders['token_val']=$_POST['token_non_ins_pay'];
		$s = select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			//update count time
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into check_token set token_val=:token_val, when_added=now()";
			$error="Unable to add 2 token val";
			$placeholders['token_val']=$_POST['token_non_ins_pay'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			//echo "bad#token";

		}
		else{
		$exit_flag=false;
		//check fileds
			//check if amount is set
			if(!isset($_POST['amount']) or $_POST['amount']==''){
				$exit_flag=true;
				$message="bad#Please specify the amount paid.";
			}

			//check if pay type is set
			if(!$exit_flag and (!isset($_POST['non_ins_payment_type']) or $_POST['non_ins_payment_type']=='')){
				$exit_flag=true;
				$message="bad#Please specify the payment type.";
			}

			//check if amount is > 0
			if(!$exit_flag and $_POST['amount']==0){
				$exit_flag=true;
				$message="bad#The amount paid must be greater than zero!!!";
			}

			//check if amount is avlaid number
			if(!$exit_flag){
				//remove commas
				$amount=str_replace(",", "", $_POST["amount"]);
				if(!ctype_digit($amount)){
					//check if it has only 2 decimal places
					$data=explode('.',$amount);
					$invalid_value=html($amount);
					if ( count($data) != 2 ){

					$message="bad#Amount specified, $invalid_value is not a valid number. ";
					$exit_flag=true;
					}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
					$message="bad#Amount specified, $invalid_value is not a valid number. ";
					$exit_flag=true;
					}
				}
			}

			//check that payment type is set correctly
			if(!$exit_flag){
				$pay_type=$encrypt->decrypt($_POST['non_ins_payment_type']);
				//ensure pay type is valid option
				if(!$exit_flag and $pay_type != 2  and $pay_type != 3  and $pay_type != 4  and $pay_type != 5  and $pay_type != 6 and $pay_type != 10){
					$exit_flag=true;
					$message="bad#Please specify the pay type.";
				}
				//cheque_number
				if(!$exit_flag and $pay_type==3 and (!isset($_POST['cheque_number']) or $_POST['cheque_number']=='')){
					$exit_flag=true;
					$message="bad#Please specify the cheque number.";
				}

				//mpesa_number
				if(!$exit_flag and $pay_type==4 and (!isset($_POST['mpesa_number']) or $_POST['mpesa_number']=='')){
					$exit_flag=true;
					$message="bad#Please specify the Mpesa transaction number.";
				}

				//visa_number
				if(!$exit_flag and $pay_type==5 ){
					if(!isset($_POST['visa_number']) or $_POST['visa_number']==''){
						$exit_flag=true;
						$message="bad#Please specify the VISA transaction number.";
					}
					elseif(!$exit_flag and !isset($_POST['bank_name']) or $_POST['bank_name']==''){
						$exit_flag=true;
						$message="bad#Please specify the VISA Bank used for the transaction.";
					}
				}

				//waiver
				if(!$exit_flag and $pay_type==6 and (!isset($_POST['waiver_reason']) or $_POST['waiver_reason']=='')){
					$exit_flag=true;
					$message="bad#Please specify the reason for giving the payment waiver.";
				}

				//credit transfer
				if(!$exit_flag and $pay_type==10 and (!isset($_POST['cred_family_mem']) or $_POST['cred_family_mem']=='')){
					$exit_flag=true;
					$message="bad#Please specify the patient frmo whom you are transferring credit.";
				}
				if(!$exit_flag and $pay_type==10 and isset($_POST['cred_family_mem']) and $_POST['cred_family_mem']!=''){
					$data1=$encrypt->decrypt($_POST['cred_family_mem']);
					$data2=explode('#',"$data1");
					$data2[1]=str_replace("-", "", $data2[1]);
					if($data2[1] < $amount){
						$exit_flag=true;
						$message="bad#The credit amount transfered exceeds the donor's limit.";
					}

				}
				//check if the amount will clear the balance or not
				if(!$exit_flag and $pay_type==2 or $pay_type==3 or $pay_type==4 or $pay_type==5 or $pay_type==10 ){
					//now get patient self balance
					$result=show_pt_statement_brief($pdo,$_POST['token_ninye'],$encrypt);
					$result=str_replace(",", "", "$result");

					$data=explode('#',"$result");
					//if($data[1] == 0 or $data[1] < 0){echo "bad#no_balance";}
					if($data[1] > 0 and ($data[1] - $amount) > 0 and (!isset($_POST['date_clear_bal']) or $_POST['date_clear_bal']=='')){
						$exit_flag=true;
						$message="bad#date_clear_bal#Please specify when the remaining balance of KES: ".number_format($data[1] - $amount,2)." will be cleared ";
					}
				}
			}

		//now perform insert
		if(!$exit_flag){
			try{
				$pdo->beginTransaction();
				$id='';
				if($pay_type == 2 or $pay_type == 3  or $pay_type == 4  or $pay_type == 5  or
					($pay_type == 6 and userHasSubRole($pdo,6)) or $pay_type == 10){
					//subrole 6 is for approving waivers
					$receipt_number='';
					$rid=0;
					//first get receipt number for non insured payment
					$sql=$error=$s='';$placeholders=array();
					$sql="select max(receipt_num) from non_insurance_receipt_id_generator";
					$error="Unable to get non insured receipt number";
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$rid=$row[0] + 1;}
					if($rid == 0){$rid = 1;}

					$sql=$error=$s='';$placeholders=array();
					$sql="insert into non_insurance_receipt_id_generator set receipt_num =:rid";
					$error="Unable to get non insured receipt number";
					$placeholders[':rid']=$rid;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					$receipt_number="R$rid-".date('m/y');
					$receipt_num_id=$rid;

					//now that i have receipt number i can insert payment details
					if($receipt_number != ''){
						$pid=$encrypt->decrypt("$_POST[token_ninye]");
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into payments set when_added=now(), receipt_num=:receipt_num,
							amount=:amount,
							pay_type=:pay_type,
							pid=:pid,
							tx_number=:tx_number,
							receipt_num_id=:receipt_num_id,
							created_by=:created_by,
							bank_id=:bank_id";
						$error="Unable to make non-insured payment";
						$placeholders[':receipt_num']="$receipt_number";
						$placeholders[':amount']=$amount;
						$placeholders[':pay_type']=$pay_type;
						$placeholders[':pid']=$pid;
						$placeholders[':created_by']=$_SESSION['id'];
						if($pay_type==2 or $pay_type==6){$placeholders[':tx_number']='';$placeholders[':bank_id']=0;}
						elseif($pay_type==3){$placeholders[':tx_number']=$_POST['cheque_number'];$placeholders[':bank_id']=0;}
						elseif($pay_type==4){$placeholders[':tx_number']=$_POST['mpesa_number'];$placeholders[':bank_id']=0;}
						elseif($pay_type==5){
							$placeholders[':tx_number']=$_POST['visa_number'];
							$placeholders[':bank_id']=$encrypt->decrypt("$_POST[bank_name]");
						}
						elseif($pay_type==10){$placeholders[':tx_number']=$data2[0];$placeholders[':bank_id']=0;}
						$placeholders[':receipt_num_id']=$receipt_num_id ;
						$id = get_insert_id($sql, $placeholders, $error, $pdo);

						//now get patient self balance
						$result=show_pt_statement_brief($pdo,$_POST['token_ninye'],$encrypt);
						//echo "xx $result xx";
						$result=str_replace(",", "", "$result");

						$data=explode('#',"$result");
						if($data[1] == 0){
							//if balance is zero delete any balance clearance dates
							$sql=$error=$s='';$placeholders=array();
							$sql="delete from  balance_clearance_date  where pid=:pid";
							$error="Unable to clear date balance will be cleared";
							$placeholders[':pid']=$pid;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
							$bal="Cash balance is 0.00";

							$sql=$error=$s='';$placeholders=array();
							$sql="delete from  cash_pledge_comments  where pid=:pid";
							$error="Unable to clear date balance will be cleared";
							$placeholders[':pid']=$pid;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}
						elseif($data[1] > 0){
							//insert date when balance will be cleared
							if($pay_type==2 or $pay_type==3 or $pay_type==4 or $pay_type==5 or $pay_type==10 and $data[1] > 0){
								//get old balabce record if any
								$sql=$error=$s='';$placeholders=array();
								//$sql="delete from  balance_clearance_date  where pid=:pid";
								$sql="select pid, date_to_clear, added_by , balance, comments, when_added from  balance_clearance_date  where pid=:pid";
								$error="Unable to get old pleges";
								$placeholders[':pid']=$pid;
								$s = select_sql($sql, $placeholders, $error, $pdo);

								//insert that into comments table
								foreach($s as $row){
									$sql=$error=$s='';$placeholders=array();
									$sql="insert into cash_pledge_comments  set pid=:pid, when_added=:when_added,
										pledge_date=:pledge_date, added_by=:added_by, balance=:balance, comments=:comments";
									$error="Unable to record old balance pledge";
									$placeholders[':pid']=$row['pid'];
									$placeholders[':when_added']=$row['when_added'];
									$placeholders[':pledge_date']=$row['date_to_clear'];
									$placeholders[':added_by']=$row['added_by'];
									$placeholders[':balance']=$row['balance'];
									$placeholders[':comments']=$row['comments'];
									$s = insert_sql($sql, $placeholders, $error, $pdo);
								}

								//remove old balabce record if any
								$sql=$error=$s='';$placeholders=array();
								$sql="delete from  balance_clearance_date  where pid=:pid";
								$error="Unable to clear date balance will be cleared";
								$placeholders[':pid']=$pid;
								$s = insert_sql($sql, $placeholders, $error, $pdo);

								$sql=$error=$s='';$placeholders=array();
								$sql="insert into balance_clearance_date  set when_added=now(),
									date_to_clear=:date_to_clear, pid=:pid, added_by=:added_by ,balance=:balance, comments=:comments";
								$error="Unable to record date balance will be cleared";
								$placeholders[':date_to_clear']=$_POST['date_clear_bal'];
								$placeholders[':pid']=$pid;
								$placeholders[':added_by']=$_SESSION['id'];
								$placeholders[':balance']=$data[1];
								$placeholders[':comments']=$_POST['comment'];
								$s = insert_sql($sql, $placeholders, $error, $pdo);
							}
							$bal="Cash balance is KES: ".number_format($data[1],2);
						}
						elseif($data[1] < 0){
							$data[1]=str_replace("-", "", "$data[1]");
							$bal="Cash credit is KES: ".number_format($data[1],2);
							//if balance is a credit delete any balance clearance dates
							$sql=$error=$s='';$placeholders=array();
							$sql="delete from  balance_clearance_date  where pid=:pid";
							$error="Unable to clear date balance will be cleared";
							$placeholders[':pid']=$pid;
							$s = insert_sql($sql, $placeholders, $error, $pdo);

							$sql=$error=$s='';$placeholders=array();
							$sql="delete from  cash_pledge_comments  where pid=:pid";
							$error="Unable to clear date balance will be cleared";
							$placeholders[':pid']=$pid;
							$s = insert_sql($sql, $placeholders, $error, $pdo);

						}

						//insert balance statement
						$sql=$error=$s='';$placeholders=array();
						$sql="update payments set balance=:balance where id=:id";
						$error="Unable to make add balance to payment";
						$placeholders[':id']=$id;
						$placeholders[':balance']="$bal";
						$s = insert_sql($sql, $placeholders, $error, $pdo);

						//now produce statement for loyalty points.
						$points_bal='';
						if($data[2] == 0){$points_bal="Loyalty points 0.";}
						elseif($data[2] < 0){
							$data[2]=str_replace("-", "", "$data[2]");
							$points_bal="Loyalty points ".number_format($data[2],2);
						}
						//insert points  statement
						$sql=$error=$s='';$placeholders=array();
						$sql="update payments set points_balance=:points_balance where id=:id";
						$error="Unable to make add points to payment";
						$placeholders[':id']=$id;
						$placeholders[':points_balance']="$points_bal";
						$s = insert_sql($sql, $placeholders, $error, $pdo);






					}

					//now insert into credit transfer table
					if($pay_type==10){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into credit_transfer set
							when_added=now(),
							donor_pid=:donor_pid,
							receiver_pid=:receiver_pid,
							amount=:amount,
							created_by=:created_by";
						$error="Unable to record credit trasnfer";
						$placeholders[':donor_pid']=$data2[0];
						$placeholders[':receiver_pid']=$encrypt->decrypt("$_POST[token_ninye]");;
						$placeholders[':amount']=$amount;
						$placeholders[':created_by']=$_SESSION['id'];
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}

				}
				elseif($pay_type==6){
						//this is for waived treatment
						if($id == ''){$id=0;}
						$waiver_id='';
						$user_type=0;
						if(userHasSubRole($pdo,6)){
							$user_type=1;
						}
						//insert waiver request status
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into waiver_approvals set
							amount=:amount,
							pid=:pid,
							pay_id=:pay_id";
						$error="Unable to update waiver approvals";
						$placeholders[':amount']=$amount;
						//$placeholders[':pay_id']=$id;
						$placeholders[':pay_id']=0;
						$placeholders[':pid']=$encrypt->decrypt("$_POST[token_ninye]");
						$waiver_id = get_insert_id($sql, $placeholders, $error, $pdo);

						//insert waiver comments
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into waiver_approval_communication set waiver_id=:waiver_id,
							date_of_comment=now(),
							comment=:comment,
							user_id=:user_id,
							user_type=:user_type
							";
						$error="Unable to update wiver approval communication";
						$placeholders[':waiver_id']=$waiver_id;
						$placeholders[':comment']=$_POST['waiver_reason'];
						$placeholders[':user_id']=$_SESSION['id'];
						$placeholders[':user_type']=$user_type;
						$s = insert_sql($sql, $placeholders, $error, $pdo);

				}
					if($s){$tx_result = $pdo->commit();}
						elseif(!$s){$pdo->rollBack();$tx_result=false;}
						if($tx_result){
							if($id==0){$message="good#self_payment#Waiver payment sent for approval";}
							elseif($id>0){$message="good#self_payment#Payment saved";}
							//insert token into table and check if unique
							$sql=$error=$s='';$placeholders=array();
							$sql="insert into check_token set token_val=:token_val, when_added=now()";
							$error="Unable to add token val";
							$placeholders['token_val']=$_POST['token_non_ins_pay'];
							$s = 	insert_sql($sql, $placeholders, $error, $pdo);
						}
			}
			catch (PDOException $e)
			{
			$pdo->rollBack();
			//$message="bad#Unable to edit Lab Technicians  ";
			}
		}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){
			$_SESSION['result_class']='success_response';
			$_SESSION['result_message']="$data[2]";
			$_SESSION['pay_id']=$id;
			$pid_bal="pid_".$encrypt->decrypt("$_POST[token_ninye]");
			$_SESSION["$pid_bal"]=array();
			$result=show_pt_statement_brief($pdo,$_POST['token_ninye'],$encrypt);
			$data=explode('#',"$result");
			$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
		}
		echo $message;
	}//end check token else if
}


//this is for adding cadcam referal servies
if(isset($_SESSION['token_cmr1']) and isset($_POST['token_cmr1']) and $_POST['token_cmr1']==$_SESSION['token_cmr1']
	and userHasRole($pdo,55)){

	//perform verifications
	$exit_flag=false;

	//check names
	if($_POST['first_name']=='' and $_POST['middle_name']=='' and $_POST['last_name']==''){
		$exit_flag=true;
		$message="bad#Please specify the patient's names";
	}
	//check patient type
	if(!$exit_flag and $_POST['ptype']!=''){
		$ptype=html($encrypt->decrypt($_POST['ptype']));//echo "<br>$ptype is ";exit;
		if(!$exit_flag and !in_array($ptype, $_SESSION['patient_type_array'])){

			$exit_flag=true;
			$message="somebody tried to input $ptype as a patient type into patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as patient type is not specified. ";
		}
	}
	elseif(!$exit_flag and $_POST['ptype']==''){
		$exit_flag=true;
		$message="bad#Please specify the patient type";
	}

	//check covered compnaycovered_company
	$company_covered='';
	if(!$exit_flag and isset($_POST['covered_company'])){
		$company_covered=html($encrypt->decrypt($_POST['covered_company']));
		if(!$exit_flag and isset($_POST['covered_company']) and $_POST['covered_company']!=''){

			if(!in_array($company_covered,$_SESSION['covered_company_array'])){

				$exit_flag=true;
				$message="somebody tried to input $company_covered as a covered compnay into patient details";
				log_security($pdo,$message);
				$message="bad#Unable to save details as covered company  is not correctly specified. ";
			}
		}
	}

		//check if cost is set and is valid
		if(!$exit_flag and !isset($_POST['cost']) or 	$_POST['cost']==''){
			$message="bad#Unable to save details as cost is not set. ";
			$exit_flag=true;
		}


		if(!$exit_flag and isset($_POST['cost']) and 	$_POST['cost']!=''){
			//remove commas
			$amount=str_replace(",", "", $_POST['cost']);
				//check if amount is integer
			if(!ctype_digit($amount)){//echo "ooooo $unit_price[$i] ";
				//check if it has only 2 decimal places
				$data=explode('.',$amount);
				$invalid_amount=html("$amount");
				if ( count($data) != 2 ){
					$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
					$exit_flag=true;

				}
				elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
					$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
					$exit_flag=true;

				}
			}


		}

	$pay_type=$encrypt->decrypt($_POST['non_ins_payment_type']);

		//check if insurer is suspended
	if(!$exit_flag and $pay_type==7 and $company_covered!='' and $ptype!=''){
		//check if insurance cover is supensed
		$sql=$error=$s='';$placeholders=array();
		$sql="select suspended_cover, suspended_reason from covered_company where id=:covered_company";
		$placeholders[':covered_company']=$company_covered;
		$error="Error: Unable to check cover status ";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		$reason2='';
		foreach($s as $row){
		 if($row['suspended_cover']=='Yes'){
		  $reason2=html($row['suspended_reason']);
		  $message="bad#Insurance cover is suspended. Reason: $reason2 ";
		  $exit_flag=true;
		 }
		}
	}

	if(!$exit_flag and $pay_type != 2  and $pay_type != 3  and $pay_type != 4  and $pay_type != 5  and $pay_type != 7){
		$exit_flag=true;
		$message="bad#Please specify the pay type.";
	}
	//cheque_number
	if(!$exit_flag and $pay_type==3 and (!isset($_POST['cheque_number']) or $_POST['cheque_number']=='')){
		$exit_flag=true;
		$message="bad#Please specify the cheque number.";
	}

	//mpesa_number
	if(!$exit_flag and $pay_type==4 and (!isset($_POST['mpesa_number']) or $_POST['mpesa_number']=='')){
		$exit_flag=true;
		$message="bad#Please specify the Mpesa transaction number.";
	}

	//visa_number
	if(!$exit_flag and $pay_type==5 ){
		if(!isset($_POST['visa_number']) or $_POST['visa_number']==''){
			$exit_flag=true;
			$message="bad#Please specify the VISA transaction number.";
		}
		elseif(!$exit_flag and !isset($_POST['bank_name']) or $_POST['bank_name']==''){
			$exit_flag=true;
			$message="bad#Please specify the VISA Bank used for the transaction.";
		}
	}

	//check referres

	if(!$exit_flag and $_POST['ref_doc']!=''){$referee=html($encrypt->decrypt($_POST['ref_doc']));}
	elseif(!$exit_flag and $_POST['ref_doc']==''){
		$exit_flag=true;
		$message="bad#Please specify the referrer.";
	}




	//now insert
	if(!$exit_flag ){
		try{
			$pdo->beginTransaction();


			//get patient ID
			$year=date('y');
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into cadcam_ref_pt_num_generator set id=null";
			$error="Unable to get cadcam ref patient number";
			$xid = 	get_insert_id($sql, $placeholders, $error, $pdo);

		//	echo "-3157-";
			//now insert into patient_details_a
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_details_a set last_name=:last_name, middle_name=:middle_name, first_name=:first_name, mobile_phone=:mobile_phone,
					type=:type, patient_number=:patient_number, member_no=:member_no, company_covered=:company_covered, pnum=:pnum,
					year=:year, internal_patient=2";
			$error="Unable to add cadcam referal patient ";
			$placeholders[':last_name']=$_POST['last_name'];
			$placeholders[':middle_name']=$_POST['middle_name'];
			$placeholders[':first_name']=$_POST['first_name'];
			$placeholders[':mobile_phone']=$_POST['mobile_no'];
			$placeholders[':type']=$ptype;
			$placeholders[':patient_number']="C$xid";
			$placeholders[':member_no']=$_POST['mem_no'];
			$placeholders[':company_covered']=$company_covered;
			$placeholders[':pnum']=0;
			$placeholders[':year']="0";
			$pid = get_insert_id($sql, $placeholders, $error, $pdo);
		//	echo "-3175-";
			//now insert into patient_details_b
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_details_b set  when_added=:when_added,
					 pid=:pid,  referee=:referee";
			$error="Unable to add patient new patient";
			$placeholders[':when_added']=date('Y-m-d');
			$placeholders[':referee']=$referee;
			$placeholders[':pid']=$pid;
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			//get if pre-auth  is needed
				$pre_auth_needed=$smart_needed='';
				//check if pre-auth or smart is needed for this patient
				$sql=$error1=$s='';$placeholders=array();
				$sql="select pre_auth_needed, smart_needed from covered_company a, patient_details_a b where b.type=a.insurer_id and b.company_covered=a.id
					and b.pid=:pid";
				$error="Unable to check if pre-auth is needed";
				$placeholders[':pid']=$pid;
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$pre_auth_needed=html($row['pre_auth_needed']);
					$smart_needed=html($row['smart_needed']);
				}
			//now get insert into blocks_used_group_number_generator and get group number

			//create group number
			$sql=$error=$s=$group_id='';$placeholders=array();
			$sql="insert into blocks_used_group_number_generator set  added_by=:added_by, when_added=now(), cost=:cost, user_type=0,
					user_id=:user_id";
			$error="Unable to generate block used group number";
			$placeholders[':added_by']=$_SESSION['id'];
			$placeholders[':cost']=$amount;
			$placeholders[':user_id']=$pid;
			$group_id = 	get_insert_id($sql, $placeholders, $error, $pdo);

			//now record stock used
			$quantity=$_POST['stock_in'];
			$block_id=$_POST['ninye'];
			$n=count($block_id);
			$i=0;
			while($i < $n){
				if($quantity[$i]==''){
					$i++;
					if($i==$n){
						$message="bad#Please specify stock used. ";
						$exit_flag=true;
						break;
					}
					continue;
				}

				//check if quantity is valid integer
				if(!ctype_digit($quantity[$i])){
					$var=html("$quantity[$i]");
					$message="bad#Unable to save details as quantity $var is not a valid integer. ";
					$exit_flag=true;
					break;
				}

				$clean_block_id=$encrypt->decrypt($block_id[$i]);
				$balance=0;

				//check if stock is adequate
				$sql5=$error5=$s5='';$placeholders5=array();
				$sql5="SELECT cadcam_types.id, cadcam_types.name, b.quantity_in,c.quantity_out, b.quantity_in - ifnull(c.quantity_out,0) as stock_left
										from cadcam_types  LEFT JOIN( select block_id, ifnull( sum( blocks_stock_in.quantity ) , 0 ) as quantity_in
										from blocks_stock_in group by block_id ) as b on  cadcam_types.id=b.block_id
										left join (select block_id, sum(quantity) as quantity_out from blocks_stock_out group by block_id) as c on cadcam_types.id=c.block_id
										WHERE  cadcam_types.id=:block_id";
				/*$sql5="SELECT ifnull( sum( blocks_stock_in.quantity ) , 0 ) - ifnull( sum( blocks_stock_out.quantity ) , 0 ) as stock_left
					FROM cadcam_types LEFT JOIN blocks_stock_in ON cadcam_types.id = blocks_stock_in.block_id
					LEFT JOIN blocks_stock_out ON cadcam_types.id = blocks_stock_out.block_id
					WHERE cadcam_types.id=:block_id
					GROUP BY cadcam_types.id ";*/
				$error5="Unable to get stock left for cadcam block";
				$placeholders5[':block_id']=$clean_block_id;
				$s5 = 	select_sql($sql5, $placeholders5, $error5, $pdo);
				foreach($s5 as $row5){$balance=html($row5['stock_left']);}

				if($quantity[$i] > $balance){
					$var=html("$quantity[$i]");
					$message="bad#Unable to save details as quantity $var exceeds availlable stock of $balance. ";
					$exit_flag=true;
					break;
				}

				//now insert
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into blocks_stock_out set block_id=:block_id, quantity=:quantity, group_number=:group_number";
				$error="Unable to record block usage";
				$placeholders[':block_id']=$clean_block_id;
				$placeholders[':quantity']=$quantity[$i];
				$placeholders[':group_number']=$group_id;
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);

				$i++;
			}
			if(!$exit_flag){
				//now insert into tplan procedure
				//get tplan id
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into tplan_id_generator set when_added=now(), pid=:pid, created_by=:user_id";
				$error="Unable to create treatment plan";
				$placeholders[':pid']=$pid;
				$placeholders[':user_id']=$_SESSION['id'];
				$tplan_id = get_insert_id($sql, $placeholders, $error, $pdo);
		//		echo "-3195-";
				//now insert

				if($pay_type == 7){//this will generate an invoice number for the xray
					/*$sql=$error=$s='';$placeholders=array();
					$sql="insert into cadcam_ref_invoice_number_generator set id=null";
					$error="Unable to get cadcam ref invoice number";
					$xinv = 	get_insert_id($sql, $placeholders, $error, $pdo);
					$invoice_number="C$xinv-".date("m/y");*/

							//first get invocie number
							$sql=$error=$s='';$placeholders=array();
							$sql="select max(invoice_num_id) from cadcam_ref_invoice_number_generator";
							$error="Unable to get cadcam ref invoice number";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){$xinv=$row[0] + 1;}
							if($xinv == 0){$xinv = 1;}

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into cadcam_ref_invoice_number_generator set invoice_num_id =:xinv";
							$error="Unable to get cadcam ref invoice number";
							$placeholders[':xinv']=$xinv;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
							$invoice_number="C$xinv-".date("m/y");


					//also get unique interger to identoify this invoice as the above may be used by internal patients
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into unique_invoice_number_generator set when_raised=now(), pid=:pid,
						added_by=:added_by, invoice_number=:invoice_number";
					$error="Unable to get unique invoice number";
					$placeholders[':pid']=$pid;
					$placeholders[':added_by']=$_SESSION['id'];
					$placeholders[':invoice_number']="$invoice_number";
					$inv_num = 	get_insert_id($sql, $placeholders, $error, $pdo);



					if($pre_auth_needed=='YES' or $smart_needed=='YES'){$authorised_cost=NULL;}
					elseif($pre_auth_needed!='YES' and $smart_needed!='YES'){$authorised_cost=$amount;}

				}
				else{$authorised_cost=$amount;}

				$sql2=$error2=$s2='';$placeholders2=array();
				if($pay_type == 7){
					$sql2="insert tplan_procedure set
						tplan_id=:tplan_id,
						procedure_id=3,
					 details=:details,
					  unauthorised_cost=:unathorised_cost,
					  authorised_cost=:authorised_cost,
					  pay_type=1,
					  invoice_number=:invoice_number,
					  pid=:pid,
					  created_by=:created_by,
					  date_procedure_added=now(),
					  status=2,
					  date_invoiced=now(),
					  number_done=1,
					   invoice_id=:invoice_id

						";
					$error2="Unable to add xrays to tplan";
					$placeholders2[':tplan_id']=$tplan_id;
					$placeholders2[':details']=$group_id;
					$placeholders2[':unathorised_cost']=$amount;
					$placeholders2[':authorised_cost']=$authorised_cost;
					$placeholders2[':invoice_number']="$invoice_number";
					$placeholders2[':pid']=$pid;
					$placeholders2[':created_by']=$_SESSION['id'];
					$placeholders2[':invoice_id']=$inv_num;

				}//now insert for cash patients
				elseif($pay_type == 2 or $pay_type == 3 or $pay_type == 4 or $pay_type == 5){
					$sql2="insert tplan_procedure set
						tplan_id=:tplan_id,
						procedure_id=3,
					 details=:details,
					  unauthorised_cost=:unathorised_cost,
					  authorised_cost=:authorised_cost,
					  pay_type=2,
					  pid=:pid,
					  created_by=:created_by,
					  date_procedure_added=now(),
					  status=2,
					   number_done=1";
					$error2="Unable to add xrays to tplan";
					$placeholders2[':tplan_id']=$tplan_id;
					$placeholders2[':details']=$group_id;
					$placeholders2[':unathorised_cost']=$amount;
					$placeholders2[':authorised_cost']=$amount;
					$placeholders2[':pid']=$pid;
					$placeholders2[':created_by']=$_SESSION['id'];

				}
				$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);



				//make payment for non invoice cadcams
				if(!$exit_flag and $pay_type == 2 or $pay_type == 3 or $pay_type == 4 or $pay_type == 5){
						//add payment record in payments table
						/*$sql=$error=$s='';$placeholders=array();
						$sql="insert into non_insurance_receipt_id_generator set id=null";
						$error="Unable to get receipt number";
						$rid = 	get_insert_id($sql, $placeholders, $error, $pdo);
						$receipt_number="R$rid-".date("m/y");*/

					//first get receipt number for non insured payment
					$sql=$error=$s='';$placeholders=array();
					$sql="select max(receipt_num) from non_insurance_receipt_id_generator";
					$error="Unable to get non insured receipt number";
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$rid=$row[0] + 1;}
					if($rid == 0){$rid = 1;}

					$sql=$error=$s='';$placeholders=array();
					$sql="insert into non_insurance_receipt_id_generator set receipt_num =:rid";
					$error="Unable to get non insured receipt number2";
					$placeholders[':rid']=$rid;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					$receipt_number="R$rid-".date('m/y');
					$receipt_num_id=$rid;
						//now add to payments table
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into payments set when_added=now(),
							receipt_num=:receipt_num,
							amount=:amount,
							pay_type=:pay_type,
							pid=:pid,
							tx_number=:tx_number,
							created_by=:created_by,
							receipt_num_id=:receipt_num_id,
							bank_id=:bank_id";
						$error="Unable to make payment for xray referal";
						$placeholders[':receipt_num']="$receipt_number";
						$placeholders[':amount']=$amount;
						$placeholders[':pay_type']=$pay_type;
						$placeholders[':pid']=$pid;
						$placeholders[':created_by']=$_SESSION['id'];
						$placeholders[':receipt_num_id']=$receipt_num_id ;
						if($pay_type==2){$placeholders[':tx_number']='';$placeholders[':bank_id']=0;}
						elseif($pay_type==3){$placeholders[':tx_number']=$_POST['cheque_number'];$placeholders[':bank_id']=0;}
						elseif($pay_type==4){$placeholders[':tx_number']=$_POST['mpesa_number'];$placeholders[':bank_id']=0;}
						elseif($pay_type==5){
							$placeholders[':tx_number']=$_POST['visa_number'];
							$placeholders[':bank_id']=$encrypt->decrypt("$_POST[bank_name]");
						}
						$id = 	get_insert_id($sql, $placeholders, $error, $pdo);
				}
			}
			if(!$exit_flag and $s2 and $pay_type == 7){
				$message="good#cadcam-referal#Invoice $invoice_number has been raised ";
				$_SESSION['inv_no']="$invoice_number";
			}
			elseif(!$exit_flag and $s2 and $pay_type == 2 or $pay_type == 3 or $pay_type == 4 or $pay_type == 5){
				$message="good#cadcam-referal#Receipt number $receipt_number has been raised ";
				$_SESSION['pay_id']=$id;
			}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();

		}
	}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo $message;

}

//this is for xray referals submissions
//this is adding an xray referal
if(isset($_SESSION['token_xr1']) and isset($_POST['token_xr1']) and $_POST['token_xr1']==$_SESSION['token_xr1']
	and userHasRole($pdo,54)){

	//perform verifications
	$exit_flag=false;

	//check names
	if($_POST['first_name']=='' and $_POST['middle_name']=='' and $_POST['last_name']==''){
		$exit_flag=true;
		$message="bad#Please specify the patient's names";
	}
	//check patient type
	if($_POST['ptype']!=''){
		$ptype=html($encrypt->decrypt($_POST['ptype']));//echo "<br>$ptype is ";exit;
		if(!$exit_flag and !in_array($ptype, $_SESSION['patient_type_array'])){

			$exit_flag=true;
			$message="somebody tried to input $ptype as a patient type into patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as patient type is not specified. ";
		}
	}
	elseif($_POST['ptype']==''){
		$exit_flag=true;
		$message="bad#Please specify the patient type";
	}

	//check covered compnaycovered_company
	$company_covered='';
	if(isset($_POST['covered_company'])){
		$company_covered=html($encrypt->decrypt($_POST['covered_company']));
		if(!$exit_flag and isset($_POST['covered_company']) and $_POST['covered_company']!=''){

			if(!in_array($company_covered,$_SESSION['covered_company_array'])){

				$exit_flag=true;
				$message="somebody tried to input $company_covered as a covered compnay into patient details";
				log_security($pdo,$message);
				$message="bad#Unable to save details as covered company  is not correctly specified. ";
			}
		}
	}


	$pay_type=$encrypt->decrypt($_POST['non_ins_payment_type']);

	if(!$exit_flag and $pay_type != 2  and $pay_type != 3  and $pay_type != 4  and $pay_type != 5  and $pay_type != 7){
		$exit_flag=true;
		$message="bad#Please specify the pay type.";
	}

	//check if insurer is suspended
	if(!$exit_flag and $pay_type==7 and $company_covered!='' and $ptype!=''){
		//check if insurance cover is supensed
		$sql=$error=$s='';$placeholders=array();
		$sql="select suspended_cover, suspended_reason from covered_company where id=:covered_company";
		$placeholders[':covered_company']=$company_covered;
		$error="Error: Unable to check cover status ";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		$reason2='';
		foreach($s as $row){
		 if($row['suspended_cover']=='Yes'){
		  $reason2=html($row['suspended_reason']);
		  $message="bad#Insurance cover is suspended. Reason: $reason2 ";
		  $exit_flag=true;
		 }
		}
	}

	//cheque_number
	if(!$exit_flag and $pay_type==3 and (!isset($_POST['cheque_number']) or $_POST['cheque_number']=='')){
		$exit_flag=true;
		$message="bad#Please specify the cheque number.";
	}

	//mpesa_number
	if(!$exit_flag and $pay_type==4 and (!isset($_POST['mpesa_number']) or $_POST['mpesa_number']=='')){
		$exit_flag=true;
		$message="bad#Please specify the Mpesa transaction number.";
	}

	//visa_number
	if(!$exit_flag and $pay_type==5 ){
		if(!isset($_POST['visa_number']) or $_POST['visa_number']==''){
			$exit_flag=true;
			$message="bad#Please specify the VISA transaction number.";
		}
		elseif(!$exit_flag and !isset($_POST['bank_name']) or $_POST['bank_name']==''){
			$exit_flag=true;
			$message="bad#Please specify the VISA Bank used for the transaction.";
		}
	}

	//check referres

	if($_POST['ref_doc']!=''){$referee=html($encrypt->decrypt($_POST['ref_doc']));}
	elseif($_POST['ref_doc']==''){
		$exit_flag=true;
		$message="bad#Please specify the referrer.";}




	//now insert
	if(!$exit_flag ){
		try{
			$pdo->beginTransaction();


			//get patient ID
			$year=date('y');
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into xray_ref_pt_num_generator set id=null";
			$error="Unable to get xray ref patient number";
			$xid = 	get_insert_id($sql, $placeholders, $error, $pdo);

		//	echo "-3157-";
			//now insert into patient_details_a
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_details_a set last_name=:last_name, middle_name=:middle_name, first_name=:first_name, mobile_phone=:mobile_phone,
					type=:type, patient_number=:patient_number, member_no=:member_no, company_covered=:company_covered, pnum=:pnum,
					year=:year, internal_patient=1";
			$error="Unable to add xray referal patient ";
			$placeholders[':last_name']=$_POST['last_name'];
			$placeholders[':middle_name']=$_POST['middle_name'];
			$placeholders[':first_name']=$_POST['first_name'];
			$placeholders[':mobile_phone']=$_POST['mobile_no'];
			$placeholders[':type']=$ptype;
			$placeholders[':patient_number']="X$xid";
			$placeholders[':member_no']=$_POST['mem_no'];
			$placeholders[':company_covered']=$company_covered;
			$placeholders[':pnum']=0;
			$placeholders[':year']="0";
			$pid = get_insert_id($sql, $placeholders, $error, $pdo);
		//	echo "-3175-";
			//now insert into patient_details_b
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_details_b set  when_added=:when_added,
					 pid=:pid,  referee=:referee";
			$error="Unable to add patient new patient";
			$placeholders[':when_added']=date('Y-m-d');
			$placeholders[':referee']=$referee;
			$placeholders[':pid']=$pid;
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
//echo "-3185-";

			//get if pre-auth  is needed
				$pre_auth_needed=$smart_needed='';
				//check if pre-auth or smart is needed for this patient
				$sql=$error1=$s='';$placeholders=array();
				$sql="select pre_auth_needed, smart_needed from covered_company a, patient_details_a b where b.type=a.insurer_id and b.company_covered=a.id
					and b.pid=:pid";
				$error="Unable to check if pre-auth is needed";
				$placeholders[':pid']=$pid;
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$pre_auth_needed=html($row['pre_auth_needed']);
					$smart_needed=html($row['smart_needed']);
				}

			//now insert for non-invoice
			//get tplan id
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into tplan_id_generator set when_added=now(), pid=:pid, created_by=:user_id";
			$error="Unable to create treatment plan";
			$placeholders[':pid']=$pid;
			$placeholders[':user_id']=$_SESSION['id'];
			//$placeholders[':pid']=$_SESSION['pid'];
			$tplan_id = get_insert_id($sql, $placeholders, $error, $pdo);
	//		echo "-3195-";
			//now insert xrays done
			$nimeana=$encrypt->decrypt($_POST['nimeana']);

			if($pay_type == 7){//this will generate an invoice number for the xray
				/*$sql=$error=$s='';$placeholders=array();
				$sql="insert into xray_ref_invoice_number_generator set id=null";
				$error="Unable to get xray ref invoice number";
				$xinv = 	get_insert_id($sql, $placeholders, $error, $pdo);
				$invoice_number="X$xinv-".date("m/y");*/
							//first get invocie number
							$sql=$error=$s='';$placeholders=array();
							$sql="select max(invoice_num_id) from xray_ref_invoice_number_generator";
							$error="Unable to get xray ref invoice number";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){$xinv=$row[0] + 1;}
							if($xinv == 0){$xinv = 1;}

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into xray_ref_invoice_number_generator set invoice_num_id =:xinv";
							$error="Unable to get xray ref invoice number";
							$placeholders[':xinv']=$xinv;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
							$invoice_number="X$xinv-".date("m/y");


				//also get unique interger to identoify this xray as the above may be used by internal patients
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into unique_invoice_number_generator set invoice_number=:invoice_number, when_raised=now(),
					added_by=:added_by, pid=:pid";
				$placeholders[':invoice_number']="$invoice_number";
				$placeholders[':added_by']=$_SESSION['id'];
				$placeholders[':pid']=$pid;
				$error="Unable to get unique invoice number";
				$inv_num = 	get_insert_id($sql, $placeholders, $error, $pdo);

			//	if($pre_auth_needed=='YES' or $smart_needed=='YES'){$authorised_cost=NULL;}
			//	elseif($pre_auth_needed!='YES' and $smart_needed!='YES'){$authorised_cost=$amount;}


			}
		//	else{$authorised_cost=$amount;}
			$count=1;
			$cost=0;
			$empty=0;
			//echo "nimeana is $nimeana --";
			while($count < $nimeana){
				if(!isset($_POST["xrays$count"]) or $_POST["xray_cost$count"]==''){
					$empty++;
			//		echo "<br>$empty-$count-$nimeana";
					if(($empty + 1) == $nimeana){
						$exit_flag=true;
						$message="bad#Please specify the X-RAY to be performed and it's cost";
						break;
					}
					$count++;
					continue;
					}
				if(isset($_POST["xrays$count"]) and $_POST["xray_cost$count"]==''){
						$exit_flag=true;
						$message="bad#Please specify a cost for each X-RAY selected";
						break;
					}
					//get specified teeth if any
					$teeth_specified='';
					if(isset($_POST["teeth_specified$count"])){
						$teeth=$_POST["teeth_specified$count"];
						$n=count($teeth);
						$i=0;
						while($i < $n){
							if($i == 0){$teeth_specified=$encrypt->decrypt("$teeth[$i]");}
							else{$teeth_specified="$teeth_specified, ".$encrypt->decrypt("$teeth[$i]");}
							$i++;
						}
					}
				//check if xray cost is a valid number
						//remove commas
						$amount=str_replace(",", "", $_POST["xray_cost$count"]);
							//check if amount is integer
						if(!ctype_digit($amount)){//echo "ooooo $unit_price[$i] ";
							//check if it has only 2 decimal places
							$data=explode('.',$amount);
							$invalid_amount=html("$amount");
							if ( count($data) != 2 ){

							$message="bad#Unable to save details  as cost $invalid_amount is not a 	valid number. ";
							$exit_flag=true;
							break;}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
							$exit_flag=true;
							break;}
						}
				if($pay_type == 7){
					if($pre_auth_needed=='YES' or $smart_needed=='YES'){$authorised_cost=NULL;}
					elseif($pre_auth_needed!='YES' and $smart_needed!='YES'){$authorised_cost=$amount;}
				}
				else{$authorised_cost=$amount;}

				$xray_id=$encrypt->decrypt($_POST["xrays$count"]);
				$sql2=$error2=$s2='';$placeholders2=array();
				if($pay_type == 7){
					$sql2="insert tplan_procedure set
						tplan_id=:tplan_id,
						procedure_id=:procedure_id,
					  unauthorised_cost=:unathorised_cost,
					  authorised_cost=:authorised_cost,
					  pay_type=1,
					  invoice_number=:invoice_number,
					  pid=:pid,
					  created_by=:created_by,
					  date_procedure_added=now(),
					  status=2,
					  date_invoiced=now(),
					  number_done=1,
					  teeth=:teeth_specified,
					  invoice_id=:invoice_id

						";
					$error2="Unable to add xrays to tplan";
					$placeholders2[':tplan_id']=$tplan_id;
					$placeholders2[':unathorised_cost']=$amount;
					$placeholders2[':authorised_cost']=$authorised_cost;
					$placeholders2[':invoice_number']="$invoice_number";
					$placeholders2[':pid']=$pid;
					$placeholders2[':created_by']=$_SESSION['id'];
					$placeholders2[':teeth_specified']="$teeth_specified";
					$placeholders2[':invoice_id']=$inv_num;
					$placeholders2[':procedure_id']=$xray_id;

				}//now insert for cash patients
				elseif($pay_type == 2 or $pay_type == 3 or $pay_type == 4 or $pay_type == 5){
					$sql2="insert tplan_procedure set
						tplan_id=:tplan_id,
						procedure_id=:procedure_id,
					  unauthorised_cost=:unathorised_cost,
					  authorised_cost=:authorised_cost,
					  pay_type=2,
					  pid=:pid,
					  created_by=:created_by,
					  date_procedure_added=now(),
					  status=2,
					   number_done=1,
					  teeth=:teeth_specified

						";
					$error2="Unable to add xrays to tplan";
					$placeholders2[':tplan_id']=$tplan_id;
					$placeholders2[':unathorised_cost']=$amount;
					$placeholders2[':authorised_cost']=$amount;
					$placeholders2[':pid']=$pid;
					$placeholders2[':created_by']=$_SESSION['id'];
					$placeholders2[':teeth_specified']="$teeth_specified";
					$placeholders2[':procedure_id']=$xray_id;


				}
				$cost= $cost + $amount;
				$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
				$count++;
			}

			//make payment for non invoice xrays
			if(!$exit_flag and $pay_type == 2 or $pay_type == 3 or $pay_type == 4 or $pay_type == 5){
								//add payment record in payments table
					/*$sql=$error=$s='';$placeholders=array();
					$sql="insert into non_insurance_receipt_id_generator set id=null";
					$error="Unable to get receipt number";
					$rid = 	get_insert_id($sql, $placeholders, $error, $pdo);
					$receipt_number="R$rid-".date("m/y");*/

							$receipt_number='';
							$rid=0;
							//first get receipt number for non insured payment
							$sql=$error=$s='';$placeholders=array();
							$sql="select max(receipt_num) from non_insurance_receipt_id_generator";
							$error="Unable to get non insured receipt number";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){$rid=$row[0] + 1;}
							if($rid == 0){$rid = 1;}

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into non_insurance_receipt_id_generator set receipt_num =:rid";
							$error="Unable to get non insured receipt number";
							$placeholders[':rid']=$rid;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
							$receipt_number="R$rid-".date('m/y');

					//now add to payments table
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into payments set when_added=now(),
						receipt_num=:receipt_num,
						amount=:amount,
						pay_type=:pay_type,
						pid=:pid,
						tx_number=:tx_number,
						receipt_num_id=:receipt_num_id,
						created_by=:created_by,
						bank_id=:bank_id";
					$error="Unable to make payment for xray referal";
					$placeholders[':receipt_num']="$receipt_number";
					$placeholders[':amount']=$cost;
					$placeholders[':pay_type']=$pay_type;
					$placeholders[':pid']=$pid;
					if($pay_type==2){$placeholders[':tx_number']='';$placeholders[':bank_id']=0;}
					elseif($pay_type==3){$placeholders[':tx_number']=$_POST['cheque_number'];$placeholders[':bank_id']=0;}
					elseif($pay_type==4){$placeholders[':tx_number']=$_POST['mpesa_number'];$placeholders[':bank_id']=0;}
					elseif($pay_type==5){
						$placeholders[':tx_number']=$_POST['visa_number'];
						$placeholders[':bank_id']=$encrypt->decrypt("$_POST[bank_name]");
					}
					$placeholders[':receipt_num_id']=$rid ;
					$placeholders[':created_by']=$_SESSION['id'];
					$id = 	get_insert_id($sql, $placeholders, $error, $pdo);
			}

			if(!$exit_flag and $s2 and $pay_type == 7){
				$message="good#xray-referal#Invoice $invoice_number has been raised ";
				$_SESSION['inv_no']="$invoice_number";
			}
			elseif(!$exit_flag and $s2 and $pay_type == 2 or $pay_type == 3 or $pay_type == 4 or $pay_type == 5){
				$message="good#xray-referal#Receipt number $receipt_number has been raised ";
				$_SESSION['pay_id']=$id;
			}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();

		}
	}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo $message;

}

//this will edit procedures
elseif(isset($_POST['token_ep1']) and isset($_SESSION['token_ep1']) and $_POST['token_ep1']==$_SESSION['token_ep1']
	and userHasRole($pdo,23)){
	//save entries
	$i=1;
	$exit_flag=false;
	$message='';
	try{
		$pdo->beginTransaction();
			//list the procedure it will be unlisted below if need be
			$sql=$error=$s='';$placeholders=array();
			$sql="update procedures set listed=0  ";
			$error="Unable to delete old procedure";
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			//get number of records
			$nisiana=$encrypt->decrypt($_POST['nisiana']);
			while($i <= $nisiana){
					$ninye="ninye$i";
					$procedure_name="procedure_name$i";
					$procedure_cost="procedure_cost$i";
					$tooth_specific="tooth_specific$i";
					$delete_procedure="delete_procedure$i";
					$procedure_type="procedure_type$i";
					//check if procedure type is in valid range
					$procedure_type=$encrypt->decrypt($_POST["$procedure_type"]);
					if ($procedure_type!=1 and $procedure_type!=2){
							$message="bad# Procedure type not properly set for procedure number $i";
							$exit_flag=true;
							break;
					}

					//check if cost is a valid number
					//remove commas if they were used for formating
					$procedure_cost=str_replace(",", "", $_POST["$procedure_cost"]);
					if(isset($procedure_cost) and $procedure_cost!='' and !ctype_digit($procedure_cost)){
						//check if it has only 2 decimal places
						$data=explode('.',$procedure_cost);
						if ( count($data) != 2 ){
							$procedure_cost=html("$procedure_cost");
							$message="bad# Unable to save changes as $procedure_cost is not a valid number for procedure number $i ";
							$exit_flag=true;
							break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$procedure_cost=html("$procedure_cost");
							$message="bad# Unable to save changes as $procedure_cost is not a valid number for procedure number $i ";
							$exit_flag=true;
							break;
						}
					}



					//unlist procedures
					if(isset($_POST["$delete_procedure"]) and $_POST["$delete_procedure"]=='delete' ){
						$id=$encrypt->decrypt($_POST["$ninye"]);
						$sql=$error=$s='';$placeholders=array();
						$sql="update procedures set listed=1 where id=:id";
						$error="Unable to delete old procedure";
						$placeholders[':id']=$id;
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);
						$i++;
						continue;
					}

					if(!isset($_POST["$procedure_name"]) or $_POST["$procedure_name"]=='' ){
								$message="bad# Unable to edit procedures, please check for any empty procedure fields and fill them in for procedure number $i .";
								$exit_flag=true;
								break;
					}

					//edit existing procedures
					if(!isset($_POST["$tooth_specific"]) or ($_POST["$tooth_specific"]!='yes' and $_POST["$tooth_specific"]!='no')){
								$var=html($_POST["$procedure_name"]);
								$message="bad# Unable to edit procedures, please ensure all fields  have been specified correctly
								                  for procedure number $i";
								$exit_flag=true;
								if (isset($_POST["$tooth_specific"]) and $_POST["$tooth_specific"]!='yes' and $_POST["$tooth_specific"]!='no'){
									$var2=html($_POST["$tooth_specific"]);
									$security_log="somebody tried to input $var2 as tooth specific for procedure $var in treatment procedures";
								log_security($pdo,$security_log);
								}
					}


					//now edit procedure
					if(!$exit_flag ){
					$id=$encrypt->decrypt($_POST["$ninye"]);
					$sql=$error=$s='';$placeholders=array();
					$sql="update procedures set name=:name, all_teeth=:all_teeth , cost=:cost , type=:type where id=:id";
					$error="Unable to edit  procedure";
					$placeholders[':name']=$_POST["$procedure_name"];
					$placeholders[':cost']=$procedure_cost;
					$placeholders[':type']=$procedure_type;
					$placeholders[':all_teeth']=$_POST["$tooth_specific"];
					$placeholders[':id']=$id;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					//if(!$s ){break;$error="Unable to add new employer";}	*/

					}
					if($exit_flag) break;
					$i++;
			}


			if(!$exit_flag){
				$tx_result = $pdo->commit();
				if($tx_result){$message="good#add_procedure#Treatment procedures edited  ";}
			}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}

			//elseif(!$tx_result){$error_message="   Unable to edit Insured Companies  ";}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="   Unable to edit treatment procedures   ";
	}
			//$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}*/
		/*$_SESSION['result_class']=$_SESSION['result_message']='';
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}*/
		echo "$message";
		exit;
}

//this will insert new procedure
elseif(isset($_POST['token_ep2']) and isset($_SESSION['token_ep2']) and $_POST['token_ep2']==$_SESSION['token_ep2']
	and userHasRole($pdo,23)){
	//save entries
	$i=1;
	$n=8;


	$exit_flag=false;
	try{
		$pdo->beginTransaction();
			while($i <= $n){
					//now check cover limit
					$procedure_name="procedure_name$i";
					$tooth_specific="tooth_specific$i";
					$procedure_cost="procedure_cost$i";
					$procedure_type="procedure_type$i";
					//echo "$_POST['$procedure_name'] and
				//	echo "<br>$i<br>";
					if(!isset($_POST["$procedure_name"]) or $_POST["$procedure_name"]=='' ){
					//	echo "hhhhh$i";
						$i++;
						continue;
					}

					//check if procedure type is in valid range
					$procedure_type=$encrypt->decrypt($_POST["$procedure_type"]);
					if ($procedure_type!=1 and $procedure_type!=2){
							$message="bad#new_procedure# Procedure type not properly set for procedure number $i";
							$exit_flag=true;
							break;
					}
					//check if cost is a valid number
					//remove commas if they were used for formating
					$procedure_cost=str_replace(",", "", $_POST["$procedure_cost"]);
					if(isset($procedure_cost) and $procedure_cost!='' and !ctype_digit($procedure_cost)){
						//check if it has only 2 decimal places
						$data=explode('.',$procedure_cost);
						if ( count($data) != 2 ){
							$procedure_cost=html("$procedure_cost");
							$message="bad#new_procedure# Unable to save changes as $procedure_cost is not a valid number  for procedure number $i";
							$exit_flag=true;
							break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$procedure_cost=html("$procedure_cost");
							$message="bad#new_procedure# Unable to save changes as $procedure_cost is not a valid number  for procedure number $i";
							$exit_flag=true;
							break;
						}
					}

					//echo "$i $_POST[$procedure_name] and $_POST[$tooth_specific] <br>";
					if(!isset($_POST["$tooth_specific"]) or ($_POST["$tooth_specific"]!='yes' and $_POST["$tooth_specific"]!='no')){
								$var=html($_POST["$procedure_name"]);
								$message="bad#new_procedure# Unable to edit procedures, please ensure all fields  have been specified correctly
								                 for procedure $var";
								$exit_flag=true;
								if (isset($_POST["$tooth_specific"]) and $_POST["$tooth_specific"]!='yes' and $_POST["$tooth_specific"]!='no'){
									$var2=html($_POST["$tooth_specific"]);
									$security_log="somebody tried to input $var2 as tooth specific for procedure $var in treatment procedures";
								log_security($pdo,$security_log);
								}
					}

					//check if similar procedure name already exixts
					if(!$exit_flag){
						$sql=$error=$s='';$placeholders=array();
						$sql="select name from procedures where upper(name)=:name";
						$error="Unable to add new procedure";
						$placeholders[':name']=strtoupper($_POST["$procedure_name"]);
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						if($s->rowCount() > 0){
										$var=html($_POST["$procedure_name"]);
										$message="bad#new_procedure# Unable to add new procedure, $var as it already exists";
										$exit_flag=true;
										break;
						}
					}
					//now insert new company
					if(!$exit_flag ){
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into procedures set name=:name, all_teeth=:all_teeth , cost=:cost, type=:type";
					$error="Unable to add new procedure";
					$placeholders[':name']=$_POST["$procedure_name"];
					$placeholders[':all_teeth']=$_POST["$tooth_specific"];
					$placeholders[':cost']=$procedure_cost;
					$placeholders[':type']=$procedure_type;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					//if(!$s ){break;$error="Unable to add new employer";}	*/

					}
					if($exit_flag) break;
					$i++;
			}

			if(!$exit_flag){
				$tx_result = $pdo->commit();
				if($tx_result){$message="good#add_procedure# New treatment procedure added  ";}
			}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}

			//elseif(!$tx_result){$error_message="   Unable to edit Insured Companies  ";}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="   Unable to add new treatment procedure   ";
	}
			$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}*/
		$_SESSION['result_class']=$_SESSION['result_message']='';
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo "$message";
		exit;

}

//this will insert email address credentials
if( isset($_POST['token_em']) and $_POST['token_em']!='' and $_SESSION['token_em'] == $_POST['token_em'] and userHasRole($pdo,114)){
	//$_SESSION['token_em']='';
	//save entries
	$i=0;
	$n=0;

	$from_name=$_POST['name'];
	$email_address=$_POST['email_add'];
	$id=$_POST['ninye'];
	$passwd=$_POST['passwd'];
	$n=count($id);
	$exit_flag=false;
	try{
		$pdo->beginTransaction();
			while($i < $n){
				//echo "$i";
				//check if email address
				if(!isset($email_address[$i]) or $email_address[$i]==''){
					$message="bad#Please ensure that all email addresses are specified";
					$exit_flag=true;
					break;
				}

				//check if passwd is set
				if(!$exit_flag and !isset($passwd[$i]) or $passwd[$i]==''){
					$message="bad#Please ensure that all email addresses have passwords";
					$exit_flag=true;
					break;
				}

				//check if email format is correct
				if(!filter_var($email_address[$i], FILTER_VALIDATE_EMAIL)) {
					$exit_flag=true;
					$message=html("bad#Unable to save details as the email $email_address[$i]  is not correctly specified. ");
					$exit_flag=true;
					break;
				}
					$i++;
			}

			//now insert
			if(!$exit_flag){
				$i=0;

				while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update smtp_users set smtp_user_name=:smtp_user_name, smtp_password=:smtp_password,
					from_name=:from_name where id=:id ";
					$error="Unable to save email addresses";
					$placeholders[':smtp_user_name']="$email_address[$i]";
					$placeholders[':smtp_password']=$encrypt->encrypt("$passwd[$i]");
					$placeholders[':from_name']="$from_name[$i]";
					$placeholders[':id']=$encrypt->decrypt("$id[$i]");
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					$i++;
				}

			}

			if(!$exit_flag){
				$tx_result = $pdo->commit();
			}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();echo "$message";}
			if($tx_result){
				echo "good#test_emails";
				$_SESSION['test_connection']='yes';
				$_SESSION['result_class']='success_response';
				$_SESSION['result_message']='Email credentials saved';
			}
			//elseif(!$tx_result){$error_message="   Unable to edit Insured Companies  ";}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	 $pdo->rollBack();
	// echo "$message";
	}
	//get_covered_company($pdo);
}

//test all email accounts
elseif(isset($_POST['test_all_email_accounts']) and $_POST['test_all_email_accounts']=='test_all_email_accounts' and userHasRole($pdo,114)){
	test_all_email_accounts($pdo,$encrypt);
}


//this is for editing procedures already in points cover
elseif(isset($_POST['token_loyal2']) and isset($_SESSION['token_loyal2']) and $_POST['token_loyal2']==$_SESSION['token_loyal2']
	and userHasRole($pdo,42)){
		$exit_flag=false;
		//update points per minute

		try{
			$pdo->beginTransaction();
			//first edit entries
			$n=count($_POST['ninye']);
			$old_points=$_POST['old_points'];
			$ninye=$_POST['ninye'];
			$i=0;
			while($i < $n){
				//check if value is specified
				if(!$exit_flag and !isset($old_points[$i]) or $old_points[$i]==''){
					$exit_flag=true;
					$message="bad#Please specify number of points for each procedure in the loyalty scheme. ";
					break;
				}

				//checkif value is a number
				if(!$exit_flag and !ctype_digit($old_points[$i])){//echo "ooooo $unit_price[$i] ";
					//check if it has only 2 decimal places
					$data=explode('.',$old_points[$i]);
					$invalid_value=html($old_points[$i]);
					if ( count($data) != 2 ){

					$message="bad#Points per procedure specified, $invalid_value is not a valid number. ";
					$exit_flag=true;
					break;
					}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
					$message="bad#Points per procedure specified, $invalid_value is not a valid number. ";
					$exit_flag=true;
					break;
					}
				}

				//now update old entries
				if(!$exit_flag){
					$id=$encrypt->decrypt($ninye[$i]);
					$data=explode('#',"$id");
					//this is for procedures
					if(count($data)==1){
						$sql=$error=$s='';$placeholders=array();
						$sql="update  procedures_in_points_scheme set  points=:points where id=:id";
						$error="Unable to edit procedure in  points scheme";
						$placeholders[':points']=$old_points[$i];
						$placeholders[':id']=$id;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}
					//this is for xrays
					if(count($data)==2){
						$sql=$error=$s='';$placeholders=array();
						$sql="update  xrays_in_points_scheme set  points=:points where id=:id";
						$error="Unable to edit xrays in  points scheme";
						$placeholders[':points']=$old_points[$i];
						$placeholders[':id']=$data[1];
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}
				}
				$i++;
			}//end while for editing

			//now remove those that are marked for deletion
			if(!$exit_flag and isset($_POST['remove_procedure'])){

				$n=count($_POST['remove_procedure']);
				$remove_procedure=$_POST['remove_procedure'];
				$i=0;
				while($i < $n){
					//now update old entries

						$id=$encrypt->decrypt($remove_procedure[$i]);
						$data=explode('#',"$id");
						//this is for procedures
						if(count($data)==1){
							$sql=$error=$s='';$placeholders=array();
							$sql="delete from  procedures_in_points_scheme where id=:id";
							$error="Unable to remove  procedure fromn  points scheme";
							$placeholders[':id']=$id;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}//this is for xrays
						if(count($data)==2){
							$sql=$error=$s='';$placeholders=array();
							$sql="delete from  xrays_in_points_scheme where id=:id";
							$error="Unable to remove  xrays fromn  points scheme";
							$placeholders[':id']=$data[1];
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}

					$i++;
				}//end while for editing
			}
			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$message="good#loyalty_points#Changes saved";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#Unable to edit Lab Technicians  ";
		}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo $message;
}


//submit invoice authorisations
if(isset($_POST['token_inv_auth2']) and isset($_SESSION['token_inv_auth2']) and $_POST['token_inv_auth2']==$_SESSION['token_inv_auth2']
and userHasRole($pdo,57)){
			//check if token exists
		$sql=$error=$s='';$placeholders=array();
		$sql="select id from check_token where token_val=:token_val";
		$error="Unable to get token val";
		$placeholders['token_val']=$_POST['token_inv_auth2'];
		$s = select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			//update count time
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into check_token set token_val=:token_val, when_added=now()";
			$error="Unable to add 2 token val";
			$placeholders['token_val']=$_POST['token_inv_auth2'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			//echo "bad#token";

		}
		else{
		//echo "good#sss";
		//$_SESSION['token_inv_auth2']='';
		$exit_flag=$edit_invoice_flag=false;
		$n=0;
		if(isset($_POST['ninye'])){
			$invoice_id=$_POST['ninye'];
			$amount_authorised=$_POST['authorisation_received'];
			$authorisations_needed1=$_POST['authorisations_needed1'];
			$comments=$_POST['comments'];
			$n=count($invoice_id);
		}

		//$amount_authorised=$_POST['authorisation_received'];


		$i=0;
		$output='';
		//this will check that only numbers are used for amount and that amount authorised macthes amount requested else it will prompt for invoice edit
		while($i < $n){
				$invoice_id[$i]=$encrypt->decrypt($invoice_id[$i]);
				//check if both are empty skip
				if($amount_authorised[$i] == '' and $comments[$i] == ''){
					$i++;
					continue;
				}
				//check if comments have amount
				if($amount_authorised[$i] == '' and $comments[$i] != ''){
					$exit_flag=true;
					$message="bad#Please ensure an amount is specified for each comment entered";
					break;
				}
				//check if amount is valid number
				//remove commas
				$amount=str_replace(",", "", $amount_authorised[$i]);
				if(!ctype_digit($amount)){
					//check if it has only 2 decimal places
					$data=explode('.',$amount);
					$invalid_amount=html("$amount");
					if ( count($data) != 2 ){

					$message="bad#Unable to save changes as amount authorised $invalid_amount is not a 	valid number. ";
					$exit_flag=true;
					break;}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
					$message="bad#Unable to save changes as amount authorised $invalid_amount is not a 	valid number. ";
					$exit_flag=true;
					break;}
				}
				//now check if invoice needs to be edited
				//get cost of invoice
				//echo "<br>invoice id is $invoice_id[$i]";
				$sql=$error=$s='';$placeholders=array();
				$sql="sELECT tplan_procedure.invoice_id, tplan_procedure.invoice_number,
						sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested,
						 ifnull( sum(tplan_procedure.authorised_cost ),0) - ifnull( co_payment.amount, 0 ) AS amount_authorised
						FROM tplan_procedure  LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
						where invoice_id=:invoice_id
						GROUP BY invoice_id";
				//$sql="select sum(authorised_cost),invoice_number from tplan_procedure where invoice_id=:invoice_id group by invoice_id";
				$placeholders[':invoice_id']=$invoice_id[$i];
				$error="Unable to get cost of invoice";
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$cost=html($row['amount_requested']);
					$amount_authorised2=html($row['amount_authorised']);
					$invoice_number=html($row[1]);
				}



				/*//get cost of co-payment
				$co_payment=0;
				$sql=$error=$s='';$placeholders=array();
				$sql="select sum(amount) from co_payment where invoice_number=:invoice_id";
				$placeholders[':invoice_id']=$invoice_id[$i];
				$error="Unable to get cost of invoice co_payment";
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){$co_payment=html($row[0]);}

				$cost=$cost - $co_payment;*/
			//	echo "<br>invoice id is $invoice_id[$i] cost is $cost and amount is $amount";
				if($cost != $amount and $amount > 0 and $amount != $amount_authorised2){
					$edit_invoice_flag=true;
					$authorised_amount=number_format($amount, 2);
					$current_authorised_amount=number_format($amount_authorised2, 2);
					$current_cost=number_format($cost, 2);
					$token_value=form_token();
					$token = "".$invoice_id[$i]."#"."$token_value";
					$token=$encrypt->encrypt($token);
					//put this token in db to allow edit of the invoice
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into invoice_edit_token set token_value=:token , invoice_id=:invoice_id, when_added=now()";
					$placeholders[':token']=$token_value;
					$placeholders[':invoice_id']=$invoice_id[$i];
					$error="Unable to set invoice token";
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					if($amount_authorised2 > 0){
						$output="$output"."Invoice number $invoice_number has been authorised for $authorised_amount but is currently authorised for $current_authorised_amount. Please <a href='$token' class=edit_invoice>edit</a> the invoice amount first<br>";
					}
					else{
						$output="$output"."Invoice number $invoice_number has been authorised for $authorised_amount but  is currently costed for $current_cost. Please <a href='$token' class=edit_invoice>edit</a> the invoice amount first<br>";
					}
				}
				$i++;
		}
		if(!$exit_flag){
			$n=0;
			if(isset($_POST['ninye_smart'])){
				$invoice_id_smart=$_POST['ninye_smart'];
				$smart_run=$_POST['smart_run'];
				$n=count($invoice_id_smart);
			}

			$i=0;
			//this will check that only numbers are used for amount and that amount in smart run macthes amount requested else it will prompt for invoice edit
			while($i < $n){
					$invoice_id_smart[$i]=$encrypt->decrypt($invoice_id_smart[$i]);
					//check if both are empty skip
					if($smart_run[$i] == ''){
						$i++;
						continue;
					}

					//check if amount is valid number
					//remove commas
					$amount=str_replace(",", "", $smart_run[$i]);
					if(!ctype_digit($amount)){
						//check if it has only 2 decimal places
						$data=explode('.',$amount);
						$invalid_amount=html("$amount");
						if ( count($data) != 2 ){

						$message="bad#Unable to save changes as amount from smart $invalid_amount is not a 	valid number. ";
						$exit_flag=true;
						break;}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#Unable to save changes as amount from smart $invalid_amount is not a 	valid number. ";
						$exit_flag=true;
						break;}
					}
					//now check if invoice needs to be edited
					//get cost of invoice
					$sql=$error=$s='';$placeholders=array();
					$sql="sELECT tplan_procedure.invoice_id, tplan_procedure.invoice_number,
							sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested,
							 ifnull( sum(tplan_procedure.authorised_cost ),0) - ifnull( co_payment.amount, 0 ) AS amount_authorised
							FROM tplan_procedure  LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
							where invoice_id=:invoice_id
							GROUP BY invoice_id";
					//$sql="select sum(authorised_cost),invoice_number from tplan_procedure where invoice_id=:invoice_id group by invoice_id";
					$placeholders[':invoice_id']=$invoice_id_smart[$i];
					$error="Unable to get cost of invoice";
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$cost=html($row['amount_requested']);
						$amount_authorised2=html($row['amount_authorised']);
						$invoice_number=html($row[1]);
					}

					/*//get cost of co-payment
					$co_payment=0;
					$sql=$error=$s='';$placeholders=array();
					$sql="select sum(amount) from co_payment where invoice_number=:invoice_id";
					$placeholders[':invoice_id']=$invoice_id_smart[$i];
					$error="Unable to get cost of invoice co_payment";
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$co_payment=html($row[0]);}

					$cost=$cost - $co_payment;*/
				//	echo "<br>invoice id is $invoice_id[$i] cost is $cost and amount is $amount";
					if($cost != $amount and $amount > 0 and $amount != $amount_authorised2){
					//if($cost != $amount and $amount > 0){
						$edit_invoice_flag=true;
						$authorised_amount=number_format($amount, 2);
						$current_authorised_amount=number_format($amount_authorised2, 2);
						$current_cost=number_format($cost, 2);
						$token_value=form_token();
						$token = "".$invoice_id_smart[$i]."#"."$token_value";
						$token=$encrypt->encrypt($token);
						//put this token in db to allow edit of the invoice
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into invoice_edit_token set token_value=:token , invoice_id=:invoice_id, when_added=now()";
						$placeholders[':token']=$token_value;
						$placeholders[':invoice_id']=$invoice_id_smart[$i];
						$error="Unable to set invoice token";
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						if($amount_authorised2 > 0){
							$output="$output"."Invoice number $invoice_number has been authorised for $authorised_amount but is currently authorised for $current_authorised_amount. Please <a href='$token' class=edit_invoice>edit</a> the invoice amount first<br>";
						}
						else{
							$output="$output"."Invoice number $invoice_number has been authorised for $authorised_amount but  is currently costed for $current_cost. Please <a href='$token' class=edit_invoice>edit</a> the invoice amount first<br>";
						}
					}

					$i++;
			}
		}
		if(!$exit_flag and $edit_invoice_flag){
			$exit_flag=true;
			echo "bad#$output";
			exit;
		}
		if(!$exit_flag){
			try{
				$pdo->beginTransaction();
				//update authorisations sent
				if(isset($_POST['authorisation_sent'])){
					$auth_sent=$_POST['authorisation_sent'];
					$n=count($auth_sent);
					$i=0;
					while($i < $n){
						$invoice_id=$encrypt->decrypt($auth_sent[$i]);
						//check if the invoice exists
						$sql=$error=$s='';$placeholders=array();
						$sql="select invoice_id from invoice_authorisation	where invoice_id=:invoice_id";
						$placeholders[':invoice_id']=$invoice_id;
						$error="Unable to get authorisation sent for invoice";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						//if it exists
						if($s->rowCount() == 1){
						$sql=$error=$s='';$placeholders=array();
						$sql="update invoice_authorisation set authorisation_sent = now()	where invoice_id=:invoice_id";
						$placeholders[':invoice_id']=$invoice_id;
						$error="Unable to update authorisation sent for invoice";
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						}
						//if it does not exist
						elseif($s->rowCount() == 0){
							$sql=$error=$s='';$placeholders=array();
							$sql="insert into   invoice_authorisation set authorisation_sent = now(),  invoice_id=:invoice_id";
							$error="Unable to add authorisation sent for invoice";
							$placeholders[':invoice_id']=$invoice_id;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}
						$i++;
					}
				}

				//update authorisations received
				if(isset($_POST['authorisation_received'])){
					$auth_received=$_POST['authorisation_received'];
					$invoice_id_array=$_POST['ninye'];
					$comments=$_POST['comments'];
					$n=count($auth_received);
					$i=0;
					while($i < $n){
						if($amount_authorised[$i]==''){
							if(($i + 1) == $n){
								$message="";break;
							}
							$i++;
							continue;
						}

						$invoice_id=$encrypt->decrypt($invoice_id_array[$i]);
						//check if the invoice exists
						$sql=$error=$s='';$placeholders=array();
						$sql="select invoice_id from invoice_authorisation	where invoice_id=:invoice_id";
						$placeholders[':invoice_id']=$invoice_id;
						$error="Unable to get authorisation sent for invoice";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						//if it exists
						if($s->rowCount() == 1){
							//get cost of invoice
							$sql=$error=$s='';$placeholders=array();
							$sql="sELECT tplan_procedure.invoice_id, tplan_procedure.invoice_number,
									sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested,
									 ifnull( sum(tplan_procedure.authorised_cost ),0) - ifnull( co_payment.amount, 0 ) AS amount_authorised
									FROM tplan_procedure  LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
									where invoice_id=:invoice_id
									GROUP BY invoice_id";
							//$sql="select sum(authorised_cost),invoice_number from tplan_procedure where invoice_id=:invoice_id group by invoice_id";
							$placeholders[':invoice_id']=$invoice_id;
							$error="Unable to get cost of invoice";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){
								$cost=html($row['amount_requested']);
								$amount_authorised2=html($row['amount_authorised']);
								$invoice_number=html($row[1]);
							}

							$amount=str_replace(",", "", $amount_authorised[$i]);
							//check if authorisation is partiall or rejected
							if($cost > $amount){
								//check if smart is also needed
								$authorisations_needed1=$encrypt->decrypt("$authorisations_needed1[$i]");
								$data_array=array();
								$data_array=explode('#',"$authorisations_needed1");
								$pre_auth_needed1=$data_array[0];
								$smart_needed1=$data_array[1];
								$pre_auth_received1=$data_array[2];
								$smart_received1=$data_array[3];
								$pid1=$data_array[4];
								//commented out since pre-auth is of more importance than smart
								//if($smart_needed1=='NO' or ($smart_needed1=='YES' and $smart_received1=='YES')){
									//means i can treat this authorisation as final invoice cost edit and put it in invoice_admin_approval
									$amount1=number_format($amount,2);
									$cost1=number_format($cost,2);
									$sql=$error=$s='';$placeholders=array();
									$sql="insert into invoice_admin_approval set invoice_id=:invoice_id , pid=:pid, description=:description";
									$placeholders[':invoice_id']=$invoice_id;
									$placeholders[':pid']=$pid1;
									$placeholders[':description']="Invoice $invoice_number has been authorised for $amount1 but  is currently billed for $cost1";
									$error="Unable for invoice to be added for admin approval";
									$admin_approval_id = get_insert_id($sql, $placeholders, $error, $pdo);

									//get comment if any and add it
									if(isset($comments[$i]) and $comments[$i]!=''){
										$sql=$error=$s='';$placeholders=array();
										$sql="insert into invoice_admin_approval_communication set
											communication_id=:communication_id ,
											date_of_comment=now(),
											comment=:comment,
											user_id=:user_id";
										$placeholders[':communication_id']=$admin_approval_id;
										$placeholders[':comment']=html("$comments[$i]");
										$placeholders[':user_id']=$_SESSION['id'];
										$error="Unable for invoice to be added for admin approval comment";
										$s = insert_sql($sql, $placeholders, $error, $pdo);
									}
								//}
							}
							$sql=$error=$s='';$placeholders=array();
							$sql="update invoice_authorisation set authorisation_received=now(), amount_authorised=:amount_authorised,
									comments=:comments  where invoice_id=:invoice_id";
							$placeholders[':invoice_id']=$invoice_id;
							$placeholders[':comments']=$comments[$i];
							$placeholders[':amount_authorised']=$amount;
							$error="Unable to update authorisation sent for invoice";
							$s = insert_sql($sql, $placeholders, $error, $pdo);

							//now update authorised cost in tplan procedure


							//case 1 all cost is authorised
							if($cost == $amount){
								//update tplan_procedure
								$sql=$error=$s='';$placeholders=array();
								$sql="select treatment_procedure_id, unauthorised_cost from tplan_procedure
										where invoice_id=:invoice_id";
								$placeholders[':invoice_id']=$invoice_id;
								$error="Unable to update authorised invoice";
								$s = select_sql($sql, $placeholders, $error, $pdo);
								foreach($s as $row){
									$sql2=$error2=$s2='';$placeholders2=array();
									$sql2="update tplan_procedure set authorised_cost=:authorised_cost
											where treatment_procedure_id=:treatment_procedure_id";
									$placeholders2[':authorised_cost']=$row['unauthorised_cost'];
									$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
									$error2="Unable to update authorised invoice 2";
									$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);
								}
							}

							//case 2 request is completely declined
							elseif($amount == 0){
								//update tplan_procedure
								$sql=$error=$s='';$placeholders=array();
								$sql="select treatment_procedure_id, unauthorised_cost from tplan_procedure
										where invoice_id=:invoice_id";
								$placeholders[':invoice_id']=$invoice_id;
								$error="Unable to update authorised invoice";
								$s = select_sql($sql, $placeholders, $error, $pdo);
								foreach($s as $row){
									$sql2=$error2=$s2='';$placeholders2=array();
									$sql2="update tplan_procedure set authorised_cost=:authorised_cost
											where treatment_procedure_id=:treatment_procedure_id";
									$placeholders2[':authorised_cost']=0;
									$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
									$error2="Unable to update authorised invoice 2";
									$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);
								}
							}

							//case 3 request is partliay accepted
							//elseif($amount == $amount_authorised){}

						}
						//if it does not exist
						else{
							$message="bad#  Unable to save changes as authorised invoice is missing. Please contact support.";
							$exit_flag=true;
							break;
						}

						//updatye pt_balances
						//updatye pt_balances
								//first get bloody pid
								$sql2=$error2=$s2='';$placeholders2=array();
								$sql2="select pid from tplan_procedure where invoice_id=:invoice_id";
								$placeholders2[':invoice_id']=$invoice_id;
								$error2="Unable to update authorised invoice 2";
								$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
								foreach($s2 as $row2){
									$pid_encrypt2=$encrypt->encrypt($row2['pid']);
									$result1=show_pt_statement_brief($pdo,$pid_encrypt2,$encrypt);
								}
						$i++;
					}
				}

				//update smartcared run
				if(!$exit_flag and isset($_POST['smart_run'])){
					$invoice_id_smart_array=$_POST['ninye_smart'];
					$smart_run=$_POST['smart_run'];
					$authorisations_needed2=$_POST['authorisations_needed2'];
					$n=count($smart_run);
					$i=0;
					while($i < $n){
						if($smart_run[$i]==''){
							if(($i + 1) == $n){
								$message="";break;
							//$exit_flag=true;
							//break;
							}
							$i++;continue;}

						//get cost of invoice
						$invoice_id_smart=$encrypt->decrypt($invoice_id_smart_array[$i]);
						$sql=$error=$s='';$placeholders=array();
						$sql="sELECT tplan_procedure.invoice_id, tplan_procedure.invoice_number,
								sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested,
								 ifnull( sum(tplan_procedure.authorised_cost ),0) - ifnull( co_payment.amount, 0 ) AS amount_authorised
								FROM tplan_procedure  LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
								where invoice_id=:invoice_id
								GROUP BY invoice_id";

						//$sql="select sum(authorised_cost),invoice_number from tplan_procedure where invoice_id=:invoice_id group by invoice_id";
						$placeholders[':invoice_id']=$invoice_id_smart;
						$error="Unable to get cost of invoice";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$cost=html($row['amount_requested']);
							$amount_authorised2=html($row['amount_authorised']);
							$invoice_number=html($row[1]);
						}


						$amount=str_replace(",", "", $smart_run[$i]);
						//check if invoice is fully approved
							//check if authorisation is partiall or rejected
							if($cost > $amount){
								//check if smart is also needed
								$authorisations_needed2=$encrypt->decrypt("$authorisations_needed2[$i]");
								$data_array=array();
								$data_array=explode('#',"$authorisations_needed2");
								$pre_auth_needed1=$data_array[0];
								$smart_needed1=$data_array[1];
								$pre_auth_received1=$data_array[2];
								$smart_received1=$data_array[3];
								$pid1=$data_array[4];
								if($pre_auth_needed1=='NO' or ($pre_auth_needed1=='YES' and $pre_auth_received1=='YES')){
									//means i can treat this authorisation as final invoice cost edit and put it in invoice_admin_approval
									$amount1=number_format($amount,2);
									$cost1=number_format($cost,2);
									$sql=$error=$s='';$placeholders=array();
									$sql="insert into invoice_admin_approval set invoice_id=:invoice_id , pid=:pid, description=:description";
									$placeholders[':invoice_id']=$invoice_id_smart;
									$placeholders[':pid']=$pid1;
									$placeholders[':description']="Invoice $invoice_number has been authorised for $amount1 but  is currently billed for $cost1";
									$error="Unable for invoice to be added for admin approval";
									$admin_approval_id = get_insert_id($sql, $placeholders, $error, $pdo);

								}
							}
						//check if the invoice exists
						$sql=$error=$s='';$placeholders=array();
						$sql="select invoice_id from invoice_authorisation where invoice_id=:invoice_id";
						$placeholders[':invoice_id']=$invoice_id_smart;
						$error="Unable to get  smart run for invoice";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						if($s->rowCount() == 1){//invoice exists
							$sql=$error=$s='';$placeholders=array();
							$sql="update invoice_authorisation set smart_run = now(), smart_amount=:smart_amount
								 where invoice_id=:invoice_id";
							$placeholders[':invoice_id']=$invoice_id_smart;
							$placeholders[':smart_amount']=$amount;
							$error="Unable to update smart run for invoice";
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}
						elseif($s->rowCount() == 0){//invoice does not exists
							$sql=$error=$s='';$placeholders=array();
							$sql="insert into   invoice_authorisation set smart_run = now(),  invoice_id=:invoice_id,
								 smart_amount=:smart_amount ";
							$error="Unable to add smart run for invoice";
							$placeholders[':invoice_id']=$invoice_id_smart;
							$placeholders[':smart_amount']=$amount;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}

						//now update authorised cost in tplan procedure


							//case 1 all cost is authorised
							if($cost == $amount){
								//update tplan_procedure
								$sql=$error=$s='';$placeholders=array();
								$sql="select treatment_procedure_id, unauthorised_cost from tplan_procedure
										where invoice_id=:invoice_id";
								$placeholders[':invoice_id']=$invoice_id_smart;
								$error="Unable to update authorised invoice";
								$s = select_sql($sql, $placeholders, $error, $pdo);
								foreach($s as $row){
									$sql2=$error2=$s2='';$placeholders2=array();
									$sql2="update tplan_procedure set authorised_cost=:authorised_cost
											where treatment_procedure_id=:treatment_procedure_id";
									$placeholders2[':authorised_cost']=$row['unauthorised_cost'];
									$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
									$error2="Unable to update authorised invoice 2";
									$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);
								}
							}

							//case 2 request is completely declined
							elseif($amount == 0){
								//update tplan_procedure
								$sql=$error=$s='';$placeholders=array();
								$sql="select treatment_procedure_id, unauthorised_cost from tplan_procedure
										where invoice_id=:invoice_id";
								$placeholders[':invoice_id']=$invoice_id_smart;
								$error="Unable to update authorised invoice";
								$s = select_sql($sql, $placeholders, $error, $pdo);
								foreach($s as $row){
									$sql2=$error2=$s2='';$placeholders2=array();
									$sql2="update tplan_procedure set authorised_cost=:authorised_cost
											where treatment_procedure_id=:treatment_procedure_id";
									$placeholders2[':authorised_cost']=0;
									$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
									$error2="Unable to update authorised invoice 2";
									$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);
								}
							}

							//updatye pt_balances
								//first get bloody pid
								$sql2=$error2=$s2='';$placeholders2=array();
								$sql2="select pid from tplan_procedure where invoice_id=:invoice_id";
								$placeholders2[':invoice_id']=$invoice_id_smart;
								$error2="Unable to update authorised invoice 2";
								$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
								foreach($s2 as $row2){
									$pid_encrypt2=$encrypt->encrypt($row2['pid']);
									$result1=show_pt_statement_brief($pdo,$pid_encrypt2,$encrypt);
								}
						$i++;
					}
				}


				if(!$exit_flag){$tx_result = $pdo->commit();}
				elseif($exit_flag){$pdo->rollBack();$tx_result=false;}
				if($tx_result){
					$message="good#authorise_invoice#Changes saved";
					$_SESSION['token_inv_auth2']='';
					//insert token into table and check if unique
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into check_token set token_val=:token_val, when_added=now()";
					$error="Unable to add token val";
					$placeholders['token_val']=$_POST['token_inv_auth2'];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
				}
			}
			catch (PDOException $e)
			{
			$pdo->rollBack();
			//$message="bad#Unable to edit Lab Technicians  ";
			}
		}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2] ";
		}
		echo "$message ";
		}
}

//submit points per minute or add new procedure to cover
if(isset($_POST['token_loyal1']) and isset($_SESSION['token_loyal1']) and $_POST['token_loyal1']==$_SESSION['token_loyal1'] and userHasRole($pdo,42)){
		$exit_flag=false;
		//update points per minute
		//check if value is specified
		if(!isset($_POST['points_per_time']) or $_POST['points_per_time']==''){
			$exit_flag=true;
			$message="bad#Please specify a value for points gained per minute. If you don't want points to be awarded then please type the number zero";
		}

		//check if value is integer
		if(!$exit_flag){
			if(!ctype_digit($_POST['points_per_time'])){//echo "ooooo $unit_price[$i] ";
				//check if it has only 2 decimal places
				$data=explode('.',$_POST['points_per_time']);
				$invalid_value=html($_POST['points_per_time']);
				if ( count($data) != 2 ){

				$message="bad#Points per minute specified, $invalid_value is not a valid number. ";
				$exit_flag=true;
				}
				elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
				$message="bad#Points per minute specified, $invalid_value is not a valid number. ";
				$exit_flag=true;
				}
			}
		}
			//echo "eee-$_POST[points_per_procedure]-$_POST[procedure_added]";
		//check if procedure and it's points are both specified and not just one
		if( $_POST['procedure_added']=='' and (isset($_POST['points_per_procedure']) or $_POST['points_per_procedure']!='')){
			$exit_flag=true;
			$message="bad#Please select a procedure";
		}
		if((!isset($_POST['points_per_procedure']) or $_POST['points_per_procedure']=='') and (isset($_POST['procedure_added']) and $_POST['procedure_added']!='')){
			$exit_flag=true;
			$message="bad#Please specify points needed to cover the selected a procedure";
		}
		try{
			$pdo->beginTransaction();
			//update points per minute table
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from points_per_time";
			$error="Unable to delete points per time table";
			$s = insert_sql($sql, $placeholders, $error, $pdo);

			$sql=$error=$s='';$placeholders=array();
			$sql="insert into   points_per_time set points=:points";
			$error="Unable to add points per time table";
			$placeholders[':points']=$_POST['points_per_time'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);

			//check if there a procedure to add to the scheme as well
			if(isset($_POST['points_per_procedure']) and $_POST['points_per_procedure']!='' and
			isset($_POST['procedure_added']) and $_POST['procedure_added']!=''){
				//check if points is an inetegr
				if(!ctype_digit($_POST['points_per_procedure'])){//echo "ooooo $unit_price[$i] ";
					//check if it has only 2 decimal places
					$data=explode('.',$_POST['points_per_procedure']);
					$invalid_value=html($_POST['points_per_procedure']);
					if ( count($data) != 2 ){

					$message="bad#The points specified, $invalid_value , for the selected procedure is not a valid number. ";
					$exit_flag=true;
					}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
					$message="bad#The points specified, $invalid_value , for the selected procedure is not a valid number. ";
					$exit_flag=true;
					}
				}
				//perform addition
				if(!$exit_flag  ){
					$procedure_id=$encrypt->decrypt($_POST['procedure_added']);
					//check if procedure is array
					$data=explode('#',$procedure_id);
					//check normal procedure
					if(count($data)==1){
						if(!in_array($procedure_id, $_SESSION['procedures_array'])){
							$message="bad#Unable to add procedure to points scheme, please try again";
							$exit_flag=true;
						}

						if(!$exit_flag){
							$sql=$error=$s='';$placeholders=array();
							$sql="insert into  procedures_in_points_scheme set procedure_id=:procedure_id, points=:points";
							$error="Unable to add procedure to points scheme";
							$placeholders[':points']=$_POST['points_per_procedure'];
							$placeholders[':procedure_id']=$procedure_id;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}
					}

				}
			}
			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$message="good#loyalty_points#Changes saved";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#Unable to edit Lab Technicians  ";
		}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo $message;
}

//add xray referrer
elseif(isset($_SESSION['token_xray_ref_1']) and isset($_POST['token_xray_ref_1'])  and
 $_SESSION['token_xray_ref_1']==$_POST['token_xray_ref_1'] and userHasRole($pdo,27)){
			//$_SESSION['token']='';
	$exit_flag=false;
	if(!isset($_POST['ref_name']) or $_POST['ref_name']==''){
		$exit_flag=true;
		$message="bad#Referrer's name must be specified";
	}
	//check email format
	$email_address=html($_POST['email_address']);
	if(!$exit_flag and isset($_POST['email_address']) and $_POST['email_address']!=''){
		if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
			$message="bad#Unable to save details as the email $email_address  is not correctly specified. ";
			$exit_flag=true;
		}
	}


	//empty the unset ones
	if(!isset($_POST['email_address']))  {$_POST['email_address']='';}
	if(!isset($_POST['telephone_no'])) {$_POST['telephone_no']='';}

	//check thata the referrers is not entered twice
	if(!$exit_flag){
		$sql=$error=$s='';$placeholders=array();
		$sql="select referrer_name from xray_refering_doc where upper(referrer_name)=:name";
		$error="Unable to get referrer name";
		$placeholders[':name']=strtoupper($_POST['ref_name']);
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount()>0){
			$name=html($_POST['ref_name']);
			$message="bad#Unable to add referrer $name as that name already exists";
		}
		else{
			//insert xray referrer value
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into xray_refering_doc set referrer_name=:name, telephone=:telephone, email_address=:email";
			$error="Unable to add xray refferer";
			$placeholders[':name']=$_POST['ref_name'];
			$placeholders[':telephone']=$_POST['telephone_no'];
			$placeholders[':email']=$_POST['email_address'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#add_referrer#X-ray referrer  added ";}
				elseif(!$s){$message="bad#Unable to add X-ray referrer ";}
		}
	}
			$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
	echo "$message";
}

//this is for submitting  patient dental information
elseif(isset($_SESSION['token_1b_patinet']) and 	isset($_POST['token_1b_patinet']) and $_POST['token_1b_patinet']==$_SESSION['token_1b_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,13)){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag ){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	global $exit_flag;

//sleep(5);
	function check_yes_no($parameter){
		//check bleeding
		global $pdo, $message;
		if("$parameter" !='yes' and "$parameter" !='no'  ){
			$message="bad#Unable to save details as some parameters may not be properly set. Please recheck the Yes/No values";
			$var=html("$parameter");
			$security_log="sombody tried to input $var into patient_dental for a yes no value";
			log_security($pdo,$security_log);
			return true;
		}
		else{return false;}
	}

	if(!$exit_flag and isset($_POST['gums'])) {$exit_flag=check_yes_no($_POST['gums']);} else {$_POST['gums']='';}
	if(!$exit_flag and isset($_POST['orthodontic'])) {$exit_flag=check_yes_no($_POST['orthodontic']);} else {$_POST['orthodontic']='';}
	if(!$exit_flag and isset($_POST['sensitive'])) {$exit_flag=check_yes_no($_POST['sensitive']);} else {$_POST['sensitive']='';}
	if(!$exit_flag and isset($_POST['headaches'])) {$exit_flag=check_yes_no($_POST['headaches']);} else {$_POST['headaches']='';}
	if(!$exit_flag and isset($_POST['periodontal'])) {$exit_flag=check_yes_no($_POST['periodontal']);} else {$_POST['periodontal']='';}
	if(!$exit_flag and isset($_POST['appliances'])) {$exit_flag=check_yes_no($_POST['appliances']);} else {$_POST['appliances']='';}
	if(!$exit_flag and isset($_POST['difficulty'])) {$exit_flag=check_yes_no($_POST['difficulty']);} else {$_POST['difficulty']='';}



	//empty the unset ones
	if(!isset($_POST['gums']))  {$_POST['gums']='';}
	if(!isset($_POST['orthodontic'])) {$_POST['orthodontic']='';}
	if(!isset($_POST['sensitive']))  {$_POST['sensitive']='';}
	if(!isset($_POST['headaches']))  {$_POST['headaches']='';}
	if(!isset($_POST['periodontal']))  {$_POST['periodontal']='';}
	if(!isset($_POST['appliances']))  {$_POST['appliances']='';}
	if(!isset($_POST['difficulty']))  {$_POST['difficulty']='';}

	//chreck date of last exam
	if(!$exit_flag and isset($_POST['date_last_exam']) and $_POST['date_last_exam']!='')	{
		$date='';
		$date=explode('-',$_POST['date_last_exam']);
		if(!checkdate( $date[1],$date[2],$date[0] )){
		$date_last_exam=html($_POST['date_last_exam']);
		$message="bad#Unable to save details as date of last examination $date_last_exam is not in the correct format";
		$exit_flag=true;
		$security_log="somebody tried to input $date_last_exam as date of last examintaion for patient_dental";
		log_security($pdo,$security_log);
		}
	}

	//chreck date of last xray
	if(!$exit_flag and isset($_POST['date_of_last_xray']) and $_POST['date_of_last_xray']!='')	{
		$date='';
		$date=explode('-',$_POST['date_of_last_xray']);
		if(!checkdate( $date[1],$date[2],$date[0] )){
		$date_of_last_xray=html($_POST['date_of_last_xray']);
		$message="bad#Unable to save details as date of last examination $date_of_last_xray is not in the correct format";
		$exit_flag=true;
		$security_log="somebody tried to input $date_of_last_xray as date of last examintaion for patient_dental";
		log_security($pdo,$security_log);
		}
	}

	if(!$exit_flag){
		try{
			$pdo->beginTransaction();

			//now delete old record
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from patient_dental where pid=:pid";
			$error="Unable to update patient dental form";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			//print_r($_POST);
			//now update with new details
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_dental set
					gums_bleed=:gums_bleed,
					sensitive_teeth=:sensitive_teeth,
					periodontal=:periodontal,
					when_added=now(),
					braces=:braces,
					aches=:aches,
					removeable=:removeable,
					prev_ye_no=:prev_ye_no,
					prev=:prev,
					curr=:curr,
					last_dental=:last_dental,
					last_xray=:last_xray,
					done1=:done1,
					appearance=:appearance,
					history_complain=:history_complain,
					medical_history=:medical_history,
					chief_complain=:chief_complain,
					pid=:pid
					";
			$error="Unable to update medical patient form";
			$placeholders[':gums_bleed']=$_POST['gums'];
			$placeholders[':sensitive_teeth']=$_POST['sensitive'];
			$placeholders[':periodontal']=$_POST['periodontal'];
			$placeholders[':braces']=$_POST['orthodontic'];
			$placeholders[':aches']=$_POST['headaches'];
			$placeholders[':removeable']=$_POST['appliances'];
			$placeholders[':prev_ye_no']=$_POST['difficulty'];
			$placeholders[':prev']=$_POST['serious_difficulty'];
			$placeholders[':curr']=$_POST['dental_problem'];
			$placeholders[':last_dental']=$_POST['date_last_exam'];
			$placeholders[':last_xray']=$_POST['date_of_last_xray'];
			$placeholders[':done1']=$_POST['what_was_done'];
			$placeholders[':appearance']=$_POST['feel'];
			$placeholders[':history_complain']=$_POST['history'];
			$placeholders[':medical_history']=$_POST['med'];
			$placeholders[':chief_complain']=$_POST['chief'];

			$placeholders[':pid']=$_SESSION['pid'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#Patient details saved. ";}
			elseif(!$s){$message="bad#Unable to save patient details ";}

			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save patient details  ";
		}
	}
		echo "$message";

}


//this is for submitting treatment plans
elseif(isset($_SESSION['token_h_patient']) and 	isset($_POST['token_h_patient']) and $_POST['token_h_patient']==$_SESSION['token_h_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''  and userHasRole($pdo,19)){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}

	//check appointment number is a valid number
	if(!$exit_flag and (!isset($_POST['alias_invoice']) or $_POST['alias_invoice']!='alias_invoice')){
		if($_POST["appointment_number"]=='' ){
			$message="bad#Unable to save treatment plan as number of appointments is not specified";
			$exit_flag=true;

			}
			//check if  is integer
		if(!$exit_flag and !ctype_digit($_POST["appointment_number"])){//echo "ooooo $unit_price[$i] ";
			//check if it has only 2 decimal places
			$data=explode('.',$_POST["appointment_number"]);
			$invalid_amount=html("$_POST[appointment_number]");
			if ( count($data) != 2 ){

			$message="bad#Unable to save treatment plan as $invalid_amount is not a
			valid number of appointments. ";
			$exit_flag=true;
			}
			elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
			$message="bad#Unable to save treatment plan as cost $invalid_amount is not a
			valid number of appointments. ";
			$exit_flag=true;
			}
		}
	}


	$procedure_name_array=$procedure_array=$all_teeth=array();
	$pre_auth_needed=$smart_needed='';
	//check if pre-auth or smart is needed for this patient
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

//	global $exit_flag ,$procedure_array ,$all_teeth ;

	//get current procedures
	$sql=$error1=$s='';$placeholders=array();
	$sql="select id,name,all_teeth from procedures";
	$error="Unable to get procedures";
	$s = select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$procedure_array[]=$row['id'];
		$all_teeth[]=$row['all_teeth'];
		$procedure_name_array[]=html($row['name']);
	}

	function check_procedure($procedure, $teeth_specified){
		global $pdo, $message,$procedure_array ,$all_teeth, $procedure_name_array, $exit_flag;

		$n2=count($procedure_array);
		$i2=0;
		if($teeth_specified==''){$teeth_count=0;}
		elseif($teeth_specified!=''){$teeth_count=count($teeth_specified);}
		while($i2 < $n2){
			if($procedure == $procedure_array[$i2]){ //check if procedure is in array
				//now check if teeth are properly specified
				if($all_teeth[$i2]=='yes' and $teeth_count > 0){return true;}
				elseif($all_teeth[$i2]=='yes' and $teeth_count == 0){
					$message="bad#Unable to save treatment plan, it appears that teeth have not been specified for
					$procedure_name_array[$i2]. Please specify the teeth that the procedure will be performed on.";
					$exit_flag=true;
					return false;
				}
				elseif($all_teeth[$i2]=='no' and $teeth_count > 0){
					$message="bad#Unable to save treatment plan, it appears that teeth have been incorrectly specified for
					$procedure_name_array[$i2].";
					$exit_flag=true;
					return false;
				}
				elseif($all_teeth[$i2]=='no' and $teeth_count == 0){return true;}
			}
			$i2++;
		}
	}

	function check_payment_method($parameter){
		global $pdo, $message;
		if("$parameter" !='1' and "$parameter" !='2' and "$parameter" !='3' ){
			$message="bad#Unable to save treatment plan as payment option is not correctly set";
			$var=html("$parameter");
			$security_log="sombody tried to input $var into payment option for treatment procedure";
			log_security($pdo,$security_log);
			$exit_flag=true;
			return false;
		}
		else{return true;}
	}


	if(!$exit_flag){
		try{
			$pdo->beginTransaction();
			$tplan_total_cost=0;
			$none_invoice=false;
			//insert into  tplan_id_generator
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into tplan_id_generator set when_added=now(), pid=:pid, created_by=:user_id";
			$error="Unable to create treatment plan";
			$placeholders[':pid']=$_SESSION['pid'];
			$placeholders[':user_id']=$_SESSION['id'];
			//$placeholders[':pid']=$_SESSION['pid'];
			$tplan_id = get_insert_id($sql, $placeholders, $error, $pdo);

			//insert tplan visits
			if(!isset($_POST['alias_invoice']) or $_POST['alias_invoice']!='alias_invoice'){
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into tplan_visits set
					visits_planned=:visits_planned,
					pid=:pid,
					tplan_id=:tplan_id,
					visits_remaining=:visits_planned,
					cleared_by_admin=:cleared_by_admin
					";
				$error="Unable to save treatment plan";
				$placeholders[':pid']=$_SESSION['pid'];
				$placeholders[':tplan_id']=$tplan_id;
				$placeholders[':visits_planned']=$_POST["appointment_number"];
				if($_POST["appointment_number"]<5){$placeholders[':cleared_by_admin']=0;}
				else{$placeholders[':cleared_by_admin']=1;}
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			}

			if(isset($_POST['xrays']) and $_POST['xrays']!=''){
				//insert any appended xrays
				$n=count($_POST['xrays']);
				$i=0;
				$xrays=$_POST['xrays'];
				while($i < $n){
					$xray_id=$encrypt->decrypt($xrays[$i]);
					//get the xray details
					$sql=$error=$s='';$placeholders=array();
					$sql="select * from xray_holder where id=:id";
					$error="Unable to get xray from holder";
					$placeholders[':id']=$xray_id;
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						/*//get x-ray names
						$xrays2=explode(',',$row['xrays_done']);
						$i2=0;
						$n2=count($xrays2);
						$xrays_done='';
						while($i2 < $n2){
							$name=html($_SESSION['xray_names_array'][$xrays2[$i2]]);
							if($i2==0){$xrays_done="$name";}
							else{$xrays_done="$xrays_done \n $name";}
							$i2++;
						}*/
						$xrays_done=html("$row[xrays_done] $row[teeth]");
						//now insert it into tplan_procedure table
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="insert tplan_procedure set
								tplan_id=:tplan_id,
								procedure_id=:procedure_id,
							 xray_comments=:details,
							 teeth=:teeth,
							  unauthorised_cost=:unathorised_cost,
							  pay_type=:pay_type,
							  authorised_cost=:authorised_cost,
							  pid=:pid,
							  created_by=:created_by,
							 date_procedure_added=:date_procedure_added,
							 number_done=:number_done,
							  status=2;
								";
						$error2="Unable to add xrays to tplan";
						$placeholders2[':pid']=$_SESSION['pid'];
						$placeholders2[':tplan_id']=$tplan_id;
						$placeholders2[':procedure_id']=$row['xrays_done'];
						$placeholders2[':details']=$row['xray_comments'];
						if($row['teeth']!=''){
							$teeth_array=explode(',',$row['teeth']);
							$placeholders2[':number_done']=count($teeth_array);
							$placeholders2[':teeth']=$row['teeth'];
						}
						elseif($row['teeth']==''){
							$placeholders2[':number_done']=1;
							$placeholders2[':teeth']=$row['teeth'];
						}
						$placeholders2[':date_procedure_added']=$row['date_taken'];
						$placeholders2[':unathorised_cost']=$row['cost'];
						$placeholders2[':pay_type']=$row['pay_type'];
						$placeholders2[':authorised_cost']=$row['authorised_cost'];
						$placeholders2[':created_by']=$row['doc_id'];
						$treatment_procedure_id = 	get_insert_id($sql2, $placeholders2, $error2, $pdo);
						$tplan_total_cost = $tplan_total_cost + $row['cost'];
						if($row['pay_type'] != 1){$none_invoice = true;}


					}

					//now delete xray from xray_holder
					$sql=$error=$s='';$placeholders=array();
					$sql="delete  from xray_holder where id=:id";
					$error="Unable to delete xray from holder";
					$placeholders[':id']=$xray_id;
					$s = insert_sql($sql, $placeholders, $error, $pdo);

					$i++;
				}
			}


			//insert diagnosis
			$n=count($_POST['diagnosis']);
			$diagnosis=$_POST['diagnosis'];
			$complaint=$_POST['complaint'];
			$i=0;
			while($i < $n){
				if($diagnosis[$i]==''){$i++;continue;}
				$sql=$error=$s='';$placeholders=array();
				$sql="insert tplan_diagnosis set
					tplan_id=:tplan_id,
					pid=:pid,
					complaint=:complaint,
					diagnosis=:diagnosis
					";
				$error="Unable to save treatment plan";
				$placeholders[':tplan_id']=$tplan_id;
				$placeholders[':diagnosis']=$diagnosis[$i];
				$placeholders[':pid']=$_SESSION['pid'];
				$placeholders[':complaint']=$complaint[$i];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);
				$i++;
			}

			//update ptag
			$sql=$error=$s='';$placeholders=array();
			$sql="update patient_details_b set tag=:tag where pid=:pid";
			$error="Unable to save treatment plan";
			$placeholders[':pid']=$_SESSION['pid'];;
			$placeholders[':tag']=$_POST['pt_tag'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			//now loop and insert treatment procedures
			$n=count($_POST['nisiana']);
			$i=1;
			$n22=0;
			$insert_id_array=array();
			$array_string='';

			while($i <= $n){
				if($exit_flag){ break;}
				//check selected procedure is valid
				$procedure="procedure$i";
				$teeth_specified="teeth_specified$i";
				$pay_method="pay_method$i";
				$cost="cost$i";
				$details="details$i";
				$tplan_alias="tplan_alias$i";
				//$discount="discount$i";
				if($_POST["$procedure"]==''){
					$n22++;
					$i++;
					//echo "n is $n and n22 is $n22";
					if($n22 == $n and count($_POST['xrays'])==0 ){$exit_flag=true;$message="bad#Please specify the procedure to be done";}
					continue;
				}
				else{
					//echo "procedure is ".$_POST["$procedure"];
				//	echo "i is $i";
					$meno=$amount=$discount_amout='';
					$procedure_id=$encrypt->decrypt($_POST["$procedure"]);
					//echo "xxxxx";
					if(!isset($_POST["$teeth_specified"])){$_POST["$teeth_specified"]='';}
					$result=check_procedure($procedure_id,$_POST["$teeth_specified"]);
					//echo "result is $result";
					if(!$result){ break;}
					else{
						if($_POST["$teeth_specified"]!=''){
							$meno='';
							$teeth=$_POST["$teeth_specified"];

							$n2=count($teeth);

							$i2=0;

							while($i2 < $n2){
						//	echo "xxx$i2 xxx$teeth[$i2]xxx".$encrypt->decrypt($teeth[$i2])."xxxxx";
								//check that meno is a valid teeth number

								if($i2==0){$meno=$encrypt->decrypt($teeth[$i2]);}
								else{$meno="$meno,".$encrypt->decrypt($teeth[$i2]);}
								if (!in_array($encrypt->decrypt($teeth[$i2]), $_SESSION['meno_yote'])) {
									$message="bad#Unable to save treatment plan as some teeth values are not correctly set";
									$var=html($encrypt->decrypt($teeth[$i2]));
									$security_log="sombody tried to input $var into treatment procedure as a tooth value";
									log_security($pdo,$security_log);
									$exit_flag=true;
									break;
								}
								$i2++;
							}

						}
						else{//set number of procedure done to 1
							$n2=1;
						}
					}
				//	echo"tttttttttttt";
					//check payment method is valid
					if(!$exit_flag){
				//	echo "pay $i is ".$_POST["$pay_method"];
					//	echo "ccccccc ".$_POST["$pay_method"]."".$encrypt->decrypt($_POST["$pay_method"])."xxxxxi2";
						if($_POST["$pay_method"]==''){
							$message="bad#Unable to save treatment plan as payment option is not correctly set";
							$exit_flag=true;
							break;
						}
					//	echo "ccccccc ".$_POST["$pay_method"]."".$encrypt->decrypt($_POST["$pay_method"])."xxxxxi2";
						$pay_method_id=$encrypt->decrypt($_POST["$pay_method"]);
						$result=check_payment_method($pay_method_id);
						if(!$result){ break;}
						//check of there is any none insureance payment mehtod, this will be used for raising alias invoices
						if($pay_method_id != 1){$none_invoice = true;}

					}



					//check cost is a valid number
					if(!$exit_flag){
						if($_POST["$cost"]==''){
							$message="bad#Unable to save treatment plan as cost is not specified";
							$exit_flag=true;
							break;
							}
						//remove commas
						$amount=str_replace(",", "", $_POST["$cost"]);
							//check if amount is integer
						if(!ctype_digit($amount)){//echo "ooooo $unit_price[$i] ";
							//check if it has only 2 decimal places
							$data=explode('.',$amount);
							$invalid_amount=html("$amount");
							if ( count($data) != 2 ){

							$message="bad#Unable to save treatment plan as cost $invalid_amount is not a
							valid number. ";
							$exit_flag=true;
							break;}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$message="bad#Unable to save treatment plan as cost $invalid_amount is not a
							valid number. ";
							$exit_flag=true;
							break;}
						}
					}



					//set authorised cost for cash and point
					//set authorised cost to empty if insured else make it equal to unauthorised
							if($pay_method_id==1){
								if($pre_auth_needed=='YES' or $smart_needed=='YES'){$authorised_cost=NULL;}
								elseif($pre_auth_needed!='YES' and $smart_needed!='YES'){$authorised_cost=$amount;}
							}
							else{$authorised_cost=$amount;}


					//check cost is a valid number
					/*if(!$exit_flag and isset($_POST["$discount"]) and $_POST["$discount"]!=''){
						//remove commas
						$discount_amout=str_replace(",", "", $_POST["$discount"]);
							//check if amount is integer
						if(!ctype_digit($discount_amout)){//echo "ooooo $unit_price[$i] ";
							//check if it has only 2 decimal places
							$data=explode('.',$discount_amout);
							$invalid_amount=html("$discount_amout");
							if ( count($data) != 2 ){

							$message="bad#Unable to save treatment plan as discount $invalid_amount is not a
							valid number. ";
							$exit_flag=true;
							break;}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$message="bad#Unable to save treatment plan as dicount $invalid_amount is not a
							valid number. ";
							$exit_flag=true;
							break;}
						}
					}*/
					//insert
					//if(!$exit_flag and $pay_method_id!=1){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert tplan_procedure set
							tplan_id=:tplan_id,
							procedure_id=:procedure_id,
						  teeth=:meno,
						  details=:details,
						  unauthorised_cost=:unathorised_cost,
						  pay_type=:pay_type,
						  authorised_cost=:authorised_cost,
						  number_done=:number_done,
						  created_by=:created_by,
						  pid=:pid,
						  alias=:alias,
						  date_procedure_added=now();
							";
						$error="Unable to save treatment plan";
						$placeholders[':pid']=$_SESSION['pid'];
						$placeholders[':tplan_id']=$tplan_id;
						$placeholders[':procedure_id']=$procedure_id;
						$placeholders[':meno']="$meno";
						$placeholders[':details']=$_POST["$details"];
						$placeholders[':unathorised_cost']=$amount;
						$placeholders[':pay_type']=$pay_method_id;
						$placeholders[':authorised_cost']=$authorised_cost;
						$placeholders[':created_by']=$_SESSION['id'];
						if(isset($_POST["$tplan_alias"]) and $_POST["$tplan_alias"]!=''){$placeholders[':alias']=1;}
						else{$placeholders[':alias']=0;}
						//add number of selected procedure to be done
						if($n2 > 0){$placeholders[':number_done']=$n2;}
						else{$placeholders[':number_done']=1;}
						$sid = 	get_insert_id($sql, $placeholders, $error, $pdo);

					//}
					//check if points are needed
					if($pay_method_id==3){
						$result=pay_treatment_in_points($pdo,$_SESSION['pid'],$amount,$procedure_id);

						if($result=='good'){
							//make payment
							$receipt_number='';
							$rid=0;
							//first get receipt number for non insured payment
							$sql=$error=$s='';$placeholders=array();
							$sql="select max(receipt_num) from non_insurance_receipt_id_generator";
							$error="Unable to get non insured receipt number";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){$rid=$row[0] + 1;}
							if($rid == 0){$rid = 1;}

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into non_insurance_receipt_id_generator set receipt_num =:rid";
							$error="Unable to get non insured receipt number";
							$placeholders[':rid']=$rid;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
							$receipt_number="R$rid-".date('m/y');

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into payments set when_added=now(), receipt_num=:receipt_num,
								amount=:amount,
								pay_type=8,
								pid=:pid,
								treatment_procedure_id=:treatment_procedure_id,
								created_by=:created_by";
							$error="Unable to make non-insured payment";
							$placeholders[':receipt_num']="$receipt_number";
							$placeholders[':amount']=$amount;
							$placeholders[':pid']=$_SESSION['pid'];
							$placeholders[':treatment_procedure_id']=$sid;
							$placeholders[':created_by']=$_SESSION['id'];
							$s = insert_sql($sql, $placeholders, $error, $pdo);



						}
						else{
							$message="$result ";
							$exit_flag=true;
							break;
						}

					}
					$tplan_total_cost = $tplan_total_cost + $amount;
					//if($array_string == ''){$array_string=" '$i'=>$sid ";}
					//else{$array_string="$array_string , '$i'=>$sid ";}

					//$insert_id_array[$i]=$sid;
				}
				$i++;
			}

			//check if the invoice is aliased
			if(isset($_POST['alias_invoice']) and $_POST['alias_invoice']=='alias_invoice'){
				//ensure all payment menthods are invoice
				if($none_invoice){
					$exit_flag = true;
					$message="bad#For an alias invoice all payment types in the treatment plan must be 'Insurance'. ";
				}
				else{
					//check cost of invoiced procedures against dailiy invoice limit
					$sql=$error=$s='';$placeholders=array();
					$sql="select sum(unauthorised_cost) from tplan_procedure where tplan_id=:tplan_id";
					$placeholders[':tplan_id']=$tplan_id;
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$inv_total2 = $row[0];}

					//check against daily invoice limit
					$invoice_daily_limit=0;
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select invoice_daily_limit from  covered_company where id=:covered_company";
					$error2="Unable to update treatment procedure invoice number";
					$placeholders2[':covered_company']=$_SESSION['company_covered'];
					$s2= select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2){$invoice_daily_limit = $row2['invoice_daily_limit'];}

					if($invoice_daily_limit == 0){
						//check if insurer has daily limit
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select invoice_daily_limit from  insurance_company where id=:patient_type";
						$error2="Unable to update treatment procedure invoice number";
						$placeholders2[':patient_type']=$_SESSION['type'];
						$s2= select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){$invoice_daily_limit = $row2['invoice_daily_limit'];}
					}
					$daily_invoice_limit_error='';
					if($invoice_daily_limit > 0 and $inv_total2  > $invoice_daily_limit){
						$daily_invoice_limit_error = " <br>Also please note that the daily invoice limit for this corporate of KES ".number_format($invoice_daily_limit,2)." is exceeded by the invoice your raising of KES ".number_format($inv_total2,2)."<br>";
					}

					//check for admin password
					if(isset($_POST['admin_password']) and  $_POST['admin_password']!='' ){
						//now check if it matches a user with administrator privilege
						$admin_p=hash_hmac('sha1', $_POST['admin_password'], $salt);
						$sql=$error=$s='';$placeholders=array();
						$sql="select a.id from users a, privileges b where a.id=b.user_id and b.menu_id=109 and
						a.password=:password limit 1";
						$error="Unable to get administartor user";
						$placeholders['password']="$admin_p";
						$s = select_sql($sql, $placeholders, $error, $pdo);

						if($s->rowCount() == 0){ //if privilege not found check in roles
							$sql=$error=$s='';$placeholders=array();
							$sql="SELECT d.id FROM users d, user_roles c, role_privileges b WHERE d.id = c.user_id
							AND c.role_id = b.role_id aND b.menu_id =109 AND d.password = :password limit 1";
							$error="Unable to get administartor user";
							$placeholders['password']="$admin_p";
							$s = select_sql($sql, $placeholders, $error, $pdo);
						}
						if($s->rowCount() == 0){
							$message= "bad#tdone_admin_pass#Wrong administrator password provided";
							$exit_flag=true;

						}


					}
					else{
						$message="bad#tdone_admin_pass#Alias invoices require Admin password. $daily_invoice_limit_error Please provide Admin password to continue";
						$exit_flag=true;

					}

					if(!$exit_flag){
						//get invoice number
						$sql=$error=$s='';$placeholders=array();
						$sql="select max(invoice_num_id) from invoice_number_generator";
						$error="Unable to get moalsrs pt invoice number";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){$inv_id=$row[0] + 1;}
						if($inv_id == 0){$inv_id = 1;}

						$sql=$error=$s='';$placeholders=array();
						$sql="insert into invoice_number_generator set invoice_num_id =:inv_id";
						$error="Unable to get moalsrs pt invoice number";
						$placeholders[':inv_id']=$inv_id;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						$invoice_num="I$inv_id-".date("m/y");


						//get unique invoice id
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="insert into unique_invoice_number_generator set pid=:pid, invoice_number=:invoice_number,
							when_raised=now(),added_by=:added_by, aliased=1";
							$placeholders2[':pid']=$_SESSION['pid'];
							$placeholders2[':invoice_number']="$invoice_num";
							$placeholders2[':added_by']=$_SESSION['id'];
						$error2="Unable to update unique invoice number generator";
						$invoice_id = get_insert_id($sql2, $placeholders2, $error2, $pdo);

						//update tplan with invoice number and set status to cmoplete
						$sql=$error=$s='';$placeholders=array();
						$sql="update tplan_procedure set invoice_number=:invoice_number, date_invoiced=now(), invoice_id=:invoice_id , status = 2, procedure_in_alias_invoice=1 where tplan_id=:tplan_id";
						$error="Unable to update treatment procedure invoice number";
						$placeholders[':invoice_number']="$invoice_num";
						$placeholders[':invoice_id']=$invoice_id;
						$placeholders[':tplan_id']=$tplan_id;
						//$placeholders[':invoiced_by']=$_SESSION['id'];
						$s = insert_sql($sql, $placeholders, $error, $pdo);

						//now raise co-payment
						$sql=$error=$s='';$placeholders=array();
						$sql="select co_pay_type,value from covered_company  where id=:covered_company and insurer_id=:insurer_id";
						$error="Unable to get co-payments for invoice";
						$placeholders[':covered_company']=$_SESSION['company_covered'];
						$placeholders[':insurer_id']=$_SESSION['type'];
						$s = select_sql($sql, $placeholders, $error, $pdo);
						$deduction='';
						foreach($s as $row){
							if($row['co_pay_type']=="CASH") {$deduction=$row['value'];}
							elseif($row['co_pay_type']=="PERCENTAGE") {
								//get sum for the invoice
								$deduction=ceil(($row['value'] * $inv_total2)/100)*100;
							}
							if($deduction!=''){
								//check inf the co-payment for this invoice already exists
								$sql2=$error2=$s2='';$placeholders2=array();
								$sql2="delete from co_payment where invoice_number=:invoice_number";
								$error2="Unable to delete invoice  co-payments";
								$placeholders2[':invoice_number']=$invoice_id;
								$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);

								//now insert new co-payment value
								$sql2=$error2=$s2='';$placeholders2=array();
								$sql2="insert into co_payment set invoice_number=:invoice_number, amount=:amount";
								$error2="Unable to add invoice  co-payments";
								$placeholders2[':invoice_number']=$invoice_id;
								$placeholders2[':amount']=$deduction;
								$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);
							}
						}
					}
				}
			}

			//$insert_id_array[]=array("$array_string");
			//$key_list=array_keys($insert_id_array);
			//foreach($insert_id_array as $row){
				//echo $row['1'];
			//}
			//print_r($key_list);
			//echo " mm $array_string mmm";
			//echo $insert_id_array['1']."   nn";
			//now insret aliases
			/*$aliases=$_POST['aliasest'];
			$n=count($aliases);
			$i=0;
			print_r($insert_id_array);
			while($i < $n){
				$var=$encrypt->decrypt("$aliases[$i]");
				echo "--$var--";
				$data=explode('#',"$var");
				$invoice_tproc=$data[0];
				$aliased_tproc=$data[1];
				echo $insert_id_array["$invoice_tproc"]." -- ".$insert_id_array["$aliased_tproc"];
				$i++;
			}*/


			if(!$exit_flag){
				$tx_result = $pdo->commit();
				$message="good#treatment_plan_reload#Treatment plan saved. ";
				$_SESSION['result_class']='success_response';
				$_SESSION['result_message']="Treatment plan saved.";
				get_patient($pdo,'patient_number',$_SESSION['patient_number']);
				}
			elseif($exit_flag){$pdo->rollBack();}

			//$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save treatment plan  ";
		}
	}

		echo "$message";

}


//edit insured companies
elseif( isset($_POST['ninye']) and $_POST['ninye']!='' and isset($_POST['token_corporate_1']) and $_POST['token_corporate_1']!=''
	and $_SESSION['token_corporate_1'] == $_POST['token_corporate_1'] and userHasRole($pdo,10)){
	//print_r($_POST);
	//save entries
	$n=count($_POST['ninye']);
	$tt=$_POST['ninye'];
	$n=count($tt);
	//echo "tn is $n";
	$insured_yes_no=$_POST['insured_yes_no'];
	$company_id=$_POST['ninye'];
	$ins_id=$_POST['old_ins'];
	$pre_auth_needed=$_POST['old_pre_auth'];
	$smart_needed=$_POST['old_smart'];
	$co_pay_type=$_POST['old_co_pay'];
	$co_pay_val=$_POST['co_pay_val'];
	$start_cover=$_POST['start_cover'];
	$end_cover=$_POST['end_cover'];
	$cover_type=$_POST['cover_type'];
	$cover_limit=$_POST['cover_limit'];
	$i=0;
	$exit_flag=false;
	try{
		$pdo->beginTransaction();
			while($i < $n){


					//decrypt compnay id and check that exist
					$company_id[$i]=$encrypt->decrypt($company_id[$i]);
					$sql=$error=$s='';$placeholders=array();
					$sql="select name from covered_company where id=:id";
					$error="Unable to check if insured company exists";
					$placeholders[':id']=$company_id[$i];
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					if($s->rowCount() > 0){
						foreach($s as $row){$comp_name=html($row['name']);}
					}
					else{
								$message="bad# Unable to save changes, this error has been logged";
								//call function to log this activity
								$message2="an update of $company_id[$i] was attemped into covered_company table for column company_id";
								log_security($pdo,$message2);
								$exit_flag=true;
								break;
					}

				//check if insurer is propoerly set
				if(!$exit_flag and $insured_yes_no[$i]=='YES' and $ins_id[$i]==''){$message="bad# $comp_name is set as insured but no insurer has been specified";
													$exit_flag=true;
													break;
				}
				if(!$exit_flag and $insured_yes_no[$i]=='NO' and isset($ins_id[$i]) and $ins_id[$i]!=''){$message="bad# $comp_name is set as not insured but an insurer has been specified";
													$exit_flag=true;
													break;
				}






				//ensure all fields are correctly set
				/*	if($ins_id[$i]==''){
						//check if pre-auth is set
						if($pre_auth_needed[$i]=='YES'){$message="bad# Unable to save changes, Pre-Auth needed has been set to YES for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if smart is set
						if($smart_needed[$i]=='YES'){$message="bad# Unable to save changes, SMart Check Needed  has been set to YES for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if co_pay is set
						if($co_pay_type[$i]!=''){		$co_pay=html("$co_pay_type[$i]");
														$message="bad# Unable to save changes, Co-Pay Type has been set to $co_pay for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if co_pay_val is set
						if($co_pay_val[$i]!=''){$co_pay_amount=html("$co_pay_val[$i]");
												$message="bad# Unable to save changes, Co-Pay Value has been set to $co_pay_amount for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if start_cover is set
						if($start_cover[$i]!=''){$start=html("$start_cover[$i]");
						$message="bad# Unable to save changes, Start Cover has been set to $start for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if end cover is set
						if($end_cover[$i]!=''){$end=html("$end_cover[$i]");
						$message="bad# Unable to save changes, End Cover has been set to $end for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if cover_type is set
						if($cover_type[$i]!=''){$cover_t=html("$cover_type[$i]");
													$message="bad# Unable to save changes, Cover Type has been set to $cover_t for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if cover limit
						if($cover_limit[$i]!=''){$cover_l=html("$cover_limit[$i]");
													$message="bad# Unable to save changes, Cover Limit has been set to $cover_l for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}

					}*/
					//this ios for when insurer is specified
					if(!$exit_flag and isset($ins_id[$i]) and $ins_id[$i]!=''){

						//now check cover limit

						if(isset($cover_limit[$i]) and $cover_limit[$i]!=''){
							$cover_limit[$i]=str_replace(",", "", "$cover_limit[$i]");
							if( !ctype_digit($cover_limit[$i])){
								//check if it has only 2 decimal places
								$data=explode('.',$cover_limit[$i]);
								if ( count($data) != 2 ){
									$cover_limit[$i]=html("$cover_limit[$i]");
									$message="bad# Unable to save changes for $comp_name as $cover_limit[$i] is not a valid number ";
									$exit_flag=true;
									break;
								}
								elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
									$cover_limit[$i]=html("$cover_limit[$i]");
									$message="bad# Unable to save changes for $comp_name as $cover_limit[$i] is not a valid number ";
									$exit_flag=true;
									break;
								}
							}
						}
						else{$cover_limit[$i]='';}

						//now check start and end date
						$data=explode("-",$start_cover[$i]);
						if(!$exit_flag and isset($start_cover[$i]) and $start_cover[$i]!='' and !checkdate($data[1], $data[2], $data[0])){
								$start_cover[$i]=html("$start_cover[$i]");
								$message="bad# Unable to save changes for $comp_name as $start_cover[$i] is not a valid date ";
								$exit_flag=true;
								break;
							}
						$data=explode("-",$end_cover[$i]);
						if(!$exit_flag and isset($end_cover[$i]) and $end_cover[$i]!='' and !checkdate($data[1], $data[2], $data[0])){
								$end_cover[$i]=html("$end_cover[$i]");
								$message="bad# Unable to save changes for $comp_name as $end_cover[$i] is not a valid date ";
								$exit_flag=true;
								break;
							}

						//check if pre-auth is set
						if(!$exit_flag and $pre_auth_needed[$i]==''){$message="bad# Unable to save changes, Pre-Auth needed has not been set  for $comp_name yet
														the company is insured";
														$exit_flag=true;
														$message2="an attempt has been made to make pre-auth needed empty for $comp_name in table covered_company";
														log_security($pdo,$message2);
														break;
						}
						//check if smart is set
						if(!$exit_flag and $smart_needed[$i]==''){$message="bad# Unable to save changes, Smart Check needed has not been set  for $comp_name yet
														the company is insured";
														$exit_flag=true;
														$message2="an attempt has been made to make smart check run needed empty for $comp_name in table covered_company";
														log_security($pdo,$message2);
														break;
						}
						//check if co_pay is set
						if(!$exit_flag and $co_pay_type[$i]!='' and $co_pay_val[$i]==''){		$co_pay=html("$co_pay_type[$i]");
														$message="bad# Unable to save changes, Co-Pay Type has been set to $co_pay for $comp_name but
														but no corresponding value has been set";
														$exit_flag=true;
														break;
						}
						//check if co_value is set
						if(!$exit_flag and $co_pay_type[$i]=='' and $co_pay_val[$i]!=''){		$co_pay_amount=html("$co_pay_val[$i]");
														$message="bad# Unable to save changes, Co-Pay Value  has been set to $co_pay_amount for $comp_name but
														but no corresponding Co-Pay Type  has been set";
														$exit_flag=true;
														break;
						}
						//check if start_cover is set
						if(!$exit_flag and $start_cover[$i]==''){$start=html("$start_cover[$i]");
						$message="bad# Unable to save changes, as Start Cover date has not been set  for $comp_name though the company is insured";
														$exit_flag=true;
														break;
						}
						//check if end cover is set
						if(!$exit_flag and $end_cover[$i]==''){$end=html("$end_cover[$i]");
						$message="bad# Unable to save changes, as End Cover date has not been set  for $comp_name though the company is insured";
														$exit_flag=true;
														break;
						}
						if(!$exit_flag and $end_cover[$i] < $start_cover[$i]){$end=html("$end_cover[$i]");$start=html("$start_cover[$i]");
						$message="bad# Unable to save changes, the end cover date of $end is before the start cover date of $start  for $comp_name.";
														$exit_flag=true;
														break;
						}
						//check if cover_type is set
						if(!$exit_flag and $cover_type[$i]==''){$cover_t=html("$cover_type[$i]");
													$message="bad# Unable to save changes, as Cover Type has not been set for $comp_name.";
														$exit_flag=true;
														break;
						}
						//check if cover limit
						if(!$exit_flag and $cover_limit[$i]==''){$cover_l=html("$cover_limit[$i]");
													$message="bad# Unable to save changes, as Cover Limit has not been set  for $comp_name";
														$exit_flag=true;
														break;
						}

						//remove commas if they were used for formating
						$co_pay_val[$i]=str_replace(",", "", "$co_pay_val[$i]");
						if(!$exit_flag and isset($co_pay_val[$i]) and $co_pay_val[$i]!='' and !ctype_digit($co_pay_val[$i])){
							//check if it has only 2 decimal places
							$data=explode('.',$co_pay_val[$i]);
							if ( count($data) != 2 ){
								$co_pay_val[$i]=html("$co_pay_val[$i]");
								$message="bad# Unable to save changes as $co_pay_val[$i] is not a valid number ";
								$exit_flag=true;
								break;
							}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
								$co_pay_val[$i]=html("$co_pay_val[$i]");
								$message="bad# Unable to save changes as $co_pay_val[$i] is not a valid number ";
								$exit_flag=true;
								break;
							}
						}

						if(!$exit_flag and isset($ins_id[$i]) and $ins_id[$i]!=''){
							//decrypt insurance compnay id and check that exist
							$ins_id[$i]=$encrypt->decrypt($ins_id[$i]);
							$sql=$error=$s='';$placeholders=array();
							$sql="select id from insurance_company where id=:id";
							$error="Unable to check if insurance company exists";
							$placeholders[':id']=$ins_id[$i];
							$s = 	select_sql($sql, $placeholders, $error, $pdo);
							if((0 + $s->rowCount()) ==  0){
										$message="bad# Unable to save changes, this error has been logged";
										//call function to log this activity
										$message2="an update of $ins_id[$i] was attemped into covered_company table for column insurer_id";
										log_security($pdo,$message2);
										$exit_flag=true;
										break;
							}
						}

					}
					// start by validating input
					//check i fvalue for co_pay is valid number



				if(!$exit_flag and $insured_yes_no[$i]=='YES' ){
					$sql=$error=$s='';$placeholders=array();
					$sql="update covered_company set  insurer_id=:ins_id, 	co_pay_type=:co_pay_type ,	value=:value ,	pre_auth_needed=:pre_auth,
						smart_needed=:smart_needed, 	start_cover=:start_cover, 	end_cover=:end_cover, 	cover_type=:cover_type,
						cover_limit=:cover_limit, insured=:insured_yes_no where id=:id";
					$error="Unable to edit insured companies";
					$placeholders[':ins_id']=$ins_id[$i];
					$placeholders[':co_pay_type']="$co_pay_type[$i]";
					$placeholders[':value']="$co_pay_val[$i]";
					$placeholders[':pre_auth']="$pre_auth_needed[$i]";
					$placeholders[':smart_needed']="$smart_needed[$i]";
					$placeholders[':start_cover']="$start_cover[$i]";
					$placeholders[':end_cover']="$end_cover[$i]";
					$placeholders[':cover_limit']="$cover_limit[$i]";
					$placeholders[':cover_type']="$cover_type[$i]";
					$placeholders[':insured_yes_no']="$insured_yes_no[$i]";
					$placeholders[':id']=$company_id[$i];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
				}
				elseif(!$exit_flag and $insured_yes_no[$i]=='NO' ){
					$sql=$error=$s='';$placeholders=array();
					$sql="update covered_company set  insurer_id=0, 	co_pay_type='' ,	value='' ,	pre_auth_needed='NO',
						smart_needed='NO', 	start_cover='', 	end_cover='', 	cover_type='',
						cover_limit='',  insured='NO' where id=:id";
					$error="Unable to edit insured companies";
					$placeholders[':id']=$company_id[$i];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					//echo "<br>$company_id[$i]--echo <br>$comp_name";
					//now delete uncovered procedures
					$sql=$error=$s='';$placeholders=array();
					$sql="delete from procedures_not_covered where company_id=:company_id";
					$error="Unable to edit insured companies";
					$placeholders[':company_id']=$company_id[$i];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);

				}






					//if(!$s ){break;$message="bad#Unable to save changes";}
					$i++;
			}
			//echo "i is $i ";

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			if($tx_result){$message="good# Insured Companies Edited  ";}
			//elseif(!$tx_result){$error_message="   Unable to edit Insured Companies  ";}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();

	}
	echo "$message";
}


//this is for submitting female patient details
elseif(isset($_SESSION['token_1d_patinet']) and 	isset($_POST['token_1d_patinet']) and $_POST['token_1d_patinet']==$_SESSION['token_1d_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,15)){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag ){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	global $exit_flag;

//sleep(5);
	function check_yes_no($parameter){
		//check bleeding
		global $pdo, $message;
		if("$parameter" !='yes' and "$parameter" !='no'  ){
			$message="bad#Unable to save details as some parameters may not be properly set. Please recheck the Yes/No values";
			$var=html("$parameter");
			$security_log="sombody tried to input $var into patient_women for a yes no value";
			log_security($pdo,$security_log);
			return true;
		}
		else{return false;}
	}

	if(!$exit_flag and isset($_POST['pregnant'])) {$exit_flag=check_yes_no($_POST['pregnant']);} else {$_POST['pregnant']='';}
	if(!$exit_flag and isset($_POST['nursing'])) {$exit_flag=check_yes_no($_POST['nursing']);} else {$_POST['nursing']='';}
	if(!$exit_flag and isset($_POST['control'])) {$exit_flag=check_yes_no($_POST['control']);} else {$_POST['control']='';}
	if(!$exit_flag and isset($_POST['orthopedic'])) {$exit_flag=check_yes_no($_POST['orthopedic']);} else {$_POST['orthopedic']='';}
	if(!$exit_flag and isset($_POST['complications'])) {$exit_flag=check_yes_no($_POST['complications']);} else {$_POST['complications']='';}
	if(!$exit_flag and isset($_POST['recommended'])) {$exit_flag=check_yes_no($_POST['recommended']);} else {$_POST['recommended']='';}

	//empty the unset ones
	if(!isset($_POST['pregnant']))  {$_POST['pregnant']='';}
	if(!isset($_POST['nursing'])) {$_POST['nursing']='';}
	if(!isset($_POST['control'])) {$_POST['control']='';}
	if(!isset($_POST['orthopedic']))  {$_POST['orthopedic']='';}
	if(!isset($_POST['complications'])) {$_POST['complications']='';}
	if(!isset($_POST['recommended'])) {$_POST['recommended']='';}

	//chreck opeartion date isa  date
	if(!$exit_flag and isset($_POST['done']) and $_POST['done']!='')	{
		$date='';
		$date=explode('-',$_POST['done']);
		if(!checkdate( $date[1],$date[2],$date[0] )){
		$done=html($_POST['done']);
		$message="bad#Unable to save details as date of orthopedic operation $done is not in the correct format";
		$exit_flag=true;
		$security_log="somebody tried to input $done as date of orthopedic operation for patient_women";
		log_security($pdo,$security_log);
		}
	}

	if(!$exit_flag){
		try{
			$pdo->beginTransaction();

			//now delete old record
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from patient_women where pid=:pid";
			$error="Unable to update female patient form";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			//print_r($_POST);
			//now update with new details
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_women set
				pid=:pid,
			  when_added=now(),
			  pregnant=:pregnant,
			  nursing=:nursing,
			  control=:control,
			  pjoint=:pjoint,
			  pwhen=:pwhen,
			  complication=:complication,
			  antibiotics=:antibiotics,
			  dose=:dose,
			  pname=:pname,
			  pphone=:pphone";
			$error="Unable to update female patient form";
			$placeholders[':pregnant']=$_POST['pregnant'];
			$placeholders[':nursing']=$_POST['nursing'];
			$placeholders[':control']=$_POST['control'];
			$placeholders[':pjoint']=$_POST['orthopedic'];
			$placeholders[':pwhen']=$_POST['done'];
			$placeholders[':complication']=$_POST['complications'];
			$placeholders[':antibiotics']=$_POST['recommended'];
			$placeholders[':dose']=$_POST['antibiotic'];
			$placeholders[':pname']=$_POST['Name'];
			$placeholders[':pphone']=$_POST['Phone'];
			$placeholders[':pid']=$_SESSION['pid'];
			//$placeholders[':when_added']=now();
			//print_r($placeholders);
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#Patient details saved. ";}
			elseif(!$s){$message="bad#Unable to save patient details ";}

			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save patient details  ";
		}
	}
		echo "$message";

}

//this is for selecting a treatment plan
elseif(isset($_SESSION['token_g_patient']) and 	isset($_POST['token_g_patient']) and $_POST['token_g_patient']==$_SESSION['token_g_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,20)){
	$_SESSION['tplan_id']=$encrypt->decrypt($_POST['ninye']);
	echo "good#treatment-done";
}

//this is for returning to patient allocation from t-done isset($_SESSION['token_g3_patient']) and
elseif( isset($_POST['token_g3_patient']) and  isset($_SESSION['token_g3_patient'])
	and $_POST['token_g3_patient']==$_SESSION['token_g3_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,20))
	{//echo "ppp $_POST[token_g3_patient] ppp $_SESSION[pid]";
	//echo "ppp $_SESSION[token_g3_patient] ppp<br> ";
	if(isset($_POST['treatment_status']) and $_POST['treatment_status']!=''){
		$allocation_id=$encrypt->decrypt($_POST['ninye']);





		$sql=$error=$s='';$placeholders=array();
		if($_POST['treatment_status']=='hold'){$sql="update patient_allocations set pause_treatment=now(), treatment_status=2 where id=:allocation_id";}
		elseif($_POST['treatment_status']=='finish'){
			//check if treatment is finished and treatment is still paused
			$sql3=$error3=$s3='';$placeholders3=array();
			$sql3="SELECT pause_treatment, resume_treatment FROM patient_allocations WHERE id=:allocation_id ";
			$error3="Unable to alloaction id";
			$placeholders3[':allocation_id']=$allocation_id;
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			foreach($s3 as $row){
				if($row['pause_treatment'] > '0000-00-00 00:00:00' and
					$row['resume_treatment'] == '0000-00-00 00:00:00')
				{
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="update patient_allocations set resume_treatment=now() where id=:allocation_id";
					$error2="Unable to edit treatment status in waiting list ";
					$placeholders2[':allocation_id']=$allocation_id;
					$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
				}
			}
			$sql="update patient_allocations set treatment_finish=now(), treatment_status=3 where id=:allocation_id";
		}
		else{echo "good#treatment-done-allocation";exit;}
		$error="Unable to edit treatment status in waiting list ";
		$placeholders[':allocation_id']=$allocation_id;
		$s = 	insert_sql($sql, $placeholders, $error, $pdo);


		if($s){echo "good#return_to_allocation";}
		elseif(!$s){echo 'bad#Unable to update patient allocations';}
	}
	elseif(isset($_POST['ninye2']) and $_POST['ninye2']!=''){//no pid in allocation so just go to allocations
		echo "good#return_to_allocation";
	}

	//serial is token_g3_patient=fd67668a9d6faee4b1d686fdd4c4b59e80abe34c&ninye2=ninye2
}

//this is for submitting patient completion
elseif(userIsLoggedIn() and isset($_SESSION['token_f_patient']) and 	isset($_POST['token_f_patient']) and $_POST['token_f_patient']==$_SESSION['token_f_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,17)){
	//$_SESSION['token_f_patient']='';

		try{
			$pdo->beginTransaction();



			//now delete old record
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from patient_completion where pid=:pid";
			$error="Unable to update patient completion form";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);

			//now update with new details
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_completion set pid=:pid, when_added=now(), comments=:comments, significant=:significant,
					management=:management";
			$error="Unable to update patient completion form";
			$placeholders[':comments']=$_POST['commebts'];
			$placeholders[':significant']=$_POST['Significant'];
			$placeholders[':management']=$_POST['dental'];
			$placeholders[':pid']=$_SESSION['pid'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#Patient details saved. ";}
			elseif(!$s){$message="bad#Unable to save Patient details ";}

			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save patient details  ";
		}
		echo "$message";

}


//this is for submitting treatment done
elseif(isset($_SESSION['token_g2_patient']) and 	isset($_POST['token_g2_patient']) and $_POST['token_g2_patient']==$_SESSION['token_g2_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!='' and userHasRole($pdo,20)){
	//$_SESSION['token_f_patient']='';
	$count=$encrypt->decrypt($_POST['nisiana']);
	$count2=$count;
	$exit_flag=false;

	//$token = form_token();
	$_SESSION['token_g3_patient'] = '';
	$_SESSION['token_g3_patient'] = form_token();


	//check if the patient has been swapped
	if(!$exit_flag ){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	//check if the patient has been swapped
	if(!$exit_flag ){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
		try{
			$pdo->beginTransaction();
			$i=1;
			$existing_new=$existing_quote_new='';
			while($i <= $count){
				$note="note$i";
				$status="status$i";
				$raise_quotation="raise_quotation$i";
				$raise_invoice="raise_invoice$i";
				$append_invoice="append_invoice$i";
				$append_quotation="append_quotation$i";
				$procedure_number="procedure$i";
				$change_to_cash="change_to_cash$i";

				//change payment status if set
				if(isset($_POST["$change_to_cash"]) and $_POST["$change_to_cash"]!=''){
					$sql=$error=$s='';$placeholders=array();
					$sql="update tplan_procedure set pay_type=2 where treatment_procedure_id=:treatment_procedure_id";
					$error="Unable to update treatment procedure pay type";
					$placeholders[':treatment_procedure_id']=$encrypt->decrypt("$_POST[$change_to_cash]");
					$s = insert_sql($sql, $placeholders, $error, $pdo);
				}
				//insert status if any
				if(isset($_POST["$status"]) and $_POST["$status"]!=''){
				//echo "status is $_POST[$status] ";
					$treatment_procedure_id=$encrypt->decrypt($_POST["$procedure_number"]);
					$procedure_status=$encrypt->decrypt($_POST["$status"]);
					//check if procedure status has changed
					$sql=$error=$s='';$placeholders=array();
					$sql="select status from tplan_procedure where treatment_procedure_id=:treatment_procedure_id";
					$error="Unable to get procedure status";
					$placeholders[':treatment_procedure_id']=$treatment_procedure_id;
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						if($row['status'] != $procedure_status and $procedure_status==1){
						//insert status change into procedure notes when proedure is started
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into treatment_procedure_notes set treatment_procedure_id=:treatment_procedure_id,
								when_added=now(), doc_id=:doc_id, notes='Treatment started', status=:status , pid=:pid";
							$error2="Unable to update treatment procedure notes for starting treatment";
							$placeholders2[':treatment_procedure_id']=$treatment_procedure_id;
							$placeholders2[':doc_id']=$_SESSION['id'];
							$placeholders2[':status']=$procedure_status;
							$placeholders2[':pid']=$_SESSION['pid'];
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);

							/*$pid_bal="pid_".$_SESSION['pid'];
							if(isset($_SESSION["$pid_bal"])){unset($_SESSION["$pid_bal"]);}
							$_SESSION["$pid_bal"]=array();
							$result=show_pt_statement_brief($pdo,$encrypt->encrypt("$_SESSION[pid]"),$encrypt);
							$data=explode('#',"$result");
							$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
							echo "x $pid_bal x";
							print_r($_SESSION["$pid_bal"]);*/
						}
						elseif($row['status'] != $procedure_status and $procedure_status==2){
						//insert status change into procedure notes when proedure is finished
							$sql2b=$error2b=$s2b='';$placeholders2b=array();
							$sql2b="insert into treatment_procedure_notes set treatment_procedure_id=:treatment_procedure_id,
								when_added=now(), doc_id=:doc_id, notes='Treatment finished', status=:status, pid=:pid";
							$error2b="Unable to update treatment procedure notes for starting treatment";
							$placeholders2b[':treatment_procedure_id']=$treatment_procedure_id;
							$placeholders2b[':doc_id']=$_SESSION['id'];
							$placeholders2b[':status']=$procedure_status;
							$placeholders2b[':pid']=$_SESSION['pid'];

						}
					}

					$sql=$error=$s='';$placeholders=array();
					$sql="update tplan_procedure set status=:status where treatment_procedure_id=:treatment_procedure_id";
					$error="Unable to update treatment procedure status";
					$placeholders[':treatment_procedure_id']=$treatment_procedure_id;
					$placeholders[':status']=$procedure_status;
					if($procedure_status!=0 and $procedure_status!=1 and $procedure_status!=2){
								$var=html("$procedure_status");
								$security_log="sombody tried to input $var into tplan procedure as a procedure  status";
								log_security($pdo,$security_log);
								$message="bad#Unable to update procedure due to unverified procedure status.";
								$exit_flag=true;
								break;
					}
					$s = insert_sql($sql, $placeholders, $error, $pdo);
				}

				//insert comment if any
				//echo "note is $_POST[$note] ";
				if(isset($_POST["$note"]) and $_POST["$note"]!=''){
					$treatment_procedure_id=$encrypt->decrypt($_POST["$procedure_number"]);

					$sql=$error=$s='';$placeholders=array();
					$sql="insert into treatment_procedure_notes set treatment_procedure_id=:treatment_procedure_id,
						when_added=now(), doc_id=:doc_id, notes=:notes, status=:status, pid=:pid";
					$error="Unable to update treatment procedure notes";
					$placeholders[':treatment_procedure_id']=$treatment_procedure_id;
					$placeholders[':doc_id']=$_SESSION['id'];
					$placeholders[':notes']=$_POST["$note"];
					$placeholders[':status']=$procedure_status;
					$placeholders[':pid']=$_SESSION['pid'];
					$s = insert_sql($sql, $placeholders, $error, $pdo);
				}

				//insert for finished treatment status
				if(isset($_POST["$status"]) and $_POST["$status"]!=''){
					if($procedure_status==2){
						$s2b = insert_sql($sql2b, $placeholders2b, $error2b, $pdo);
						//check if tplan is finshed and clear pending visits for it
						$sql=$error=$s='';$placeholders=array();
						$sql="select tplan_id from  tplan_procedure where treatment_procedure_id=:treatment_procedure_id";
						$error="Unable to get tplan";
						$placeholders[':treatment_procedure_id']=$treatment_procedure_id;
						$s = select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){$tplan_id=$row['tplan_id'];}

						$sql=$error=$s='';$placeholders=array();
						$sql="select treatment_procedure_id from  tplan_procedure where tplan_id=:tplan_id and status!=2";
						$error="Unable to get tplan status";
						$placeholders[':tplan_id']=$tplan_id;
						$s = select_sql($sql, $placeholders, $error, $pdo);
						if($s->rowCount() > 0){
							//still has some unfinished treamens
						}
						else{//all treatments are fnished so clear any pending visits for the tplan
							$sql=$error=$s='';$placeholders=array();
							$sql="update tplan_visits set visits_remaining=0 where tplan_id=:tplan_id";
							$error="Unable to update tplan pending visist";
							$placeholders[':tplan_id']=$tplan_id;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}

					}
				}
				//insert invocie number  if any
				if(isset($_POST["$raise_invoice"]) and $_POST["$raise_invoice"]!=''){
				//	echo "invoice is $_POST[$raise_invoice] ";
					$treatment_procedure_id=$encrypt->decrypt($_POST["$procedure_number"]);
					$invoice_type=$encrypt->decrypt($_POST["$raise_invoice"]);
					//echo"invoice_type is $invoice_type";
					if($invoice_type=="new" and $existing_new==''){//raise new invoice number
						//check if the treatment is to be appended to an invoice
						if($_POST["$append_invoice"]==''){
							//check cost of procedures against dailiy invoice limit
							/*$i2=1;
							$total_cost2=0;
							while($i2 <= $count2){
								$raise_invoice2="raise_invoice$i2";
								$procedure_number2="procedure$i2";
								if(isset($_POST["$raise_invoice2"]) and $_POST["$raise_invoice2"]!=''){
									$treatment_procedure_id2=$encrypt->decrypt($_POST["$procedure_number2"]);
									//get cost of this procedure
									$sql=$error=$s='';$placeholders=array();
									$sql="select unauthorised_cost from tplan_procedure where treatment_procedure_id=:treatment_procedure_id";
									$error="Unable to get moalsrs pt invoice number";
									$placeholders[':treatment_procedure_id']=$treatment_procedure_id2;
									$s = select_sql($sql, $placeholders, $error, $pdo);
									foreach($s as $row){$total_cost2  = $total_cost2  + $row['unauthorised_cost'];}
								}
								$i2++;

							}
							$message="bad#$total_cost2";
							echo "$message";
								$exit_flag=true;
								break;*/

							/*$sql=$error=$s='';$placeholders=array();
							$sql="SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'dental_new' AND
								TABLE_NAME = 'invoice_number_generator'";
							$error="Unable to generate new invoice number";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){
								$invoice_num="I$row[0]-".date("m/y");
								$existing_new="$invoice_num";

							}*/
							//first get invocie number for molars pt
							$sql=$error=$s='';$placeholders=array();
							$sql="select max(invoice_num_id) from invoice_number_generator";
							$error="Unable to get moalsrs pt invoice number";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){$inv_id=$row[0] + 1;}
							if($inv_id == 0){$inv_id = 1;}

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into invoice_number_generator set invoice_num_id =:inv_id";
							$error="Unable to get moalsrs pt invoice number";
							$placeholders[':inv_id']=$inv_id;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
							$invoice_num="I$inv_id-".date("m/y");
							$existing_new="$invoice_num";

							/*//insert into invoice_generator_table
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into invoice_number_generator set pid=:pid";
							$error2="Unable to update invoice number generator";
							$placeholders2[':pid']=$_SESSION['pid'];
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);	*/

							//get unique invoice id
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into unique_invoice_number_generator set pid=:pid, invoice_number=:invoice_number,
								when_raised=now(),added_by=:added_by";
								$placeholders2[':pid']=$_SESSION['pid'];
								$placeholders2[':invoice_number']="$invoice_num";
								$placeholders2[':added_by']=$_SESSION['id'];
							$error2="Unable to update unique invoice number generator";
							$invoice_id = get_insert_id($sql2, $placeholders2, $error2, $pdo);
						}
						else{//append to invoice
							$result=$encrypt->decrypt("$_POST[$append_invoice]");
							$data=explode('#',"$result");
							$invoice_num="$data[0]";
							$invoice_id="$data[1]";
						}
					}
					elseif($invoice_type=="new" and $existing_new!=''){//use newly created invoice number
						$invoice_num="$existing_new";
					}
					else{//cehck if old invoice exists
						$sql=$error=$s='';$placeholders=array();
						$sql="SELECT invoice_number, invoice_id from tplan_procedure where invoice_number=:invoice_number";
						$error="Unable to verify old invoice number for insertion into tplan_procedure";
						$placeholders[':invoice_number']="$invoice_type";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						if($s->rowCount()>0){
							$invoice_num="$invoice_type";
							foreach($s as $row){
								$invoice_id=html($row['invoice_id']);
							}
						}
						else{
								$var=html("$invoice_type");
								$security_log="sombody tried to input $var into tplan procedure as an invocie number";
								log_security($pdo,$security_log);
								$message="bad#Unable to update procedure due to unverified invoice number.";
								$exit_flag=true;
								break;
						}
					}
					//insert invoice number
					$sql=$error=$s='';$placeholders=array();
					$sql="update tplan_procedure set invoice_number=:invoice_number, date_invoiced=now(), invoice_id=:invoice_id where treatment_procedure_id=:treatment_procedure_id";
					$error="Unable to update treatment procedure invoice number";
					$placeholders[':invoice_number']="$invoice_num";
					$placeholders[':invoice_id']=$invoice_id;
					$placeholders[':treatment_procedure_id']="$treatment_procedure_id";
					$s = insert_sql($sql, $placeholders, $error, $pdo);

					////check cost of invoiced procedures against dailiy invoice limit
					$sql=$error=$s='';$placeholders=array();
					$sql="select sum(unauthorised_cost) from tplan_procedure where invoice_id=:invoice_id";
					$error="Unable to update treatment procedure invoice number";
					$placeholders[':invoice_id']=$invoice_id;
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$inv_total2 = $row[0];
						//echo $message="bad#$row[0]";
						//$exit_flag=true;
						$invoice_daily_limit=0;
						//check if corporate has daily limit
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select invoice_daily_limit from  covered_company where id=:covered_company";
						$error2="Unable to update treatment procedure invoice number";
						$placeholders2[':covered_company']=$_SESSION['company_covered'];
						$s2= select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){$invoice_daily_limit = $row2['invoice_daily_limit'];}

						if($invoice_daily_limit == 0){
							//check if insurer has daily limit
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="select invoice_daily_limit from  insurance_company where id=:patient_type";
							$error2="Unable to update treatment procedure invoice number";
							$placeholders2[':patient_type']=$_SESSION['type'];
							$s2= select_sql($sql2, $placeholders2, $error2, $pdo);
							foreach($s2 as $row2){$invoice_daily_limit = $row2['invoice_daily_limit'];}
						}

						if($invoice_daily_limit > 0 and $inv_total2 > $invoice_daily_limit){
							//check if admin password is provided
							if(isset($_POST['admin_password']) and  $_POST['admin_password']!='' ){
										//now check if it matches a user with administrator privilege

									$admin_p=hash_hmac('sha1', $_POST['admin_password'], $salt);
									$sql=$error=$s='';$placeholders=array();
									$sql="select user_name from users a, privileges b where a.id=b.user_id and b.menu_id=109 and
									a.password=:password limit 1";
									$error="Unable to get administartor user";
									$placeholders['password']="$admin_p";
									$s = select_sql($sql, $placeholders, $error, $pdo);

									if($s->rowCount() == 0){ //if privilege not found check in roles
										$sql=$error=$s='';$placeholders=array();
										$sql="SELECT user_name FROM users d, user_roles c, role_privileges b WHERE d.id = c.user_id
										AND c.role_id = b.role_id aND b.menu_id =109 AND d.password = :password limit 1";
										$error="Unable to get administartor user";
										$placeholders['password']="$admin_p";
										$s = select_sql($sql, $placeholders, $error, $pdo);
									}
									if($s->rowCount() == 0){
										$message= "bad#tdone_admin_pass#Wrong administrator password provided";
										$exit_flag=true;
										break;
									}


							}
							//if(!isset($_POST['admin_password']) or  $_POST['admin_password']==''){
							else{	 $message="bad#tdone_admin_pass#The daily invoice limit for this corporate of KES ".		number_format($invoice_daily_limit,2)." is exceeded by the invoice 	your raising of KES ".number_format($inv_total2,2).". Please provide Admin password to continue";
								$exit_flag=true;
								break;
							}
						}


					}


					//now raise co-payment
					$sql=$error=$s='';$placeholders=array();
					$sql="select co_pay_type,value from covered_company  where id=:covered_company and insurer_id=:insurer_id";
					$error="Unable to get co-payments for invoice";
					$placeholders[':covered_company']=$_SESSION['company_covered'];
					$placeholders[':insurer_id']=$_SESSION['type'];
					$s = select_sql($sql, $placeholders, $error, $pdo);
					$deduction='';
					foreach($s as $row){
						if($row['co_pay_type']=="CASH") {$deduction=$row['value'];}
						elseif($row['co_pay_type']=="PERCENTAGE") {
							//get sum for the invoice
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="select sum(unauthorised_cost) from tplan_procedure where invoice_id=:invoice_id";
							$error2="Unable to get invoice total for co-payments";
							$placeholders2[':invoice_id']=$invoice_id;
							$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
							foreach($s2 as $row2){ $invoice_cost=$row2[0];}
							$deduction=ceil(($row['value'] * $invoice_cost)/100)*100;
						}
						if($deduction!=''){
							//check inf the co-payment for this invoice already exists
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="delete from co_payment where invoice_number=:invoice_number";
							$error2="Unable to delete invoice  co-payments";
							$placeholders2[':invoice_number']=$invoice_id;
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);

							//now insert new co-payment value
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into co_payment set invoice_number=:invoice_number, amount=:amount";
							$error2="Unable to add invoice  co-payments";
							$placeholders2[':invoice_number']=$invoice_id;
							$placeholders2[':amount']=$deduction;
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);
						}
					}
				}

				//insert quotation number  if any
				if(isset($_POST["$raise_quotation"]) and $_POST["$raise_quotation"]!=''){
					$treatment_procedure_id=$encrypt->decrypt($_POST["$procedure_number"]);
					$quotation_type=$encrypt->decrypt($_POST["$raise_quotation"]);
					//echo "z $quotation_type ";
					if($quotation_type!="new"){//cechk if quotation number is valid
								$var=html("$quotation_type");
								$security_log="sombody tried to input $var into tplan procedure as an quotation type";
								log_security($pdo,$security_log);
								$message="bad#Unable to update quoation due to unverified quotation number.";
								$exit_flag=true;
								break;
					}
					if($quotation_type=="new" and $existing_quote_new==''){//raise new quotation number
						//check if the treatment is to be appended to an quotation
						if($_POST["$append_quotation"]==''){
						//echo "z2 $_POST[$append_quotation] ";
							//get unique quotation id id
							$sql=$error=$s='';$placeholders=array();
							$sql="insert into quotation_number_generator set pid=:pid,
								when_raised=now(),
								added_by=:added_by";
								$placeholders[':pid']=$_SESSION['pid'];
								$placeholders[':added_by']=$_SESSION['id'];
							$error="Unable to update unique quotation number generator";
							$quoataion_id = get_insert_id($sql, $placeholders, $error, $pdo);
							$quotation_num="Q$quoataion_id-".date("m/y");
							$existing_quote_new="$quotation_num";

							/*$sql=$error=$s='';$placeholders=array();
							$sql="SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'dental_new' AND
								TABLE_NAME = 'quotation_number_generator'";
							$error="Unable to generate new quotation number";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){
								$quotation_num="Q$row[0]-".date("m/y");
								$existing_quote_new="$quotation_num";
							}
							*/
							//update into quotation_number_generator_table
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="update quotation_number_generator set
								quotation_number=:quotation_number where id=:quotation_id";
							$error2="Unable to update quotation number generator";
							$placeholders2[':quotation_id']=$quoataion_id;
							$placeholders2[':quotation_number']="$quotation_num";
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);

						}
						else{//append to quotation
							$result=$encrypt->decrypt("$_POST[$append_quotation]");
							$data=explode('#',"$result");
							$quotation_num="$data[0]";
						}
					}
					elseif($quotation_type=="new" and $existing_quote_new!=''){//use newly created quotation number
						$quotation_num="$existing_quote_new";
					}

					//insert quotaion number
					$sql=$error=$s='';$placeholders=array();
					$sql="update tplan_procedure set quotation_number=:quotation_number where treatment_procedure_id=:treatment_procedure_id";
					$error="Unable to update treatment procedure quotation number";
					$placeholders[':quotation_number']="$quotation_num";
					$placeholders[':treatment_procedure_id']="$treatment_procedure_id";
					$s = insert_sql($sql, $placeholders, $error, $pdo);

				}
				$i++;
			}
			if(!$exit_flag){$tx_result = $pdo->commit();$message="good#treatment-done#Treatment procedures have been updated. ";}
			elseif($exit_flag){$pdo->rollBack();}




		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save treatment procedure changes  ";
		}
		echo "$message";

}

//this is for doing a patient search
elseif(isset($_POST['search_by']) and $_POST['search_by']!='' and isset($_POST['token_search_patient']) and
	isset($_SESSION['token_search_patient']) and $_POST['token_search_patient']==$_SESSION['token_search_patient']){
	//$_SESSION['token_search_patient']='';
	$_SESSION['tplan_id']='';
		//call search function
		//echo "-- $_POST[search_by] --";
		if($_POST['search_by']=='patient_number' or $_POST['search_by']=='mobile_number' or $_POST['search_by']=='business_number'){
			$result=get_patient($pdo,$_POST['search_by'],$_POST['search_ciretia']);
		}
		elseif($_POST['search_by']=='first_name' or $_POST['search_by']=='middle_name' or $_POST['search_by']=='last_name'){
			$result=get_pt_name($_POST['search_by'],$_POST['search_ciretia'],$pdo,$encrypt);
		}
		//$result=get_patient($pdo,$_POST['search_by'],$_POST['search_ciretia']);
		//$data=explode("#","$result");
		//if($data[0]=="error"){$error_message=" $data[1] ";}
		if($_SESSION['pid']!='' and isset($_POST['ptc']) ){
			//insert into patient seraches
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_contact_searches set when_added=now(), user_id=:user_id, pid=:pid";
			$placeholders[':user_id']=$_SESSION['id'];
			$placeholders[':pid']=$_SESSION['pid'];
			$error="Error: Unable to record patient search";
			$s = insert_sql($sql, $placeholders, $error, $pdo);
		}
		echo "$result";
}



//this will clear a form
//this is for doing a patient search
elseif(isset($_POST['clear_form']) and $_POST['clear_form']!='' ){
	clear_patient();
}




//this is for removing a procedure from cover
elseif( isset($_POST['remove_procedure_cover_token']) and 	isset($_SESSION['remove_procedure_cover_token']) and
	$_POST['remove_procedure_cover_token']==$_SESSION['remove_procedure_cover_token'] and userHasRole($pdo,10)){
	$exit_flag=false;
	//verify that the values given do exist
	$company_id=$encrypt->decrypt($_POST['ninye']);
	$insurer_id=$encrypt->decrypt($_POST['ninye_ins']);
	$procedure_id=$encrypt->decrypt($_POST['procedure_removed']);
	//verify company
	if (!in_array($company_id, $_SESSION['covered_company_array'])){
			$message="bad#Unable to save details as corptate details are not correct. ";
			$var=html("$company_id");
			$security_log="sombody tried to input $var into procedures_not_covered as company id";
			log_security($pdo,$security_log);
			$exit_flag=true;
	}
	//verify insurer is
	if (!in_array($insurer_id, $_SESSION['patient_type_array'])){
			$message="bad#Unable  to save details as insurer details are not correct. ";
			$var=html("$insurer_id");
			$security_log="sombody tried to input $var into procedures_not_covered as insurer id";
			log_security($pdo,$security_log);
			$exit_flag=true;
	}
	//verify procedure
	if (!in_array($procedure_id, $_SESSION['procedures_array'])){
			$message="bad#Unable to save details as procedure details are not correct. Please contact support.";
			$var=html("$procedure_id");
			$security_log="sombody tried to input $var into procedures_not_covered as procedure not covered id";
			log_security($pdo,$security_log);
			$exit_flag=true;
	}
	if(!$exit_flag){
		//insert into procedures not covered
		$sql=$error=$s='';$placeholders=array();
		$sql="insert into procedures_not_covered set company_id=:company_id, insurer_id=:insurer_id, procedure_not_covered=:procedure_not_covered";
		$placeholders[':company_id']=$company_id;
		$placeholders[':insurer_id']=$insurer_id;
		$placeholders[':procedure_not_covered']=$procedure_id;
		$error="Unable to remove procedure from cover";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s){$message="good#Procedure removed from insurance cover.#$_POST[ninye]";}
		elseif(!$s){$message="bad#Unable to remove procedure from insurance cover.";}
	}
	echo "$message";
	//$data=explode('#',
}

//this is for return a procedure to cover
elseif( isset($_POST['return_procedure_cover_token']) and 	isset($_SESSION['return_procedure_cover_token']) and
	$_POST['return_procedure_cover_token']==$_SESSION['return_procedure_cover_token'] and userHasRole($pdo,10)){
	$exit_flag=false;
	if(!$exit_flag){
		$i=0;
		$id_1=$_POST['return_procedure'];
		$n=count($id_1);
		while($i<$n){
			//insert into procedures not covered
			$id=$encrypt->decrypt("$id_1[$i]");
			//get company id
			$sql=$error=$s='';$placeholders=array();
			$sql="select company_id from procedures_not_covered where id=:id";
			$placeholders[':id']=$id;
			$error="Unable to get company id";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			foreach($s as $row){$company_id=html($row['company_id']);
				//echo "company id is $row[company_id] <br>";
			}
			//echo " id is $id <br>";
			//now delere it
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from procedures_not_covered where id=:id";
			$placeholders[':id']=$id;;
			$error="Unable to return procedure to cover";
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			$i++;
		}
		$id=$encrypt->encrypt($company_id);
		if($s){$message="good#Procedure returned to insurance cover.#$id";}
		elseif(!$s){$message="bad#Unable to return procedure to insurance cover.";}
	}
	echo "$message";
	//$data=explode('#',
}

//this is for booking an appointment from a school holiday appointment reminder
elseif(isset($_POST['tdone_appointment_date5']) and $_POST['tdone_appointment_date5']!='' and userHasRole($pdo,122)){
	$appointment_date=html($_POST['tdone_appointment_date5']);
	$appointments=array();
	$token = form_token();
	$data=explode('#',$_SESSION['appointment_from_holidays']);
	$task2=html("$data[4]");
	$_SESSION['token_school_holiday_actual_appointment'] = "$token";
	$data=explode('-',"$appointment_date");
	$month=$data[1];
	$day=$data[2];
	//check if this date is a public holiday
	$description='';
	$sql=$error=$s='';$placeholders=array();
	$sql="select description from public_holidays where holiday_month=:month and month_day=:day";
	$placeholders[':month']=$month;
	$placeholders[':day']=$day;
	$error="Unable to select public holidays";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$description=html("$row[description]");
	}
	if($description!=''){
		echo "<label class=label>$appointment_date is a public holiday $description , and no appointments can be booked</label>";
		exit;
	}

	//now check if it's a working weekday
	$week_day=date("N", strtotime("$appointment_date"));
	//check if week day has any appointment
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select shour,rank from appointment_hours where work_day=:workday order by rank";
	$error2="Unable to get appointment hours";
	$placeholders2[':workday']=$week_day;
	$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
	if($s2->rowCount() == 0){
		echo "<label class=label>".date("l", strtotime("$appointment_date"))." is a none working day and no appointments can be booked</label>";
		exit;
	}


	//get appointments on that day first for registerd folks
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.last_name,a.middle_name, a.first_name,b.first_name,b.middle_name,b.last_name,c.treatment,c.shour,c.smin,c.rank,c.surgical_unit,c.am_pm
	from patient_details_a a, users b, registered_patient_appointments c where c.pid=a.pid and c.doc_id=b.id and c.appointment_date=:appointment_date";
	$placeholders[':appointment_date']=$appointment_date;
	$error="Unable to get registerd appointments";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$patient_name=html("$row[2] $row[1] $row[0]");
		$doctor_name=html("$row[3] $row[4] $row[5]");
		$treatment=html("$row[treatment]");
		$hour=html("$row[shour]");
		$min=html("$row[smin]");
		$rank=html("$row[rank]");
		$surgery=html("$row[surgical_unit]");
		$appointments[]=array('registered'=>'yes','patient_name'=>"$patient_name", 'doctor_name'=>"$doctor_name",'hour'=>"$hour",'min'=>"$min",'rank'=>"$rank",'surgery'=>"$surgery",'treatment'=>"$treatment");
	}

	//get appointments on that day first for unregisterd folks
	$sql=$error=$s='';$placeholders=array();
	$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names,b.first_name,b.middle_name,b.last_name,c.treatment,c.shour,c.smin,c.rank,c.surgical_unit,c.am_pm
	from unregistered_patients a, users b, unregistered_patient_appointments c where c.pid=a.id and c.doc_id=b.id and c.appointment_date=:appointment_date";
	$placeholders[':appointment_date']=$appointment_date;
	$error="Unable to get un-registerd appointments";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$patient_name=html("$row[0]");
		$doctor_name=html("$row[1] $row[2] $row[3]");
		$treatment=html("$row[treatment]");
		$hour=html("$row[shour]");
		$min=html("$row[smin]");
		$rank=html("$row[rank]");
		$surgery=html("$row[surgical_unit]");
		$appointments[]=array('registered'=>'no','patient_name'=>"$patient_name", 'doctor_name'=>"$doctor_name",'hour'=>"$hour",'min'=>"$min",'rank'=>"$rank",'surgery'=>"$surgery",'treatment'=>"$treatment");
	}

	//now show appointment table forthe day
	echo "<div class='grid-100 caption'>Appointments for $appointment_date </div>";
	echo "<div class='appointment_table_div'>";

	//echo "<table class='fixed_column'><caption>Appointments for $_SESSION[appointment_date]</caption><thead><tr><th class='headcol appoint_time'>Time</th>";
	echo "<table class='fixed_column replace_header'><thead><tr>";

	//start by getting surgery names
	$sql=$error=$s='';$placeholders=array();
	$sql="select surgery_id, surgery_name from surgery_names order by surgery_name";
	$error="Unable to get surgery names";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$surgery_id_array[]=html("$row[surgery_id]");
		$surgery_name_array[]=html("$row[surgery_name]");
		$surgery_name=html("$row[surgery_name]");
		echo "<th class=appoint_surgery>$surgery_name</th>";
	}
	echo "</tr></thead><tbody>";

	//now get minute intervals
	$sql=$error=$s='';$placeholders=array();
	$sql="select minute_interval from appointment_minutes_interval";
	$error="Unable to get appointment interval";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$minutes_interval_array='';
	foreach($s as $row){
		$minute_interval=html($row['minute_interval']);
		$intervals = 60 / $minute_interval;
	}

	//now get working hours
	/*$sql=$error=$s2='';$placeholders=array();
	$sql="select shour,rank from appointment_hours order by rank";
	$error="Unable to get appointment hours";
	$s2 = 	select_sql($sql, $placeholders, $error, $pdo);*/
	$rank_array=array();
	$am_pm='';
	foreach($s2 as $row){
		$rank=html($row['rank']);
		$hour=html($row['shour']);
		if($rank < 12){$am_pm="AM";}
		else{$am_pm="PM";}
		$i=1;
		while($i <= $intervals){
			if($i==1){$minute="00";$minute_compare=0;}
		//	echo "<tr><td class='headcol'>$hour:$minute $am_pm</td>";
			echo "<tr>";
			//now loop through the surgeries
			$n2=count($surgery_id_array);
			$i2=0;
			while($i2 < $n2){
				$appointment_exists =false;
				$td_class='';
				//check if appointment is in this surgery ,hour(rank) and minute
				foreach($appointments as $current_appointment){
					if($surgery_id_array[$i2] == $current_appointment['surgery'] and $current_appointment['rank'] == $rank and
						$current_appointment['min'] == $minute_compare){
						$appointment_exists =true;
						if($current_appointment['registered'] == 'no'){$td_class='unregistered_appointment_highlight';}
						//check if treatment is defined and show this
						$task='';
						if($current_appointment['treatment']!=''){$task="<br>$current_appointment[treatment]";}
						echo "<td class=$td_class>$current_appointment[patient_name] $hour:$minute $am_pm $task</td>";
						}
				}
				if(!$appointment_exists){
					echo "<td>";
						$val2=$encrypt->encrypt("$rank#$minute#$surgery_id_array[$i2]#$appointment_date");
						echo "<input type=button value='$hour:$minute $am_pm $surgery_name_array[$i2] ' class=' button_style toggle_appointment_form' />";
							echo "<div class=appointment_form><form action='' method='POST'  name='' id='' class='patient_form'>";
							echo "<input name=ninye type=hidden value='$val2' /><input type='hidden' name='token_school_holiday_actual_appointment'  value=$_SESSION[token_school_holiday_actual_appointment] />";
							echo "<label class=label>Appointment Task</label><textarea name=appointment_task rows=2>$task2</textarea>";
							echo "<input type=submit class=diff_color value='Submit Appointment' />";
							echo "</form></div>";
					echo "</td>";
				}
				$i2++;
			}
			echo "</tr>";
			$minute=$minute + $minute_interval ;
			$minute_compare = $minute;
			if ($minute < 10){$minute="0$minute";}
			$i++;
		}
	}
	echo "</tbody></table>";
echo "</div>";


}

//this is for booking an appointment in tdone
elseif(isset($_POST['tdone_appointment_date']) and $_POST['tdone_appointment_date']!='' and userHasRole($pdo,20)){
	$appointment_date=html($_POST['tdone_appointment_date']);
	$appointments=array();
	$token = form_token();
	$_SESSION['token_tdone_appointment'] = "$token";
	$data=explode('-',"$appointment_date");
	$month=$data[1];
	$day=$data[2];
	//check if this date is a public holiday
	$description='';
	$sql=$error=$s='';$placeholders=array();
	$sql="select description from public_holidays where holiday_month=:month and month_day=:day";
	$placeholders[':month']=$month;
	$placeholders[':day']=$day;
	$error="Unable to select public holidays";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$description=html("$row[description]");
	}
	if($description!=''){
		echo "<label class=label>$appointment_date is a public holiday $description , and no appointments can be booked</label>";
		exit;
	}

	//now check if it's a working weekday
	$week_day=date("N", strtotime("$appointment_date"));
	//check if week day has any appointment
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select shour,rank from appointment_hours where work_day=:workday order by rank";
	$error2="Unable to get appointment hours";
	$placeholders2[':workday']=$week_day;
	$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
	if($s2->rowCount() == 0){
		echo "<label class=label>".date("l", strtotime("$appointment_date"))." is a none working day and no appointments can be booked</label>";
		exit;
	}


	//get appointments on that day first for registerd folks
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.last_name,a.middle_name, a.first_name,b.first_name,b.middle_name,b.last_name,c.treatment,c.shour,c.smin,c.rank,c.surgical_unit,c.am_pm
	from patient_details_a a, users b, registered_patient_appointments c where c.pid=a.pid and c.doc_id=b.id and c.appointment_date=:appointment_date";
	$placeholders[':appointment_date']=$appointment_date;
	$error="Unable to get registerd appointments";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$patient_name=html("$row[2] $row[1] $row[0]");
		$doctor_name=html("$row[3] $row[4] $row[5]");
		$treatment=html("$row[treatment]");
		$hour=html("$row[shour]");
		$min=html("$row[smin]");
		$rank=html("$row[rank]");
		$surgery=html("$row[surgical_unit]");
		$appointments[]=array('registered'=>'yes','patient_name'=>"$patient_name", 'doctor_name'=>"$doctor_name",'hour'=>"$hour",'min'=>"$min",'rank'=>"$rank",'surgery'=>"$surgery",'treatment'=>"$treatment");
	}

	//get appointments on that day first for unregisterd folks
	$sql=$error=$s='';$placeholders=array();
	$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names,b.first_name,b.middle_name,b.last_name,c.treatment,c.shour,c.smin,c.rank,c.surgical_unit,c.am_pm
	from unregistered_patients a, users b, unregistered_patient_appointments c where c.pid=a.id and c.doc_id=b.id and c.appointment_date=:appointment_date";
	$placeholders[':appointment_date']=$appointment_date;
	$error="Unable to get un-registerd appointments";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$patient_name=html("$row[0]");
		$doctor_name=html("$row[1] $row[2] $row[3]");
		$treatment=html("$row[treatment]");
		$hour=html("$row[shour]");
		$min=html("$row[smin]");
		$rank=html("$row[rank]");
		$surgery=html("$row[surgical_unit]");
		$appointments[]=array('registered'=>'no','patient_name'=>"$patient_name", 'doctor_name'=>"$doctor_name",'hour'=>"$hour",'min'=>"$min",'rank'=>"$rank",'surgery'=>"$surgery",'treatment'=>"$treatment");
	}

	//now show appointment table forthe day
	echo "<div class='grid-100 caption'>Appointments for $appointment_date </div>";
	echo "<div class='appointment_table_div'>";

	//echo "<table class='fixed_column'><caption>Appointments for $_SESSION[appointment_date]</caption><thead><tr><th class='headcol appoint_time'>Time</th>";
	echo "<table class='fixed_column replace_header'><thead><tr>";

	//start by getting surgery names
	$sql=$error=$s='';$placeholders=array();
	$sql="select surgery_id, surgery_name from surgery_names order by surgery_name";
	$error="Unable to get surgery names";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$surgery_id_array[]=html("$row[surgery_id]");
		$surgery_name_array[]=html("$row[surgery_name]");
		$surgery_name=html("$row[surgery_name]");
		echo "<th class=appoint_surgery>$surgery_name</th>";
	}
	echo "</tr></thead><tbody>";

	//now get minute intervals
	$sql=$error=$s='';$placeholders=array();
	$sql="select minute_interval from appointment_minutes_interval";
	$error="Unable to get appointment interval";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$minutes_interval_array='';
	foreach($s as $row){
		$minute_interval=html($row['minute_interval']);
		$intervals = 60 / $minute_interval;
	}

	//now get working hours
	/*$sql=$error=$s2='';$placeholders=array();
	$sql="select shour,rank from appointment_hours order by rank";
	$error="Unable to get appointment hours";
	$s2 = 	select_sql($sql, $placeholders, $error, $pdo);*/
	$rank_array=array();
	$am_pm='';
	foreach($s2 as $row){
		$rank=html($row['rank']);
		$hour=html($row['shour']);
		if($rank < 12){$am_pm="AM";}
		else{$am_pm="PM";}
		$i=1;
		while($i <= $intervals){
			if($i==1){$minute="00";$minute_compare=0;}
		//	echo "<tr><td class='headcol'>$hour:$minute $am_pm</td>";
			echo "<tr>";
			//now loop through the surgeries
			$n2=count($surgery_id_array);
			$i2=0;
			while($i2 < $n2){
				$appointment_exists =false;
				$td_class='';
				//check if appointment is in this surgery ,hour(rank) and minute
				foreach($appointments as $current_appointment){
					if($surgery_id_array[$i2] == $current_appointment['surgery'] and $current_appointment['rank'] == $rank and
						$current_appointment['min'] == $minute_compare){
						$appointment_exists =true;
						if($current_appointment['registered'] == 'no'){$td_class='unregistered_appointment_highlight';}
						//check if treatment is defined and show this
						$task='';
						if($current_appointment['treatment']!=''){$task="<br>$current_appointment[treatment]";}
						echo "<td class=$td_class>$current_appointment[patient_name] $hour:$minute $am_pm $task</td>";
						}
				}
				if(!$appointment_exists){
					echo "<td>";
						$val2=$encrypt->encrypt("$rank#$minute#$surgery_id_array[$i2]#$appointment_date");
						echo "<input type=button value='$hour:$minute $am_pm $surgery_name_array[$i2] ' class=' button_style toggle_appointment_form' />";
							echo "<div class=appointment_form><form action='' method='POST'  name='' id='' class='patient_form'>";
							echo "<input name=ninye type=hidden value='$val2' /><input type='hidden' name='token_tdone_appointment'  value=$_SESSION[token_tdone_appointment] />";
							echo "<label class=label>Appointment Task</label><textarea name=appointment_task rows=2></textarea>";
							echo "<input type=submit class=diff_color value='Submit Appointment' />";
							echo "</form></div>";
					echo "</td>";
				}
				$i2++;
			}
			echo "</tr>";
			$minute=$minute + $minute_interval ;
			$minute_compare = $minute;
			if ($minute < 10){$minute="0$minute";}
			$i++;
		}
	}
	echo "</tbody></table>";
echo "</div>";


}

//this is for booking an appointment
elseif(isset($_POST['appointment_date']) and $_POST['appointment_date']!='' and userHasRole($pdo,45)){
	$_SESSION['appointment_date']=$appointment_date=html($_POST['appointment_date']);
	$appointments=array();

	$data=explode('-',"$appointment_date");
	$month=$data[1];
	$day=$data[2];
	//check if this date is a public holiday
	$description='';
	$sql=$error=$s='';$placeholders=array();
	$sql="select description from public_holidays where holiday_month=:month and month_day=:day";
	$placeholders[':month']=$month;
	$placeholders[':day']=$day;
	$error="Unable to select public holidays";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$description=html("$row[description]");
	}
	if($description!=''){
		echo "<label class=label>$appointment_date is a public holiday $description , and no appointments can be booked</label>";
		exit;
	}

	//now check if it's a working weekday
	$week_day=date("N", strtotime("$appointment_date"));
	//check if week day has any appointment
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select shour,rank from appointment_hours where work_day=:workday order by rank";
	$error2="Unable to get appointment hours";
	$placeholders2[':workday']=$week_day;
	$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
	if($s2->rowCount() == 0){
		echo "<label class=label>".date("l", strtotime("$appointment_date"))." is a none working day and no appointments can be booked</label>";
		exit;
	}


	//get appointments on that day first for registerd folks
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.last_name,a.middle_name, a.first_name,b.first_name,b.middle_name,b.last_name,c.treatment,c.shour,c.smin,c.rank,c.surgical_unit,c.am_pm
	from patient_details_a a, users b, registered_patient_appointments c where c.pid=a.pid and c.doc_id=b.id and c.appointment_date=:appointment_date";
	$placeholders[':appointment_date']=$appointment_date;
	$error="Unable to get registerd appointments";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$patient_name=html("$row[2] $row[1] $row[0]");
		$doctor_name=html("$row[3] $row[4] $row[5]");
		$treatment=html("$row[treatment]");
		$hour=html("$row[shour]");
		$min=html("$row[smin]");
		$rank=html("$row[rank]");
		$surgery=html("$row[surgical_unit]");
		$appointments[]=array('registered'=>'yes','patient_name'=>"$patient_name", 'doctor_name'=>"$doctor_name",'hour'=>"$hour",'min'=>"$min",'rank'=>"$rank",'surgery'=>"$surgery",'treatment'=>"$treatment");
	}

	//get appointments on that day first for unregisterd folks
	$sql=$error=$s='';$placeholders=array();
	$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names,b.first_name,b.middle_name,b.last_name,c.treatment,c.shour,c.smin,c.rank,c.surgical_unit,c.am_pm
	from unregistered_patients a, users b, unregistered_patient_appointments c where c.pid=a.id and c.doc_id=b.id and c.appointment_date=:appointment_date";
	$placeholders[':appointment_date']=$appointment_date;
	$error="Unable to get un-registerd appointments";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$patient_name=html("$row[0]");
		$doctor_name=html("$row[1] $row[2] $row[3]");
		$treatment=html("$row[treatment]");
		$hour=html("$row[shour]");
		$min=html("$row[smin]");
		$rank=html("$row[rank]");
		$surgery=html("$row[surgical_unit]");
		$appointments[]=array('registered'=>'no','patient_name'=>"$patient_name", 'doctor_name'=>"$doctor_name",'hour'=>"$hour",'min'=>"$min",'rank'=>"$rank",'surgery'=>"$surgery",'treatment'=>"$treatment");
	}

	//now show appointment table forthe day
	echo "<div class='grid-100 caption'>Appointments for $_SESSION[appointment_date] </div>";
	echo "<div class='appointment_table_div'>";

	//echo "<table class='fixed_column'><caption>Appointments for $_SESSION[appointment_date]</caption><thead><tr><th class='headcol appoint_time'>Time</th>";
	echo "<table class='fixed_column replace_header'><thead><tr>";

	//start by getting surgery names
	$sql=$error=$s='';$placeholders=array();
	$sql="select surgery_id, surgery_name from surgery_names order by surgery_name";
	$error="Unable to get surgery names";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$surgery_id_array[]=html("$row[surgery_id]");
		$surgery_name_array[]=html("$row[surgery_name]");
		$surgery_name=html("$row[surgery_name]");
		echo "<th class=appoint_surgery>$surgery_name</th>";
	}
	echo "</tr></thead><tbody>";

	//now get minute intervals
	$sql=$error=$s='';$placeholders=array();
	$sql="select minute_interval from appointment_minutes_interval";
	$error="Unable to get appointment interval";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$minutes_interval_array='';
	foreach($s as $row){
		$minute_interval=html($row['minute_interval']);
		$intervals = 60 / $minute_interval;
	}

	//now get working hours
	/*$sql=$error=$s2='';$placeholders=array();
	$sql="select shour,rank from appointment_hours order by rank";
	$error="Unable to get appointment hours";
	$s2 = 	select_sql($sql, $placeholders, $error, $pdo);*/
	$rank_array=array();
	$am_pm='';
	foreach($s2 as $row){
		$rank=html($row['rank']);
		$hour=html($row['shour']);
		if($rank < 12){$am_pm="AM";}
		else{$am_pm="PM";}
		$i=1;
		while($i <= $intervals){
			if($i==1){$minute="00";$minute_compare=0;}
		//	echo "<tr><td class='headcol'>$hour:$minute $am_pm</td>";
			echo "<tr>";
			//now loop through the surgeries
			$n2=count($surgery_id_array);
			$i2=0;
			while($i2 < $n2){
				$appointment_exists =false;
				$td_class='';
				//check if appointment is in this surgery ,hour(rank) and minute
				foreach($appointments as $current_appointment){
					if($surgery_id_array[$i2] == $current_appointment['surgery'] and $current_appointment['rank'] == $rank and
						$current_appointment['min'] == $minute_compare){
						$appointment_exists =true;
						if($current_appointment['registered'] == 'no'){$td_class='unregistered_appointment_highlight';}
						//check if treatment is defined and show this
						$task='';
						if($current_appointment['treatment']!=''){$task="<br>$current_appointment[treatment]";}
						echo "<td class=$td_class>$current_appointment[patient_name] $hour:$minute $am_pm $task</td>";
						}
				}
				if(!$appointment_exists){
					$val2=$encrypt->encrypt("$rank#$minute#$surgery_id_array[$i2]");
					echo "<td><input type=hidden value='$val2' /><input type=button value='$hour:$minute $am_pm $surgery_name_array[$i2]' class='create_appointment button_style' /></td>";}
				$i2++;
			}
			echo "</tr>";
			$minute=$minute + $minute_interval ;
			$minute_compare = $minute;
			if ($minute < 10){$minute="0$minute";}
			$i++;
		}
	}
	echo "</tbody></table>";
echo "</div>";


}

//this will show patient serach form for booking appointments depeedning on the type of patien type
elseif(isset($_POST['appointment_patient_type']) and $_POST['appointment_patient_type']!='' and userHasRole($pdo,45) ){
	if($_POST['appointment_patient_type'] == 'registered'){ ?>
	<!--	<form class='' action='' method="POST"  name="" id="">	-->
			<?php //$token = form_token(); $_SESSION['token_search_patient_appoint1'] = "$token";  ?>
		<!--	<input type="hidden" name="token_search_patient_appoint1"  value="<?php //echo $_SESSION['token_search_patient_appoint1']; ?>" />	 -->
			<div class='grid-20'>
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
		<!--	<div class='grid-35 show_spin'><input type=submit value="Find"  /></div>
		</form> -->


	<?php }
	elseif($_POST['appointment_patient_type'] == 'unregistered'){?>
		<!-- <form class='' action='' method="POST"  name="" id="">	 -->
			<?php //$token = form_token(); $_SESSION['token_search_patient_appoint4'] = "$token";  ?>
		<!--	<input type="hidden" name="token_search_patient_appoint4"  value="<?php //echo $_SESSION['token_search_patient_appoint4']; ?>" />	 -->
			<?php
		echo "<div class='grid-20 '><label for='' class='label'>First Name</label></div>";
		echo "<div class='grid-25 '><input  type=text name=first_name  /></div>";
		echo "<div class='grid-15 '><label for='' class='label'>Second Name</label></div>";
		echo "<div class='grid-25 '><input  type=text name=middle_name  /></div>";
		echo "<div class=clear></div><br>";
		echo "<div class='grid-20 '><label for='' class='label'>Last Name</label></div>";
		echo "<div class='grid-25 '><input  type=text name=last_name  /></div>";
		echo "<div class=clear></div><br>";
		echo "<div class='grid-20 '><label for='' class='label'>Telephone Number</label></div>";
		echo "<div class='grid-25 '><input  type=text name=phone  /></div>";
		echo "<div class='grid-15 '><label for='' class='label'>Email Address</label></div>";
		echo "<div class='grid-25 '><input  type=text name=email_address  /></div>";


		//echo "<div class='grid-15 '><input type=submit value='Book Appointment'  /></div></form>";
		//echo "<div class=clear></div></br>";
	}
}

//process patint notes
if(isset($_SESSION['token_pn1']) and isset($_POST['token_pn1']) and $_POST['token_pn1']==$_SESSION['token_pn1']){
	$exit_flag=false;
	$message='';
	//check if title is set
	if(!isset($_POST['patient_title']) or $_POST['patient_title']==''){
		$message="bad#Please specify the patient title";
		$exit_flag=true;
	}
	//check if review type is set
	elseif(!$exit_flag and !isset($_POST['review_type']) or $_POST['review_type']=='' or ($_POST['review_type']!='review_date'  and
			$_POST['review_type']!='sick_off' )){
		$message="bad#Please specify the patient note type";
		$exit_flag=true;
	}
	//check if correct date is set
	elseif(!$exit_flag and $_POST['review_type']=='review_date'  and (!isset($_POST['from_date']) or $_POST['from_date']=='' )){
		$message="bad#Please specify the review date";
		$exit_flag=true;
	}
	//check if correct date is set
	elseif(!$exit_flag and $_POST['review_type']=='sick_off'  and (!isset($_POST['from_date1']) or $_POST['from_date1']=='' or
			!isset($_POST['to_date']) or $_POST['to_date']=='')){
		$message="bad#Please specify the sick off dates";
		$exit_flag=true;
	}
	//process the note
	elseif(!$exit_flag){
		//fore revirew date
		if($_POST['review_type']=='review_date'){
			$date=date('F jS, Y');
			$rev_date=html($_POST['from_date']);
			$title=html($_POST['patient_title']);
			$patient_number=$encrypt->decrypt("$_POST[token_ninye]");
			$patient_number=html("$patient_number");
			$name=$encrypt->decrypt("$_POST[token_ninye2]");
			$name=html("$name");

			$message= "good#patient_notes#<div class=grid-100><input class='button_style printment' type='button' value='Print'></div>
			 <div class='grid-100 print_on_letter_head'>
					$date<br><br>
					<b>TO WHO IT MAY CONCERN<br><br>
					Dear Sir/Madam,<br><br></b>
					This is to certify that $title. $name File No. $patient_number  has been seen and treated at our Institution and has been
					scheduled for another appointment on $rev_date.<br><br>
					Kindly avail this consideration.<br><br>
					Yours faithfully,<br><br><br><br><br><br><br><br><br><br><br><br>
					<b>For: Cuspid Dental Care</b>
			</div>";
		}
		//fore sick off
		if($_POST['review_type']=='sick_off'){
			$date=date('F jS, Y');
			$from_date=html($_POST['from_date1']);
			$to_date=html($_POST['to_date']);
			$title=html($_POST['patient_title']);
			$patient_number=$encrypt->decrypt("$_POST[token_ninye]");
			$patient_number=html("$patient_number");
			$name=$encrypt->decrypt("$_POST[token_ninye2]");
			$name=html("$name");
	//		$s=mysql_query("SELECT DATEDIFF( '$to', '$from' )") or die(mysql_error());
			//while($s1=mysql_fetch_row($s)) {$day_num=$s1[0] + 1;}
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT DATEDIFF( :to, :from )";
			$error="Unable to get time diff";
			$placeholders[':to']="$to_date";
			$placeholders[':from']="$from_date";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			foreach($s as $row){$day_num=$row[0] + 1;}
			//get days in words
			try
				{
				$day_words =  ucfirst(convert_number($day_num));
				}
			catch(Exception $e)
				{
				echo $e->getMessage();
				}


			$message= "good#patient_notes#<div class=grid-100><input class='button_style printment' type='button' value='Print'></div>
			 <div class='grid-100 print_on_letter_head'>
					$date<br><br>
					<b>TO WHO IT MAY CONCERN<br><br>
					Dear Sir/Madam,<br><br></b>
					This is to certify that $title. $name File No. $patient_number  has been seen and treated at our Institution and has been given:<br><br>
					$day_words ($day_num) Days sick off - $from_date - $to_date<br><br>
					Kindly avail this consideration.<br><br>
					Yours faithfully,<br><br><br><br><br><br><br><br><br><br><br><br>
					<b>For: Cuspid Dental Care</b>
			</div>";
		}
	}
	echo"$message";
	exit;
}

//this will reprint a receipt
elseif(isset($_POST['receipt1']) and $_POST['receipt1']!='' and userHasRole($pdo,72)){
	print_receipt($pdo,$_POST['receipt1'], $encrypt);
}

//this will insert new appointment reminder
if(isset($_POST['token_appt_rem']) and isset($_SESSION['token_appt_rem']) and
$_POST['token_appt_rem']==$_SESSION['token_appt_rem'] and userHasRole($pdo,122)){

	//echo "bad#dddd";
	$exit_flag=false;
	$email_address='';
	//check if doctor is set
	if(!isset($_POST['doctor']) or $_POST['doctor']==''){
		$exit_flag=true;
		$message="bad#Please specify the doctor for the appointment";
	}

	//check that holiday is slected
	if(!$exit_flag and $_POST['holiday_period'] == ''){

		$exit_flag=true;
		$message="bad#Holiday period not selected";
	}

	//check that patient is slected
	if(!$exit_flag and ($_POST['search_by'] == '' or $_POST['search_ciretia']=='')){

		$exit_flag=true;
		$message="bad#Patient not specified";
	}

	if(!$exit_flag and (!isset($_POST['selected_patient']) or $_POST['selected_patient']=='')){
		$result  = check_if_patient_exists($_POST['search_by'], $_POST['search_ciretia'],$pdo,$encrypt);
		$data = explode('#',$result);
		$result=$data[0];
		if(isset($data[1])){$searched_patient_pid=$data[1];}
	}

	//check if selectefd  patient is set
	if(!$exit_flag and isset($_POST['selected_patient']) and $_POST['selected_patient']!=''){
		$searched_patient_pid=$encrypt->decrypt($_POST['selected_patient']);
		$result=1;
		//echo "kk";
	}


	//check if the registered patient has been swapped
	if(!$exit_flag and isset($searched_patient_pid) and $searched_patient_pid!=''){
		$resultx = check_if_swapped($pdo,'pid',$searched_patient_pid);
		if($resultx!='good'){
			$exit_flag=true;
			$message="bad#$resultx and cannot be edited.";
		}
	}


	//insert regiesterd patient appointment
	if(!$exit_flag and $result==1){


			$sql=$error=$s='';$placeholders=array();
			$sql="insert into school_holiday_appointment_reminders set when_added=now(),
					doctor=:doc_id,
					pid=:pid,
					holiday_id=:holiday_id,
					task=:task,
					added_by=:added_by";
			$error="Unable to get add appointment";
			$placeholders[':doc_id']=$encrypt->decrypt($_POST['doctor']);
			$placeholders[':pid']=$searched_patient_pid;
			$placeholders[':added_by']=$_SESSION['id'];
			$placeholders[':task']=$_POST['holiday_task'];
			$placeholders[':holiday_id']=$encrypt->decrypt($_POST['holiday_period']);
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message = "good#Appointment reminder saved";}
			else{$message = "bad#Unable to save appointment reminder";}


	}
	elseif(!$exit_flag and $result == 2){$message= "bad#No such patient";}

		$data=explode('#',"$message");
		if("$data[0]" == "good"){$_SESSION['token_appt_rem']='';}
		echo "$message";

}

//this will insert new appointment
elseif(isset($_POST['token_search_patient_appoint3']) and isset($_SESSION['token_search_patient_appoint3']) and
$_POST['token_search_patient_appoint3']==$_SESSION['token_search_patient_appoint3'] and userHasRole($pdo,45)){
	//echo "bad#dddd";
	$exit_flag=false;
	$email_address='';
	//check if doctor is set
	if(!isset($_POST['doctor']) or $_POST['doctor']==''){
		$exit_flag=true;
		$message="bad#Please specify the doctor for the appointment";
	}

	/*//check if treatment is set
	if(!isset($_POST['procedure']) or $_POST['procedure']==''){
		$exit_flag=true;
		$message="bad#Please specify the treatment that will be done";
	}
	if(!$exit_flag and !isset($_POST['procedure'])){$_POST['procedure']='';}
	else{$_POST['procedure']=$encrypt->decrypt($_POST['procedure']);}*/

	//check if date is set
	if(!$exit_flag and !isset($_SESSION['appointment_date']) or $_SESSION['appointment_date']==''){
		$exit_flag=true;
		$message="bad#Please specify the appointment date";
	}

	//check if doctor is set
	$rank=$min=$surgical_unit='';
	$data=explode('#',$_SESSION['create_appointment']);
	$rank=$data[0];
	$min=$data[1];
	$surgical_unit=$data[2];
	if(!$exit_flag and $rank=='' or $min=='' or $surgical_unit==''){
		$exit_flag=true;
		$message="bad#Unable to save appointments, missing some entries, please try again.";
	}

	if(!$exit_flag and $_POST['patient_type']=='registered' and (!isset($_POST['selected_patient']) or $_POST['selected_patient']=='')){
		$result  = check_if_patient_exists($_POST['search_by'], $_POST['search_ciretia'],$pdo,$encrypt);
		$data = explode('#',$result);
		$result=$data[0];
		if(isset($data[1])){$searched_patient_pid=$data[1];}
	}

	//check if selectefd  patient is set
	if(!$exit_flag and isset($_POST['selected_patient']) and $_POST['selected_patient']!=''){
		$searched_patient_pid=$encrypt->decrypt($_POST['selected_patient']);
		$result=1;
		//echo "kk";
	}


	//check if the registered patient has been swapped
	if(!$exit_flag and isset($searched_patient_pid) and $searched_patient_pid!=''){
		$resultx = check_if_swapped($pdo,'pid',$searched_patient_pid);
		if($resultx!='good'){
			$exit_flag=true;
			$message="bad#$resultx and cannot be edited.";
		}
	}

	//set email address
	if(isset($_POST['email_address']) and  $_POST['email_address']!=''){
		$email_address=html($_POST['email_address']);
	}

	if(!$exit_flag and $_POST['patient_type']=='unregistered' and ($_POST['first_name']!='' and  $_POST['phone']!='')){
		$result=1;
	}
	elseif(!$exit_flag and $_POST['patient_type']=='unregistered' and $_POST['first_name']=='' ){
		$exit_flag=true;
		$message="bad#Unable to save appointments as first name needs to be specified.";
	}
	//check email format
	if(!$exit_flag and $_POST['patient_type']=='unregistered' and $_POST['email_address']!='' ){
		$email_address=html($_POST['email_address']);
		if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
			$exit_flag=true;
			$message="bad#Unable to save details as the email $email_address  is not correctly specified. ";
		}
	}

	if(!$exit_flag and $_POST['patient_type']=='unregistered' and $_POST['phone']==''){
		$exit_flag=true;
		$message="bad#Unable to save appointments, telephone number needs to be specified.";
	}
	//insert regiesterd patient appointment
	if(!$exit_flag and $result==1){
		if( $_POST['patient_type']=='registered' and $searched_patient_pid!=''){
			//remove any auto appointment for this guy
			$today=date('Y-m-d');
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from registered_patient_appointments where pid=:pid and appointment_date > '$today'";
			$error="Unable to deleet auto appointment";
			$placeholders[':pid']=$searched_patient_pid;
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			$sql=$error=$s='';$placeholders=array();
			$sql="insert into registered_patient_appointments set when_added=now(),
					doc_id=:doc_id,
					pid=:pid,
					appointment_date=:appointment_date,
					shour=:shour,
					smin=:smin,
					rank=:rank,
					am_pm=:am_pm,
					surgical_unit=:surgical_unit,
					added_by=:added_by";
			$error="Unable to get add appointment";
			$placeholders[':doc_id']=$encrypt->decrypt($_POST['doctor']);
			$placeholders[':pid']=$searched_patient_pid;
			$placeholders[':added_by']=$_SESSION['id'];
			//$placeholders[':treatment']=$_POST['procedure'];
			$placeholders[':appointment_date']=$_SESSION['appointment_date'];
			if($rank > 12){
					$hour=$rank - 12;
					$am_pm="PM";
			}
			else{
				$hour = $rank;
				$am_pm="AM";
			}
			$placeholders[':shour']=$hour;
			$placeholders[':smin']=$min;
			$placeholders[':rank']=$rank;
			$placeholders[':am_pm']=$am_pm;
			$placeholders[':surgical_unit']=$surgical_unit;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s){$message = "good#book_appointment#Appointment saved";}
			else{$message = "bad#Unable to save appointment";}
		}
		elseif( $_POST['patient_type']=='unregistered' and  $_POST['first_name']!='' and $_POST['phone']!='' ){
			try{
				$pdo->beginTransaction();
				if(!isset($_POST['phone'])){$_POST['phone']='';}

				//firts insert unregsiterd patient details
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into unregistered_patients set first_name=:first_name, middle_name=:middle_name, last_name=:last_name
					,when_added=now(),phone=:phone, email_address=:email_address";
				$error="Unable to add unregisterde patient";
				$placeholders[':first_name']=$_POST['first_name'];
				$placeholders[':middle_name']=$_POST['middle_name'];
				$placeholders[':last_name']=$_POST['last_name'];
				$placeholders[':phone']=$_POST['phone'];
				$placeholders[':email_address']="$email_address";
				$unregistered_id = 	get_insert_id($sql, $placeholders, $error, $pdo);
				//$_SESSION['unregistered_patient_name']=$_SESSION['unregistered_patient_phone']='';
				//now insert into unregisterd appointments
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into unregistered_patient_appointments set when_added=now(),
						doc_id=:doc_id,
						pid=:pid,
						appointment_date=:appointment_date,
						shour=:shour,
						smin=:smin,
						rank=:rank,
						am_pm=:am_pm,
						surgical_unit=:surgical_unit,added_by=:added_by";
				$error="Unable to get add unregisteerd patienbt appointment";
				$placeholders[':doc_id']=$encrypt->decrypt($_POST['doctor']);
				$placeholders[':pid']=$unregistered_id;
				$placeholders[':added_by']=$_SESSION['id'];
				//$placeholders[':treatment']=$_POST['procedure'];
				$placeholders[':appointment_date']=$_SESSION['appointment_date'];
				if($rank > 12){
						$hour=$rank - 12;
						$am_pm="PM";
				}
				else{
					$hour = $rank;
					$am_pm="AM";
				}
				$placeholders[':shour']=$hour;
				$placeholders[':smin']=$min;
				$placeholders[':rank']=$rank;
				$placeholders[':am_pm']=$am_pm;
				$placeholders[':surgical_unit']=$surgical_unit;
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				$tx_result = $pdo->commit();
				if($tx_result){$message = "good#book_appointment#Appointment saved";}
				else{$message = "bad#Unable to save appointment";}
			}
			catch (PDOException $e)
			{
			$pdo->rollBack();
			$message="bad#Unable to save appointment ";
			}
			}
	}
	elseif(!$exit_flag and $result == 2){$message= "bad#No such patient";}
	/*else{
		//$message="bad#Please specify the patient to allocate";
	}*/
		$data=explode('#',"$message");
		if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
	echo "$message";
}

//this will show the form pop up for creating a new appointment
elseif(isset($_POST['appointment_doctor']) and $_POST['appointment_doctor']!='' and userHasRole($pdo,45) ){
	$doc_id=$encrypt->decrypt($_POST['appointment_doctor']);
	$date_of_appointment=html($_SESSION['appointment_date']);
	//get docotor appointmateds for unregisterad
	$sql=$error=$s='';$placeholders=array();
	$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names, b.shour, b.smin, b.rank ,b.am_pm , c.first_name,c.middle_name,c.last_name
		from unregistered_patient_appointments as b join unregistered_patients as a on b.pid=a.id and b.doc_id=:doc_id
		and b.appointment_date=:appointment_date
		join users as c on c.id=b.doc_id";
	$error="Unable to get unregisterd apponitments for doctors";
	$placeholders['doc_id']=$doc_id;
	$placeholders['appointment_date']=$_SESSION['appointment_date'];
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$appoint_array=array();
	foreach($s as $row){
		$doctor_name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
		$patient_name=ucfirst(html($row['names']));
		$time=html("$row[shour]:$row[smin] $row[am_pm]");
		$rank=html($row['rank']);
		$smin=html($row['smin']);
		$appoint_array[]=array('doctor'=>"$doctor_name", 'patient_name'=>"$patient_name", 'time'=>"$time", 'rank'=>"$rank", 'smin'=>"$smin");
	}

	//get docotor appointmateds for registerad
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.first_name,a.middle_name,a.last_name, b.shour, b.smin, b.rank ,b.am_pm , c.first_name,c.middle_name,c.last_name
		from registered_patient_appointments as b join patient_details_a as a on b.pid=a.pid and b.doc_id=:doc_id
		and b.appointment_date=:appointment_date
		join users as c on c.id=b.doc_id";
	$error="Unable to get registerd apponitments for doctors";
	$placeholders['doc_id']=$doc_id;
	$placeholders['appointment_date']=$_SESSION['appointment_date'];
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$doctor_name=ucfirst(html("$row[7] $row[8] $row[9]"));
		$patient_name=ucfirst(html("$row[0] $row[1] $row[2]"));
		$time=html("$row[shour]:$row[smin] $row[am_pm]");
		$rank=html($row['rank']);
		$smin=html($row['smin']);
		$appoint_array[]=array('doctor'=>"$doctor_name", 'patient_name'=>"$patient_name", 'time'=>"$time", 'rank'=>"$rank",  'smin'=>"$smin");
	}

	if(count($appoint_array) > 0){
		foreach ($appoint_array as $key => $row) {
			$rank1[$key]  = $row['rank'];
			$smin1[$key]  = $row['smin'];
		}

		// Sort the data with when_added
		array_multisort($rank1, SORT_ASC,$smin1, SORT_ASC, $appoint_array);
		$i=0;
		foreach($appoint_array as $row){
			if($i==0){
				echo "<table class='normal_table'><caption>Appointments for Dr. $doctor_name on $date_of_appointment</caption><thead>
					<tr><th class='ds_count'></th><th class='ds_pname'>PATIENT NAME</th><th class='ds_time'>TIME</th></tr></thead><tbody>";
			}
			$i++;
			echo "<tr><td>$i</td><td>$row[patient_name]</td><td>$row[time]</td></tr>";

		}
		echo "</tbody></table>";
	}
	else{echo "<div class='label'>There are no appointments for the doctor on the day</div>";}
}


//this will show the form pop up for creating a new appointment
elseif(isset($_POST['create_appointment']) and $_POST['create_appointment']!='' and userHasRole($pdo,45) ){
	$_SESSION['create_appointment']=$encrypt->decrypt($_POST['create_appointment']);
	?>

	<div id=registered_appointment>
			<div class='feedback hide_element'></div>
			<form class='patient_form check_selected_patient' action="" method="post" name="" id="">
				<?php $token = form_token(); $_SESSION['token_search_patient_appoint3'] = "$token";  ?>
				<input type="hidden" name="token_search_patient_appoint3"  value="<?php echo $_SESSION['token_search_patient_appoint3']; ?>" />

				<div class='grid-20'><label for="" class="label">Select Patient Type</label></div>
				<div class='grid-15'><select name=patient_type class=appointment_patient_type >
					<option></option>
					<option value=registered>Registered</option>
					<option value=unregistered>Un-registered</option>
					</select>
				</div>
				<div class=clear></div></br>
				<div class='grid-100 remove_left_padding' id=appointment_patient_search ></div>
				<div class=clear></div></br>
				<?php
				//select doctor
				$sql=$error=$s='';$placeholders=array();
				$sql="select id,first_name, middle_name,last_name from users where user_type=1 and status='active'";
				$error="Unable to get list of doctors";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				echo "<div class='grid-20'><label for='' class='label'>Select Doctor</label></div>";
				echo "<div class='grid-25'><select class=appointment_doctor name=doctor><option></option>";
					foreach($s as $row){
						$doctor_name=html("$row[first_name] $row[middle_name] $row[last_name]");
						$doc_id=$encrypt->encrypt("$row[id]");
						echo "<option value='$doc_id'>$doctor_name</option>";
					}
				echo "</select></div>";

				/*//show pending treatments
				$sql=$error=$s='';$placeholders=array();
				$sql="select a.procedure_id,b.name from procedures b, tplan_procedure a, tplan_id_generator c where a.procedure_id=b.id and
						a.tplan_id=c.tplan_id and c.pid=:pid and a.status!=2";
				$error="Unable to get list of pending treatments for registerd patient appointment";
				$placeholders[':pid']=$_SESSION['pid'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				echo "<div class='grid-15'><label for='' class='label'>Select Procedure</label></div>";
				echo "<div class='grid-35'><select name=procedure><option></option>";
					if($s->rowCount() > 0){
						foreach($s as $row){
							$procedure_name=html("$row[name]");
							$procedure_id=$encrypt->encrypt("$row[name]");
							echo "<option value='$procedure_id'>$procedure_name</option>";
						}
					}
					else{
						$consultation=$encrypt->encrypt("Consultation");
						echo "<option value='$consultation'>Consultation</option>";
					}
				echo "</select></div>";*/ ?>
				<!--<div class=' grid-25'><input class='button_style check_doctor_schedule' type=button value="Check Doctor's Availlability" /></div>
				--><?php
				echo "<div class=clear></div><br>";?>

				<div class='prefix-20 show_doc_appointments grid-80'></div>
				<div class=clear></div><br>
				<div class='prefix-20 grid-15'><input class='' type=submit value='Book Appointment' /></div></form><?php
	echo "</div>";//this is for registered_appointment div
	echo "<div id=unregistered_appointment></div>";

}

//this will submit shool holiday actual appointment
elseif(isset($_SESSION['token_school_holiday_actual_appointment']) and isset($_POST['token_school_holiday_actual_appointment']) and $_SESSION['token_school_holiday_actual_appointment']==$_POST['token_school_holiday_actual_appointment']){
	$data2=explode('#',$_SESSION['appointment_from_holidays']);
	$doc_id=$data2[3];
	$pid=$data2[2];
	$holiday_reminder_id=$data2[1];

	$data1=$encrypt->decrypt($_POST['ninye']);
	$data=explode('#',"$data1");
			//remove any auto appointment for this guy
			$today=date('Y-m-d');
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from registered_patient_appointments where pid=:pid and appointment_date > '$today'";
			$error="Unable to deleet auto appointment";
			$placeholders[':pid']=$pid;
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);


			$sql=$error=$s='';$placeholders=array();
			$sql="insert into registered_patient_appointments set when_added=now(),
					doc_id=:doc_id,
					pid=:pid,
					appointment_date=:appointment_date,
					shour=:shour,
					smin=:smin,
					rank=:rank,
					am_pm=:am_pm,
					treatment=:appointment_task,
					surgical_unit=:surgical_unit,
					added_by=:added_by";
			$error="Unable to get add appointment";
			$placeholders[':doc_id']=$doc_id;
			$placeholders[':added_by']=$_SESSION['id'];
			$placeholders[':appointment_task']=$_POST['appointment_task'];
			$placeholders[':pid']=$pid;
			//$rank#$minute#$surgery_id_array[$i2]#$appointment_date
			$placeholders[':appointment_date']="$data[3]";
			$rank="$data[0]";
			$min="$data[1]";
			$surgical_unit=$data[2];
			if($rank > 12){
					$hour=$rank - 12;
					$am_pm="PM";
			}
			else{
				$hour = $rank;
				$am_pm="AM";
			}
			$placeholders[':shour']=$hour;
			$placeholders[':smin']=$min;
			$placeholders[':rank']=$rank;
			$placeholders[':am_pm']=$am_pm;
			$placeholders[':surgical_unit']=$surgical_unit;
			$appointment_id = 	get_insert_id($sql, $placeholders, $error, $pdo);

			//update holiday reminders with  appointment id
			$sql=$error=$s='';$placeholders=array();
			$sql="update school_holiday_appointment_reminders set status=:appointment_id where id=:holiday_reminder_id";
			$error="Unable to get add appointment";
			$placeholders[':appointment_id']=$appointment_id;
			$placeholders[':holiday_reminder_id']=$holiday_reminder_id;
			$s = insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message = "good#appointment_for_school_holiday#$data[3]#Appointment saved";}
			else{$message = "bad#appointment_form_tdone#Unable to save appointment";}
			echo "$message";
}

//this will submit tdone appointment
elseif(isset($_SESSION['token_tdone_appointment']) and isset($_POST['token_tdone_appointment']) and $_SESSION['token_tdone_appointment']==$_POST['token_tdone_appointment']){
	$data1=$encrypt->decrypt($_POST['ninye']);
	$data=explode('#',"$data1");
			//remove any auto appointment for this guy
			$today=date('Y-m-d');
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from registered_patient_appointments where pid=:pid and appointment_date > '$today'";
			$error="Unable to deleet auto appointment";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);


			$sql=$error=$s='';$placeholders=array();
			$sql="insert into registered_patient_appointments set when_added=now(),
					doc_id=:doc_id,
					pid=:pid,
					appointment_date=:appointment_date,
					shour=:shour,
					smin=:smin,
					rank=:rank,
					am_pm=:am_pm,
					treatment=:appointment_task,
					surgical_unit=:surgical_unit";
			$error="Unable to get add appointment";
			$placeholders[':doc_id']=$_SESSION['id'];
			$placeholders[':appointment_task']=$_POST['appointment_task'];
			$placeholders[':pid']=$_SESSION['pid'];
			//$rank#$minute#$surgery_id_array[$i2]#$appointment_date
			$placeholders[':appointment_date']="$data[3]";
			$rank="$data[0]";
			$min="$data[1]";
			$surgical_unit=$data[2];
			if($rank > 12){
					$hour=$rank - 12;
					$am_pm="PM";
			}
			else{
				$hour = $rank;
				$am_pm="AM";
			}
			$placeholders[':shour']=$hour;
			$placeholders[':smin']=$min;
			$placeholders[':rank']=$rank;
			$placeholders[':am_pm']=$am_pm;
			$placeholders[':surgical_unit']=$surgical_unit;
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message = "good#appointment_form_tdone#Appointment saved";}
			else{$message = "bad#appointment_form_tdone#Unable to save appointment";}
			echo "$message";
}


//this will submit edit tplan for tplan with no invoice
elseif(isset($_SESSION['edit_tplan_token_2']) and isset($_POST['edit_tplan_token_2']) and $_SESSION['edit_tplan_token_2']==$_POST['edit_tplan_token_2']){
	$nimeana=$encrypt->decrypt($_POST['nimeana']);


	$data=explode('ninye',"$nimeana");
	$old_count=$data[2];
	$new_count=$data[0];
	$pid=$data[3];
	$tplan_id=$data[4];
	$exit_flag=false;

	//check if pre-auth or smart is needed for this patient
	$pre_auth_needed=$smart_needed='';
	$sql=$error1=$s='';$placeholders=array();
	$sql="select pre_auth_needed, smart_needed from covered_company a, patient_details_a b where b.type=a.insurer_id and b.company_covered=a.id
		and b.pid=:pid";
	$error="Unable to check if pre-auth is needed";
	$placeholders[':pid']=$pid;
	$s = select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$pre_auth_needed=html($row['pre_auth_needed']);
		$smart_needed=html($row['smart_needed']);
	}

	try{
		$pdo->beginTransaction();
			//update old treatments
			$i=1;
			while($i <= $old_count){

				//this will delete old treatment
				if(isset($_POST["old_remove$i"]) and $_POST["old_remove$i"]!=''){
					//echo "4706";
					$sql=$error=$s='';$placeholders=array();
					$sql="delete from tplan_procedure where treatment_procedure_id=:treatment_procedure_id";
					$error="Unable to delete procedure from tplan";
					$placeholders[':treatment_procedure_id']=$encrypt->decrypt($_POST["old_remove$i"]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
				}
				else{ //for updating check if parameters are properly set

					//check if procedure is set
					if($_POST["old_procedure$i"]==''){
							$message="bad#Unable to save details as not all treatment procedures have been set, please check if all treatment procedures have been specified";
							$exit_flag=true;
							break;
					}

					$var=$encrypt->decrypt($_POST["old_procedure$i"]);
					$data=explode('#',"$var");//xrays will have #
					//these are common checks
					//check if cost is set
					if(!isset($_POST["old_cost$i"])){
							$message="bad#Unable to save details as procedure cost is not specified for all procedures, please ensure that each procedure has a cost";
							$exit_flag=true;
							break;
					}

					//check if  payment method is set
					if(!isset($_POST["old_pay_method$i"])){
							$message="bad#Unable to save details as payment method is not specified, please ensure that each procedure has a payment method specified";
							$exit_flag=true;
							break;
					}

					//check amount
					//remove commas
					$amount=str_replace(",", "", $_POST["old_cost$i"]);
						//check if amount is integer
					if(!ctype_digit($amount)){//echo "ooooo $unit_price[$i] ";
						//check if it has only 2 decimal places
						$data=explode('.',$amount);
						$invalid_amount=html("$amount");
						if ( count($data) != 2 ){

						$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;
						break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;
						break;
						}
					}

					//check if pay type is valid
					$pay_type=$encrypt->decrypt($_POST["old_pay_method$i"]);
					if($pay_type!=1 and $pay_type!=2 and $pay_type!=3){
						$message="bad#Unable to save details as payment method is not correctly set for all procedures, please ensure that a payment method is set for each procedure. ";
						$exit_flag=true;
						break;
					}
					$v=$encrypt->decrypt($_POST["old_procedure$i"]);
					$data=explode('#',"$v");
					if($pay_type==1){
						if($pre_auth_needed=='YES' or $smart_needed=='YES'){$authorised_cost=NULL;}
						elseif($pre_auth_needed!='YES' and $smart_needed!='YES'){$authorised_cost=$amount;}
					}
					else{$authorised_cost=$amount;}
					//check if points are needed
					if($pay_type==3){
						//echo "points";
						//get points earned
						$points_earned=0;
						$sql=$error=$s='';$placeholders=array();
						$sql="select  TIMEDIFF( discharge_time, time_allocated ), points_per_min from patient_allocations
						where discharge_time!='0000-00-00 00:00:00' and pid=:pid";
						$placeholders[':pid']=$pid;
						$error="Error: Unable to patient points ";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$data=explode(':',"$row[0]");
							$points_earned=(($data[0] * 60) + $data[1]) * $row['points_per_min'];

						}

						//points used so far
						$sql=$error=$s='';$placeholders=array();
						$sql="select  sum(authorised_cost) from tplan_procedure where pid=:pid and pay_type=3 group by pid";
						$placeholders[':pid']=$pid;
						$error="Error: Unable to patient points used";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$points_used=html($row[0]);

						}

						if(($points_earned - $points_used) < $amount){
							$message="bad#Unable to save treatment plan as loyalty points used  exceed the availlable balance. ";
							$exit_flag=true;
							break;
						}
						else{
							//make payment
							$receipt_number='';
							$rid=0;
							//first get receipt number for non insured payment
							$sql=$error=$s='';$placeholders=array();
							$sql="select max(receipt_num) from non_insurance_receipt_id_generator";
							$error="Unable to get non insured receipt number";
							$s = select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){$rid=$row[0] + 1;}
							if($rid == 0){$rid = 1;}

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into non_insurance_receipt_id_generator set receipt_num =:rid";
							$error="Unable to get non insured receipt number";
							$placeholders[':rid']=$rid;
							$s = insert_sql($sql, $placeholders, $error, $pdo);
							$receipt_number="R$rid-".date('m/y');

							$sql=$error=$s='';$placeholders=array();
							$sql="insert into payments set when_added=now(), receipt_num=:receipt_num,
								amount=:amount,
								pay_type=8,
								pid=:pid,
								created_by=:created_by";
							$error="Unable to make non-insured payment";
							$placeholders[':receipt_num']="$receipt_number";
							$placeholders[':amount']=$amount;
							$placeholders[':pid']=$pid;
							$placeholders[':created_by']=$_SESSION['id'];
							$s = insert_sql($sql, $placeholders, $error, $pdo);



						}

					}
					/*//check if xrays
					if(!$exit_flag and isset($data[1]) and $data[1]!='' and $data[0]==1){
						//echo "4769";
						//get xray types
						$sql=$error=$s='';$placeholders=array();
						$sql="select id , all_teeth, name from teeth_and_xray_types";
						$error="Unable to get xray types";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						$xray_id=$xray_all_teeth=$xray_name1=array();
						foreach($s as $row){
							$xray_id["$row[id]"]=$row['id'];
							$xray_all_teeth["$row[id]"]=$row['all_teeth'];
							$xray_name1["$row[id]"]=html($row['name']);
						}

						//check if xray is valid tyoe
						if (!in_array($data[1], $xray_id)) {
							$message="bad#Unable to save details as some x-ray values are not correctly set";
							$var=html("$data[1]");
							$security_log="sombody tried to input $var into on_examination for xray types";
							log_security($pdo,$security_log);
							$exit_flag=true;
							break;
						}
						//check if teeth are specified
						elseif (in_array($data[1], $xray_id)) {
							$xid=$data[1];
							$xray_done_name="$xray_name1[$xid]";
							$teeth_xray='';
							if(isset($_POST["old_teeth_specified$i"]) and $xray_all_teeth["$xid"] == 'yes'){
								$xt=$_POST["old_teeth_specified$i"];
								$nt=count($xt);
								$ni=0;

								while($ni < $nt){
									if($ni == 0){$teeth_xray=$encrypt->decrypt("$xt[$ni]");}
									else{$teeth_xray="$teeth_xray, ".$encrypt->decrypt("$xt[$ni]");}
									$ni++;
								}
							}
							elseif(!isset($_POST["old_teeth_specified$i"]) and $xray_all_teeth["$xid"] == 'yes'){
								$message="bad#Unable to save details as no tooth has been specified for $xray_name1[$xid]";
								$exit_flag=true;
								break;
							}
						}

						//update tplan procedure
						$sql=$error=$s='';$placeholders=array();
						$sql="update tplan_procedure set
								procedure_id=1,
								teeth=:teeth,
								details=:details,
								unauthorised_cost=:unauthorised_cost,
								authorised_cost=:authorised_cost,
								status=2,
								pay_type=:pay_type,
								date_procedure_added=now(),
								number_done=1,
								created_by=:created_by
								where treatment_procedure_id=:treatment_procedure_id
								";
						$error="Unable to add xray to tplan xray count";
								$placeholders['treatment_procedure_id']=$encrypt->decrypt($_POST["old_ninye$i"]);
								$placeholders['teeth']="$teeth_xray";
								$placeholders['details']="$xray_done_name";
								$placeholders['unauthorised_cost']=$amount;
								$placeholders['authorised_cost']=$authorised_cost;
								$placeholders['pay_type']=$pay_type;
								$placeholders['created_by']=$_SESSION['id'];


						$s = 	insert_sql($sql, $placeholders, $error, $pdo);


						//insert into tplan_xray_count
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into tplan_xray_count set
								treatment_procedure_id=:treatment_procedure_id,
								xray_id=:xray_id,
								teeth=:teeth,
								xray_holder_id=:xray_holder_id";
						$error="Unable to add xray to tplan xray count";
								$placeholders['treatment_procedure_id']=$encrypt->decrypt($_POST["old_ninye$i"]);
								$placeholders['xray_id']=$data[1];
								$placeholders['xray_holder_id']=0;
								$placeholders['teeth']="$teeth_xray";
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);

					}//end xray type of procedure
					*/
					//for non xray procedurescheck if xrays
					if(!$exit_flag and !isset($data[1]) and $data[0]!=1){

						//get procedure types
						$sql=$error=$s='';$placeholders=array();
						$sql="select id , all_teeth, name,type from procedures";
						$error="Unable to get procedure types";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						$procedure_id=$procedure_all_teeth=$procedure_name1=$procedure_type=array();
						foreach($s as $row){
							$procedure_id["$row[id]"]=$row['id'];
							$procedure_all_teeth["$row[id]"]=$row['all_teeth'];
							$procedure_name1["$row[id]"]=html($row['name']);
							$procedure_type["$row[id]"]=html($row['type']);
						}

						//check if procedure is valid tyoe
						if (!in_array($data[0], $procedure_id)) {
							$message="bad#Unable to save details as not all procedures are correctly set";
							$var=html("$data[0]");
							$security_log="sombody tried to input $var into tplan_procedure";
							log_security($pdo,$security_log);
							$exit_flag=true;
							break;
						}
						//check if teeth are specified
						elseif (in_array($data[0], $procedure_id)) {
							$proc_id=$data[0];
							$procedure_done_name="$procedure_name1[$proc_id]";
							$teeth_procedure='';
							$nt=1;
							//echo "-$i- ".$_POST["old_teeth_specified$i"]." --";
							if(isset($_POST["old_teeth_specified$i"]) and $procedure_all_teeth["$proc_id"] == 'yes'){
								$xt=$_POST["old_teeth_specified$i"];
								$nt=count($xt);
								$ni=0;

								while($ni < $nt){
									if($ni == 0){$teeth_procedure=$encrypt->decrypt("$xt[$ni]");}
									else{$teeth_procedure="$teeth_procedure, ".$encrypt->decrypt("$xt[$ni]");}
									$ni++;
								}
							}
							elseif(!isset($_POST["old_teeth_specified$i"]) and $procedure_all_teeth["$proc_id"] == 'yes'){
								$message="bad#Unable to save details as no tooth has been specified for $procedure_name1[$proc_id]";
								$exit_flag=true;
								break;
							}
						}

						//now perform update of tplan
						$sql=$error=$s='';$placeholders=array();
						$sql="update tplan_procedure set
								procedure_id=:procedure_id,
								teeth=:teeth,
								details=:details,
								unauthorised_cost=:unauthorised_cost,
								authorised_cost=:authorised_cost,
								status=0,
								pay_type=:pay_type,
								date_procedure_added=now(),
								number_done=:number_done,
								created_by=:created_by,
								alias=:alias
								where treatment_procedure_id=:treatment_procedure_id
								";
						$error="Unable to add xray to tplan xray count";
								$placeholders['procedure_id']=$data[0];
								$placeholders['treatment_procedure_id']=$encrypt->decrypt($_POST["old_ninye$i"]);
								$placeholders['teeth']="$teeth_procedure";
								$placeholders['details']=$_POST["old_details$i"];
								$placeholders['unauthorised_cost']=$amount;
								$placeholders['authorised_cost']=$authorised_cost;
								$placeholders['pay_type']=$pay_type;
								$placeholders['number_done']=$nt;
								if(isset($_POST["old_alias$i"]) and $_POST["old_alias$i"]!=''){$placeholders['alias']=1;}
								else{$placeholders['alias']=0;}
								$placeholders['created_by']=$_SESSION['id'];
								$s = 	insert_sql($sql, $placeholders, $error, $pdo);

					}//end non xray procedure
				} //end for none delete opeartion

				$i++;
			}


			//insert new treatments
			$i=1;
			while($i <= $new_count){
					//check if procedure is set
					if($_POST["new_procedure$i"]==''){
							$message="bad#Unable to save details as not all treatment procedures have been set, please check if all treatment procedures have been specified";
							$exit_flag=true;
							break;
					}

					$var=$encrypt->decrypt($_POST["new_procedure$i"]);
					$data=explode('#',"$var");//xrays will have #
					//these are common checks
					//check if cost is set
					if(!isset($_POST["new_cost$i"])){
							$message="bad#Unable to save details as procedure cost is not specified for all procedures, please ensure that each procedure has a cost";
							$exit_flag=true;
							break;
					}

					//check if  payment method is set
					if(!isset($_POST["new_pay_method$i"])){
							$message="bad#Unable to save details as payment method is not specified, please ensure that each procedure has a payment method specified";
							$exit_flag=true;
							break;
					}

					//check amount
					//remove commas
					$amount=str_replace(",", "", $_POST["new_cost$i"]);
						//check if amount is integer
					if(!ctype_digit($amount)){//echo "ooooo $unit_price[$i] ";
						//check if it has only 2 decimal places
						$data=explode('.',$amount);
						$invalid_amount=html("$amount");
						if ( count($data) != 2 ){

						$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;
						break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;
						break;
						}
					}

					//check if pay type is valid
					$pay_type=$encrypt->decrypt($_POST["new_pay_method$i"]);
					if($pay_type!=1 and $pay_type!=2 and $pay_type!=3){
						$message="bad#Unable to save details as payment method is not correctly set for all procedures, please ensure that a payment method is set for each procedure. ";
						$exit_flag=true;
						break;
					}
					if($pay_type==1){
						if($pre_auth_needed=='YES' or $smart_needed=='YES'){$authorised_cost=NULL;}
						elseif($pre_auth_needed!='YES' and $smart_needed!='YES'){$authorised_cost=$amount;}
					}
					else{$authorised_cost=$amount;}
					$v=$encrypt->decrypt($_POST["new_procedure$i"]);
					$data=explode('#',"$v");
					//check if xrays
				/*	if(!$exit_flag and isset($data[1]) and $data[1]!='' and $data[0]==1){
						//get xray types
						$sql=$error=$s='';$placeholders=array();
						$sql="select id , all_teeth, name from teeth_and_xray_types";
						$error="Unable to get xray types";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						$xray_id=$xray_all_teeth=$xray_name1=array();
						foreach($s as $row){
							$xray_id["$row[id]"]=$row['id'];
							$xray_all_teeth["$row[id]"]=$row['all_teeth'];
							$xray_name1["$row[id]"]=html($row['name']);
						}

						//check if xray is valid tyoe
						if (!in_array($data[1], $xray_id)) {
							$message="bad#Unable to save details as some x-ray values are not correctly set";
							$var=html("$data[1]");
							$security_log="sombody tried to input $var into on_examination for xray types";
							log_security($pdo,$security_log);
							$exit_flag=true;
							break;
						}
						//check if teeth are specified
						elseif (in_array($data[1], $xray_id)) {
							$xid=$data[1];
							$xray_done_name="$xray_name1[$xid]";
							$teeth_xray='';
							if(isset($_POST["new_teeth_specified$i"]) and $xray_all_teeth["$xid"] == 'yes'){
								$xt=$_POST["new_teeth_specified$i"];
								$nt=count($xt);
								$ni=0;

								while($ni < $nt){
									if($ni == 0){$teeth_xray=$encrypt->decrypt("$xt[$ni]");}
									else{$teeth_xray="$teeth_xray, ".$encrypt->decrypt("$xt[$ni]");}
									$ni++;
								}
							}
							elseif(!isset($_POST["new_teeth_specified$i"]) and $xray_all_teeth["$xid"] == 'yes'){
								$message="bad#Unable to save details as no tooth has been specified for $xray_name1[$xid]";
								$exit_flag=true;
								break;
							}
						}

						//insert tplan procedure
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into tplan_procedure set
								tplan_id=:tplan_id,
								procedure_id=1,
								teeth=:teeth,
								details=:details,
								unauthorised_cost=:unauthorised_cost,
								authorised_cost=:authorised_cost,
								status=2,
								pay_type=:pay_type,
								date_procedure_added=now(),
								number_done=1,
								created_by=:created_by,
								pid=:pid

								";
						$error="Unable to add xray to tplan xray count";
								$placeholders['tplan_id']=$tplan_id;
								$placeholders['pid']=$pid;

								$placeholders['teeth']="$teeth_xray";
								$placeholders['details']="$xray_done_name";
								$placeholders['unauthorised_cost']=$amount;
								$placeholders['authorised_cost']=$authorised_cost;
								$placeholders['pay_type']=$pay_type;
								$placeholders['created_by']=$_SESSION['id'];
						$id = 	 get_insert_id($sql, $placeholders, $error, $pdo);


						//insert into tplan_xray_count
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into tplan_xray_count set
								treatment_procedure_id=:treatment_procedure_id,
								xray_id=:xray_id,
								teeth=:teeth,
								xray_holder_id=:xray_holder_id";
						$error="Unable to add xray to tplan xray count";
								$placeholders['treatment_procedure_id']=$id;
								$placeholders['xray_id']=$data[1];
								$placeholders['xray_holder_id']=0;
								$placeholders['teeth']="$teeth_xray";
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);

					}//end xray type of procedure
					*/
					//for non xray procedurescheck if xrays
					if(!$exit_flag and !isset($data[1]) and $data[0]!=1){

						//get procedure types
						$sql=$error=$s='';$placeholders=array();
						$sql="select id , all_teeth, name from procedures";
						$error="Unable to get procedure types";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						$procedure_id=$procedure_all_teeth=$procedure_name1=array();
						foreach($s as $row){
							$procedure_id["$row[id]"]=$row['id'];
							$procedure_all_teeth["$row[id]"]=$row['all_teeth'];
							$procedure_name1["$row[id]"]=html($row['name']);
						}

						//check if procedure is valid tyoe
						if (!in_array($data[0], $procedure_id)) {
							$message="bad#Unable to save details as not all procedures are correctly set";
							$var=html("$data[0]");
							$security_log="sombody tried to input $var into tplan_procedure";
							log_security($pdo,$security_log);
							$exit_flag=true;
							break;
						}
						//check if teeth are specified
						elseif (in_array($data[0], $procedure_id)) {
							$proc_id=$data[0];
							$procedure_done_name="$procedure_name1[$proc_id]";
							$teeth_procedure='';
							$nt=1;
							//echo "-$i- ".$_POST["new_teeth_specified$i"]." --";
							if(isset($_POST["new_teeth_specified$i"]) and $procedure_all_teeth["$proc_id"] == 'yes'){
								$xt=$_POST["new_teeth_specified$i"];
								$nt=count($xt);
								$ni=0;

								while($ni < $nt){
									if($ni == 0){$teeth_procedure=$encrypt->decrypt("$xt[$ni]");}
									else{$teeth_procedure="$teeth_procedure, ".$encrypt->decrypt("$xt[$ni]");}
									$ni++;
								}
							}
							elseif(!isset($_POST["new_teeth_specified$i"]) and $procedure_all_teeth["$proc_id"] == 'yes'){
								$message="bad#Unable to save details as no tooth has been specified for $procedure_name1[$proc_id]";
								$exit_flag=true;
								break;
							}
						}

						//now perform update of tplan
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into tplan_procedure set
								tplan_id=:tplan_id,
								pid=:pid,
								procedure_id=:procedure_id,
								teeth=:teeth,
								details=:details,
								unauthorised_cost=:unauthorised_cost,
								authorised_cost=:authorised_cost,
								status=0,
								pay_type=:pay_type,
								date_procedure_added=now(),
								number_done=:number_done,
								created_by=:created_by,
								alias=:alias
								";
						$error="Unable to add xray to tplan xray count";
								$placeholders['tplan_id']=$tplan_id;
								$placeholders['pid']=$pid;
								$placeholders['procedure_id']=$data[0];

								$placeholders['teeth']="$teeth_procedure";
								$placeholders['details']=$_POST["new_details$i"];
								$placeholders['unauthorised_cost']=$amount;
								$placeholders['authorised_cost']=$authorised_cost;
								$placeholders['pay_type']=$pay_type;
								$placeholders['number_done']=$nt;
								if(isset($_POST["new_alias$i"]) and $_POST["new_alias$i"]!=''){$placeholders['alias']=1;}
								else{$placeholders['alias']=0;}
								$placeholders['created_by']=$_SESSION['id'];
								$s = 	insert_sql($sql, $placeholders, $error, $pdo);

					}//end non xray procedure


				$i++;
			}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){
				//get patient number will be used to refresh page
				$sql=$error1=$s='';$placeholders=array();
				$sql="select patient_number from patient_details_a where pid=:pid";
				$error="Unable to get patient number";
				$placeholders[':pid']=$pid;
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$patient_number=html($row['patient_number']);

				}
				$message="good#Changes saved#$patient_number";}
			//elseif(!$tx_result){}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$message="bad#Unable to edit Lab Technicians  ";
	}
	$data=explode('#',"$message");
	if($data[0]=='good'){
		$_SESSION['result_class']='success_response';
		$_SESSION['result_message']="$data[1]";
	}
echo "$message";
}

//this will submit edit invoice
elseif(isset($_SESSION['edit_inv_2b']) and isset($_POST['edit_inv_2b']) and $_SESSION['edit_inv_2b']==$_POST['edit_inv_2b']){
	$nimeana=$encrypt->decrypt($_POST['nimeana']);
	//echo "$nimeana xx";
	$data=explode('ninye',"$nimeana");
	//print_r($data);exit;
	$old_count=$data[2];
	$new_count=$data[0];
	$pid=$data[3];
	$tplan_id=$data[4];
	$invoice_id=$data[5];
	$exit_flag=false;
	$has_authorised='';

	//check if the entry is for an aliased invoice
	$is_invoice_aliased=0;
	$aliased='';
	if( $invoice_id > 0){
			$is_invoice_aliased = is_invoice_id__alias($pdo,$invoice_id);
	}

	//get current treatments from tplan into session
	$sql=$error1=$s='';$placeholders=array();
	$sql="select tplan_id, c.name as procedure_name, procedure_id, teeth, unauthorised_cost, treatment_procedure_id, invoice_number, pay_type, authorised_cost
		from tplan_procedure a join procedures c on a.procedure_id=c.id where invoice_id=:invoice_id";
	$error="Unable to get treatments from tplan";
	$placeholders[':invoice_id']=$invoice_id;
	$s = select_sql($sql, $placeholders, $error, $pdo);
	$before_edit_tproc_id_array=$before_edit_procedure_id_array=$before_edit_teeth_array=$before_edit_unauthorised_cost_array=array();
	$before_edit_procedure_name_array=$before_edit_tplan_id_array=$before_edit_invoice_id_array=$before_edit_invoice_number_array=$before_edit_pay_type_array=array();
	foreach($s as $row){
		if($row['pay_type']==1){$row['pay_type']='Insurance';}
		elseif($row['pay_type']==2){$row['pay_type']='Cash';}
		elseif($row['pay_type']==3){$row['pay_type']='Loyalty Points';}
		$before_edit_tproc_id_array[]=html($row['treatment_procedure_id']);
		$before_edit_procedure_id_array[]=html($row['procedure_id']);
		$before_edit_teeth_array[]=html($row['teeth']);
		$before_edit_unauthorised_cost_array[]=html($row['unauthorised_cost']);
		$before_edit_authorised_cost_array[]=html($row['authorised_cost']);
		$before_edit_pay_type_array[]=html($row['pay_type']);
		$before_edit_invoice_number_array[]=html($row['invoice_number']);
		$before_edit_invoice_id_array[]=html($row['treatment_procedure_id']);
		$before_edit_tplan_id_array[]=html($row['tplan_id']);
		$before_edit_procedure_name_array[]=html($row['procedure_name']);
	}


	//check if pre-auth or smart is needed for this patient
	$pre_auth_needed=$smart_needed='';
	$sql=$error1=$s='';$placeholders=array();
	$sql="select pre_auth_needed, smart_needed from covered_company a, patient_details_a b where b.type=a.insurer_id and b.company_covered=a.id
		and b.pid=:pid";
	$error="Unable to check if pre-auth is needed";
	$placeholders[':pid']=$pid;
	$s = select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$pre_auth_needed=html($row['pre_auth_needed']);
		$smart_needed=html($row['smart_needed']);
	}
	try{
		$pdo->beginTransaction();
			//update old treatments
			$i=1;
			while($i <= $old_count){

				//this will delete old treatment
				if(isset($_POST["old_action$i"]) and $_POST["old_action$i"]=='delete'){
					//echo "4706";
					$sql=$error=$s='';$placeholders=array();
					$sql="delete from tplan_procedure where treatment_procedure_id=:treatment_procedure_id";
					$error="Unable to delete procedure from tplan";
					$placeholders[':treatment_procedure_id']=$encrypt->decrypt($_POST["old_ninye$i"]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
				}
				else{ //for updating check if parameters are properly set

					$procedure_type=$encrypt->decrypt($_POST["ninye_oo$i"]);

					//check if procedure is set
					if($_POST["old_procedure$i"]==''){
							$message="bad#edit_invoice#Unable to save details as not all treatment procedures have been set, please check if all treatment procedures have been specified";
							$exit_flag=true;
							break;
					}

					//$var=$encrypt->decrypt($_POST["old_procedure$i"]);
					//$data=explode('#',"$var");//xrays will have #
					//these are common checks
					//check if cost is set
					if(!isset($_POST["old_cost$i"])){
							$message="bad#edit_invoice#Unable to save details as procedure cost is not specified for all procedures, please ensure that each procedure has a cost";
							$exit_flag=true;
							break;
					}

					//check if  payment method is set
					if(!isset($_POST["old_pay_method$i"])){
							$message="bad#edit_invoice#Unable to save details as payment method is not specified, please ensure that each procedure has a payment method specified";
							$exit_flag=true;
							break;
					}

					//check amount
					//remove commas
					$amount=str_replace(",", "", $_POST["old_cost$i"]);
						//check if amount is integer
					if(!ctype_digit($amount)){//echo "ooooo $unit_price[$i] ";
						//check if it has only 2 decimal places
						$data=explode('.',$amount);
						$invalid_amount=html("$amount");
						if ( count($data) != 2 ){

						$message="bad#edit_invoice#Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;
						break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#edit_invoice#Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;
						break;
						}
					}


					//check amount for authorised_cost
					if(isset($_POST["old_cost_authorised$i"]) and $_POST["old_cost_authorised$i"]!=''){
						//remove commas
						$amount2=str_replace(",", "", $_POST["old_cost_authorised$i"]);
							//check if amount is integer
						if(!ctype_digit($amount2)){//echo "ooooo $unit_price[$i] ";
							//check if it has only 2 decimal places
							$data=explode('.',$amount2);
							$invalid_amount=html("$amount2");
							if ( count($data) != 2 ){

							$message="bad#edit_invoice#Unable to save details as authorised cost $invalid_amount is not a valid number. ";
							$exit_flag=true;
							break;
							}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$message="bad#edit_invoice#Unable to save details as authorised cost $invalid_amount is not a valid number. ";
							$exit_flag=true;
							break;
							}
						}
					}

					//check if authorised cost is more than amount billed
					if(isset($_POST["old_cost_authorised$i"]) and $_POST["old_cost_authorised$i"]!='' and isset($_POST["old_cost$i"]) and $amount2 > $amount){
							$amount2=number_format(html($amount2,2));
							$amount=number_format(html($amount,2));
							$message="bad#edit_invoice#Unable to save details as authorised cost $amount2 is more than the billed cost $amount";
							$exit_flag=true;
							break;
					}

					//check if pay type is valid
					$pay_type=$encrypt->decrypt($_POST["old_pay_method$i"]);
					if($pay_type!=1 and $pay_type!=2 and $pay_type!=3){
						$message="bad#edit_invoice#Unable to save details as payment method is not correctly set for all procedures, please ensure that a payment method is set for each procedure. ";
						$exit_flag=true;
						break;
					}

					//check if all insurance items are authorised or not all authorised can't have them in between
					if($pay_type == 1 ){
						if(isset($_POST["old_cost_authorised$i"]) and $_POST["old_cost_authorised$i"]!=''){
							if($has_authorised==''){$has_authorised=true;}
							elseif($has_authorised==false){
								$message="bad#edit_invoice#Unable to save changes as some invoiced procedures have an authorised cost while others dont. Please check this. ";
								$exit_flag=true;
								break;
							}
						}
						elseif(isset($_POST["old_cost_authorised$i"]) and $_POST["old_cost_authorised$i"]==''){
							if($procedure_type=='old_uninvoiced' and $_POST["old_action$i"]!='invoice'){}
							elseif($procedure_type=='old_invoiced' and $_POST["old_action$i"]=='uninvoice'){}
							else{
									if($has_authorised==''){$has_authorised=false;}
									elseif($has_authorised==true){
										$message="bad#edit_invoice#Unable to save changes as some invoiced procedures have an authorised cost while others dont. Please check this. ";
										$exit_flag=true;
										break;
									}
							}
						}

					}

					//check if paytype is set to insurance for those to be invoiced
					if($procedure_type=='old_uninvoiced' and $pay_type != 1 and $_POST["old_action$i"]=='invoice'){
						$message="bad#edit_invoice#Unable to add treatment procedure to invoice as pay type is not correctly set. ";
						$exit_flag=true;
						break;
					}

					//check if authorised cost is set and make procedure invoiced
					if(isset($_POST["old_cost_authorised$i"]) and $_POST["old_cost_authorised$i"]!='' and $procedure_type=='old_uninvoiced'  and $pay_type==1 and $_POST["old_action$i"]!='invoice'){
						$message="bad#edit_invoice#Unable to save changes as uninvoiced procedure with authorised cost is not invoiced. ";
						$exit_flag=true;
						break;
					}

					if(isset($_POST["old_ninye$i"])){$tproc_id=$encrypt->decrypt($_POST["old_ninye$i"]);}
					$v=$encrypt->decrypt($_POST["old_procedure$i"]);
					$data=explode('#',"$v");

					//for non xray procedurescheck if xrays
					if(!$exit_flag and !isset($data[1]) and $data[0]!=1){

						//get procedure types
						$sql=$error=$s='';$placeholders=array();
						$sql="select id , all_teeth, name from procedures";
						$error="Unable to get procedure types";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						$procedure_id=$procedure_all_teeth=$procedure_name1=array();
						foreach($s as $row){
							$procedure_id["$row[id]"]=$row['id'];
							$procedure_all_teeth["$row[id]"]=$row['all_teeth'];
							$procedure_name1["$row[id]"]=html($row['name']);
						}

						//check if procedure is valid tyoe
						if (!in_array($data[0], $procedure_id)) {
							$message="bad#edit_invoice#Unable to save details as not all procedures are correctly set";
							$var=html("$data[0]");
							$security_log="sombody tried to input $var into tplan_procedure";
							log_security($pdo,$security_log);
							$exit_flag=true;
							break;
						}
						//check if teeth are specified
						elseif (in_array($data[0], $procedure_id)) {
							$proc_id=$data[0];
							$procedure_done_name="$procedure_name1[$proc_id]";
							$teeth_procedure='';
							$nt=1;
							//echo "-$i- ".$_POST["old_teeth_specified$i"]." --";
							if(isset($_POST["old_teeth_specified$i"]) and $procedure_all_teeth["$proc_id"] == 'yes'){
								$xt=$_POST["old_teeth_specified$i"];
								$nt=count($xt);
								$ni=0;

								while($ni < $nt){
									if($ni == 0){$teeth_procedure=$encrypt->decrypt("$xt[$ni]");}
									else{$teeth_procedure="$teeth_procedure, ".$encrypt->decrypt("$xt[$ni]");}
									$ni++;
								}
							}
							elseif(!isset($_POST["old_teeth_specified$i"]) and $procedure_all_teeth["$proc_id"] == 'yes'){
								$message="bad#edit_invoice#Unable to save details as no tooth has been specified for $procedure_name1[$proc_id]";
								$exit_flag=true;
								break;
							}
						}

						if(!isset($_POST["old_ninye$i"])){
							$procedure_in_alias_invoice='';
							$sql=$error=$s='';$placeholders=array();

							if($is_invoice_aliased == 1){
								//get the invoice number
								$sql=$error=$s='';$placeholders=array();
								$sql="select invoice_number from tplan_procedure where invoice_id=:invoice_id";
								$error="Unable to get invoice number";
								$placeholders['invoice_id']=$invoice_id;
								$s = select_sql($sql, $placeholders, $error, $pdo);
								if($s->rowCount() > 0){
									foreach($s as $row){
										$invoice_number=html($row['invoice_number']);
									}
								}
								$procedure_in_alias_invoice = "
									,procedure_in_alias_invoice=1 ,
									invoice_number=:invoice_number,
									invoice_id=:invoice_id
								";
								$placeholders['invoice_number']="$invoice_number";
								$placeholders['invoice_id']=$invoice_id;

							}
							//now perform update of tplan

							$sql="insert tplan_procedure set
									tplan_id=:tplan_id,
									procedure_id=:procedure_id,
									teeth=:teeth,
									details=:details,
									unauthorised_cost=:unauthorised_cost,
									authorised_cost=:authorised_cost,
									status=0,
									pay_type=:pay_type,
									date_procedure_added=now(),
									number_done=:number_done,
									created_by=:created_by,
									pid=:pid $procedure_in_alias_invoice
									";
							$error="Unable to add xray to tplan xray count";
									$placeholders['procedure_id']=$data[0];
									$placeholders['tplan_id']=$tplan_id;
									$placeholders['teeth']="$teeth_procedure";
									$placeholders['details']=$_POST["old_details$i"];
									$placeholders['unauthorised_cost']=$amount;
									if($pay_type == 1 ){
										//is it old_invoiced getting uninvoiced
										$procedure_type=$encrypt->decrypt($_POST["ninye_oo$i"]);
										if($procedure_type=='new_procedure' and  ($pre_auth_needed=='YES' or $smart_needed=='YES')){
											$placeholders['authorised_cost']=NULL;
										}
										elseif($procedure_type=='new_procedure' and  $pre_auth_needed!='YES' and $smart_needed!='YES'){
											$placeholders['authorised_cost']=$amount;
										}
										//else{$placeholders['authorised_cost']=NULL;}
									}
									else{$placeholders['authorised_cost']=$amount;}
									$placeholders['pay_type']=$pay_type;
									$placeholders['number_done']=$nt;
									$placeholders['created_by']=$_SESSION['id'];
									$placeholders['pid']=$pid;
									$s = 	insert_sql($sql, $placeholders, $error, $pdo);


						}
						//now perform update of tplan
						elseif(isset($_POST["old_ninye$i"])){
							$sql=$error=$s='';$placeholders=array();
							$sql="update tplan_procedure set
									procedure_id=:procedure_id,
									teeth=:teeth,
									details=:details,
									unauthorised_cost=:unauthorised_cost,
									authorised_cost=:authorised_cost,
									status=0,
									pay_type=:pay_type,
									date_procedure_added=now(),
									number_done=:number_done,
									created_by=:created_by
									where treatment_procedure_id=:treatment_procedure_id
									";
							$error="Unable to add xray to tplan xray count";
									$placeholders['procedure_id']=$data[0];
									$placeholders['treatment_procedure_id']=$encrypt->decrypt($_POST["old_ninye$i"]);
									$placeholders['teeth']="$teeth_procedure";
									$placeholders['details']=$_POST["old_details$i"];
									$placeholders['unauthorised_cost']=$amount;
									if($pay_type == 1 ){
										//is it old_invoiced getting uninvoiced
										$procedure_type=$encrypt->decrypt($_POST["ninye_oo$i"]);
										if($procedure_type=='old_invoiced' and $_POST["old_action$i"]!='uninvoice' and isset($_POST["old_cost_authorised$i"])){
											if($_POST["old_cost_authorised$i"]==''){$placeholders['authorised_cost']=NULL;}
											elseif($_POST["old_cost_authorised$i"]!=''){$placeholders['authorised_cost']=$_POST["old_cost_authorised$i"];}
										}
										elseif($procedure_type=='old_invoiced' and $_POST["old_action$i"]=='uninvoice' and ($pre_auth_needed=='YES' or $smart_needed=='YES')){
											$placeholders['authorised_cost']=NULL;
										}
										elseif($procedure_type=='old_invoiced' and $_POST["old_action$i"]=='uninvoice' and ($pre_auth_needed!='YES' and $smart_needed!='YES')){
											$placeholders['authorised_cost']=$amount;
										}
										elseif($procedure_type=='old_uninvoiced' and  isset($_POST["old_cost_authorised$i"])){
											if($_POST["old_cost_authorised$i"]==''){$placeholders['authorised_cost']=NULL;}
											elseif($_POST["old_cost_authorised$i"]!=''){$placeholders['authorised_cost']=$_POST["old_cost_authorised$i"];}
										}
										else{$placeholders['authorised_cost']=NULL;}
									}
									else{$placeholders['authorised_cost']=$amount;}
									$placeholders['pay_type']=$pay_type;
									$placeholders['number_done']=$nt;
									$placeholders['created_by']=$_SESSION['id'];
									$s = 	insert_sql($sql, $placeholders, $error, $pdo);

							//now act on uninvoiced
							if($procedure_type=='old_invoiced' and ($pay_type == 2 or $pay_type == 3 or $_POST["old_action$i"]=='uninvoice')){
								$sql=$error=$s='';$placeholders=array();
								$sql="update tplan_procedure set
										invoice_number='',
										invoice_id=0
										where treatment_procedure_id=:treatment_procedure_id
										";
								$error="Unable to update invoice";
										$placeholders['treatment_procedure_id']=$tproc_id;
										$s = 	insert_sql($sql, $placeholders, $error, $pdo);
							}
							//now act on invoiced
							elseif($procedure_type=='old_uninvoiced' and $pay_type == 1 and $_POST["old_action$i"]=='invoice'){
								//get the invoice number
								$sql=$error=$s='';$placeholders=array();
								$sql="select invoice_number from tplan_procedure where invoice_id=:invoice_id";
								$error="Unable to get invoice number";
								$placeholders['invoice_id']=$invoice_id;
								$s = select_sql($sql, $placeholders, $error, $pdo);
								if($s->rowCount() > 0){
									foreach($s as $row){
										$invoice_number=html($row['invoice_number']);
									}
									$sql2=$error2=$s2='';$placeholders2=array();
									$sql2="update tplan_procedure set
											invoice_number=:invoice_number,
											invoice_id=:invoice_id
											where treatment_procedure_id=:treatment_procedure_id
											";
									$error2="Unable to update invoice";
											$placeholders2['invoice_number']="$invoice_number";
											$placeholders2['invoice_id']=$invoice_id;
											$placeholders2['treatment_procedure_id']=$tproc_id;
											$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
								}
								else{
									$message="bad#edit_invoice#Unable to add procedure to invoice due to missing invoice number. Please contact support";
									$exit_flag=true;
									break;

								}

							}
						}
					}//end non xray procedure
				} //end for none delete opeartion

				$i++;
			}


			//insert new treatments


			//comnpare new invoice details with old ones and record if need be
			$sql=$error1=$s='';$placeholders=array();
			$sql="select tplan_id, b.name,procedure_id, teeth, unauthorised_cost, treatment_procedure_id, invoice_number, pay_type,
				authorised_cost from tplan_procedure a join procedures b on a.procedure_id=b.id where invoice_id=:invoice_id";
			$error="Unable to get treatments from tplan";
			$placeholders[':invoice_id']=$invoice_id;
			$s = select_sql($sql, $placeholders, $error, $pdo);
				$edited_tprocid=array();
				foreach($s as $row){
					if($row['pay_type']==1){$row['pay_type']='Insurance';}
					elseif($row['pay_type']==2){$row['pay_type']='Cash';}
					elseif($row['pay_type']==3){$row['pay_type']='Loyalty Points';}
					$edit=false;
					$edited_tprocid[]=$row['treatment_procedure_id'];
					//check if this is a new procedure
					if(!in_array("$row[treatment_procedure_id]",$before_edit_tproc_id_array)){
						$edit=true;
						$billed_cost="0.00";
						if($row['unauthorised_cost'] > 0){$billed_cost=html(number_format($row['unauthorised_cost'],2));}
						$description=html("Procedure, $row[name] billed for $billed_cost  added to invoice");
					}
					//this is for procedures in array
					else{
						$key = array_search("$row[treatment_procedure_id]", $before_edit_tproc_id_array);

						//check if billed cost is differenrt
						if($row['procedure_id'] == $before_edit_procedure_id_array[$key] and $row['unauthorised_cost'] != $before_edit_unauthorised_cost_array[$key]){
							$edit=true;
							$old_billed_cost="0.00";
							$new_billed_cost="0.00";
							if($before_edit_unauthorised_cost_array[$key] > 0){$old_billed_cost=number_format($before_edit_unauthorised_cost_array[$key],2);}
							if($row['unauthorised_cost'] > 0){$new_billed_cost=number_format($row['unauthorised_cost'],2);}
							$description=html("Procedure, $row[name] changed billed cost from $old_billed_cost to $new_billed_cost");
						}

						//check if the procedure has changed
						elseif($row['procedure_id'] != $before_edit_procedure_id_array[$key]){
							$edit=true;
							$old_billed_cost="0.00";
							$new_billed_cost="0.00";
							if($before_edit_unauthorised_cost_array[$key] > 0){$old_billed_cost=number_format($before_edit_unauthorised_cost_array[$key],2);}
							if($row['unauthorised_cost'] > 0){$new_billed_cost=number_format($row['unauthorised_cost'],2);}
							$description=html("Procedure, $before_edit_procedure_name_array[$key] at billed cost $old_billed_cost changed to
								$row[name] billed at $new_billed_cost");
						}
					}

					if($edit){
						$sql=$error1=$s='';$placeholders=array();
						$sql="insert into invoice_edits set
							tplan_id=:tplan_id,
							invoice_id=:invoice_id,
							user_id=:user_id,
							when_edited=now(),
							edit_description=:edit_description,
							treatment_procedure_id=:treatment_procedure_id";
						$error="Unable to insert invoice edits";
						$placeholders[':tplan_id']=$row['tplan_id'];
						$placeholders[':invoice_id']=$invoice_id;
						$placeholders[':user_id']=$_SESSION['id'];
						$placeholders[':edit_description']="$description";
						$placeholders[':treatment_procedure_id']=$row['treatment_procedure_id'];
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}

				}

				//check if any old invoiced procedure is no longer invoiced
				$i=0;
				$n=count($before_edit_tproc_id_array);
				while($i < $n){
					if(!in_array($before_edit_tproc_id_array["$i"],$edited_tprocid)){
						$old_billed_cost="0.00";
						if($before_edit_unauthorised_cost_array[$key] > 0){$old_billed_cost=number_format($before_edit_unauthorised_cost_array[$i],2);}

						$description=html("Procedure, $before_edit_procedure_name_array[$i] billed at $old_billed_cost removed from invoice");
						$sql=$error1=$s='';$placeholders=array();
						$sql="insert into invoice_edits set
							tplan_id=:tplan_id,
							invoice_id=:invoice_id,
							user_id=:user_id,
							when_edited=now(),
							edit_description=:edit_description,
							treatment_procedure_id=:treatment_procedure_id";
						$error="Unable to insert invoice edits";
						$placeholders[':tplan_id']=$before_edit_tplan_id_array[$i];
						$placeholders[':invoice_id']=$invoice_id;
						$placeholders[':user_id']=$_SESSION['id'];
						$placeholders[':edit_description']="$description";
						$placeholders[':treatment_procedure_id']=$before_edit_tproc_id_array[$i];
						$s = insert_sql($sql, $placeholders, $error, $pdo);

					}
					$i++;
				}

				//update pt_balances
				$pid_encrypt2=$encrypt->encrypt($pid);
				$result=show_pt_statement_brief($pdo,$pid_encrypt2,$encrypt);

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$message="good#edit_invoice#Changes saved#$_POST[edit_inv_2c]";}
			//elseif(!$tx_result){}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$message="bad#Unable to edit Lab Technicians  ";
	}
echo "$message";
}


//this is for creating a new family group
elseif(isset($_POST['new_family']) and $_POST['new_family']!='' and userHasRole($pdo,12)){
	?>
		<form action="#pt_contact_fm_form1" method="POST"  name="" id="" class='patient_form'>
			<div class='grid-15'><label class="label">Select Action</label></div>
			<div class='grid-30'><select name=action1 class='new_family_action'>
					<option></option>
					<option value='new'>Add patient to new family group</option>
					<option value='old'>Add patient to existing family group</option>
				</select>
				<?php $token = form_token(); $_SESSION['token_ptf_a'] = "$token";  ?>
				<input type="hidden" name="token_ptf_a"  value="<?php echo $_SESSION['token_ptf_a']; ?>" />
			</div>
			<div class=clear></div><br>
			<!--new family group -->
			<div class='grid-100 new_fam_grp no_padding'>
				<div class='grid-15'><label class="label">Family Name</label></div>
				<div class='grid-50'><input type=text name=family_name /></div>
				<div class=clear></div><br>
				<div class='grid-15'><label for="" class="label">Relationship</label></div>
				<div class='grid-15'><select name=family_title><option></option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from patient_relationships order by name";
						$error = "Unable to list patient relationships";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}

					?>
					</select>
				</div>
				<div class=clear></div><br>
				<div class='grid-10 prefix-15'><input type=submit value=Submit /></div>
			</div>

			<!-- add pt to existing family groyp-->
			<div class='grid-100 old_fam_grp no_padding'>
				<div class='grid-15'><label class="label">Search for group by</label></div>
				<div class='grid-30'>
					<select name=search_criteria>
						<option></option>
						<option value='group_name'>Group Name</option>
						<option value='first_name'>Member's First Name</option>
						<option value='middle_name'>Member's Middle Name</option>
						<option value='last_name'>Member's Last Name</option>
						<option value='patient_number'>Member's Patient Number</option>
					</select>
				</div>
				<div class='grid-40'><input type=text name=criteria_value /></div>
				<div class='grid-10 '><input type=submit value=Find /></div>
			</div>
		</form>
		<br>
		<div class='grid-100 ' id='imwe_family'></div>


		<?php
}

//this is for adding a pt to an existing family group
elseif(isset($_POST['token_ptf_b']) and isset($_SESSION['token_ptf_b']) and $_SESSION['token_ptf_b']==$_POST['token_ptf_b']
and userHasRole($pdo,12)){
	$exit_flag=false;
	if(!$exit_flag  and $_POST['family_title']==''){
		$message="bad#family_pt#Please specify the patient's relationship in the family group";
		$exit_flag=true;
	}
	if(!$exit_flag){
		try{
			$pdo->beginTransaction();
				//now update current patient
				$sql=$error1=$s='';$placeholders=array();
				$sql="update patient_details_a set family_id=:family_id , family_title=:family_title where pid=:pid";
				$error="Unable to add family group id to patient";
				$placeholders['family_id']=$encrypt->decrypt($_POST['ninye']);
				$placeholders['pid']=$_SESSION['pid'];
				$placeholders['family_title']=$encrypt->decrypt($_POST['family_title']);
				$s = insert_sql($sql, $placeholders, $error, $pdo);
				if($s){ $pdo->commit();$message='good#family_pt#';}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#   Unable to save patient details  ";
		}
	}
	if(isset($message)){echo "$message";}
}

//this is for creating a new follow up in t done
elseif(isset($_POST['follow_up']) and $_POST['follow_up']!='' and userHasRole($pdo,20)){
if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';
			}

		}


?>
	<div class='feedback hide_element'></div>
	<form action="" method="POST" enctype="" name="" id="" class="patient_form" >
			<?php $token = form_token(); $_SESSION['token_cf1'] = "$token";  ?>
			<input type="hidden" name="token_cf1"  value="<?php echo $_SESSION['token_cf1']; ?>" />
			<div class='grid-15 label'>Follow up Date</div><div class='grid-10'><input type=text class=date_picker_no_past name=follow_up_date /></div>
			<div class=clear></div><br>
			<div class='grid-15 label'>Follow Up Questions</div><div class='grid-50'><textarea name=follow_up_question rows=5></textarea></div>
			<div class=clear></div><br>
			<div class='grid-10 prefix-15'><input type=submit value=Submit /></div>
	</form>
	<div class=clear></div><br>
<?php
//show previous  follow ups
	//GET ANY PENDING follow ups
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.first_name, a.middle_name, a.last_name,  a.mobile_phone, a.biz_phone, b.id , b.treatment_plan_id,
		b.follow_up_date, b.status
		from patient_details_a a, follow_ups b  where b.pid=:pid and b.pid=a.pid  order by b.id desc";
	$error="Unable to get follows  for single patient";
	$placeholders['pid']=$_SESSION['pid'];
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){ ?>

		<div class=waiver_table3><div class=tplan_table_caption><?php echo "$_SESSION[first_name] $_SESSION[middle_name] $_SESSION[last_name] FOLLOW UPS"; ?></div>
		<div class='waiver_table_row2 '>
			<div class='cfu_tplan no_border_bottom white_text'>TREATMENT PLAN ID</div>
			<div class='cfu_comments white_text'>COMMENTS</div>
			<div class='cfu_status no_border_bottom white_text'>STATUS</div>
		</div>
		</div>
		<div class=waiver_table3><!--table definition -->
	<?php
			$i=0;
		foreach($s as $row){
			$i++;
			$follow_up_date=html($row['follow_up_date']);
			$treatment_pan=html($row['treatment_plan_id']);
			$status='';
			if($row['status']==1){
				$status='Follow Up Finished';
			}
			elseif($row['status']==0 and $follow_up_date!=''){
				$status="Next follow up is on $follow_up_date";
			}
			echo "<div class='waiver_table_row2 waiver_row'>"; //table row
				echo "<div class=cfu_tplan>$treatment_pan</div>";//pt name
				//check if we have extra comments apart from original
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select b.date_of_comment, b.comment, c.first_name, c.middle_name, c.last_name
					from follow_up_communication b , users c
					where b.follow_up_id=:follow_up_id and b.user_id = c.id order by b.id";
				$placeholders2[':follow_up_id']=$row['id'];
				$error2="Unable to get follow up comments";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='no_border_right tplan_procedure_row'>";		?>
				<div class='waiver_sub_header waiver_table_row2'><!--comment headers-->
						<div class='cfu_date_comment'>Date</div> <!-- comment date -->
						<div class=cfu_user>User</div> <!-- comment made by -->
						<div class='cfu_comment  no_border_right'>Comment</div> <!-- comment  -->
				</div>
						<?php //now show newer comments
							foreach($s2 as $row2){ ?>
								<div class='waiver_table_row2 '>
									<div class=cfu_date_comment><?php htmlout($row2['date_of_comment']); ?></div> <!-- comment date -->
									<div class=cfu_user><?php ucfirst(htmlout("$row2[2] $row2[3] $row2[4]")); ?></div> <!-- comment made by -->
									<div class='cfu_comment  no_border_right'><?php htmlout($row2['comment']); ?></div> <!-- comment  -->
								</div>
							<?php

							}

				echo "</div>"; //end 	 tplan_procedure_row
				echo "<div class=cfu_status>$status</div>";//balance
			echo "</div>"; //end 	 waiver_table_row2
		}
		echo "</div>"; //end 	 waiver_table

	}

}

//this is for submiting a follow up the first time
elseif(isset($_POST['token_cf1']) and isset($_SESSION['token_cf1']) and $_SESSION['token_cf1']==$_POST['token_cf1']
	and userHasRole($pdo,20)){
	$exit_flag=false;
	//check date
	if(!$exit_flag  and $_POST['follow_up_date']==''){
		$message="bad#follow_up#Please specify the follow up date";
		$exit_flag=true;
	}
	//check if question is set
	if(!$exit_flag  and $_POST['follow_up_question']==''){
		$message="bad#follow_up#Please specify the follow up question";
		$exit_flag=true;
	}

	if(!$exit_flag ){
		try{
				$pdo->beginTransaction();
				//insert in follow up
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into follow_ups set created_by=:created_by, when_added=now(),
						treatment_plan_id=:treatment_plan_id, follow_up_date=:follow_up_date,pid=:pid";
				$error="Unable to create follow up";
				$placeholders['created_by']=$_SESSION['id'];
				$placeholders['treatment_plan_id']=$_SESSION['tplan_id'];
				$placeholders['follow_up_date']=$_POST['follow_up_date'];
				$placeholders['pid']=$_SESSION['pid'];
				$id = get_insert_id($sql, $placeholders, $error, $pdo);


				//now insert first communication
				//check if user has ability to end follow up
				$user_type=0;
				if(userHasSubRole($pdo,9)){
					$user_type=1;
				}
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into follow_up_communication set
				follow_up_id=:follow_up_id,
				date_of_comment=now(),
				comment=:comment,
				user_id=:user_id,
				user_type=:user_type
						";
				$error="Unable to create follow comment";
				$placeholders['follow_up_id']=$id;
				$placeholders['comment']=$_POST['follow_up_question'];
				$placeholders['user_id']=$_SESSION['id'];
				$placeholders['user_type']=$user_type;
				$s = insert_sql($sql, $placeholders, $error, $pdo);

				if($s ){ $pdo->commit();$message='good#follow_up#Follow up created';}
				else{ $pdo->rollBack();}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#   Unable to save patient details  ";
		}
	}
			$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";

		}
		echo $message;

}

//this is for waiver correspondence
elseif(isset($_POST['token_cf2']) and isset($_SESSION['token_cf2']) and $_SESSION['token_cf2']==$_POST['token_cf2']
and userHasRole($pdo,95)){
	$exit_flag=false;

	if(!$exit_flag ){
		try{
			$pdo->beginTransaction();
			$action=$_POST['action'];
			$follow_up=$_POST['ninye'];
			$comment=$_POST['comment'];
			$next_follow_up=$_POST['next_follow_up'];
			$user_type=0;
			$pending=' pending=1 ';
			if(userHasSubRole($pdo,9)){
				$user_type=1;
				$pending=' pending=0 ';
			}
			$i=0;$n=count($action);
			while($i < $n){
				$follow_up_id=$encrypt->decrypt($follow_up[$i]);
				$action_name=$encrypt->decrypt("$action[$i]");
				//for accept
				if($action_name=='approved'){
					$sql=$error=$s='';$placeholders=array();
					$sql="update follow_ups set status=1, pending=0 where id=:follow_up_id";
					$error="Unable to complete follow up";
					$placeholders['follow_up_id']=$follow_up_id;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
				}
				elseif($action_name=='replied'){
					$sql=$error=$s='';$placeholders=array();
					$sql="update follow_ups set $pending where id=:follow_up_id";
					$error="Unable to update pending  follow up";
					$placeholders['follow_up_id']=$follow_up_id;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
				}

				//for reply we just add the comment, this will also any comment included in the accpetance
				if($comment[$i]!=''){
						/*$user_type=0;
						if(userHasSubRole($pdo,9)){
							$user_type=1;
						}*/
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into follow_up_communication set follow_up_id=:follow_up_id,
							date_of_comment=now(), comment=:comment, user_id=:user_id, user_type=:user_type";
						$error="Unable to add follow up comment";
						$placeholders[':follow_up_id']=$follow_up_id;
						$placeholders[':comment']=$comment[$i];
						$placeholders[':user_id']=$_SESSION['id'];
						$placeholders[':user_type']=$user_type;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						//echo "	$waiver_id - 	$comment[$i] - $_SESSION[id] - $user_type";
				}

				//update next follow up date
				if($next_follow_up[$i]!=''){
					$sql=$error=$s='';$placeholders=array();
					$sql="update follow_ups set follow_up_date=:follow_up_date where id=:follow_up_id";
					$error="Unable to complete follow up";
					$placeholders['follow_up_id']=$follow_up_id;
					$placeholders['follow_up_date']=$next_follow_up[$i];
					$s = insert_sql($sql, $placeholders, $error, $pdo);
				}
				$i++;
			}

				if($s and !$exit_flag){ $pdo->commit();$message='good#follow_up_comment#Changes Saved';}
				elseif(!$s or $exit_flag){ $pdo->rollBack();}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#   Unable to save patient details  ";
		}
	}
			$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";

		}
		echo $message;
}

//this is for waiver correspondence
elseif(isset($_POST['token_wap1']) and isset($_SESSION['token_wap1']) and $_SESSION['token_wap1']==$_POST['token_wap1']
and userHasRole($pdo,52)){
	$exit_flag=false;

	if(!$exit_flag ){
		try{
			$pdo->beginTransaction();
			$action=$_POST['action'];
			$waiver=$_POST['ninye'];
			$comment=$_POST['comment'];
			$pid_amount=$_POST['ninye2'];
			$i=0;$n=count($action);
			while($i < $n){
				$waiver_id=$encrypt->decrypt($waiver[$i]);
				$action_name=$encrypt->decrypt("$action[$i]");
				$data=$encrypt->decrypt("$pid_amount[$i]");
				$result=explode('#',$data);
				$amount=$result[0];
				$pid=$result[1];
				//for decline
				if($action_name=='denied'){
					$sql=$error=$s='';$placeholders=array();
					$sql="update waiver_approvals set pay_id=-1 where id=:waiver_id";
					$error="Unable to decline waiver";
					$placeholders['waiver_id']=$waiver_id;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
				}

				//for accept
				if($action_name=='approved'){
					//check if amount is valid number
					//remove commas
					$amount=str_replace(",", "", $amount);
					if(!ctype_digit($amount)){
						//check if it has only 2 decimal places
						$data=explode('.',$amount);
						$invalid_value=html($amount);
						if ( count($data) != 2 ){
						$message="bad#Unable to accept waiver request. ";
						$exit_flag=true;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$message="bad#Unable to accept waiver request. ";
						$exit_flag=true;
						}
					}

					//subrole 6 is for approving waivers
					$receipt_number='';
					$rid=0;
					//first get receipt number for non insured payment
					$sql=$error=$s='';$placeholders=array();
					$sql="select max(receipt_num) from non_insurance_receipt_id_generator";
					$error="Unable to get non insured receipt number";
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$rid=$row[0] + 1;}
					if($rid == 0){$rid = 1;}

					$sql=$error=$s='';$placeholders=array();
					$sql="insert into non_insurance_receipt_id_generator set receipt_num =:rid";
					$error="Unable to get non insured receipt number";
					$placeholders[':rid']=$rid;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					$receipt_number="R$rid-".date('m/y');
					$receipt_num_id=$rid;

					//now that i have receipt number i can insert payment details
					if($receipt_number != ''){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into payments set when_added=now(), receipt_num=:receipt_num,
							amount=:amount,
							pay_type=6,
							pid=:pid,
							receipt_num_id=:receipt_num_id,
							created_by=:created_by,
							tx_number=''";
						$error="Unable to make non-insured payment";
						$placeholders[':receipt_num']="$receipt_number";
						$placeholders[':amount']=$amount;
						$placeholders[':pid']=$pid;
						$placeholders[':created_by']=$_SESSION['id'];
						$placeholders[':receipt_num_id']=$receipt_num_id ;
						$id = get_insert_id($sql, $placeholders, $error, $pdo);

						//now get patient self balance
						$result=show_pt_statement_brief($pdo,$encrypt->encrypt("$pid"),$encrypt);
						//echo "xx $result xx";
						$result=str_replace(",", "", "$result");

						$data=explode('#',"$result");
						if($data[1] == 0){$bal="Cash balance is 0.00";}
						elseif($data[1] > 0){$bal="Cash balance is KES: ".number_format($data[1],2);}
						elseif($data[1] < 0){
							$data[1]=str_replace("-", "", "$data[1]");
							$bal="Cash credit is KES: ".number_format($data[1],2);}

						//insert balance statement
						$sql=$error=$s='';$placeholders=array();
						$sql="update payments set balance=:balance where id=:id";
						$error="Unable to make add balance to payment";
						$placeholders[':id']=$id;
						$placeholders[':balance']="$bal";
						$s = insert_sql($sql, $placeholders, $error, $pdo);

						//update waiver approvals with new pay_id
						$sql=$error=$s='';$placeholders=array();
						$sql="update waiver_approvals set pay_id=:pay_id where id=:id";
						$error="Unable to compete waiver acceptance";
						$placeholders[':id']=$waiver_id;
						$placeholders[':pay_id']=$id;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}
					if($s){
						$pid_bal="pid_$pid";
						$_SESSION["$pid_bal"]=array();
						$result=show_pt_statement_brief($pdo,$encrypt->encrypt("$pid"),$encrypt);
						$data=explode('#',"$result");
						$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
					}
				}

				//for reply we just add the comment, this will also any comment included in the accpetance or decline
				if($comment[$i]!=''){
						$user_type=0;
						if(userHasSubRole($pdo,6)){
							$user_type=1;
						}
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into waiver_approval_communication set waiver_id=:waiver_id,
							date_of_comment=now(), comment=:comment, user_id=:user_id, user_type=:user_type";
						$error="Unable to add waiver comment";
						$placeholders[':waiver_id']=$waiver_id;
						$placeholders[':comment']=$comment[$i];
						$placeholders[':user_id']=$_SESSION['id'];
						$placeholders[':user_type']=$user_type;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						//echo "	$waiver_id - 	$comment[$i] - $_SESSION[id] - $user_type";
				}
				$i++;
			}

				if($s and !$exit_flag){ $pdo->commit();$message='good#waiver_approval#Changes Saved';}
				elseif(!$s or $exit_flag){ $pdo->rollBack();}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#   Unable to save patient details  ";
		}
	}
			$data=explode('#',"$message");
		/*if($data[0]=='bad'){$_SESSION['result_class']='bad';
							$_SESSION['result_message']="$data[1]";
		}*/
		if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";

		}
		echo $message;
}

//this is for creating a new family group from above form
elseif(isset($_POST['token_ptf_a']) and isset($_SESSION['token_ptf_a']) and $_SESSION['token_ptf_a']==$_POST['token_ptf_a']
and userHasRole($pdo,12)){
	$exit_flag=false;
	if(!$exit_flag and $_POST['action1']=='old' and $_POST['search_criteria']==''){
		$message="bad#family_pt#Please specify the family group search criteria";
		$exit_flag=true;
	}
	if(!$exit_flag and $_POST['action1']=='old' and $_POST['criteria_value']==''){
		$message="bad#family_pt#Please specify the family group serach value";
		$exit_flag=true;
	}
	if(!$exit_flag and $_POST['action1']=='new' and $_POST['family_name']==''){
		$message="bad#family_pt#Please specify the family group name";
		$exit_flag=true;
	}
	if(!$exit_flag and $_POST['action1']=='new' and $_POST['family_title']==''){
		$message="bad#family_pt#Please specify the patient's relationship in the family group";
		$exit_flag=true;
	}
	//check if the family name is repeated
	if(!$exit_flag and $_POST['action1']=='new'){

		$sql=$error=$s='';$placeholders=array();
		$sql="select name from family_group where upper(name)=:name";
		$error="Unable to check if family name exists";
		$placeholders['name']=strtoupper($_POST['family_name']);
		$s = select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			$var=html($_POST['family_name']);
			$message="bad#family_pt#Family group $var already exists, please use another name";
			$exit_flag=true;
		}
	}
	if(!$exit_flag and $_POST['action1']=='old'){
		if(isset($_POST['selected_fm']) and $_POST['selected_fm']!=''){
			show_family_group_members($pdo, $_POST['selected_fm'], $encrypt, 'add_member');
		}
		elseif(!isset($_POST['selected_fm'])){
			//check if that family group name exists
			$result=check_if_family_group_exists($_POST['search_criteria'],$_POST['criteria_value'],$pdo,$encrypt);
			$data = explode('#',$result);
			$result=$data[0];
			if($result==1){
				if(isset($data[1])){show_family_group_members($pdo, $encrypt->encrypt("$data[1]"), $encrypt, 'add_member');}
			}
			elseif($result==2){
				echo "inakwatafamily<label class=label>Your search does criteria does not match any family group.</label>";
			}
		}
	}
	if(!$exit_flag and $_POST['action1']=='new'){
		try{
			$pdo->beginTransaction();
			if($_POST['action1']=='new'){
				//get family id
				$sql=$error1=$s='';$placeholders=array();
				$sql="insert into family_group set name=:name";
				$error="Unable to add family group name";
				$placeholders['name']=$_POST['family_name'];
				$id = get_insert_id($sql, $placeholders, $error, $pdo);

				//now update current patient
				$sql=$error1=$s='';$placeholders=array();
				$sql="update patient_details_a set family_id=:family_id , family_title=:family_title where pid=:pid";
				$error="Unable to add family group id to patient";
				$placeholders['family_id']=$id;
				$placeholders['pid']=$_SESSION['pid'];
				$placeholders['family_title']=$encrypt->decrypt($_POST['family_title']);
				$s = insert_sql($sql, $placeholders, $error, $pdo);
				if($s){ $pdo->commit();$message='good#family_pt#';}
			}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#   Unable to save patient details  ";
		}
	}
	if(isset($message)){echo "$message";}
}

//this is for shpwing the family group in non insurance payments to enable credit transfer
elseif(isset($_POST['ninye1']) and $_POST['ninye1']!='' and userHasRole($pdo,50)){
	get_pt_family_memebrs_for_credit_transfer($pdo, $_POST['ninye1'], $encrypt);
}

//this is for shpwing the family group in patient contacts after the family group has jiust been cteaed
elseif(isset($_POST['get_fam']) and $_POST['get_fam']=='yes' and userHasRole($pdo,12)){
	if($_SESSION['pid']!=''){
		$pid=$encrypt->encrypt($_SESSION['pid']);
		get_pt_family_memebrs($pdo, $pid, $encrypt);
	}
}

//this will shw treatment history from treatment done
elseif(isset($_POST['tplan_history']) and $_POST['tplan_history']!='' and userHasRole($pdo,120)){
	get_tplan_history($pdo, $_POST['tplan_history']);
	}

//this will shw treatment history from treatment done
elseif(isset($_POST['treatment_history']) and $_POST['treatment_history']!='' and userHasRole($pdo,20)){
	get_treatments_done($pdo, $encrypt->encrypt($_SESSION['pid']), $encrypt);
	}

//this will shw patient statement from treatment done -- old implementation not touched as it may affect some other stuff
elseif(isset($_POST['pt_statement']) and $_POST['pt_statement']!='' and userHasRole($pdo,20)){
	show_pt_statement($pdo, $encrypt->encrypt($_SESSION['pid']), $encrypt);
	}

//this will shw patient statement from treatment done -- new impelemtation to include swapped guys
elseif(isset($_POST['pt_statement_swapped']) and $_POST['pt_statement_swapped']!='' and userHasRole($pdo,20)){
	show_pt_statement_also_with_swapped($pdo, $encrypt->encrypt($_SESSION['pid']), $encrypt);
	}

//this will shw patient statement from waiver approvals
elseif(isset($_POST['pt_statement_a']) and $_POST['pt_statement_a']!='' and userHasRole($pdo,52)){
	show_pt_statement($pdo, $_POST['pt_statement_a'], $encrypt);
	}


//this is for tdone appointment
elseif(isset($_POST['tdone_appointment3']) and $_POST['tdone_appointment3']!=''){
	$_SESSION['appointment_from_holidays']=$encrypt->decrypt($_POST['tdone_appointment3']);
	if(!userHasRole($pdo,122)){
		echo "<div class='error_response'>You don't have permission to create an appointment</div>";
		exit;
	}
	?>
	<div class='grid-100 feedback hide_element'></div>

	<!--<div class='grid-35 select_date'>-->

		<div class='grid-15 '><label for="" class="label">Appointment Date </label></div>
		<div class='grid-20 '><input type=text id=appointment_datex class='date_picker_no_past tdone_appointment_date3' /></div>
	<!--</div>-->
	<div class='grid-65 show_current_appointments'></div>
	<div class=clear></div><br>
	<div class='grid-100' id='tdone_appointment_times'></div>
	<?php
}

//this is for tdone appointment
elseif(isset($_POST['tdone_appointment'])){
	if(!userHasRole($pdo,20)){
		echo "<div class='error_response'>You don't have permission to make a prescription</div>";
		exit;
	}
	?>
	<div class='grid-100 feedback hide_element'></div>

		<div class='grid-15 appointment_type_div'><label for="" class="label">Appointment Type </label></div>
		<div class='grid-20 appointment_type_div'><select class='appointment_type'>
			<option value='normal'>Normal</option>
			<option value='school_holiday'>School Holiday</option>
		</select></div>

	<div class=clear></div><br>

	<!-- show school holiday options -->
	<form action="" method="POST"  name="" id="" class="patient_form">
	<div class='grid-15 holiday_class'><label for="" class="label">Holiday Period </label></div>
	<div class='grid-20 holiday_class'>


			<?php $token = form_token(); $_SESSION['token_shr'] = "$token";
				$mgonjwa = $encrypt->encrypt($_SESSION['pid']);
				$daktari = $encrypt->encrypt($_SESSION['id']);
				echo "<input type='hidden' name='token_ninye' id='token_ninye' value='$mgonjwa' />";
				echo "<input type='hidden' name='token_ninye2' id='token_ninye2' value='$daktari' />";
			?>
			<input type="hidden" name="token_shr"  value="<?php echo $_SESSION['token_shr']; ?>" />
		<?php
			$sql=$error=$s='';$placeholders=array();
			$sql="select id, description from school_holiday_description where notify_date > curdate() order by notify_date";
			$error="Unable to get school holidays";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			echo "<select name='holiday_period'><option></option>";
			foreach($s as $row){
				$val=$encrypt->encrypt($row['id']);
				$holiday_period=html($row['description']);
				echo "<option value='$val'>$holiday_period</option>";
			}
			echo "</select>";
		?>
	</div>

	<div class='grid-5 holiday_class'><label for="" class="label">Task </label></div>
	<div class='grid-20 holiday_class'><textarea    rows='2' name=holiday_task ></textarea></div>
	<div class='grid-5 holiday_class'><input type=submit value='Submit' /></div>
	</form>


	<div class=clear></div><br>
	<!--<div class='grid-35 select_date'>-->

		<div class='grid-15 select_date'><label for="" class="label">Appointment Date </label></div>
		<div class='grid-20 select_date'><input type=text id=appointment_date class='date_picker_no_past tdone_appointment_date'/></div>
	<!--</div>-->
	<div class='grid-65 show_current_appointments'></div>
	<div class=clear></div><br>
	<div class='grid-100' id='tdone_appointment_times'></div>
	<?php
}

//this will show current appointments in creating appooinntment in school holidays
elseif(isset($_POST['tdone_appointment_date4']) and $_POST['tdone_appointment_date4']!='' and userHasRole($pdo,122)){
	$_POST['tdone_appointment_date2']=$_POST['tdone_appointment_date4'];
	$appointment_date=html($_POST['tdone_appointment_date4']);
	$data=explode('#',$_SESSION['appointment_from_holidays']);

	$doc_id=$data[3];
	$date_of_appointment=html($_POST['tdone_appointment_date2']);
	//get docotor appointmateds for unregisterad
	$sql=$error=$s='';$placeholders=array();
	$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names, b.shour, b.smin, b.rank ,b.am_pm ,     c.first_name,c.middle_name,c.last_name, 				b.treatment
		from unregistered_patient_appointments as b join unregistered_patients as a on b.pid=a.id and b.doc_id=:doc_id
		and b.appointment_date=:appointment_date
		join users as c on c.id=b.doc_id";
	$error="Unable to get unregisterd apponitments for doctors";
	$placeholders['doc_id']=$doc_id;
	$placeholders['appointment_date']=$date_of_appointment;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$appoint_array=array();
	foreach($s as $row){
		$doctor_name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
		$patient_name=ucfirst(html($row['names']));
		$time=html("$row[shour]:$row[smin] $row[am_pm]");
		$rank=html($row['rank']);
		$smin=html($row['smin']);
		$treatment= html($row['treatment']);
		$appoint_array[]=array('doctor'=>"$doctor_name", 'patient_name'=>"$patient_name", 'time'=>"$time", 'rank'=>"$rank", 'smin'=>"$smin",'treatment'=>"$treatment");
	}

	//get docotor appointmateds for registerad
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.first_name,a.middle_name,a.last_name, b.shour, b.smin, b.rank ,b.am_pm , c.first_name,c.middle_name,c.last_name,b.treatment
		from registered_patient_appointments as b join patient_details_a as a on b.pid=a.pid and b.doc_id=:doc_id
		and b.appointment_date=:appointment_date
		join users as c on c.id=b.doc_id";
	$error="Unable to get registerd apponitments for doctors";
	$placeholders['doc_id']=$doc_id;
	$placeholders['appointment_date']=$date_of_appointment;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$doctor_name=ucfirst(html("$row[7] $row[8] $row[9]"));
		$patient_name=ucfirst(html("$row[0] $row[1] $row[2]"));
		$time=html("$row[shour]:$row[smin] $row[am_pm]");
		$rank=html($row['rank']);
		$smin=html($row['smin']);
		$treatment= html($row['treatment']);
		$appoint_array[]=array('doctor'=>"$doctor_name", 'patient_name'=>"$patient_name", 'time'=>"$time", 'rank'=>"$rank",  'smin'=>"$smin",'treatment'=>"$treatment");
	}

	if(count($appoint_array) > 0){
		foreach ($appoint_array as $key => $row) {
			$rank1[$key]  = $row['rank'];
			$smin1[$key]  = $row['smin'];
		}

		// Sort the data with when_added
		array_multisort($rank1, SORT_ASC,$smin1, SORT_ASC, $appoint_array);
		$i=0;
		foreach($appoint_array as $row){
			if($i==0){
				echo "<table class='normal_table'><caption>Appointments for Dr. $doctor_name on $date_of_appointment</caption><thead>
					<tr><th class='ds_count'></th><th class='ds_pname'>PATIENT NAME</th><th class='ds_time'>TIME</th><th class='ds_task'>APPOINTMENT TASK</th></tr></thead><tbody>";
			}
			$i++;
			echo "<tr><td>$i</td><td>$row[patient_name]</td><td>$row[time]</td><td>$row[treatment]</td></tr>";

		}
		echo "</tbody></table>";
	}
	else{echo "<div class='label'>There are no appointments for the doctor on the day</div>";}
}

//this will show current appointments in tdone appointment
elseif(isset($_POST['tdone_appointment_date2']) and $_POST['tdone_appointment_date2']!='' and userHasRole($pdo,20)){
	$appointment_date=html($_POST['tdone_appointment_date2']);
	$doc_id=$_SESSION['id'];
	$date_of_appointment=html($_POST['tdone_appointment_date2']);
	//get docotor appointmateds for unregisterad
	$sql=$error=$s='';$placeholders=array();
	$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names, b.shour, b.smin, b.rank ,b.am_pm ,     c.first_name,c.middle_name,c.last_name, 				b.treatment
		from unregistered_patient_appointments as b join unregistered_patients as a on b.pid=a.id and b.doc_id=:doc_id
		and b.appointment_date=:appointment_date
		join users as c on c.id=b.doc_id";
	$error="Unable to get unregisterd apponitments for doctors";
	$placeholders['doc_id']=$doc_id;
	$placeholders['appointment_date']=$date_of_appointment;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$appoint_array=array();
	foreach($s as $row){
		$doctor_name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
		$patient_name=ucfirst(html($row['names']));
		$time=html("$row[shour]:$row[smin] $row[am_pm]");
		$rank=html($row['rank']);
		$smin=html($row['smin']);
		$treatment= html($row['treatment']);
		$appoint_array[]=array('doctor'=>"$doctor_name", 'patient_name'=>"$patient_name", 'time'=>"$time", 'rank'=>"$rank", 'smin'=>"$smin",'treatment'=>"$treatment");
	}

	//get docotor appointmateds for registerad
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.first_name,a.middle_name,a.last_name, b.shour, b.smin, b.rank ,b.am_pm , c.first_name,c.middle_name,c.last_name,b.treatment
		from registered_patient_appointments as b join patient_details_a as a on b.pid=a.pid and b.doc_id=:doc_id
		and b.appointment_date=:appointment_date
		join users as c on c.id=b.doc_id";
	$error="Unable to get registerd apponitments for doctors";
	$placeholders['doc_id']=$doc_id;
	$placeholders['appointment_date']=$date_of_appointment;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$doctor_name=ucfirst(html("$row[7] $row[8] $row[9]"));
		$patient_name=ucfirst(html("$row[0] $row[1] $row[2]"));
		$time=html("$row[shour]:$row[smin] $row[am_pm]");
		$rank=html($row['rank']);
		$smin=html($row['smin']);
		$treatment= html($row['treatment']);
		$appoint_array[]=array('doctor'=>"$doctor_name", 'patient_name'=>"$patient_name", 'time'=>"$time", 'rank'=>"$rank",  'smin'=>"$smin",'treatment'=>"$treatment");
	}

	if(count($appoint_array) > 0){
		foreach ($appoint_array as $key => $row) {
			$rank1[$key]  = $row['rank'];
			$smin1[$key]  = $row['smin'];
		}

		// Sort the data with when_added
		array_multisort($rank1, SORT_ASC,$smin1, SORT_ASC, $appoint_array);
		$i=0;
		foreach($appoint_array as $row){
			if($i==0){
				echo "<table class='normal_table'><caption>Appointments for Dr. $doctor_name on $date_of_appointment</caption><thead>
					<tr><th class='ds_count'></th><th class='ds_pname'>PATIENT NAME</th><th class='ds_time'>TIME</th><th class='ds_task'>APPOINTMENT TASK</th></tr></thead><tbody>";
			}
			$i++;
			echo "<tr><td>$i</td><td>$row[patient_name]</td><td>$row[time]</td><td>$row[treatment]</td></tr>";

		}
		echo "</tbody></table>";
	}
	else{echo "<div class='label'>There are no appointments for the doctor on the day</div>";}
}

//this will make a prescription
elseif(isset($_POST['prescribe'])){
	if(!userHasRole($pdo,20)){
		echo "<div class='error_response'>You don't have permission to make a prescription</div>";
		exit;
	}
	echo "<div class='feedback2 hide_element'></div>";
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']=='success_response'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';
			}
		}

	?>
	<div class='grid-100 no_padding'><input type=button class='new_prescription2 button_style' value='New Prescription' /></div>
	<div class='grid-100 grid-parent new_prescribe '>
	<form action="#prescribe_drug" method="POST"  name="" id="" class='tab_form patient_form'>
				<?php $token = form_token();
				//$token ="2297487ffa0be065c4c87d8b01c8ee264de58e0b";
				$_SESSION['token_presc_pta'] = "$token";  ?>
				<input class='token_presc_pta' type="hidden" name="token_presc_pta"  value="<?php echo $_SESSION['token_presc_pta']; ?>" />
		<!--<div class='grid-40 '><label class=label>SELECT DRUG</label></div>
		<div class='grid-10  '><label class=label>QUANTITY</label></div>
		<div class='grid-25  '><label class=label>DETAILS</label></div>
		<div class='grid-15  '><label class=label>PRESCRIPTION TYPE</label></div>
		<div class='grid-10  '><label class=label>PRICE</label></div>
		-->

		<div class='grid-35 '><label class=label>SELECT DRUG</label></div>
		<div class='grid-25  '><label class=label>DETAILS</label></div>
		<div class='grid-15  '><label class=label>PRESCRIPTION TYPE</label></div>
		<div class='grid-15  '><label class=label>QUANTITY</label></div>
		<div class='grid-10  '><label class=label>PRICE</label></div>
		<div class=clear></div>
		<div class='presc_container grid-100 no_padding'>
			<div class='grid-100 highlight_on_hover1 no_padding'>
				<div class=grid-35 >
					<select name='drug[]' class='drug_name' ><option></option> <?php
						$sql=$error1=$s='';$placeholders=array();
						$sql="select name, selling_price, id from drugs where listed!=1 order by name";
						$error="Unable to get drugs";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$name=html($row['name']);
							$price=html($row['selling_price']);
							if($price > 0){$price=number_format($price,2);}
							else{$price='';}
							$val=$encrypt->encrypt($row['id']);
							echo "<option value='$val'>$name</option>";
						} ?>
					</select>
					<div class=get_drug_quantity></div>
				</div>
				<div class=' grid-20'><textarea disabled class='drug_details' width='100%' name='details[]' ></textarea></div>
				<div class=grid-20>
					<select name='presc_type[]' disabled class='drug_presc_type' ><option></option>
						<?php
							$self=$encrypt->encrypt("2");//self
							$presc=$encrypt->encrypt("presc");
							echo "<option value=$self>Sell</option>
									<option value=$presc>Prescribe</option>";
						?>
					</select>
					<br>
					<div class=commissioners_div>

					<label class=label>Commission</label>
					<select name='commissioners[]' class='commissioners' ><option></option> <?php
						$sql=$error1=$s='';$placeholders=array();
						$sql="select first_name,middle_name,last_name ,id from users where status='active' order by first_name";
						$error="Unable to get users";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$name=html("$row[first_name] $row[middle_name] $row[last_name]");
							$val=$encrypt->encrypt($row['id']);
							echo "<option value='$val'>$name</option>";
						} ?>
					</select>
					</div>
				</div>

					<div class=' grid-5 '><input disabled type=text name='drug_quantity[]' class='drug_quantity'  /></div>
					<div class=' grid-10  drug_unit_price'>&nbsp;<!-- unit cost here --></div>

				<div class=grid-10><input disabled type=text name='price[]' class='drug_price'  /></div>
				<div class=clear></div><BR>
				<div class='grid-100 grey_bottom_border'></div>
			</div>
		</div>
		<div class=grid-10><input type=button class='button_style add_drug' value='Add Drug' /></div>
		<div class=grid-10><input type=button class=' button_style prescribe_cancel' value='cancel' /></div>
		<div class='prefix-70 grid-10'><input type=submit class=' button_style' value='Submit' /></form></div>

	</div>
		<br>
	<?php
	//get previous prescriptions
	$sql=$error1=$s='';$placeholders=array();
	$sql="select a.when_added, b.name, a.details, c.first_name, c.middle_name, c.last_name , a.prescription_id , a.prescription_number
	from drugs b, prescriptions a, users c
		where b.id=a.drug_id and c.id=a.created_by and a.pid=:pid order by a.prescription_id desc";
	$error="Unable to check if pre-auth is needed";
	$placeholders[':pid']=$_SESSION['pid'];
	$s = select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount()>0){
		echo "<form action='' method='post' name='' id=''><table class='normal_table'><caption>Prescription Drugs</caption><thead>
		<tr><th class=presc_date>DATE PRESCRIBED</th><th class=presc_number>PRESCRIPTION NUMBER</th>
		<th class=presc_name>PRESCRIPTION</th>
		<th class=presc_doc>PRESCRIBING DOCTOR</th></tr></thead><tbody>";
			$drug='';$prescription_id='';
		foreach($s as $row){

			if($prescription_id==''){$prescription_id=html($row['prescription_id']);}
			else{
				//check if it has changed or not so that we print it
				if($prescription_id!=$row['prescription_id']){
					echo "<tr><td>$date</td><td><input type=button class='button_style show_prescription' value=$prescription_number /></td><td>$drug</td><td>$doctor</td></tr>";
					$prescription_id=html($row['prescription_id']);
					$drug='';
				}

			}
			if($drug==''){$drug=html("$row[name]  $row[details]");}
			elseif($drug!=''){$drug="$drug <br>".html("$row[name]  $row[details]");}
			$doctor=html("$row[first_name] $row[middle_name] $row[last_name]");
			$date=html($row['when_added']);
			$prescription_number=html("$row[prescription_number]");

		}
		echo "<tr><td>$date</td><td><input type=button class='button_style show_prescription' value=$prescription_number /></td><td>$drug</td><td>$doctor</td></tr>";
		echo "</tbody></table>";
	}
	else{echo "<label class=label>This patient does not have any prescription records</label>";}
}

//this will show form to edit an invoice
elseif(isset($_POST['edit_invoice']) and $_POST['edit_invoice']!=''){
	if(!userHasRole($pdo,60)){
		echo "<div class='error_response'>You don't have permission to edit an invoice</div>";
		exit;
	}
	//check token validity
	$token_valid=false;
	$var=$encrypt->decrypt($_POST['edit_invoice']);
	$invoice_id_token=html("$_POST[edit_invoice]");
	$data=explode('#',"$var");
	$invoice_id=$data[0];
	$token_value="$data[1]";

	//check if the entry is for an aliased invoice
	$is_invoice_aliased=0;
	$aliased='';
	if($invoice_id > 0){
			$is_invoice_aliased = is_invoice_id__alias($pdo,$invoice_id);
			if($is_invoice_aliased == 1){$aliased="<br>Alias";}
	}

	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select token_value from invoice_edit_token where invoice_id=:invoice_id";
	$placeholders2[':invoice_id']=$invoice_id;
	$error2="Unable to get invoice edit token";
	$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
	foreach($s2 as $row2){
		if($row2['token_value'] == "$data[1]"){$token_valid=true;}
	}
	if(!$token_valid){
		echo "<div class='error_response'>The link used to edit this invoice is expired, please obtain a new one";
		exit;
	}
	$old_procedure_invoiced=$encrypt->encrypt("old_invoiced");
	$old_procedure_uninvoiced=$encrypt->encrypt("old_uninvoiced");
	$invoice_authorisation_sent=$invoice_authorisation_received=false;

	//check if pre-auth or smart is needed for this patient
	$pre_auth_needed=$smart_needed='';
	$sql=$error1=$s='';$placeholders=array();
	$sql="select pre_auth_needed, smart_needed from covered_company a, patient_details_a b , tplan_procedure c
			where b.type=a.insurer_id and b.company_covered=a.id and b.pid=c.pid and invoice_id=:invoice_id group by invoice_id";
	$error="Unable to check if pre-auth is needed";
	$placeholders[':invoice_id']=$invoice_id;
	$s = select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$pre_auth_needed=html($row['pre_auth_needed']);
		$smart_needed=html($row['smart_needed']);
	}

	//check if the invoice is sent for authorisation
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select invoice_id, authorisation_sent, authorisation_received from invoice_authorisation where invoice_id=:invoice_id";
	$placeholders2[':invoice_id']=$invoice_id;
	$error2="Unable to check if invoice is authorised";
	$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
	foreach($s2 as $row2){
		$invoice_authorisation_sent=true;
		$invoice_authorisation_received=true;
	}

	//$data=explode('ninye',$_POST['edit_tplan']);
	//$tplan_id=$encrypt->decrypt("$data[0]");
	//$pid=$encrypt->decrypt("$data[1]");

	//$tplan_id=html($tplan_id);
	//get invoice treatment procedures
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select b.treatment_procedure_id, a.name, b.teeth, b.details , case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'
			end as status , b.procedure_id, all_teeth ,
			case b.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points' end as pay_type
			, b.invoice_number, b.unauthorised_cost, b.authorised_cost, pid ,b.tplan_id from procedures a, tplan_procedure b where b.invoice_id=:invoice_id and
		  b.procedure_id=a.id";
	$placeholders2[':invoice_id']=$invoice_id;
	$error2="Unable to get invoice procedures";
	$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
	//$has_invoice=false;
	//put invoice procedures into array
	$treatment_procedure_id_array=$procedure_name_array=$teeth_array=$details_array=$status_array=$pay_type_array=$invoice_number_array=array();
	$invoiced_array=$unauthorised_cost_array=$authorised_cost_array=$procedure_id_array=$all_teeth_array=array();
	$points_sum=$insurance_sum=$self_sum=0;
	foreach($s2 as $row2){
		$pid=html($row2['pid']);
		$invoice_number=html($row2['invoice_number']);
	//	if($row2['invoice_number'] != ''){$invoiced_array[]=true;}else{$invoiced_array[]=false;}
		$procedure_id_array[]=html($row2['procedure_id']);
		if($row2['procedure_id'] == 1){
			$details_array[]='';
			$procedure_name_array[]=html($row2['details']);
		}
		elseif($row2['procedure_id'] != 1){
			$details_array[]=html($row2['details']);
			$procedure_name_array[]=html($row2['name']);
		}
		$all_teeth_array[]=html($row2['all_teeth']);
		$tplan_id_array[]=html($row2['tplan_id']);
		$treatment_procedure_id_array[]=$encrypt->encrypt(html($row2['treatment_procedure_id']));
		$treatment_procedure_id_clean_array[]=html($row2['treatment_procedure_id']);
		$teeth_array[]=html($row2['teeth']);

		$status_array[]=html($row2['status']);
		$pay_type_array[]=html($row2['pay_type']);
	//	$invoice_number_array[]=html($row2['invoice_number']);
		$unauthorised_cost_array[]=html(number_format($row2['unauthorised_cost'],2));
		if($row2['pay_type']=='Self'){
			$authorised_cost_array[]='';
			$self_sum = $self_sum + html($row2['unauthorised_cost']);
		}
		elseif($row2['pay_type']=='Points' ){
			$authorised_cost_array[]='';
			$points_sum = $points_sum + html($row2['unauthorised_cost']);
		}
		elseif($row2['pay_type']=='Insurance'){
			if(is_null($row2['authorised_cost'])){
				$authorised_cost_array[]='';
				$insurance_sum = $insurance_sum + html($row2['unauthorised_cost']);
			}
			elseif(!is_null($row2['authorised_cost'])){
				$authorised_cost_array[]=html($row2['authorised_cost']);
				$insurance_sum = $insurance_sum + html($row2['authorised_cost']);
				$self_sum = $self_sum + html($row2['unauthorised_cost'] - $row2['authorised_cost']);
			}
		}
	}
	echo "<div class='feedback hide_element'></div>";
	//get uniqe tplans from array
	$tplan_unique_array = array_unique($tplan_id_array);
	get_patient_basics($pdo,$pid,$encrypt);
	$total_sum=$insurance_sum + $self_sum;
	$total_sum=number_format($total_sum,2);
	$points_sum=number_format($points_sum,2);
	$insurance_sum=number_format($insurance_sum,2);
	$self_sum=number_format($self_sum,2);
	?>

	<span class='blue_shade_background'>Procedures in this invoice</span><br>
	<?php
		?> <form action="" method="post" name="" class='patient_form' id=""> <?php
		 $token = form_token(); $_SESSION['edit_inv_2b'] = "$token";  ?>
		<input type="hidden" name="edit_inv_2b"  value="<?php echo $_SESSION['edit_inv_2b']; ?>" />
		<?php
		echo "<input type='hidden' name='edit_inv_2c'  value='$invoice_id_token' />";
		$n=count($treatment_procedure_id_array);
		$i=$count=$old_count=0;
	//check if this patient type is insured or not
		$insured='NO';
		$sql=$error=$s='';$placeholders=array();
		$sql="select insured from covered_company a, patient_details_a b where b.pid=:pid and b.company_covered=a.id ";
		$error="Unable to check if the company is insured";
		$placeholders['pid']=$pid;
		$s = select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){$insured=html($row['insured']);}

		echo "<br>	<table id=edit_tplan_no_invoice class='normal_table '><caption>Invoice Number $invoice_number</caption><thead>
					<tr><th class=etp_count2></th><th class=etp_procedure2>TREATMENT PROCEDURE</th>
					<th class=etp_details2>PROCEDURE DETAILS</th><th class=etp_staus2>STATUS</th><th class=etp_pay_type2>PAYMENT TYPE</th>
					<th class=etp_cost2>COST</th><th class=etp_authorised_cost2>AUTHORISED<BR>COST</th><th class=etp_delete2>ACTION</th>
					</tr></thead><tbody>";
		while($i < $n){
			$count++;

			/*//check if invoiced and make uneditable
			if($invoiced_array[$i]){
				echo "<tr><td>$count</td><td>$procedure_name_array[$i] $teeth_array[$i]</td><td>$details_array[$i]</td><td old_pay_type>$pay_type_array[$i]</td>
						<td>$unauthorised_cost_array[$i]</td><td>$authorised_cost_array[$i]</td><td>Invoiced<br>$invoice_number_array[$i]</td></tr>";
			}/*/
		//	elseif(!$invoiced_array[$i]){
				//if stared or finished no editing
				/*if("$status_array[$i]" != 'Not Started'){
					echo "<tr><td>$count</td><td>$procedure_name_array[$i] $teeth_array[$i]</td><td>$details_array[$i]</td><td class=old_pay_type>$pay_type_array[$i]</td>
						<td>$unauthorised_cost_array[$i]</td><td>$authorised_cost_array[$i]</td><td>$status_array[$i]</td></tr>";
				}*/
			//	elseif("$status_array[$i]" == 'Not Started'){
					$old_count++;//class=invoiced
					echo "<tr class=blue_shade_background><td class=procedure_count>$count</td><td>";
						//show procedure
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name,id,all_teeth from procedures where id!=2 and id!=8 and id!=59 order by name";
						$error2="Unable to get prodcedures";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						echo "<select name=old_procedure$old_count class='input_in_table_cell select_procedure2' ><option></option>";
							foreach($s2 as $row2){
								$procedure=html($row2['name']);
								$val2=$encrypt->encrypt(html($row2['id']));

									if($procedure_id_array[$i] == $row2['id']){echo "<option value='$val2' selected >$procedure</option>"; }
									elseif($procedure_id_array[$i] != $row2['id']){echo "<option value='$val2'>$procedure</option>"; }

								/*elseif($row2['id'] == 1){echo "<optgroup label='X-RAYS'>";
									$xray_ida='';
									if($procedure_id_array[$i] == $row2['id']){
										//get the xray number
										$sql3=$error3=$s3='';$placeholders3=array();
										$sql3="select xray_id from tplan_xray_count where treatment_procedure_id=:treatment_procedure_id";
										$error3="Unable to get xrays";
										$placeholders3['treatment_procedure_id']=$treatment_procedure_id_clean_array[$i];
										$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
										foreach($s3 as $row3){
											$xray_ida=html($row3['xray_id']);
											//$val3=$encrypt->encrypt("$row2[id]#$row3[id]");
											//echo "<option value='$val3' >$xray</option>";
										}
									}
									$sql3=$error3=$s3='';$placeholders3=array();
									$sql3="select name,id,all_teeth from teeth_and_xray_types order by name";
									$error3="Unable to get xrays";
									$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
									foreach($s3 as $row3){
										$xray=html($row3['name']);
										$val3=$encrypt->encrypt("$row2[id]#$row3[id]");
										if($xray_ida == $row3['id']){echo "<option value='$val3' selected >$xray</option>"; }
										else{echo "<option value='$val3' >$xray</option>"; }
									}
									echo "</optgroup>";
								}*/
							}
							echo "</select>";
						//show teeth
						//if($all_teeth_array[$i] == 'yes'){
							$teeth_body='';
							$selected_teeth_array=explode(',',"$teeth_array[$i]");
							if($teeth_array[$i] != ''){$teeth_body=" teeth_body ";}

							echo "<div class='grid-100 teeth_div $teeth_body'>
								<div class='teeth_row'>
									<div class='hover  teeth_heading_cell'>Upper Right - 1x
										<div class='teeth_body2'>";


										$i2=8;
										$teeth_specified="old_teeth_specified$old_count"."[]";
										while($i2 >= 1){
											$number="1$i2";
											$checked=$highlight='';
											if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number  $highlight'>$number<br><input  $checked  class='tooth_checkbox2' type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2--;
										}	?>
										</div>
									</div>
									<div class='hover teeth_heading_cell'>Upper Left - 2x
										<div class='teeth_body2'>
										<?php
										$i2=1;
										while($i2 <= 8){
											$number="2$i2";
											$checked=$highlight='';
											if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2++;
										}	?>
										</div>
									</div>
								</div>
								<!-- second row -->
								<div class='teeth_row'>
									<div class='hover  no_padding teeth_heading_cell'>Lower Right - 4x
										<div class='teeth_body2'>
										<?php
										$i2=8;
										while($i2 >= 1){
											$number="4$i2";
											$checked=$highlight='';
											if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2--;
										}	?>
										</div>
									</div>
									<div class='hover  no_padding teeth_heading_cell'>Lower Left - 3x
										<div class='teeth_body2'>
										<?php
										$i2=1;
										while($i2 <= 8){
											$number="3$i2";
											$checked=$highlight='';
											if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2++;
										}	?>
										</div>
									</div>
								</div>

							</div>

				<?php	//	} //end show teeth
					//check pay type
					$self_selected=$points_selected=$insurance_selected='';
					if($pay_type_array[$i]=='Self'){$self_selected =" selected ";}
					elseif($pay_type_array[$i]=='Points'){$points_selected =" selected ";}
					elseif($pay_type_array[$i]=='Insurance'){$insurance_selected =" selected ";}
					$invoice_pay=$encrypt->encrypt("1");
					$cash_pay=$encrypt->encrypt("2");
					$points_pay=$encrypt->encrypt("3");
					echo "</td><td><textarea  class=tplan_details  rows='' name=old_details$old_count >$details_array[$i]</textarea></td>
						<td>$status_array[$i]</td>
					<td>
						<select  name=old_pay_method$old_count class='input_in_table_cell pay_method pay_method_inv pay_method_inv2' ><option></option>";
							if($insured == 'YES'){echo "<option value='$invoice_pay' $insurance_selected >Insurance</option>";}

							//for aliased invoice only insurance paytype is accepted
							if($is_invoice_aliased == 0){
								echo "<option value='$cash_pay' $self_selected >Self</option>
								<option value='$points_pay' $points_selected >Points</option>";
							}

						echo "</select></td><td><input type=text name=old_cost$old_count value=$unauthorised_cost_array[$i] class=tplan_cost2 /></td>
						<td>";
						if($pay_type_array[$i]=='Self' or $pay_type_array[$i]=='Points'){echo "<span class='na'>N/A</span>";}
						elseif($pay_type_array[$i]=='Insurance'){//only show auth_cost field for authorised or companies that don't need authorisation
							if($invoice_authorisation_sent==true or ($pre_auth_needed!='YES' and $smart_needed!='YES')){
								echo "<input type=text name=old_cost_authorised$old_count value='$authorised_cost_array[$i]' class=tplan_cost2 />
								<span class='na'>N/A</span>";
							}
							else{echo "un-authorised";}
						}
						echo "</td>
					<td><select name=old_action$old_count><option></option>";
						//for aliased invoice only delete makes senss
						if($is_invoice_aliased == 1){ echo "<option value='delete'>Delete</option>";}
						elseif($is_invoice_aliased == 0){ echo "<option value='uninvoice'>Uninvoice</option>";}
					echo "</select>
						<input type=hidden name=old_ninye$old_count value=$treatment_procedure_id_array[$i] class=tplan_remove />
						<input type=hidden name=ninye_oo$old_count value=$old_procedure_invoiced class=tplan_remove />
					</td></tr>";
		//		}
		//	}
			$i++;
		}

		//show other procedures in same treatment plan that have not yet been invoiced
		$n2=count($tplan_unique_array);
		$i2=0;
		while($i2 < $n2){
			$tplan_id=$tplan_unique_array[$i2];
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select b.treatment_procedure_id, a.name, b.teeth, b.details , case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'
					end as status , b.procedure_id, all_teeth ,
					case b.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points' end as pay_type
					, b.invoice_number, b.unauthorised_cost, b.authorised_cost, pid ,b.tplan_id from procedures a, tplan_procedure b where b.invoice_id=0 and b.tplan_id=:tplan_id and
				  b.procedure_id=a.id";
			$placeholders2[':tplan_id']=$tplan_id;
			$error2="Unable to get tplan procedures";
			$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
			//$has_invoice=false;
			//put invoice procedures into array
			$treatment_procedure_id_array=$procedure_name_array=$teeth_array=$details_array=$status_array=$pay_type_array=$invoice_number_array=array();
			$invoiced_array=$unauthorised_cost_array=$authorised_cost_array=$procedure_id_array=$all_teeth_array=array();
			$points_sum=$insurance_sum=$self_sum=0;
			foreach($s2 as $row2){
				$pid=html($row2['pid']);
			//	$invoice_number=html($row2['invoice_number']);
				if($row2['invoice_number'] != ''){$invoiced_array[]=true;}else{$invoiced_array[]=false;}
				$procedure_id_array[]=html($row2['procedure_id']);
				if($row2['procedure_id'] == 1){
					$details_array[]='';
					$procedure_name_array[]=html($row2['details']);
				}
				elseif($row2['procedure_id'] != 1){
					$details_array[]=html($row2['details']);
					$procedure_name_array[]=html($row2['name']);
				}
				$all_teeth_array[]=html($row2['all_teeth']);
				$tplan_id_array[]=html($row2['tplan_id']);
				$treatment_procedure_id_array[]=$encrypt->encrypt(html($row2['treatment_procedure_id']));

				$teeth_array[]=html($row2['teeth']);

				$status_array[]=html($row2['status']);
				$pay_type_array[]=html($row2['pay_type']);
				$invoice_number_array[]=html($row2['invoice_number']);
				$unauthorised_cost_array[]=html(number_format($row2['unauthorised_cost'],2));
				if($row2['pay_type']=='Self'){
					$authorised_cost_array[]='';
					$self_sum = $self_sum + html($row2['unauthorised_cost']);
				}
				elseif($row2['pay_type']=='Points' ){
					$authorised_cost_array[]='';
					$points_sum = $points_sum + html($row2['unauthorised_cost']);
				}
				elseif($row2['pay_type']=='Insurance'){
					if(is_null($row2['authorised_cost'])){
						$authorised_cost_array[]='';
						$insurance_sum = $insurance_sum + html($row2['unauthorised_cost']);
					}
					elseif(!is_null($row2['authorised_cost'])){
						$authorised_cost_array[]=html($row2['authorised_cost']);
						$insurance_sum = $insurance_sum + html($row2['authorised_cost']);
						$self_sum = $self_sum + html($row2['unauthorised_cost'] - $row2['authorised_cost']);
					}
				}
			}

				$n=count($treatment_procedure_id_array);
				$i=$count2=$old_count2=0;

				while($i < $n){
					$count++;

					/*//check if invoiced and make uneditable
					if($invoiced_array[$i]){
						echo "<tr><td>$count</td><td>$procedure_name_array[$i] $teeth_array[$i]</td><td>$details_array[$i]</td><td old_pay_type>$pay_type_array[$i]</td>
								<td>$unauthorised_cost_array[$i]</td><td>$authorised_cost_array[$i]</td><td>Invoiced<br>$invoice_number_array[$i]</td></tr>";
					}/*/
				//	elseif(!$invoiced_array[$i]){
						//if stared or finished no editing
						/*if("$status_array[$i]" != 'Not Started'){
							echo "<tr><td>$count</td><td>$procedure_name_array[$i] $teeth_array[$i]</td><td>$details_array[$i]</td><td class=old_pay_type>$pay_type_array[$i]</td>
								<td>$unauthorised_cost_array[$i]</td><td>$authorised_cost_array[$i]</td><td>$status_array[$i]</td></tr>";
						}*/
					//	elseif("$status_array[$i]" == 'Not Started'){
							$old_count++;
							echo "<tr class=''><td class=procedure_count>$count</td><td>";
								//show procedure
								$sql2=$error2=$s2='';$placeholders2=array();
								$sql2="select name,id,all_teeth from procedures order by name";
								$error2="Unable to get prodcedures";
								$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
								echo "<select name=old_procedure$old_count class='input_in_table_cell select_procedure2' ><option></option>";
									foreach($s2 as $row2){
										$procedure=html($row2['name']);
										$val2=$encrypt->encrypt(html($row2['id']));

											if($procedure_id_array[$i] == $row2['id']){echo "<option value='$val2' selected >$procedure</option>"; }
											elseif($procedure_id_array[$i] != $row2['id']){echo "<option value='$val2'>$procedure</option>"; }

									/*	elseif($row2['id'] == 1){echo "<optgroup label='X-RAYS'>";
											$sql3=$error3=$s3='';$placeholders3=array();
											$sql3="select name,id,all_teeth from teeth_and_xray_types order by name";
											$error3="Unable to get xrays";
											$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
											foreach($s3 as $row3){
												$xray=html($row3['name']);
												$val3=$encrypt->encrypt("$row2[id]#$row3[id]");
												echo "<option value='$val3' >$xray</option>";
											}
											echo "</optgroup>";
										}*/
									}
									echo "</select>";
								//show teeth
								//if($all_teeth_array[$i] == 'yes'){
									$teeth_body='';
									$selected_teeth_array=explode(',',"$teeth_array[$i]");
									if($teeth_array[$i] != ''){$teeth_body=" teeth_body ";}

									echo "<div class='grid-100 teeth_div $teeth_body'>
										<div class='teeth_row'>
											<div class='hover  teeth_heading_cell'>Upper Right - 1x
												<div class='teeth_body'>";


												$i2=8;
												$teeth_specified="old_teeth_specified$old_count"."[]";
												while($i2 >= 1){
													$number="1$i2";
													$checked=$highlight='';
													if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
													$name="tooth$number";
													//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
													echo "<div class='hover-row tooth_number  $highlight'>$number<br><input  $checked  class='tooth_checkbox2' type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
													$checked=$highlight='';
													if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
													$name="tooth$number";
													//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
													echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
													$checked=$highlight='';
													if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
													$name="tooth$number";
													//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
													echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
													$checked=$highlight='';
													if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
													$name="tooth$number";
													//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
													echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
													$i2++;
												}	?>
												</div>
											</div>
										</div>

									</div>

						<?php	//	} //end show teeth
							//check pay type
							$self_selected=$points_selected=$insurance_selected='';
							if($pay_type_array[$i]=='Self'){$self_selected =" selected ";}
							elseif($pay_type_array[$i]=='Points'){$points_selected =" selected ";}
							elseif($pay_type_array[$i]=='Insurance'){$insurance_selected =" selected ";}
							$invoice_pay=$encrypt->encrypt("1");
							$cash_pay=$encrypt->encrypt("2");
							$points_pay=$encrypt->encrypt("3");
							echo "</td><td><textarea  class=tplan_details  rows='' name=old_details$old_count >$details_array[$i]</textarea></td>
							<td>$status_array[$i]</td><td>
								<select  name=old_pay_method$old_count class='input_in_table_cell pay_method pay_method_inv' ><option></option>
									<option value='$invoice_pay' $insurance_selected >Insurance</option>
									<option value='$cash_pay' $self_selected >Self</option>
									<option value='$points_pay' $points_selected >Points</option>
								</select></td><td><input type=text name=old_cost$old_count value=$unauthorised_cost_array[$i] class=tplan_cost2 /></td>
								<td>";
						if($pay_type_array[$i]=='Self' or $pay_type_array[$i]=='Points'){echo "<span class=''>N/A</span>
							<input type=text name=old_cost_authorised$old_count value='$authorised_cost_array[$i]' class='tplan_cost2 na' />
						";}
						elseif($pay_type_array[$i]=='Insurance'){
							if($pre_auth_needed!='YES' and $smart_needed!='YES'){
								echo "<input type=text name=old_cost_authorised$old_count value='$authorised_cost_array[$i]' class=tplan_cost2 />
								<span class='na'>N/A</span>";
							}
							else{echo "un-authorised";}
							//echo "<input type=text name=old_cost_authorised$old_count value='$authorised_cost_array[$i]' class=tplan_cost2 />
							//<span class='na'>N/A</span>";
						}
						echo "</td>
							<td><select name=old_action$old_count><option></option><option value='invoice'>Invoice</option>
							<option value='delete'>Delete</option></select>
							<input type=hidden name=old_ninye$old_count value=$treatment_procedure_id_array[$i] class=tplan_remove />
						<input type=hidden name=ninye_oo$old_count value=$old_procedure_uninvoiced class=tplan_remove />
							</td></tr>";
				//		}
				//	}
					$i++;
				}
			$i2++;
		}
		echo "</table>"; ?>
	<div class='grid-50 '>
		<?php
			//will add new procedue for aliased invoice
			if($is_invoice_aliased == 1){
				echo "<input class=aliased_invoice   type=hidden value='aliased_invoice' />";
			}
			else{
				echo "<input class=aliased_invoice   type=hidden value='normal_invoice' />";
			}
			$zero=$encrypt->encrypt("0ninye$count"."ninye$old_count"."ninye$pid"."ninye$tplan_id"."ninye$invoice_id");
			echo "<input name=nimeana id=nimeana type=hidden value='$zero' />";
		?>
		<input type=button class="add_new_procedure_edit_tplan_no_invoice2  button_style" value="Add Procedure to Treatment Plan" />&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo "<input type=hidden value='$_POST[edit_invoice]' />"; ?>
		<input type=button id="undo_edit_invoice" class="button_style" value="Undo Changes" />

	</div>
	<div class='grid-50 put_right no_padding_right'>
			<div class='grid-40 prefix-50 '><label for="" class="label">Insurance total cost(Kes): </label></div>
			<div class='grid-10 no_padding_right' ><span id=treatment_plan_insurance_total class='put_right label'><?php echo $insurance_sum; ?></span></div>
		<div class='grid-40 prefix-50   '><label for="" class="label">Self total cost(Kes): </label></div>
			<div class='grid-10 no_padding_right' ><span id=treatment_plan_self_total class='put_right label '><?php echo $self_sum; ?></span></div>
		<div class='grid-40 prefix-50 '><label for="" class="label">Total cost(Kes): </label></div>
			<div class='grid-10 no_padding_right' ><span id=treatment_plan_sum class='put_right label'><?php echo $total_sum; ?></span></div>
		<div class='grid-40 prefix-50  '><label for="" class="label">Points total cost: </label></div>
			<div class='grid-10 no_padding_right' ><span id=treatment_plan_points_total class='put_right label'><?php echo $points_sum; ?></span></div>
		<input type=submit class='put_right'  value='Submit' /></form>
	</div>
			<?php

}


//this will append or unapaedn treatment to invoice
elseif(isset($_SESSION['ai1_token']) and isset($_POST['ai1_token']) and $_POST['ai1_token']==$_SESSION['ai1_token']
and userHasRole($pdo,20)){

	if(isset($_POST['add_invoice']) and $_POST['add_invoice']!='' and $_POST['add_invoice']!='new_invoice'){
		$element=$encrypt->decrypt("$_POST[ninye]");
		echo "good#append#$element#$_POST[add_invoice]";
	}
	elseif(isset($_POST['add_invoice']) and $_POST['add_invoice']!='' and $_POST['add_invoice']=='new_invoice'){
		$element=$encrypt->decrypt("$_POST[ninye]");
		echo "good#noappend#$element#$element";
	}

}

//this will append or unapaedn treatment to quotation
elseif(isset($_SESSION['ai1_tokenq']) and isset($_POST['ai1_tokenq']) and $_POST['ai1_tokenq']==$_SESSION['ai1_tokenq']
and userHasRole($pdo,20)){

	if(isset($_POST['add_quotation']) and $_POST['add_quotation']!='' and $_POST['add_quotation']!='new_quotation'){
		$element=$encrypt->decrypt("$_POST[ninye]");
		echo "good#append#$element#$_POST[add_quotation]";
	}
	elseif(isset($_POST['add_quotation']) and $_POST['add_quotation']!='' and $_POST['add_quotation']=='new_quotation'){
		$element=$encrypt->decrypt("$_POST[ninye]");
		echo "good#noappend#$element#$element";
	}

}

//this will check if an quotation has already been raised for this pt and append this one to it
elseif(isset($_POST['var1q']) and $_POST['var1q']!='' and userHasRole($pdo,20)){
	//check if quotation is raised
	$var2=html($_POST['var1q']);
	$var1=$encrypt->encrypt("$var2");
	$sql2=$error2=$s2='';$placeholders2=array();
	//$sql2="select distinct invoice_number, invoice_id from tplan_procedure where  invoice_id > 0 and pid=:pid and date_invoiced=curdate()";
	$sql2="select quotation_number, id from quotation_number_generator where pid=:pid and when_raised=curdate()";
	$placeholders2[':pid']=$_SESSION['pid'];
	$error2="Unable to check quotation raised today";
	$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
	if($s2->rowCount() > 0){
		$token = form_token(); $_SESSION['ai1_tokenq'] = "$token";
		$output="<form action='#append_invoice' method=POST  name='' id='' class='patient_form'>
				<input type=hidden name=ai1_tokenq  value=$_SESSION[ai1_tokenq] />
				<input type=hidden name=ninye  value=$var1 />
				<table class='normal_table'><caption>Quotations raised today</caption><thead>
				<tr><th class=ai_inv>QUOTATION NUMBER</th><th class=ai_sel>SELECT</th></tr></thead><tbody>";
		$print_flag=false;
		foreach($s2 as $row2){
				$print_flag=true;
				$quotation_number=html($row2['quotation_number']);
				$var=$encrypt->encrypt("$row2[quotation_number]#$row2[id]");
				$output = "$output <tr><td><input type=button class='button_style button_in_table_cell quotation_no' value=$quotation_number /></td>
										<td><input type=radio name=add_quotation value=$var /></td></tr>";

		}
		$output="$output <tr><td>Raise new quotation</td><td><input type=radio name=add_quotation value='new_quotation' /></td></tr></tbody>
		<thead><tr><th></th><th><input type=submit class='button_style  button_in_table_cell' value=Submit /></th></tr></thead></table></form>";
		if($print_flag){echo "$output";}
	}

}



//this will check if an invoice has already been raised for this pt and append this one to it
elseif(isset($_POST['var1']) and $_POST['var1']!='' and userHasRole($pdo,20)){
	//check if invoice is raised
	$var2=html($_POST['var1']);
	$var1=$encrypt->encrypt("$var2");
	$sql2=$error2=$s2='';$placeholders2=array();
	//$sql2="select distinct invoice_number, invoice_id from tplan_procedure where  invoice_id > 0 and pid=:pid and date_invoiced=curdate()";
	$sql2="select invoice_number, id from unique_invoice_number_generator where pid=:pid and when_raised=curdate() and aliased=0";
	$placeholders2[':pid']=$_SESSION['pid'];
	$error2="Unable to check invoice raised today";
	$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
	if($s2->rowCount() > 0){
		$token = form_token(); $_SESSION['ai1_token'] = "$token";
		$output="<form action='#append_invoice' method=POST  name='' id='' class='patient_form'>
				<input type=hidden name=ai1_token  value=$_SESSION[ai1_token] />
				<input type=hidden name=ninye  value=$var1 />
				<table class='normal_table'><caption>Invoices raised today</caption><thead>
				<tr><th class=ai_inv>INVOICE NUMBER</th><th class=ai_sel>SELECT</th></tr></thead><tbody>";
		$print_flag=false;
		foreach($s2 as $row2){
			//check if the invoice status
			$result=get_invoice_status($row2['id'],$pdo);
			//echo"$row2[id] is $result --";
			if($result==''){
				$print_flag=true;
				$invoice_number=html($row2['invoice_number']);
				$var=$encrypt->encrypt("$row2[invoice_number]#$row2[id]");
				$output = "$output <tr><td><input type=button class='button_style button_in_table_cell invoice_no' value=$invoice_number /></td>
										<td><input type=radio name=add_invoice value=$var /></td></tr>";
			}
		}
		$output="$output <tr><td>Raise new invoice</td><td><input type=radio name=add_invoice value='new_invoice' /></td></tr></tbody>
		<thead><tr><th></th><th><input type=submit class='button_style append_invoice_submit button_in_table_cell' value=Submit /></th></tr></thead></table></form>";
		if($print_flag){echo "$output";}
	}

}

//this will show form to edit a tplan but will not edit invoices
elseif(isset($_POST['edit_tplan']) and $_POST['edit_tplan']!='' and userHasRole($pdo,51)){
	$data=explode('ninye',$_POST['edit_tplan']);
	$tplan_id=$encrypt->decrypt("$data[0]");
	$pid=$encrypt->decrypt("$data[1]");

	//check if this patient type is insured or not
	$insured='NO';
	$sql=$error=$s='';$placeholders=array();
	$sql="select insured from covered_company a, patient_details_a b where b.pid=:pid and b.company_covered=a.id ";
	$error="Unable to check if the company is insured";
	$placeholders['pid']=$pid;
	$s = select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){$insured=html($row['insured']);}

	echo "<div class='feedback hide_element'></div>";
	get_patient_basics($pdo,$pid,$encrypt);
	$tplan_id=html($tplan_id);
	//get tplan procedures
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select b.treatment_procedure_id, a.name, b.teeth, b.details , case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'
			end as status , b.procedure_id, all_teeth ,
			case b.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points' end as pay_type
			, b.invoice_number, b.unauthorised_cost, b.authorised_cost, pid ,a.type, b.alias from procedures a, tplan_procedure b where b.tplan_id=:tplan_id and
		  b.procedure_id=a.id";
	$placeholders2[':tplan_id']=$tplan_id;
	$error2="Unable to get unfinished treatment plan procedure";
	$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
	$has_invoice=false;
	//put procedures into array
	$treatment_procedure_id_array=$procedure_name_array=$teeth_array=$details_array=$status_array=$pay_type_array=$invoice_number_array=array();
	$invoiced_array=$unauthorised_cost_array=$authorised_cost_array=$procedure_id_array=$all_teeth_array=$alias_array=array();
	$points_sum=$insurance_sum=$self_sum=0;
	foreach($s2 as $row2){
		$pid=html($row2['pid']);
		if($row2['invoice_number'] != ''){$invoiced_array[]=true;}else{$invoiced_array[]=false;}
		$procedure_id_array[]=html($row2['procedure_id']);
		if($row2['procedure_id'] == 1){
			$details_array[]='';
			$procedure_name_array[]=html($row2['details']);
		}
		elseif($row2['procedure_id'] != 1){
			$details_array[]=html($row2['details']);
			$procedure_name_array[]=html($row2['name']);
		}
		$all_teeth_array[]=html($row2['all_teeth']);
		$treatment_procedure_id_array[]=$encrypt->encrypt(html($row2['treatment_procedure_id']));

		$teeth_array[]=html($row2['teeth']);

		$status_array[]=html($row2['status']);
		$pay_type_array[]=html($row2['pay_type']);
		$invoice_number_array[]=html($row2['invoice_number']);
		$unauthorised_cost_array[]=html(number_format($row2['unauthorised_cost'],2));
		$alias_array[]=html($row2['alias']);
		if($row2['pay_type']=='Self'){
			$authorised_cost_array[]='N/A';
			$self_sum = $self_sum + html($row2['unauthorised_cost']);
		}
		elseif($row2['pay_type']=='Points' ){
			$authorised_cost_array[]='N/A';
			$points_sum = $points_sum + html($row2['unauthorised_cost']);
		}
		elseif($row2['pay_type']=='Insurance'){
			if(is_null($row2['authorised_cost'])){
				$authorised_cost_array[]='';
				$insurance_sum = $insurance_sum + html($row2['unauthorised_cost']);
			}
			elseif(!is_null($row2['authorised_cost'])){
				$authorised_cost_array[]=html($row2['authorised_cost']);
				$insurance_sum = $insurance_sum + html($row2['authorised_cost']);
				$self_sum = $self_sum + html($row2['unauthorised_cost'] - $row2['authorised_cost']);
			}
		}
	}

	$total_sum=$insurance_sum + $self_sum;
	$total_sum=number_format($total_sum,2);
	$points_sum=number_format($points_sum,2);
	$insurance_sum=number_format($insurance_sum,2);
	$self_sum=number_format($self_sum,2);
		?> <form action="#edit_tplan" method="post" name="" class='patient_form' id=""> <?php
		 $token = form_token(); $_SESSION['edit_tplan_token_2'] = "$token";  ?>
		<input type="hidden" name="edit_tplan_token_2"  value="<?php echo $_SESSION['edit_tplan_token_2']; ?>" />
		<?php
		$n=count($treatment_procedure_id_array);
		$i=$count=$old_count=0;
		echo "<br>	<table id=edit_tplan_no_invoice class='normal_table '><caption>Treatment Plan No. $tplan_id</caption><thead>
					<tr><th class=etp_count></th><th class=etp_procedure>TREATMENT PROCEDURE</th>
					<th class=etp_details>PROCEDURE DETAILS</th><th class=etp_pay_type>PAYMENT TYPE</th>
					<th class=etp_cost>COST</th><th class=etp_authorised_cost>AUTHORISED<BR>COST</th><th class=etp_delete>REMOVE<BR>PROCEDURE</th>
					</tr></thead><tbody>";
		while($i < $n){
			$count++;
				$alias=$checked_alias='';
				if($alias_array[$i]==1){$alias=" -- Alias ";$checked_alias=' checked ';}
			//check if invoiced and make uneditable
			if($invoiced_array[$i]){

				echo "<tr><td>$count</td><td>$procedure_name_array[$i] $teeth_array[$i] $alias</td><td>$details_array[$i]</td><td old_pay_type>$pay_type_array[$i]</td>
						<td>$unauthorised_cost_array[$i]</td><td>$authorised_cost_array[$i]</td><td>Invoiced<br>$invoice_number_array[$i]</td></tr>";
			}
			elseif(!$invoiced_array[$i]){
				//if stared or finished no editing
				if("$status_array[$i]" != 'Not Started'){
					echo "<tr><td>$count</td><td>$procedure_name_array[$i] $teeth_array[$i] $alias</td><td>$details_array[$i]</td><td class=old_pay_type>$pay_type_array[$i]</td>
						<td>$unauthorised_cost_array[$i]</td><td>$authorised_cost_array[$i]</td><td>$status_array[$i]</td></tr>";
				}
				elseif("$status_array[$i]" == 'Not Started'){
					$old_count++;
					echo "<tr><td>$count</td><td>";
						//show procedure
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name,id,all_teeth,type from procedures order by name";
						$error2="Unable to get prodcedures";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						echo "<select name=old_procedure$old_count class='input_in_table_cell select_procedure2' ><option></option>";
							foreach($s2 as $row2){
								$procedure=html($row2['name']);
								$val2=$encrypt->encrypt(html($row2['id']));

									if($procedure_id_array[$i] == $row2['id']){echo "<option value='$val2' selected >$procedure</option>"; }
									elseif($procedure_id_array[$i] != $row2['id']){echo "<option value='$val2'>$procedure</option>"; }

								/*elseif($row2['id'] == 1){echo "<optgroup label='X-RAYS'>";
									$sql3=$error3=$s3='';$placeholders3=array();
									$sql3="select name,id,all_teeth from teeth_and_xray_types order by name";
									$error3="Unable to get xrays";
									$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
									foreach($s3 as $row3){
										$xray=html($row3['name']);
										$val3=$encrypt->encrypt("$row2[id]#$row3[id]");
										echo "<option value='$val3' >$xray</option>";
									}
									echo "</optgroup>";
								}*/
							}
							echo "</select>";
						//show teeth
						//if($all_teeth_array[$i] == 'yes'){
							$teeth_body='';
							$selected_teeth_array=explode(',',"$teeth_array[$i]");
							if($teeth_array[$i] != ''){$teeth_body=" teeth_body ";}

							echo "<div class='grid-100 teeth_div $teeth_body'>
								<div class='teeth_row'>
									<div class='hover  teeth_heading_cell'>Upper Right - 1x
										<div class='teeth_body'>";


										$i2=8;
										$teeth_specified="old_teeth_specified$old_count"."[]";
										while($i2 >= 1){
											$number="1$i2";
											$checked=$highlight='';
											if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number  $highlight'>$number<br><input  $checked  class='tooth_checkbox2' type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
											$checked=$highlight='';
											if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
											$checked=$highlight='';
											if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
											$checked=$highlight='';
											if(in_array($number,$selected_teeth_array)){$checked = " checked ";$highlight = " highlight ";}
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
											$i2++;
										}	?>
										</div>
									</div>
								</div>

							</div>

				<?php	//	} //end show teeth
					//check pay type
					$self_selected=$points_selected=$insurance_selected='';
					if($pay_type_array[$i]=='Self'){$self_selected =" selected ";}
					elseif($pay_type_array[$i]=='Points'){$points_selected =" selected ";}
					elseif($pay_type_array[$i]=='Insurance'){$insurance_selected =" selected ";}
					$invoice_pay=$encrypt->encrypt("1");
					$cash_pay=$encrypt->encrypt("2");
					$points_pay=$encrypt->encrypt("3");
					echo "</td><td><textarea  class=tplan_details  rows='' name=old_details$old_count >$details_array[$i]</textarea></td><td>
						<select  name=old_pay_method$old_count class='input_in_table_cell pay_method' ><option></option>";
						if($insured == 'YES'){echo "<option value='$invoice_pay' $insurance_selected >Insurance</option>";}
							echo "<option value='$cash_pay' $self_selected >Self</option>
							<option value='$points_pay' $points_selected >Points</option>
						</select></td><td><input type=text name=old_cost$old_count value=$unauthorised_cost_array[$i] class=tplan_cost2 />";
						if(userHasRole($pdo,113)){
							$alias_val=$encrypt->encrypt("ta$i");
							echo "Aliased <input  class=tplan_alias type=checkbox name=old_alias$old_count value=$alias_val $checked_alias />";
						}
						echo "</td><td>N/A</td>
					<td><input type=checkbox name=old_remove$old_count value=$treatment_procedure_id_array[$i] class=tplan_remove />
						<input type=hidden name=old_ninye$old_count value=$treatment_procedure_id_array[$i] class=tplan_remove />
					</td></tr>";
				}
			}
			$i++;
		}
		//show 2 empty new rows for adding a treatment
	//	edit_tplan_no_invoice

		//end new treatment
		echo "</table>"; ?>
	<div class='grid-50 '>
		<?php
			$zero=$encrypt->encrypt("0ninye$count"."ninye$old_count"."ninye$pid"."ninye$tplan_id");
			echo "<input name=nimeana id=nimeana type=hidden value='$zero' />";
		?>
		<input type=button class="add_new_procedure_edit_tplan_no_invoice  button_style" value="Add Procedure" />
	</div>
	<div class='grid-50 put_right no_padding_right'>
			<div class='grid-40 prefix-50 '><label for="" class="label">Insurance total cost(Kes): </label></div>
			<div class='grid-10 no_padding_right' ><span id=treatment_plan_insurance_total class='put_right label'><?php echo $insurance_sum; ?></span></div>
		<div class='grid-40 prefix-50   '><label for="" class="label">Self total cost(Kes): </label></div>
			<div class='grid-10 no_padding_right' ><span id=treatment_plan_self_total class='put_right label '><?php echo $self_sum; ?></span></div>
		<div class='grid-40 prefix-50 '><label for="" class="label">Total cost(Kes): </label></div>
			<div class='grid-10 no_padding_right' ><span id=treatment_plan_sum class='put_right label'><?php echo $total_sum; ?></span></div>
		<div class='grid-40 prefix-50  '><label for="" class="label">Points total cost: </label></div>
			<div class='grid-10 no_padding_right' ><span id=treatment_plan_points_total class='put_right label'><?php echo $points_sum; ?></span></div>
		<input type=submit class='put_right'  value='Submit' /></form>
	</div>
			<?php

}

//this is for adding a new drug in patient prescription
elseif(isset($_POST['add_drug']) and $_POST['add_drug']!='' and userHasRole($pdo,20)){
	//$val=$encrypt->decrypt($_POST['extra_procedure_invoice']);
	//$val=$_POST['extra_procedure_tplan_no_invoice'];
	//$data=explode('ninye',"$val");
	//print_r($data);
	//$old_count=$data[2] + 1;//exit;
	//$count=$data[1] + 1;//echo "- 0ninye$count"."ninye$old_count"."ninye$pid"."ninye$tplan_id" ."-";
		//			$var2=$encrypt->encrypt("0ninye$data[1]"."ninye$old_count"."ninye$data[3]"."ninye$data[4]"."ninye$data[5]");
			//		echo "$var2"."nonye";
				//	$new_procedure_to_add_to_tplan=$encrypt->encrypt("new_procedure");
	?><div class='grid-100 highlight_on_hover1 no_padding'>
		<div class=grid-35>
			<select name='drug[]' class='drug_name' ><option></option> <?php
				$sql=$error=$s='';$placeholders=array();
				$sql="select name, selling_price, id from drugs where listed!=1 order by name";
				$error="Unable to get drugs";
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$name=html($row['name']);
					$price=html($row['selling_price']);
					if($price > 0){$price=number_format($price,2);}
					else{$price='';}
					$val=$encrypt->encrypt($row['id']);
					echo "<option value='$val'>$name</option>";
				} ?>
			</select>
			<div class=get_drug_quantity></div>
		</div>
		<div class=grid-20><textarea disabled width='100%' class='drug_details' name='details[]' ></textarea></div>
		<div class=grid-20>
			<select name='presc_type[]' disabled class='drug_presc_type' ><option></option>
				<?php
					$self=$encrypt->encrypt("2");//self
					$presc=$encrypt->encrypt("presc");
					echo "<option value=$self>Sell</option>
							<option value=$presc>Prescribe</option>";
				?>
			</select>
			<div class=commissioners_div>

					<label class=label>Commission</label>
					<select name='commissioners[]' class='commissioners' ><option value=''></option> <?php
						$sql=$error1=$s='';$placeholders=array();
						$sql="select first_name,middle_name,last_name ,id from users where status='active' order by first_name";
						$error="Unable to get users";
						$s = select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$name=html("$row[first_name] $row[middle_name] $row[last_name]");
							$val=$encrypt->encrypt($row['id']);
							echo "<option value='$val'>$name</option>";
						} ?>
					</select>
					</div>
		</div>
		<div class=' grid-5 '><input disabled type=text name='drug_quantity[]' class='drug_quantity'  /></div>
		<div class=' grid-10  drug_unit_price'>&nbsp;<!-- unit cost here --></div>
		<div class=grid-10><input class='drug_price' disabled type=text name='price[]'  /></div>
		<div class=clear></div><BR>
		<div class='grid-100 grey_bottom_border'></div>
	<div><?php
}

//this is for adding a new procedure to edit tplan with  invoice
elseif(isset($_POST['extra_procedure_invoice']) and $_POST['extra_procedure_invoice']!='' and userHasRole($pdo,60)){

	$val=$encrypt->decrypt($_POST['extra_procedure_invoice']);
	//$val=$_POST['extra_procedure_tplan_no_invoice'];
	$data=explode('ninye',"$val");
	//print_r($data);
	$old_count=$data[2] + 1;//exit;
	//$count=$data[1] + 1;//echo "- 0ninye$count"."ninye$old_count"."ninye$pid"."ninye$tplan_id" ."-";
					$var2=$encrypt->encrypt("0ninye$data[1]"."ninye$old_count"."ninye$data[3]"."ninye$data[4]"."ninye$data[5]");
					//check if this patient type is insured or not
					$insured='NO';
					$sql=$error=$s='';$placeholders=array();
					$sql="select insured from covered_company a, patient_details_a b where b.pid=:pid and b.company_covered=a.id ";
					$error="Unable to check if the company is insured";
					$placeholders['pid']=$data[3];
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$insured=html($row['insured']);}

					echo "$var2"."nonye";
					$new_procedure_to_add_to_tplan=$encrypt->encrypt("new_procedure");
					echo "<tr><td class=procedure_count>$old_count
						<input type=hidden name=ninye_oo$old_count value=$new_procedure_to_add_to_tplan class=tplan_remove />
					</td><td>";
						//show procedure
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name,id,all_teeth from procedures where listed=0 order by name";
						$error2="Unable to get prodcedures";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						echo "<select name=old_procedure$old_count class='input_in_table_cell select_procedure2' ><option></option>";
							foreach($s2 as $row2){
								$procedure=html($row2['name']);
								$val2=$encrypt->encrypt(html($row2['id']));

									echo "<option value='$val2'>$procedure</option>";

							/*	elseif($row2['id'] == 1){echo "<optgroup label='X-RAYS'>";
									$sql3=$error3=$s3='';$placeholders3=array();
									$sql3="select name,id,all_teeth from teeth_and_xray_types order by name";
									$error3="Unable to get xrays";
									$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
									foreach($s3 as $row3){
										$xray=html($row3['name']);
										$val3=$encrypt->encrypt("$row2[id]#$row3[id]");
										echo "<option value='$val3' >$xray</option>";
									}
									echo "</optgroup>";
								}*/
							}
							echo "</select>";
						//show teeth
						//if($all_teeth_array[$i] == 'yes'){

							echo "<div class='grid-100 teeth_div '>
								<div class='teeth_row'>
									<div class='hover  teeth_heading_cell'>Upper Right - 1x
										<div class='teeth_body'>";


										$i2=8;
										$teeth_specified="old_teeth_specified$old_count"."[]";
										while($i2 >= 1){
											$number="1$i2";
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number'>$number<br><input    class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
											echo "<div class='hover-row tooth_number'>$number<br><input   class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
											echo "<div class='hover-row tooth_number'>$number<br><input    class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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

				<?php	//	} //end show teeth
					//check pay type
					$invoice_pay=$encrypt->encrypt("1");
					$cash_pay=$encrypt->encrypt("2");
					$points_pay=$encrypt->encrypt("3");
					echo "</td><td><textarea  class=tplan_details  rows='' name=old_details$old_count ></textarea></td><td>&nbsp;</td><td>
						<select  name=old_pay_method$old_count class='input_in_table_cell pay_method' ><option></option>";
							if($insured == 'YES'){echo "<option value='$invoice_pay'  >Insurance</option>";}

							//checkif the invoice is aliasd
							if($_POST['aliased_invoice'] == 'normal_invoice'){
								echo "<option value='$cash_pay'  >Self</option>
								<option value='$points_pay'  >Points</option>";
							}

						echo "</select>
						</td><td><input  type=text name=old_cost$old_count  class=tplan_cost2 />
						</td><td colspan=2>
						&nbsp;</td>
					</tr>";
}

//this is for adding a new procedure to edit tplan with no invoice
elseif(isset($_POST['extra_procedure_tplan_no_invoice']) and $_POST['extra_procedure_tplan_no_invoice']!='' and userHasRole($pdo,51)){
	$val=$encrypt->decrypt($_POST['extra_procedure_tplan_no_invoice']);
	//$val=$_POST['extra_procedure_tplan_no_invoice'];
	$data=explode('ninye',"$val");
	$new_count=$data[0] + 1;
	$count=$data[1] + 1;//echo "- 0ninye$count"."ninye$old_count"."ninye$pid"."ninye$tplan_id" ."-";
					$var2=$encrypt->encrypt("$new_count"."ninye$count"."ninye$data[2]"."ninye$data[3]"."ninye$data[4]");

	//check if this patient type is insured or not
	$insured='NO';
	$sql=$error=$s='';$placeholders=array();
	$sql="select insured from covered_company a, patient_details_a b where b.pid=:pid and b.company_covered=a.id ";
	$error="Unable to check if the company is insured";
	$placeholders['pid']=$data[3];
	$s = select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){$insured=html($row['insured']);}

					echo "$var2"."ninye";
					echo "<tr><td class=procedure_count>$count</td><td>";
						//show procedure
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name,id,all_teeth from procedures where listed=0  order by name";
						$error2="Unable to get prodcedures";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						echo "<select name=new_procedure$new_count class='input_in_table_cell select_procedure2' ><option></option>";
							foreach($s2 as $row2){
								$procedure=html($row2['name']);
								$val2=$encrypt->encrypt(html($row2['id']));

									if(isset($procedure_id_array) and isset($procedure_id_array[$i])){  //$procedure_id_array[$i]
										if($procedure_id_array[$i] == $row2['id']){echo "<option value='$val2' selected >$procedure</option>"; }
										elseif($procedure_id_array[$i] != $row2['id']){echo "<option value='$val2'>$procedure</option>"; }
									}
									else{echo "<option value='$val2'>$procedure</option>"; }

							/*	elseif($row2['id'] == 1){echo "<optgroup label='X-RAYS'>";
									$sql3=$error3=$s3='';$placeholders3=array();
									$sql3="select name,id,all_teeth from teeth_and_xray_types order by name";
									$error3="Unable to get xrays";
									$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
									foreach($s3 as $row3){
										$xray=html($row3['name']);
										$val3=$encrypt->encrypt("$row2[id]#$row3[id]");
										echo "<option value='$val3' >$xray</option>";
									}
									echo "</optgroup>";
								}*/
							}
							echo "</select>";
						//show teeth
						//if($all_teeth_array[$i] == 'yes'){

							echo "<div class='grid-100 teeth_div '>
								<div class='teeth_row'>
									<div class='hover  teeth_heading_cell'>Upper Right - 1x
										<div class='teeth_body'>";


										$i2=8;
										$teeth_specified="new_teeth_specified$new_count"."[]";
										while($i2 >= 1){
											$number="1$i2";
											$name="tooth$number";
											//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
											echo "<div class='hover-row tooth_number'>$number<br><input    class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
											echo "<div class='hover-row tooth_number'>$number<br><input   class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
											echo "<div class='hover-row tooth_number'>$number<br><input    class=tooth_checkbox2 type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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

				<?php	//	} //end show teeth
					//check pay type
					$invoice_pay=$encrypt->encrypt("1");
					$cash_pay=$encrypt->encrypt("2");
					$points_pay=$encrypt->encrypt("3");
					echo "</td><td><textarea disabled class=tplan_details  rows='' name=new_details$new_count ></textarea></td><td>
						<select disabled name=new_pay_method$new_count class='input_in_table_cell pay_method' ><option></option>";
							if($insured == 'YES'){echo "<option value='$invoice_pay'  >Insurance</option>";}
						echo "	<option value='$cash_pay'  >Self</option>
							<option value='$points_pay'  >Points</option>
						</select></td><td><input disabled type=text name=new_cost$new_count  class=tplan_cost2 />";
						if(userHasRole($pdo,113)){
							$alias_val=$encrypt->encrypt("ta$new_count");
							echo "Aliased <input disabled class=tplan_alias type=checkbox name=new_alias$new_count value=$alias_val />";
						}
						echo "</td><td>N/A</td>
					<td></td></tr>";
}

//this is for dispalying allocation dialog in t-done
elseif(isset($_POST['show_allocation_dialog']) and $_POST['show_allocation_dialog']!=''  and userHasRole($pdo,20)){
	$_POST['show_allocation_dialog']='';
	//check if pt was from alloaction
	$sql3=$error3=$s3='';$placeholders3=array();
	$sql3="SELECT id FROM patient_allocations WHERE pid =:pid AND time_start_treatment > '0000-00-00 00:00:00'
			AND treatment_finish = '0000-00-00 00:00:00' ";
	$error3="Unable to alloaction id";
	$placeholders3[':pid']=$_SESSION['pid'];
	$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
	if($s3->rowCount() > 0){//pt is froma allocation
	foreach($s3 as $row){
		$allocation_id = html($row['id']);
		$allocation_id2 = $encrypt->encrypt($allocation_id);
	}
	?>
		<form action="" method="POST"  name="" id="" class="patient_form2 token_g3_patient">
			<div class='clear'></div>
			<input  type="hidden" name="token_g3_patient"  value="<?php echo $_SESSION['token_g3_patient']; ?>" />
			<input  type="hidden" name="ninye"  value="<?php echo $allocation_id2; ?>" />
			<div class='grid-30 label'>Select treatment status</div>
			<div class='grid-15 '><select name=treatment_status>
					<option value=''></option>
					<option value='hold'>Suspend</option>
					<option value='finish'>Finish</option>
					<option value='continue'>Continue</option>
				</select>
			</div>
			<div class='grid-10'>
				<input type=submit value=Submit /></div>
			<div class='clear'></div>
		</form>
		<?php
	}
	else{ ?>
		<form action="" method="POST"  name="" id="" class="patient_form2 ">
			<input  type="hidden" name="token_g3_patient"  value="<?php echo $_SESSION['token_g3_patient']; ?>" />
			<input  type="hidden" name="ninye2"  value="ninye2" />
			<div class='grid-20 label'><input type=submit value='Return to patient allocation' /></div>
		</form>
	<?php
	}


}

//this is for dispalying invoices raised for pt
elseif(isset($_POST['tdone_invoice']) and $_POST['tdone_invoice']!=''  and userHasRole($pdo,20)){

	//$result=get_pt_invoices($pdo, $_SESSION['pid']);
	//$result=show_pt_invoices_also_with_swapped($pdo,$_SESSION['pid']);
	//$data=explode('###',"$result");
	//if("$data[0]"=='good'){echo "$data[1]<br><br>";}
	show_pt_invoices_also_with_swapped($pdo,$_SESSION['pid']);

	//look for older swapped patient number
	/*	$sql=$error=$s='';$placeholders=array();
		$sql="select old_pid from swapped_patients where new_pid=:pid ";
		$placeholders[':pid']=$_SESSION['pid'];
		$error="Unable to get old patient number for patient";
		$s = select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			foreach($s as $row){$pid_clean=html($row['old_pid']);}
			$result=get_pt_invoices($pdo, $pid_clean);
			$data=explode('###',"$result");
			if("$data[0]"=='good'){echo "$data[1]";}
		}*/

	/*$sql=$error=$s='';$placeholders=array();
	$sql="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced),
				 covered_company.pre_auth_needed,
				covered_company.smart_needed,
				invoice_authorisation.authorisation_sent, invoice_authorisation.authorisation_received,
				invoice_authorisation.smart_run,
				invoice_authorisation.amount_authorised, invoice_authorisation.comments,invoice_authorisation.smart_amount,
				users.first_name,users.middle_name,users.last_name ,
				sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested,
				sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_approved
				from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid
				join users on tplan_procedure.created_by=users.id
				join covered_company on patient_details_a.company_covered=covered_company.id
				left join invoice_authorisation on tplan_procedure.invoice_id=invoice_authorisation.invoice_id
				left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
				where tplan_procedure.invoice_id > 0  and tplan_procedure.pid=:pid

				group by tplan_procedure.invoice_id  order by tplan_procedure.invoice_id";
	$error="Unable to get invoices for patient in tdone";
	$placeholders[':pid']=$_SESSION['pid'];
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$i=0;
	 $smart_amount2='';
	if($s->rowCount() > 0){
		$var='';
		foreach($s as $row){
			if($i==0){
				$caption="Invoices raised for patient: $_SESSION[patient_number] - $_SESSION[first_name] $_SESSION[middle_name] $_SESSION[last_name]";
				$var= "<table class='normal_table'><caption>$caption</caption><thead>
							<tr>
							<th class=ar_inv>INVOICE No.</th>
							<th class=ar_date>DATE</th>
							<th class=ar_doc>DOCTOR</th>
							<th class=ar_inv>BILLED<br>COST</th>
							<th class=ar_inv>AUTHORISED<br>COST.</th>
							<th class=ar_status>STATUS</th>
							<th class=ar_pre_sent>PRE-AUTH<br>SENT</th>
							<th class=ar_pre_received>PRE-AUTH<br>RECEIVED</th>
							<th class=ar_smart>SMART<br>CHECKED</th>
							<th class=ar_comment>COMMENTS</th>
							</tr></thead><tbody>";

			}
			$invoice_num=html("$row[invoice_number]");
			$date=html("$row[2]");
			$doc=html("$row[first_name] $row[middle_name] $row[last_name]");
			$billed=html("$row[amount_requested]");
			if($billed!=''){$billed=number_format($billed,2);}
			$authorised=html("$row[amount_approved]");
			if($authorised!=''){$authorised=number_format($authorised,2);}
			$status=get_invoice_status($row['invoice_id'],$pdo);
			$pre_sent=html("$row[authorisation_sent]");
			$pre_receive=html("$row[authorisation_received]");
			$amount_authorised=html("$row[amount_authorised]");
			$smart_date=html("$row[smart_run]");
			$smart_amount=html("$row[smart_amount]");
			$comments=html("$row[comments]");
			$pre_auth_amount=$smart_amount='';
			if($pre_receive!='' and $amount_authorised!=''){$pre_auth_amount="<br>".number_format($amount_authorised,2);}
			if($smart_date!='' and $smart_amount!=''){$smart_amount2="<br>".number_format($smart_amount,2);}
			$pre_receive = "$pre_receive $pre_auth_amount";
			$smart_date ="$smart_date $smart_amount2";
			if($row['pre_auth_needed']!='YES'){}
			if($row['smart_needed']!='YES'){}
			$var=  "$var<tr><td ><input type=button class='button_style button_in_table_cell invoice_no' value=$invoice_num /></td><td >$date</td><td >$doc</td><td >$billed</td><td >$authorised</td><td >$status</td>";
				//check if pre-auth is need
				if($row['pre_auth_needed']!='YES'){$var= "$var<td colspan=2>N/A</td>";}
				elseif($row['pre_auth_needed']=='YES'){$var= "$var<td>$pre_sent</td><td>$pre_receive</td>";}
				//check if smart is needed
				if($row['smart_needed']!='YES'){$var= "$var<td >N/A</td>";}
				elseif($row['smart_needed']=='YES'){$var= "$var<td >$smart_date</td>";}
			$var "$var<td >$comments</td></tr>";
			$i++;
		}
		$var= "$var</table>";
		echo "$var";
	}
	else{echo "<label class=label>This patient has no invoices</label>";}

	*/

}

//this is for submitting procedures removed fomr insurer cover
//this will submit procedure prices for insurer
elseif(isset($_SESSION['token_ipe12']) and isset($_POST['token_ipe12']) and $_POST['token_ipe12']==$_SESSION['token_ipe12']
and userHasRole($pdo,9)){
	$exit_flag=false;
	$procedures=$_POST['ninye3'];
	$n=count($procedures);
	$i=0;

	try{
			$pdo->beginTransaction();
			$i=0;
			$insurer_id=$encrypt->decrypt($_POST['ninye2']);

			//delete procedures from table 1
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from procedures_not_insured where insurer_id=:insurer_id";
			$placeholders[':insurer_id']=$insurer_id;
			$error="Unable to delete procedure 1";
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			//delete procedures from table 2
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from procedures_not_covered where insurer_id=:insurer_id";
			$placeholders[':insurer_id']=$insurer_id;
			$error="Unable to delete procedure 2";
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			//get covered corporates
			$sql=$error=$s='';$placeholders=array();
			$sql="select id as covered_company from covered_company where insurer_id=:insurer_id";
			$placeholders[':insurer_id']=$insurer_id;
			$error="Unable to delete procedure 1";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$companies_array=array();
			foreach($s as $row){$companies_array[]=html($row['covered_company']);}

			$insurer_id=$encrypt->decrypt($_POST['ninye2']);
			//now insert procedures
			while($i < $n){
				$procedure_id=$encrypt->decrypt("$procedures[$i]");

				//insert into procedures_not_covered in table first
				$sql1=$error1=$s1='';$placeholders1=array();
				$sql1="insert into procedures_not_insured set insurer_id=:insurer_id , procedure_id=:procedure_id";
				$placeholders1[':insurer_id']=$insurer_id;
				$placeholders1[':procedure_id']=$procedure_id;
				$error1="Unable to add procedure not covered";
				$s1 = 	insert_sql($sql1, $placeholders1, $error1, $pdo);

				foreach($companies_array as $comp_cov){
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="insert into procedures_not_covered set insurer_id=:insurer_id,
						procedure_not_covered=:procedure_not_covered, company_id=:company_id";
					$placeholders2[':insurer_id']=$insurer_id;
					$placeholders2[':procedure_not_covered']=$procedure_id;
					$placeholders2[':company_id']=$comp_cov;
					$error2="Unable to  add uninsured procedures";
					$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);

				}
				$i++;
			}


			if(!$exit_flag){$tx_result = $pdo->commit();$message="good#ins_price# Changes saved. ";}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
	}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	$message="bad#   Unable to save  details  ";
	}


	echo "$message";

	//now insert
}

//this is for dispalying insurer procedure to remove from cover
elseif(isset($_POST['edit_insured_procedure']) and $_POST['edit_insured_procedure']!='' and userHasRole($pdo,9)){
	$insurer_id=$encrypt->decrypt($_POST['edit_insured_procedure']);
	//echo "$insurer_id";
	//get insurer name
	$insurer_name='';
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT name from insurance_company where id=:insurance_id";
	$placeholders[':insurance_id']=$insurer_id;
	$error="Unable to insurer name  1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$insurer_name=html($row['name']);
	}
	if($insurer_name==''){echo "bad#The insurer does not exist";exit;}

	?><form action="" class='patient_form' method="post" name="" id="">
			<?php $token = form_token(); $_SESSION['token_ipe12'] = "$token";
						$ninye2 = $encrypt->encrypt("$insurer_id");
			?>
		<input type="hidden" name="token_ipe12"  value="<?php echo $_SESSION['token_ipe12']; ?>" />
		<input type="hidden" name="ninye2"  value="<?php echo $ninye2; ?>" />


			<!-- show acorporates -->
			<div class='grid-30'><label for="" class="label"> Select procedures not insured by <?php echo "$insurer_name"; ?></label></div>
			<div class='grid-50'>
					<?php
					//get procuderes not covered by the insurer
					$sql=$error=$s='';$placeholders=array();
					$sql="select procedure_id from procedures_not_insured where insurer_id=:insurer_id";
					$error="Unable to select procedures";
					$placeholders[':insurer_id']=$insurer_id;
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					$procudures_not_insured_array=array();
					foreach($s as $row){$procudures_not_insured_array[] = html($row['procedure_id']);}

					//get corporates under the insurer
					$sql=$error=$s='';$placeholders=array();
					$sql="select a.name,a.id from procedures a order by a.name";
					$error="Unable to select procedures";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					echo "<table class='insurance_procedure_table'><caption>Select procedures to remove from cover</caption><thead>
							<tr><th class=count_iep></th><th class=name_iep>Procedure</th><th class=check_iep></th></tr></thead><tbody>";
					$count=0;
					foreach($s as $row){
						$count++;
						$procedure_name=html($row['name']);
						$procedure_id=$encrypt->encrypt(html($row['id']));

						if(in_array($row['id'], $procudures_not_insured_array)){$checked= ' checked ';}
						else{$checked='';}
						echo "<tr><td>$count</td><td>$procedure_name</td><td><input $checked type=checkbox name=ninye3[] value='$procedure_id' /></td></tr>";
					}
					echo "</tbody></table>";
				?>
				<br><input type=submit value=Submit class='put_right' />
			</div>
			</form>
			<div class=clear></div>
			<br><br>



	<?php
}

//this is for dispalying insurer price edit
elseif(isset($_POST['insurer_id_price']) and $_POST['insurer_id_price']!='' and userHasRole($pdo,9)){
	$insurer_id=$encrypt->decrypt($_POST['insurer_id_price']);
	//get insurer name
	$insurer_name='';
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT name from insurance_company where id=:insurance_id";
	$placeholders[':insurance_id']=$insurer_id;
	$error="Unable to insurer name  1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$insurer_name=html($row['name']);
	}
	if($insurer_name==''){echo "bad#The insurer does not exist";exit;}

	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT procedures.id, procedures.name , insurer_procedure_price.price ,insurer_procedure_price.id
		from procedures left join insurer_procedure_price
		on procedures.id=insurer_procedure_price.procedure_id and insurer_procedure_price.insurer_id=:insurer_id
		where procedures.id!=1 order by name";
	$placeholders[':insurer_id']=$insurer_id;
	$error="Unable to insurer price  1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo); ?>
	<form action="" class='patient_form' method="post" name="" id="">
			<?php $token = form_token(); $_SESSION['token_ipe1'] = "$token";
						$ninye2 = $encrypt->encrypt("$insurer_id");
			?>
		<input type="hidden" name="token_ipe1"  value="<?php echo $_SESSION['token_ipe1']; ?>" />
		<input type="hidden" name="ninye2"  value="<?php echo $ninye2; ?>" />
	<?php
	echo "<table class='insurance_company_table'><caption>PROCEDURE PRICES FOR $insurer_name</caption><thead>
				<tr><th class='count'></th><th class='name'>PROCEDURE</th><th class='del'>PRICE</th></tr></thead><tbody>";
	$i=1;
	foreach($s as $row){
		$procedure=html("$row[name]");
		$price=html($row['price']);
		if($price!=''){$price=number_format($price, 2);}
		$var=$encrypt->encrypt(html("$row[0]"));
		$procedure_id=html("$row[0]");
		echo "<tr><td>$i</td><td>$procedure</td><td><input type=text name=price[] value='$price' /><input type=hidden name=ninye[] value=$var /></td></tr>";
		$i++;
	}

	/*//now show xrays
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT teeth_and_xray_types.id, teeth_and_xray_types.name , insurer_procedure_price2.price ,insurer_procedure_price2.id
		from teeth_and_xray_types left join insurer_procedure_price2
		on teeth_and_xray_types.id=insurer_procedure_price2.procedure_id and insurer_procedure_price2.insurer_id=:insurer_id
		 order by name";
	$placeholders[':insurer_id']=$insurer_id;
	$error="Unable to get xray price  1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$procedure=html("$row[name]");
		$price=html($row['price']);
		if($price!=''){$price=number_format($price, 2);}
		$var=$encrypt->encrypt(html("$row[0]"));
		$procedure_id=html("$row[0]");
		echo "<tr><td>$i</td><td>$procedure</td><td><input type=text name=price2[] value='$price' /><input type=hidden name=ninye22[] value=$var /></td></tr>";
		$i++;
	}*/
	echo "</tbody></table>";

	?>
	<div class='grid-100'><input type=submit value=Submit /></form></div>
	<?php
}

//this will submit procedure prices for insurer
elseif(isset($_SESSION['token_ipe1']) and isset($_POST['token_ipe1']) and $_POST['token_ipe1']==$_SESSION['token_ipe1']
and userHasRole($pdo,9)){
	$exit_flag=false;
	$price=$_POST['price'];
	$n=count($price);
	$i=0;
	//check if price is correct format
	while($i < $n){
		if($price[$i]==''){
			$i++;
			continue;
		}

		$amount=str_replace(",", "", $price[$i]);
		$var=html("$price[$i]");
		if(!ctype_digit($amount)){
			//check if it has only 2 decimal places
			$data=explode('.',$amount);
			if ( count($data) != 2 ){
				$exit_flag=true;
				$message2="somebody tried to input $var as price for procedure price";
				log_security($pdo,$message2);
				$message="bad#ins_price#  Unable to save details as $var is not valid amount";
				break;
			}
			elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){

				$exit_flag=true;
				$message2="somebody tried to input $var as price for procedure price";
				log_security($pdo,$message2);
				$message="bad#ins_price#  Unable to save details as $var is not valid amount";
				break;
			}
		}

		$i++;
	}

		//$exit_flag=false;
	if(!$exit_flag and isset($_POST['price2'])){
		$price=$_POST['price2'];
		$n=count($price);
		$i=0;
		//check if price is correct format
		while($i < $n){
			if($price[$i]==''){
				$i++;
				continue;
			}

			$amount=str_replace(",", "", $price[$i]);
			$var=html("$price[$i]");
			if(!ctype_digit($amount)){
				//check if it has only 2 decimal places
				$data=explode('.',$amount);
				if ( count($data) != 2 ){
					$exit_flag=true;
					$message2="somebody tried to input $var as price for procedure price";
					log_security($pdo,$message2);
					$message="bad#ins_price#  Unable to save details as $var is not valid amount";
					break;
				}
				elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){

					$exit_flag=true;
					$message2="somebody tried to input $var as price for procedure price";
					log_security($pdo,$message2);
					$message="bad#ins_price#  Unable to save details as $var is not valid amount";
					break;
				}
			}

			$i++;
		}
	}
	try{
			$pdo->beginTransaction();
			$i=0;
			$insurer_id=$encrypt->decrypt($_POST['ninye2']);
			//echo "insurer id is $insurer_id --";
			//delete insurer prices in table first
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from insurer_procedure_price where insurer_id=:insurer_id";
			$placeholders[':insurer_id']=$insurer_id;
			$error="Unable to un price insurer 1";
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);


			//now insert new prices
			$ninye=$_POST['ninye'];
			$price=$_POST['price'];
			$n=count($price);
			while($i < $n){
				if($price[$i]==''){
					$i++;
					continue;
				}

				$amount=str_replace(",", "", $price[$i]);
				$procedure_id=$encrypt->decrypt($ninye[$i]);

				$sql=$error=$s='';$placeholders=array();
				$sql="insert into insurer_procedure_price set insurer_id=:insurer_id, procedure_id=:procedure_id, price=:price";
				$placeholders[':insurer_id']=$insurer_id;
				$placeholders[':procedure_id']=$procedure_id;
				$placeholders[':price']=$amount;
				$error="Unable to  price insurer 1";
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);


				$i++;
			}

		/*	//now insert new prices for things like xrays
			if(isset($_POST['price2'])){
				$price=$_POST['price2'];
				$n=count($price);
				$i=0;
				$ninye=$_POST['ninye22'];
				while($i < $n){
					if($price[$i]==''){
						$i++;
						continue;
					}

					$amount=str_replace(",", "", $price[$i]);
					$procedure_id=$encrypt->decrypt($ninye[$i]);

					$sql=$error=$s='';$placeholders=array();
					$sql="insert into insurer_procedure_price2 set insurer_id=:insurer_id, procedure_id=:procedure_id, price=:price";
					$placeholders[':insurer_id']=$insurer_id;
					$placeholders[':procedure_id']=$procedure_id;
					$placeholders[':price']=$amount;
					$error="Unable to  price insurer 1";
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);


					$i++;
				}
			}*/
			if(!$exit_flag){$tx_result = $pdo->commit();$message="good#ins_price# Prices saved ";}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
	}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$message="bad#   Unable to save patient details  ";
	}


	echo "$message";

	//now insert
}

//this is for dispalying a prescription
elseif(isset($_POST['prescription_num']) and $_POST['prescription_num']!='' ){
	$prescription_num=$_POST['prescription_num'];
	//echo "fff $invoice_number";
	//get pt name, insurer
	$sql=$error1=$s='';$placeholders=array();
	$sql="select a.when_added, b.name, a.details, concat(c.first_name,' ',c.middle_name,' ',c.last_name) as doctor_name ,
		a.prescription_id , a.prescription_number, concat(d.first_name,' ',d.middle_name,' ',d.last_name) as patient_names,
		d.patient_number,e.dob,a.pay_type,a.quantity, b.drug_type
		from drugs b, prescriptions a, users c, patient_details_a as d, patient_details_b as e
		where b.id=a.drug_id and c.id=a.created_by and a.pid=d.pid and e.pid=a.pid and a.prescription_number=:prescription_number
		order by a.id asc";
	$error="Unable to get prescription for printing";
	$placeholders[':prescription_number']="$prescription_num";
	$s = select_sql($sql, $placeholders, $error, $pdo);
	$i=0;
	$drug='';
	foreach($s as $row){
		if($i==0){
			$prescription_number=html("$prescription_num");
			$when_added=html("$row[when_added]");
			$pathent_names=html("$row[patient_names]");
			$patient_number=html("$row[patient_number]");
			$doctor=html("$row[doctor_name]");
			$dob = new DateTime(html($row['dob']));
			$today = new DateTime(date('Y-m-d'));
			$interval = $dob->diff($today);
			$age=$interval->y;
		}
		$i++;
		$line_through=$units='';
		if($row['quantity'] > 0){
			if($row['drug_type']=='Tablet'){$units=" ".html(number_format($row['quantity']))." tablets ";}
			elseif($row['drug_type']=='Syrup'){$units=" ".html(number_format($row['quantity']))." units ";}
		}
		if($row['pay_type']==2){$line_through='line_through';}
		if($drug==''){$drug="<div class='grid-100 no_padding $line_through label2'>$i.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".html("$row[name] $units $row[details]")."</div>";}
		elseif($drug!=''){$drug="$drug <div class='grid-100 no_padding $line_through label2'>$i.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".html("$row[name] $units $row[details]")."</div>";}

	}

	?>
	<div class='grid-100'><input type=button class='button_style printment' value=Print /></div>
	<div class='grid-100 no_padding prescription'> <?php
		echo "<div class='grid-20 label prescription_left_column'>PRESCRIPTION NUMBER:</div><div class='grid-40 label2 prescription_middle_column'> $prescription_number </div><div class='grid-40 put_right label2 receip_right_column'>$when_added </div>";
		echo "<div class=clear></div></br>";
		echo "<div class='grid-20 label prescription_left_column'>PATIENT NAME: </div><div class='grid-80 label2 prescription_middle_column'>$pathent_names</div>";
		echo "<div class=clear></div>";
		echo "<div class='grid-20 label prescription_left_column'>FILE NO: </DIV><div class='grid-80 label2 prescription_middle_column'>$patient_number </div>";
		echo "<div class=clear></div>";
		echo "<div class='grid-20 label prescription_left_column'>AGE: </div><div class='grid-80 label2 prescription_middle_column'>$age</div><br><br>";
		echo "<div class=clear></div>";
		echo "<div class='grid-100 label'><span class=underline>DRUGS PRESCRIBED</span></div>";
		echo "<div class='grid-100 label'>$drug</div><br>";
		echo "<div class='grid-20 label prescription_left_column'>PRESCRIBING DOCTOR: </div><div class='grid-80 label2 prescription_middle_column'>$doctor</div><br><br><br>";
		echo "<div class='grid-20 label prescription_left_column'>SIGNATURE: </div><div class='grid-80 label prescription_middle_column'>............................</div>";
	echo "</div>";
}

//this is for dispalying an quotation
elseif(isset($_POST['quotation_disp_num']) and $_POST['quotation_disp_num']!='' ){
	$quotation_number=html($_POST['quotation_disp_num']);
	//get date from other table
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT  a.last_name, a.middle_name, a.first_name,  a.patient_number ,b.when_raised
			FROM quotation_number_generator b	JOIN patient_details_a a ON a.pid = b.pid AND
			b.quotation_number =:quotation_number";
	$placeholders[':quotation_number']=$_POST['quotation_disp_num'];
	$error="Unable to quoattion details 1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$pt_name=html("$row[first_name] $row[middle_name] $row[last_name]");
		$file_no=html($row['patient_number']);
		$date_raised=html("$row[when_raised]");
	}

	?>
	<div class='grid-100'><input type=button class='button_style printment' value=Print /></div>
	<div class='grid-100 no_padding '> <?php
		echo "<div class='grid-30 prefix-70 right_float'><label class=label>QUOTATION NO: $quotation_number <br> DATE: $date_raised </label></div>";
		echo "<div class=clear></div></br>";
		echo "<div class='grid-100 majina'><label class=label>PATIENT NAME: $pt_name <br>FILE NO: $file_no </label></div><br><br>";
		echo "<div class='invoice_view_table'>";
			//now show procedures done
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT procedures.name, tplan_procedure.teeth, tplan_procedure.details, tplan_procedure.authorised_cost
				FROM tplan_procedure	JOIN procedures ON procedures.id = tplan_procedure.procedure_id AND tplan_procedure.quotation_number =:quotation_number
				";
			$placeholders[':quotation_number']="$quotation_number";
			$error="Unable to quotation details 2";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$total =0;
			foreach($s as $row){
				$procedure=html("$row[name]");
				$teeth=html("$row[teeth]");
				$details=html("$row[details]");
				if($details != ''){$details="<br>$details";}
				$authorised_cost=html("$row[authorised_cost]");
				if($procedure == 'X-Ray'){echo "<div class=invoice_view_row><div class='inv_view_80 '>$details $teeth</div><div class='inv_view_20 '>".number_format($authorised_cost,2)."</div></div>";}
				else{echo "<div class=invoice_view_row><div class='inv_view_80 '>$procedure $teeth $details</div><div class='inv_view_20 '>".number_format($authorised_cost,2)."</div></div>";}
				echo "<div class='clear'></div>";
				$total = $total  + $authorised_cost;
			}


			echo "<div class='clear'></div>";
			//now show total
			echo "<div class=invoice_view_row><div class='inv_view_80 total_cost'>TOTAL COST</div><div class='inv_view_20 cost_view'>".number_format($total,2)."</div></div>";
		echo "</div>";
	echo "</div>";
}

//this is for dispalying an invoice that needs admin approval for partiall authorisation settlement
elseif(isset($_POST['invoice_disp_num2']) and $_POST['invoice_disp_num2']!='' and isset($_POST['encoded2']) and
	$_POST['encoded2']=='yes' ){

		$invoice_id=$encrypt->decrypt("$_POST[invoice_disp_num2]");
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT invoice_number fROM unique_invoice_number_generator where	id=:invoice_id ";
		$placeholders[':invoice_id']=$invoice_id;
		$error="Unable to invoice details 1";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() == 0){echo "<div class='gird_100 error_response'>The invoice number does not exist</div>";exit;}
		foreach($s as $row){$invoice_number=html($row['invoice_number']);}
		$_POST['invoice_disp_num']="$invoice_number";


	//check if the invoice has any treatments
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT treatment_procedure_id FROM tplan_procedure where	invoice_number=:invoice_number limit 1";
	$placeholders[':invoice_number']=$_POST['invoice_disp_num'];
	$error="Unable to invoice details 1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() == 0){echo "<div class='gird_100 error_response'>This invoice has no treatments</div>";exit;}

	//get pt name, insurer
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT tplan_procedure.invoice_id, min( tplan_procedure.date_invoiced ) , patient_details_a.last_name, patient_details_a.middle_name,
			patient_details_a.first_name, insurance_company.name, patient_details_a.member_no, patient_details_a.patient_number,
			covered_company.name, covered_company.pre_auth_needed, covered_company.smart_needed
			FROM tplan_procedure	JOIN patient_details_a ON patient_details_a.pid = tplan_procedure.pid AND tplan_procedure.invoice_number =:invoice_number
			left JOIN insurance_company ON insurance_company.id = patient_details_a.type
			left JOIN covered_company ON patient_details_a.company_covered = covered_company.id
			GROUP BY tplan_procedure.invoice_id";
	$placeholders[':invoice_number']=$_POST['invoice_disp_num'];
	$error="Unable to invoice details 1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$company_name=html(" - $row[8]");
		$insurer_name=html("$row[5]");
		$invoice_id=html("$row[0]");
		$pt_name=html("$row[first_name] $row[middle_name] $row[last_name]");
		$file_no=html($row['patient_number']);
		$member_no=html($row['member_no']);
		$date_raised=html("$row[1]");
	}

	?>
	<!--<div class='grid-100'><input type=button class='button_style printment' value=Print /></div>-->
	<div class='grid-100 no_padding '> <?php
		echo "<div class='grid-30 prefix-70 right_float'><label class=label>INVOICE NO: $invoice_number <br> DATE: $date_raised</label></div>";
		echo "<div class=clear></div></br>";
		echo "<div class='grid-100 majina'><label class=label>PATIENT NAME: $pt_name <br>FILE NO: $file_no <br>CORPORATE: $insurer_name $company_name<br>MEMBER NO:$member_no</label></div><br><br>";
		echo "<div class='invoice_view_table'>";
		echo "<div class=invoice_view_row><div class='inv_view2_80 '>TREATMENT PROCEDURE</div>
			  <div class='inv_view2_10 '>COST</div><div class='inv_view2_10 '>AUTHORISED COST</div></div>";

			//now show procedures done
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT procedures.name, tplan_procedure.teeth, tplan_procedure.details, tplan_procedure.authorised_cost , tplan_procedure.unauthorised_cost
				FROM tplan_procedure	JOIN procedures ON procedures.id = tplan_procedure.procedure_id AND tplan_procedure.invoice_id =:invoice_id
				";
			$placeholders[':invoice_id']=$invoice_id;
			$error="Unable to invoice details 2";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$total =$unauthorised_total=0;
			foreach($s as $row){
				$procedure=html("$row[name]");
				$teeth=html("$row[teeth]");
				$details=html("$row[details]");
				if($details != ''){$details="<br>$details";}
				$authorised_cost=html("$row[authorised_cost]");
				$unauthorised_cost=html("$row[unauthorised_cost]");
				if($authorised_cost == ''){$authorised_cost=$unauthorised_cost;}
				if($procedure == 'X-Ray'){
					echo "<div class=invoice_view_row>
						<div class='inv_view2_80 '>$details $teeth</div>
						<div class='inv_view2_10 '>".number_format($unauthorised_cost,2)."</div>
						<div class='inv_view2_10 '>".number_format($authorised_cost,2)."</div>
						</div>";
				}
				else{
					echo "<div class=invoice_view_row>
						<div class='inv_view2_80 '>$procedure $teeth $details</div>
						<div class='inv_view2_10 '>".number_format($unauthorised_cost,2)."</div>
						<div class='inv_view2_10 '>".number_format($authorised_cost,2)."</div>
						</div>";
				}
				echo "<div class='clear'></div>";
				$total = $total  + $authorised_cost;
				$unauthorised_total = $unauthorised_total  + $unauthorised_cost;
			}

			//now show co-payment
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT amount from co_payment where invoice_number=:invoice_id";
			$placeholders[':invoice_id']=$invoice_id;
			$error="Unable to invoice details 3";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$co_payment =0;
			foreach($s as $row){
				$amount=html("$row[amount]");
				echo "<div class=invoice_view_row>
					<div class='inv_view2_80 '>CO-PAYMENT</div>
					<div class='inv_view2_10 '>(".number_format($amount,2).")</div>
					<div class='inv_view2_10 '>(".number_format($amount,2).")</div>
					</div>";
				$total = $total  - $amount;
				$unauthorised_total = $unauthorised_total - $amount;
			}
			echo "<div class='clear'></div>";
			//now show total
			echo "<div class='invoice_view_row total_background'>
				<div class='inv_view2_80 total_cost'>TOTAL COST</div>
				<div class='inv_view2_10 cost_view'>".number_format($unauthorised_total,2)."</div>
				<div class='inv_view2_10 cost_view'>".number_format($total,2)."</div>
				</div>";
		echo "</div>";

		//check if this invoice has been edited and show history
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT a.first_name, a.middle_name, a.last_name, when_edited, edit_description
			from invoice_edits b join users a on b.user_id=a.id  where invoice_id=:invoice_id order by b.id";
		$placeholders[':invoice_id']=$invoice_id;
		$error="Unable to get invoice edits";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			$i=0;
			echo "<div class='clear'></div>";
			echo "<br>";
			echo "<div class='grid-100 no_padding  no_print'><input type=button class='show_invoice_history button_style' value='Previous Changes' /></div>";
			echo "<div class='grid-100 no_padding no_print'><table class='normal_table no_print'><caption>PREVIOUS CHANGES TO INVOICE $invoice_number</caption><thead><tr>
				<th class=inv_ed_count></th><th class=inv_ed_date>DATE<br>EDITED</th>
				<th class=inv_ed_user>EDITED BY</th><th class=inv_ed_edit>EDIT ACTION</th></tr></thead><tbody>";
			foreach($s as $row){
				$i++;
				$when_edited=html("$row[when_edited]");
				$editor=html(ucfirst("$row[first_name] $row[middle_name] $row[last_name]"));
				$description=html("$row[edit_description]");
				echo "<tr><td>$i</td><td>$when_edited</td><td>$editor</td><td>$description</td></tr>";
			}
			echo "</tbody></table></div>";
		}
	echo "</div>";
}

//this is for dispalying an invoice
elseif(isset($_POST['invoice_disp_num']) and $_POST['invoice_disp_num']!='' ){
	if(isset($_POST['encoded1']) and $_POST['encoded1']=='yes'){
		$invoice_id=$encrypt->decrypt("$_POST[invoice_disp_num]");
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT invoice_number fROM unique_invoice_number_generator where	id=:invoice_id ";
		$placeholders[':invoice_id']=$invoice_id;
		$error="Unable to invoice details 1";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() == 0){echo "<div class='gird_100 error_response'>This invoice has no treatments</div>";exit;}
		foreach($s as $row){$invoice_number=html($row['invoice_number']);}
		$_POST['invoice_disp_num']="$invoice_number";
	}
	else{$invoice_number=html($_POST['invoice_disp_num']);}
	//check if the invoice has any treatments
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT treatment_procedure_id FROM tplan_procedure where	invoice_number=:invoice_number limit 1";
	$placeholders[':invoice_number']=$_POST['invoice_disp_num'];
	$error="Unable to invoice details 1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() == 0){echo "<div class='gird_100 error_response'>This invoice has no treatments</div>";exit;}

	//get pt name, insurer
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT tplan_procedure.invoice_id, min( tplan_procedure.date_invoiced ) , patient_details_a.last_name, patient_details_a.middle_name,
			patient_details_a.first_name, insurance_company.name, patient_details_a.member_no, patient_details_a.patient_number,
			covered_company.name, covered_company.pre_auth_needed, covered_company.smart_needed, patient_details_a.type
			FROM tplan_procedure	JOIN patient_details_a ON patient_details_a.pid = tplan_procedure.pid AND tplan_procedure.invoice_number =:invoice_number
			left JOIN insurance_company ON insurance_company.id = patient_details_a.type
			left JOIN covered_company ON patient_details_a.company_covered = covered_company.id
			GROUP BY tplan_procedure.invoice_id";
	$placeholders[':invoice_number']=$_POST['invoice_disp_num'];
	$error="Unable to invoice details 1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$company_name=html(" - $row[8]");
		$insurer_name=html("$row[5]");
		$invoice_id=html("$row[0]");
		$pt_name=html("$row[first_name] $row[middle_name] $row[last_name]");
		$file_no=html($row['patient_number']);
		$member_no=html($row['member_no']);
		$date_raised=html("$row[1]");
		$insurer_id1=html($row['type']);
	}
	$real_date='';
	//check if the invoices for this insurer need to be post dated
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT added_days,start_date from insurance_company where id=:insurer_id";
	$placeholders[':insurer_id']=$insurer_id1;
	$error="Unable to invoice details 2a";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		if($row['start_date'] > '0000-00-00' and "$date_raised" >= $row['start_date']){
			$var = html("$row[added_days]");
			if($row['added_days'] > 0){
				$post_inv_date = date('Y-m-d',strtotime($date_raised ."+ $var day"));
				$real_date='real_date';
			}
			else{$real_date='added_date';}
		}
	}

	//if($real_date==''){$real_date='real_date';}#this calss determines which date is printed as invoice date
	?>
	<div class='grid-100'><input type=button class='button_style printment' value=Print /></div>
	<div class='grid-100 no_padding '> <?php
		echo "<div class='grid-30 prefix-70 right_float'><label class=label>INVOICE NO: $invoice_number <br> <span class=$real_date>DATE: $date_raised</span> ";
		if(isset($post_inv_date)){
			echo "<span class='br_class'><br></span>
				  <span class='added_date'>DATE: $post_inv_date</span>";
		}
		echo "</label></div>";
		echo "<div class=clear></div></br>";
		echo "<div class='grid-100 majina'><label class=label>PATIENT NAME: $pt_name <br>FILE NO: $file_no <br>CORPORATE: $insurer_name $company_name<br>MEMBER NO:$member_no</label></div><br><br>";
		echo "<div class='invoice_view_table'>";
			//now show procedures done
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT procedures.name, tplan_procedure.teeth, tplan_procedure.details, tplan_procedure.authorised_cost , tplan_procedure.unauthorised_cost
				FROM tplan_procedure	JOIN procedures ON procedures.id = tplan_procedure.procedure_id AND tplan_procedure.invoice_id =:invoice_id
				";
			$placeholders[':invoice_id']=$invoice_id;
			$error="Unable to invoice details 2";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$total =0;
			foreach($s as $row){
				$procedure=html("$row[name]");
				$teeth=html("$row[teeth]");
				$details=html("$row[details]");
				if($details != ''){$details="<br>$details";}
				$authorised_cost=html("$row[authorised_cost]");
				$unauthorised_cost=html("$row[unauthorised_cost]");
				if($authorised_cost == ''){$authorised_cost=$unauthorised_cost;}
				if($procedure == 'X-Ray'){echo "<div class=invoice_view_row><div class='inv_view_80 '>$details $teeth</div><div class='inv_view_20 '>".number_format($authorised_cost,2)."</div></div>";}
				else{echo "<div class=invoice_view_row><div class='inv_view_80 '>$procedure $teeth $details</div><div class='inv_view_20 '>".number_format($authorised_cost,2)."</div></div>";}
				echo "<div class='clear'></div>";
				$total = $total  + $authorised_cost;
			}

			//now show co-payment
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT amount from co_payment where invoice_number=:invoice_id";
			$placeholders[':invoice_id']=$invoice_id;
			$error="Unable to invoice details 3";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$co_payment =0;
			foreach($s as $row){
				$amount=html("$row[amount]");
				echo "<div class=invoice_view_row><div class='inv_view_80 '>CO-PAYMENT</div><div class='inv_view_20 '>(".number_format($amount,2).")</div></div>";
				$total = $total  - $amount;
			}
			echo "<div class='clear'></div>";
			//now show total
			echo "<div class='invoice_view_row total_background'><div class='inv_view_80 total_cost'>TOTAL COST</div><div class='inv_view_20 cost_view'>".number_format($total,2)."</div></div>";
		echo "</div>";

		//check if this invoice has been edited and show history
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT a.first_name, a.middle_name, a.last_name, when_edited, edit_description
			from invoice_edits b join users a on b.user_id=a.id  where invoice_id=:invoice_id order by b.id";
		$placeholders[':invoice_id']=$invoice_id;
		$error="Unable to get invoice edits";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			$i=0;
			echo "<div class='clear'></div>";
			echo "<br>";
			echo "<div class='grid-100 no_padding  no_print'><input type=button class='show_invoice_history button_style' value='Previous Changes' /></div>";
			echo "<div class='grid-100 no_padding no_print'><table class='normal_table no_print'><caption>PREVIOUS CHANGES TO INVOICE $invoice_number</caption><thead><tr>
				<th class=inv_ed_count></th><th class=inv_ed_date>DATE<br>EDITED</th>
				<th class=inv_ed_user>EDITED BY</th><th class=inv_ed_edit>EDIT ACTION</th></tr></thead><tbody>";
			foreach($s as $row){
				$i++;
				$when_edited=html("$row[when_edited]");
				$editor=html(ucfirst("$row[first_name] $row[middle_name] $row[last_name]"));
				$description=html("$row[edit_description]");
				echo "<tr><td>$i</td><td>$when_edited</td><td>$editor</td><td>$description</td></tr>";
			}
			echo "</tbody></table></div>";
		}
	echo "</div>";
}


//get results for cash balances
elseif(isset($_POST['token_cbr1']) and 	$_POST['token_cbr1']!='' and $_POST['token_cbr1']==$_SESSION['token_cbr1']
	and  userHasRole($pdo,85)){
		$_SESSION['token_cbr1']='';
		$exit_flag=false;
		$insurer='';
		$having=' having balance_left > 0 ';
		$exit_flag=false;//$greater_and_less=$less_than=$greater_than=$no_range=false;
		$criteria = ' self > 0 ';
		$caption="Patient's with cash balance";
		$sql=$error=$s='';$placeholders=array();
		$sql2=$error2=$s2='';$placeholders2=array();
		//check if balance amount is set
		if(!$exit_flag and ($_POST['balance_range']=='greater_than' or $_POST['balance_range']=='less_than') and
			(!isset($_POST['balance']) or $_POST['balance']=='')){
				$result_class="error_response";
				$result_message="Please specify the balance amount";
				$exit_flag=true;
		}

		//check if ranges are set for range balance
		if(!$exit_flag and  $_POST['balance_range']=='range'  and
			(!isset($_POST['balance_greater']) or $_POST['balance_greater']=='' or !isset($_POST['balance_less']) or $_POST['balance_less']=='')){
				$result_class="error_response";
				$result_message="Please specify the balance range";
				$exit_flag=true;
		}

		//ptypr criteria
		if(!$exit_flag and  $_POST['ptype']!='all'){
			$insurer_id=$encrypt->decrypt($_POST['ptype']);
			$insurer = " and patient_details_a.type=:insurer_id ";
			$placeholders[':insurer_id']=$insurer_id;
		}

		//set having criteria
		if(!$exit_flag and  $_POST['balance_range']=='range'){
			//$greater_and_less=true;
			//$greater_than1=$_POST['balance_greater'];
			//$less_than1=$_POST['balance_less'];
			$criteria = ' self >=:greater_than1 and self <=:less_than1 ';
			$placeholders2[':greater_than1']=$_POST['balance_greater'];
			$placeholders2[':less_than1']=$_POST['balance_less'];
			/*$having = " having balance_left >=:balance_greater and  balance_left <=:balance_less ";
			$placeholders[':balance_greater']=$_POST['balance_greater'];
			$placeholders[':balance_less']=$_POST['balance_less'];*/
			$v1=html($_POST['balance_greater']);
			$v2=html($_POST['balance_less']);
			$caption="Patient's with cash balance between ".number_format($v1,2)." and ".number_format($v2,2);
		}

		//greater than
		elseif(!$exit_flag and  $_POST['balance_range']=='greater_than'){
			$greater_than=true;
			$greater_than_amount=$_POST['balance'];
			$criteria = ' self >=:greater_than_amount1 ';
			$placeholders2[':greater_than_amount1']=$_POST['balance'];/*
			$having = " having balance_left >=:balance";
			$placeholders[':balance']=$_POST['balance'];*/
			$v=html($_POST['balance']);
			$caption="Patient's with cash balance greater than ".number_format($v,2);
		}

		//less than
		elseif(!$exit_flag and  $_POST['balance_range']=='less_than'){
			$less_than=true;
			$less_than_amount=$_POST['balance'];
			$criteria = ' self > 0 and self <=:less_than_amount ';
			$placeholders2[':less_than_amount']=$_POST['balance'];
			/*$having = " having balance_left >0 and balance_left <=:balance";
			$placeholders[':balance']=$_POST['balance'];*/
			$v=html($_POST['balance']);
			$caption="Patient's with cash balance below ".number_format($v,2);
		}

		//no range
		elseif(!$exit_flag and  $_POST['balance_range']=='no_range'){
			$no_range=true;
			$criteria = ' self > 0 ';
			//$less_than_amount=$_POST['balance'];
			/*$having = " having balance_left >0 and balance_left <=:balance";
			$placeholders[':balance']=$_POST['balance'];*/
			$caption="Patient's with cash balance ";
		}

		if(!$exit_flag){
				//fill pt_balances
				//pt_balances($pdo,$encrypt);
				//echo "count is ".$s->rowCount();exit;
				$i=$total=0;
				$invoices_array=$_SESSION['balance_invoice']=array();
					//now check if the pt is from the mentioned insuer

					$sql2="select patient_number,a.pid,first_name,middle_name,last_name,mobile_phone, biz_phone, email_address,
							email_address_2,b.name as company_covered,c.name as insurer , self as balance
							from patient_details_a a
							left join covered_company b on a.company_covered=b.id
							left join insurance_company c on a.type=c.id join pt_balances d on a.pid=d.pid
							where $criteria ";
					$error2="Error: Unable to pt details from uniq ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						$email_present=false;
						foreach($s2 as $row2){
							//$total_cost=$cash_cost2=$cash_cost1=0;

							//$endelea=false;
							//$balance=$total_cost - $amount_paid;
							/*$pid_encrypt2=$encrypt->encrypt($row2['pid']);
							$result=show_pt_statement_brief($pdo,$pid_encrypt2,$encrypt);
							$data=explode('#',"$result");
							$balance=$data[1];*/
							//now insert into pt_balances done only once
							/*$sql3=$error3=$s3='';$placeholders3=array();
							$sql3="update pt_balances set  pnum=:pnum where pid=:pid";
							$placeholders3[':pid']=$row2['pid'];
							$placeholders3[':pnum']=$row2['patient_number'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	insert_sql($sql3, $placeholders3, $error3, $pdo);
							*/
							/*//now insert into pt_balances done only once
							$sql3=$error3=$s3='';$placeholders3=array();
							$sql3="insert into pt_balances set pid=:pid, insurance=:balance";
							$placeholders3[':pid']=$row2['pid'];
							$placeholders3[':balance']=$balance;
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	insert_sql($sql3, $placeholders3, $error3, $pdo);*/

							//$balance=$row2['balance'];
							//if($balance <= 0){continue;}
							/*if($no_range){$endelea=true;}
							elseif($greater_than and ($balance >= $greater_than_amount)){$endelea=true;}
							elseif($less_than and ($balance <=  $less_than_amount)){$endelea=true;}
							elseif($greater_and_less and $balance >= $greater_than1 and $balance <= $less_than1 ){$endelea=true;}
							if($endelea){ */

								$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
								$pnum=html("$row2[patient_number]");
								$company=html("$row2[company_covered]");
								$insurer=html("$row2[insurer]");
								$mobile_no=html("$row2[mobile_phone]");
								$biz_no=html("$row2[biz_phone]");
								$email1=html("$row2[email_address]");
								$email2=html("$row2[email_address_2]");
								$balance=html("$row2[balance]");
								$pid=html("$row2[pid]");
								$pid_encrypt=$encrypt->encrypt("$pid");
								$var=html("$balance#$patient#$pnum#$email1#$email2#$pid");
								$var=$encrypt->encrypt("$var");
								$total=$total + $balance;
								if($company!=''){$company="$insurer - $company";}
								else{$company="$insurer";}

									if($i==0){ ?>
									<form class='' action="dental_b/" target="_new" method="POST" enctype="" name="" id="">
										<?php $token = form_token(); $_SESSION['token_cbr2'] = "$token";  ?>
										<input type="hidden" name="token_cbr2"  value="<?php echo $_SESSION['token_cbr2']; ?>" />

								<?php
										echo "<br><br>
										<table class='normal_table email_table'><caption>$caption</caption><thead>
										<tr><th class=cbr_count></th>
										<th class=cbr_pname>PATIENT</th>
										<th class=cbr_pnum>PATIENT No.</th>
										<th class=cbr_type>TYPE</th>
										<th class=cbr_mobile>MOBILE No.</th>
										<th class=cbr_biz>BUSINESS No.</th>
										<th class=cbr_email1>EMAIL 1</th>
										<th class=cbr_email2>EMAIL 2</th>
										<th class=cbr_balance>BALANCE</th>
										<th class=cbr_send_email>EMAIL</th>
										</tr></thead><tbody>";
									}

									$i++;
									$background_color='';
									if($email1=='' and $email2==''){$background_color='light_blue_background';}
									echo "<tr class=$background_color><td>$i</td><td>$patient</td><td>$pnum</td><td>$company</td><td>$mobile_no</td><td>$biz_no</td><td>$email1</td><td>$email2</td><td>
											<input type=hidden value='$pid_encrypt' /><a href='' class='link_color pt_statement_a'>".
											number_format($balance,2)."</a></td><td>";
											//check if email address i set
											if($email1 == '' and $email2==''){echo "&nbsp;";}
											else{
												$email_present=true;
												echo "<input type=checkbox name='send_email[]' class=email_balance value=$var />";
											}
									echo "</td></tr>";



							/*	$invoices_array[]=array('patient'=>"$patient",  'pnum'=>"$pnum", 'company'=>"$company",
										'mobile_no'=>"$mobile_no",'biz_no'=>"$biz_no", 'email1'=>"$email1",'email2'=>"$email2",
									 'balance'=>"$balance", 'pid'=>"$pid");
						*/
							//	}

						}//end s2
						if($i > 0 ){
							echo "<tr class=total_background><td colspan=8>TOTAL</td><td colspan=2>".number_format($total,2)."</td></tr></tbody></table>";
							if($email_present){
								$result='';
								$result = get_smtp_details($pdo,'credit_control',$encrypt);
								if ("$result" != '') {
											$data =explode('Resource id #',"$result");
											if(isset($data[1])){
												imap_close($result);
												//email doctor appointments
												echo "<div class='grid-100'><input type=button class='button_style check_all put_right check_all_email' value='Check All' /></div><br>";
												/*//get email subject and body
												$sql=$error=$s='';$placeholders=array();
												$sql = "select email_subject, email_body from smtp_users where code='credit_control'";
												$error = "Unable to get credit control email body";
												$s = 	select_sql($sql, $placeholders, $error, $pdo);
												foreach($s as $row){
													$email_subject=html($row['email_subject']);
													$email_body=html($row['email_body']);
													echo "<div class='grid-20 '><label class=label>Email Subject</label></div>
															<div class='grid-50 '><input type=text value='$email_subject' name=email_subject /></div>
															<div class=clear></div>";
													echo "<div class='grid-20 '><label class=label>Email Body</label></div>
														<div class='grid-50'><textarea   rows='' value='$email_body' name=email_body></textarea></div>
														<div class=clear></div>";
												}*/

												echo "<div class='grid-100'><input type=submit class='put_right' value='Send Balance in Email' /></form></div>";
											}
											else{echo "<div class='grid-100 label put_right'>Unable to send emails. Please check email account credentials and internet connectivity</div>";}
								}
								else{echo "<div class='grid-100 label put_right'>Unable to send emails. Please check email account credentials and internet connectivity</div>";}
							}
							//now output the labs to be paid
							exit;
						}
					}//end if
					else{echo "<label  class=label>There is no cash balance matching the selected criteria</label>";}

					/*

				if(count($invoices_array) > 0){
					?>
						<form class='' action="dental_b/" target="_new" method="POST" enctype="" name="" id="">
							<?php $token = form_token(); $_SESSION['token_cbr2'] = "$token";  ?>
							<input type="hidden" name="token_cbr2"  value="<?php echo $_SESSION['token_cbr2']; ?>" />

					<?php
					$i=$total=0;
					foreach($invoices_array as $row){
						if($i==0){
							echo "<br><br>
							<table class='normal_table email_table'><caption>$caption</caption><thead>
							<tr><th class=cbr_count></th>
							<th class=cbr_pname>PATIENT</th>
							<th class=cbr_pnum>PATIENT No.</th>
							<th class=cbr_type>TYPE</th>
							<th class=cbr_mobile>MOBILE No.</th>
							<th class=cbr_biz>BUSINESS No.</th>
							<th class=cbr_email1>EMAIL 1</th>
							<th class=cbr_email2>EMAIL 2</th>
							<th class=cbr_balance>BALANCE</th>
							<th class=cbr_send_email>EMAIL</th>
							</tr></thead><tbody>";
						}
						$i++;
						$patient=ucfirst(html("$row[patient]"));
						$pnum=html("$row[pnum]");
						$type=html("$row[company]");
						$mobile=html("$row[mobile_no]");
						$biz=html("$row[biz_no]");
						$email1=html("$row[email1]");
						$email2=html("$row[email2]");
						$balance=html("$row[balance]");
						$pid=html($row['pid']);
						$pid_encrypt=$encrypt->encrypt("$pid");
						$var=html("$balance#$patient#$pnum#$email1#$email2#$pid");
						$var=$encrypt->encrypt("$var");
						$total=$total + $balance;
						$background_color='';
						if($email1=='' and $email2==''){$background_color='light_blue_background';}
						echo "<tr class=$background_color><td>$i</td><td>$patient</td><td>$pnum</td><td>$type</td><td>$mobile</td><td>$biz</td><td>$email1</td><td>$email2</td><td>
							<input type=hidden value='$pid_encrypt' /><a href='' class='link_color pt_statement_a'>".
							number_format($balance,2)."</a></td><td><input type=checkbox name='send_email[]' class=email_balance value=$var /></td></tr>";
					}
					echo "<tr class=total_background><td colspan=8>TOTAL</td><td colspan=2>".number_format($total,2)."</td></tr></tbody></table>";
					echo "<div class='grid-100'><input type=button class='button_style check_all put_right check_all_email' value='Check All' /></div><br>";
					echo "<div class='grid-100'><input type=submit class=put_right value='Send Balance in Email' /></form></div>";
					//now output the labs to be paid
					exit;

				}
				else{echo "<label  class=label>There is no cash balances that match the selected criteria</label>";}*/
		}
}

//this is for showing invoice payments report by insurer payment date
elseif(isset($_POST['token_ipr2']) and 	$_POST['token_ipr2']!='' and $_POST['token_ipr2']==$_SESSION['token_ipr2']){
		$_SESSION['token_ipr2']='';
		$exit_flag=false;
		$insurer=' all ';
		$covered_company='';$comp_covered=$pnum=$date_criteria=$inv_num_criteria='';
		$pnum_search=$exit_flag=false;
		$sql=$error=$s='';$placeholders=array();

		//check if insurer is selcted
		if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']==''   ){
				$result_class="error_response";
				$result_message="Please select the insurer";
				$exit_flag=true;
		}

		//check if company si selected
		if(!$exit_flag and !isset($_POST['covered_company']) or $_POST['covered_company']=='' ){
				$result_class="error_response";
				$result_message="Please specify the company covered in the search criteria";
				$exit_flag=true;
		}

		//check if dates are selected
		if(!$exit_flag and (!isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']=='') ){
				$result_class="error_response";
				$result_message="Please specify the date range for the report";
				$exit_flag=true;
		}


		if(!$exit_flag){
		$from_date=html($_POST['from_date']);
		$to_date=html($_POST['to_date']);
		$doctor=$insurer_criteria=$covered_company=$balance='';
		$total_cost=$total_paid=$total_billed_cost=0;
		$left_join_insurer=$left_join_company=' left ';
		$paid=$partially_paid=$unpaid=$paid_and_unpaid=false;


		//insurer criteria
		if($_POST['ptype']!='all'){
			$insurer_id=$encrypt->decrypt($_POST['ptype']);
			$insurer_criteria = " and a.type=:insurer_id ";
			$left_join_insurer='';
		}

		//company criteria
		if($_POST['covered_company']!='all'){
			$company_id=$encrypt->decrypt($_POST['covered_company']);
			$covered_company=" and a.company_covered=:company ";
			$left_join_company='';
		}
			//echo "count is ".$s->rowCount();exit;
			$invoices_array=$_SESSION['balance_invoice']=array();
			//get details from unique_inv_table first
			$sql1=$error1=$s1='';$placeholders1=array();
			$sql1="SELECT when_added, receipt_num, amount,pid, invoice_id,receipt_num FROM payments WHERE when_added >=:from_date AND
					when_added <=:to_date and invoice_id > 0";

			$placeholders1[':from_date']=$_POST['from_date'];
			$placeholders1[':to_date']=$_POST['to_date'];
			$error1="Error: Unable to get payments";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			$i1=0;
			foreach($s1 as $row1 ){
				//now check if the pt is from the mentioned insuer
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer
						from patient_details_a a $left_join_company join covered_company b
						on a.company_covered=b.id $covered_company
						$left_join_insurer join insurance_company c on a.type=c.id $insurer_criteria
						where pid=:pid";
				if(isset($company_id) and $company_id > 0){
					$placeholders2[':company']=$company_id;
				}
				if(isset($insurer_id) and $insurer_id > 0){
					$placeholders2[':insurer_id']=$insurer_id;
				}
				$placeholders2[':pid']=$row1['pid'];
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0){
					foreach($s2 as $row2){
						//now get invoice cost
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="SELECT sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 )  AS cost, dispatch_number
								FROM tplan_procedure left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['invoice_id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							$invoice_cost=html($row3['cost']);
							$dispatch_number=html($row3['dispatch_number']);
						}

						//get billed cost
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="SELECT sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 )  AS cost
								FROM tplan_procedure left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['invoice_id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){$billed_cost=html($row3['cost']);}

						//now amount paid
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="SELECT sum( amount ) as amount FROM payments where invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['invoice_id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){$amount_paid=html($row3['amount']);}

						/*//check if fully paid
						$endelea=false;
						if($paid and $amount_paid == $invoice_cost){$endelea=true;}
						elseif($partially_paid and ($amount_paid < $invoice_cost) and ($amount_paid > 0)){$endelea=true;}
						elseif($unpaid and ($amount_paid < $invoice_cost) and ($amount_paid == 0)){$endelea=true;}
						elseif($paid_and_unpaid ){$endelea=true;}
						if($endelea){*/
							$i1++;
							//get doctor who raised invoice
							$doctor='';
							$sql4=$error3=$s3='';$placeholders3=array();
							$sql4="SELECT first_name, middle_name, last_name, invoice_number FROM users a, unique_invoice_number_generator b
									where b.id=:invoice_id and a.id=b.added_by ";
							$placeholders4[':invoice_id']=$row1['invoice_id'];
							$error4="Error: Unable to pt details from uniq ";
							$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
							foreach($s4 as $row4){
								$invoice_number=html($row4['invoice_number']);
								$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));
							}

							$when_added=html("$row1[when_added]");
							$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
							$company=html("$row2[company_covered]");
							$insurer=html("$row2[insurer]");
							if($company!=''){$company="$insurer - $company";}
							else{$company="$insurer";}
							$cost=$invoice_cost;
							$balance=html($invoice_cost - $amount_paid);
							$current_payment=html(number_format($row1['amount'],2));
							$total_paid = $total_paid + $row1['amount'];
							//$invoice_number=html("$row1[invoice_number]");
							$invoice_id=html("$row1[invoice_id]");
							$receipt_number=html("$row1[receipt_num]");
							//$val=$encrypt->encrypt("$invoice_id#$balance#$row1[pid]");
							//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
							$_SESSION['balance_invoice'][$invoice_id]=$balance;
							/*$invoices_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor",
										'dispatch_number'=>"$dispatch_number",'company'=>"$company", 'cost'=>"$cost",'sum_paid'=>"$amount_paid",
									 'invoice_id'=>"$invoice_id", 'invoice_number'=>"$invoice_number", 'balance'=>"$balance",
									 'billed_cost'=>"$billed_cost");*/
							//start ouput
								if($i1==1){//print table header
									if($_POST['ptype']!='all'){$caption ="$insurer";}
									else{$caption ="Insurance ";}
									if($_POST['covered_company']!='all'){$caption ="$insurer - $company";}

									$caption=strtoupper("$caption payments between $from_date and $to_date");
									echo "<br><br>
										<table class='normal_table'><caption>$caption</caption><thead>
										<tr><th class=invoice_in_count></th>
										<th class=invoice_in_date>DATE</th>
										<th class=invoice_in_doctor>DOCTOR</th>
										<th class=invoice_in_patient>PATIENT NAME</th>
										<th class=invoice_in_company>CORPORATE</th>
										<th class=invoice_in_id>INVOICE No.</th>
										<th class=invoice_in_cost>BILLED<br>COST</th>
										<th class=invoice_in_cost>AUTHORISED<br>COST</th>
										<th class=invoice_in_tray>AMOUNT PAID</th>
										<th class=invoice_in_finished>RECEIPT NUMBER</th>
										</tr></thead><tbody>";
								}
								//print table body
									$sum_paid=$amount_paid;


									echo "<tr><td class=count>$i1</td><td>$when_added</td><td>$doctor</td><td>$patient</td>
											<td>$company</td><td><a href='' class='invoice_no_link link_color'>$invoice_number</a>
									</td><td>";
									if(!in_array("$invoice_number", $invoices_array)){
										$total_billed_cost = $total_billed_cost + $billed_cost;
										$total_cost = $total_cost + $cost;
										if($billed_cost!=''){echo number_format($billed_cost,2); }
										else {echo '';}
									}
									else{echo '';}
									echo "</td><td>";
									if(!in_array("$invoice_number", $invoices_array)){
										if($cost!=''){echo number_format($cost,2);}
										else{echo 'Unauthorised';}
									}
									else{echo '';}
									$invoices_array[]="$invoice_number";
									echo "</td><td>";
									//check to see if balance is full or partiall and show a link
									/*if($cost > $sum_paid and $sum_paid > 0){
										$val=$encrypt->encrypt("$invoice_id#$invoice_number");
										echo "<a href='?$val' class='balance_payment link_color'>".number_format($sum_paid,2)."</a>";
									}
									else{
										if($sum_paid > 0){echo number_format($sum_paid,2);}
										else echo '';
									}*/
									echo "$current_payment</td><td>$receipt_number</td></tr>";




							//end
						//}

					}//end s2


				}//end if


			}//end s1

			if($i1 > 0){//tbale was printed so show total
				echo  "<tr class=total_background><td colspan=6>TOTAL</td><td>".number_format($total_billed_cost,2)."</td><td >".number_format($total_cost,2)."</td><td>".number_format($total_paid,2)."</td><td></td></tr>";
					echo "</tbody></table>";
					echo "<br>";
			}
			else{//tbale not printed
				echo "<label  class=label>There are no payments for the selected criteria</label>";
			}

				//}
			//else{echo "<label  class=label>There is no unpaid invoices for the selected criteria</label>";}
			echo "<div id=view_lab></div>";
			exit;
		}//end do if exit flag is not true
		if($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}


}

//this is for showing invoice payments report
elseif(isset($_POST['token_ipr1']) and 	$_POST['token_ipr1']!='' and $_POST['token_ipr1']==$_SESSION['token_ipr1']){
		$_SESSION['token_ipr1']='';
		$exit_flag=false;
		$insurer=' all ';
		$covered_company='';$comp_covered=$pnum=$date_criteria=$inv_num_criteria='';
		$pnum_search=$exit_flag=false;
		$sql=$error=$s='';$placeholders=array();
		//check if search byis set
		if(!$exit_flag and !isset($_POST['sby']) or $_POST['sby']==''   ){
				$result_class="error_response";
				$result_message="Please select the search criteria";
				$exit_flag=true;
		}

		//check if doctor selected
		if(!$exit_flag and !isset($_POST['doc']) or $_POST['doc']=='' ){
				$result_class="error_response";
				$result_message="Please specify a search criteria for the doctor";
				$exit_flag=true;
		}

		//check if insurer is selcted
		if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']==''   ){
				$result_class="error_response";
				$result_message="Please select the insurer";
				$exit_flag=true;
		}

		//check if company si selected
		if(!$exit_flag and !isset($_POST['covered_company']) or $_POST['covered_company']=='' ){
				$result_class="error_response";
				$result_message="Please specify the company covered in the search criteria";
				$exit_flag=true;
		}

		//check if dates are selected
		if(!$exit_flag and (!isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']=='') ){
				$result_class="error_response";
				$result_message="Please specify the date range for the report";
				$exit_flag=true;
		}


		if(!$exit_flag){
		$from_date=html($_POST['from_date']);
		$to_date=html($_POST['to_date']);
		$doctor=$insurer_criteria=$covered_company=$balance='';
		$total_cost=$total_paid=$total_billed_cost=0;
		$left_join_insurer=$left_join_company=' left ';
		$paid=$partially_paid=$unpaid=$paid_and_unpaid=false;
		//doctor criteria
		if($_POST['doc']!='all'){
			$doc_id=$encrypt->decrypt($_POST['doc']);
			$doctor = " and unique_invoice_number_generator.added_by=:doc_id ";

		}

		//insurer criteria
		if($_POST['ptype']!='all'){
			$insurer_id=$encrypt->decrypt($_POST['ptype']);
			$insurer_criteria = " and a.type=:insurer_id ";
			$left_join_insurer='';
		}

		//company criteria
		if($_POST['covered_company']!='all'){
			$company_id=$encrypt->decrypt($_POST['covered_company']);
			$covered_company=" and a.company_covered=:company ";
			$left_join_company='';
		}
		//paid invoices
		if($_POST['sby']=='paid'){
			//$balance = " and sum_invoice_paid > 0 ";
			$paid=true;
			$caption=strtoupper("paid  invoices raised between $from_date and $to_date");
		}

		//partially paid invoices
		if($_POST['sby']=='partially_paid'){
			//$balance = " and balance > 0 ";
			$partially_paid=true;
			$caption=strtoupper("partially paid invoices raised between $from_date and $to_date");
		}

		//unpaid invoices
		if($_POST['sby']=='unpaid'){
			//$balance = " and sum_invoice_paid = 0 ";
			$unpaid=true;
			$caption=strtoupper("unpaid invoices raised between $from_date and $to_date");
		}

		//paid and unpaid invoices
		if($_POST['sby']=='paid_and_unpaid'){
			$paid_and_unpaid=true;
			$caption=strtoupper("paid and unpaid invoices raised between $from_date and $to_date");
		}

			/*	$sql="select tplan_procedure.invoice_id,tplan_procedure.invoice_number,
						sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS cost,
						sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) - c.sum_paid  as balance,
						min(tplan_procedure.date_invoiced) as date_invoiced, users.first_name,users.last_name,users.middle_name,
						patient_details_a.first_name,patient_details_a.last_name,patient_details_a.middle_name,	insurance_company.name,
						covered_company.name ,tplan_procedure.pid , ifnull(c.sum_paid,0) as sum_invoice_paid, dispatch_number,
						sum( tplan_procedure.authorised_cost ) as sum_authorised_cost
						from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid and tplan_procedure.invoice_id > 0

						join users on users.id=tplan_procedure.created_by $doctor
						left join covered_company on patient_details_a.company_covered=covered_company.id $company
						left	join insurance_company on patient_details_a.type=insurance_company.id  $insurer
						left join (select invoice_id,sum(amount) as sum_paid from payments group by invoice_id) as c
							on c.invoice_id=tplan_procedure.invoice_id
						left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
						group by tplan_procedure.invoice_id
						having  sum_authorised_cost > 0 and date_invoiced >=:from_date and date_invoiced <=:to_date $balance
						order by tplan_procedure.invoice_id";


			$placeholders[':from_date']=$_POST['from_date'];
			$placeholders[':to_date']=$_POST['to_date'];
			$s = select_sql($sql, $placeholders, $error, $pdo);	*/
			//echo "count is ".$s->rowCount();exit;
			$invoices_array=$_SESSION['balance_invoice']=array();
			//get details from unique_inv_table first
			$sql1=$error1=$s1='';$placeholders1=array();
			$sql1="SELECT * FROM unique_invoice_number_generator WHERE when_raised >=:from_date AND when_raised <=:to_date
				$doctor";
			if(isset($doc_id) and $doc_id > 0){
				$placeholders1[':doc_id']=$doc_id;
			}
			$placeholders1[':from_date']=$_POST['from_date'];
			$placeholders1[':to_date']=$_POST['to_date'];
			$error1="Error: Unable to date range uniq ";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			$i1=0;
			foreach($s1 as $row1 ){
				//now check if the pt is from the mentioned insuer
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer
						from patient_details_a a $left_join_company join covered_company b
						on a.company_covered=b.id $covered_company
						$left_join_insurer join insurance_company c on a.type=c.id $insurer_criteria
						where pid=:pid";
				if(isset($company_id) and $company_id > 0){
					$placeholders2[':company']=$company_id;
				}
				if(isset($insurer_id) and $insurer_id > 0){
					$placeholders2[':insurer_id']=$insurer_id;
				}
				$placeholders2[':pid']=$row1['pid'];
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0){
					foreach($s2 as $row2){
						//now get invoice cost
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="SELECT sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 )  AS cost, dispatch_number
								FROM tplan_procedure left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							$invoice_cost=html($row3['cost']);
							$dispatch_number=html($row3['dispatch_number']);
						}

						//get billed cost
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="SELECT sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 )  AS cost
								FROM tplan_procedure left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){$billed_cost=html($row3['cost']);}

						//now amount paid
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="SELECT sum( amount ) as amount FROM payments where invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){$amount_paid=html($row3['amount']);}

						//check if fully paid
						$endelea=false;
						if($paid and $amount_paid == $invoice_cost){$endelea=true;}
						elseif($partially_paid and ($amount_paid < $invoice_cost) and ($amount_paid > 0)){$endelea=true;}
						elseif($unpaid and ($amount_paid < $invoice_cost) and ($amount_paid == 0)){$endelea=true;}
						elseif($paid_and_unpaid ){$endelea=true;}
						if($endelea){
							$i1++;
							//get doctor who raised invoice
							$doctor='';
							$sql4=$error3=$s3='';$placeholders3=array();
							$sql4="SELECT first_name, middle_name, last_name FROM users where id=:user_id";
							$placeholders4[':user_id']=$row1['added_by'];
							$error4="Error: Unable to pt details from uniq ";
							$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
							foreach($s4 as $row4){$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));}

							$when_added=html("$row1[when_raised]");
							$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
							$company=html("$row2[company_covered]");
							$insurer=html("$row2[insurer]");
							if($company!=''){$company="$insurer - $company";}
							else{$company="$insurer";}
							$cost=$invoice_cost;
							$balance=html($invoice_cost - $amount_paid);
							$invoice_number=html("$row1[invoice_number]");
							$invoice_id=html("$row1[id]");
							//$val=$encrypt->encrypt("$invoice_id#$balance#$row1[pid]");
							//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
							$_SESSION['balance_invoice'][$invoice_id]=$balance;
							/*$invoices_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor",
										'dispatch_number'=>"$dispatch_number",'company'=>"$company", 'cost'=>"$cost",'sum_paid'=>"$amount_paid",
									 'invoice_id'=>"$invoice_id", 'invoice_number'=>"$invoice_number", 'balance'=>"$balance",
									 'billed_cost'=>"$billed_cost");*/
							//start ouput
								if($i1==1){//print table header
									echo "<br><br>
										<table class='normal_table'><caption>$caption</caption><thead>
										<tr><th class=invoice_in_count></th>
										<th class=invoice_in_date>DATE</th>
										<th class=invoice_in_doctor>DOCTOR</th>
										<th class=invoice_in_patient>PATIENT NAME</th>
										<th class=invoice_in_company>CORPORATE</th>
										<th class=invoice_in_id>INVOICE No.</th>
										<th class=invoice_in_cost>BILLED<br>COST</th>
										<th class=invoice_in_cost>AUTHORISED<br>COST</th>
										<th class=invoice_in_tray>AMOUNT PAID</th>
										<th class=invoice_in_finished>DISPATCH NUMBER</th>
										</tr></thead><tbody>";
								}
								//print table body
									$sum_paid=$amount_paid;
									$total_billed_cost = $total_billed_cost + $billed_cost;
									$total_cost = $total_cost + $cost;
									$total_paid = $total_paid + $sum_paid;
									echo "<tr><td class=count>$i1</td><td>$when_added</td><td>$doctor</td><td>$patient</td>
											<td>$company</td><td><a href='' class='invoice_no_link link_color'>$invoice_number</a>
									</td><td>";
									if($billed_cost!=''){echo number_format($billed_cost,2); }
									else {echo '';}
									echo "</td><td>";
									if($cost!=''){echo number_format($cost,2);}
									else{echo 'Unauthorised';}
									echo "</td><td>";
									//check to see if balance is full or partiall and show a link
									if($cost > $sum_paid and $sum_paid > 0){
										$val=$encrypt->encrypt("$invoice_id#$invoice_number");
										echo "<a href='?$val' class='balance_payment link_color'>".number_format($sum_paid,2)."</a>";
									}
									else{
										if($sum_paid > 0){echo number_format($sum_paid,2);}
										else echo '';
									}
									echo "</td><td>$dispatch_number</td></tr>";




							//end
						}

					}//end s2


				}//end if


			}//end s1

			if($i1 > 0){//tbale was printed so show total
				echo  "<tr class=total_background><td colspan=6>TOTAL</td><td>".number_format($total_billed_cost,2)."</td><td >".number_format($total_cost,2)."</td><td>".number_format($total_paid,2)."</td><td></td></tr>";
					echo "</tbody></table>";
					echo "<br>";
			}
			else{//tbale not printed
				if($paid){echo "<label  class=label>There are no paid invoices for the selected criteria</label>";}
					elseif($partially_paid){echo "<label  class=label>There are no partially paid invoices for the selected criteria</label>";;}
					elseif($unpaid){echo "<label  class=label>There are no unpaid invoices for the selected criteria</label>";}
					elseif($paid_and_unpaid ){echo "<label  class=label>There is no invoices for the selected criteria</label>";}

			}
			/*if($s->rowCount() > 0){

				$i=0;
				foreach($s as $row){
					$when_added=html("$row[date_invoiced]");
					$patient=ucfirst(html("$row[8] $row[10] $row[9]"));
					$doctor=ucfirst(html("$row[5] $row[7] $row[6]"));
					$insurer=html("$row[11]");
					$company=html("$row[12]");
					if($company!=''){$company="$insurer - $company";}
					else{$company="$insurer";}
					$dispatch_number=html($row['dispatch_number']);
					if($dispatch_number == '0'){$dispatch_number='';}
					$cost=html($row['cost']);
					$sum_paid=html($row['sum_invoice_paid']);
					$balance=html($row['balance']);
					$invoice_number=html("$row[invoice_number]");
					$invoice_id=html("$row[invoice_id]");
					$val=$encrypt->encrypt("$invoice_id");
					//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
					//$_SESSION['balance_invoice'][$invoice_id]=$balance;
					if($i==0 and $pnum_search){$caption=strtoupper("Unpaid invoices for $patient");}

					$invoices_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor", 'company'=>"$company", 'cost'=>"$cost",
									 'invoice_id'=>"$invoice_id", 'val'=>"$val",'invoice_number'=>"$invoice_number", 'balance'=>"$balance",
									 'dispatch_number'=>"$dispatch_number",'sum_paid'=>"$sum_paid");
				}*/
				//now output the labs to be paid
				/*if(count($invoices_array) > 0){ ?>


			<div class=clear></div><br>
			<?php
					$count=0;
					echo "<br><br>
					<table class='normal_table'><caption>$caption</caption><thead>
					<tr><th class=invoice_in_count></th>
					<th class=invoice_in_date>DATE</th>
					<th class=invoice_in_doctor>DOCTOR</th>
					<th class=invoice_in_patient>PATIENT NAME</th>
					<th class=invoice_in_company>CORPORATE</th>
					<th class=invoice_in_id>INVOICE No.</th>
					<th class=invoice_in_cost>BILLED<br>COST</th>
					<th class=invoice_in_cost>AUTHORISED<br>COST</th>
					<th class=invoice_in_tray>AMOUNT PAID</th>
					<th class=invoice_in_finished>DISPATCH NUMBER</th>
					</tr></thead><tbody>";
					$i=0;
					$n=count($invoices_array);
					foreach($invoices_array as $unpaid_invoice_array){
						$count++;
						$total_billed_cost = $total_billed_cost + $unpaid_invoice_array['billed_cost'];
						$total_cost = $total_cost + $unpaid_invoice_array['cost'];
						$total_paid = $total_paid + $unpaid_invoice_array['sum_paid'];
						echo "<tr><td class=count>$count</td>
								<td>$unpaid_invoice_array[when_added]</td>
								<td>$unpaid_invoice_array[doctor]</td>
								<td>$unpaid_invoice_array[patient]</td>
								<td>$unpaid_invoice_array[company]</td>
						<td><a href='' class='invoice_no_link link_color'>$unpaid_invoice_array[invoice_number]</a>
						</td><td>";
						if($unpaid_invoice_array['billed_cost']!=''){echo number_format($unpaid_invoice_array['billed_cost'],2); }
						else {echo '';}
						echo "</td><td>";
						if($unpaid_invoice_array['cost']!=''){echo number_format($unpaid_invoice_array['cost'],2);}
						else{echo 'Unauthorised';}
						echo "</td><td>";
						//check to see if balance is full or partiall and show a link
						if($unpaid_invoice_array['cost'] > $unpaid_invoice_array['sum_paid'] and $unpaid_invoice_array['sum_paid'] > 0){
							$val=$encrypt->encrypt("$unpaid_invoice_array[invoice_id]#$unpaid_invoice_array[invoice_number]");
							echo "<a href='?$val' class='balance_payment link_color'>".number_format($unpaid_invoice_array['sum_paid'],2)."</a>";
						}
						else{
							if($unpaid_invoice_array['sum_paid'] > 0){echo number_format($unpaid_invoice_array['sum_paid'],2);}
							else echo '';
						}
						echo "</td><td>$unpaid_invoice_array[dispatch_number]</td></tr>";
						$i++;
					}
					echo  "<tr class=total_background><td colspan=6>TOTAL</td><td>".number_format($total_billed_cost,2)."</td><td >".number_format($total_cost,2)."</td><td>".number_format($total_paid,2)."</td><td></td></tr>";
					echo "</tbody></table>";
					echo "<br>";

				}
				else{
					if($paid){echo "<label  class=label>There are no paid invoices for the selected criteria</label>";}
					elseif($partially_paid){echo "<label  class=label>There are no partially paid invoices for the selected criteria</label>";;}
					elseif($unpaid){echo "<label  class=label>There are no unpaid invoices for the selected criteria</label>";}
					elseif($paid_and_unpaid ){echo "<label  class=label>There is no invoices for the selected criteria</label>";}

				}*/
				//}
			//else{echo "<label  class=label>There is no unpaid invoices for the selected criteria</label>";}
			echo "<div id=view_lab></div>";
			exit;
		}//end do if exit flag is not true
		if($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}


}

//this is for dispalying partiaall payments for invoices
elseif(isset($_POST['partiall_payment']) and $_POST['partiall_payment']!='' ){
	$var=$encrypt->decrypt("$_POST[partiall_payment]");
	$data=explode('#',"$var");
	$invoice_id=$data[0];
	$invoice_number=html("$data[1]");
	//get pt name, insurer
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT a.when_added, a.receipt_num,a.amount,a.tx_number, b.name,c.first_name,c.middle_name,c.last_name
		 from payments a left join payment_types b on a.pay_type=b.id
		 left join users c on a.created_by=c.id
		 where invoice_id=:invoice_id order by a.id";
	$placeholders[':invoice_id']=$invoice_id;
	$error="Unable to get invoice payment details";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$total=0;
		echo "<table class=normal_table><caption>PAYMENTS FOR INVOICE $invoice_number</caption><thead><th class=pp_date>PAYMENT DATE</th><th class=pp_amount>AMOUNT PAID</th>
			<th class=pp_tx>TRANSACTION NUMBER</th><th class=pp_receipt>RECEIPT NUMBER</th><th class=pp_user>RECEIVED BY</th></thead><tbody>";
		foreach($s as $row){
			$payment_date=html($row['when_added']);
			$amount=html($row['amount']);
			$transaction_number=html($row['tx_number']);
			$received_by=html("$row[first_name] $row[middle_name] $row[last_name]");
			$receipt_number=html($row['receipt_num']);
			$total=$total + $amount;
			$amount=number_format($amount,2);
			echo "<tr><td>$payment_date</td><td>$amount</td><td>$transaction_number</td><td>$receipt_number</td>
			<td>$received_by</td></tr>";
		}
		echo "<tr class=total_background><td>TOTAL</td><td>".number_format($total,2)."</td><td colspan=3></td></tr></tbody></table>";
	}
}

//this is for dispalying an invoice that has been deleted
elseif(isset($_POST['invoice_disp_num_deleted']) and $_POST['invoice_disp_num_deleted']!='' ){
	$invoice_number=$_POST['invoice_disp_num_deleted'];
	//echo "fff $invoice_number";
	//get pt name, insurer
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT deleted_invoices.invoice_id, min( deleted_invoices.date_invoiced ) , patient_details_a.last_name, patient_details_a.middle_name,
			patient_details_a.first_name, insurance_company.name, patient_details_a.member_no, patient_details_a.patient_number,
			covered_company.name, covered_company.pre_auth_needed, covered_company.smart_needed
			FROM deleted_invoices	JOIN patient_details_a ON patient_details_a.pid = deleted_invoices.pid AND deleted_invoices.invoice_number =:invoice_number
			left JOIN insurance_company ON insurance_company.id = patient_details_a.type
			left JOIN covered_company ON patient_details_a.company_covered = covered_company.id
			GROUP BY deleted_invoices.invoice_id";
	$placeholders[':invoice_number']=$_POST['invoice_disp_num_deleted'];
	$error="Unable to get deleted invoice details 1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$company_name=html(" - $row[8]");
		$insurer_name=html("$row[5]");
		$invoice_id=html("$row[0]");
		$pt_name=html("$row[first_name] $row[middle_name] $row[last_name]");
		$file_no=html($row['patient_number']);
		$member_no=html($row['member_no']);
		$date_raised=html("$row[1]");
	}

	?>
	<!--<div class='grid-100'><input type=button class='button_style printment' value=Print /></div>-->
	<div class='grid-100 no_padding '> <?php
		echo "<div class='prefix-80 grid-10 label'>INVOICE NO: </div><div class='grid-10 label2'>$invoice_number </div>
			<div class='prefix-80 grid-10 label'>DATE: </div><div class='grid-10 label2'>$date_raised </div>";
		echo "<div class=clear></div></br>";
		echo "<div class='grid-15 label no_padding'>PATIENT NAME:</div><div class='grid-85 label2'> $pt_name </div>
			<div class='grid-15 label no_padding'>FILE NO: </div><div class='grid-85 label2'>$file_no </div>
			<div class='grid-15 label no_padding'>CORPORATE: </div><div class='grid-85 label2'>$insurer_name $company_name</div>
			<div class='grid-15 label no_padding'>MEMBER NO:</div><div class='grid-85 label2'>$member_no <br><br></div>";
		echo "<div class='invoice_view_table'>";
			//now show procedures done
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT procedures.name, deleted_invoices.teeth, deleted_invoices.details, deleted_invoices.authorised_cost , deleted_invoices.unauthorised_cost
				FROM deleted_invoices	JOIN procedures ON procedures.id = deleted_invoices.procedure_id AND deleted_invoices.invoice_id =:invoice_id
				";
			$placeholders[':invoice_id']=$invoice_id;
			$error="Unable to deleted invoice details 2";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$total =0;
			foreach($s as $row){
				$procedure=html("$row[name]");
				$teeth=html("$row[teeth]");
				$details=html("$row[details]");
				if($details != ''){$details="<br>$details";}
				$authorised_cost=html("$row[authorised_cost]");
				$unauthorised_cost=html("$row[unauthorised_cost]");
				if($authorised_cost == ''){$authorised_cost=$unauthorised_cost;}
				//if($procedure == 'X-Ray'){echo "<div class=invoice_view_row><div class='inv_view_80 '>$details $teeth</div><div class='inv_view_20 '>".number_format($authorised_cost,2)."</div></div>";}
				//else{
				echo "<div class=invoice_view_row><div class='inv_view_80 '>$procedure $teeth $details</div><div class='inv_view_20 '>".number_format($authorised_cost,2)."</div></div>";
				$total = $total  + $authorised_cost;
			}

			//now show co-payment
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT amount from deleted_co_payment where invoice_number=:invoice_id";
			$placeholders[':invoice_id']=$invoice_id;
			$error="Unable to get deleted invoice details 3";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$co_payment =0;
			foreach($s as $row){
				$amount=html("$row[amount]");
				echo "<div class=invoice_view_row><div class='inv_view_80 '>CO-PAYMENT</div><div class='inv_view_20 '>(".number_format($amount,2).")</div></div>";
				$total = $total  - $amount;
			}

			//now show total
			echo "<div class='total_background invoice_view_row'><div class='inv_view_80 '>TOTAL COST</div><div class=inv_view_20>".number_format($total,2)."</div></div>";
		echo "</div>";
	echo "</div>";
}

//this is for editing a corprate details e.g. pre-auth needed or not
elseif( isset($_POST['edit_corprate_details']) and 	isset($_SESSION['edit_corprate_details']) and
	$_POST['edit_corprate_details']==$_SESSION['edit_corprate_details'] and userHasRole($pdo,10)){
	$exit_flag=false;
	$i=0;
	$n=1;

	$company_id=$encrypt->decrypt(html($_POST['ninye']));
	if(isset($_POST['insured_yes_no'])){$insured_yes_no[$i]=$_POST['insured_yes_no'];}
	if(isset($_POST['employer_name'])){$comp_name=html($_POST['employer_name']);}
	if(isset($_POST['ins_name'])){$ins_id[$i]=$_POST['ins_name'];}
	//$ins_id=$_POST['ins_name'];
	if(isset($_POST['pre_auth'])){$pre_auth_needed[$i]=$_POST['pre_auth'];}
	if(isset($_POST['smart_check'])){$smart_needed[$i]=$_POST['smart_check'];}
	if(isset($_POST['co_pay'])){$co_pay_type[$i]=$_POST['co_pay'];}
	if(isset($_POST['co_pay_value'])){$co_pay_val[$i]=$_POST['co_pay_value'];}
	if(isset($_POST['start_date'])){$start_cover[$i]=$_POST['start_date'];}
	if(isset($_POST['end_date'])){$end_cover[$i]=$_POST['end_date'];}
	if(isset($_POST['cover_type'])){$cover_type[$i]=$_POST['cover_type'];}
	if(isset($_POST['cover_limit'])){$cover_limit[$i]=$_POST['cover_limit'];}
	if(isset($_POST['suspend_cover'])){$suspend_cover[$i]=$_POST['suspend_cover'];}
	if(isset($_POST['suspension_reason'])){$suspension_reason[$i]=$_POST['suspension_reason'];}
	//check if max limt is integer
	$invoice_daily_limit[$i]=0;
	if($_POST['invoice_daily_limit'] != ''){

		$_POST['invoice_daily_limit']=str_replace(",", "", $_POST['invoice_daily_limit']);
		if(!ctype_digit($_POST['invoice_daily_limit'])){
			$var=html("$_POST[invoice_daily_limit]");
			$message = "bad# Unable to save details as Daily max invoice amount  $var is not a valid integer. ";
			$exit_flag=true;

		}
		else{$invoice_daily_limit[$i]=$_POST['invoice_daily_limit'];}
	}
	if(isset($_POST['suspension_reason'])){$suspension_reason[$i]=$_POST['suspension_reason'];}

	try{
		$pdo->beginTransaction();
			while($i < $n){
					//echo "$insured_yes_no -- $insured_yes_no[$i]";exit;
					//check in insurer is set
					$insured_yes_no[$i]=html("$insured_yes_no[$i]");
					/*if($insured_yes_no[$i]=='YES' and $ins_id[$i]==''){$message = "bad#  This patient type is insured but no insurer has been specified";
														$exit_flag=true;
														break;
					}*/
					if($ins_id[$i]==''){$message = "bad#  Patient type has not been specified";
														$exit_flag=true;
														break;
					}
					else{	$ins_id[$i]=$encrypt->decrypt("$ins_id[$i]");}

					if(!$exit_flag and $insured_yes_no[$i]=='YES' and $ins_id[$i]==3){
						$message = "bad#  Patient type cannot be CASH for insured companies";
						$exit_flag=true;
						break;
					}

					if(!$exit_flag and $insured_yes_no[$i]=='NO' and $ins_id[$i]!=3){
						$message = "bad#  Only CASH patient types can be uninsured";
						$exit_flag=true;
						break;
					}
					//now check cover limit

					if(isset($cover_limit[$i]) and $cover_limit[$i]!=''){
						if(strtoupper("$cover_limit[$i]") != 'UNLIMITED'){
							$cover_limit[$i]=str_replace(",", "", "$cover_limit[$i]");
							if( !ctype_digit($cover_limit[$i])){
								//check if it has only 2 decimal places
								$data=explode('.',$cover_limit[$i]);
								if ( count($data) != 2 ){
									$cover_limit[$i]=html("$cover_limit[$i]");
									$message = "bad#  Unable to save changes as $cover_limit[$i] is not a valid number or UNLIMITED";
									$exit_flag=true;
									break;
								}
								elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
									$cover_limit[$i]=html("$cover_limit[$i]");
									$message = "bad#  Unable to save changes as $cover_limit[$i] is not a valid number or UNLIMITED";
									$exit_flag=true;
									break;
								}
							}
						}
					}
					else{$cover_limit[$i]='';}

					//now check start and end date
					$data=explode("-",$start_cover[$i]);
					if(isset($start_cover[$i]) and $start_cover[$i]!='' and !checkdate($data[1], $data[2], $data[0])){
							$start_cover[$i]=html("$start_cover[$i]");
							$message = "bad#  Unable to save changes as $start_cover[$i] is not a valid date ";
							$exit_flag=true;
							break;
						}
					$data=explode("-",$end_cover[$i]);
					if(isset($end_cover[$i]) and $end_cover[$i]!='' and !checkdate($data[1], $data[2], $data[0])){
							$end_cover[$i]=html("$end_cover[$i]");
							$message = "bad#  Unable to save changes as $end_cover[$i] is not a valid date ";
							$exit_flag=true;
							break;
						}
					//this will ensure that insurance is not empty
					/*if($ins_id[$i]==''){
						//check if pre-auth is set
						$error_message = " Unable to add new corprate as no insurer has been specified";
														$exit_flag=true;
														break;

					}*/

				//ensure all fields are correctly set
					if($ins_id[$i]==3){//this is for cash 3 is patient type cash
						//check if pre-auth is set
						if($pre_auth_needed[$i]=='YES'){$message = "bad#  Unable to save changes, Pre-Auth needed has been set to YES for $comp_name but
														this patient type has been set to CASH";
														$exit_flag=true;
														break;
						}
						//check if smart is set
						if($smart_needed[$i]=='YES'){$message = "bad#  Unable to save changes, Smart Check Needed  has been set to YES for $comp_name but
														this patient type has been set to CASH";
														$exit_flag=true;
														break;
						}
						//check if co_pay is set
						if($co_pay_type[$i]!=''){		$co_pay=html("$co_pay_type[$i]");
														$message = "bad#  Unable to save changes, Co-Pay Type has been set to $co_pay for $comp_name but
														this patient type has been set to CASH";
														$exit_flag=true;
														break;
						}
						//check if co_pay_val is set
						if($co_pay_val[$i]!=''){$co_pay_amount=html("$co_pay_val[$i]");
												$message = "bad#  Unable to save changes, Co-Pay Value has been set to $co_pay_amount for $comp_name but
														this patient type has been set to CASH";
														$exit_flag=true;
														break;
						}
						//check if start_cover is set
						if($start_cover[$i]!=''){$start=html("$start_cover[$i]");
						$message = "bad#  Unable to save changes, Start Cover has been set to $start for $comp_name but
														this patient type has been set to CASH";
														$exit_flag=true;
														break;
						}
						//check if end cover is set
						if($end_cover[$i]!=''){$end=html("$end_cover[$i]");
						$message = "bad#  Unable to save changes, End Cover has been set to $end for $comp_name but
														this patient type has been set to CASH";
														$exit_flag=true;
														break;
						}
						//check if cover_type is set
						if($cover_type[$i]!=''){$cover_t=html("$cover_type[$i]");
													$message = "bad#  Unable to save changes, Cover Type has been set to $cover_t for $comp_name but
														this patient type has been set to CASH";
														$exit_flag=true;
														break;
						}
						//check if cover limit
						if($cover_limit[$i]!=''){$cover_l=html("$cover_limit[$i]");
													$message = "bad#  Unable to save changes, Cover Limit has been set to $cover_l for $comp_name but
														this patient type has been set to CASH";
														$exit_flag=true;
														break;
						}

					}
					//this ios for when insurer is specified as anything else other than cash 3
					elseif($ins_id[$i]!=3){
						//check if pre-auth is set
						if($pre_auth_needed[$i]==''){$message = "bad#  Unable to  save changes, Pre-Auth needed has not been set  for $comp_name yet
														the company is insured";
														$exit_flag=true;
														//$message="an attempt has been made to make pre-auth needed empty for $comp_name in table covered_company";
														//log_security($pdo,$message);
														break;
						}
						//check if smart is set
						if($smart_needed[$i]==''){$message = "bad#  Unable to  save changes, Smart Check needed has not been set  for $comp_name yet
														the company is insured";
														$exit_flag=true;
														//$message="an attempt has been made to make smart check run needed empty for $comp_name in table covered_company";
														//log_security($pdo,$message);
														break;
						}
						//check if co_pay is set
						if($co_pay_type[$i]!='' and $co_pay_val[$i]==''){		$co_pay=html("$co_pay_type[$i]");
														$message = "bad#  Unable to  save changes, Co-Pay Type has been set to $co_pay for $comp_name but
														but no corresponding value has been set";
														$exit_flag=true;
														break;
						}
						//check if co_value is set
						if($co_pay_type[$i]=='' and $co_pay_val[$i]!=''){		$co_pay_amount=html("$co_pay_val[$i]");
														$message = "bad#  Unable to  save changes, Co-Pay Value  has been set to $co_pay_amount for $comp_name but
														but no corresponding Co-Pay Type  has been set";
														$exit_flag=true;
														break;
						}
						//check if start_cover is set
						if($start_cover[$i]==''){$start=html("$start_cover[$i]");
						$message = "bad#  Unable to  save changes, as Start Cover date has not been set  for $comp_name though the company is insured";
														$exit_flag=true;
														break;
						}
						//check if end cover is set
						if($end_cover[$i]==''){$end=html("$end_cover[$i]");
						$message = "bad#  Unable to  save changes, as End Cover date has not been set  for $comp_name though the company is insured";
														$exit_flag=true;
														break;
						}
						if($end_cover[$i] < $start_cover[$i]){$end=html("$end_cover[$i]");$start=html("$start_cover[$i]");
						$message = "bad#  Unable to  save changes, the end cover date of $end is before the start cover date of $start  for $comp_name.";
														$exit_flag=true;
														break;
						}
						//check if cover_type is set
						if($cover_type[$i]==''){$cover_t=html("$cover_type[$i]");
													$message = "bad#  Unable to  save changes, as Cover Type has not been set for $comp_name.";
														$exit_flag=true;
														break;
						}
						//check if cover limit
						if($cover_limit[$i]==''){$cover_l=html("$cover_limit[$i]");
													$message = "bad#  Unable to  save changes, as Cover Limit has not been set  for $comp_name";
														$exit_flag=true;
														break;
						}
						//check if cover suspension reason is given
						if($suspend_cover[$i]=='Yes' and $suspension_reason[$i]==''){$cover_l=html("$cover_limit[$i]");
													$message = "bad#  Unable to  save changes, no reason has been specified for suspending cover";
														$exit_flag=true;
														break;
						}

					}
					// start by validating input
					//check i fvalue for co_pay is valid number
					//remove commas if they were used for formating
					$co_pay_val[$i]=str_replace(",", "", "$co_pay_val[$i]");
					if(isset($co_pay_val[$i]) and $co_pay_val[$i]!='' and !ctype_digit($co_pay_val[$i])){
						//check if it has only 2 decimal places
						$data=explode('.',$co_pay_val[$i]);
						if ( count($data) != 2 ){
							$co_pay_val[$i]=html("$co_pay_val[$i]");
							$message = "bad#  Unable to  save changes as $co_pay_val[$i] is not a valid number ";
							$exit_flag=true;
							break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$co_pay_val[$i]=html("$co_pay_val[$i]");
							$message = "bad#  Unable to  save changes as $co_pay_val[$i] is not a valid number ";
							$exit_flag=true;
							break;
						}
					}





					//if(isset($ins_id[$i]) and $ins_id[$i]!=''){
						//decrypt insurance compnay id and check that exist
						//echo "$ins_id[$i]--".$encrypt->decrypt("$ins_id[$i]");
						//$ins_id[$i]=$encrypt->decrypt("$ins_id[$i]");
						//echo "xxxx--$ins_id[$i]";
						$sql=$error=$s='';$placeholders=array();
						$sql="select id from insurance_company where id=:id";
						$error="Unable to check if insurance company exists";
						$placeholders[':id']=$ins_id[$i];
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						if((0 + $s->rowCount()) ==  0){
									$message = "bad#  Unable to  save changes, invalid insurer set";
									//call function to log this activity
									//$message="an update of $ins_id[$i] was attemped into covered_company table for column insurer_id";
									//log_security($pdo,$message);
									$exit_flag=true;
									break;
						}
					//}



					//set insurer to 0 if the patient type is not insured
					//if($insured_yes_no[$i]=='NO'){$ins_id[$i]=0;}

					//get new insurer name
					$after_edit_insurer_name='';
					$sql=$error=$s='';$placeholders=array();
					$sql="select name from insurance_company where id=:insurer_id";
					$error="Unable to get new insurer name";
					$placeholders[':insurer_id']=$ins_id[$i];
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$after_edit_insurer_name=html($row['name']);}

					//get current details
					$sql=$error=$s='';$placeholders=array();
					$sql="select a.name, b.name as insurer_name, insurer_id, co_pay_type, value, pre_auth_needed, smart_needed, start_cover, end_cover,
						cover_type, cover_limit, insured , suspended_cover, suspended_reason ,a.invoice_daily_limit from covered_company a left join insurance_company b on a.insurer_id=b.id
						where a.id=:company_id";
					$error="Unable to get company details";
					$placeholders[':company_id']=$company_id;
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$before_edit_company_name=html($row['name']);
						$before_edit_insurer_name=html($row['insurer_name']);
						$before_edit_insurer_id=html($row['insurer_id']);
						$before_edit_co_pay_type=html($row['co_pay_type']);
						$before_edit_value=html($row['value']);
						$before_edit_pre_auth_needed=html($row['pre_auth_needed']);
						$before_edit_smart_needed=html($row['smart_needed']);
						$before_edit_start_cover=html($row['start_cover']);
						$before_edit_end_cover=html($row['end_cover']);
						$before_edit_cover_type=html($row['cover_type']);
						$before_edit_cover_limit=html($row['cover_limit']);
						$before_edit_insured=html($row['insured']);
						$before_edit_suspend_cover=html($row['suspended_cover']);
						$before_edit_suspend_cover_reason=html($row['suspended_reason']);
						$before_edit_invoice_daily_limit=html($row['invoice_daily_limit']);
					}

					//check if a patient swap is needed
						//are patients moving from insured to CASH
						if(!isset($_POST['admin_p']) and $before_edit_insurer_id != $ins_id[$i]){
							//get number of patients under current employer and type who were not swapped
							$sql=$error=$s='';$placeholders=array();
							$sql="select count(pid) from patient_details_a where type=:type and company_covered=:company_covered
								and pid not in (select old_pid from swapped_patients )";
							$error="Unable to get number of pts in company covered";
							$placeholders[':type']=$before_edit_insurer_id;
							$placeholders[':company_covered']=$company_id;
							$s = 	select_sql($sql, $placeholders, $error, $pdo);
							$number_of_pts=0;
							foreach($s as $row){$number_of_pts = html($row[0]);}
							if($number_of_pts > 0){
								//prompt for admin password
								$admin_pass_input="<div class='grid-20 remove_left_padding'>Administrator Password</div>
									<div class=grid-20><input type=password name=admin_pass /></div>
									<div class='grid-10 '><input class='changed_patient_type button_style' type=button value=Submit /></div>
											<div class=clear></div><br>";

								$string="You are trying to change the patient type from $before_edit_insurer_name
										to $after_edit_insurer_name for $before_edit_company_name patients.<br> Currently there are ".number_format($number_of_pts)."
										patients under $before_edit_insurer_name / $before_edit_company_name, these patients will be swapped and moved from
										$before_edit_insurer_name to $after_edit_insurer_name. <br>Please provide the administrator password to perform the corporate
										swap.<br> $admin_pass_input";

								$message= "bad#patient_type_changed#$string ";
								$exit_flag=true;
								break;

							}

						}

						if(isset($_POST['admin_p'])){// and $before_edit_insurer_id != $ins_id[$i]){
							//echo "admin p is $_POST[admin_p] and $before_edit_insurer_id != $ins_id[$i] ";
							//check if password is empty
							if($_POST['admin_p']==''){
								//get number of patients under current employer and type who were not swapped
								$sql=$error=$s='';$placeholders=array();
								$sql="select count(pid) from patient_details_a where type=:type and company_covered=:company_covered
									and pid not in (select old_pid from swapped_patients )";
								$error="Unable to get number of pts in company covered";
								$placeholders[':type']=$before_edit_insurer_id;
								$placeholders[':company_covered']=$company_id;
								$s = 	select_sql($sql, $placeholders, $error, $pdo);
								$number_of_pts=0;
								foreach($s as $row){$number_of_pts = html($row[0]);}
								if($number_of_pts > 0){
									//prompt for admin password
									$admin_pass_input="<div class='grid-20 remove_left_padding'>Administrator Password</div>
										<div class=grid-20><input type=password name=admin_pass /></div>
										<div class='grid-10 '><input class='changed_patient_type button_style' type=button value=Submit /></div>
												<div class=clear></div><br>";

									$string="<span class=make_bold>Wrong administrator password provided</span> <br>
											You are trying to change the patient type from $before_edit_insurer_name
											to $after_edit_insurer_name for $before_edit_company_name patients.<br> Currently there are ".number_format($number_of_pts)."
											patients under $before_edit_insurer_name / $before_edit_company_name, these patients will be swapped and moved from
											$before_edit_insurer_name to $after_edit_insurer_name. <br>Please provide the administrator password to perform the corporate
											swap.<br> $admin_pass_input";

									$message= "bad#patient_type_changed#$string ";
									$exit_flag=true;
									break;

								}

							}
							//now check if it matches a user with administrator privilege
							elseif($_POST['admin_p']!=''){
								$admin_p=hash_hmac('sha1', $_POST['admin_p'], $salt);
								$sql=$error=$s='';$placeholders=array();
								$sql="select first_name, middle_name, last_name , a.id from users a, privileges b where a.id=b.user_id and b.menu_id=109 and
								a.password=:password limit 1";
								$error="Unable to get administrator user";
								$placeholders['password']="$admin_p";
								$s = select_sql($sql, $placeholders, $error, $pdo);

								if($s->rowCount() == 0){ //if privilege not found check in roles
									$sql=$error=$s='';$placeholders=array();
									$sql="SELECT first_name, middle_name, last_name, d.id FROM users d, user_roles c, role_privileges b WHERE d.id = c.user_id
									AND c.role_id = b.role_id aND b.menu_id =109 AND d.password = :password limit 1";
									$error="Unable to get administartor user";
									$placeholders['password']="$admin_p";
									$s = select_sql($sql, $placeholders, $error, $pdo);
								}
								if($s->rowCount() == 0){
									//wrong password
									//get number of patients under current employer and type who were not swapped
									$sql=$error=$s='';$placeholders=array();
									$sql="select count(pid) from patient_details_a where type=:type and company_covered=:company_covered
										and pid not in (select old_pid from swapped_patients )";
									$error="Unable to get number of pts in company covered";
									$placeholders[':type']=$before_edit_insurer_id;
									$placeholders[':company_covered']=$company_id;
									$s = 	select_sql($sql, $placeholders, $error, $pdo);
									$number_of_pts=0;
									foreach($s as $row){$number_of_pts = html($row[0]);}
									if($number_of_pts > 0){
										//prompt for admin password
										$admin_pass_input="<div class='grid-20 remove_left_padding'>Administrator Password</div>
											<div class=grid-20><input type=password name=admin_pass /></div>
											<div class='grid-10 '><input class='changed_patient_type button_style' type=button value=Submit /></div>
													<div class=clear></div><br>";

										$string="<span class=make_bold>Wrong administrator password provided</span> <br>
												You are trying to change the patient type from $before_edit_insurer_name
												to $after_edit_insurer_name for $before_edit_company_name patients.<br> Currently there are ".number_format($number_of_pts)."
												patients under $before_edit_insurer_name / $before_edit_company_name, these patients will be swapped and moved from
												$before_edit_insurer_name to $after_edit_insurer_name. <br>Please provide the administrator password to perform the corporate
												swap.<br> $admin_pass_input";

										$message= "bad#patient_type_changed#$string ";
										$exit_flag=true;
										break;

									}

								}
								else{
									foreach($s as $row){
										$admin_user_name=html("$row[first_name] $row[middle_name] $row[last_name]");
										$admin_id=$row['id'];
									}
								}
							}

							//perform swap

						}

					//perform corporate swap
					if(isset($admin_user_name) and $admin_user_name!=''){
						corporate_swap("$admin_user_name", $pdo, $before_edit_insurer_id, $company_id, $ins_id[$i], $admin_id);/*$result1 = corporate_swap("$admin_user_name", $pdo, $before_edit_insurer_id, $company_id, $ins_id[$i], $admin_id);
						if($result1=='bad'){
							$message= "bad#Unable to complete corporate swap ";
							$exit_flag=true;
							break;
						}*/
					}

					//now update new company
					$sql=$error=$s='';$placeholders=array();
					$sql="update covered_company set  name=:name, insurer_id=:ins_id, 	co_pay_type=:co_pay_type ,	value=:value ,	pre_auth_needed=:pre_auth,
						smart_needed=:smart_needed, 	start_cover=:start_cover, 	end_cover=:end_cover, 	cover_type=:cover_type,
						cover_limit=:cover_limit, insured=:insured_yes_no , suspended_cover=:suspend_cover , suspended_reason=:suspend_reason ,invoice_daily_limit=:invoice_daily_limit
						where id=:id";
					$error="Unable to edit insured companies";
					$placeholders[':ins_id']=$ins_id[$i];
					$placeholders[':insured_yes_no']="$insured_yes_no[$i]";
					$placeholders[':co_pay_type']="$co_pay_type[$i]";
					$placeholders[':value']="$co_pay_val[$i]";
					$placeholders[':pre_auth']="$pre_auth_needed[$i]";
					$placeholders[':smart_needed']="$smart_needed[$i]";
					$placeholders[':start_cover']="$start_cover[$i]";
					$placeholders[':end_cover']="$end_cover[$i]";
					$placeholders[':cover_limit']="$cover_limit[$i]";
					$placeholders[':cover_type']="$cover_type[$i]";
					$placeholders[':name']="$comp_name";
					$placeholders[':id']=$company_id;
					$placeholders[':suspend_cover']="$suspend_cover[$i]";
					$placeholders[':suspend_reason']="$suspension_reason[$i]";
					$placeholders[':invoice_daily_limit']=$invoice_daily_limit[$i];


					$s = 	insert_sql($sql, $placeholders, $error, $pdo);

					//record changes for auditing
					if($s ){
						$description=array();
						//$skip_insurer_id_compare=false;
						if("$before_edit_company_name" != "$comp_name"){
							if($comp_name == ''){$comp_name = " unset";}
							$description[]="Corporate name changed from $before_edit_company_name to $comp_name";}
						if("$before_edit_insured" != "$insured_yes_no[$i]"){
							if($insured_yes_no[$i] == 'NO'){
								//check if administartor password is set
							$description[]="Corporate changed insurance cover from $before_edit_insurer_name to cash";}
							elseif($insured_yes_no[$i] == 'YES'){
								//$skip_insurer_id_compare=true;
								$description[]="Corporate changed from cash to insured by $after_edit_insurer_name";}
						}
						if( $before_edit_insurer_id != $ins_id[$i] ){
							if($after_edit_insurer_name == ''){$after_edit_insurer_name = "  unset";}
							if($before_edit_insurer_name == ''){$before_edit_insurer_name=' unset ';}
							$description[]="Insurer changed from $before_edit_insurer_name  to $after_edit_insurer_name";
						}
						if("$before_edit_co_pay_type" != "$co_pay_type[$i]"){
							if($before_edit_co_pay_type == ''){$before_edit_co_pay_type='  unset';}
							if($co_pay_type[$i] == ''){$co_pay_type[$i]='  unset';}
							if($before_edit_co_pay_type == ''){$before_edit_co_pay_type=' unset ';}
							$description[]="Co-Pay Type changed from $before_edit_co_pay_type to $co_pay_type[$i]";}
						if("$before_edit_value" != "$co_pay_val[$i]"){
							if($before_edit_value == ''){$before_edit_value=' unset';}
							if($co_pay_val[$i] == ''){$co_pay_val[$i]=' unset';}
							if($before_edit_value == ''){$before_edit_value=' unset ';}
							$description[]="Co-Pay Type value changed from $before_edit_value to $co_pay_val[$i]";}
						if("$before_edit_pre_auth_needed" != "$pre_auth_needed[$i]"){$description[]="Pre-Authorisation Needed changed from $before_edit_pre_auth_needed to $pre_auth_needed[$i]";}
						if("$before_edit_smart_needed" != "$smart_needed[$i]"){$description[]="Smart Card Check Needed changed from $before_edit_smart_needed to $smart_needed[$i]";}
						if("$before_edit_start_cover" != "$start_cover[$i]"){
							if($start_cover[$i] == ''){$start_cover[$i]=' unset';}
							if($before_edit_start_cover == ''){$before_edit_start_cover=' unset ';}
							$description[]="Start insurance cover date changed from $before_edit_start_cover to  $start_cover[$i]";
						}
						if("$before_edit_end_cover" != "$end_cover[$i]"){
							if($end_cover[$i] == ''){$end_cover[$i]=' unset';}
							if($before_edit_end_cover == ''){$before_edit_end_cover=' unset ';}
							$description[]="End insurance cover date changed from $before_edit_end_cover to $end_cover[$i]";
						}
						if("$before_edit_cover_type" != "$cover_type[$i]"){
							if($cover_type[$i] == ''){$cover_type[$i]=' unset';}
							if($before_edit_cover_type == ''){$before_edit_cover_type=' unset ';}
							$description[]="Insurance cover type changed from $before_edit_cover_type to $cover_type[$i]";
						}
						if("$before_edit_cover_limit" != "$cover_limit[$i]"){
							if($cover_limit[$i] == ''){$cover_limit[$i]=' unset';}
							if($before_edit_cover_limit == ''){$before_edit_cover_limit=' unset ';}
							$description[]="Insurance cover limit changed from $before_edit_cover_limit to $cover_limit[$i]";
						}
						if("$before_edit_suspend_cover" != "$suspend_cover[$i]"){
							if($cover_limit[$i] == ''){$cover_limit[$i]=' unset';}
							if($before_edit_cover_limit == ''){$before_edit_cover_limit=' unset ';}
							$description[]="Insurance 'Suspend Cover' changed from $before_edit_suspend_cover to $suspend_cover[$i]";
						}



						$n1=0;
						$n2=count($description);
						while($n1 < $n2){
							$sql=$error1=$s='';$placeholders=array();
							$sql="insert into corporate_edits set
								when_changed=now(),
								user_id=:user_id,
								company_id=:company_id,
								edit_description=:edit_description";
							$error="Unable to insert corprate  edits";
							$placeholders[':user_id']=$_SESSION['id'];
							$placeholders[':company_id']=$company_id;
							$placeholders[':edit_description']="$description[$n1]";
							$s = insert_sql($sql, $placeholders, $error, $pdo);
							$n1++;
						}

					}
					$i++;
			}


			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			if($tx_result){$message = "good2#  Changes have been saved #$_POST[ninye] ";}
			//elseif(!$tx_result){$error_message="   Unable to edit Insured Companies  ";}
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="   Unable to add new corprate   ";
	}
	echo "$message";
	//$data=explode('#',
}

//this is for editiong a corprate for thing s like insurer and ore auth
elseif(isset($_POST['edit_corporate2']) and $_POST['edit_corporate2']!='' and userHasRole($pdo,10)){
	$company_id=$encrypt->decrypt($_POST['edit_corporate2']);
	$company_name=$insurer_name=$insurer_id='';
	//get company name and insurer
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from covered_company where id=:company_id";
	$placeholders[':company_id']=$company_id;
	$error="Unable to select covered company details";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$insured_yes_no=html($row['insured']);
			$name=html($row['name']);
			$emp_id=html($row['id']);
			$val=$encrypt->encrypt("$emp_id");
			//$val=$row['id'];
			$co_pay_val=html($row['value']);
			$start_cover=html($row['start_cover']);
			$end_cover=html($row['end_cover']);
			$cover_type=html($row['cover_type']);
			$cover_limit=html($row['cover_limit']);
			$insurer_id = html($row['insurer_id']);
			$pre_auth=html($row['pre_auth_needed']);
			$smart_run=html($row['smart_needed']);
			$co_pay=html($row['co_pay_type']);
			$suspend_cover=html($row['suspended_cover']);
			$suspended_reason=html($row['suspended_reason']);
			if($row['invoice_daily_limit'] == 0){$row['invoice_daily_limit']='';}
			$invoice_daily_limit=html($row['invoice_daily_limit']);
	}
	if(isset($error_message) and $error_message!=''){echo "<div class='error_response'>";htmlout("ERROR: $error_message");echo "</div>";$error_message='';}
	elseif(isset($success_message) and $success_message!=''){echo "<div class='success_response'>";htmlout("$success_message");echo"</div>";$success_message='';}

	?>
	<div  id=edit_covered_procedure >

		<form action="" method="post" name="" class='dialog_form' id="">
		<div class='grid-20 alpha'><label for="" class="label">Corporate/Employer Name</label></div>
			<div class='grid-30'><input type=text name=employer_name id=employer_name value='<?php echo "$name"; ?>' /></div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label">Insured</label></div>
			<div class='grid-30'><select id=insured_yes_no name="insured_yes_no"  >
			<?php
				if($insured_yes_no=='YES'){	echo "<option value='YES' selected>YES</option><option value='NO'>NO</option>"; }
				else{	echo "<option value='NO' selected>NO</option><option value='YES' >YES</option>"; }
			?>
			</select></div>
			<div class=' grid-20'><label for="" class="label"> Patient Type</label></div>
			<div class=' grid-30 omega'><?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select name,id from insurance_company order by name";
				$error2="Unable to get insurer";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					echo "<select name=ins_name id=ins_name class='set_to_empty insurer_input input_in_table_cell edit_corporate_employer' ><option></option>";
					foreach($s2 as $row2){
						$insurer=html($row2['name']);
						$val=$encrypt->encrypt(html($row2['id']));
						if($row2['id'] == 3){$_SESSION['cash_type_coded'] = "$val";}
						if($insurer_id == $row2['id']) {	echo "<option value='$val' selected>$insurer</option>"; }
						else {	echo "<option value='$val'>$insurer</option>"; }
					}
					echo "</select>";

			?></div>
			<div class=clear></div>
			<br>
			<div class='grid-20 alpha'><label for="" class="label"> Pre-Authorisation Needed</label></div>
			<div class='grid-30'><select class='pre_smart insurer_input' id=pre_auth name=pre_auth>
			<?php
					if($pre_auth == "YES") {	echo "<option value='YES' selected>YES</option><option value='NO' >NO</option>"; }
					elseif($pre_auth == "NO") {	echo "<option value='NO' selected>NO</option><option value='YES' >YES</option>"; }
			?>
			</select></div>
			<div class='grid-20 '><label for="" class="label"> Smart Card Check Needed</label></div>
			<div class='grid-30 omega'><select class='pre_smart insurer_input' id=smart_check name=smart_check>
			<?php
					if($smart_run == "YES") {	echo "<option value='YES' selected>YES</option><option value='NO' >NO</option>"; }
					elseif($smart_run == "NO") {	echo "<option value='NO' selected>NO</option><option value='YES' >YES</option>"; }
			?>
			</select></div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label"> Co-Pay Type</label></div>
			<div class='grid-30'><select class=' insurer_input' id=co_pay name=co_pay><option></option>
			<?php
					if($co_pay == "PERCENTAGE") {	echo "<option value='PERCENTAGE' selected>PERCENTAGE</option><option value='CASH' >CASH</option>"; }
					elseif($co_pay == "CASH") {	echo "<option value='CASH' selected>CASH</option><option value='PERCENTAGE' >PERCENTAGE</option>"; }
					else{	echo "<option value='CASH' >CASH</option><option value='PERCENTAGE' >PERCENTAGE</option>"; }
			?>
			</select></div>
			<div class='grid-20'><label for="" class="label"> Value</label></div>
			<div class='grid-30 omega'><input value='<?php echo "$co_pay_val"; ?>' class='insurer_input' type=text id=co_pay_value name=co_pay_value title="For percentage, value should be between 0 and 100 withiut the % sign" /></div>

			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label">Insurance valid from</label></div>
			<div class='grid-30'><input  value='<?php echo "$start_cover"; ?>' type=text id=start_date class='insurer_input date_picker_no_past' name=start_date  /></div>
			<div class='grid-20'><label for="" class="label">Until this date</label></div>
			<div class='grid-30 omega'><input  plcaeholder='yyyy-mm-dd' value='<?php echo "$end_cover"; ?>' class='insurer_input date_picker_no_past' id=end_date type=text name=end_date  /></div>

			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label">Insurance cover type</label></div>
			<div class='grid-30'><select id=cover_type name=cover_type class='insurer_input input_in_table_cell'><option></option>
			<?php
				if($cover_type == "Family"){echo "<option value='Family' selected>Family</option><option value='Individual'>Individual</option>";}
				elseif($cover_type == "Individual"){echo "<option value='Family' >Family</option><option value='Individual' selected>Individual</option>";}
				else{echo "<option value='Family' >Family</option><option value='Individual'>Individual</option>";}
			?>
			</select>
			</div>
			<div class='grid-20'><label for="" class="label">Cover Limit(KES)</label></div>
			<div class='grid-30 omega'><input  value='<?php echo "$cover_limit"; ?>'  class='insurer_input' id=cover_limit type=text name=cover_limit  />
				<br><label for="" class="label">If the cover is unlimited, then type "UNLIMITED" in the field above.</label>
			</div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label">Suspend Cover</label></div>
			<div class='grid-30 '><select id=suspend_cover name=suspend_cover class='insurer_input input_in_table_cell'>
			<?php
			    $suspend_cover_class='';
				if($suspend_cover == "No"){
					echo "<option value='No' selected>No</option><option value='Yes'>Yes</option>";
					$suspend_cover_class='hide_first';
				}
				elseif($suspend_cover == "Yes"){
					echo "<option value='Yes' selected>Yes</option><option value='No' >No</option>";
					$suspend_cover_class='';
				}
				else{echo "<option value='No' >No</option><option value='Yes'>Yes</option>";}
			?>
				</select>
			</div>
			<?php echo "<div class='grid-20 $suspend_cover_class suspend_reason_class'>"; ?> <label for="" class="label insurer_input">Suspension Reason</label></div>
			<?php echo "<div class='grid-30 omega $suspend_cover_class suspend_reason_class '>"; ?> <input class='suspend_reason_text insurer_input' type=text name=suspension_reason id=suspension_reason value='<?php echo "$suspended_reason"; ?>' />
			</div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label">Daily Max Inv. Amount</label></div>
			<div class='grid-30 '><input value='<?php echo "$invoice_daily_limit"; ?>' class='insurer_input' type=text id=invoice_daily_limit name=invoice_daily_limit   /></div>
			<div class=clear></div><br>

			<?php
				$token = form_token(); $_SESSION['edit_corprate_details'] = "$token";
				$ninye=$encrypt->encrypt($company_id);
			?>
			<input type="hidden" name="ninye"  value="<?php echo $ninye; ?>" />

			<input type="hidden" name="edit_corprate_details"  value="<?php echo $_SESSION['edit_corprate_details']; ?>" />

			<div class='grid-30 prefix-70'>	<br><input class='submit' type="submit"  value="Save Changes"/></div>
			<div class=clear></div><br>
			</form>
			<br>
			<?php
				//check if we have previous changes
				$sql4=$error3=$s3='';$placeholders3=array();
				$sql4="SELECT first_name, middle_name, last_name, when_changed, edit_description
					FROM corporate_edits a join users b on a.user_id=b.id where company_id=:company_id order by a.id";
				$placeholders4[':company_id']=$company_id;
				$error4="Error: Unable to company history edits ";
				$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
				if($s4->rowCount() > 0){

					echo "<table class=normal_table><caption>PREVIOUS CHANGES FOR $name</caption><thead>
					<tr><th class=inv_ed_count></th><th class=inv_ed_date>DATE</th><th class=inv_ed_user>EDITED BY</th>
					<th class=inv_ed_edit>EDIT ACTION</th></tr></thead><tbody>";
					$i1=0;
					foreach($s4 as $row4){
						$i1++;
						$user=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));
						$edit_action=html($row4['edit_description']);
						$date=html($row4['when_changed']);
						echo "<tr><td>$i1</td><td>$date</td><td>$user</td><td>$edit_action</td></tr>";
					}
					echo "</tbody></table>";
				}

			?>
		</div>
		<?php
}


//this is for editiong x-ray refferer
elseif(isset($_POST['edit_xray_referer']) and $_POST['edit_xray_referer']!='' and userHasRole($pdo,27)){
	$ref_id=$encrypt->decrypt($_POST['edit_xray_referer']);
	$company_name=$insurer_name=$insurer_id='';
	//get ref details
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from xray_refering_doc where id=:ref_id";
	$placeholders[':ref_id']=$ref_id;
	$error="Unable to select xray refferer";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$name=html($row['referrer_name']);
		$tel=html($row['telephone']);
		$email=html($row['email_address']);
		$val=$encrypt->encrypt(html($row['id']));
		if($row['listed']==1){$checked= ' checked ';}
		else{$checked='';}
	}
	if(isset($error_message) and $error_message!=''){echo "<div class='error_response'>";htmlout("ERROR: $error_message");echo "</div>";$error_message='';}
	elseif(isset($success_message) and $success_message!=''){echo "<div class='success_response'>";htmlout("$success_message");echo"</div>";$success_message='';}

	?>
	<div  id=edit_xray_ref >

		<form class='dialog_form2' action="" method="post" name="" id="">
			<div class='grid-20 alpha'><label for="user" class="label">Technician Name </label></div>
			<div class='grid-30'><input type=text name=ref_name value='<?php echo "$name"; ?>' /></div>
			<div class='grid-20'><label for="user" class="label"> Telephone </label></div>
			<div class='grid-30 omega'><input type=text name=telephone_no value='<?php echo "$tel"; ?>' /></div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="user" class="label">Email Address </label></div>
			<div class='grid-30 '><input type=text name=email_address value='<?php echo "$email"; ?>'/></div>
			<div class='grid-20 alpha'><label for="user" class="label">Unlist this refferer </label></div>
			<div class='grid-30 '>
				<input type=checkbox name=del value='<?php echo "$val"; ?>' <?php echo "$checked" ?> />
			</div>

			<?php $token = form_token(); $_SESSION['token_xray_ref_2'] = "$token";  ?>
		<input type="hidden" name="token_xray_ref_2"  value="<?php echo $_SESSION['token_xray_ref_2']; ?>" />
		<input type="hidden" name="ninye"  value="<?php echo "$val"; ?>" />
			<div class='grid-30 prefix-20 suffix-50'>	<br><input type="submit"  value="Edit Referrer"/></div>
			<div class=clear></div>
			</form>
		</div>
		<?php
}


//this is for editiong a corprate cover details
elseif(isset($_POST['edit_corporate']) and $_POST['edit_corporate']!='' and userHasRole($pdo,10)){
	$company_id=$encrypt->decrypt($_POST['edit_corporate']);
	$company_name=$insurer_name=$insurer_id='';
	//get company name and insurer
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.name,b.name,a.insurer_id from covered_company a, insurance_company b where a.id=:company_id and b.id=a.insurer_id";
	$placeholders[':company_id']=$company_id;
	$error="Unable to select covered company details";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$company_name=html($row[0]);
		$insurer_name=html($row[1]);
		$insurer_id=html($row[2]);
	}

	?>
	<div  id=edit_covered_procedure >

		<form action="" method="post" name="" class='dialog_form' id="">


			<div class='grid-30'><label for="" class="label"> Select Procedure to remove from cover</label></div>
			<div class='grid-50'>
				<?php
					$ninye=$encrypt->encrypt($company_id);
					$ninye_ins=$encrypt->encrypt($insurer_id);
					 $token = form_token(); $_SESSION['remove_procedure_cover_token'] = "$token";  ?>
					<input type="hidden" name="remove_procedure_cover_token"  value="<?php echo $_SESSION['remove_procedure_cover_token']; ?>" />
					<input type="hidden" name="ninye"  value="<?php echo $ninye; ?>" />
					<input type="hidden" name="ninye_ins"  value="<?php echo $ninye_ins; ?>" />
					<?php
					//get procedures that have not yet been removed from cover
					$sql=$error=$s='';$placeholders=array();
					$sql="select name,id from procedures a  where a.id not in (select procedure_not_covered from procedures_not_covered where
						company_id=:company_id and insurer_id=:insurer_id) order by name";
					$placeholders[':company_id']=$company_id;
					$placeholders[':insurer_id']=$insurer_id;
					$error="Unable to select uncovered company procedures";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					echo "<select class=input_in_table_cell name=procedure_removed ><option></option>";
					foreach($s as $row){
						$procedure_name=html($row['name']);
						$procedure_id=$encrypt->encrypt(html($row['id']));
						echo "<option value='$procedure_id'>$procedure_name</option>";
					}
					echo "</select>";
				?>
			</div>
			<div class='grid-20'><input type=submit  value='Remove From Cover' /></form></div>
			<div class=clear></div>
			<br><br>
			<!--now show procedures already removed from cover-->
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select a.name,b.id from procedures a, procedures_not_covered b where b.company_id=:company_id and b.insurer_id=:insurer_id
					and a.id=b.procedure_not_covered order by name";
				$placeholders2[':insurer_id']=$insurer_id;
				$placeholders2[':company_id']=$company_id;
				$error2="Unable to get uncovered company procedures";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount()>0){
						$token = form_token(); $_SESSION['return_procedure_cover_token'] = "$token";  ?>
						<form action="" class='dialog_form' method="post" name="" id="">
						<input type="hidden" name="return_procedure_cover_token"  value="<?php echo $_SESSION['return_procedure_cover_token']; ?>" />
						<?php
						echo "<table class='normal_table'><caption>Procedures not covered for this corprate</caption><thead>
						<th class='uncovered_procedure_name'>PROCEDURE NAME</th>
						<th class='uncovered_procedure_select'>RETURN TO COVER</th>
						</thead><tbody>";
						foreach($s2 as $row2){
							$procedure_name=html($row2['name']);
							$val=$encrypt->encrypt(html($row2['id']));
							echo "<tr><td>$procedure_name</td><td><input type=checkbox name='return_procedure[]' value=$val /></td></tr>";
						}
						echo "<tr><td></td><td><input type=submit  value='Return Procedure To Insurance Cover' /></td></tr></table></form>";
					}
		echo "</div>";

}

//this will enable invoice admin comments in tdone
elseif(isset($_SESSION['token_aia']) and isset($_POST['token_aia']) and $_POST['token_aia']==$_SESSION['token_aia']
and userHasRole($pdo,20)){
	$ninye=$_POST['ninye'];
	$comment=$_POST['comment'];
	if(isset($_POST['inv_action'])){$action=$_POST['inv_action'];}
	$i=0;
	$n=count($ninye);
	try{
		$pdo->beginTransaction();
		while($i < $n){
			//check if chat is ending
			if(isset($action)){
				if($action[$i]=='end_chat'){
					$comment_id=$encrypt->decrypt("$ninye[$i]");
					$sql=$error=$s='';$placeholders=array();
					$sql="update invoice_admin_approval set status=1 where id=:communication_id";
					$error="Unable to end invoice admin comment";
					$placeholders[':communication_id']=$comment_id;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
				}
			}

			if($comment[$i]==''){$i++;continue;}
			elseif($comment[$i]!=''){
				$comment_id=$encrypt->decrypt("$ninye[$i]");
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into invoice_admin_approval_communication set communication_id=:communication_id,
					date_of_comment=now(),
					comment=:comment,
					user_id=:user_id";
				$error="Unable to add invoice admin comment";
				$placeholders[':comment']="$comment[$i]";
				$placeholders[':communication_id']=$comment_id;
				$placeholders[':user_id']=$_SESSION['id'];
				$s = insert_sql($sql, $placeholders, $error, $pdo);
			}
			$i++;
		}


		$tx_result = $pdo->commit();
		if($tx_result){$message="good#tdone_invoice_admin_approval#Changes saved";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#tdone_invoice_admin_approval#Unable to save changes ";
		}
		echo "$message";
}

//this will reload admin invoice approve after submit in tdone
elseif( isset($_POST['tdone_admin_invoice_approve1']) and $_POST['tdone_admin_invoice_approve1']=='yes' and userHasRole($pdo,20)){
	partially_approved_invoices($pdo,$_SESSION['pid'],$encrypt);
}

//this will add a patients precription
elseif(isset($_SESSION['token_presc_pta']) and isset($_POST['token_presc_pta']) and $_POST['token_presc_pta']==$_SESSION['token_presc_pta']
and userHasRole($pdo,20)){
	//check if token exists
	$sql=$error=$s='';$placeholders=array();
	$sql="select id from check_token where token_val=:token_val";
	$error="Unable to get token val";
	$placeholders['token_val']=$_POST['token_presc_pta'];
	$s = select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		//update count time
		$sql=$error=$s='';$placeholders=array();
		$sql="insert into check_token set token_val=:token_val, when_added=now()";
		$error="Unable to add 2 token val";
		$placeholders['token_val']=$_POST['token_presc_pta'];
		$s = insert_sql($sql, $placeholders, $error, $pdo);
		//echo "bad#token";

	}
	else{

	//unset($_SESSION['token_presc_pta']);
	$exit_flag=false;

	//check if the patient has been swapped
	if(!$exit_flag and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
	if(!$exit_flag){
		$drug=$_POST['drug'];
		$details=$_POST['details'];
		$presc_type=$_POST['presc_type'];
		$commission_receiver=$_POST['commissioners'];
		$price='';
		if(isset($_POST['price'])){$price=$_POST['price'];}
		$drug_quantity='';
		if(isset($_POST['drug_quantity'])){$drug_quantity=$_POST['drug_quantity'];}
		$pay_type='';
		$presc_id='';
		$n=count($drug);
		$i=0;
			//get list of drugs in array
			$sql=$error=$s='';$drug_array=$placeholders=array();
			$sql="select id from drugs";
			$error="Unable to get list of drugs";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			foreach($s as $row){$drug_array[]=html($row['id']);}
	//	echo "8602";
		//now insert record
		try{
			$pdo->beginTransaction();
				while($i < $n){
					if($drug[$i]==''){
						$i++;
						continue;
					}
					else{$drug[$i]=$encrypt->decrypt("$drug[$i]");}
					//check if drug is in array
					if(!$exit_flag and !in_array($drug[$i], $drug_array)){
						$var=html("$drug[$i]");
						$message2="bad#Unable to save prescription as someone tried to put $var as a drug. ";
						log_security($pdo,$message2);
						$exit_flag=true;
						$message="bad#patient_prescription#  Please select a prescription drug";
						break;
					}
					//check prescription type
					if($presc_type[$i]==''){
						$exit_flag=true;
						$message="bad#patient_prescription#  Please specify the prescription type for all drugs prescribed";
						break;
					}
					$presc_type[$i]=$encrypt->decrypt("$presc_type[$i]");
					if($presc_type[$i]!=2 and $presc_type[$i]!="presc"){
						$var=html("$$presc_type[$i]");
						$message2="bad#Unable to save prescription as someone tried to put $var as a precription type. ";
						log_security($pdo,$message2);
						$exit_flag=true;
						$message="bad#patient_prescription# Please select a prescription type for each drug prescribed";
						break;
					}
					//check if commisioner is set
					if($presc_type[$i]==2 and $commission_receiver[$i]==''){
						$exit_flag=true;
						$message="bad#patient_prescription# Sale prescriptions need to have a commission receiver";
						break;
					}
					//check if price needs to be set
					if($presc_type[$i]=="2" and $price[$i]==''){
						$exit_flag=true;
						$message="bad#patient_prescription# Please specify the selling price for each drug to be sold";
						break;
					}
					if($presc_type[$i]=="2" and $price[$i]!=''){
						$amount=str_replace(",", "", $price[$i]);
						$var=html("$price[$i]");
						if(!ctype_digit($amount)){
							//check if it has only 2 decimal places
							$data=explode('.',$amount);
							if ( count($data) != 2 ){

							$exit_flag=true;
							$message2="somebody tried to input $var as price for pt prescripton drug";
							log_security($pdo,$message2);
							$message="bad#patient_prescription#  Unable to save prescription as $var is not valid amount";
							break;
							}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){

							$exit_flag=true;
							$message2="somebody tried to input $var as price for pt prescripton drug";
							log_security($pdo,$message2);
							$message="bad#patient_prescription#  Unable to save prescription as $var is not valid amount";
							break;
							}
						}
					}

					//check if quantity is a valid number
					if($presc_type[$i]=="2" and $drug_quantity[$i]==''){
						$exit_flag=true;
						$message="bad#patient_prescription# Please specify the quantity for each drug to be sold";
						break;
					}
					if($presc_type[$i]=="2" and $drug_quantity[$i]!=''){
						$quantity_sold=str_replace(",", "", $drug_quantity[$i]);
						$quantity_sold=html("$drug_quantity[$i]");
						if(!ctype_digit($quantity_sold)){
							$message="bad#patient_prescription#  Unable to save prescription as $quantity_sold is not a valid quantity";
							break;
							/*//check if it has only 2 decimal places
							$data=explode('.',$quantity_sold);
							if ( count($data) != 2 ){

							$exit_flag=true;
							$message2="somebody tried to input $quantity_sold as quantity for pt prescripton drug";
							log_security($pdo,$message2);
							$message="bad#patient_prescription#  Unable to save prescription as $quantity_sold is not a valid quantity";
							break;
							}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){

							$exit_flag=true;
							$message2="somebody tried to input $quantity_sold as quantity for pt prescripton drug";
							log_security($pdo,$message2);
							$message="bad#patient_prescription#  Unable to save prescription as $quantity_sold is not a valid quantity";
							break;
							}*/
						}
						//check if quantiry is greater than zero
						if($quantity_sold == 0){
							$message="bad#patient_prescription#  Unable to save prescription as 0 is not a valid quantity";
							break;
						}
					}


					//check quantity is sufficient
					$sql=$error=$s='';$placeholders=array();
					$sql="select quantity,name,drug_type from drugs where id=:drug_id";
					$error="Unable to get drug quantity";
					$placeholders[':drug_id']=$drug[$i];
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$drug_in_stock=html($row['quantity']);
						$drug_in_stock_name=html($row['name']);
						if($row['drug_type']=='Tablet'){$unit='tablets';}
						elseif($row['drug_type']=='Syrup'){$unit='units';}
					}
					if($presc_type[$i]=="2" and $drug_in_stock < $quantity_sold){
						$message="bad#patient_prescription#  Unable to save prescription as $drug_in_stock_name only has $drug_in_stock $unit in stock but you want to sell $quantity_sold $units";
						$exit_flag=true;
						break;
					}

					if($presc_type[$i]=="presc"){$amount='';$pay_type=0;$quantity_sold=0;}
					if($presc_type[$i]=="2"){
						$pay_type=2;
						//update stock availlable
						$sql=$error=$s='';$placeholders=array();
						$sql="update drugs set quantity=(quantity  - :quantity_sold) where id=:drug_id";
						$error="Unable to update drug quantity";
						$placeholders[':drug_id']=$drug[$i];
						$placeholders[':quantity_sold']=$quantity_sold;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}
					//get prescription ID
					if($presc_id==''){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into prescription_id_generator set pid=:pid";
						$error="Unable to generate prescription id";
						$placeholders[':pid']=$_SESSION['pid'];
						$presc_id = 	get_insert_id($sql, $placeholders, $error, $pdo);
						$presc_num="P$presc_id-".date('m/y');
					}

					//now insert into prescrioptions table
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into prescriptions set when_added=now(), drug_id=:drug_id, details=:details,pay_type=:pay_type,
							cost=:cost, prescription_number=:prescription_number, created_by=:created_by, pid=:pid,
							prescription_id=:prescription_id, quantity=:quantity_sold, commission_receiver=:commission_receiver";
					$error="Unable to add patient prescription";
					if($presc_type[$i]==2){
						$placeholders[':commission_receiver']=$encrypt->decrypt("$commission_receiver[$i]");
					}
					else{$placeholders[':commission_receiver']=0;}
					$placeholders[':drug_id']=$drug[$i];
					$placeholders[':details']="$details[$i]";
					$placeholders[':pay_type']=$pay_type;
					$placeholders[':cost']=$amount;
					$placeholders[':prescription_number']="$presc_num";
					$placeholders[':created_by']=$_SESSION['id'];
					$placeholders[':pid']=$_SESSION['pid'];
					$placeholders[':prescription_id']=$presc_id;
					$placeholders[':quantity_sold']=$quantity_sold;
					$s = insert_sql($sql, $placeholders, $error, $pdo);


					$i++;
				}

			//if($s){$message="good#patient_prescription# Prescription saved ";}
			//elseif(!$s){$message="bad# Unable to save patient details ";}

			if(!$exit_flag){$tx_result = $pdo->commit();
				if($tx_result){
					$message="good#patient_prescription# Prescription saved ";
										//insert token into table and check if unique
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into check_token set token_val=:token_val, when_added=now()";
					$error="Unable to add token val";
					$placeholders['token_val']=$_POST['token_presc_pta'];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);
					$_SESSION['token_presc_pta']='';
					unset($_SESSION['token_presc_pta']);
				}
			}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		//$message="bad#   Unable to save patient details  ";
		}
	}
				$data=explode('#',"$message");
				if($data[0]=='good'){$_SESSION['result_class']='success_response';
							$_SESSION['result_message']="$data[2]";
		}
		echo "$message";
	}
}

//this will insert or update patient contacts
//insert or update record
elseif(isset($_SESSION['token_a1_patinet']) and isset($_POST['token_a1_patinet']) and $_POST['token_a1_patinet']==$_SESSION['token_a1_patinet']){
	//$_SESSION['token_a1_patinet']='';
	//perform verifications
	$exit_flag=false;
	$photo_path=$other_relationship=$ptype=$company_covered=$pnum=$year=$em_relationship='';


	//check if the patient has been swapped
	if(!$exit_flag and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
		$result = check_if_swapped($pdo,'pid',$_SESSION['pid']);
		if($result!='good'){
			$exit_flag=true;
			$message="bad#$result and cannot be edited.";
		}
	}
//	echo "38";
		//upload photo
	//$upload=upload_photo($_FILES['image_upload']);
	//echo "$_POST[upload_status]";exit;
	if(isset($_FILES["image_upload"])){
		//echo "a";
		if($_FILES["image_upload"]["name"]!=''){
			$upload=upload_photo($_FILES['image_upload']);
			$_POST['upload_status']= "$upload";
		}
		elseif($_FILES["image_upload"]["name"]==''){
			$upload="GOODsplitter$_SESSION[photo_path]";
			$_POST['upload_status']= "$upload";
		}
		echo "--$_POST[upload_status]--";exit;
		$data=explode("splitter","$_POST[upload_status]");
		if($data[0]=="ERROR"){
			$message="bad#".html("$data[1]");
			$exit_flag=true;
		}

	}

	//check patient name
	if(!$exit_flag and $_POST['first_name']=='' and $_POST['middle_name']=='' and $_POST['last_name']==''){

			$exit_flag=true;
				$message="bad#Unable to save details as no patient name is specified. ";

	}

	//check gender
	if(!$exit_flag and $_POST['gender']!='MALE' and $_POST['gender']!='FEMALE'  ){

		$exit_flag=true;
		$gender=html($_POST['gender']);
		$message="sombody tried to input $gender into patient details";
		log_security($pdo,$message);
		$message="bad#Unable to save details as gender is not specified. ";
	}

	//check card issued
	if(!$exit_flag and $_POST['card_issued']!='NO' and $_POST['card_issued']!='YES'  ){

		$exit_flag=true;
		$card_issed=html($_POST['card_issued']);
		$message="sombody tried to input $card_issed into patient details";
		log_security($pdo,$message);
		$message="bad#Unable to save details as card issued details not specified. ";
	}

	//check patient type
	if(!$exit_flag and $_POST['ptype']==''){
			$exit_flag=true;
			$message="bad#Unable to save details as patient type is not specified. ";
	}


	if(!$exit_flag and $_POST['ptype']!=''){
		$ptype=html($encrypt->decrypt($_POST['ptype']));//echo "<br>$ptype is ";exit;
		if(!$exit_flag and !in_array($ptype, $_SESSION['patient_type_array'])){

			$exit_flag=true;
			$message="somebody tried to input $ptype as a patient type into patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as patient type is not specified. ";
		}

		//check if the type has companies under it
		$company_covered_array=array();
		$sql=$error=$s='';$placeholders=array();
		$sql="select  id  from covered_company where insurer_id=:insured";
		$placeholders[':insured']=$ptype;
		$error="Error: Unable to get covered company name for patient type";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row ){$company_covered_array[]=html($row['id']);}
		//print_r($company_covered_array);
	}

	//check covered compnaycovered_company
	$company_covered='';
	if(!$exit_flag and isset($_POST['covered_company'])){
		$company_covered=html($encrypt->decrypt($_POST['covered_company']));
		if(!$exit_flag and isset($_POST['covered_company']) and $_POST['covered_company']!=''){

			if(!in_array($company_covered,$_SESSION['covered_company_array'])){

				$exit_flag=true;
				$message="somebody tried to input $company_covered as a covered compnay into patient details";
				log_security($pdo,$message);
				$message="bad#Unable to save details as covered company  is not correctly specified.";
			}
		}
	}

	//check if company covered is corrwectly selected
	if(!$exit_flag and count($company_covered_array) > 0 and !in_array($company_covered,$company_covered_array)){
		$exit_flag=true;
		$message="bad#Unable to save details as no appropriate covered company  is set for the patient type. ";

	}

	/* //check email format
	if(!$exit_flag and $_POST['email_address']=='' and $_POST['email_address_2']==''){

			$exit_flag=true;
				$message="bad#Unable to save details as no email $email_address  is specified. ";

	} */

	//check phone
	if(!$exit_flag and $_POST['mobile_no']=='' and $_POST['tel_bix']==''){

			$exit_flag=true;
				$message="bad#Unable to save details as no phone contacts are specified ";

	}

	/* if(!$exit_flag and isset($_POST['email_address']) and $_POST['email_address']!=''){
		$email_address=html($_POST['email_address']);

		if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {

			$exit_flag=true;
			$message="somebody tried to input $email_address as a email address for a patient in  patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as the email $email_address  is not correctly specified. ";
		}
	}

	//check email format for email address 2
	if(!$exit_flag and isset($_POST['email_address_2']) and $_POST['email_address_2']!=''){
		$email_address_2=html($_POST['email_address_2']);

		if(!filter_var($email_address_2, FILTER_VALIDATE_EMAIL)) {

			$exit_flag=true;
			$message="somebody tried to input $email_address_2 as a email address for a patient in  patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as the email $email_address_2  is not correctly specified. ";
		}
	} */


	//check city
	/* $city=''; */
	if(!$exit_flag and $_POST['city']=='' and $_POST['city']==''){

		$exit_flag=true;
			$message="bad#Unable to save details as city is not specified ";

}
	/* if(!$exit_flag and isset($_POST['city']) and $_POST['city']!=''){
		$city=html($encrypt->decrypt($_POST['city']));

		if(!in_array($city,$_SESSION['cities_array'])) {

			$exit_flag=true;
			$message="somebody tried to input $city as a city for a patient in  patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as city  is not correctly specified. ";
		}
	} */


	//check date of birth
	if(!$exit_flag and isset($_POST['dob']) and $_POST['dob']!='')	{
		$date='';
		$date=explode('-',$_POST['dob']);
		if(!checkdate( $date[1],$date[2],$date[0] )){
		$dob=html($_POST['dob']);

		$exit_flag=true;
		$message="somebody tried to input $dob as date of birth for a patient in  patient details";
		log_security($pdo,$message);
		$message="bad#Unable to save details as date of birth $dob is not in the correct format";
		}
	}

	//check if weight is a proper number
	/* $weight=html($_POST['weight']);
	if(!$exit_flag and isset($_POST['weight']) and $_POST['weight']!=''){

		if(!ctype_digit($_POST['weight'])){
			//check if it has only 2 decimal places
			$data=explode('.',$_POST['weight']);
			if ( count($data) != 2 ){

			$exit_flag=true;
			$message="somebody tried to input $weight as weight for a patient in  patient details";
			log_security($pdo,$message);
			$message="bad# Unable to save details as $weight is not a valid weight number";
			}
			elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){

			$exit_flag=true;
			$message="somebody tried to input $weight as weight for a patient in  patient details";
			log_security($pdo,$message);
			$message="bad# Unable to save details as $weight is not a valid weight number";
			}
		}
	} */

	//check relationships for emergency
	$em_relationship='';
	if(!$exit_flag and isset($_POST['em_relationship']) and $_POST['em_relationship']!=''){
		$em_relationship=html($encrypt->decrypt($_POST['em_relationship']));

		if(!in_array($em_relationship,$_SESSION['relationship_array'])){

			$exit_flag=true;
			$message="somebody tried to input $em_relationship as a patient relationship into patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as patient relationship  is not correctly specified. ";
		}
	}

	//check relationships for on behalf form filling
	$other_relationship='';
	if(!$exit_flag and isset($_POST['other_relationship']) and $_POST['other_relationship']!=''){
		$other_relationship=html($encrypt->decrypt($_POST['other_relationship']));

		if(!in_array($other_relationship,$_SESSION['relationship_array'])){

			$exit_flag=true;
			$message="somebody tried to input $other_relationship as a on behalf relationship into patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as relationship for form filler  is not correctly specified. ";
		}
	}

	//check referres
	/* $referee='';
	if(!$exit_flag and isset($_POST['referee']) and $_POST['referee']!=''){
		$referee=html($encrypt->decrypt($_POST['referee']));

		if(!in_array($referee,$_SESSION['referee_array'])){

			$exit_flag=true;
			$message="somebody tried to input $referee as a patient referrrer into patient details";
			log_security($pdo,$message);
			$message="bad#Unable to save details as patient referrer  is not correctly specified. ";
		}
	} */

	//check emcontat
	if(!$exit_flag and $_POST['em_contact']=='' and $_POST['em_contact']==''){

			$exit_flag=true;
				$message="bad#Unable to save details as no emergency contact is specified ";

	}


	//check em reltaionship
	if(!$exit_flag and $_POST['em_relationship']=='' and $_POST['em_relationship']==''){

			$exit_flag=true;
				$message="bad#Unable to save details as emergency contact relationship is not specified ";

	}

	//check em phone
	if(!$exit_flag and $_POST['em_phone']=='' and $_POST['em_phone']==''){

			$exit_flag=true;
				$message="bad#Unable to save details as emergency contact phone is not specified ";

	}

	//check if patient type details have changed
	if(!$exit_flag){
		//check if type has changed
		if($_POST['ptype']!=''){$ptype_id=$encrypt->decrypt($_POST['ptype']);}
		else{$ptype_id='';}

		if(isset($_POST['covered_company']) and $_POST['covered_company']!=''){$covered_company_id=$encrypt->decrypt($_POST['covered_company']);}
		else{$covered_company_id='';}

		if(!isset($_POST['admin_p']) and
			($_SESSION['type'] !=  $ptype_id or $_SESSION['company_covered']!= $covered_company_id)

			){
				$result=get_pt_invoices($pdo, $_SESSION['pid']);
				$data=explode('###',"$result");

			if("$data[2]"!='no_invoices'){
				$exit_flag=true;
				$message="bad#insurer_changed#Patient Insurance details have changed ";


				$string='';
				$admin_pass_input="<div class='grid-20 label'>Administrator Password</div>
					<div class=grid-20><input type=password name=admin_pass /></div>
					<div class='grid-10 '><input class='change_pt_details button_style' type=button value=Submit /></div>
							<div class=clear></div><br>";
				if("$data[2]"=='Paid' or "$data[2]"=='Partially Paid'){
					$string="<div class='error_response  grid-100'>You are trying to change the patient type details of a patient with <span class=make_bold>PAID</span> invoices.
						In this scenario a <span class=make_bold>PATIENT  SWAP IS RECOMMENDED</span> instead of directly changing the patient type details.
						This will ensure that revenue received is not allocated to the wrong insurer.</div>";
					$string= "$string";
				}
				elseif("$data[2]"=='Dispatched'){
					$string= "<div class='error_response  grid-100'>You are trying to change the patient type details yet the patient has atleast one <span class=make_bold>DISPATCHED</span> invoice.
						In this scenario a <span class=make_bold>PATIENT  SWAP IS RECOMMENDED</span> instead of directly changing the patient type details.
						This will ensure that the invoice remains payable by the correct insurer</div>";
					$string= "$string";
				}
				elseif("$data[2]"=='Unpaid'){
					//check if the new company is insured
					if($covered_company_id==''){
						$string= "<div class='grid-100 error_response'>You are trying to change the patient type details for a patient with atleast one <span class=make_bold>UNPAID</span> invoice.
						<br>You have however not specified the insured company which will inherit the unpaid invoices.
						<br>In this scenario you can either delete the patient's invoices and then change his/her patient type details or
						place the patient under an insured company that will inherit the unpaid invoice</div>";
						$string="$string";
					}
					elseif($covered_company_id!=''){
						$sql=$error1=$s='';$placeholders=array();
						$sql="select insured ,name from covered_company where id=:id";
						$error="Unable to get new company name";
						$placeholders['id']=$covered_company_id;
						$s = select_sql($sql, $placeholders, $error, $pdo);
						foreach($s as $row){
							$new_company_name=html($row['name']);
							$insured=html($row['insured']);
						}
						if($insured=='NO'){
							$string= "<div class='grid-100 error_response'>You are trying to change the patient type details for a patient with atleast one <span class=make_bold>UNPAID</span> invoice.
							<br>You have however specified a company ($new_company_name) that is not insured.
							<br>You need to specify an insured company which will inherit the unpaid invoices.
							<br>In this scenario you can either delete the patient's invoices and then change his/her patient type details or
							place the patient under an insured company that will inherit the unpaid invoice</div>";
							$string="$string";
						}
						elseif($insured=='YES'){
							$string= "<div class='grid-100 error_response'>You are trying to change the patient type details yet the patient has atleast one <span class=make_bold>UNPAID</span> invoice.
							<br>In this scenario a <span class=make_bold>PATIENT  SWAP IS RECOMMENDED</span> instead of directly changing the patient type details.
							<br>If you understand the impact of your actions and still want to proceed despite the recommendation then provide the administrator password</div>";
							$string="$string $admin_pass_input";
						}

					}

				}


				$message= "bad#insurer_changed#$string $data[1] ";


			}
		}
	}

	//check if admin password is set and if it is correct
	if(!$exit_flag and  isset($_POST['admin_p'])){
		//check if password is empty
		if($_POST['admin_p']==''){
			$message= "bad#Wrong administrator password provided";
			$exit_flag=true;
		}

		//now check if it matches a user with administrator privilege
		elseif($_POST['admin_p']!=''){
			$admin_p=hash_hmac('sha1', $_POST['admin_p'], $salt);
			$sql=$error=$s='';$placeholders=array();
			$sql="select user_name from users a, privileges b where a.id=b.user_id and b.menu_id=109 and
			a.password=:password limit 1";
			$error="Unable to get administartor user";
			$placeholders['password']="$admin_p";
			$s = select_sql($sql, $placeholders, $error, $pdo);

			if($s->rowCount() == 0){ //if privilege not found check in roles
				$sql=$error=$s='';$placeholders=array();
				$sql="SELECT user_name FROM users d, user_roles c, role_privileges b WHERE d.id = c.user_id
				AND c.role_id = b.role_id aND b.menu_id =109 AND d.password = :password limit 1";
				$error="Unable to get administartor user";
				$placeholders['password']="$admin_p";
				$s = select_sql($sql, $placeholders, $error, $pdo);
			}
			if($s->rowCount() == 0){
				$message= "bad#wrong_admin_pass#Wrong administrator password provided";
				$exit_flag=true;
			}
			else{
				foreach($s as $row){$admin_user_name=html($row['user_name']);}
			}
		}

	}

	//now insert
	if(!$exit_flag and (!isset($_SESSION['pid']) or $_SESSION['pid']=='')){
		try{
		//echo "insert";exit;
			$pdo->beginTransaction();
			//get photo path if set
			if(isset($_POST['upload_status']) and $_POST['upload_status']!=''){
				$data=explode("splitter","$_POST[upload_status]");
				$photo_path="$data[1]";
			}

			//get patient ID
			$year=date('y');
			$sql=$error=$s='';$placeholders=array();
			$sql="select max(pnum) from pnum_generator where year=:year";
			//$sql="insert into pnum_generator where year=:year";
			$error="Unable to get max pnum for year $year";
			$placeholders[':year']="$year";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){foreach($s as $row){$pnum=$row[0] + 1;}}
			else{$pnum=1;}
			$pid="$pnum/$year";

			//insert that pid into pnum generator
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into pnum_generator set pnum=:pnum,  year=:year";
			$error="Unable to insert max pnum for year $year";
			$placeholders[':year']="$year";
			$placeholders[':pnum']=$pnum;
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			//now insert into patient_details_a
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_details_a set last_name=:last_name, middle_name=:middle_name, first_name=:first_name, mobile_phone=:mobile_phone,
					biz_phone=:biz_phone, type=:type, patient_number=:patient_number, member_no=:member_no, company_covered=:company_covered, pnum=:pnum,
					year=:year, card_issued=:card_issued, added_by=:added_by";
			$error="Unable to add patient new patient";
			$placeholders[':last_name']=$_POST['last_name'];
			$placeholders[':middle_name']=$_POST['middle_name'];
			$placeholders[':first_name']=$_POST['first_name'];
			$placeholders[':mobile_phone']=$_POST['mobile_no'];
			$placeholders[':biz_phone']=$_POST['tel_bix'];
			$placeholders[':added_by']=$_SESSION['id'];
			$placeholders[':type']=$ptype;
			$placeholders[':patient_number']="$pid";
			$placeholders[':member_no']=$_POST['mem_no'];
			$placeholders[':company_covered']=$company_covered;
			$placeholders[':pnum']=$pnum;
			$placeholders[':year']="$year";
			$placeholders[':card_issued']=$_POST['card_issued'];
			$id = get_insert_id($sql, $placeholders, $error, $pdo);

			//now insert into patient_details_b
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into patient_details_b set id_number=:id_number, city=:city, occupation=:occupation,
					em_relationship=:em_relationship, em_phone=:em_phone, behalf_name=:behalf_name, behalf_relationship=:behalf_relationship, when_added=:when_added,
					gender=:gender,	photo_path=:photo_path, pid=:pid, dob=:dob,  em_contact=:em_contact";
			$error="Unable to add patient new patient";
			$placeholders[':id_number']=$_POST['id_no'];
			$placeholders[':city']=$_POST['city'];
			$placeholders[':dob']=$_POST['dob'];
			$placeholders[':em_contact']=$_POST['em_contact'];
			$placeholders[':occupation']=$_POST['occupation'];
			$placeholders[':em_relationship']=$em_relationship;
			$placeholders[':em_phone']=$_POST['em_phone'];
			$placeholders[':behalf_name']=$_POST['other_name'];
			$placeholders[':behalf_relationship']=$other_relationship;
			$placeholders[':when_added']=date('Y-m-d');
			$placeholders[':gender']=$_POST['gender'];
			$placeholders[':photo_path']="$photo_path";
			$placeholders[':pid']=$id;

			$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			if($s){$message="good#hii_ni_pt_contact# Patient $pid has been created ";get_patient($pdo,"pid","$id");}
			elseif(!$s){$message="bad# Unable to save patient details ";}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){
				//delete photo if set
				if($photo_path!=''){unlink("$path_photo");}
				$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#   Unable to save patient details  ";
		}
	}

	//now update
	elseif(!$exit_flag and (isset($_SESSION['pid']) and $_SESSION['pid']!='')){
		try{

			$pdo->beginTransaction();
				//echo "	Type--$_SESSION[type]--$ptype_id xxx company--$_SESSION[company_covered]--$covered_company_id xxx";
			//get photo path if set
			$photo_path='';
			if(isset($_POST['upload_status']) and $_POST['upload_status']!=''){
				$data=explode("splitter","$_POST[upload_status]");
				$photo_path="$data[1]";
			}
			//remove from family group if need be
			if(isset($_POST['del_family_mem'])){

				$family_id=$_POST['del_family_mem'];
				$n2=count($family_id);
				$i2=0;
				while($i2 < $n2){
					$var=$encrypt->decrypt("$family_id[$i2]");
					$sql=$error=$s='';$placeholders=array();
					$sql="update patient_details_a set family_id=null, family_title=null where pid=:pid";
					$error="Unable to remove pt from family group";
					$placeholders[':pid']=$var;
					$s = insert_sql($sql, $placeholders, $error, $pdo);
					$i2++;
				}
			}

			//now update into patient_details_a
			$sql=$error=$s='';$placeholders=array();
			$sql="update patient_details_a set last_name=:last_name, middle_name=:middle_name, first_name=:first_name, mobile_phone=:mobile_phone,
					biz_phone=:biz_phone, type=:type,  member_no=:member_no, company_covered=:company_covered,
					 card_issued=:card_issued where pid=:pid";
			$error="Unable to update patient details";
			$placeholders[':last_name']=$_POST['last_name'];
			$placeholders[':middle_name']=$_POST['middle_name'];
			$placeholders[':first_name']=$_POST['first_name'];
			$placeholders[':mobile_phone']=$_POST['mobile_no'];
			$placeholders[':biz_phone']=$_POST['tel_bix'];
			$placeholders[':type']=$ptype;
			$placeholders[':pid']=$_SESSION['pid'];
			$placeholders[':member_no']=$_POST['mem_no'];
			$placeholders[':company_covered']=$company_covered;
			$placeholders[':card_issued']=$_POST['card_issued'];
			$s = insert_sql($sql, $placeholders, $error, $pdo);

			//now update patient_details_b
			$sql=$error=$s='';$placeholders=array();
			$sql="update patient_details_b set id_number=:id_number, address=:address, city=:city, occupation=:occupation,
					em_relationship=:em_relationship, em_phone=:em_phone, behalf_name=:behalf_name, behalf_relationship=:behalf_relationship,
					gender=:gender,	photo_path=:photo_path, dob=:dob, em_contact=:em_contact where pid=:pid";
			$error="Unable to update patient details";
			$placeholders[':id_number']=$_POST['id_no'];
			$placeholders[':city']=$_POST['city'];
			$placeholders[':weight']=$weight;
			$placeholders[':dob']=$_POST['dob'];
			$placeholders[':em_contact']=$_POST['em_contact'];
			$placeholders[':occupation']=$_POST['occupation'];
			$placeholders[':em_relationship']=$em_relationship;
			$placeholders[':em_phone']=$_POST['em_phone'];
			$placeholders[':behalf_name']=$_POST['other_name'];
			$placeholders[':behalf_relationship']=$other_relationship;
			$placeholders[':gender']=$_POST['gender'];
			$placeholders[':photo_path']="$photo_path";
			$placeholders[':pid']=$_SESSION['pid'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);

			//this will hadnle patient type changes and record them
			if($_SESSION['type'] !=  $ptype_id )
			{
				//get type names
				$old_type_name='';
				$sql=$error1=$s='';$placeholders=array();
				$sql="select name from insurance_company where id=:id";
				$error="Unable to get new type name";
				$placeholders['id']=$ptype_id;
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){$old_type_name=html($row['name']);}

				$sql=$error=$s='';$placeholders=array();
				$sql="insert into changed_patient_details set
						pid=:pid,
						field_changed='Patient Type',
						before_value=:before_value,
						after_value=:after_value,
						changer=:changer,
						when_changed=now() ,
						approved=0";
				$error="Unable to update patient details";
				$placeholders[':pid']=$_SESSION['pid'];
				$placeholders[':before_value']=$_SESSION['type_name'];
				$placeholders[':after_value']="$old_type_name";
				$placeholders[':changer']=$_SESSION['id'];
				$s = insert_sql($sql, $placeholders, $error, $pdo);
			}

			//this will hadnle covered company changes and record them
			if($_SESSION['company_covered']!= $covered_company_id )
			{
				if(!isset($new_company_name)){//get type names
					$new_company_name='';
					$sql=$error1=$s='';$placeholders=array();
					$sql="select name from covered_company where id=:id";
					$error="Unable to get new company name";
					$placeholders['id']=$covered_company_id;
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$new_company_name=html($row['name']);}
				}
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into changed_patient_details set
						pid=:pid,
						field_changed='Company Covered',
						before_value=:before_value,
						after_value=:after_value,
						changer=:changer,
						when_changed=now() ,
						approved=0";
				$error="Unable to update patient details";
				$placeholders[':pid']=$_SESSION['pid'];
				$placeholders[':before_value']=$_SESSION['company_covered_name'];
				$placeholders[':after_value']="$new_company_name";
				$placeholders[':changer']=$_SESSION['id'];
				$s = insert_sql($sql, $placeholders, $error, $pdo);
			}

			if($s){$message="good#hii_ni_pt_contact#Patient details saved. ";get_patient($pdo,"pid","$_SESSION[pid]");}
			elseif(!$s){$message="bad# Unable to save Patient details ";}

			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){
				//delete photo if set
				if($photo_path!=''){unlink("$path_photo");}
				$tx_result=false;$pdo->rollBack();}
			//if($tx_result){$success_message=" Patient details saved ";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#   Unable to save patient details  ";
		}
	}
	$data=explode('#',"$message");
	if("$data[0]"=='good'){
		$_SESSION['result_class']='success_response';
		$_SESSION['result_message']="$data[2]";
	}
	echo "$message";
}