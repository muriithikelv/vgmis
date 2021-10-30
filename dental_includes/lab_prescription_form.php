<?php
/*
if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,29)){exit;}
echo "<div class='grid_12 page_heading'>LAB PRESCRIPTION FORM</div>";//check if this guy is a doctor
?>
<div class='grid-container completion_form'>
	<?php
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']=='success_response'){
				$data=explode('#',"$_SESSION[result_message]");
				echo "<div class='feedback $_SESSION[result_class]'>$data[0]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';
				//show lab for printing
				display_lab($pdo, $data[1]);
				exit;
			}

		}
	 //include  '../../dental_includes/response.php'; 
		$_SESSION['tab_name']="lab_prescription_form";
		include '../dental_includes/search_for_patient_no_session.php';
		if(isset($pid) and $pid!=''){
			if(isset($pid_clean) and $pid_clean!=''){
				$result = check_if_swapped($pdo,'pid',$pid_clean);
				if($result!='good'){
					$swapped="$result and cannot be edited";
					echo "<div class='grid-100 error_response'>$result</div>";
					exit;
				}
				elseif($result=='good'){$swapped='';}
			}
			lab_prescription($pid,$encrypt,$pdo,'');
		}	?>
</div>

