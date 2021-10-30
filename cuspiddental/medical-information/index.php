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
if(!userIsLoggedIn() or !userHasRole($pdo,14)){
		   ?>
<script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
		<?php
		exit;}
$_SESSION['tplan_id']='';		
echo "<div class='grid_12 page_heading'>MEDICAL INFORMATION</div>";
	

//this will unset the patient contact session variables if not pid is currenlty set
if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_medical_patient();}
if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){clear_medical_patient();get_patient_medical($pdo,'pid',$_SESSION['pid']);}
?>
<div class=grid-container>
	<div class=grid-100 >
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="#medical-information";
			 include '../../dental_includes/search_for_patient.php';
			if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){show_patient_balance($pdo,$_SESSION['pid'],$encrypt);}
			if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_examination();exit;}
	?>
	
	<form action="" method="POST"  name="" id=""  class='tab_form patient_form'>
	<?php $token = form_token(); $_SESSION['token_1c_patinet'] = "$token";  ?>
	<input type="hidden" name="token_1c_patinet"  value="<?php echo $_SESSION['token_1c_patinet']; ?>" />
				
	<br>
	<div class='grid-50'>
		<fieldset><legend>General Health Information</legend>
			<div class='grid-10 prefix-80'>YES</div><div class='grid-10'>NO</div>
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['good_health']=="yes"){$yes=" checked ";}
					elseif($_SESSION['good_health']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Are you in good health?</label></div>
				<div class='grid-10'><input name="good_health" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="good_health" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>		
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['change']=="yes"){$yes=" checked ";}
					elseif($_SESSION['change']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Has there been any change in your general health
					within the past year?</label></div>
				<div class='grid-10'><input name="change" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="change" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['tb']=="yes"){$yes=" checked ";}
					elseif($_SESSION['tb']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Active tubercolosis?</label></div>
				<div class='grid-10'><input name="tubercolosis" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="tubercolosis" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['persistent']=="yes"){$yes=" checked ";}
					elseif($_SESSION['persistent']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Persistent cough greater than a week duration</label></div>
				<div class='grid-10'><input name="Persistent" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="Persistent" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['cblood']=="yes"){$yes=" checked ";}
					elseif($_SESSION['cblood']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Cough that produces blood</label></div>
				<div class='grid-10'><input name="blood" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="blood" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['care_yes_no']=="yes"){$yes=" checked ";}
					elseif($_SESSION['care_yes_no']=="no"){$no=" checked ";}
				?>
				<div class='grid-100 blue_higlight remove-inside-padding'>
					<div class=grid-80><label for="" class="label">Are you now under the care of a physician?</label></div>
					<div class='grid-10'><input name="care" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
					<div class='grid-10'><input name="care" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>
						<div class='grid-100'><label for="" class="label">If so, what is/are the condition(s) being treated? </label></div>
						<div class='grid-100'><textarea  rows="" name="pcare"><?php echo "$_SESSION[care]"; ?></textarea></div>
				</div>
						<div class='grid-100 blue_higlight remove-inside-padding'>
							<div class='grid-50'><label for="" class="label">Date of last physical examination</label></div>
							<div class='grid-50'><input type=text class='date_picker' name="date_last_exam" value='<?php echo "$_SESSION[ldate]"; ?>' /></div>
						</div>
						
						<div class='grid-100 blue_higlight remove-inside-padding'>
							<div class='grid-50'><label for="" class="label">Physician(s) NAME </label></div>
							<div class='grid-50'><input type=text  name="pname"  value='<?php echo "$_SESSION[pname_m]"; ?>' /></div>	
						</div>
						
						<div class='grid-100 blue_higlight remove-inside-padding'>
							<div class='grid-50'><label for="" class="label">Physician(s) Phone </label></div>
							<div class='grid-50'><input type=text  name="pphone" value='<?php echo "$_SESSION[pphone_m]"; ?>' /></div>	
						</div>
						<div class='grid-100 blue_higlight remove-inside-padding'>
							<div class='grid-50'><label for="" class="label">Physician(s) Address </label></div>
							<div class='grid-50'><input type=text  name="paddress" value='<?php echo "$_SESSION[paddress]"; ?>' /></div>	
						</div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['illnes_yes_no']=="yes"){$yes=" checked ";}
					elseif($_SESSION['illnes_yes_no']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Have you had any serious illness, operation, or been
					hospitalized in the past 5 years? </label></div>
				<div class='grid-10'><input name="hospitalized" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="hospitalized" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
						<div class=clear></div>
						<div class='grid-100'><label for="" class="label">If so, what was the illness or problem? </label></div>
						<div class='grid-100'><textarea  rows="" name="operation"><?php echo "$_SESSION[illness]"; ?></textarea></div>				
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['medicine']=="yes"){$yes=" checked ";}
					elseif($_SESSION['medicine']=="no"){$no=" checked ";}
				?>
				<div class='grid-100 blue_higlight remove-inside-padding'>
				<div class=grid-80><label for="" class="label">Are you taking or have you&nbsp;recently taken
						any medicine(s) including non-prescription medicine? </label></div>
				<div class='grid-10'><input name="prescription" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="prescription" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>	
						<div class='grid-100'><label for="" class="label">If so, what medicine(s) are you taking? </label></div>
						<div class='grid-100'><label for="" class="label">Prescribed </label></div>
						<div class='grid-100'><textarea  rows="" name="prescribed"><?php echo "$_SESSION[prescribed]"; ?></textarea></div>		
					</div>
					<div class='grid-100 blue_higlight remove-inside-padding'>
						<div class='grid-100'><label for="" class="label">Over the Counter </label></div>
						<div class='grid-100'><textarea  rows="" name="Counter"><?php echo "$_SESSION[counter]"; ?></textarea></div>		
					</div>
					<div class='grid-100 blue_higlight remove-inside-padding'>	
						<div class='grid-100'><label for="" class="label">Natural or herbal preparations </label></div>
						<div class='grid-100'><textarea  rows="" name="herbal"><?php echo "$_SESSION[natural]"; ?></textarea></div>
					</div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['diet']=="yes"){$yes=" checked ";}
					elseif($_SESSION['diet']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Have you taken any diet drugs such as Pondimin
						(fenfluramine), Redux (dexphenfluramine) or phen-fen
						(fenfluramine-phentermine combination)?</label></div>
				<div class='grid-10'><input name="diet" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="diet" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['alcoholic']=="yes"){$yes=" checked ";}
					elseif($_SESSION['alcoholic']=="no"){$no=" checked ";}
				?>
				<div class='grid-100 blue_higlight remove-inside-padding'>	
					<div class=grid-80><label for="" class="label">Do you drink alcoholic beverages? </label></div>
					<div class='grid-10'><input name="drink" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
					<div class='grid-10'><input name="drink" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>		
						<div class='grid-100'><label for="" class="label">If yes, how much
								alcohol did you drink in: </label></div>
						<div class='grid-50'><label for="" class="label">The last 24 hours?</label></div>
						<div class='grid-50'><input type=text class='' name="l24" value='<?php echo "$_SESSION[l24]"; ?>'  /></div>		
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>		
						<div class='grid-50'><label for="" class="label">In the past month?</label></div>
						<div class='grid-50'><input type=text class='' name="month" value='<?php echo "$_SESSION[lmonth]"; ?>'  /></div>	
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>			
						<div class='grid-50'><label for="" class="label">Drinks per day</label></div>
						<div class='grid-50'><input type=text class='' name="day" value='<?php echo "$_SESSION[ndrinks]"; ?>'  /></div>	
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>		
						<div class='grid-50'><label for="" class="label">For how many years</label></div>
						<div class='grid-50'><input type=text class='' name="years1" value='<?php echo "$_SESSION[nyrs]"; ?>'  /></div>		
				</div>			
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['adependent']=="yes"){$yes=" checked ";}
					elseif($_SESSION['adependent']=="no"){$no=" checked ";}
				?>
				<div class='grid-100 blue_higlight remove-inside-padding'>	
					<div class=grid-80><label for="" class="label">Are you alcohol and/or drug dependent? </label></div>
					<div class='grid-10'><input name="alcohol" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
					<div class='grid-10'><input name="alcohol" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
				</div>	
					<?php
						$yes=$no='';
						if($_SESSION['treatment']=="yes"){$yes=" checked ";}
						elseif($_SESSION['treatment']=="no"){$no=" checked ";}
					?>
				<div class='grid-100 blue_higlight remove-inside-padding'>		
					<div class=grid-80><label for="" class="label">If so, have you received treatment?</label></div>
					<div class='grid-10'><input name="treatment" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
					<div class='grid-10'><input name="treatment" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
				</div>	
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['substance_yes_no']=="yes"){$yes=" checked ";}
					elseif($_SESSION['substance_yes_no']=="no"){$no=" checked ";}
				?>
				<div class='grid-100 blue_higlight remove-inside-padding'>	
					<div class=grid-80><label for="" class="label">Do you use substances for recreational purposes?</label></div>
					<div class='grid-10'><input name="substances" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
					<div class='grid-10'><input name="substances" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>	
						<div class='grid-100'><label for="" class="label"> If yes, please list  </label></div>
						<div class='grid-100'><textarea  rows="" name="list"><?php echo "$_SESSION[substances]"; ?></textarea></div>
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>	
						<div class='grid-50'><label for="" class="label">Frequency of use (daily, weekly,etc)</label></div>
						<div class='grid-50'><input type=text  name="frequency"  value='<?php echo "$_SESSION[frequency]"; ?>' /></div>
				</div>
				<div class='grid-100 blue_higlight remove-inside-padding'>	
						<div class='grid-50'><label for="" class="label">Number of years of recreational drug use </label></div>
						<div class='grid-50'><input type=text  name="years2"  value='<?php echo "$_SESSION[years]"; ?>' /></div>	
				</div>				
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['tobacco']=="yes"){$yes=" checked ";}
					elseif($_SESSION['tobacco']=="no"){$no=" checked ";}
				?>
				<div class='grid-100 blue_higlight remove-inside-padding'>	
					<div class=grid-80><label for="" class="label">Do you use tobacco (smoking,snuff,chew)?</label></div>
					<div class='grid-10'><input name="tobacco" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
					<div class='grid-10'><input name="tobacco" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
				</div>
					<?php
						$very=$somewhat=$not_interested='';
						if($_SESSION['stoping']=="very"){$very=" checked ";}
						elseif($_SESSION['stoping']=="Somewhat"){$somewhat=" checked ";}
						elseif($_SESSION['stoping']=="Not interested"){$not_interested=" checked ";}
					?>
				<div class='grid-100 blue_higlight remove-inside-padding'>			
					<div class=grid-100><label for="" class="label">If so, how interested are you in stopping?</label></div>
					<div class='grid-50'><label for="" class="label">Very</label></div>
					<div class='grid-50'><input name="how" value="very" type="radio"  <?php echo "$very"; ?> /></div>
					<div class='grid-50'><label for="" class="label">Somewhat</label></div>
					<div class='grid-50'><input name="how" value="Somewhat" type="radio"  <?php echo "$somewhat"; ?> /></div>
					<div class='grid-50'><label for="" class="label">Not interested</label></div>
					<div class='grid-50'><input name="how" value="Not interested" type="radio"  <?php echo "$not_interested"; ?> /></div>
				</div>	
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['lenses']=="yes"){$yes=" checked ";}
					elseif($_SESSION['lenses']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Do you wear contact lenses?</label></div>
				<div class='grid-10'><input name="contact" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="contact" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$a=$b=$o=$ab='';
					if($_SESSION['bgroup']=="A"){$a=" selected ";}
					elseif($_SESSION['bgroup']=="B"){$b=" selected ";}
					elseif($_SESSION['bgroup']=="O"){$o=" selected ";}
					elseif($_SESSION['bgroup']=="AB"){$ab=" selected ";}
				?>				
				<div class=grid-50><label for="" class="label">Blood group </label></div>
				<div class='grid-50'><select name="blood_groups">
					<option></option>
					<option  <?php echo "$a"; ?> value="A">A</option>
					<option  <?php echo "$b"; ?> value="B">B</option>
					<option  <?php echo "$o"; ?> value="O">O</option>
					<option  <?php echo "$ab"; ?> value="AB">AB</option>
					</select>
				</div>
				
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>				
		</fieldset>
	</div><!--end griid 50-->	
	<div class='grid-50'>
		<fieldset><legend>Allergies</legend>
			<div class='grid-10 prefix-80'>YES</div><div class='grid-10'>NO</div>
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['anaethesia']=="yes"){$yes=" checked ";}
					elseif($_SESSION['anaethesia']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Local anaethesia</label></div>
				<div class='grid-10'><input name="anaethesia" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="anaethesia" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>		
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['Asprin']=="yes"){$yes=" checked ";}
					elseif($_SESSION['Asprin']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Asprin</label></div>
				<div class='grid-10'><input name="asprin" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="asprin" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['penicilin']=="yes"){$yes=" checked ";}
					elseif($_SESSION['penicilin']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Penicillin or other antibiotics</label></div>
				<div class='grid-10'><input name="antibiotics" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="antibiotics" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>		
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['sedatives']=="yes"){$yes=" checked ";}
					elseif($_SESSION['sedatives']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Barbituaries, sedatives, or sleeping pills</label></div>
				<div class='grid-10'><input name="sedatives" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="sedatives" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['sulfa']=="yes"){$yes=" checked ";}
					elseif($_SESSION['sulfa']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Sulfa drugs</label></div>
				<div class='grid-10'><input name="sulfa" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="sulfa" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['codeine']=="yes"){$yes=" checked ";}
					elseif($_SESSION['codeine']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Codeine or narcotics</label></div>
				<div class='grid-10'><input name="narcotics" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="narcotics" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['latex']=="yes"){$yes=" checked ";}
					elseif($_SESSION['latex']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Latex</label></div>
				<div class='grid-10'><input name="Latex" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="Latex" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['iodine']=="yes"){$yes=" checked ";}
					elseif($_SESSION['iodine']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Iodine</label></div>
				<div class='grid-10'><input name="iodine" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="iodine" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['hay']=="yes"){$yes=" checked ";}
					elseif($_SESSION['hay']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Hay fever/seasonal</label></div>
				<div class='grid-10'><input name="fever" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="fever" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['animals']=="yes"){$yes=" checked ";}
					elseif($_SESSION['animals']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Animals</label></div>
				<div class='grid-10'><input name="animals" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="animals" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['food']=="yes"){$yes=" checked ";}
					elseif($_SESSION['food']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Food</label></div>
				<div class='grid-10'><input name="food" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="food" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
						<div class=clear></div>
						<div class='grid-100'><label for="" class="label"> If yes specify </label></div>
						<div class='grid-100'><textarea  rows="" name="food_specify"><?php echo "$_SESSION[food_specify]"; ?></textarea></div>				
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<?php
					$yes=$no='';
					if($_SESSION['other']=="yes"){$yes=" checked ";}
					elseif($_SESSION['other']=="no"){$no=" checked ";}
				?>
				<div class=grid-80><label for="" class="label">Other</label></div>
				<div class='grid-10'><input name="other" value="yes"  <?php echo "$yes"; ?>   type="radio" /></div>
				<div class='grid-10'><input name="other" value="no"  <?php echo "$no"; ?>   type="radio" /></div>
						<div class=clear></div>
						<div class='grid-100'><label for="" class="label">specify </label></div>
						<div class='grid-100'><textarea  rows="" name="other_specify"><?php echo "$_SESSION[other_specify]"; ?></textarea></div>						
			</div>
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>				
		</fieldset>
	</div><!--end griid 50-->	
		<div class=clear></div>
		<div class='grid-50'>
			
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
</div>