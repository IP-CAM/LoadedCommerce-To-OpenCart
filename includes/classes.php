<?php
	// the database details
	include_once 'credentials.php';

	// LoadedCommerce portion of code (Exfiltrating all the data from existing cart to migrate)
	class LCDB {
		//Make connection on instantioation (that word is hard af to spell)
	    function __construct() {
			$this->dbCon = mysqli_connect(constant("LC_HOSTNAME"), constant("LC_USERNAME"), constant("LC_PASSWORD"), constant("LC_DATABASE"));
	    }

	    function GetCustomers() {
			$query = "SELECT
			tbl1.customers_id,tbl1.purchased_without_account,tbl1.customers_gender,tbl1.customers_firstname,tbl1.customers_lastname,
			tbl1.customers_dob,tbl1.customers_email_address,tbl1.customers_default_address_id,tbl1.customers_password,tbl1.customers_newsletter,
			tbl1.customers_selected_template,tbl1.customers_group_id,tbl1.customers_access_group_id,tbl1.customers_group_ra,
			tbl1.customers_payment_allowed,tbl1.customers_shipment_allowed,tbl1.customers_validation_code,tbl1.customers_validation,
			tbl1.customers_email_registered,tbl1.customers_account_approval,tbl1.customers_telephone,tbl1.customers_fax,
			tbl1.business_description,tbl1.business_age,tbl1.business_type,tbl1.business_products,tbl1.business_referred,
			tbl1.business_contact,tbl1.newsletter,tbl1.catalog_print,tbl1.catalog_pdf, tbl2.address_book_id,tbl2.customers_id,
			tbl2.entry_gender,tbl2.entry_company,tbl2.entry_company_tax_id,tbl2.entry_firstname,tbl2.entry_lastname,tbl2.entry_street_address,
			tbl2.entry_suburb,tbl2.entry_postcode,tbl2.entry_city,tbl2.entry_state,tbl2.entry_country_id,tbl2.entry_zone_id,tbl2.entry_telephone,
			tbl2.entry_fax,tbl2.entry_email_address, tbl3.customers_info_id, tbl3.customers_info_date_account_created

			FROM customers tbl1, address_book tbl2, customers_info tbl3
			WHERE tbl1.customers_id = tbl2.customers_id AND tbl1.customers_id = tbl3.customers_info_id AND tbl1.customers_email_address <> ''
			GROUP BY tbl1.customers_email_address
			ORDER BY tbl3.customers_info_date_account_created DESC";

	    	$customers = mysqli_query($this->dbCon, $query);

			return $customers;
	    }

	    function GetCategories() {
			$query = "
			SELECT tbl1.categories_id, tbl1.categories_image, tbl1.parent_id, tbl1.status, tbl1.sort_order,

			tbl2.categories_name, tbl2.categories_heading_title, tbl2.categories_description, tbl2.categories_head_title_tag,
			tbl2.categories_head_desc_tag, tbl2.categories_head_keywords_tag, tbl2.categories_seo_url

			FROM categories tbl1, categories_description tbl2
			WHERE tbl1.categories_id=tbl2.categories_id
			";

	    	$categories = mysqli_query($this->dbCon, $query);

			return $categories;
	    }

	    function GetProducts() {
			$query = "SELECT p1.products_id as pID, p1.products_quantity, p1.products_model, p1.products_image, p1.products_image_med, p1.products_image_lrg, p1.products_image_sm_1, p1.products_image_xl_1, p1.products_image_sm_2, p1.products_image_xl_2, p1.products_image_sm_3, p1.products_image_xl_3, p1.products_image_sm_4, p1.products_image_xl_4, p1.products_image_sm_5, p1.products_image_xl_5, p1.products_image_sm_6, p1.products_image_xl_6, p1.products_price, p1.products_cost, p1.products_msrp, p1.products_date_added, p1.products_last_modified, p1.products_date_available, p1.products_weight, p1.products_status, p1.products_tax_class_id, p1.manufacturers_id, p1.products_ordered, p1.products_parent_id, p1.products_price1, p1.products_price2, p1.products_price3, p1.products_price4, p1.products_price5, p1.products_price6, p1.products_price7, p1.products_price8, p1.products_price9, p1.products_price10, p1.products_price11, p1.products_price1_qty, p1.products_price2_qty, p1.products_price3_qty, p1.products_price4_qty, p1.products_price5_qty, p1.products_price6_qty, p1.products_price7_qty, p1.products_price8_qty, p1.products_price9_qty, p1.products_price10_qty, p1.products_price11_qty, p1.products_qty_blocks, p1.products_group_access, p1.products_nav_access, p1.sort_order, p1.vendors_id, p1.vendors_product_price, p1.vendors_prod_id, p1.vendors_prod_comments, p1.products_qty_days, p1.products_qty_years, p1.parent_model, p1.products_quantity_order_min, p1.products_strict_inventory, p1.products_text_for_madetoorder, p1.products_text_for_outofstock, p1.products_text_for_upc, p2.*, p3.*, p4.*, p5.*, (SELECT GROUP_CONCAT(categories_id) FROM products_to_categories WHERE products_id = p1.products_id) as ProductCategories FROM products as p1
JOIN products_groups as p2
ON p1.products_id = p2.products_id
JOIN products_description as p3
ON p1.products_id = p3.products_id
LEFT JOIN products_attributes as p4
ON p1.products_id = p4.products_id
LEFT JOIN products_options_values as p5
ON p4.options_values_id = p5.products_options_values_id
ORDER BY p1.products_parent_id ASC";

			$products = mysqli_query($this->dbCon, $query);

			return $products;
	    }

	    function GetOrders($customer_id) {
	    	// get the orders for the customer
	    	$query = "
			SELECT * FROM orders WHERE customers_id = '" . $customer_id . "'
	    	";

	    	$orders = mysqli_query($this->dbCon, $query);

	    	$transaction_history = array();
	    	$order_products = array();
	    	$order_status_history = array();

	    	$customers_orders = array();

			while($row = mysqli_fetch_assoc($orders)){
				$transaction_history[] = $row; //add all the orders to the transaction history
				//append to array orders_products

				$transaction_products = mysqli_query($this->dbCon, "SELECT * FROM orders_products WHERE orders_id = '" .
					$row["orders_id"] . "'");
				while ($prod_row = mysqli_fetch_assoc($transaction_products)) {
					$order_products[] = $prod_row;
				}

				$transaction_status_history = mysqli_query($this->dbCon, "SELECT * FROM orders_status_history WHERE orders_id = '"
				. $row["orders_id"] . "'");
				while ($trans_status_row = mysqli_fetch_assoc($transaction_status_history)) {
					$order_status_history[] = $trans_status_row;
				}

				$transaction_history[] = $order_products;
				$transaction_history[] = $order_status_history;

				//pass all the things to the final array
				$customers_orders[] = $transaction_history;
				unset($order_products);
				unset($transaction_history);
				unset($order_status_history);
				//append to array order_status_history
				//append to array order_attributes per product basis
			}

			return $customers_orders;
	    }
	}

	// OpenCart portion of code (Adding Products, Categories, Transactions, Customers, Categories...)
	class OCDB {
		//Make connection on instantioation (that word is hard af to spell)
		private $Unique_Product_Models = array();

	    function __construct() {

			$this->dbCon = mysqli_connect(constant("OC_HOSTNAME"), constant("OC_USERNAME"), constant("OC_PASSWORD"), constant("OC_DATABASE"));
	    }

	    function AddCustomer($customer) {
			if ($customer["catalog_print"] == '0') {
				$catalog_or_print = '1';
			} else {
				$catalog_or_print = '2';
			}

	    	if ($customer["customers_group_id"] == '1') {
	    		//wholesale

	    		$query = "INSERT INTO oc_customer SET customer_group_id = '2', firstname = '" . $customer["customers_firstname"] . "',
	    		lastname = '" . $customer["customers_lastname"] . "', email = '" . $customer["customers_email_address"] . "',
	    		telephone = '" . preg_replace("/[^0-9,.]/", "",$customer["entry_telephone"]) . "', fax = '" .
	    		preg_replace("/[^0-9,.]/", "", $customer["entry_fax"]) ."', custom_field = '
	    		{
		    		\"1\":\"" . $customer["entry_company"] . "\",
		    		\"2\":\"" . $customer["entry_company_tax_id"] . "\",
		    		\"3\":\"" . $customer["purchased_without_account"] . "\",
		    		\"4\":\"" . $customer["customers_dob"] . "\",
		    		\"5\":\"" . $customer["customers_payment_allowed"] . "\",
		    		\"6\":\"" . $customer["customers_shipment_allowed"] . "\",
		    		\"7\":\"" . $customer["customers_validation"] . "\",
		    		\"8\":\"" . $customer["customers_validation_code"] . "\",
		    		\"9\":\"" . $customer["business_description"] . "\",
		    		\"10\":\"" . $customer["business_age"] . "\",
		    		\"11\":\"" . $customer["business_type"] . "\",
		    		\"12\":\"" . $customer["business_products"] . "\",
		    		\"13\":\"" . $customer["business_referred"] . "\",
		    		\"14\":\"" . $customer["business_contact"] . "\",
		    		\"15\":\"" . $customer["customers_password"] . "\",
		    		\"16\":\"" . $catalog_or_print . "\"
	    		}', newsletter = '" . $customer["newsletter"] . "', salt = '9X766kTza',
	    		password = '1c17ffd16ad47cd86321dc4b708e4d76c8cb4a24', status = '1', approved = '1',
	    		safe = '1', date_added = '" . $customer['customers_info_date_account_created'] . "'";
	    	} else {
	    		//retail
	    		$query = "INSERT INTO oc_customer SET customer_group_id = '1', firstname = '" . $customer["customers_firstname"] . "',
	    		lastname = '" . $customer["customers_lastname"] . "', email = '" . $customer["customers_email_address"] . "',
	    		telephone = '" . preg_replace("/[^0-9,.]/", "",$customer["entry_telephone"]) . "', fax = '" .
	    		preg_replace("/[^0-9,.]/", "", $customer["entry_fax"]) ."', custom_field = '
	    		{
		    		\"1\":\"" . $customer["entry_company"] . "\",
		    		\"2\":\"" . $customer["entry_company_tax_id"] . "\",
		    		\"3\":\"" . $customer["purchased_without_account"] . "\",
		    		\"4\":\"" . $customer["customers_dob"] . "\",
		    		\"5\":\"" . $customer["customers_payment_allowed"] . "\",
		    		\"6\":\"" . $customer["customers_shipment_allowed"] . "\",
		    		\"7\":\"" . $customer["customers_validation"] . "\",
		    		\"8\":\"" . $customer["customers_validation_code"] . "\",
		    		\"9\":\"" . $customer["business_description"] . "\",
		    		\"10\":\"" . $customer["business_age"] . "\",
		    		\"11\":\"" . $customer["business_type"] . "\",
		    		\"12\":\"" . $customer["business_products"] . "\",
		    		\"13\":\"" . $customer["business_referred"] . "\",
		    		\"14\":\"" . $customer["business_contact"] . "\",
		    		\"15\":\"" . $customer["customers_password"] . "\",
		    		\"16\":\"" . $catalog_or_print . "\"
	    		}', newsletter = '" . $customer["newsletter"] . "', salt = '9X766kTza',
	    		password = '1c17ffd16ad47cd86321dc4b708e4d76c8cb4a24', status = '1', approved = '1',
	    		safe = '1', date_added = '" . $customer['customers_info_date_account_created'] . "'";
	    	}

	    	$this->dbCon->query($query);
	    }

	    function AddCategory($category) {
			if ($category["parent_id"] == '0') {
				$query = "INSERT INTO oc_category SET parent_id = '0', `top` = '0', `column` = '1', sort_order = '0', status = '1',
				date_modified = NOW(), date_added = NOW(), old_category_id = " . $category["categories_id"] . ";";
				mysqli_query($this->dbCon, $query);

				$cat_insert_id = $this->dbCon->insert_id;

				$query = "UPDATE oc_category SET image = 'catalog/" . $category["categories_image"] . "' WHERE category_id = '" . $cat_insert_id . "'";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO oc_category_description SET category_id = '" . $cat_insert_id . "', language_id = '1', name = '" .
				$category["categories_name"] . "', description = '" . htmlentities($category["categories_description"]) .
				"', meta_title = '" . $category["categories_head_title_tag"] . "', meta_description = '" .
				$category["categories_head_desc_tag"] . "', meta_keyword = '" . $category["categories_head_keywords_tag"] . "';";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO `oc_category_path` SET `category_id` = '" . $cat_insert_id . "', `path_id` = '" .
				$cat_insert_id . "', `level` = '0';";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO oc_category_to_store SET category_id = '" . $cat_insert_id . "', store_id = '0';";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO oc_category_to_layout SET category_id = '" . $cat_insert_id . "', store_id = '0',
				layout_id = '0';";
				mysqli_query($this->dbCon, $query);

				$seo_URL = preg_replace('/[^a-zA-Z0-9]+/', '-', trim(strtolower($category["categories_name"])));

				$query = "INSERT INTO oc_url_alias SET query = 'category_id=" . $cat_insert_id . "', keyword = '" . $seo_URL . "';";
				mysqli_query($this->dbCon, $query);

			} else {
				//sub catagories

				//get parent category ID
				$query = "SELECT category_id FROM oc_category WHERE old_category_id = '" . $category["parent_id"] . "'";
				$curr_parent_id_query = mysqli_query($this->dbCon, $query);

				$curr_parent_id = mysqli_fetch_assoc($curr_parent_id_query);

				$query = "INSERT INTO oc_category SET parent_id = '" . $curr_parent_id["category_id"] . "', `top` = '0', `column` = '1', sort_order = '0', status = '1',
				date_modified = NOW(), date_added = NOW(), old_category_id = " . $category["categories_id"] . ";";
				mysqli_query($this->dbCon, $query);

				$cat_insert_id = $this->dbCon->insert_id;

				echo "sub category: " . $category["categories_name"] . " - is a sub";

				$query = "UPDATE oc_category SET image = 'catalog/" . $category["categories_image"] . "' WHERE category_id = '" . $cat_insert_id . "'";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO oc_category_description SET category_id = '" . $cat_insert_id . "', language_id = '1', name = '" .
				$category["categories_name"] . "', description = '" . htmlentities($category["categories_description"]) .
				"', meta_title = '" . $category["categories_head_title_tag"] . "', meta_description = '" .
				$category["categories_head_desc_tag"] . "', meta_keyword = '" . $category["categories_head_keywords_tag"] . "';";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO `oc_category_path` SET `category_id` = '" . $cat_insert_id . "', `path_id` = '" .
				$cat_insert_id . "', `level` = '0';";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO oc_category_to_store SET category_id = '" . $cat_insert_id . "', store_id = '0';";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO oc_category_to_layout SET category_id = '" . $cat_insert_id . "', store_id = '0',
				layout_id = '0';";
				mysqli_query($this->dbCon, $query);

				$seo_URL = preg_replace('/[^a-zA-Z0-9]+/', '-', trim(strtolower($category["categories_name"])));

				$query = "INSERT INTO oc_url_alias SET query = 'category_id=" . $cat_insert_id . "', keyword = '" . $seo_URL . "';";
				mysqli_query($this->dbCon, $query);
			}
	    }
	    
	    // OK this is kinda hacky but I don't want to tweak the fucking SQL query again :/
	    function AddProduct($product) { /*just pass the whole array fsck all those arguments*/
	    	global $Parent_Products;
	    	global $Sub_Products;
	    	global $icount;

			// TAX RATE
			if ($product["products_tax_class_id"] == "2") { //apply the 7% tax rate
				$oc_tax_class_id = "11"; //check this on migration because may change on production server
			} else {
				$oc_tax_class_id = "";
			}

			// CLEANUP DESCRIPTION BECAUSE THEY'RE A MESS
			$product_description = strip_tags($product["products_description"]);

			$query = "INSERT INTO oc_product SET model = '{$product["products_model"]}', sku = '', upc = '{$product["products_text_for_upc"]}', ean = '', jan = '', isbn = '', mpn = '', location = '', quantity = '{$product["products_quantity"]}', minimum = '{$product["products_quantity_order_min"]}', subtract = '1', stock_status_id = '6', date_available = '2016-08-08', manufacturer_id = '0', shipping = '1', price = '{$product["products_price"]}', points = '0', weight = '{$product["products_weight"]}', weight_class_id = '5', length = '', width = '', height = '', length_class_id = '3', status = '1', tax_class_id = '{$oc_tax_class_id}', sort_order = '', date_added = NOW()";

			// sanity check before adding
			if (
					trim($product["products_parent_id"]) === "0" ||
					trim($product["products_parent_id"]) === NULL ||
					trim($product["products_parent_id"]) === trim($product["products_parent_id"]) && // is parent item
					array_key_exists(trim($product["products_id"]), $this->Unique_Product_Models) === FALSE // hasn't already been added
				) {

				mysqli_query($this->dbCon, $query);
				// we'll need the auto-incremented insert ID for the rest of this query
				$product_id = $this->dbCon->insert_id;
				$product['new_product_id'] = $product_id;

				// add product_id and model to array for no dupes and retrieval of parent ids
				$this->Unique_Product_Models[trim($product["products_model"])] = $product;
				
				$Parent_Products++;
			} else {
				if ($product["options_id"] !== "1") {//make sure it's not just product options
					foreach ($this->Unique_Product_Models as $prod_model => $values) {
						if (strpos($prod_model, $product["products_parent_id"]) !== FALSE) {
							$product_id = $values["new_product_id"];
							break;
						}
					}

					$product_id = "not found {$product["products_parent_id"]}\n";
					echo $product_id;
				} else {
					// it's a product's options
					$product_id = 0;
				}

			}

			$query = "UPDATE oc_product SET image = '{$product["products_image"]}' WHERE product_id = '{$product_id}';";

			$query .= "INSERT INTO oc_product_description SET product_id = '{$product_id}', language_id = '1', name = '{$product["products_name"]}', description = '{$product_description}', tag = '', meta_title = '{$product["products_head_title_tag"]}', meta_description = '{$product["products_head_desc_tag"]}', meta_keyword = '{$product["products_head_keywords_tag"]}';";

			$query .= "INSERT INTO oc_product_to_store SET product_id = '{$product_id}', store_id = '0';";

			/*
			check if it's a subproduct or if we have options for the product
			the particular LoadedCommerce setup I'm working with had this coded into it (sub_products)
			and it was done in the shittiest way possible
			*/
			mysqli_multi_query($this->dbCon, $query);
			while(mysqli_next_result($this->dbCon)){;} // flush multiqueries - http://stackoverflow.com/questions/27899598/mysqli-multi-query-commands-out-of-sync-you-cant-run-this-command-now

			if (
					$product["products_parent_id"] !== "0" && // if the parent_id is not 0
					$product["parent_model"] > 0 || // OR the parent_model is set (because their database is a mess)
					$product["options_id"] === "1" //  OR if the options_id value is set
				) {

				/*
				option_id is based off of your OpenCart setting / '11' is Sizes (for me)
				Catalog->Options->Selection Option Name Click edit, look @ URL &option_id=11
				(in my setup it's Size, this suits my needs)
				*/

				$Sub_Products++;

				// CREATE sub option values
				$query = "INSERT INTO oc_option_value SET option_id = '11', image = '{$product["products_image"]}', sort_order = '0';";
				mysqli_query($this->dbCon, $query);
				$option_vale_insert_id = $this->dbCon->insert_id;

				$query = "INSERT INTO oc_option_value_description SET option_value_id = '{$option_vale_insert_id}', language_id = '1', option_id = '11', name = '{$product["products_name"]}';";
				mysqli_query($this->dbCon, $query);

				$query = "INSERT INTO oc_product_option SET product_id = '{$product_id}', option_id = '11', required = '1';";
				mysqli_query($this->dbCon, $query);

				$option_insert_id = $this->dbCon->insert_id;

				if (isset($products["options_values_price"])) {
					// has option price
					$option_price = $products["options_values_price"];
				} else {
					// is subproduct so get main price
					$option_price = $product["products_price"];
				}

				// product_option_value_id = auto_incrementing
				$query = "INSERT INTO oc_product_option_value SET product_option_id = '{$option_insert_id}', product_id = '{$product_id}', option_id = '11', option_value_id = '{$option_vale_insert_id}', quantity = '{$product["products_quantity"]}', subtract = '1', price = '1', price_prefix = '+', points = '0', points_prefix = '+', weight = '{$product["products_weight"]}', weight_prefix = '+';";
				mysqli_query($this->dbCon, $query);
			}

			$retail_prices =
			array(
				$product["products_price1_qty"] => $product["products_price1"], // <= 10 products
				$product["products_price2_qty"] => $product["products_price2"], // <= 20 products
				$product["products_price3_qty"] => $product["products_price3"], // <= 30 etc..
				$product["products_price4_qty"] => $product["products_price4"],
				$product["products_price5_qty"] => $product["products_price5"],
				$product["products_price6_qty"] => $product["products_price6"],
				$product["products_price7_qty"] => $product["products_price7"],
				$product["products_price8_qty"] => $product["products_price8"],
				$product["products_price9_qty"] => $product["products_price9"],
				$product["products_price10_qty"] => $product["products_price10"],
				$product["products_price11_qty"] => $product["products_price11"]
			);

			$wholesale_prices =
			array(
				$product["products_price1_qty"] => $product["customers_group_price1"], // <= 10 products
				$product["products_price2_qty"] => $product["customers_group_price2"], // <= 20 products
				$product["products_price3_qty"] => $product["customers_group_price3"], // <= 30 etc..
				$product["products_price4_qty"] => $product["customers_group_price4"],
				$product["products_price5_qty"] => $product["customers_group_price5"],
				$product["products_price6_qty"] => $product["customers_group_price6"],
				$product["products_price7_qty"] => $product["customers_group_price7"],
				$product["products_price8_qty"] => $product["customers_group_price8"],
				$product["products_price9_qty"] => $product["customers_group_price9"],
				$product["products_price10_qty"] => $product["customers_group_price10"],
				$product["products_price11_qty"] => $product["customers_group_price11"]
			);

			/* quantity and or wholesaler discounts
			$customer_group = "2"; //wholesale
			$customer_group = "1"; //retail
			*/
			foreach ($retail_prices as $qty => $price) {
				$query .= "INSERT INTO oc_product_discount SET product_id = '{$product_id}', customer_group_id = '1', quantity = '{$qty}', priority = '1', price = '{$price}', date_start = '', date_end = '';";
			}

			foreach ($wholesale_prices as $qty => $price) {
				$query .= "INSERT INTO oc_product_discount SET product_id = '{$product_id}', customer_group_id = '2', quantity = '{$qty}', priority = '1', price = '{$price}', date_start = '', date_end = '';";
			}

			
			// Now that we have a Group_Concat for the product categories, link them badboys ups
			$product_categories = explode(",", $product["ProductCategories"]);

			foreach ($product_categories as $cat_id) {
				$query .= "INSERT INTO oc_product_to_category SET product_id = '{$product_id}', category_id = '{$cat_id}';";
			}

			$query .= "INSERT INTO oc_product_to_layout SET product_id = '{$product_id}', store_id = '0', layout_id = '0';";

			$seo_URL = preg_replace('/[^a-zA-Z0-9]+/', '-', trim(strtolower($product["products_name"])));

			$query .= "INSERT INTO oc_url_alias SET query = 'product_id={$product_id}', keyword = '{$seo_URL}';";
			
			$result = mysqli_multi_query($this->dbCon, $query);

			file_put_contents('/var/www/queries.sql', $query . "\n\n", FILE_APPEND);

			while(mysqli_next_result($this->dbCon)){;} // flush multiqueries - http://stackoverflow.com/questions/27899598/mysqli-multi-query-commands-out-of-sync-you-cant-run-this-command-now
	    }
	}
?>