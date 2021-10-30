<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,94)){exit;}
echo "<div class='grid_12 page_heading'>EXPENSES REPORT</div>";
$user=$user_name=$var='';

//add expense
if( isset($_POST['token_er1a']) and isset($_SESSION['token_er1a']) and $_SESSION['token_er1a']==$_POST['token_er1a']){
			$_SESSION['token_er1a']=$deducted='';
			$exit_flag=false;
			//ipdate epxnese types array
			get_expense_types($pdo);
			//check that expense class is not empty
			if(!$exit_flag and  !isset($_POST['expense_class']) or $_POST['expense_class']=='' ){
				$result_message="   Expense class was not set   ";
				$result_class="error_response";
				$exit_flag=true;
			}
			
			//check expense type
			if(!$exit_flag and !isset($_POST['etype']) or $_POST['etype']==''){
				$result_message="   Expense type was not set   ";
				$result_class="error_response";
				$exit_flag=true;
			}
			//check if date is selcted
			if(!$exit_flag and !isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']==''  ){	
					$result_class="error_response";
					$result_message="Please specify the date range for the search criteria";
					$exit_flag=true;
			}

			if(!$exit_flag){
				$etype_criteria=$expense_class_criteria ='';
				$sql=$error=$s='';$placeholders=array();
				//expense class criteria
				if($_POST['expense_class']!='all'){
					$expense_class=$encrypt->decrypt($_POST['expense_class']);
					$expense_class_criteria = " and b.deducted_from_income=:expense_class ";
					$placeholders[':expense_class']=$expense_class;
					
				}
				
				//expense type criteria
				if($_POST['etype']!='all'){
					$etype=$encrypt->decrypt($_POST['etype']);
					$etype_criteria = " and b.expense_type=:expense_type  ";
					$placeholders[':expense_type']=$etype;
					
				}				
				
				
				$sql = "select a.name, b.when_added, b.cost, c.first_name, c.middle_name, c.last_name, deducted_from_income 
						from expense_types a, expenses b , users c
						where b.when_added >=:from_date and b.when_added <=:to_date and b.expense_type=a.id and c.id=b.added_by 
						 $expense_class_criteria $etype_criteria order by b.id";
				$error = "Unable to get expense incurred";
				$placeholders[':from_date']=$_POST['from_date'];	
				$placeholders[':to_date']=$_POST['to_date'];	
				$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				if($s->rowCount() > 0){
					$from_date=html($_POST['from_date']);
					$to_date=html($_POST['to_date']);
					$count=0;$total=0;
					echo "<table class='normal_table'><caption>Expense incurred between  $from_date and $to_date</caption><thead>
					<tr><th class=exp_count2></th><th class=exp_date2>DATE</th><th class=exp_name2>EXPENSE</th><th class=exp_cost2>COST</th>
					<th class=exp_creator2>ADDED BY</th><th class=exp_class2>EXPENSE CLASS</th></tr></thead><tbody>";
					foreach($s as $row){
						$count++;
						$date=html($row['when_added']);
						$name=html($row['name']);
						$user=html("$row[first_name] $row[middle_name] $row[last_name] ");
						$cost=number_format(html($row['cost']),2);
						$total = $total + $cost;
						if($row['deducted_from_income']==1){$deducted="Deducted from income ";}
						elseif($row['deducted_from_income']==0){$deducted="Not deducted from income ";}
						echo "<tr><td>$count</td><td>$date</td><td>$name</td><td>$cost</td><td>$user</td><td>$deducted</td></tr>";
					}
					echo "<tr class=total_background><td colspan=3>Total value of expenses</td><td>".number_format($total,2)."</td><td colspan=2>&nbsp;</td></tr>";
					echo "</tbody></table>";
					
				}
				else{echo "<label  class=label>There are no expenses for the selected criteria</label>";}
				exit;
		
					
			
			
			}
			elseif($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}

}

?>
	<div class="grid-100 margin_top ">
	<?php include  'response.php'; ?>
	<form action="" method="post" name="" id="">
			<div class='grid-20 '><label for="user" class="label">Select Expense Class</label></div>
			<?php
				$deducted_from_income=$encrypt->encrypt("1");
				$not_deducted=$encrypt->encrypt("0");
				echo "<div class='grid-20'><select name=expense_class><option value='all'>All Expense Classes</option>
					<option value='$deducted_from_income'>Deducted from income</option>
					<option value='$not_deducted'>Not deducted from income</option>
					</select>";
				?>
			</div>
			<div class=clear></div><br>
			<div class='grid-20 '><label for="user" class="label">Expense Type</label></div>
			<div class='grid-50'><select  name=etype><option value='all'>All Expense Types</option>
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql = "select id,name from expense_types where deleted=0 order by name";
					$error = "Unable to expense types";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);	
					foreach($s as $row){
						$name=html($row['name']);
						$val=$encrypt->encrypt(html($row['id']));
						echo "<option value='$val'>$name</option>";
					}
						
				?>
				</select>
			</div>	
			
			<div class=clear></div><br>			
			<div class='grid-20 '><label for="user" class="label">Expense incurred between</label></div>
			<div class='grid-10'><input type=text class=date_picker name=from_date /></div>
			
			<div class='grid-5 '><label for="user" class="label">And</label></div>
			<div class='grid-10'><input type=text  name=to_date class=date_picker /></div>
			
			
			

			
			<div class='grid-10'>	<input type="submit"  value="Submit"/></div>	
			<?php $token = form_token(); $_SESSION['token_er1a'] = "$token";  ?>
		<input type="hidden" name="token_er1a"  value="<?php echo $_SESSION['token_er1a']; ?>" />
			
			
			</form>

</div>
