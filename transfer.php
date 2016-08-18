<?php 
	// credentials and classes
	include_once 'includes/classes.php';

	// Make the database connections ooo such naming schema
	$LoadedCommerce = new LCDB;
	$OpenCart = new OCDB;

	$Old_Products = $LoadedCommerce->GetProducts();

	while($row = mysqli_fetch_assoc($Old_Products)){
		$OpenCart->AddProduct($row);
	}
 ?>