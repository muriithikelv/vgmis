<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,64)){exit;}
echo "<div class='grid_12 page_heading'>RECORD EXPENSES</div>";
$user=$user_name=$var='';

//add expense
if( isset($_POST['token_er1']) and isset($_SESSION['token_er1']) and $_SESSION['token_er1']==$_POST['token_er1']){
			$_SESSION['token_er1']=$deducted='';
			$exit_flag=false;
			//ipdate epxnese types array
			get_expense_types($pdo);
			//check that expense class is not empty
			if(!$exit_flag and ( $_POST['expense_class']!='deducted' and $_POST['expense_class']!='non-deducted' )){
				$error_message="   Expense class was not set   ";
				$exit_flag=true;
			}
			if(!$exit_flag and $_POST['expense_class']=='deducted'){$deducted=1;}
			if(!$exit_flag and $_POST['expense_class']=='non-deducted'){$deducted=0;} 
			//echo "dd $deducted";exit;
			//check expense type
			if(!$exit_flag and $_POST['etype']==''){
				$error_message="   Expense type was not set   ";
				$exit_flag=true;
			}
			//check date
			if(!$exit_flag and $_POST['date']==''){
				$error_message="   Expense date was not set   ";
				$exit_flag=true;
			}
			//check cost
			if(!$exit_flag and $_POST['cost']==''){
				$error_message="   Expense cost was not set   ";
				$exit_flag=true;
			}
			
			//expense type
			if(!$exit_flag and $_POST['etype']!=''){
				$etype=html($encrypt->decrypt($_POST['etype']));
				if(!in_array($etype,$_SESSION['expense_type_array'])){
					$error_message="   Expense type was not set   ";
					$exit_flag=true;
					$var=html($_POST['etype']);
					$message="somebody tried to input $var as expense type for expenses";
					log_security($pdo,$message);
				}
			}
			
			//date
			if(!$exit_flag and $_POST['date']!=''){
					$date=explode('-',$_POST['date']);
					if(!checkdate( $date[1],$date[2],$date[0] )){
						$date=html($_POST['date']);
						$error_message="   $date is not a correct date format   ";
						$exit_flag=true;
						$security_log="somebody tried to input $date as date of expense";
						log_security($pdo,$security_log);		
					}
			}
		
			//check amount
			if(!$exit_flag and $_POST['cost']!=''){		
				//remove commas
				$amount=str_replace(",", "", $_POST["cost"]);
					//check if amount is integer
				if(!ctype_digit($amount)){$data=explode('.',$amount);
					$invalid_amount=html("$amount");
					if ( count($data) != 2 ){
						$error_message="Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;
					}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
						$error_message="Unable to save details as cost $invalid_amount is not a valid number. ";
						$exit_flag=true;
					}
				}
			}
			//echo "decuetd is $deducted";
			if(	!$exit_flag and $deducted==1 or $deducted==0){
					
					//insert expense 
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into expenses set when_added=:when_added, added_by=:added_by, expense_type=:expense_type, cost=:cost, 
						deducted_from_income=:deducted";
					$error="Unable to add expense";
					$placeholders[':when_added']=$_POST['date'];
					$placeholders[':added_by']=$_SESSION['id'];
					$placeholders[':expense_type']=$etype;
					$placeholders[':cost']=$amount;
					$placeholders[':deducted']=$deducted;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if($s){$success_message=" Expense  added ";}
						elseif(!$s){$error_message=" Unable to add expense  ";}			
				
			}
}

?>
	<div class="grid-100 margin_top ">
	<?php include  'response.php'; ?>
	<form action="" method="post" name="" id="">
			<div class='grid-10 '><label for="user" class="label">Expense Class</label></div>
			<div class='grid-20'><select name=expense_class><option></option>
				<option value='deducted'>Deducted from income</option>
				<option value='non-deducted'>Not deducted from income</option>
				</select>
			</div>
			
			<div class='grid-5 '><label for="user" class="label">Date</label></div>
			<div class='grid-10'><input type=text class=date_picker name=date /></div>
			
			<div class='grid-5 '><label for="user" class="label">Cost</label></div>
			<div class='grid-10'><input type=text  name=cost /></div>
			
			<div class=clear></div><br>
			
			<div class='grid-10 '><label for="user" class="label">Expense Type</label></div>
			<div class='grid-50'><select  name=etype><option></option>
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
			<div class='grid-10 prefix-10'>	<input type="submit"  value="Submit"/></div>	
			<?php $token = form_token(); $_SESSION['token_er1'] = "$token";  ?>
		<input type="hidden" name="token_er1"  value="<?php echo $_SESSION['token_er1']; ?>" />
			
			
			</form>
		<div class=clear></div><br>
		<!-- show todays expenses -->
		<?php
			$sql=$error=$s='';$placeholders=array();
			$sql = "select a.name, b.when_added, b.cost, c.first_name, c.middle_name, c.last_name, deducted_from_income 
					from expense_types a, expenses b , users c
					where b.when_added=curdate() and b.expense_type=a.id and c.id=b.added_by order by b.id";
			$error = "Unable to get expense types";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			if($s->rowCount() > 0){
				$count=0;
				echo "<table class='normal_table'><caption>Expense recorded today</caption><thead>
				<tr><th class=exp_count2></th><th class=exp_date2>DATE</th><th class=exp_name2>EXPENSE</th><th class=exp_cost2>COST</th>
				<th class=exp_creator2>ADDED BY</th><th class=exp_class2>EXPENSE CLASS</th></tr></thead><tbody>";
				foreach($s as $row){
					$count++;
					$date=html($row['when_added']);
					$name=html($row['name']);
					$user=html("$row[first_name] $row[middle_name] $row[last_name] ");
					$cost=number_format(html($row['cost']),2);
					if($row['deducted_from_income']==1){$deducted="Deducted from income ";}
					elseif($row['deducted_from_income']==0){$deducted="Not deducted from income ";}
					echo "<tr><td>$count</td><td>$date</td><td>$name</td><td>$cost</td><td>$user</td><td>$deducted</td></tr>";
				}
				echo "</tbody></table>";
				
			}
		
		
		?>

</div>
