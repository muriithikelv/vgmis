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
if(!userIsLoggedIn() or !userHasRole($pdo,18)){
	   ?>
<script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
		<?php
		exit;}
$_SESSION['tplan_id']='';		
echo "<div class='grid_12 page_heading'>EXAMINATION</div>";
//this will unset the patient examination session variables if not pid is currenlty set

	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']=='success_response'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}

		}
if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){clear_patient_examination();get_patient_examination($pdo,'pid',$_SESSION['pid']);get_xray_types($pdo);}
?>
<div class=grid-container>
	<!--<div class='grid-100 hide_first hide_element'>-->
	<div class='grid-100 '>
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="#examination";
			 include '../../dental_includes/search_for_patient.php';
			if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){show_patient_balance($pdo,$_SESSION['pid'],$encrypt);}
			if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_examination();exit;}
	?>
<fieldset><legend>On Examination</legend>	
	<form action="" method="POST"  name="" id="" enctype="multipart/form-data" class='tab_form patient_form2'>

	

			<!--first name-->
			<div class='grid-45 suffix-5 grid-parent'>
				<?php $token = form_token(); $_SESSION['token_g_patinet'] = "$token";  ?>
				<input type="hidden" name="token_g_patinet"  value="<?php echo $_SESSION['token_g_patinet']; ?>" />
				<div class='prefix-80 grid-10'><label for="" class="label">YES</label></div>
				<div class='grid-10'><label for="" class="label">NO</label></div>	
			</div>	
			<div class='grid-45 prefix-5 grid-parent'>
				<div class='prefix-80 grid-10'><label for="" class="label">YES</label></div>
				<div class='grid-10'><label for="" class="label">NO</label></div>
			</div>	

			<div class=clear></div>
			<div class='grid-100 grey_bottom_border'></div>		
			<!--row 1-->
			<div class='grid-45 grid-parent highlight_on_hover1 suffix-5 row1e'>
				<?php
					$yes=$no='';
					if($_SESSION['swelling']=="yes"){$yes=" checked ";}
					elseif($_SESSION['swelling']=="no"){$no=" checked ";}
				?>
				<div class='grid-100 heading_bg '>EXTRA ORAL</div>
				<div class='grid-80 question '><label for="" class="label">Swelling</label></div>
				<div class='answer_yes grid-10 '><input name="swelling" value="yes" <?php echo "$yes"; ?> type="radio" /></div>
				<div class='answer_no grid-10 '><input name="swelling" value="no" <?php echo "$no"; ?> type="radio" /></div>		
					<div class=clear></div>
					<div class=grid-100><label for="" class="label">If yes specify</label></div>
					<div class=grid-100><textarea  rows="" name="swell_specify"><?php echo "$_SESSION[swell_specify]"; ?></textarea></div>
				<?php
					$yes=$no='';
					if($_SESSION['lymph']=="yes"){$yes=" checked ";}
					elseif($_SESSION['lymph']=="no"){$no=" checked ";}
				?>				
				<div class='grid-80 question '><label for="" class="label">Lymphodonopathy</label></div>
				<div class='answer_yes grid-10 '><input name="lymph" value="yes" <?php echo "$yes"; ?> type="radio" /></div>
				<div class='answer_no grid-10 '><input name="lymph" value="no" <?php echo "$no"; ?> type="radio" /></div>		
					<div class=clear></div>
					<div class=grid-100><label for="" class="label">If yes specify</label></div>
					<div class=grid-100><textarea  rows="" name="lymph_specify"><?php echo "$_SESSION[lymph_specify]"; ?></textarea></div>
					<div class=grid-100><label for="" class="label">Lips</label></div>
					<div class=grid-100><textarea  rows="" name="lips"><?php echo "$_SESSION[lips]"; ?></textarea></div>
					<div class=grid-100><label for="" class="label">Others</label></div>
					<div class=grid-100><textarea  rows="" name="other"><?php echo "$_SESSION[other]"; ?></textarea></div>
					<div class=clear></div>
				
					
			</div>	
			<div class='grid-45 prefix-5 grid-parent  highlight_on_hover1 row1e '>
			<?php
					$yes=$no='';
					if($_SESSION['pocket']=="yes"){$yes=" checked ";}
					elseif($_SESSION['pocket']=="no"){$no=" checked ";}
				?>
				<div class='grid-100 heading_bg'>PERIODONTAL DISEASE</div>
				<div class='grid-80 question '><label for="" class="label">Periodontal pocket</label></div>
				<div class='answer_yes grid-10 '><input name="pocket" value="yes" <?php echo "$yes"; ?> type="radio" /></div>
				<div class='answer_no grid-10 '><input name="pocket" value="no" <?php echo "$no"; ?> type="radio" /></div>		
					<div class=clear></div>
					<div class=grid-100><label for="" class="label">If yes specify</label></div>
					<div class=grid-100><textarea  rows="" name="pockspec"><?php echo "$_SESSION[pockspec]"; ?></textarea></div>
			<?php
					$yes=$no='';
					if($_SESSION['bone']=="yes"){$yes=" checked ";}
					elseif($_SESSION['bone']=="no"){$no=" checked ";}
				?>					
				<div class='grid-80 question '><label for="" class="label">Bone loss</label></div>
				<div class='answer_yes grid-10 '><input name="bone" value="yes" <?php echo "$yes"; ?> type="radio" /></div>
				<div class='answer_no grid-10 '><input name="bone" value="no" <?php echo "$no"; ?> type="radio" /></div>		
					<div class=clear></div>
					<div class=grid-100><label for="" class="label">If yes specify</label></div>
					<div class=grid-100><textarea  rows="" name="bspecify"><?php echo "$_SESSION[bspecify]"; ?></textarea></div>
			<?php
					$yes=$no='';
					if($_SESSION['ging']=="yes"){$yes=" checked ";}
					elseif($_SESSION['ging']=="no"){$no=" checked ";}
				?>						
				<div class='grid-80 question '><label for="" class="label">Gingivitis</label></div>
				<div class='answer_yes grid-10 '><input name="ging" value="yes" <?php echo "$yes"; ?> type="radio" /></div>
				<div class='answer_no grid-10 '><input name="ging" value="no" <?php echo "$no"; ?> type="radio" /></div>		
					<div class=clear></div>		
			<?php
					$yes=$no='';
					if($_SESSION['per']=="yes"){$yes=" checked ";}
					elseif($_SESSION['per']=="no"){$no=" checked ";}
				?>						
				<div class='grid-80 question '><label for="" class="label">Periodontis</label></div>
				<div class='answer_yes grid-10 '><input name="per" value="yes" <?php echo "$yes"; ?> type="radio" /></div>
				<div class='answer_no grid-10 '><input name="per" value="no" <?php echo "$no"; ?> type="radio" /></div>		
					<div class=clear></div>
				<?php
					$slight=$moderate=$severe='';
					if($_SESSION['pspecify']=="slight"){$slight=" checked ";}
					elseif($_SESSION['pspecify']=="moderate"){$moderate=" checked ";}
					elseif($_SESSION['pspecify']=="severe"){$severe=" checked ";}
				?>						
					<div class=grid-100><label for="" class="label">If yes specify</label></div>
					<div class='grid-50 alpha'><label for="" class="label">Slight</label></div>
					<div class='grid-50 omega'><input name="pspecify" value="slight"   <?php echo "$slight"; ?>  type="radio" /></div>		
					<div class='grid-50 alpha'><label for="" class="label">Moderate</label></div>
					<div class='grid-50'><input name="pspecify" value="moderate"   <?php echo "$moderate"; ?>  type="radio" /></div>					
					<div class='grid-50 alpha'><label for="" class="label"> Severe</label></div>
					<div class='grid-50'><input name="pspecify" value="severe"   <?php echo "$severe"; ?>  type="radio" /></div>	
					<div class=clear></div>

					
			</div>	
			<div class='grid-100 grey_bottom_border'></div>	<br>	
		<div class='grid-45 suffix-5 grid-parent highlight_on_hover1 row2e'>
						<!--intra oral -->
				<?php
					$good=$fair=$poor='';
					if($_SESSION['oh']=="good"){$good=" checked ";}
					elseif($_SESSION['oh']=="fair"){$fair=" checked ";}
					elseif($_SESSION['oh']=="poor"){$poor=" checked ";}
				?>					
					<div class='grid-100 heading_bg'>INTRA ORAL</div>
					<div class=grid-100><label for="" class="label">OH</label></div>
					<div class='grid-50 alpha'><label for="" class="label">Good</label></div>
					<div class='grid-50 omega'><input name="oh" value="good"   <?php echo "$good"; ?>  type="radio" /></div>		
					<div class='grid-50 alpha'><label for="" class="label">Fair</label></div>
					<div class='grid-50'><input name="oh" value="fair"   <?php echo "$fair"; ?>  type="radio" /></div>					
					<div class='grid-50 alpha'><label for="" class="label"> Poor</label></div>
					<div class='grid-50'><input name="oh" value="poor"   <?php echo "$poor"; ?>  type="radio" /></div>	
					<div class=clear></div>	
		</div>
		<div class='grid-45 prefix-5 grid-parent  highlight_on_hover1 row2e'>
				<!--sof tissue-->
				<?php
					$yes=$no='';
					if($_SESSION['ulcers']=="yes"){$yes=" checked ";}
					elseif($_SESSION['ulcers']=="no"){$no=" checked ";}
				?>				
				<div class='grid-100 heading_bg'>SOFT TISSUES</div>
				<div class='grid-80 question '><label for="" class="label">Ulcers</label></div>
				<div class='answer_yes grid-10 '><input name="ulcers" value="yes" <?php echo "$yes"; ?> type="radio" /></div>
				<div class='answer_no grid-10 '><input name="ulcers" value="no" <?php echo "$no"; ?> type="radio" /></div>		
					<div class=clear></div>
					<div class=grid-100><label for="" class="label">If yes specify</label></div>
					<div class=grid-100><textarea  rows="" name="uspecify"><?php echo "$_SESSION[uspecify]"; ?></textarea></div>
								
		</div>	
		<div class='grid-100 grey_bottom_border'></div>		
		<!--hard tissue-->
		<br>
		<div class='grid-100 grid-parent   '>
			<div class='grid-100'>
					<div class='heading_bg grid-100'>HARD TISSUE</div>
				<?php
					$adult=$mixed=$pedo='';
					$adult_visible=' adult ';
					$mixed_visible=' mixed ';
					$pedo_visible=' pedo ';
					if($_SESSION['dentition']=="adult"){$adult=" checked ";$adult_visible=" show_element ";}
					elseif($_SESSION['dentition']=="mixed"){$mixed=" checked ";$mixed_visible=" show_element ";}
					elseif($_SESSION['dentition']=="pedo"){$pedo=" checked ";$pedo_visible=" show_element ";}
				?>						
					<div class=grid-100><label for="" class="label">Dentition</label></div>
					<div class='grid-10 alpha'><label for="" class="label">Adult</label></div>
					<div class='grid-5 '><input class='dentition' name="dentition" value="adult"   <?php echo "$adult"; ?>  type="radio" /></div>	<br>	
					<div class='grid-10'><label for="" class="label">Mixed</label></div>
					<div class='grid-5'><input name="dentition"  class='dentition' value="mixed"   <?php echo "$mixed"; ?>  type="radio" /></div>		<br>			
					<div class='grid-10'><label for="" class="label"> Pedo</label></div>
					<div class='grid-5'><input name="dentition"  class='dentition' value="pedo"   <?php echo "$pedo"; ?>  type="radio" /></div>	
					<div class='clear'></div>
			
			</div>
			<?php echo "<div class='grid-100  $adult_visible' id=adult>"; ?>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Missing Teeth</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_missing"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_missing'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight '>$number<br><input $checked class=tooth_checkbox type=checkbox name='$teeth_specified' value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_missing'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight '>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_missing'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_missing'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<!--this is for roots -->
				<div class='grid-45 prefix-5 grid-parent '>
					<div class='grid-100 teeth_caption'>Roots</div>
					<div class='grid-100 teeth_body'>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_roots"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_roots'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_roots'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_roots'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_roots'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-45-->				
				<!--end for roots-->
				<div class='clear'></div>
				<br>
				<div class='grid-100 grid-parent highlight_on_hover1'><!-- this is for cariuous teeth-->
				<div class='remove_left_padding grid-100 heading_bg'>CARIOUS TEETH</div>
				<div class='grid-45 suffix-5 remove_left_padding'>
					<div class='grid-100 tplan_table_caption'>Occlusal</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_occlusal"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div> <!-- end Occlusal parent div-->  
				
				<div class='grid-45 prefix-5 grid-parent'>
					<div class='grid-100 tplan_table_caption'>Distal occlusal</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_docclusal"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_docclusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_docclusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_docclusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_docclusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div>   <!-- end Distal occlusal  parent div-->  
				<div class='clear'></div>
				<br>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class='grid-100 tplan_table_caption'>Mesial occlusal</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_mocclusal"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_mocclusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_mocclusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_mocclusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_mocclusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>	
					<div class='clear'></div>
					
				</div>  <!-- end Mesial occlusal  parent div-->  
				<div class='grid-45 prefix-5  grid-parent'>
					<div class='grid-100 tplan_table_caption'>Root</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_root"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_root'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_root'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_root'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_root'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div><!-- end  Root  parent div--> 
				<div class=clear></div>
				<br>
				<div class='grid-45 suffix-5  grid-parent'>
					<div class='grid-100 tplan_table_caption'>Cervical/gingival</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_cervical"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_cervical'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_cervical'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_cervical'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_cervical'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div><!-- end Cervical/gingiva  parent div-->  
				<div class='clear'></div>
				</div><!--end CARIOUS TEETH -->
				<br>
				<!--replaced-->
				<div class='grid-100 grid-parent highlight_on_hover1'><!-- this is for replaced teeth-->
				<div class='remove_left_padding grid-100 heading_bg'>REPLACED</div>
				<div class='grid-45 suffix-5 remove_left_padding'>
					<div class='grid-100 tplan_table_caption'>Crown</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_crown"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_crown'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_crown'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_crown'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_crown'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div> <!-- end Occlusal parent div-->  
				
				<div class='grid-45 prefix-5 grid-parent'>
					<div class='grid-100 tplan_table_caption'>Implant</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_implant"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_implant'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_implant'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_implant'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_implant'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div>   <!-- end Distal occlusal  parent div-->  
				<div class='clear'></div>
				<br>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class='grid-100 tplan_table_caption'>Denture</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_danturv"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_danturv'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_danturv'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_danturv'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_danturv'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>	
					<div class='clear'></div>
					
				</div>  <!-- end Mesial occlusal  parent div-->  
				<div class='grid-45 prefix-5  grid-parent'>
					<div class='grid-100 tplan_table_caption'>Bridge</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_bridge"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_bridge'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_bridge'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_bridge'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_bridge'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div><!-- end  Root  parent div--> 
				<div class=clear></div>
				<br>
				<div class='grid-45 suffix-5  grid-parent'>
					<div class='grid-100 tplan_table_caption'>Root Canal</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_rcanal"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_rcanal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_rcanal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_rcanal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_rcanal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div><!-- end Cervical/gingiva  parent div-->  
				<div class='clear'></div>
				</div><!--end replaced TEETH -->	
				<!--filled-->
				<br>
				<div class='grid-100 grid-parent highlight_on_hover1'><!-- this is for filled teeth-->
				<div class='remove_left_padding grid-100 heading_bg'>FILLED</div>
				<div class='grid-45 suffix-5 remove_left_padding'>
					<div class='grid-100 tplan_table_caption'>Amalgam</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_amalgam"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_amalgam'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_amalgam'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_amalgam'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_amalgam'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div> <!-- end amalgam parent div-->  
				
				<div class='grid-45 prefix-5 grid-parent'>
					<div class='grid-100 tplan_table_caption'>Composite</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_composite"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_composite'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_composite'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_composite'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_composite'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div>   <!-- end composite  parent div-->  
				<div class='clear'></div>
				<br>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class='grid-100 tplan_table_caption'>Gic</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="adult_gic"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['adult_gic'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_gic'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_gic'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									if (in_array("$number", $_SESSION['adult_gic'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>	
					<div class='clear'></div>
					
				</div>  <!-- end Gic  parent div-->  
				
				<div class='clear'></div>
				</div><!--end replaced TEETH -->						
			</div><!--end adult dentiiton-->
			<?php echo "<div class='grid-100 $mixed_visible grid-parent' id=mixed>"; ?>
				<!--missing teeth and roots-->
				<!-- this is for missing teeth and roots teeth-->
				<div class='grid-45 suffix-5 remove_left_padding'>
					<div class='grid-100 '>Missing Teeth</div>
					<div class='grid-100'><textarea   rows='' name=mixed_missing_teeth ><?php echo "$_SESSION[mixed_missing_teeth]"; ?></textarea></div>				
				</div> <!-- end missing teeth parent div-->  
				
				<div class='grid-45 prefix-5 grid-parent'>
					<div class='grid-100 '>Roots</div>
					<div class='grid-100'><textarea   rows='' name=mixed_roots ><?php echo "$_SESSION[mixed_roots]"; ?></textarea></div>						
				</div>   <!-- end composite  parent div-->  
				<div class='clear'></div>
				<!--cariois teeth mixed-->
				<div class='grid-100 grid-parent highlight_on_hover1'><!-- this is for carious teeth mixed -->
					<div class='grid-100 heading_bg'>CARIOUS TEETH   </div>
					<div class='grid-45 suffix-5 remove_left_padding'>
						<div class='grid-100 '>Occlusal   </div>
						<div class='grid-100'><textarea   rows='' name=mixed_occlusal ><?php echo "$_SESSION[mixed_occlusal]"; ?></textarea></div>				
					</div> 
					
					<div class='grid-45 prefix-5 grid-parent'>
						<div class='grid-100 '>Distal Occlusal</div>
						<div class='grid-100'><textarea   rows='' name=mixed_distal_occlusal ><?php echo "$_SESSION[mixed_distal_occlusal]"; ?></textarea></div>						
					</div>   
					<div class=clear></div>
					<div class='grid-45 suffix-5 remove_left_padding'>
						<div class='grid-100 '>Mesial Occlusal</div>
						<div class='grid-100'><textarea   rows='' name=mixed_mesial_occlusal ><?php echo "$_SESSION[mixed_mesial_occlusal]"; ?></textarea></div>				
					</div> 
					
					<div class='grid-45 prefix-5 grid-parent'>
						<div class='grid-100 '>Root</div>
						<div class='grid-100'><textarea   rows='' name=mixed_root_carious ><?php echo "$_SESSION[mixed_root_carious]"; ?></textarea></div>						
					</div>  
					<div class=clear></div>
					<div class='grid-45 suffix-5 remove_left_padding'>
						<div class='grid-100 '>Cervical/gingival</div>
						<div class='grid-100'><textarea   rows='' name=mixed_cervical ><?php echo "$_SESSION[mixed_cervical]"; ?></textarea></div>				
					</div> 			
				</div><!--end carious teeth  mixed TEETH -->						

				<!--replaced teeth mixed-->
				<br>
				<div class='grid-100 grid-parent highlight_on_hover1'><!-- this is for carious teeth mixed -->
					<div class='grid-100 heading_bg'>REPLCAED   </div>
					<div class='grid-45 suffix-5 remove_left_padding'>
						<div class='grid-100 '>Crown       </div>
						<div class='grid-100'><textarea   rows='' name=mixed_crown ><?php echo "$_SESSION[mixed_crown]"; ?></textarea></div>				
					</div> 
					
					<div class='grid-45 prefix-5 grid-parent'>
						<div class='grid-100 '>Implant</div>
						<div class='grid-100'><textarea   rows='' name=mixed_implant ><?php echo "$_SESSION[mixed_implant]"; ?></textarea></div>						
					</div>   
					<div class=clear></div>
					<div class='grid-45 suffix-5 remove_left_padding'>
						<div class='grid-100 '>Denture</div>
						<div class='grid-100'><textarea   rows='' name=mixed_denture ><?php echo "$_SESSION[mixed_denture]"; ?></textarea></div>				
					</div> 
					
					<div class='grid-45 prefix-5 grid-parent'>
						<div class='grid-100 '>Bridge</div>
						<div class='grid-100'><textarea   rows='' name=mixed_bridge ><?php echo "$_SESSION[mixed_bridge]"; ?></textarea></div>						
					</div>  
					<div class=clear></div>
					<div class='grid-45 suffix-5 remove_left_padding'>
						<div class='grid-100 '>Root Canal</div>
						<div class='grid-100'><textarea   rows='' name=mixed_root_canal ><?php echo "$_SESSION[mixed_root_canal]"; ?></textarea></div>				
					</div> 			
				</div><!--end replaced teeth  mixed TEETH -->		

				<!--filled teeth mixed-->
				<br>
				<div class='grid-100 grid-parent highlight_on_hover1'><!-- this is for carious teeth mixed -->
					<div class='grid-100 heading_bg'>FILLED </div>
					<div class='grid-45 suffix-5 remove_left_padding'>
						<div class='grid-100 '>Amalgam       </div>
						<div class='grid-100'><textarea   rows='' name=mixed_amalgam ><?php echo "$_SESSION[mixed_amalgam]"; ?></textarea></div>				
					</div> 
					
					<div class='grid-45 prefix-5 grid-parent'>
						<div class='grid-100 '>Composite</div>
						<div class='grid-100'><textarea   rows='' name=mixed_composite ><?php echo "$_SESSION[mixed_composite]"; ?></textarea></div>						
					</div>   
					<div class=clear></div>
					<div class='grid-45 suffix-5 remove_left_padding'>
						<div class='grid-100 '>Gic</div>
						<div class='grid-100'><textarea   rows='' name=mixed_gic ><?php echo "$_SESSION[mixed_gic]"; ?></textarea></div>				
					</div> 
					
	
				</div><!--end filled teeth  mixed TEETH -->				
			</div><!--end mixed dentitio -->
			<div class=clear></div>
			<!--pedo-->			
			<?php echo "<div class='grid-100 $pedo_visible' id=pedo>"; ?><!--begin pedo-->
				<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Missing Teeth</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_missing_teeth"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_missing_teeth'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_missing_teeth'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_missing_teeth'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_missing_teeth'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked   class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<!--this is for roots -->
				<div class='grid-45 prefix-5 grid-parent '>
					<div class=' teeth_caption'>Roots</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_roots"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_roots'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input   $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_roots'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_roots'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_roots'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div><!-- end div-45-->				
				<!--end for roots-->
				<div class='clear'></div>
				<br>
				<div class='grid-100 grid-parent highlight_on_hover1'><!-- this is for cariuous teeth-->
				<div class='remove_left_padding grid-100 heading_bg'>CARIOUS TEETH</div>
					<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Occlusal</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_occlusal"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50--> 
				
				<div class='grid-45 prefix-5 grid-parent'>
					<div class=' teeth_caption'>Distal occlusal</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_distal_occlusal"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_distal_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked   class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_distal_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_distal_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_distal_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked   class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<div class='clear'></div>
				<br>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Mesial occlusal</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_mesial_occlusal"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_mesial_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_mesial_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_mesial_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_mesial_occlusal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<div class='grid-45 prefix-5 grid-parent'>
					<div class=' teeth_caption'>Root</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_root_carious"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_root_carious'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_root_carious'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_root_carious'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_root_carious'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked   class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<div class='clear'></div>
				<br>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Cervical/gingival</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_cervical"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_cervical'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_cervical'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_cervical'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_cervical'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->				
				</div><!--end CARIOUS TEETH -->
				<!--replaced-->
				<div class='grid-100 grid-parent highlight_on_hover'><!-- this is for cariuous teeth-->
				<div class='remove_left_padding grid-100'>REPLACED</div>
					<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Crown</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_crown"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_crown'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_crown'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_crown'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_crown'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50--> 
				
				<div class='grid-45 prefix-5 grid-parent'>
					<div class=' teeth_caption'>Implant</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_implant"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_implant'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_implant'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_implant'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_implant'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<div class='clear'></div>
				<br>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Denture</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_denture"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_denture'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_denture'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_denture'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_denture'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<div class='grid-45 prefix-5 grid-parent'>
					<div class=' teeth_caption'>Bridge</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_bridge"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_bridge'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_bridge'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_bridge'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_bridge'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<div class='clear'></div>
				<br>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Root Canal</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_root_canal"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_root_canal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_root_canal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_root_canal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked   class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_root_canal'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->				
				</div><!--end CARIOUS TEETH -->				
				<!--end replaced-->
				<!--filled-->
				<div class='grid-100 grid-parent highlight_on_hover1'><!-- this is for cariuous teeth-->
				<div class='remove_left_padding grid-100 heading_bg'>FILLED</div>
					<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Amalgam</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_amalgam"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_amalgam'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_amalgam'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_amalgam'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_amalgam'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50--> 
				
				<div class='grid-45 prefix-5 grid-parent'>
					<div class=' teeth_caption'>Composite</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_composite"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_composite'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_composite'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_composite'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked   class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_composite'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				<div class='clear'></div>
				<br>
				<div class='grid-45 suffix-5 grid-parent'>
					<div class=' teeth_caption'>Gic</div>
					<div class='grid-100 grid-parent teeth_body '>
						
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 5x
								<div class='teeth_body'>
								<?php
								$i2=5;
								$teeth_specified="pedo_gic"."[]";
								while($i2 >= 1){
									$number="5$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_gic'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 6x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="6$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_gic'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 8x
								<div class='teeth_body'>
								<?php
								$i2=5;
								while($i2 >= 1){
									$number="8$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_gic'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 7x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 5){
									$number="7$i2";
									$checked=$highlight='';
									if (in_array("$number", $_SESSION['pedo_gic'])) {$checked=' checked ';$highlight=' highlight ';}
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input  $checked  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>					
				</div><!-- end div-50-->
				
				</div><!--end filled TEETH -->				
				<!--end filled-->
			</div><!--end pedo dentiiton-->			
			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-100 grid-parent highlight_on_hover1'><!--begin xrays-->
				<?php
				//check if the patient has taken x-rays in the past
					$xrays_done=array();
					//get xray types first
					$sql=$error=$s1='';$placeholders=array();
					$sql="select id, name from procedures where type=2";
					$error="Unable to get xrays done";
					$s1 = select_sql($sql, $placeholders, $error, $pdo);	
					foreach($s1 as $row1){
						//get costed xrays
						$sql1=$error1=$s11='';$placeholders1=array();
						$sql1="select teeth, date_procedure_added, xray_comments from tplan_procedure where pid=:pid and 
								procedure_id=:procedure_id";
						$placeholders1['pid']=$_SESSION['pid'];
						$placeholders1['procedure_id']=$row1['id'];
						$error1="Unable to get xrays done";
						$s11 = select_sql($sql1, $placeholders1, $error1, $pdo);	
						foreach($s11 as $row11){
							$xrays_done=array('xray_name'=>$row1['name'],'teeth'=>$row11['teeth'],'date_done'=>$row11['date_procedure_added'],
							'comments'=>$row11['xray_comments'],);
						}
						
						//get uncosted xrays
						$sql1=$error1=$s11='';$placeholders1=array();
						$sql1="select teeth, date_taken, xray_comments from xray_holder where pid=:pid and 
								xrays_done=:procedure_id";
						$placeholders1['pid']=$_SESSION['pid'];
						$placeholders1['procedure_id']=$row1['id'];
						$error1="Unable to get xrays done";
						$s11 = select_sql($sql1, $placeholders1, $error1, $pdo);	
						foreach($s11 as $row11){
							$xrays_done[]=array('xray_name'=>$row1['name'],'teeth'=>$row11['teeth'],'date_done'=>$row11['date_taken'],
							'comments'=>$row11['xray_comments'],);
						}
					}
					//print_r($xrays_done);
					//now get xrays from old db
					$sql=$error=$s1='';$placeholders=array();
					$sql="select xray, when_added,xgroup from old_xrays where pid=:pid";
					$error="Unable to get xrays done";
					$placeholders['pid']=$_SESSION['pid'];
					$s1 = select_sql($sql, $placeholders, $error, $pdo);
					//echo "<br>ww1".$s1->rowCount();
					foreach($s1 as $row1){
					
						//get comments if any
						$sql1=$error1=$s11='';$placeholders1=array();
						$sql1="select comment from old_xray_comment where xgroup=:xgroup";
						$placeholders1['xgroup']=$row1['xgroup'];
						$error1="Unable to get xrays done";
						$s11 = select_sql($sql1, $placeholders1, $error1, $pdo);
						//echo "xx1".$s11->rowCount();
						foreach($s11 as $row11){
							//echo"<br>222";
							$xrays_done[]=array('xray_name'=>$row1['xray'],'teeth'=>'','date_done'=>$row1['when_added'],
							'comments'=>$row11['comment'],);
						}						
						
					}
				//get any xrays that have not been added to a tplan
					
					if(count($xrays_done) > 0){
					//print_r("$xrays_done");
						?>
						<div class=grid-100><table class='normal_table'><caption>Previous X-rays</caption>
							<tr><th class=xray_hist_count></th><th class=xray_hist_date>Date of X-ray</th>
							<th class=xray_hist_xray_type>X-ray Done</th>
							<th class=xray_hist_comment>Doctor's Comment</th></tr> <?php
						$count=1;	
						foreach($xrays_done as $row){
							if(!isset($row['date_done'])){continue;}
							//get x-ray names
							//$xrays_donev=html("$row[xray_name]");
							//$date=html($row['date_done']);
							//$comment=html($row['comments']);
							//echo "<tr><td>$count</td><td>$date</td><td>$xrays_donev</td><td>$comment</td></tr>";
							echo "<tr><td>$count</td><td>".html($row['date_done'])."</td><td>".html($row['xray_name'])."</td><td>".html($row['comments'])."</td></tr>";
							$count++;
						}

					echo "</table></div>";
					}							
						
							
					
			
				?>
				<div class='heading_bg grid-100'>X-RAYS TO PERFORM</div>
				<div class=grid-30><label for="" class="label">X-RAY TYPE</label></div>
				<div class=grid-15><label for="" class="label">Payment Method</label></div>
				<div class=grid-10><label for="" class="label">Cost</label></div>
				<div class='grid-10'><label for="" class="label">Comments</label></div>
				<div class=grid-10><label for="" class="label">Xray Image</label></div>
				<div class='grid-20'><label for="" class="label">Describe Xray</label></div>
				
				<div class=clear></div>
				<!-- <div class='grid-100'> -->
				
				<?php 
					//check if this patient type is insured or not
					$insured='NO';
					$sql=$error=$s='';$placeholders=array();
					$sql="select insured from covered_company where id=:covered_company";
					$error="Unable to check if the company is insured";
					$placeholders['covered_company']=$_SESSION['company_covered'];
					$s = select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){$insured=html($row['insured']);}
					
					//get xray types
					$sql=$error=$s='';$placeholders=array();
					$sql="select id,name from procedures where type=2";
					$error="Unable to get xray types";
					$s = select_sql($sql, $placeholders, $error, $pdo);	
					$count=1;
					foreach($s as $row){
						$xray_id=$encrypt->encrypt($row['id']);
						$xray_name=html($row['name']);?>
						
							<div class=grid-25><label for="" class="label"><?php echo "$xray_name"; ?></label></div>
							<div class='grid-5'><input class='select_xray' type=checkbox name='<?php echo "xrays$count"; ?>' value='<?php echo "$xray_id"; ?>' /></div>	

		
				 
                
                 
					
						
						<div class='grid-15'><?php
						$invoice_pay=$encrypt->encrypt("1");
						$cash_pay=$encrypt->encrypt("2");
						$points_pay=$encrypt->encrypt("3");
						echo "<select name=pay_type$count class='input_in_table_cell xray_examination_input pay_method_exam' ><option></option>";
						if($insured == 'YES' and !$_SESSION['ins_suspend']){echo "<option value='$invoice_pay'>Insurance</option>";}
						
						echo "<option value='$cash_pay'>Self</option>";
						//check if procedure is in points program
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select points from procedures_in_points_scheme where procedure_id=:procedure_id";
						$placeholders2['procedure_id']=$row['id'];
						$error2="Unable to check if procedure is in points program";
						$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
						if($s2->rowCount() > 0){echo "<option value='$points_pay'>Points</option>";}
						echo "</select>";	?>					
						</div>	
						
						
						<div class='grid-10'><input type=text class='xray_examination_input' name='<?php echo "xray_cost$count"; ?>'  /></div>	
						
						<div class=grid-10><textarea  rows=3 class='xray_examination_input' name='<?php echo "xray_comment$count"; ?>' ></textarea></div>
						<div class="grid-10">
                <input type="file"  accept="image/*" name='<?php echo "xray_image$count"; ?>' id="file" onchange="loadfile(event)"
                 style="display:none;">
				 </div>
				 <div class="grid-10">
                 <label for="file" style="cursor:pointer;">Xray Image</label>
				 <img id="output" width="100">
				 </div>

                 <div class=grid-10><textarea  rows=3 class='' name='<?php echo "xray_description$count"; ?>' ></textarea></div>

						<div class=clear></div>
						<div class='grid-45 grid-parent xray_tooth'><!-- 30 -->
							<div class='grid-100 teeth_div'>
								<div class='teeth_row'>
									<div class='hover  teeth_heading_cell'>Upper Right - 1x
										<div class='teeth_body2'>
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
						</div><!-- end the 30 -->
						
						<div class=clear></div>
						<?php 
						$count++;
					}	
						$ninye=$encrypt->encrypt($count);
						echo "<input type=hidden name=ninye value=$ninye />";
					?>
					
				<!-- </div> -->
				<div class='clear'></div>
						<br>
				
			</div><!--end x-rays-->
			<div class='grid-100 grey_bottom_border'></div>		
			<div class='grid-50'>ORTH<br><textarea  rows="" name="orth"><?php echo "$_SESSION[orth]"; ?></textarea></div>
			<div class='grid-50'>OTHER<br><textarea  rows="" name="otherprob"><?php echo "$_SESSION[otherprob]"; ?></textarea></div>
		</div>			
		<div class='grid-100 grey_bottom_border'></div>		
		<div class='grid-100'><br>
			
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
		</fieldset>
	</div>
</div>
<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>
<script>
    var loadfile = function(event){
        var image = document.getElementById('output');
        image.src = URL.createObjectURL(event.target.files[0]);
    };
</script>