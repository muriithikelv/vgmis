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
if(!isset($_SESSION))
{
session_start();
}
if(!userIsLoggedIn() ){exit;}
if(isset($_POST['get_company']) and $_POST['get_company']!=''){
	//get companies covered by this ptype
	$sql=$error=$s='';$placeholders=array();
	$sql="select id,name from covered_company where insurer_id=:insurer_id";
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

//this will determine oif teeth need to be specified for a procedure
if(isset($_POST['add_procedure']) and $_POST['add_procedure']!=''){
	$sql=$error=$s='';$placeholders=array();
	$sql="select all_teeth from procedures where id=:procedure_id";
	$error="Unable to determine if procedure needs for teeth to be specified";
	$placeholders[':procedure_id']=$encrypt->decrypt($_POST['add_procedure']);
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){
		if($row['all_teeth']=='yes'){echo "show_teeth";}
		elseif($row['all_teeth']=='no'){echo "do_not_show_teeth";}
	}
}


//this will add extra procedure in treatment plan
if(isset($_POST['extra_procedure']) and $_POST['extra_procedure']!=''){
				//show procedures
				$i = $_POST['extra_procedure'] + 1;
				echo "<div class='grid-100 tplan_procedures hover '>";
					echo "<div class='grid-5 procedure_count'>$i<input type=hidden name=nisiana[] /></div>";
					echo "<div class='grid-45 grid-parent'>";
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name,id,all_teeth from procedures order by name";
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
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="teeth_specified$i"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number'>$number<br><input  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									echo "<div class='hover-row tooth_number'>$number<br><input class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									$number="1$i2";
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number'>$number<br><input  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
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
									echo "<div class='hover-row tooth_number'>$number<br><input  class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>
					
					<?php
					echo "</div>";
					echo "<div class='grid-20'><textarea   rows='' name=details$i ></textarea></div>";
					echo "<div class='grid-10'>";
						$invoice_pay=$encrypt->encrypt("1");
						$cash_pay=$encrypt->encrypt("2");
						$points_pay=$encrypt->encrypt("3");
						echo "<select name=pay_method$i class='input_in_table_cell' ><option></option>
								<option value='$invoice_pay'>Invoice</option>
								<option value='$cash_pay'>Cash</option>
								<option value='$points_pay'>Points</option>";
						echo "</select>";
					echo "</div>";
					echo "<div class='grid-10'><input type=text name=cost$i /></div>";
					echo "<div class='grid-10'><input type=text name=discount$i /></div>";
				echo "</div>";	
				echo "<div class=clear></div>";

}

//this will set the tab id to be submitted to for patient tabs
if(isset($_POST['get_patient_balance']) and $_POST['get_patient_balance']=='yes'){
	show_patient_balance($pdo,'a');
	//echo "set";
}

