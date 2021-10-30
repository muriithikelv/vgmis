<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,24)){exit;}
echo "<div class='grid_12 page_heading'>X-RAY TYPES</div>";
$user=$user_name=$var='';

//add x-ray type
if( isset($_SESSION['token']) and isset($_POST['token']) and $_SESSION['token']==$_POST['token']){
	$n=$encrypt->decrypt($_POST['nisiana']);
	$_SESSION['token']='';
	//$xray_name=$_POST['old_xray'];
	$i=1;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i <= $n){	
				//escape for empty fileds
				if(!isset($_POST["xray_name$i"]) or $_POST["xray_name$i"]==''){$i++; continue;}
				
					//check if cost is a valid number
					//remove commas if they were used for formating
					$xray_cost=str_replace(",", "", $_POST["xray_cost$i"]);
					if(isset($xray_cost) and $xray_cost!='' and !ctype_digit($xray_cost)){
						//check if it has only 2 decimal places
						$data=explode('.',$xray_cost);
						if ( count($data) != 2 ){
							$xray_cost=html("$xray_cost");
							$error_message=" Unable to save changes as $xray_cost is not a valid number ";
							$exit_flag=true;
							break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$xray_cost=html("$xray_cost");
							$error_message=" Unable to save changes as $xray_cost is not a valid number ";
							$exit_flag=true;
							break;
						}
					}
					
				//check thata the xray is not entered twice
				$sql=$error=$s='';$placeholders=array();
				$sql="select name from teeth_and_xray_types where upper(name)=:name";
				$error="Unable to get xrays name";
				$placeholders[':name']=strtoupper($_POST["xray_name$i"]);
				$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				if($s->rowCount()>0){
					$exit_flag=false;
					$name=html($_POST["xray_name$i"]);
					$error_message=" Unable to add X-RAY type $name as it already exists";
					break;
				}
				//check if yes no is set
				if(!isset($_POST["tooth_specify$i"])){
					$exit_flag=false;
					$error_message=" Please specify if a tooth need's to be specified for each X-Ray Type added.";
					break;
				}

				
					//insert insurance value
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into teeth_and_xray_types set name=:name, all_teeth=:all_teeth , cost=:cost";
					$error="Unable to add xray type";
					$placeholders[':name']=$_POST["xray_name$i"];
					$placeholders[':all_teeth']=$_POST["tooth_specify$i"];
					$placeholders[':cost']=$xray_cost;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					$i++;
			}
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$success_message=" New X-RAY type added   ";}
			//elseif(!$tx_result){$error_message="   Unable to edit X-RAYS  ";}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="    Unable to edit X-RAYS   ";
	}			
						
				
}

