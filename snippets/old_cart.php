<?php

session_start();

include 'header.php';

if($_SESSION['user_email']=="")
{
    ?>

<div class="alert alert-danger text-center" role="alert">
  You need to Login to view the cart!<br>
Click <a href="login.php"><strong>here</strong></a> to Login.
</div>
<?php
}

else
{

$active_user = $_SESSION['user_email'];
$sql_check_order_id = "SELECT payment_id FROM placed_order";
$payment_id = "GLAMAROMA-ORDER-".rand(100000,999999);
$result_check_order_id = $conn->query($sql_check_order_id);

if ($result_check_order_id->num_rows > 0) {
    // output data of each row
    while($row = $result_check_order_id->fetch_assoc()) {
        if($payment_id == $row['payment_id'])
        {
        	$payment_id = "GLAMAROMA-ORDER-".rand(100000,999999);
        }
    }
}
// print_r($_SESSION['cart_content']);
// echo "<br>";
// print_r($_SESSION['cart_content_size']);
// echo "<br>";
// print_r($_SESSION['cart_content_units']);
// echo "<br>";
// print_r($_SESSION['cart_content_name']);
// echo "<br>";
// print_r($_SESSION['cart_content_price']);
// echo "<br>";




$temp_street = '';
$temp_state = '';
$temp_city = '';
$temp_zip = '';
$temp_phone = '';
$order_total = 0;
$order_total_final = 0;
$shipping_cost = 0;
$coupon_price = 0;
$coupon_percent = 0;


// echo $shipping_cost;

if(isset($_POST['apply_coupon']))
          	{
          		$couponCode = $_POST['coupon_code'];
          		$sql_fetch_coupon_details = "SELECT * FROM discount_coupon WHERE coupon_code = '$couponCode'";

                $result_coupon = $conn->query($sql_fetch_coupon_details);

                if ($result_coupon->num_rows > 0) {
                    // output data of each row
                    while($row = $result_coupon->fetch_assoc()) {
                        if($row['coupon_type']=="price")
                        {
                        	$coupon_price = $row['coupon_value'];
                        	$order_price = $order_price - $coupon_price;

                        }
                        else if($row['coupon_type']=="percent")
                        {
                        	$coupon_percent = $row['coupon_value'];
                        	$order_price = $order_price - ($coupon_percent/100)*$order_price;
                        }
                    }
                }
                else {
                  ?>
<div class="container">
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
  Invalid Coupon
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
</div>
                 <?php
                 }
          	}
          	// echo $coupon_price;
          	// echo "<br>";
          	// echo $coupon_percent;
	

$sql_fetch_address = "SELECT * FROM registered_user WHERE user_email = '$active_user'";
	$result = $conn->query($sql_fetch_address);
	if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	        $temp_street = $row['user_street'];
			$temp_state = $row['user_state'];
			$temp_city = $row['user_city'];
			$temp_zip = $row['user_zip'];
			$temp_phone = $row['user_mobile'];
			$temp_id = $row['user_id'];
	    }
	}
		
echo "<br>";

?>
<!DOCTYPE html>
<html>
<head>
	<title>Glamaroma | View Cart</title>
	<style type="text/css">
    	table {
		    font-family: arial, sans-serif;
		    border-collapse: collapse;
		    width: auto;
		}

		td, th {
		    border: 1px solid #dddddd;
		    text-align: left;
		    padding: 8px;
		}

		tr:nth-child(even) {
		    background-color: #dddddd;
		}

		#modify_user{
			max-width: 1000px;
		}

		#admin_actions button{
			margin-top:5px;
		}
    </style>
    <script type="text/javascript">
    	var shipping_cost = 0;
    	var total_cost = 0;
    	var coupon_cost = 0;
    </script>
</head>
<body>
	<script type="text/javascript">
		document.getElementById('cart_link').href="#";
	</script>

	<div class="container">
	<div class="row">
        <div class="col-md-6 mb-6">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Your cart</span>
            <span class="badge badge-secondary badge-pill"><?php echo count($_SESSION['cart_content']);?></span>
          </h4>
          <ul class="list-group mb-3">
          	<?php
          	for($index = 0; $index < count($_SESSION['cart_content']); $index++)
			{
				$temp_product = $_SESSION['cart_content'][$index];
				$temp_size = $_SESSION['cart_content_size'][$index];
				$temp_units = $_SESSION['cart_content_units'][$index];
				$temp_price = $_SESSION['cart_content_price'][$index];
				$_SESSION['current_product'] = $index;

				$sql_fetch_product = "SELECT * FROM product WHERE product_id = $temp_product";
				$result = $conn->query($sql_fetch_product);
				
				if ($result->num_rows > 0) {
				    // output data of each row
				    while($row = $result->fetch_assoc()) {
				    	$order_total = $order_total + $temp_units*$temp_price;
				    	?>
				    	<script type="text/javascript">
				    		total_cost = <?php echo $order_total;?>
				    	</script>
				    	<li class="list-group-item d-flex justify-content-between lh-condensed">
			              <div>
			                <h6 class="my-0"><?php echo $row['product_name'];?></h6>
			                <small class="text-muted"><?php echo $row['product_category'].'>'.$row['product_subcategory'];?></small>

			              </div>
			              <span class="text-muted">Rs. <?php echo $temp_price;?> (<?php echo $temp_units;?>)</span>
			            </li>
				    	
				<?php
					}
				}

			}
			?>
