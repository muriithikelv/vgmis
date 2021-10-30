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
if(!userIsLoggedIn() or !userHasRole($pdo,17)){
		   ?>
<script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
		<?php
		exit;}
$_SESSION['tplan_id']='';		
echo "<div class='grid_12 page_heading'>COMPLETION FORM</div>";
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
if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){
	clear_patient_completion();
	}
if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){
	clear_patient_completion();
	get_patient_completion($pdo,'pid',$_SESSION['pid']);
	}
?>
<div class='grid-container completion_form'>
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="#completion";
			 include '../../dental_includes/search_for_patient.php';
			if(isset($_SESSION['pid']) and $_SESSION['pid']!=''){show_patient_balance($pdo,$_SESSION['pid'],$encrypt);}
			if(!isset($_SESSION['pid']) or $_SESSION['pid']==''){clear_patient_examination();exit;}
		//set tab_name to beused in seaerch form submission
		
		 
	?>
		<fieldset><legend>For completion by dentist</legend>
	<form action="#completion" method="POST"  name="" id="" class="patient_form2">

	
					<?php $token = form_token(); $_SESSION['token_f_patient'] = "$token";  ?>
				<input type="hidden" name="token_f_patient"  value="<?php echo $_SESSION['token_f_patient']; ?>" />

		<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<div class=grid-100><label for="" class="label">Comments on patient interview concerning health history</label></div>
				<div class='grid-100'><textarea   rows="" name="commebts"><?php echo "$_SESSION[comments]"; ?></textarea></div>	
		</div>
		<div class='grid-100 grey_bottom_border'></div>
		<div class=clear></div>
		<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<div class=grid-100><label for="" class="label">Significant findings from questionnaire or oral interview</label></div>
				<div class='grid-100'><textarea  rows="" name="Significant"><?php echo "$_SESSION[significant]"; ?></textarea></div>	
		</div>
		<div class='grid-100 grey_bottom_border'></div>
		<div class=clear></div>
		<div class='grid-100 highlight_on_hover remove-inside-padding'>
				<div class=grid-100><label for="" class="label">Dental management considerations</label></div>
				<div class='grid-100'><textarea  rows="" name="dental"><?php echo "$_SESSION[management]"; ?></textarea></div>	
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
	</form>		
		</fieldset>
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>