//edit x-ray type
if( isset($_SESSION['token2']) and isset($_POST['token2']) and $_SESSION['token2']==$_POST['token2']){
	$_SESSION['token2']='';
	//save entries
	$n=$encrypt->decrypt($_POST['nisiana']);
	//$xray_id=$_POST['ninye'];
	//$xray_name=$_POST['old_xray'];
	$i=1;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i <= $n){
			
					//check if cost is a valid number
					//remove commas if they were used for formating
					$old_xray_cost=str_replace(",", "", $_POST["old_xray_cost$i"]);
					if(isset($old_xray_cost) and $old_xray_cost!='' and !ctype_digit($old_xray_cost)){
						//check if it has only 2 decimal places
						$data=explode('.',$old_xray_cost);
						if ( count($data) != 2 ){
							$old_xray_cost=html("$old_xray_cost");
							$error_message=" Unable to save changes as $old_xray_cost is not a valid number ";
							$exit_flag=true;
							break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$old_xray_cost=html("$old_xray_cost");
							$error_message=" Unable to save changes as $old_xray_cost is not a valid number ";
							$exit_flag=true;
							break;
						}
					}
					
					//check if yes no is set
					if(!isset($_POST["tooth_specify$i"])){
						$exit_flag=false;
						$error_message=" Please specify if a tooth need's to be specified for each X-Ray Type.";
						break;
					}
					else{
						$sql=$error=$s='';$placeholders=array();
						$sql="update teeth_and_xray_types set name=:name, all_teeth=:all_teeth ,cost=:cost where id=:id";
						$error="Unable to edit xrays";
						$placeholders[':name']=$_POST["old_xray$i"];
						$placeholders[':all_teeth']=$_POST["tooth_specify$i"];
						$placeholders[':cost']=$old_xray_cost;
						$placeholders[':id']=$encrypt->decrypt($_POST["ninye$i"]);
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					}
					//if(!$s and $exit_flag){$exit_flag=false;}		
					$i++;
			}
		
				//now delete entries
				
			if($exit_flag and isset($_POST['del'])){
				$n=count($_POST['del']);
				$xray_id=$_POST['del'];
				$i=0;
				while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from teeth_and_xray_types  where id=:id";
						$error="Unable to delete xrays";
						$placeholders[':id']=$encrypt->decrypt($xray_id[$i]);
					//	$s = 	insert_sql($sql, $placeholders, $error, $pdo);	first chck if the compnay has patients
						$i++;
				}	
			}
			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$success_message=" X-RAYS Edited  ";}
			//elseif(!$tx_result){$error_message="   Unable to edit X-RAYS  ";}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="    Unable to edit X-RAYS   ";
	}
		
}
?>
	<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
	<input type=button value='Add New X-RAY type' class='button_style add_xray'  />
	<div  id=patient_relationship_form_div title="New X-RAY Type">		
		
<?php
	
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='add_axray'><caption>X-RAYS DONE</caption><thead>
		<tr><th class=add_axray_rel_count></th><th class=add_xray_name>X-RAY NAME</th><th class=add_xray_cost>COST</th>
		<th class=add_xray_specify>SPECIFY TOOTH</th><th class=add_axray_no_specify>DON'T SPECIFY TOOTH</th></tr></thead><tbody>";
		while($count <= 5){
			$count++;
			$name=html($row['name']);
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td><input type=text name=xray_name$count class=input_in_table_cell  />
			</td><td><input type=text name=xray_cost$count  /></td>
			<td><input type=radio name=tooth_specify$count value='yes' /></td><td><input type=radio name=tooth_specify$count value='no' /></td>
			</tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token'] = "$token"; 
		$nisiana=$encrypt->encrypt($count);
		echo "<input name=nisiana type=hidden value=$nisiana />";
		echo "<input type=hidden name=token  value='$_SESSION[token]' /><input type=submit  value='Add X-RAY' /></form><div class=clear></div><br>";
	

?>


	</div>		
		
	
<?php
	//now show current patien relationships
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from teeth_and_xray_types  order by name";
	$error="Unable to select xray types";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='add_axray'><caption>X-RAYS DONE</caption><thead>
		<tr><th class=add_axray_rel_count></th><th class=add_xray_name>X-RAY NAME</th><th class=add_xray_cost>COST</th>
		<th class=add_xray_specify>SPECIFY TOOTH</th><th class=add_axray_no_specify>DON'T SPECIFY TOOTH</th><th class=add_axray_del>DELETE</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['name']);
			$all_teeth=html($row['all_teeth']);
			$yes=$no='';
			if($all_teeth=="yes"){$yes=" checked ";}
			elseif($all_teeth=="no"){$no=" checked ";}	
			if($row['cost'] > 0){$cost=html($row['cost']);}
			else{$cost='';}
			
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td><input type=text name=old_xray$count class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye$count value='$val' /></td><td><input type=text name=old_xray_cost$count value='$cost' /></td>
			<td><input type=radio name=tooth_specify$count value='yes' $yes /></td><td><input type=radio name=tooth_specify$count value='no' $no /></td>
			<td><input type=checkbox name=del[] value='$val' /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token2'] = "$token"; 
		$nisiana=$encrypt->encrypt($count);
		echo "<input name=nisiana type=hidden value=$nisiana />";
		echo "<input type=hidden name=token2  value='$_SESSION[token2]' /><input type=submit  value='Submit Changes' /></form><br>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
