<?php
/*
if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,100)){exit;}
echo "<div class='grid_12 page_heading'>DELETE PAYMENT</div>";//check if this guy is a doctor
?>
<div class='grid-container completion_form'>
<?php
if(isset($_SESSION['token_de1']) and isset($_POST['token_de1']) and $_POST['token_de1']==$_SESSION['token_de1']){
	$_SESSION['token_de1']='';
	
		//check dates
		if(!isset($_POST['from_date']) or !isset($_POST['to_date']) or $_POST['from_date']=='' and $_POST['to_date']==''){
			echo "<div class='error_response'>Please ensure that the date range is correctly specified</div>";
		}
		else{
			get_expense_types($pdo);
			$sql=$error=$s='';$placeholders=array();
			$sql = "select a.name, b.when_added, b.cost, c.first_name, c.middle_name, c.last_name, deducted_from_income, b.id 
					from expense_types a, expenses b , users c
					where b.when_added >=:from_date and b.when_added <=:to_date and b.expense_type=a.id and c.id=b.added_by 
					 order by b.id";
			$error = "Unable to get expense incurred for deletion";
			$placeholders[':from_date']=$_POST['from_date'];	
			$placeholders[':to_date']=$_POST['to_date'];	
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			if($s->rowCount() > 0){
				$from_date=html($_POST['from_date']);
				$to_date=html($_POST['to_date']);
				$count=0;$total=0; ?>
				<form class='' action="" method="POST" enctype="" name="" id="">
					<?php $token = form_token(); $_SESSION['token_de2'] = "$token";  ?>
					<input type="hidden" name="token_de2"  value="<?php echo $_SESSION['token_de2']; ?>" /><?php
				echo "<table class='normal_table'><caption>Expense incurred between  $from_date and $to_date</caption><thead>
				<tr><th class=exp_count22></th><th class=exp_date22>DATE</th><th class=exp_name22>EXPENSE</th><th class=exp_cost22>COST</th>
				<th class=exp_creator22>ADDED BY</th><th class=exp_class22>EXPENSE CLASS</th><th class=exp_del22>DELETE</th></tr></thead><tbody>";
				foreach($s as $row){
					$count++;
					$date=html($row['when_added']);
					$name=html($row['name']);
					$user=html("$row[first_name] $row[middle_name] $row[last_name] ");
					$cost=number_format(html($row['cost']),2);
					$val=$encrypt->encrypt("$row[id]");
					//$total = $total + $cost;
					if($row['deducted_from_income']==1){$deducted="Deducted from income ";}
					elseif($row['deducted_from_income']==0){$deducted="Not deducted from income ";}
					echo "<tr><td>$count</td><td>$date</td><td>$name</td><td>$cost</td><td>$user</td><td>$deducted</td>
						<td><input type=checkbox name=del_expense[] value=$val /></td></tr>";
				}
			//	echo "<tr class=total_background><td colspan=3>Total value of expenses</td><td>".number_format($total,2)."</td><td colspan=2>&nbsp;</td></tr>";
				echo "</tbody></table>
							<div class='grid-100'><input class=put_right type=submit  value=Submit /></div>
					</form>";
			}
			else{echo "<label  class=label>There are no expenses for the selected criteria</label>";}
			exit;			
		}

	
}
//[erform actual deletion
if(isset($_SESSION['token_de2']) and isset($_POST['token_de2']) and $_POST['token_de2']==$_SESSION['token_de2']){
	$_SESSION['token_de2']='';
	$i=$n=0;
	if(isset($_POST['del_expense'])){
		$expense_id=$_POST['del_expense'];
		$n=count($expense_id);
		try{
				$pdo->beginTransaction();
					while($i < $n){
						$del_expense_id=$encrypt->decrypt("$expense_id[$i]");
						//copy record
						$sql=$error=$s='';$placeholders=array();
						$sql="select * from expenses where id=:id";
						$error="Unable to get expenses for deletion";
						$placeholders[':id']=$del_expense_id;
						$s = select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							//insert into deletion table
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into deleted_expenses
							set id=:id, when_added=:when_added,
							added_by=:added_by,
							expense_type=:expense_type,
							cost=:cost,
							deducted_from_income=:deducted_from_income,
							deleted_by=:deleted_by,
							when_deleted=now()";
							$error2="Unable to delete  expenses 1";
							$placeholders2[':id']=$row['id'];
							$placeholders2[':when_added']=$row['when_added'];
							$placeholders2[':added_by']=$row['added_by'];
							$placeholders2[':expense_type']=$row['expense_type'];
							$placeholders2[':cost']=$row['cost'];
							$placeholders2[':deducted_from_income']=$row['deducted_from_income'];
							$placeholders2[':deleted_by']=$_SESSION['id'];
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);	
						}
						
						//now dleete the expense
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from expenses where id=:id";
						$error="Unable to get delete expenses for deletion";
						$placeholders[':id']=$del_expense_id;
						$s = insert_sql($sql, $placeholders, $error, $pdo);						
						$i++;
					}
				if($i> 0){
					$tx_result=$pdo->commit();
					if($tx_result ){echo "<div class='success_response'>Expenses deleted</div>";}
				}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		
		}
	}
	else{echo "<div class='error_response'>No expense was selected for deletion</div>";}
		
		
}
?>

<form class='' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_de1'] = "$token";  ?>
		<input type="hidden" name="token_de1"  value="<?php echo $_SESSION['token_de1']; ?>" />

		<!-- by date range-->
			<div class='grid-30'><label for="user" class="label">Select expenses incurred between this date </label></div>
			<div class='grid-10 '><input type=text name=from_date class=date_picker /></div>
			<div class='grid-10'><label for="user" class="label">And this date</label></div>
			<div class='grid-10 '><input type=text name=to_date class=date_picker /></div>
			<div class='grid-10'><input type=submit  value=Submit /></div>
</form>
</div>

