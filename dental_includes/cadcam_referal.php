<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,55)){exit;}
//echo "<div class='grid_12 page_heading'>CADCAM REFERRALS</div>";
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';
				//show receipt
				if(isset($_SESSION['pay_id']) and $_SESSION['pay_id']!=''){
					print_receipt($pdo,$encrypt->encrypt($_SESSION['pay_id']), $encrypt);
					$_SESSION['pay_id']='';
					exit;
				}
				//show invoice
				if(isset($_SESSION['inv_no']) and $_SESSION['inv_no']!=''){
					display_invoice($pdo,$_SESSION['inv_no']);
					$_SESSION['inv_no']='';
					exit;
				}
			}
			/*elseif($_SESSION['result_class']=='bad'){
				echo "<div class='feedback hide_element'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}*/
		}
 ?>

	<div id=cadcam_tabs3>
		<ul>
		<?php
		$sql2=$error2=$s2='';$placeholders2=array();
		$sql2="select id, name from cadcam_types where listed=0 and level = 1 order by name";
		$error2="Unable to get manufacturers for cadcam";
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		foreach($s2 as $row2){
			$menu_name=html("$row2[name]");
			$var=urlencode($encrypt->encrypt($row2['id']));
			echo 	"<li ><a class='tab_link' href='dental_b/?cmr=$var' >$menu_name</a></li>";
			
		}
		?>
		</ul>
	</div>


