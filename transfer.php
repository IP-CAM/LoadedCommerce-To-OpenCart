<?php 
	// credentials and classes
	include_once 'includes/classes.php';

	// Make the database connections ooo such naming schema
	$LoadedCommerce = new LCDB;
	$OpenCart = new OCDB;

	switch (strtolower($argv[1])) {
		case 'products':
			// let user know
			echo "\n\nMigrating products...\n\n";
			// tracking products
			$Parent_Products		= "0";
			$Sub_Products			= "0";

			$Old_Products = $LoadedCommerce->GetProducts();
			
			while($row = mysqli_fetch_assoc($Old_Products)){
				$OpenCart->AddProduct($row);
			}

			$Total_Products = $Parent_Products + $Sub_Products;

			echo "\n\nMigrated products, price break rules and customer group rules for:\nParent Products: {$Parent_Products}\nSub Products:{$Sub_Products}\n\nTotal Products:{$Total_Products}\n";
			break;
		case 'customers':
			echo "\nMigrating customers...\n\n";

			$Customers = $LoadedCommerce->GetCustomers();

			$Customer = 0;
			while($row = mysqli_fetch_assoc($Customers)){
				$OpenCart->AddCustomer($row);
				$Customer++;
			}

			echo "\n\nSuccessfully migrated ({$Customer}) customers\n\n";
			break;
		case 'categories':
			echo "\nMigrating categories...\n\n";
			$Categories = $LoadedCommerce->GetCategories();

			$Categorie = 0;
			while ($row = mysqli_fetch_assoc($Categories)) {
				$OpenCart->AddCategory($row);
				$Categorie++;
			}
			echo "\nMigrated ($Categorie) catgories...\n\n";
			break;
		default:
			echo "\n\ncommands are customers, categories, products, transactions\n\n";
			break;
	}

 ?>