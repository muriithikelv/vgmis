<?php
if(!isset($_SESSION))
{
session_start();
}
require_once '../../inventory_includes/db.inc.php';
require_once '../../inventory_includes/helpers.inc.php'; 
require_once '../../inventory_includes/encryption.php'; 
require_once  '../../inventory_includes/access.inc.php'; ?>
<link rel="stylesheet" type="text/css" media="all" href="../inventory_css/style1.css" />
<link rel="stylesheet" type="text/css" media="print" href="../inventory_css/print.css" />
<?php

$encrypt = new Encryption();
//this is for printing cash payments
if(isset($_GET['v1']) and $_GET['v1']!=''){
	$receipt_number = html($encrypt->decrypt($_GET['v1']));
	echo "<div class=print_font>";
	if($receipt_number!=''){
		//get goods for this receipt
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT c.selling_id,c.transaction_id, c.date_sold, c.discount_amount, c.tax_rate_used, b.user_name 
		from goods_sold_payments a, users b, goods_sold c where a.selling_id = c.selling_id and b.id=c.sold_by and a.receipt_number=:receipt_number";
		$placeholders[':receipt_number']="$receipt_number";
		$error="Unable to get receipt details";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() >0){
			foreach($s as $row){
				$transaction_id=html("$row[transaction_id]");
				$date_sold=html("$row[date_sold]");
				$discount_amount=html("$row[discount_amount]");
				$tax_rate_used=html("$row[tax_rate_used]");
				$user_name=html("$row[user_name]");
				$selling_id=html($row['selling_id']);
			}	
			//start printing receipt
			//print vat/kra pin
			echo "<table width=100%>
					
					<tr><td class=push_centre>$_SESSION[company_name]</td></tr>
					<tr><td class=push_centre>$_SESSION[company_p_o_box]</td></tr>
					<tr><td class=push_centre>$_SESSION[kra_number]</td></tr>
					<tr><td class=push_centre>VAT #: $_SESSION[vat_number]</td></tr>
					<tr><td class=push_centre>PIN: $_SESSION[pin_number]</td></tr>
					<tr><td class=push_left>Transaction # $transaction_id</td></tr>
					<tr><td class=push_left>Date: $date_sold</td></tr>
				</table>";
			//print googds sold
			echo "<table width=100% class=print_table>
				 <tr class=tr_border><td width=70%>ITEM</td><td width=30% class=push_right>AMOUNT</td></tr>";
				//now get goods sold
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT c.name, c.full_product_id, b.quantity, b.unit_price  from  goods_sold_details b, products c where b.selling_id = :selling_id and 
				b.product_id=c.id ";
			$placeholders[':selling_id']=$selling_id;
			$error="Unable to get receipt details";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() >0){
				$sub_total=0;
				foreach($s as $row){
					$product_id=html($row['full_product_id']);
					$product_name=html($row['name']);
					$quantity=html($row['quantity']);
					$unit_price=html($row['unit_price']);
					$selling_price=$quantity * $unit_price;
					echo "<tr><td>$product_id</td><td class=push_right>$quantity * $unit_price</td></tr>";
					echo "<tr><td>$product_name</td><td class=push_right>$selling_price</td></tr>";
					$sub_total = $sub_total + $selling_price;
				}
				echo "<tr class=sub_total_font><td>SUBTOTAL</td><td class=push_right>$sub_total</td></tr>";
				if($discount_amount > 0){
					echo "<tr><td>DISCOUNT</td><td class=push_right>$discount_amount</td></tr>";
					$sub_total = $sub_total - $discount_amount;
				}
				$vat= number_format((($tax_rate_used/100) * $sub_total),2);
				echo "<tr><td>VAT $tax_rate_used %</td><td class=push_right>$vat</td></tr>";
				$total = $sub_total + $vat;
				echo "<tr class=total_font><td>TOTAL</td><td class=push_right>$total</td></tr>";
				echo "<tr><td colspan=2>&nbsp;</td></tr>";
				echo "<tr><td colspan=2>&nbsp;</td></tr>";
				echo "<tr><td colspan=2>Thank you for shopping with us.</td></tr>";
				echo "<tr><td colspan=2>You were served by $user_name.</td></tr>";
				echo "<tr><td colspan=2>RECEIPT # $receipt_number</td></tr>";
				echo "</table>";
			}
		}
		else{echo "xx";}
	}
}
//this is for prining invoice payments
if(isset($_GET['v2']) and $_GET['v2']!=''){
	$receipt_number = html($encrypt->decrypt($_GET['v2']));
	echo "<div class=print_font>";
	if($receipt_number!=''){
		//get goods for this receipt
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT c.selling_id,c.transaction_id, a.date_received, c.invoice_number, b.user_name ,c.tax_rate_used
		from goods_sold_payments a, users b, goods_sold c where a.selling_id = c.selling_id and b.id=c.sold_by and a.receipt_number=:receipt_number";
		$placeholders[':receipt_number']="$receipt_number";
		$error="Unable to get receipt details";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() >0){
			$count=0;
			foreach($s as $row){
				$transaction_id=html("$row[transaction_id]");
				$date_received=html("$row[date_received]");
				$user_name=html("$row[user_name]");
				$selling_id=html("$row[selling_id]");
				$invoice_number=html("$row[invoice_number]");
				$tax_rate_used=html($row['tax_rate_used']);
			//}	
			//start printing receipt
			//print vat/kra pin
			if($count==0){
				echo "<table width=100%>
						<tr><td class=push_centre>$_SESSION[company_name]</td></tr>
						<tr><td class=push_centre>$_SESSION[company_p_o_box]</td></tr>
						<tr><td class=push_centre>$_SESSION[kra_number]</td></tr>
						<tr><td class=push_centre>VAT #: $_SESSION[vat_number]</td></tr>
						<tr><td class=push_centre>PIN: $_SESSION[pin_number]</td></tr>
						
						<tr><td class=push_left>Date: $date_received</td></tr>
					</table>";//<tr><td class=push_left>Transaction # $transaction_id</td></tr>
			}
			//print googds sold
			echo "<table width=100% class=print_table>
				 <tr class=tr_border><td class=customer_invoice_pay_inv_no>INVOICE NUMBER</td><td class='push_right customer_invoice_pay_amount'>AMOUNT</td></tr>";
				//now get goods sold
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT amount_paid from goods_sold_payments where receipt_number = :receipt_id ";
			$placeholders[':receipt_id']="$receipt_number";
			$error="Unable to get receipt details";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() >0){
				$total=0;
				foreach($s as $row){
					$amount_paid=html($row['amount_paid']);
					echo "<tr><td customer_invoice_pay_inv_no>$invoice_number</td>
							  <td class='push_right customer_invoice_pay_amount'>".number_format($amount_paid,2)."</td></tr>";
					$total = $total + $amount_paid;
				}
				echo "<tr class=total_font><td>TOTAL</td><td class='push_right customer_invoice_pay_amount'>".number_format($total,2)."</td></tr>";

				$vat= $total - ($total / ((100 + $tax_rate_used)/100));
				
				echo "<tr><td>VAT AMT</td><td class='push_right customer_invoice_pay_amount'>".number_format($vat,2)."</td></tr>";
				echo "<tr><td colspan=2>&nbsp;</td></tr>";
				echo "<tr><td colspan=2>&nbsp;</td></tr>";
				echo "<tr><td colspan=2>Thank you for shopping with us.</td></tr>";
				echo "<tr><td colspan=2>You were served by $user_name.</td></tr>";
				echo "<tr><td colspan=2>RECEIPT # $receipt_number</td></tr>";
				echo "</table>";
			}
			$count++;
			}
		}
		else{echo "xx";}
	}
}
echo "</div>";
?>
