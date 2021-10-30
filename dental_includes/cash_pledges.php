<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,96)){exit;}
echo "<div class='grid_12 page_heading'>UNFULFILLED PAYMENT PLEDGES</div>";
?>
<div class=grid-container>
<?php 
	//handle submission
	if(isset($_SESSION['token_cash_plege1a']) and isset($_POST['token_cash_plege1a']) and 
			$_POST['token_cash_plege1a']==$_SESSION['token_cash_plege1a'] and userHasRole($pdo,96)){
			$selects=$_POST['cp_action'];
			$new_pledge_date=$_POST['date_clear_bal'];
			$new_comments=$_POST['comment'];
			$pids=$_POST['ninye'];
			$n=count($selects);
			$i=0;
			//insert
			try{
			$pdo->beginTransaction();
				while($i < $n){
					$pid2=$encrypt->decrypt("$pids[$i]");
					$data=explode('#',"$pid2");
					$pid=$data[0];
					$balance=$data[1];
					if($selects[$i]=='new_pledge1'){
						//get old balabce record if any
						
						
						$sql=$error=$s='';$placeholders=array();
						$sql="select when_added, pid, date_to_clear, added_by , balance, comments from  balance_clearance_date  where pid=:pid";
						$error="Unable to get old pleges";
						$placeholders[':pid']=$pid;
						$s = select_sql($sql, $placeholders, $error, $pdo);
						
						//insert that into comments table
						foreach($s as $row){
							$sql=$error=$s='';$placeholders=array();
							$sql="insert into cash_pledge_comments  set pid=:pid,when_added=:when_added,
								pledge_date=:pledge_date, added_by=:added_by, balance=:balance, comments=:comments";
							$error="Unable to record old balance pledge";
							$placeholders[':pid']=$row['pid'];
							$placeholders[':when_added']=$row['when_added'];
							$placeholders[':pledge_date']=$row['date_to_clear'];
							$placeholders[':added_by']=$row['added_by'];
							$placeholders[':balance']=$row['balance'];
							$placeholders[':comments']=$row['comments'];
							$s = insert_sql($sql, $placeholders, $error, $pdo);
						}
						
						//delete old balabce record if any
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from  balance_clearance_date  where pid=:pid";
						$error="Unable to get old pleges";
						$placeholders[':pid']=$pid;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						

						$sql=$error=$s='';$placeholders=array();
						$sql="insert into balance_clearance_date  set when_added=now(),
							date_to_clear=:date_to_clear, pid=:pid, added_by=:added_by ,balance=:balance, comments=:comments";
						$error="Unable to record date balance will be cleared";
						$placeholders[':date_to_clear']="$new_pledge_date[$i]";
						$placeholders[':pid']=$pid;
						$placeholders[':added_by']=$_SESSION['id'];
						$placeholders[':balance']=$balance;
						$placeholders[':comments']="$new_comments[$i]";
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}
					elseif($selects[$i]=='delete1'){
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from  balance_clearance_date  where pid=:pid";
						$error="Unable to get old pleges";
						$placeholders[':pid']=$pid;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
						
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from  cash_pledge_comments  where pid=:pid";
						$error="Unable to get old pleges";
						$placeholders[':pid']=$pid;
						$s = insert_sql($sql, $placeholders, $error, $pdo);
					}
					$i++;
				}	
					if($s){$tx_result = $pdo->commit();}
					elseif(!$s){$pdo->rollBack();$tx_result=false;}
					if($tx_result){$message="good#CashChanges saved";}
				}
				catch (PDOException $e)
				{
				$pdo->rollBack();
					$message="bad#Unable to save changes  ";
				}
				
				$data=explode('#',"$message");
				if($data[0]=='good'){echo "<div class='success_response'>$data[1]</div>";}
				elseif($data[0]=='bad'){echo "<div class='error_response'>$data[1]</div>";}
			
	}
	
		$sql=$error=$s='';$placeholders=array();
		$sql="select a.when_added, a.date_to_clear,concat(b.first_name,' ',b.middle_name,' ',b.last_name)
			as patient_names, b.patient_number, concat(c.first_name,' ',c.middle_name,' ',c.last_name) as added_by, a.balance,
			b.mobile_phone,b.biz_phone,datediff(curdate(), date_to_clear) as days_passed,a.pid, a.comments
			from balance_clearance_date a force index(date_to_clear), patient_details_a b, users c where date_to_clear <= now() and 
			a.pid=b.pid and a.added_by =c.id	order by days_passed		";
		$error="Unable to get unfulfilled payment pledges";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			$count=$total=0;
			$today=date('Y-m-d');
			echo "<br><div class='grid-100 div_shower44'></div>"; ?>
			<form action="" method="POST"  name="" id="" class="">
				<?php $token = form_token(); $_SESSION['token_cash_plege1a'] = "$token"; ?>
				<input type="hidden" name="token_cash_plege1a"  value="<?php echo $_SESSION['token_cash_plege1a']; ?>" /> 
			<?php	
			echo "<table class='rowspan_table'><caption>Unpayed cash pledges due on or before $today</caption><thead>
			<tr><th class=ucp_count></th><th class=ucp_pt>PATIENT NAME</th>
			<th class=ucp_pt_num>PATIENT<br>NUMBER</th><th class=ucp_mobile>MOBILE No.</th>
			<th class=ucp_biz>BUSINESS No.</th><th class=ucp_bal>BALANCE</th>
			<th class=ucp_date_due>DATE DUE</th><th class=ucp_day_past>DAYS<br>PAST</th><th class=ucp_pledge_date>PLEDGE<br>DATE</th>
			<th class=ucp_added_by>RECORDED BY</th><th class=ucp_comment>COMMENTS</th>
			</tr></thead>";
			foreach($s as $row){
				//get historical comments
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select a.when_added, a.pledge_date,concat(b.first_name,' ',b.middle_name,' ',b.last_name)
					as patient_names, b.patient_number, concat(c.first_name,' ',c.middle_name,' ',c.last_name) as added_by, a.balance,
					datediff(curdate(), pledge_date) as days_passed,a.pid, a.comments
					from cash_pledge_comments a , patient_details_a b, users c where a.pid=:pid and 
					a.pid=b.pid and a.added_by =c.id	order by a.id		";
				$error2="Unable to get unfulfilled payment pledges";
				$placeholders2[':pid']=$row['pid'];
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
				$row_count=2;
				if($s2->rowCount() >0){$row_count = $s2->rowCount() + 2;}
				
				$count++;
				$pledge_date=html($row['when_added']);
				$added_by=ucfirst(html($row['added_by']));
				$comment=html($row['comments']);
				$patient=html($row['patient_names']);
				$patient_number=html($row['patient_number']);
				$date_to_clear=html($row['date_to_clear']);
				$mobile=html($row['mobile_phone']);
				$biz=html($row['biz_phone']);
				$days_past=html($row['days_passed']);
				$bal=html(number_format($row['balance'],2));
				$total = $total + html($row['balance']);
				$pid_encrypt=$encrypt->encrypt("$row[pid]");
				$ninye=$encrypt->encrypt(html("$row[pid]#$row[balance]"));
				/*//now get patient self balance
				$pid_encrypt=$encrypt->encrypt("$row[pid]");
				$result=show_pt_statement_brief($pdo,$pid_encrypt,$encrypt);
				$result=str_replace(",", "", "$result");
				$data=explode('#',"$result");
				if($data[1] == 0){$bal="0.00";}		
				elseif($data[1] > 0){
					$total = $total + $data[1];
					$bal=number_format($data[1],2);
				}	
				elseif($data[1] < 0){
					$total = $total + $data[1];
					$data[1]=str_replace("-", "", "$data[1]");
					$bal="Credit ".number_format($data[1],2);
				}			
				$bal=html("$bal");*/
				
				echo "<tbody><tr><td rowspan=$row_count>$count</td><td rowspan=$row_count>$patient</td><td rowspan=$row_count>$patient_number</td>
					<td rowspan=$row_count>$mobile</td><td rowspan=$row_count>$biz</td><td>
				<input type=hidden value='$pid_encrypt' /><a href='' class='link_color pt_statement_a'>$bal</a></td>
				<td>$date_to_clear</td><td>$days_past</td><td>$pledge_date</td><td>$added_by</td><td>$comment</td></tr>";
				

				foreach($s2 as $row2){
					$pledge_date2=html($row2['when_added']);
					$added_by2=ucfirst(html($row2['added_by']));
					$comment2=html($row2['comments']);
					$date_to_clear2=html($row2['pledge_date']);
					$days_past2=html($row2['days_passed']);
					$bal2=html(number_format($row2['balance'],2));
					//$total = $total + html($row['balance']);
					//$pid_encrypt=$encrypt->encrypt("$row[pid]");
					echo "<tr><td>$bal2</td>
						<td>$date_to_clear2</td><td>$days_past2</td><td>$pledge_date2</td><td>$added_by2</td><td>$comment2</td></tr>";
				}
				//show action
				echo "<tr ><td colspan=6><div class='grid-30 '>Select Action</div>
										<div class='grid-70'><select name=cp_action[] class='cp_action'>
											<option></option>
											<option value='delete1'>Delete pledge</option>
											<option value='new_pledge1'>New payment date</option>
										</select></div>
							<div class='grid-30 new_pledge_date1a'>New payment date </div>
							<div class='grid-70 new_pledge_date1a'><input type=text name=date_clear_bal[] class='date_picker_no_past new_cp_date'/></div>	
							
							<div class='grid-30 new_pledge_date1a'>Comment</div>
							<div class='grid-70 new_pledge_date1a'><textarea name=comment[] width=100%></textarea><input type=hidden name=ninye[] value='$ninye' /></div>
							
							
				</td></tr></tbody>";
				
			}
			echo "<tr class=total_background><td colspan=5>TOTAL</td><td>".number_format($total,2)."</td><td colspan=5>&nbsp;</td></tr></tbody></table>";
			echo "<span  class=put_right><input type=submit value=Submit /></span></form>";
		}
		else{echo "<label  class=label>There are no unfulfilled payment pledges due today</label>";}
		
		
	?>
	

</div>