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
if(!userIsLoggedIn() or !userHasRole($pdo,19)){
		   ?>
<script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
		<?php 
		exit;}
$_SESSION['tplan_id']='';		
echo "<div class='grid_12 page_heading'>TREATMENT PLAN</div>";
if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
	$_SESSION['result_class']!=''){
		if($_SESSION['result_class']=='success_response'){
			echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';	
		}
}
else{echo "<div class='feedback hide_element'></div>";}
//print_r($_POST);
//echo "<br>$_SESSION[token_f_patient]  and $_SESSION[pid]";

//this is for doing a patient search
/*
if(isset($_SESSION['token_f_patient']) and 	isset($_POST['token_f_patient']) and $_POST['token_f_patient']==$_SESSION['token_f_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	$_SESSION['token_f_patient']='';
	
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
			if($s){$success_message=" Patient details saved. ";get_patient_completion($pdo,'pid',$_SESSION['pid']);}
			elseif(!$s){$error_message=" Unable to save Patient details ";}			
			
			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$error_message="   Unable to save patient details  ";
		}	
		
}	*/

//this will unset the patient contact session variables if not pid is currenlty set
if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){}
if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){get_xray_types($pdo);}
?>
<div class='grid-container completion_form'>
	
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="#treatment-plan";
			 include '../../dental_includes/search_for_patient.php';
			if(isset($_SESSION['pid']) and $_SESSION['pid']!='' ){
				show_patient_balance($pdo,$_SESSION['pid'],$encrypt);
				
			}
			if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_examination();exit;}

		 
	?>
		<form action="#treatment-plan" method="POST"  name="" id="" class="patient_form2">
		
		
		<div class=grid-50>
		<fieldset><legend>Diagnosis</legend>
		<!-- patient complaint -->
			<?php
			$i=1;
			while($i <= 1){ ?>
				<div class='grid-100  remove-inside-padding'>
						<div class=grid-100><label for="" class="label">Patient complaint</label></div>
						<div class='grid-100 highlight_on_hover'><textarea   rows="" name="complaint[]"></textarea></div>	
				</div>			
			<?php 
				$i++;	
			} ?>
		
		
		
		<!-- diagnosis -->
		
		
				<?php $token = form_token(); $_SESSION['token_h_patient'] = "$token";  ?>
				<input type="hidden" name="token_h_patient"  value="<?php echo $_SESSION['token_h_patient']; ?>" />
			<?php
			$i=1;
			while($i <= 1){ ?>
				<div class='grid-100  remove-inside-padding'>
						<div class=grid-100><label for="" class="label">Diagnosis</label></div>
						<div class='grid-100 highlight_on_hover'><textarea   rows="" name="diagnosis[]"></textarea></div>	
				</div>			
			<?php 
				$i++;	
			} ?>
		</fieldset>
		</div>
		
		<div class=grid-50>
			
				<div class=grid-100><label for="" class="label">Patient Tag</label></div>
				<div class='grid-100 highlight_on_hover'><textarea   rows="" name="pt_tag"><?php echo $_SESSION['tag']; ?></textarea></div>	
			
		</div>
		<div class=clear></div>
		
		<fieldset><legend>Treatment Procedure</legend>
	<?php
			//get any xrays that have not been added to a tplan
			$sql=$error=$s='';$placeholders=array();
			$sql="select date_taken,b.name,a.id,a.cost,
					case pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points'	end as pay_type	, teeth
					from xray_holder a, procedures b where pid=:pid and a.xrays_done=b.id";
			$error="Unable to get xrays done that have not tplan yet";
			$placeholders['pid']=$_SESSION['pid'];
			$s2 = select_sql($sql, $placeholders, $error, $pdo);		
			if($s2->rowCount()>0){
				$x_ins=$x_self=$x_point=$x_tot='';
				echo "<div id=unbilled_xrays_div>";
					echo "<table class='unbilled_xrays normal_table'><caption>Unbilled X-rays</caption>
					<tr><th class=unbilled_date>Date of X-ray</th>
					<th class=unbilled_procedure>X-rays Done</th><th class=unbilled_pay_type>Payment Method</th>
					<th class=unbilled_cost>Cost</th><th class=unbilled_select>Add to Treatment Plan</th></tr>
					<tr>";
					foreach($s2 as $row){
						$xrays_done=html("$row[name] $row[teeth]");
						//$xrays_done=html("$xrays_done");
						$date=html($row['date_taken']);
						$cost=html(number_format($row['cost'],2));
						$pay_type=html($row['pay_type']);
						$id=$encrypt->encrypt($row['id']);
						echo "
							
							<td>$date</td>
							<td>$xrays_done</td>
							<td>$pay_type</td>
							<td>$cost</td>
							<td><input checked class=add_xray_to_tplan type=checkbox name=xrays[] value=$id /></td>
						</tr>";
						if($pay_type=='Insurance'){$x_ins = $x_ins + html($row['cost']);}
						elseif($pay_type=='Self'){$x_self = $x_self + html($row['cost']);}
						elseif($pay_type=='Points'){$x_point = $x_point + html($row['cost']);}
						
					}
					$x_tot =$x_ins + $x_self;
					echo "</table>";
				echo "</div>";
			}
			?>	
			<div class='grid-50'><label for="" class="label">Treatment Procedure </label></div>
			<div class='grid-25'><label for="" class="label">Procedure Details</label></div>
			<div class='grid-15'><label for="" class="label">Payment Method</label></div>
			<div class='grid-10'><label for="" class="label">Cost<br>Kshs/Points</label></div>
				
			<div class='grid-100 grid-container procedure_container no_padding'>
			<?php
					//check if this patient type is insured or not
					$insured='NO';
					$sql=$error=$s='';$placeholders=array();
					$sql="select insured from covered_company where id=:covered_company";
					$error="Unable to check if the company is insured";
					$placeholders['covered_company']=$_SESSION['company_covered'];
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$insured=html($row['insured']);}
					
			$i=1;
			show_teeth();
			//echo "<br>11 is ".$_SESSION['11'];
			while($i <= 5){ 
				//show procedures
				echo "<div class='grid-100 tplan_procedures highlight_on_hover1 '>";//was hover before
					echo "<div class='grid-5 procedure_count'>$i<input type=hidden name=nisiana[] /></div>";
					echo "<div class='grid-45 grid-parent'>";
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name,id,all_teeth from procedures where id!=2 and id!=8 and id!=59 order by name";
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
					if(userHasRole($pdo,113)){
						echo "<div class=aliasing_treatment><input class='button_style get_alias_button' type=button value='Get Aliases'/></div><div class=aliasing_treatment2></div>";
						
					}
					echo "</div>";
					echo "<div class='grid-25'><textarea  class=tplan_details disabled rows='' name=details$i ></textarea></div>";
					echo "<div class='grid-15'>";
						$invoice_pay=$encrypt->encrypt("1");
						$cash_pay=$encrypt->encrypt("2");
						$points_pay=$encrypt->encrypt("3");
						echo "<select disabled name=pay_method$i class='input_in_table_cell pay_method' ><option></option>";
								
						if($insured == 'YES' and !$_SESSION['ins_suspend']){echo "<option value='$invoice_pay'>Insurance</option>";}		
						echo "<option value='$cash_pay'>Self</option>						
								<option value='$points_pay'>Points</option>";
						echo "</select>";
					echo "</div>";
					echo "<div class='grid-10 tplan_cost_calculation'><input disabled class=tplan_cost type=text name=cost$i />";
					if(userHasRole($pdo,113)){
						$alias_val=$encrypt->encrypt("ta$i");
						echo "Aliased <input disabled class=tplan_alias type=checkbox name=tplan_alias$i value=$alias_val />";}
					echo "</div>";
					
				echo "</div>";	
				echo "<div class='grid-100 grey_bottom_border'></div><div class=clear></div>		";
				$i++;	
			} ?>
		</div>	
		<div class='grid-100 add_procedure_here'><input type=button class="add_new_procedure put_right button_style" value="Add Procedure" /></div>	
		<div class='grid-20 prefix-70 '><label for="" class="label">Insurance total cost(Kes): </label></div>
			<div class='grid-10' ><span id=treatment_plan_insurance_total class=put_right>
			<?php 
				if(isset($x_ins) and $x_ins  !='') {echo number_format($x_ins,2);}
			?></span></div>	
		<div class='grid-20 prefix-70 '><label for="" class="label">Self total cost(Kes): </label></div>
			<div class='grid-10' ><span id=treatment_plan_self_total class=put_right>
			<?php 
				if(isset($x_self) and $x_self  !='') {echo number_format($x_self,2);}
			?></span></div>	
		<div class='grid-20 prefix-70 '><label for="" class="label">Total cost(Kes): </label></div>
			<div class='grid-10' ><span id=treatment_plan_sum class=put_right>
			<?php 
				if(isset($x_tot) and $x_tot  !='') {echo number_format($x_tot,2);}
			?></span></div>		
		<div class='grid-20 prefix-70  '><label for="" class="label">Points total cost: </label></div>
			<div class='grid-10' ><span id=treatment_plan_points_total class=put_right>
			<?php 
				if(isset($x_point) and $x_point  !='') {echo number_format($x_point,2);}
			?></span></div>		
		
		</fieldset>
					<div class='grid-100'>
				<?php
					if($swapped==''){
						echo "<div class='no_padding put_right'>";
						show_submit($pdo,'','');
						echo "</div>"; 
					}
					elseif($swapped!=''){echo "<div class='error_response'>$swapped</div>";}
				?>
			</div>
		</form>				
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>