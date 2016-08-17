<?php 
	// credentials and classes
	include_once 'includes/classes.php';

	// Make the database connections ooo such naming schema
	$LoadedCommerce = new LCDB;
	$OpenCart = new OCDB;

	$Old_Products = $LoadedCommerce->GetProducts();

	while($row = mysqli_fetch_assoc($Old_Products)){
		/*if ($row["products_group_access"] !== "1") {
			$customer_group = "2"; //wholesale
		} else {
			$customer_group = "1"; //retail
		}*/

		//added in this option

		$OpenCart->AddProduct($row);
		die();
	}
 ?>