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
if(!userIsLoggedIn() or !userHasRole($pdo,13)){
	   ?>
		<script type="text/javascript">
		localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
		window.location = window.location.href;
		</script>
		<?php
		exit;}
$_SESSION['tplan_id']='';		
echo "<div class='grid_12 page_heading'>DENTAL INFORMATION</div>";


//this will unset the patient contact session variables if not pid is currenlty set
if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_dental();}
if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){clear_patient_dental();get_patient_dental($pdo,'pid',$_SESSION['pid']);}
?>
<div class=grid-container>
	<div class='grid-100 ' >
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="#dental-information";
			 include '../../dental_includes/search_for_patient.php';
			if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){show_patient_balance($pdo,$_SESSION['pid'],$encrypt);}
			if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_examination();exit;}
	?>


		<fieldset><legend>Dental Information</legend>	
	<form action="#dental-information" method="POST"  name="" id="" class='tab_form patient_form'>

	

			
			<div class='grid-50  remove-inside-padding'>
				<div class='prefix-80 grid-10'><label for="" class="label">YES</label></div>
				<div class='grid-10'><label for="" class="label">NO</label></div>
			</div>	
			<div class=grid-50>
				<div class='prefix-80 grid-10'><label for="" class="label">YES</label></div>
				<div class='grid-10'><label for="" class="label">NO</label></div>
			</div>	
				<div class=clear></div>
			<div class='grid-50 highlight_on_hover row1b remove-inside-padding'>	
				<div class='grid-80'>
					<?php $token = form_token(); $_SESSION['token_1b_patinet'] = "$token";  ?>
				<input type="hidden" name="token_1b_patinet"  value="<?php echo $_SESSION['token_1b_patinet']; ?>" />
				<?php /*if($_SESSION['gender']=='MALE'){ $male_selected=" selected ";$female_selected="";}
					  elseif($_SESSION['gender']=='FEMALE'){ $male_selected="";$female_selected=" selected ";}
					  else{ $male_selected="";$female_selected="";}*/?>	
				<?php
					$yes=$no='';
					if($_SESSION['gums_bleed']=="yes"){$yes=" checked ";}
					elseif($_SESSION['gums_bleed']=="no"){$no=" checked ";}
				?>		
				<label for="" class="label"> Do your gums bleed when you brush?</label></div>
				<div class='grid-10'><input name="gums" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="gums" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-50 grey_side_border highlight_on_hover row1b remove-inside-padding'>	
				<?php
					$yes=$no='';
					if($_SESSION['braces']=="yes"){$yes=" checked ";}
					elseif($_SESSION['braces']=="no"){$no=" checked ";}
				?>
				<div class='grid-80'><label for="" class="label">Have you ever had orthodontic (braces) treatment?</label></div>
				<div class='grid-10'><input name="orthodontic" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="orthodontic" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-50  highlight_on_hover row2b remove-inside-padding'>	
				<!--row 2-->
				<?php
					$yes=$no='';
					if($_SESSION['sensitive_teeth']=="yes"){$yes=" checked ";}
					elseif($_SESSION['sensitive_teeth']=="no"){$no=" checked ";}
				?>
				<div class='grid-80'><label for="" class="label">Are you teeth sensitive to cold, hot , sweets or pressure?</label></div>
				<div class='grid-10'><input name="sensitive" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="sensitive" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-50 grey_side_border highlight_on_hover row2b remove-inside-padding'>	
				<?php
					$yes=$no='';
					if($_SESSION['aches']=="yes"){$yes=" checked ";}
					elseif($_SESSION['aches']=="no"){$no=" checked ";}
				?>
				<div class='grid-80'><label for="" class="label"> 	Do you have headaches, earaches or neck pains?</label></div>
				<div class='grid-10'><input name="headaches" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="headaches" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-50  highlight_on_hover row3b remove-inside-padding'>	
				<!--row 3-->
				<?php
					$yes=$no='';
					if($_SESSION['periodontal']=="yes"){$yes=" checked ";}
					elseif($_SESSION['periodontal']=="no"){$no=" checked ";}
				?>
				<div class='grid-80'><label for="" class="label">Have you heard any periodontal(gum) treatments?</label></div>
				<div class='grid-10'><input name="periodontal" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="periodontal" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-50 grey_side_border highlight_on_hover row3b remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['removeable']=="yes"){$yes=" checked ";}
					elseif($_SESSION['removeable']=="no"){$no=" checked ";}
				?>
				<div class='grid-80'><label for="" class="label">Do you wear removable dental appliances?</label></div>
				<div class='grid-10'><input name="appliances" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="appliances" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-100  highlight_on_hover  remove-inside-padding'>			
				<!--row 4-->
				<?php
					$yes=$no='';
					if($_SESSION['prev_ye_no']=="yes"){$yes=" checked ";}
					elseif($_SESSION['prev_ye_no']=="no"){$no=" checked ";}
				?>
				<div class='grid-90'><label for="" class="label">Have you had any serious/difficult problem associated with any 
						previous dental treatment?</label></div>
				<div class='grid-5'><input name="difficulty" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-5'><input name="difficulty" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
				<div class=clear></div> 
				<div class='grid-100'><label for="" class="label">if so Explain</label></div>
				<div class='grid-100'><textarea  rows="3" name="serious_difficulty"><?php echo "$_SESSION[prev]"; ?></textarea></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-100  highlight_on_hover  remove-inside-padding'>						
				<!--row 5-->
				<div class='grid-100'><label for="" class="label">How would you describe your current dental problem?</label></div>
				<div class='grid-100'><textarea  rows="3" name="dental_problem"><?php echo "$_SESSION[curr]"; ?></textarea></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-100  highlight_on_hover  remove-inside-padding'>			
				<!--dates-->
				<div class='grid-25'><label for="" class="label">Date of your last dental exam?</label></div>
				<div class='grid-25'><input name="date_last_exam" class=date_picker  value='<?php echo "$_SESSION[last_dental]"; ?>' /></div>
				<div class='grid-25'><label for="" class="label">Date of your last dental x-rays?</label></div>
				<div class='grid-25'><input name="date_of_last_xray" class=date_picker  value='<?php echo "$_SESSION[last_xray]"; ?>' /></div>
				<div class='grid-100'><label for="" class="label">What was done at that time?</label></div>
				<div class='grid-100'><textarea  rows="3" name="what_was_done"><?php echo "$_SESSION[done1]"; ?></textarea></div>

			</div>
			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-100  highlight_on_hover  remove-inside-padding'>						
				<div class='grid-100'><label for="" class="label">How do you feel about the appearance of your teeth?</label></div>
				<div class='grid-100'><textarea  rows="3" name="feel"><?php echo "$_SESSION[appearance]"; ?></textarea></div>
			</div>

			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-100  highlight_on_hover  remove-inside-padding'>						
				<div class='grid-100'><label for="" class="label">History of presenting complains?</label></div>
				<div class='grid-100'><textarea  rows="3" name="history"><?php echo "$_SESSION[history_complain]"; ?></textarea></div>
			</div>

			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-100  highlight_on_hover  remove-inside-padding'>						
				<div class='grid-100'><label for="" class="label">What is the patient medical history?</label></div>
				<div class='grid-100'><textarea  rows="3" name="med"><?php echo "$_SESSION[medical_history]"; ?></textarea></div>
			</div>
			

			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div>
			<div class='grid-100  highlight_on_hover  remove-inside-padding'>						
				<div class='grid-100'><label for="" class="label">Patient Chief Complain?</label></div>
				<div class='grid-100'><textarea  rows="3" name="chief"><?php echo "$_SESSION[chief_complain]"; ?></textarea></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>	
			<div class=clear></div><br>			

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
		<div class=clear></div>
	</form>
		</fieldset>
	</div>
</div>