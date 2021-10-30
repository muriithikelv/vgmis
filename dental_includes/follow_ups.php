<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,95)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT FOLLOW UPS</div>";
?>
<div class='grid-container completion_form'>
<?php	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
			elseif($_SESSION['result_class']=='bad'){
				echo "<div class='feedback hide_element'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
		}
?>		
	<div class='feedback hide_element'></div>
	<?php 
if(userHasRole($pdo,109) and !isset($_POST['doc'])){
	$token = form_token(); $_SESSION['token_fu_1'] = "$token";
	?>
	<form action="" class='' method="post" name="" id="">
		<input type="hidden" name="token_fu_1"  value='<?php echo "$_SESSION[token_fu_1]"; ?>' />
		<div class='grid-10 label'>Select Doctor</div>
		<div class='grid-20'>
			<select name=doc><option value='all'>All Doctors</option>
			<?php
				//get doctors with open follow ups
				$sql=$error=$s='';$placeholders=array();
				$sql="SELECT DISTINCT created_by FROM follow_ups WHERE STATUS =0";
				$error="Error: Unable to get doctors ";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				$doc_array=array();
				foreach($s as $row){$doc_array[]=html($row['created_by']);}
				
				$sql=$error=$s='';$placeholders=array();
				$sql="select first_name, middle_name, last_name, id from users where user_type=1";
				$error="Error: Unable to get doctors ";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					if(!in_array($row['id'],$doc_array)){continue;}
					$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
					$id=$encrypt->encrypt("$row[id]");
					echo "<option value=$id>$name</option>";
				}
			?>
			</select>
		</div>
		<input type=submit value='Submit' />
	</form>	
	<?php
	exit;
}	

