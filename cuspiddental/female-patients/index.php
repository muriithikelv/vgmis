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
if(!userIsLoggedIn() or !userHasRole($pdo,15)){
		   ?>
<script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
		<?php
		exit;}
$_SESSION['tplan_id']='';		
echo "<div class='grid_12 page_heading'>FEMALE PATIENTS</div>";

//this will unset the patient contact session variables if not pid is currenlty set
if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_female_patient();}
if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){clear_female_patient();get_female_patient($pdo,'pid',$_SESSION['pid']);}
?>
<div class=grid-container>
	
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="#female-patients";
			 include '../../dental_includes/search_for_patient.php';
			if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){show_patient_balance($pdo,$_SESSION['pid'],$encrypt);}
			if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_examination();exit;}
		
		 
	?>
	<form action="" method="POST"  name="" id="" class='tab_form patient_form'>
	
	<fieldset><legend>Female Patient Information</legend>
					<?php $token = form_token(); $_SESSION['token_1d_patinet'] = "$token";  ?>
				<input type="hidden" name="token_1d_patinet"  value="<?php echo $_SESSION['token_1d_patinet']; ?>" />
		<div class='grid-5 prefix-80'>YES</div><div class='grid-5 suffix-10'>NO</div>		
		<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['pregnant']=="yes"){$yes=" checked ";}
					elseif($_SESSION['pregnant']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Are you pregnant?</label></div>
				<div class='grid-5'><input name="pregnant" value="yes"   <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='grid-5'><input name="pregnant" value="no"   <?php echo "$no"; ?>  type="radio" /></div>
		</div>
		<div class='grid-100 grey_bottom_border'></div>
		<div class=clear></div>
		<div class='grid-100 highlight_on_hover remove-inside-padding top-bottom-padding;'>
				<?php
					$yes=$no='';
					if($_SESSION['nursing']=="yes"){$yes=" checked ";}
					elseif($_SESSION['nursing']=="no"){$no=" checked ";}
				?>		
				<div class=grid-80><label for="" class="label">Nursing?</label></div>
				<div class='grid-5'><input name="nursing" value="yes"   <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='grid-5'><input name="nursing" value="no"   <?php echo "$no"; ?>  type="radio" /></div>
		</div>
		<div class='grid-100 grey_bottom_border'></div>
		<div class=clear></div>
		<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['control']=="yes"){$yes=" checked ";}
					elseif($_SESSION['control']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Taking birth control pills?</label></div>
				<div class='grid-5'><input name="control" value="yes"   <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='grid-5'><input name="control" value="no"   <?php echo "$no"; ?>  type="radio" /></div>
		</div>
		<div class='grid-100 grey_bottom_border'></div>
		<div class=clear></div>
		<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['pjoint']=="yes"){$yes=" checked ";}
					elseif($_SESSION['pjoint']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Have you had an orthopedic total joint (hip, knee, elbow, finger) replacement?</label></div>
				<div class='grid-5'><input name="orthopedic" value="yes"   <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='grid-5'><input name="orthopedic" value="no"   <?php echo "$no"; ?>  type="radio" /></div>
						<div class=clear></div>
						<div class='grid-25'><label for="" class="label"> If so when was this operation done ?</label></div>
						<div class='grid-25'><input type="text" value='<?php echo "$_SESSION[pwhen]"; ?>'name="done" class="date_picker" /></div>					
		</div>
		<div class='grid-100 grey_bottom_border'></div>
		<div class=clear></div>
		<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['complication']=="yes"){$yes=" checked ";}
					elseif($_SESSION['complication']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Have you had any complications or difficulties with your prosthetic joint?</label></div>
				<div class='grid-5'><input name="complications" value="yes"   <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='grid-5'><input name="complications" value="no"   <?php echo "$no"; ?>  type="radio" /></div>
		</div>
		<div class='grid-100 grey_bottom_border'></div>	
		<div class=clear></div>
		<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['antibiotics']=="yes"){$yes=" checked ";}
					elseif($_SESSION['antibiotics']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Has a physician or previous dentist recommended that you take antibiotics prior to your dental treatment? </label></div>
				<div class='grid-5'><input name="recommended" value="yes"   <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='grid-5'><input name="recommended" value="no"   <?php echo "$no"; ?>  type="radio" /></div>
						<div class=clear></div>
						<div class='grid-100'><label for="" class="label"> If so, what antibiotic and dose? </label></div>
						<div class='grid-100'><textarea  rows="" name="antibiotic"><?php echo "$_SESSION[dose]"; ?></textarea></div>
						<div class=clear></div>
						<div class='grid-25'><label for="" class="label"> Name of Physician or dentisit</label></div>
						<div class='grid-25'><input type=text name="Name" value='<?php echo "$_SESSION[pname]"; ?>' /></div>						
						<div class='grid-25'><label for="" class="label"> Phone</label></div>
						<div class='grid-25'><input type=text name="Phone" value='<?php echo "$_SESSION[pphone]"; ?>' /></div>	
		</div>
		<div class='grid-100 grey_bottom_border'></div>
		<div class=clear></div><br>		
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