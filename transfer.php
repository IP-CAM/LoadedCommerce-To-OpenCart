<?php 
	// credentials and classes
	include_once 'includes/classes.php';	

	// tracking products
	$Parent_Products		= "0";
	$Sub_Products			= "0";

	// Make the database connections ooo such naming schema
	$LoadedCommerce = new LCDB;
	$OpenCart = new OCDB;

	$Old_Products = $LoadedCommerce->GetProducts();
	
	while($row = mysqli_fetch_assoc($Old_Products)){
		$OpenCart->AddProduct($row);
	}

	$Total_Products = $Parent_Products + $Sub_Products;

	echo "\n\nMigrated:\nParent Products: {$Parent_Products}\nSub Products:{$Sub_Products}\n\nTotal Products:{$Total_Products}\n";
 ?>