if(isset($_SESSION['token_fu_1']) and isset($_POST['token_fu_1']) and $_POST['token_fu_1']==$_SESSION['token_fu_1']){
	$_SESSION['token_fu_1']='';
	if($_POST['doc']=='all'){$doc_id='all';}
	elseif($_POST['doc']!='all'){$doc_id=$encrypt->decrypt("$_POST[doc]");}
}
			// include '../dental_includes/search_for_patient_no_session.php';
	$sql=$error=$s='';$placeholders=array();		
	//if user is approver/doctor then show only the follow up request that he started
	$user_type=0;
	$user_criteria=' and b.follow_up_date <= curdate() and b.pending=0  ';
	if(userHasSubRole($pdo,9) and !isset($doc_id)){
		$user_type=1;
		$placeholders['created_by']=$_SESSION['id'];
		$user_criteria= " and b.created_by=:created_by and b.follow_up_date < curdate()";
	}
	elseif(userHasSubRole($pdo,9) and isset($doc_id)){
		$user_type=1;
		if($doc_id!='all'){
			$placeholders['created_by']=$doc_id;
			$user_criteria= " and b.created_by=:created_by and b.follow_up_date < curdate()";
		}
		/*elseif($doc_id=='all'){
			$placeholders['created_by']=$doc_id;
			$user_criteria= " and b.created_by=:created_by and b.follow_up_date < curdate()";
		}*/
	}	
	//GET ANY PENDING follow ups			
	
	$sql="select a.first_name, a.middle_name, a.last_name,  a.mobile_phone, a.biz_phone, b.id , b.treatment_plan_id,
		b.follow_up_date
		from patient_details_a a, follow_ups b  where b.status=0 and b.pid=a.pid  
		$user_criteria order by b.id";					
	$error="Unable to get follows up due today";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){ ?>
		<div class='grid-100 div_shower44'></div>
		<form action="" method="POST" enctype="" name="" id="" class="patient_form" >
			<?php $token = form_token(); $_SESSION['token_cf2'] = "$token";  ?>
			<input type="hidden" name="token_cf2"  value="<?php echo $_SESSION['token_cf2']; ?>" />
		
		<div class=waiver_table><div class=tplan_table_caption>PATIENT FOLLOW UPS DUE TODAY</div>
		<div class='waiver_table_row2 '>
			<div class='waiver_pt_name2 no_border_bottom white_text'>PATIENT</div>
			<div class='follow_up_date no_border_bottom white_text'>FOLLOW<br>UP DATE</div>
			<div class='waiver_pt_bal  no_border_bottom white_text'>MOBILE No.</div>
			<div class='waiver_pt_amount no_border_bottom white_text'>BUSINESS No.</div>
			<div class='waiver_comments2 white_text'>COMMENTS</div>
			<div class='waiver_pt_treatment2 no_border_bottom white_text'>NEXT<br>FOLLOW UP</div>
			<div class='waiver_pt_action no_border_bottom white_text'>ACTION</div>
		</div>
		</div>	
		<div class=waiver_table><!--table definition -->
	<?php
			$i=0;
			$accept=$encrypt->encrypt('approved');
			$reply=$encrypt->encrypt('replied');
		foreach($s as $row){
			$i++;
			$patient_name=ucfirst(html("$row[0] $row[1] $row[2]"));
			$follow_up_date=html($row['follow_up_date']);
			$mobile_no=html($row['mobile_phone']);
			$biz_no=html($row['biz_phone']);
			$id=$encrypt->encrypt(html($row['id']));
			$treatment_pan=html($row['treatment_plan_id']);
			echo "<div class='waiver_table_row2 waiver_row'>"; //table row
				echo "<div class=waiver_pt_name2>$patient_name</div>";//pt name
				echo "<div class=follow_up_date>$follow_up_date</div>";//balance
				echo "<div class='waiver_pt_bal  no_border_right'>$mobile_no</div>";//amount waived
				echo "<div class='waiver_pt_amount  no_border_right'>$biz_no</div>";//amount waived
				//check if we have extra comments apart from original
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select b.date_of_comment, b.comment, c.first_name, c.middle_name, c.last_name 
					from follow_up_communication b , users c
					where b.follow_up_id=:follow_up_id and b.user_id = c.id order by b.id";
				$placeholders2[':follow_up_id']=$row['id'];
				$error2="Unable to get follow up comments";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
				echo "<div class='no_border_right tplan_procedure_row'>";		?>
				<div class='waiver_sub_header waiver_table_row2'><!--comment headers-->
						<div class='waiver_pt_date_comment2'>Date</div> <!-- comment date -->
						<div class=waiver_pt_user>User</div> <!-- comment made by -->
						<div class='waiver_pt_comment2  no_border_right'>Comment</div> <!-- comment  -->
				</div>
						<?php //now show newer comments
							foreach($s2 as $row2){ ?>
								<div class='waiver_table_row2 '>
									<div class=waiver_pt_date_comment2><?php htmlout($row2['date_of_comment']); ?></div> <!-- comment date -->
									<div class=waiver_pt_user><?php ucfirst(htmlout("$row2[2] $row2[3] $row2[4]")); ?></div> <!-- comment made by -->
									<div class='waiver_pt_comment2  no_border_right'><?php htmlout($row2['comment']); ?></div> <!-- comment  -->
								</div>							
							<?php
							
							}
				//new comment will go here ?>
					<div class='waiver_table_row2 '>
						<div class=waiver_pt_date_comment2><?php echo date('Y-m-d'); ?></div> <!-- comment date -->
						<div class=waiver_pt_user><?php ucfirst(htmlout($_SESSION['logged_in_user_names'])); ?></div> <!-- comment made by -->
						<div class='waiver_pt_comment2 no_border_right'><?php echo "<textarea name=comment[] width=100%></textarea>"; ?></div> <!-- comment  -->
					</div>	<?php				
				echo "</div>"; //end 	 tplan_procedure_row
				echo "<div class=waiver_pt_treatment2><input type=text name=next_follow_up[] class=date_picker_no_past /></div>";//balance
				echo "<div class='waiver_pt_action'><input type=hidden value='$id' name='ninye[]' />
					<select name=action[]>
						<option></option>";

						if($user_type==1){//this is the approver
							echo "<option value='$accept'>End Chat</option>";
						}
						echo "<option value='$reply'>Reply</option>
					</select>
				</div>";//action taken
			echo "</div>"; //end 	 waiver_table_row2			
		}	
		echo "</div>"; //end 	 waiver_table
		echo "<div class='grid-100 '><br><input type=submit class=put_right value=Submit /><br></form></div>";
		echo "<div class='grid-100 label'>Total number of pending follow ups is ".number_format($i);
	}
	else{
		echo "<div class='grid-100 label'>There are no pending follow ups.</div>";
	}

			 

?>
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>