//this is for submitting patient diseases
if(isset($_SESSION['token_1e_patinet']) and 	isset($_POST['token_1e_patinet']) and $_POST['token_1e_patinet']==$_SESSION['token_1e_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;
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
if(isset($_SESSION['token_1c_patinet']) and 	isset($_POST['token_1c_patinet']) and $_POST['token_1c_patinet']==$_SESSION['token_1c_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;
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
if(isset($_SESSION['token_g_patinet']) and 	isset($_POST['token_g_patinet']) and $_POST['token_g_patinet']==$_SESSION['token_g_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;
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
	if(!$exit_flag and $_POST['pspecify'] !='slight' and $_POST['pspecify'] !='moderate' and $_POST['pspecify'] !='severe'   ){	
		$message="bad#Unable to save details as Periodontis is not corretcly specified";
		$var=html($_POST['pspecify']);
		$security_log="sombody tried to input $var for periodontis psecification in on_examination";
		log_security($pdo,$security_log);
		$exit_flag=true;
	}
	//check oh
	if(!$exit_flag and $_POST['oh'] !='good' and $_POST['oh'] !='fair' and $_POST['oh'] !='poor'   ){	
		$message="bad#Unable to save details as OH is not corretcly specified";
		$var=html($_POST['oh']);
		$security_log="sombody tried to input $var for OH in on_examination";
		log_security($pdo,$security_log);
		$exit_flag=true;
	}
	//check dentition
	if(!$exit_flag and $_POST['dentition'] !='adult' and $_POST['dentition'] !='mixed' and $_POST['dentition'] !='pedo'   ){	
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

	//check xrayus
	//get xray types
	$xray='';
	if(!$exit_flag and isset($_POST['xrays'])){
		$sql=$error=$s='';$placeholders=array();
		$sql="select id from teeth_and_xray_types";
		$error="Unable to get xray types";
		$s = select_sql($sql, $placeholders, $error, $pdo);
		$xray_id=array();
		foreach($s as $row){
			$xray_id[]=$row['id'];
		}
		$meno='';
		$xrays=$_POST['xrays'];
		$n2=count($xrays);
		$i2=0;
		
		while($i2 < $n2){
			if($i2==0){$xray=$encrypt->decrypt($xrays[$i2]);}
			else{$xray="$xray,".$encrypt->decrypt($xrays[$i2]);}
			if (!in_array($encrypt->decrypt($xrays[$i2]), $xray_id)) {
				$message="bad#Unable to save details as some x-ray values are not correctly set";
				$var=html($encrypt->decrypt($xrays[$i2]));
				$security_log="sombody tried to input $var into on_examination for xray types";
				log_security($pdo,$security_log);
				$exit_flag=true;
				break;
			}	
			$i2++;
		}		
	}
	
	//check amount
	if(!$exit_flag and $_POST['xray_cost']!=''){
		//remove commas
		$amount=str_replace(",", "", $_POST['xray_cost']);
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
	
	//check if amount is set without xray 
	if(!$exit_flag and $_POST['xray_cost']!='' and !isset($_POST['xrays'])){
			$message="bad#Unable to save details as X-ray cost is given but no xray has been selected";
			$exit_flag=true;			
	}	

	//check if xray is set without amount 
	if(!$exit_flag and $_POST['xray_cost']=='' and isset($_POST['xrays'])){
			$message="bad#Unable to save details as X-ray cost is not specified but an X-ray has been selected";
			$exit_flag=true;			
	}	

	//check if xray payment method is set  
	if(!$exit_flag and $_POST['pay_type']=='' and $_POST['xray_cost']!=''){
			$message="bad#Unable to save details as X-ray cost is specified but an payment method is not set";
			$exit_flag=true;			
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

			//now insert xrays
			if($xray!=''){
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into xray_holder set
						pid=:pid,
						doc_id=:doc_id,
						date_taken=now(),
						xrays_done=:xrays,
						cost=:xray_cost,
						pay_type=:pay_type,
						status=2,
						
						xray_comments=:xray_comments";
				$error="Unable to add xray to tplan procedure";
						$placeholders['pid']=$_SESSION['pid'];
						$placeholders['doc_id']=$_SESSION['id'];
						$placeholders['pay_type']=$encrypt->decrypt($_POST['pay_type']);
						$placeholders['xray_cost']=$_POST['xray_cost'];
						$placeholders['xrays']="$xray";
						$placeholders['xray_comments']=$_POST['xray_comment'];						
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
			}
					
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


//this is for submitting  a user
if(isset($_SESSION['token_add_user2']) and 	isset($_POST['token_add_user2']) and $_POST['token_add_user2']==$_SESSION['token_add_user2']
	{
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;
	global $exit_flag;
	$status='';
	
	//get action type
	$action=$encrypt->decrypt("$_POST[to_do]");
	if($action=='add_user'){
		$status="active";
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
	}

	//check first name
	if(!$exit_flag and (!isset($_POST['first_name']) or $_POST['first_name']=='') {
		$exit_flag=true;
		$message="bad#User's first name must be specified";
	}
	//check user type
	if(!$exit_flag and (!isset($_POST['user_type']) or $_POST['user_type']=='') {
		$exit_flag=true;
		$message="bad#The user type must be specified";
	}
	//check login name
	if(!$exit_flag and (!isset($_POST['user_login_name']) or $_POST['user_login_name']=='') {
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
			$sql="insert into users set
					user_name=:user_name,
					password=:password,
					 	status=:status,
					first_name=:first_name,
					middle_name=:middle_name,
					last_name=:last_name,
					gender=:gender,
					home_phone=:home_phone,
					mobile_number=:mobile_number,
					 	email_address=:email_address,
					photo_image=:photo_image,
					when_added=now()
					";
			$error="Unable to update user details";
			$placeholders[':user_name']=$_POST['user_login_name'];
			$placeholders[':password']= hash_hmac('sha1', $_POST['user_password1'], $salt);
			$placeholders[':status']="$status";
			$placeholders[':first_name']=$_POST['first_name'];
			$placeholders[':middle_name']=$_POST['middle_name'];
			$placeholders[':last_name']=$_POST['last_name'];
			$placeholders[':gender']=$_POST['gender'];
			$placeholders[':home_phone']=$_POST['user_home_phone'];
			$placeholders[':mobile_number']=$_POST['user_mobile_no'];
			$placeholders[':photo_image']=$_POST['image_upload'];
			$placeholders[':email_address']=$_POST['user_email_address'];
			
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);			
			if($s){$message="good#User details saved. ";}
			elseif(!$s){$message="bad#Unable to save user details ";}			
			
			$tx_result = $pdo->commit();

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save user details  ";
		}
	}	
		echo "$message";
		
}


//this is for submitting  patient dental information
if(isset($_SESSION['token_1b_patinet']) and 	isset($_POST['token_1b_patinet']) and $_POST['token_1b_patinet']==$_SESSION['token_1b_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;
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
if(isset($_SESSION['token_h_patient']) and 	isset($_POST['token_h_patient']) and $_POST['token_h_patient']==$_SESSION['token_h_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;
	$procedure_name_array=$procedure_array=$all_teeth=array();
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

			//insert into  tplan_id_generator
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into tplan_id_generator set when_added=now(), pid=:pid, created_by=:user_id";
			$error="Unable to create treatment plan";
			$placeholders[':pid']=$_SESSION['pid'];
			$placeholders[':user_id']=$_SESSION['id'];
			//$placeholders[':pid']=$_SESSION['pid'];
			$tplan_id = get_insert_id($sql, $placeholders, $error, $pdo);	
			
			//insert diagnosis
			$n=count($_POST['diagnosis']);
			$diagnosis=$_POST['diagnosis'];
			$i=0;
			while($i < $n){
				if($diagnosis[$i]==''){$i++;continue;}
				$sql=$error=$s='';$placeholders=array();
				$sql="insert tplan_diagnosis set
					tplan_id=:tplan_id,
					diagnosis=:diagnosis
					";
				$error="Unable to save treatment plan";
				$placeholders[':tplan_id']=$tplan_id;
				$placeholders[':diagnosis']=$diagnosis[$i];			
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				$i++;
			}			
			//now loop and insert treatment procedures
			$n=count($_POST['nisiana']);
			$i=1;
			$n22=0;
			while($i <= $n){
				if($exit_flag){ break;}
				//check selected procedure is valid
				$procedure="procedure$i";
				$teeth_specified="teeth_specified$i";
				$pay_method="pay_method$i";
				$cost="cost$i";
				$details="details$i";
				$discount="discount$i";
				if($_POST["$procedure"]==''){
					$n22++;
					$i++;
					//echo "n is $n and n22 is $n22";
					if($n22 == $n){$exit_flag=true;$message="bad#Please specify the procedure to be done";}
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
							if($pay_method_id==1){}
							else{$authorised_cost=$amount;}
											
					
					//check cost is a valid number
					if(!$exit_flag and $_POST["$discount"]!=''){
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
					}					
					//insert
					if(!$exit_flag and $pay_method_id!=1){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert tplan_procedure set
							tplan_id=:tplan_id,
							procedure_id=:procedure_id,
						  teeth=:meno,
						  details=:details,
						  unauthorised_cost=:unathorised_cost,
						  pay_type=:pay_type,
						  authorised_cost=:authorised_cost,
						  discount=:discount
							";
						$error="Unable to save treatment plan";
						$placeholders[':tplan_id']=$tplan_id;
						$placeholders[':procedure_id']=$procedure_id;
						$placeholders[':meno']="$meno";
						$placeholders[':details']=$_POST["$details"];
						$placeholders[':unathorised_cost']=$amount;
						$placeholders[':pay_type']=$pay_method_id;
						$placeholders[':authorised_cost']=$authorised_cost;
						$placeholders[':discount']=$discount_amout;
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					}
					elseif(!$exit_flag and $pay_method_id==1){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert tplan_procedure set
							tplan_id=:tplan_id,
							procedure_id=:procedure_id,
						  teeth=:meno,
						  details=:details,
						  unauthorised_cost=:unathorised_cost,
						  pay_type=:pay_type,
						  
						  discount=:discount
							";
						$error="Unable to save treatment plan";
						$placeholders[':tplan_id']=$tplan_id;
						$placeholders[':procedure_id']=$procedure_id;
						$placeholders[':meno']="$meno";
						$placeholders[':details']=$_POST["$details"];
						$placeholders[':unathorised_cost']=$amount;
						$placeholders[':pay_type']=$pay_method_id;
						//$placeholders[':authorised_cost']=$authorised_cost;
						$placeholders[':discount']=$discount_amout;
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					}

				}
				$i++;
			}
		
			if(!$exit_flag){$tx_result = $pdo->commit();$message="good#Treatment plan saved. ";}
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


//this is for submitting female patient details
if(isset($_SESSION['token_1d_patinet']) and 	isset($_POST['token_1d_patinet']) and $_POST['token_1d_patinet']==$_SESSION['token_1d_patinet']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	//$_SESSION['token_f_patient']='';
	$exit_flag=false;
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
if(isset($_SESSION['token_g_patient']) and 	isset($_POST['token_g_patient']) and $_POST['token_g_patient']==$_SESSION['token_g_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	$_SESSION['tplan_id']=$encrypt->decrypt($_POST['ninye']);
	echo "good#treatment-done";
}

//this is for submitting patient completion
if(isset($_SESSION['token_f_patient']) and 	isset($_POST['token_f_patient']) and $_POST['token_f_patient']==$_SESSION['token_f_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
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
if(isset($_SESSION['token_g2_patient']) and 	isset($_POST['token_g2_patient']) and $_POST['token_g2_patient']==$_SESSION['token_g2_patient']
	and isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	//$_SESSION['token_f_patient']='';
	$count=$encrypt->decrypt($_POST['nisiana']);
	$exit_flag=false;
		try{
			$pdo->beginTransaction();
			$i=1;
			$existing_new='';
			while($i <= $count){
				$note="note$i";
				$status="status$i";
				$raise_quotation="raise_quotation$i";
				$raise_invoice="raise_invoice$i";
				//$append_invoice="append_invoice$i";
				$procedure_number="procedure$i";
							
				//insert comment if any
				//echo "note is $_POST[$note] ";
				if(isset($_POST["$note"]) and $_POST["$note"]!=''){
					$treatment_procedure_id=$encrypt->decrypt($_POST["$procedure_number"]);	
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into treatment_procedure_notes set treatment_procedure_id=:treatment_procedure_id,
						when_added=now(), doc_id=:doc_id, notes=:notes";
					$error="Unable to update treatment procedure notes";
					$placeholders[':treatment_procedure_id']=$treatment_procedure_id;
					$placeholders[':doc_id']=$_SESSION['id'];
					$placeholders[':notes']=$_POST["$note"];
					$s = insert_sql($sql, $placeholders, $error, $pdo);	
				}
				
				//insert status if any
				if(isset($_POST["$status"]) and $_POST["$status"]!=''){
				//echo "status is $_POST[$status] ";
					$treatment_procedure_id=$encrypt->decrypt($_POST["$procedure_number"]);	
					$procedure_status=$encrypt->decrypt($_POST["$status"]);			
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

				//insert invocie number  if any
				if(isset($_POST["$raise_invoice"]) and $_POST["$raise_invoice"]!=''){
				//	echo "invoice is $_POST[$raise_invoice] ";
					$treatment_procedure_id=$encrypt->decrypt($_POST["$procedure_number"]);	
					$invoice_type=$encrypt->decrypt($_POST["$raise_invoice"]);
					if($invoice_type=="new" and $existing_new==''){//raise new invoice number
						$sql=$error=$s='';$placeholders=array();
						$sql="SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'dental_new' AND 
							TABLE_NAME = 'invoice_number_generator'";
						$error="Unable to generate new invoice number";
						$s = select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$invoice_num="I$row[0]-".date("m/y");
							$existing_new="$invoice_num";
						}
						
						//insert into invoice_generator_table
						$sql2=$error2=$s2='';$placeholders2=array(); 
						$sql2="insert into invoice_number_generator set pid=:pid";
						$error2="Unable to update invoice number generator";
						$placeholders2[':pid']=$_SESSION['pid'];
						$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);							
					}
					elseif($invoice_type=="new" and $existing_new!=''){//use newly created invoice number
						$invoice_num="$existing_new";
					}
					else{//cehck if old invoice exists
						$sql=$error=$s='';$placeholders=array();
						$sql="SELECT invoice_number from tplan_procedure where invoice_number=:invoice_number";
						$error="Unable to verify old invoice number for insertion into tplan_procedure";
						$placeholders[':invoice_number']="$invoice_type";
						$s = select_sql($sql, $placeholders, $error, $pdo);	
						if($s->rowCount()>0){$invoice_num="$invoice_type";}
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
					$sql="update tplan_procedure set invoice_number=:invoice_number, date_invoiced=now() where treatment_procedure_id=:treatment_procedure_id";
					$error="Unable to update treatment procedure invoice number";
					$placeholders[':invoice_number']="$invoice_num";
					$placeholders[':treatment_procedure_id']="$treatment_procedure_id";
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
							$sql2=$error2=$s2='';$placeholders2=array(); 
							$sql2="select sum(unauthorised_cost) from tplan_procedure where invoice_num=:invoice_number";
							$error2="Unable to get invoice total for co-payments";
							$placeholders2[':invoice_number']="$invoice_num";
							$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
							foreach($s2 as $row2){ $invoice_cost=$row2[0];}
							$deduction=ceil(($row['value'] * $invoice_cost)/100)*100;
						}
						if($deduction!=''){
							//check inf the co-payment for this invoice already exists
							$sql2=$error2=$s2='';$placeholders2=array(); 
							$sql2="delete from co_payment where invoice_number=:invoice_number";
							$error2="Unable to delete invoice  co-payments";
							$placeholders2[':invoice_number']="$invoice_num";
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);	
							
							//now insert new co-payment value	
							$sql2=$error2=$s2='';$placeholders2=array(); 
							$sql2="insert into co_payment set invoice_number=:invoice_number, amount=:amount";
							$error2="Unable to add invoice  co-payments";
							$placeholders2[':invoice_number']="$invoice_num";
							$placeholders2[':amount']="$deduction";
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);	
						}
					}					
				}	

				//insert quotation number  if any
				if(isset($_POST["$raise_quotation"]) and $_POST["$raise_quotation"]!=''){
					$treatment_procedure_id=$encrypt->decrypt($_POST["$procedure_number"]);	
					$quotation_type=$encrypt->decrypt($_POST["$raise_quotation"]);
					if($quotation_type!="new"){//cechk if quotation number is valid
								$var=html("$quotation_type");
								$security_log="sombody tried to input $var into tplan procedure as an quotation type";
								log_security($pdo,$security_log);
								$message="bad#Unable to update quoation due to unverified quotation number.";
								$exit_flag=true;
								break;
					}
					if($quotation_type=="new" and $existing_quote_new==''){//raise new quotation number
						$sql=$error=$s='';$placeholders=array();
						$sql="SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'dental_new' AND 
							TABLE_NAME = 'quotation_number_generator'";
						$error="Unable to generate new quotation number";
						$s = select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$quotation_num="Q$row[auto_increment]-".date("m/y");
							$existing_quote_new="$quotation_num";
						}
						
						//insert into quotation_number_generator_table
						$sql2=$error2=$s2='';$placeholders2=array(); 
						$sql2="insert into quotation_number_generator set pid=:pid";
						$error2="Unable to update quotation number generator";
						$placeholders2[':pid']=$_SESSION['pid'];
						$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);							
					}
					elseif($quotation_type=="new" and $existing_quote_new!=''){//use newly created quotation number
						$quotation_num="$existing_new";
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
if(isset($_POST['search_by']) and $_POST['search_by']!='' and isset($_POST['token_search_patient']) and 
	isset($_SESSION['token_search_patient']) and $_POST['token_search_patient']==$_SESSION['token_search_patient']){
	//$_SESSION['token_search_patient']='';
	$_SESSION['tplan_id']='';
		//call search function
		$result=get_patient($pdo,$_POST['search_by'],$_POST['search_ciretia']);
		//$data=explode("#","$result");
		//if($data[0]=="error"){$error_message=" $data[1] ";}
		echo "$result";
}

//this will clear a form
//this is for doing a patient search
if(isset($_POST['clear_form']) and $_POST['clear_form']!='' ){
	//echo "clearing session id is ".$_SESSION['pid'];
	$_SESSION['pid']='';
//	if($_POST['action']){}
	//echo "action is $_POST[action]";clear_patient_disease
	//echo " nnn and now is it is ".$_SESSION['pid'];
}




//this is for removing a procedure from cover
//this is for doing a patient search
if( isset($_POST['remove_procedure_cover_token']) and 	isset($_SESSION['remove_procedure_cover_token']) and 
	$_POST['remove_procedure_cover_token']==$_SESSION['remove_procedure_cover_token']){
	$exit_flag=false;
	//verify that the values given do exist
	$company_id=$encrypt->decrypt($_POST['ninye']);
	$insurer_id=$encrypt->decrypt($_POST['ninye_ins']);
	$procedure_id=$encrypt->decrypt($_POST['procedure_removed']);
	//verify company
	if (!in_array($company_id, $_SESSION['covered_company_array'])){
			$message="bad#Unable to save details as corptate details are not correct. Please contact support.";
			$var=html("$company_id");
			$security_log="sombody tried to input $var into procedures_not_covered as company id";
			log_security($pdo,$security_log);
			$exit_flag=true;
	}
	//verify insurer is
	if (!in_array($insurer_id, $_SESSION['patient_type_array'])){
			$message="bad#Unable  to save details as insurer details are not correct. Please contact support.";
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
if( isset($_POST['return_procedure_cover_token']) and 	isset($_SESSION['return_procedure_cover_token']) and 
	$_POST['return_procedure_cover_token']==$_SESSION['return_procedure_cover_token']){
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

//this is for editiong a corprate cover details
if(isset($_POST['edit_corporate']) and $_POST['edit_corporate']!='' ){
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