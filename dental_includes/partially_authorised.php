<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,112)){exit;}
echo "<div class='grid_12 page_heading'>PARTIALLY AUTHORISED INVOICES</div>"; ?>
<div class="grid-100 margin_top">
<?php
		if(isset($_POST['token_aia2']) and isset($_SESSION['token_aia2']) and $_POST['token_aia2']==$_SESSION['token_aia2'] )
		{
			$_SESSION['token_aia2']='';
			$ninye=$_POST['ninye'];
			$comment=$_POST['comment'];
			if(isset($_POST['inv_action'])){$action=$_POST['inv_action'];}
			$i=0;
			$n=count($ninye);
			try{
				$pdo->beginTransaction();
				while($i < $n){
					//check if chat is ending
					if(isset($action)){
						if($action[$i]=='end_chat'){
							$comment_id=$encrypt->decrypt("$ninye[$i]");
							$sql=$error=$s='';$placeholders=array();
							$sql="update invoice_admin_approval set status=1 where id=:communication_id";
							$error="Unable to end invoice admin comment";
							$placeholders[':communication_id']=$comment_id;
							$s = insert_sql($sql, $placeholders, $error, $pdo);	
						}
					}
								
					if($comment[$i]==''){$i++;continue;}
					elseif($comment[$i]!=''){
						$comment_id=$encrypt->decrypt("$ninye[$i]");
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into invoice_admin_approval_communication set communication_id=:communication_id,
							date_of_comment=now(),
							comment=:comment,
							user_id=:user_id";
						$error="Unable to add invoice admin comment";
						$placeholders[':comment']="$comment[$i]";
						$placeholders[':communication_id']=$comment_id;
						$placeholders[':user_id']=$_SESSION['id'];
						$s = insert_sql($sql, $placeholders, $error, $pdo);	
					}
					$i++;
				}
				
				
				$tx_result = $pdo->commit();
				if($tx_result){$message="good#Changes saved";}
				}
				catch (PDOException $e)
				{
				$pdo->rollBack();
				$message="bad#Unable to save changes ";
				}
				$data=explode('#',"$message");
				if($data[0]=='good'){echo "<div class='success_response'>$data[1]</div>";}
				elseif($data[0]=='bad'){echo "<div class='error_response'>$data[1]</div>";}
				echo "<br>";
		
		}	
		partially_approved_invoices2($pdo,$encrypt);
		?>
</div>