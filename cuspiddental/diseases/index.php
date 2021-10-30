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
if(!userIsLoggedIn() or !userHasRole($pdo,16)){
		/*   ?>
<script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
		<?php */
		exit;}
$_SESSION['tplan_id']='';		
echo "<div class='grid_12 page_heading'>DISEASES</div>";
	

//this will unset the patient diesease session variables if not pid is currenlty set
if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_disease();}
if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){clear_patient_disease();get_patient_disease($pdo,'pid',$_SESSION['pid']);}
?>
<div class=grid-container>
	<!--<div class='grid-100 hide_first hide_element'>-->
	<div class='grid-100 '>
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="#diseases";
			 include '../../dental_includes/search_for_patient.php';
			if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){show_patient_balance($pdo,$_SESSION['pid'],$encrypt);}
			if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_examination();exit;}
	?>
<fieldset><legend>Dental Diseases</legend>	
	<form action="" method="POST"  name="" id="" class='tab_form patient_form'>

	

			<!--first name-->
			<div class='grid-30 grid-parent'>
				<?php $token = form_token(); $_SESSION['token_1e_patinet'] = "$token";  ?>
				<input type="hidden" name="token_1e_patinet"  value="<?php echo $_SESSION['token_1e_patinet']; ?>" />
				<div class='prefix-66 grid-15'><label for="" class="label">YES</label></div>
				<div class='grid-15'><label for="" class="label">NO</label></div>	
			</div>	
			<div class='grid-40 grid-parent'>
				<div class='prefix-75 grid-10'><label for="" class="label">YES</label></div>
				<div class='grid-10'><label for="" class="label">NO</label></div>
			</div>	
			<div class='grid-30 grid-parent'>
				<div class='prefix-66 grid-15'><label for="" class="label">YES</label></div>
				<div class='grid-15'><label for="" class="label">NO</label></div>	
			</div>	
			<div class=clear></div>
			<!--row 1-->
			<div class='grid-30 grid-parent highlight_on_hover row1'>
				<?php
					$yes=$no='';
					if($_SESSION['bleeding']=="yes"){$yes=" checked ";}
					elseif($_SESSION['bleeding']=="no"){$no=" checked ";}
				?>
				<div class='grid-66 question '><label for="" class="label">Abnormal bleeding</label></div>
				<div class='answer_yes grid-15 '><input name="bleeding" value="yes" <?php echo "$yes"; ?> type="radio" /></div>
				<div class='answer_no grid-15 '><input name="bleeding" value="no" <?php echo "$no"; ?> type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row1 grey_side_border'>
			<?php
					$yes=$no='';
					if($_SESSION['drug']=="yes"){$yes=" checked ";}
					elseif($_SESSION['drug']=="no"){$no=" checked ";}
				?>
				<div class='grid-75 question '><label for="" class="label">Disease,drug or radiation induced immunisation</label></div>
				<div class='answer_yes grid-10'><input name="drug" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="drug" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row1 grey_side_border'>
			<?php
					$yes=$no='';
					if($_SESSION['neuro']=="yes"){$yes=" checked ";}
					elseif($_SESSION['neuro']=="no"){$no=" checked ";}
				?>
				<div class='grid-66 question alpha'><label for="" class="label">Neurological disorders.</label></div>
				<div class='answer_yes grid-15'><input name="Neurological" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15 omega'><input name="Neurological" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
					<div class=clear></div>
					<div class=grid-100><label for="" class="label">If yes specify</label></div>
					<div class=grid-100><textarea  rows="" name="neuro"><?php echo "$_SESSION[nspecify]"; ?></textarea></div>
				
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class='clear'></div>
			<!--row 2-->
			<div class='grid-30 grid-parent highlight_on_hover row2'>
			<?php
					$yes=$no='';
					if($_SESSION['aids']=="yes"){$yes=" checked ";}
					elseif($_SESSION['aids']=="no"){$no=" checked ";}
				?>
				<div class='grid-66 question'><label for="" class="label">AIDS or HIV infection</label></div>
				<div class='answer_yes grid-15'><input name="HIV" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="HIV" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row2'>	
				<?php
					$yes=$no='';
					if($_SESSION['diab1']=="yes"){$yes=" checked ";}
					elseif($_SESSION['diab1']=="no"){$no=" checked ";}
					$type_1=$type_ii='';
					if($_SESSION['diabetes']=="I"){$type_1=" checked ";}
					elseif($_SESSION['diabetes']=="II"){$type_ii=" checked ";}					
				?>
				<div class='grid-75 question '><label for="" class="label">Diabetes</label></div>
				<div class='answer_yes grid-10'><input name="Diabetes" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="Diabetes" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
					<div class=clear></div>
					<div class='grid-100'><label for="" class="label">If yes specify below:</label></div>
					<div class='grid-50 alpha'><label for="" class="label">Type I (Insulin dependent)</label></div>
					<div class='grid-50 omega'><input name="Type" value="I"   <?php echo "$type_1"; ?>  type="radio" /></div>		
					<div class='grid-50 alpha'><label for="" class="label">Type II</label></div>
					<div class='grid-50'><input name="Type" value="II"   <?php echo "$type_ii"; ?>  type="radio" /></div>					
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row2'>
			<?php
					$yes=$no='';
					if($_SESSION['osteoporosis']=="yes"){$yes=" checked ";}
					elseif($_SESSION['osteoporosis']=="no"){$no=" checked ";}
				?>
				<div class='grid-66 question'><label for="" class="label">Osteoporosis</label></div>
				<div class='answer_yes grid-15'><input name="Osteoporosis" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Osteoporosis" value="no"  <?php echo "$no"; ?>  type="radio" /></div>					
			</div>
			<div class='grid-100 grey_bottom_border'></div>			
			<div class=clear></div>	
			<!--row3-->
			<div class='grid-30 grid-parent highlight_on_hover row3'>	
			<?php
					$yes=$no='';
					if($_SESSION['anaemia']=="yes"){$yes=" checked ";}
					elseif($_SESSION['anaemia']=="no"){$no=" checked ";}
				?>	
				<div class='grid-66 question'><label for="" class="label">Anemia</label></div>
				<div class='answer_yes grid-15'><input name="anemia" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="anemia" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row3'>	
				<?php
					$yes=$no='';
					if($_SESSION['bleeding']=="yes"){$yes=" checked ";}
					elseif($_SESSION['bleeding']=="no"){$no=" checked ";}
				?>			
				<div class='grid-75 question '><label for="" class="label">Dry mouth</label></div>
				<div class='answer_yes grid-10'><input name="dry" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="dry" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row3'>
				<?php
					$yes=$no='';
					if($_SESSION['swollen']=="yes"){$yes=" checked ";}
					elseif($_SESSION['swollen']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Persistent swollen glands in neck</label></div>
				<div class='answer_yes grid-15'><input name="Persistents" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Persistents" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<!--row4-->
			<div class='grid-30 grid-parent highlight_on_hover row4'>
				<?php
					$yes=$no='';
					if($_SESSION['arthritis']=="yes"){$yes=" checked ";}
					elseif($_SESSION['arthritis']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Arthritis</label></div>
				<div class='answer_yes grid-15'><input name="arthritis" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="arthritis" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row4'>	
				<?php
					$yes=$no='';
					if($_SESSION['eating']=="yes"){$yes=" checked ";}
					elseif($_SESSION['eating']=="no"){$no=" checked ";}
				?>				
				<div class='grid-75 question '><label for="" class="label">Eating disorder</label></div>
				<div class='answer_yes grid-10'><input name="Eating" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="Eating" value="no"  <?php echo "$no"; ?>  type="radio" /></div>
					<div class=clear></div>
					<div class='grid-100'><label for="" class="label">If yes specify</label></div>
					<div class='grid-100'><textarea  rows="" name="disorder"><?php echo "$_SESSION[especify]"; ?></textarea></div>					
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row4'> 
				<?php
					$yes=$no='';
					if($_SESSION['rproblems']=="yes"){$yes=" checked ";}
					elseif($_SESSION['rproblems']=="no"){$no=" checked ";}
					$res_yes=$res_no='';
					if($_SESSION['emphysema']=="Emphysema"){$res_yes=" checked ";}
					elseif($_SESSION['emphysema']=="Bronchitis, etc"){$res_no=" checked ";}

					?>				
				<div class='grid-66 question'><label for="" class="label">Respiratory problems</label></div>
				<div class='answer_yes grid-15'><input name="Respiratory" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Respiratory" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
					<div class=clear></div>
					<div class='grid-100'><label for="" class="label">If yes specify below:</label></div>
					<div class='grid-50'><label for="" class="label">Emphysema</label></div>
					<div class='grid-50'><input name="yes" value="Emphysema"   <?php echo "$res_yes"; ?>  type="radio" /></div>		
					<div class='grid-50 alpha'><label for="" class="label">Bronchitis, etc</label></div>
					<div class='grid-50'><input name="yes" value="Bronchitis, etc"   <?php echo "$res_no"; ?>  type="radio" /></div>						
			</div>		
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>		
			<!--row5-->
			<div class='grid-30 grid-parent highlight_on_hover row5'>	
				<?php
					$yes=$no='';
					if($_SESSION['rarthritis']=="yes"){$yes=" checked ";}
					elseif($_SESSION['rarthritis']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Rheumatoid arthritis</label></div>
				<div class='answer_yes grid-15'><input name="rarthritis" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="rarthritis" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row5'>	
				<?php
					$yes=$no='';
					if($_SESSION['epilepsy']=="yes"){$yes=" checked ";}
					elseif($_SESSION['epilepsy']=="no"){$no=" checked ";}
				?>				
				<div class='grid-75 question '><label for="" class="label">Epilepsy</label></div>
				<div class='answer_yes grid-10'><input name="Epilepsy" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="Epilepsy" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row5'>
				<?php
					$yes=$no='';
					if($_SESSION['headaches']=="yes"){$yes=" checked ";}
					elseif($_SESSION['headaches']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Severe headaches</label></div>
				<div class='answer_yes grid-15'><input name="Severe" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Severe" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<!--row6-->
			<div class='grid-30 grid-parent highlight_on_hover row6'>		
				<?php
					$yes=$no='';
					if($_SESSION['asthma']=="yes"){$yes=" checked ";}
					elseif($_SESSION['asthma']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Asthma</label></div>
				<div class='answer_yes grid-15'><input name="asthma" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="asthma" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row6'>		
				<?php
					$yes=$no='';
					if($_SESSION['faint']=="yes"){$yes=" checked ";}
					elseif($_SESSION['faint']=="no"){$no=" checked ";}
				?>				
				<div class='grid-75 question '><label for="" class="label">Fainting spells or seizures</label></div>
				<div class='answer_yes grid-10'><input name="Fainting" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="Fainting" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row6'>
				<?php
					$yes=$no='';
					if($_SESSION['wloss']=="yes"){$yes=" checked ";}
					elseif($_SESSION['wloss']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Severe or rapid weight loss</label></div>
				<div class='answer_yes grid-15'><input name="weight" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="weight" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<!--row7-->
			<!--<div class='grid-30 grid-parent highlight_on_hover row7'>			
				<div class='grid-66 question'><label for="" class="label">Anemia</label></div>
				<div class='answer_yes grid-15'><input name="anemia" value="yes" type="radio" /></div>
				<div class='answer_no grid-15'><input name="anemia" value="no" type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row7'>		
				<div class='grid-75 question '><label for="" class="label">Dry mouth</label></div>
				<div class='answer_yes grid-10'><input name="Dry" value="yes" type="radio" /></div>
				<div class='answer_no grid-10'><input name="Dry" value="no" type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row7'>
				<div class='grid-66 question'><label for="" class="label">Persistent swollen glands in neck</label></div>
				<div class='answer_yes grid-15'><input name="Persistents" value="yes" type="radio" /></div>
				<div class='answer_no grid-15'><input name="Persistents" value="no" type="radio" /></div>	
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	-->
			<!--row8-->
			<div class='grid-30 grid-parent highlight_on_hover row8'>			
				<?php
					$yes=$no='';
					if($_SESSION['transfusion']=="yes"){$yes=" checked ";}
					elseif($_SESSION['transfusion']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Blood transfusion</label></div>
				<div class='answer_yes grid-15'><input name="transfusion" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="transfusion" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
					<div class=clear></div>
					<div class='grid-100'><label for="" class="label">If yes specify</label></div>
					<div class='grid-100'><textarea  rows="" name="blood"><?php echo "$_SESSION[tdate]"; ?></textarea></div>					
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row8'>		
				<?php
					$yes=$no='';
					if($_SESSION['reflux']=="yes"){$yes=" checked ";}
					elseif($_SESSION['reflux']=="no"){$no=" checked ";}
				?>				
				<div class='grid-75 question '><label for="" class="label">G.E. reflux</label></div>
				<div class='answer_yes grid-10'><input name="reflux" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="reflux" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row8'>
				<?php
					$yes=$no='';
					if($_SESSION['std']=="yes"){$yes=" checked ";}
					elseif($_SESSION['std']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Sexually transmitted disease</label></div>
				<div class='answer_yes grid-15'><input name="Sexually" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Sexually" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<!--row9-->
			<div class='grid-30 grid-parent highlight_on_hover row9'>			
				<?php
					$yes=$no='';
					if($_SESSION['cancer']=="yes"){$yes=" checked ";}
					elseif($_SESSION['cancer']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Cancer/chemotherapy/radiation treatment</label></div>
				<div class='answer_yes grid-15'><input name="chemotherapy" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="chemotherapy" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row9'>	
				<?php
					$yes=$no='';
					if($_SESSION['glaucoma']=="yes"){$yes=" checked ";}
					elseif($_SESSION['glaucoma']=="no"){$no=" checked ";}
				?>				
				<div class='grid-75 question '><label for="" class="label">Glaucoma</label></div>
				<div class='answer_yes grid-10'><input name="Glaucoma" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="Glaucoma" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row9'>
				<?php
					$yes=$no='';
					if($_SESSION['sinus']=="yes"){$yes=" checked ";}
					elseif($_SESSION['sinus']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Sinus trouble</label></div>
				<div class='answer_yes grid-15'><input name="Sinus" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Sinus" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<!--row10-->
			<div class='grid-30 grid-parent highlight_on_hover row10'>			
				<?php
					$yes=$no='';
					if($_SESSION['chronic']=="yes"){$yes=" checked ";}
					elseif($_SESSION['chronic']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Chronic pain</label></div>
				<div class='answer_yes grid-15'><input name="Chronic" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Chronic" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row10'>		
				<?php
					$yes=$no='';
					if($_SESSION['hemophilia']=="yes"){$yes=" checked ";}
					elseif($_SESSION['hemophilia']=="no"){$no=" checked ";}
				?>				
				<div class='grid-75 question '><label for="" class="label">Hemophilia</label></div>
				<div class='answer_yes grid-10'><input name="Hemophilia" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="Hemophilia" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row10'>
				<?php
					$yes=$no='';
					if($_SESSION['sleep']=="yes"){$yes=" checked ";}
					elseif($_SESSION['sleep']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Sleep disorder</label></div>
				<div class='answer_yes grid-15'><input name="Sleep" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Sleep" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>	
			<!--row11-->
			<div class='grid-30 grid-parent highlight_on_hover row11'>			
				<?php
					$yes=$no='';
					if($_SESSION['diarea']=="yes"){$yes=" checked ";}
					elseif($_SESSION['diarea']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Persistent diarrhea</label></div>
				<div class='answer_yes grid-15'><input name="Persistent" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Persistent" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-40 grid-parent grey_side_border highlight_on_hover row11'>		
				<?php
					$yes=$no='';
					if($_SESSION['hepatitis']=="yes"){$yes=" checked ";}
					elseif($_SESSION['hepatitis']=="no"){$no=" checked ";}
				?>				
				<div class='grid-75 question '><label for="" class="label">Hepatitis, jaundice or liver disease</label></div>
				<div class='answer_yes grid-10'><input name="Hepatitis" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-10'><input name="Hepatitis" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
			</div>	
			<div class='grid-30 grid-parent grey_side_border highlight_on_hover row11'>
				<?php
					$yes=$no='';
					if($_SESSION['sores']=="yes"){$yes=" checked ";}
					elseif($_SESSION['sores']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Sores or ulcers in the mouth</label></div>
				<div class='answer_yes grid-15'><input name="Sores" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Sores" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
			</div>	
			<div class='grid-100 grey_bottom_border'></div>
			<div class=clear></div>		
			<!--row12-->
			<div class='grid-30 grid-parent highlight_on_hover row12'>			
				<?php
					$yes=$no='';
					if($_SESSION['cardio_disease']=="yes"){$yes=" checked ";}
					elseif($_SESSION['cardio_disease']=="no"){$no=" checked ";}
				?>				
				<div class='grid-66 question'><label for="" class="label">Cardiovascular disease</label></div>
				<div class='answer_yes grid-15'><input name="Cardiovascular" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
				<div class='answer_no grid-15'><input name="Cardiovascular" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
					<div class=clear></div>
					<div class='grid-100'><label for="" class="label">If yes specify below:</label></div>
					
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '> 
					<?php
						$checked='';
						if($_SESSION['angina']=="Angina"){$checked=" checked ";}
					?>					
					<div class='grid-50 remove-inside-padding'><label for="" class="label">Angina</label></div>
						<div class='grid-50'><input name="Angina" value="Angina"   <?php echo "$checked"; ?>  type="checkbox" /></div>	
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '>
					<?php
						$checked='';
						if($_SESSION['arteriosclerosis']=="Arteriosclerosis"){$checked=" checked ";}
					?>		
					<div class='grid-50 remove-inside-padding'><label for="" class="label">Arteriosclerosis</label>
					</div><div class='grid-50'><input name="Arteriosclerosis" value="Arteriosclerosis"  <?php echo "$checked"; ?>   type="checkbox" /></div>		
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '>
					<?php
						$checked='';
						if($_SESSION['hvalves']=="Artificial heart valves"){$checked=" checked ";}
					?>	
					<div class='grid-50 remove-inside-padding'><label for="" class="label">Artificial heart valves</label></div>
					<div class='grid-50'><input name="Artificial" value="Artificial heart valves"   <?php echo "$checked"; ?>  type="checkbox" /></div>		
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '>
					<?php
						$checked='';
						if($_SESSION['cinsuff']=="Coronary insufficiency"){$checked=" checked ";}
					?>		
						<div class='grid-50 remove-inside-padding'><label for="" class="label">Coronary insufficiency</label></div>
					<div class='grid-50'><input name="Coronary" value="Coronary insufficiency"   <?php echo "$checked"; ?>  type="checkbox" /></div>		
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '> 
					<?php
						$checked='';
						if($_SESSION['cocclus']=="Coronary occlusion"){$checked=" checked ";}
					?>		
						<div class='grid-50 remove-inside-padding'><label for="" class="label">Coronary occlusion</label></div>
					<div class='grid-50'><input name="occlusion" value="Coronary occlusion"   <?php echo "$checked"; ?>  type="checkbox" /></div>		
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '> 	
					<?php
						$checked='';
						if($_SESSION['dhvalve']=="Damaged heart valves"){$checked=" checked ";}
					?>	
						<div class='grid-50 remove-inside-padding'><label for="" class="label">Damaged heart valves</label></div>
					<div class='grid-50'><input name="Damaged" value="Damaged heart valves"   <?php echo "$checked"; ?>  type="checkbox" /></div>		
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '>
					<?php
						$checked='';
						if($_SESSION['hattack']=="Heart attack"){$checked=" checked ";}
					?>		
						<div class='grid-50 remove-inside-padding'><label for="" class="label">Heart attack</label></div>
					<div class='grid-50'><input name="heart_attack" value="Heart attack"   <?php echo "$checked"; ?>  type="checkbox" /></div>		
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '> 
					<?php
						$checked='';
						if($_SESSION['hmurmur']=="Heart murmur"){$checked=" checked ";}
					?>	
						<div class='grid-50 remove-inside-padding'><label for="" class="label">Heart murmur</label></div>
					<div class='grid-50'><input name="murmur" value="Heart murmur"   <?php echo "$checked"; ?>  type="checkbox" /></div>		
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '> 
					<?php
						$checked='';
						if($_SESSION['inborn']=="Inborn heart defects"){$checked=" checked ";}
					?>	
						<div class='grid-50 remove-inside-padding'><label for="" class="label">Inborn heart defects</label></div>
					<div class='grid-50'><input name="Inborn" value="Inborn heart defects"   <?php echo "$checked"; ?>  type="checkbox" /></div>		
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '> 
					<?php
						$checked='';
						if($_SESSION['prolapse']=="Mitral valve prolapse"){$checked=" checked ";}
					?>	
						<div class='grid-50  remove-inside-padding'><label for="" class="label">Mitral valve prolapse</label></div>
					<div class='grid-50'><input name="Mitral" value="Mitral valve prolapse"  <?php echo "$checked"; ?>   type="checkbox" /></div>	
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '> 		
					<?php
						$checked='';
						if($_SESSION['pacemaker']=="Pacemaker"){$checked=" checked ";}
					?>	
						<div class='grid-50 remove-inside-padding'><label for="" class="label">Pacemaker</label></div>
						<div class='grid-50'><input name="Pacemaker" value="Pacemaker"   <?php echo "$checked"; ?>  type="checkbox" /></div>	
					</div>
					<div class=clear></div>	
					<div class='grid-100 blue_higlight '> 	
					<?php
						$checked='';
						if($_SESSION['rhdisease']=="Rheumatic heart disease"){$checked=" checked ";}
					?>	
						<div class='grid-50 remove-inside-padding'><label for="" class="label">Rheumatic heart disease</label></div>
						<div class='grid-50'><input name="Rheumatic" value="Rheumatic heart disease"  <?php echo "$checked"; ?>   type="checkbox" /></div>
					</div>
				
			</div>	
			<div class='grid-40   grey_side_border  row12 remove-inside-padding'>		
				<div class='grid-100  grid-parent highlight_on_hover row13 '>
				<?php
					$yes=$no='';
					if($_SESSION['recurent']=="yes"){$yes=" checked ";}
					elseif($_SESSION['recurent']=="no"){$no=" checked ";}
				?>					
					<div class='grid-75 question'><label for="" class="label">Recurent infections</label></div>
					<div class='answer_yes grid-10'><input name="Recurent" value="yes"  <?php echo "$yes"; ?>  type="radio" /></div>
					<div class='answer_no grid-10'><input name="Recurent" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
						<div class=clear></div>
						<div class='grid-100'><label for="" class="label">Indicate type of infection</label></div>
						<div class='grid-100'><textarea  rows="" name="infections"><?php echo "$_SESSION[rtype]"; ?></textarea></div>							
				</div>	
				<div class='grid-100 grey_bottom_border'></div>
				<div class='grid-100 grid-parent  highlight_on_hover row14'>
				<?php
					$yes=$no='';
					if($_SESSION['kidney']=="yes"){$yes=" checked ";}
					elseif($_SESSION['kidney']=="no"){$no=" checked ";}
				?>					
					<div class='grid-75 question '><label for="" class="label">Kidney prblems</label></div>
					<div class='answer_yes grid-10'><input name="Kidney" value="yes" type="radio" /></div>
					<div class='answer_no grid-10'><input name="Kidney" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>
				<div class='grid-100 grey_bottom_border'></div>				
				<div class='grid-100 grid-parent  highlight_on_hover row15'>
				<?php
					$yes=$no='';
					if($_SESSION['low_blood']=="yes"){$yes=" checked ";}
					elseif($_SESSION['low_blood']=="no"){$no=" checked ";}
				?>					
					<div class='grid-75 question '><label for="" class="label">Low blood pressure</label></div>
					<div class='answer_yes grid-10'><input name="Low" value="yes" type="radio" /></div>
					<div class='answer_no grid-10'><input name="Low" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>
				<div class='grid-100 grid-parent  highlight_on_hover row16'>
				<?php
					$yes=$no='';
					if($_SESSION['malnutrition']=="yes"){$yes=" checked ";}
					elseif($_SESSION['malnutrition']=="no"){$no=" checked ";}
				?>					
					<div class='grid-75 question '><label for="" class="label">Malnutrition</label></div>
					<div class='answer_yes grid-10'><input name="Malnutrition" value="yes" type="radio" /></div>
					<div class='answer_no grid-10'><input name="Malnutrition" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>
				<div class='grid-100 grid-parent  highlight_on_hover row17'>
				<?php
					$yes=$no='';
					if($_SESSION['migrain']=="yes"){$yes=" checked ";}
					elseif($_SESSION['migrain']=="no"){$no=" checked ";}
				?>					
					<div class='grid-75 question '><label for="" class="label">Migraines</label></div>
					<div class='answer_yes grid-10'><input name="Migraines" value="yes" type="radio" /></div>
					<div class='answer_no grid-10'><input name="Migraines" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>
				<div class='grid-100 grid-parent  highlight_on_hover row18'>
				<?php
					$yes=$no='';
					if($_SESSION['night_sweat']=="yes"){$yes=" checked ";}
					elseif($_SESSION['night_sweat']=="no"){$no=" checked ";}
				?>					
					<div class='grid-75 question '><label for="" class="label">Night Sweats</label></div>
					<div class='answer_yes grid-10'><input name="Night" value="yes" type="radio" /></div>
					<div class='answer_no grid-10'><input name="Night" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>
				<div class='grid-100 grid-parent  highlight_on_hover row19'>
				<?php
					$yes=$no='';
					if($_SESSION['mental']=="yes"){$yes=" checked ";}
					elseif($_SESSION['mental']=="no"){$no=" checked ";}
				?>					
					<div class='grid-75 question '><label for="" class="label">Mental health disorders</label></div>
					<div class='answer_yes grid-10'><input name="Mental" value="yes" type="radio" /></div>
					<div class='answer_no grid-10'><input name="Mental" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
						<div class=clear></div>
						<div class='grid-100'><label for="" class="label">If yes, specify below:</label></div>
						<div class='grid-100'><textarea  rows="" name="mental_disorder"><?php echo "$_SESSION[mspecify]"; ?></textarea></div>						
				</div>	
						
			</div>	
			<div class='grid-30 grid-parent grey_side_border  row12 remove-inside-padding'>
				<div class='grid-100 grid-parent  highlight_on_hover row13'>
				<?php
					$yes=$no='';
					if($_SESSION['stroke']=="yes"){$yes=" checked ";}
					elseif($_SESSION['stroke']=="no"){$no=" checked ";}
				?>					
					<div class='grid-66 question '><label for="" class="label">Stroke</label></div>
					<div class='answer_yes grid-15'><input name="Stroke" value="yes" type="radio" /></div>
					<div class='answer_no grid-15'><input name="Stroke" value="no"  <?php echo "$no"; ?>  type="radio" /></div>	
				</div>	
				<div class='grid-100 grey_bottom_border'></div>
				<div class='grid-100 grid-parent  highlight_on_hover row14'>
				<?php
					$yes=$no='';
					if($_SESSION['systematic']=="yes"){$yes=" checked ";}
					elseif($_SESSION['systematic']=="no"){$no=" checked ";}
				?>					
					<div class='grid-66 question '><label for="" class="label">Systematic lupus erythematosus</label></div>
					<div class='answer_yes grid-15'><input name="Systematic" value="yes" type="radio" /></div>
					<div class='answer_no grid-15'><input name="Systematic" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>
				<div class='grid-100 grid-parent  highlight_on_hover row15'>
				<?php
					$yes=$no='';
					if($_SESSION['thyroid']=="yes"){$yes=" checked ";}
					elseif($_SESSION['thyroid']=="no"){$no=" checked ";}
				?>					
					<div class='grid-66 question '><label for="" class="label">Thyroid problems</label></div>
					<div class='answer_yes grid-15'><input name="Thyroid" value="yes" type="radio" /></div>
					<div class='answer_no grid-15'><input name="Thyroid" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>
				<div class='grid-100 grid-parent  highlight_on_hover row16'>
				<?php
					$yes=$no='';
					if($_SESSION['tb']=="yes"){$yes=" checked ";}
					elseif($_SESSION['tb']=="no"){$no=" checked ";}
				?>					
					<div class='grid-66 question '><label for="" class="label">Tuberculosis</label></div>
					<div class='answer_yes grid-15'><input name="Tuberculosis" value="yes" type="radio" /></div>
					<div class='answer_no grid-15'><input name="Tuberculosis" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>				
				<div class='grid-100 grid-parent  highlight_on_hover row17'>
				<?php
					$yes=$no='';
					if($_SESSION['ulcers']=="yes"){$yes=" checked ";}
					elseif($_SESSION['ulcers']=="no"){$no=" checked ";}
				?>					
					<div class='grid-66 question '><label for="" class="label">Ulcers</label></div>
					<div class='answer_yes grid-15'><input name="Ulcers" value="yes" type="radio" /></div>
					<div class='answer_no grid-15'><input name="Ulcers" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>	
				<div class='grid-100 grid-parent  highlight_on_hover row18'>
				<?php
					$yes=$no='';
					if($_SESSION['urination']=="yes"){$yes=" checked ";}
					elseif($_SESSION['urination']=="no"){$no=" checked ";}
				?>					
					<div class='grid-66 question '><label for="" class="label">Excessive urination</label></div>
					<div class='answer_yes grid-15'><input name="urination" value="yes" type="radio" /></div>
					<div class='answer_no grid-15'><input name="urination" value="no"  <?php echo "$no"; ?>  type="radio" /></div>		
				</div>	
				<div class='grid-100 grey_bottom_border'></div>	
				<div class='grid-100 grid-parent  highlight_on_hover row19'>
					<div class='grid-100 question '><label for="" class="label">Do you have any disease, condition, or problem not
					listed above that you think I should know about? </label></div>
						<div class=clear></div>
						<div class='grid-100'><textarea  rows="" name="other"><?php echo "$_SESSION[other]"; ?></textarea></div>						
				</div>	
				
			</div>	
				<div class='grid-100 grey_bottom_border'></div>		

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
		</fieldset>
	</div>
</div>
<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>