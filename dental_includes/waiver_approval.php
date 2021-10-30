<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,52)){exit;}
echo "<div class='grid_12 page_heading'>WAIVER PAYMENT APPROVAL</div>";
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
			// include '../dental_includes/search_for_patient_no_session.php';
				
	//GET ANY PENDING APPROVALS			
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.first_name, a.middle_name, a.last_name,  b.amount,   b.id , b.pid from 
	patient_details_a a, waiver_approvals b  where b.pay_id=0 and b.pid=a.pid  order by b.id";					
	$error="Unable to get waiver payments pending approval";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){ ?>
		<div class='grid-100 div_shower44'></div>
		<form action="" method="POST" enctype="" name="" id="" class="patient_form" >
			<?php $token = form_token(); $_SESSION['token_wap1'] = "$token";  ?>
			<input type="hidden" name="token_wap1"  value="<?php echo $_SESSION['token_wap1']; ?>" />
		
		<div class=waiver_table><div class=tplan_table_caption>WAIVER PAYMENTS PENDING APPROVAL</div>
		<div class='waiver_table_row2 '>
			<div class='waiver_pt_name no_border_bottom white_text'>PATIENT</div><div class='waiver_pt_bal  no_border_bottom white_text'>BALANCE (KES)</div>
			<div class='waiver_pt_amount no_border_bottom white_text'>WAIVE (KES)</div><div class='waiver_comments white_text'>COMMENTS</div>
			<div class='waiver_pt_action no_border_bottom white_text'>ACTION</div>
		</div>
		</div>	
		<div class=waiver_table><!--table definition -->
	<?php
			$i=0;
			$accept=$encrypt->encrypt('approved');
			$decline=$encrypt->encrypt('denied');
			$reply=$encrypt->encrypt('replied');
		foreach($s as $row){
			$i++;
			$patient_name=ucfirst(html("$row[0] $row[1] $row[2]"));
			$pid_encrypt=$encrypt->encrypt($row['pid']);
			$result=show_pt_statement_brief($pdo,$pid_encrypt,$encrypt);
			$result=str_replace(",", "", "$result");
			$data=explode('#',"$result");
			//echo "$data[1]-";
			if($data[1] == 0){$bal="0.00";}
			elseif($data[1] > 0){$bal=number_format($data[1],2);}
			elseif($data[1] < 0){
				$data[1]=str_replace("-", "", "$data[1]");
				$bal="Credit ".number_format($data[1],2);}	
			$bal=html("$bal");
			$patient_waive=html($row['amount']);
			$pid_amount=$encrypt->encrypt("$patient_waive#$row[pid]");
			$amount_waived=number_format($patient_waive, 2);
			//$date_requested=html($row['date_requested']);
			//$requester=html("$row[5] $row[6] $row[7]");
			//$comment=html($row['reason']);
			$id=$encrypt->encrypt(html($row['id']));
			echo "<div class='waiver_table_row2 waiver_row'>"; //table row
				echo "<div class=waiver_pt_name><input type=hidden name=ninye2[] value=$pid_amount />$patient_name</div>";//pt name
				echo "<div class=waiver_pt_bal><input type=hidden value='$pid_encrypt' /><a href='' class='link_color pt_statement_a'>$bal</a></div>";//balance
				echo "<div class='waiver_pt_amount  no_border_right'>$amount_waived</div>";//amount waived
				//check if we have extra comments apart from original
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select b.date_of_comment, b.comment, c.first_name, c.middle_name, c.last_name 
					from waiver_approval_communication b , users c
					where b.waiver_id=:waiver_id and b.user_id = c.id order by b.id";
				$placeholders2[':waiver_id']=$row['id'];
				$error2="Unable to get waiver comments";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
				echo "<div class='no_border_right tplan_procedure_row'>";		?>
				<div class='waiver_sub_header waiver_table_row2'><!--comment headers-->
						<div class='waiver_pt_date_comment'>Date</div> <!-- comment date -->
						<div class=waiver_pt_user>User</div> <!-- comment made by -->
						<div class='waiver_pt_comment  no_border_right'>Comment</div> <!-- comment  -->
				</div>
						<?php //now show newer comments
							foreach($s2 as $row2){ ?>
								<div class='waiver_table_row2 '>
									<div class=waiver_pt_date_comment><?php htmlout($row2['date_of_comment']); ?></div> <!-- comment date -->
									<div class=waiver_pt_user><?php ucfirst(htmlout("$row2[2] $row2[3] $row2[4]")); ?></div> <!-- comment made by -->
									<div class='waiver_pt_comment  no_border_right'><?php htmlout($row2['comment']); ?></div> <!-- comment  -->
								</div>							
							<?php
							
							}
				//new comment will go here ?>
					<div class='waiver_table_row2 '>
						<div class=waiver_pt_date_comment><?php echo date('Y-m-d'); ?></div> <!-- comment date -->
						<div class=waiver_pt_user><?php ucfirst(htmlout($_SESSION['logged_in_user_names'])); ?></div> <!-- comment made by -->
						<div class='waiver_pt_comment no_border_right'><?php echo "<textarea name=comment[] width=100%></textarea>"; ?></div> <!-- comment  -->
					</div>	<?php				
				echo "</div>"; //end 	 tplan_procedure_row
				echo "<div class='waiver_pt_action'><input type=hidden value='$id' name='ninye[]' />
					<select name=action[]>
						<option></option>";

						if(userHasSubRole($pdo,6)){//this is the approver
							echo "<option value='$accept'>Accept</option>
						<option value='$decline'>Decline</option>";
						}
						echo "<option value='$reply'>Reply</option>
					</select>
				</div>";//action taken
			echo "</div>"; //end 	 waiver_table_row2			
		}	
		echo "</div>"; //end 	 waiver_table
		echo "<div class='grid-100 '><br><input type=submit class=put_right value=Submit /><br></form></div>";
	}
	else{
		echo "<div class='grid-100 label'>There are no waiver payments pending approval.</div>";
	}
			 

?>
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>