<script>

</script>
            
            <li class="list-group-item d-flex justify-content-between">
              <span>Net Amount (INR)</span>
              <strong>Rs. <?php echo $order_total;?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Shipping</span>
              
 
<strong id="display_cost">Rs. </strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Coupons Applied</span>
              <?php 
              	if($coupon_price != 0)
              	{
              		?>
              		<strong><i style="color: green;">- Rs. <?php echo $coupon_price;?></i></strong>
              		
              		<?php
              		$shippin = $_GET['shipping'];
              		$order_total_final = $order_total - $coupon_price;
              		?>
              		<script type="text/javascript">
              			coupon_cost = <?php echo $coupon_price;?>
              		</script>
              		<?php
              	}
              	else if($coupon_percent != 0)
              	{
              		?>
              		<strong><i style="color: green;"><?php echo $coupon_percent;?>% off</i></strong>
              		<?php
              		$shippin = $_GET['shipping'];
              		$order_total_final = $order_total - ($coupon_percent)*$order_total/100;

              		?>
              		<script type="text/javascript">
              			coupon_cost = <?php echo $coupon_percent;?>
              		</script>
              		<?php 
              	}
              	else{
              		?>
              		---
              		<?php
              		$shippin = $_GET['shipping'];
              		$order_total_final = $order_total;
              		?>
              		
              		<?php
              	}
              ?>
              
            </li>
            
          </ul>
          <?php

          ?>
          <li class="list-group-item d-flex justify-content-between">
              <span><strong>Total Amount (INR)</strong></span>
              <strong id="order_total_final_value" value="<?php echo $order_total_final;?>">Rs. <?php echo $order_total_final;?></strong>
            </li>
          <form class="card p-2" method="post" action="#">
            <div class="input-group">
              <input type="text" class="form-control" name="coupon_code" placeholder="Promo code">
              <div class="input-group-append">
                <button type="submit" name="apply_coupon" class="btn btn-secondary">Redeem</button>
              </div>
            </div>
          </form>

         
        </div>

        <div class="col-md-6 col-xs-12">
        	 <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Shipping Details</span>
           
          </h4>
		<form action="TxnTest.php" method="post" name="checkout_form">
			<input type="hidden" name="order_price" value="<?php echo $order_price;?>">
 <div class="form-group">
		    <label for="deliverTo">Name</label>
		    <input type="text" class="form-control" id="deliverTo" name="deliver_to" required>
		  </div>
		  <div class="form-group">
		    <label for="inputAddress">Address</label>
		    <input type="text" class="form-control" id="inputAddress" name="user_address" value="<?php echo $temp_street;?>" required>
		  </div>
		 <div class="form-row">
		  <div class="form-group col-md-4">
		      <label for="inputState">State</label>
		      <input type="text" class="form-control" id="inputState" name="user_state" value="<?php echo $temp_state;?>" required>
		    </div>
		 
		    <div class="form-group col-md-4">
		      <label for="inputCity">City</label>
		      <input type="text" class="form-control" id="inputCity" name="user_city" value="<?php echo $temp_city;?>" required>
		    </div>
		     <div class="form-group col-md-4">
			      <label for="inputZip"><strong>Zip</strong></label>
			      <input type="number" class="form-control" id="fname" name="fname" onchange="myFunction()" required>	
			    </div>
			  
		   
		    <script type="text/javascript">
					shipping_cost=0;
					document.getElementById("display_cost").innerHTML = "Enter the ZIP to calculate shipping.";
					document.getElementById("fname").onchange = function() {myFunction()};

					function myFunction() {
						
					    var x = document.getElementById("fname").value;

					    
					    if(x >= 400001 && x<=400105)
					    {
					    	shipping_cost = 50;
					    	document.getElementById("display_cost").innerHTML = "Rs. "+shipping_cost;
					    }
					    else{
					    	shipping_cost = 100;
					    	document.getElementById("display_cost").innerHTML = "Rs. "+shipping_cost;
					    }

						total_cost = Number(<?php echo $order_total_final;?>) + shipping_cost;
						document.getElementById("order_total_final_value").innerHTML = "Rs. "+total_cost;	
					}	

			</script>
		  </div>

		 

 <div class="form-group">
		    <label for="landmarkAddress">Landmark</label>
		    <input type="text" class="form-control" id="landmarkAddress" name="landmark_address" required>
</div>
 <div class="form-group">
		    <label for="inputContact">Contact Number</label>
		    <input type="text" class="form-control" id="inputContact" name="user_contact" value="<?php echo $temp_phone;?>" placeholder="xxxxxxxxxx" required>
		  </div>
		  <input type="hidden" name="ORDER_ID" value="<?php echo $payment_id;?>">
		  <input type="hidden" name="CUST_ID" value="<?php echo $temp_id;?>">
		  <input type="hidden" name="INDUSTRY_TYPE_ID" value="Retail109">
		  <input type="hidden" name="CHANNEL_ID" value="WEB">
		  <input type="hidden" name="TXN_AMOUNT">
		  <a href="#" onclick="setValue()" class="btn btn-dark">Proceed to Payment</a>

<br><br>
                 </form>

<script>
function setValue(){

document.checkout_form.TXN_AMOUNT.value = total_cost;
document.forms["checkout_form"].submit();
    
}


</script>
		
	</div>
    </div>
	
</div>
		
	<?php
include 'footer.php';
}
?>
