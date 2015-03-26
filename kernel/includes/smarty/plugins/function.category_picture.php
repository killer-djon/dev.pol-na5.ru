<?php

	function smarty_function_category_picture ($params, &$smarty){
		$default_limit = 4;
	  
		$limit = (isset ($params['limit']) && $params['limit'] > 0)? $params['limit'] : $default_limit;
		
		$_sql = db_query("SELECT categoryID FROM SC_categories WHERE parent = ".$params['categoryID']." ORDER BY categoryID ");
		$categories = array();
		while( $rows = db_fetch_row($_sql) ){
			$categories[] = $rows;
		}
		$product = '';
		if( is_array($categories) && count($categories) ){
			
			foreach( $categories as $key => $item ){
				$result = db_phquery_fetch (DBRFETCH_ROW_ALL, 'SELECT `products`.'.LanguagesManager::sql_prepareField('name').' as `name`, 
						`pictures`. `thumbnail`, `pictures`. `filename`  
						FROM `?#PRODUCTS_TABLE` `products` 
						LEFT JOIN `?#PRODUCT_PICTURES` `pictures` 
						USING (`productID`) WHERE `products`.`enabled` = 1 AND `products`.`categoryID` = "'.intval($item['categoryID']).'"
						ORDER BY `products`.`productID` DESC LIMIT ?', $limit);
				$product = '';
			  
				$product .= '<div class="slideshow">';
				$product .=	formatProductPuctures($result);
				$product .= '</div>';
			}
		}else{
			$result = db_phquery_fetch (DBRFETCH_ROW_ALL, 'SELECT `products`.'.LanguagesManager::sql_prepareField('name').' as `name`, 
						`pictures`. `thumbnail`, `pictures`. `filename`  
						FROM `?#PRODUCTS_TABLE` `products` 
						LEFT JOIN `?#PRODUCT_PICTURES` `pictures` 
						USING (`productID`) WHERE `products`.`enabled` = 1 AND `products`.`categoryID` = "'.intval($params['categoryID']).'"
						ORDER BY `products`.`productID` DESC LIMIT ?', $limit);
		
			$product .= '<div class="slideshow">';
			$product .=	formatProductPuctures($result);
			$product .= '</div>';
		}
		
		return $product;
	}

	function formatProductPuctures($params=array()){
		$pictures = '';
		foreach( $params as $key => $value ){
			if( !isset($value['filename']) || ! file_exists(DIR_PRODUCTS_PICTURES."/_thumb/".$value['filename']) ) continue;
				$pictures .= '<img src="'.URL_PRODUCTS_PICTURES."/_thumb/".$value['filename'].'" alt="'.$value['name'].'" />';	
		}
		return $pictures;
	}

	/*
	function smarty_function_category_picture($params, &$smarty){
		
		if( isset( $params['categoryID'] ) ){
			$_sql = db_query("SELECT categoryID FROM SC_categories WHERE parent = ".$params['categoryID']);
			$categories = array();
			while( $rows = db_fetch_row($_sql) ){
				$categories[] = $rows;
			}
			$product = '';
			if( is_array($categories) && count( $categories ) ){
				$_cats = '';
				foreach( $categories as $key => $value ){
					$_cats .= "'" . $value['categoryID'] . "',";
				}
				$inCats = substr( $_cats, strrpos(",", $_cats), strlen($_cats)-1 );
					
				// ту ща переберем все категории и ID gj ним
				$sql = db_query("SELECT pr.productID, pr.name_ru as name, c.categoryID, pict.filename
					FROM SC_categories c 
					RIGHT JOIN SC_products pr ON ( pr.categoryID = c.categoryID )
					RIGHT JOIN SC_product_pictures pict ON (pict.productID = pr.productID)
					WHERE c.categoryID IN (".$inCats.")");
					
					if( $sql ){
						while( $row = db_fetch_row( $sql ) ) {
							$result[ $row['productID'] ] = array(
								"filename" => $row['filename'],
								"name" => $row['name']
							);
						}
						
						$product .= '<div class="slideshow">';
						$product .=	formatProductPuctures($result);
						$product .= '</div>';
					}
			}else{
				$sql = db_query("SELECT pr.productID, pr.name_ru as name, c.categoryID, pict.filename
					FROM SC_categories c 
					RIGHT JOIN SC_products pr ON ( pr.categoryID = c.categoryID )
					RIGHT JOIN SC_product_pictures pict ON (pict.productID = pr.productID)
					WHERE c.categoryID IN (".$params['categoryID'].")");
					
				if( $sql ){
					while( $row = db_fetch_row($sql) )	{
						$result[ $row['productID'] ] = array(
							"filename" => $row['filename'],
							"name" => $row['name']
						);
					}
					
					$product .= '<div class="slideshow">';
					$product .=	formatProductPuctures($result);
					$product .= '</div>';
				}
			}
			
			return $product;

		}
	}
	
	function formatProductPuctures($params=array()){
		$pictures = '';
		foreach( $params as $key => $value ){
			if( ! file_exists(DIR_PRODUCTS_PICTURES."/".$value['filename']) ) continue;
				$pictures .= '<img src="'.URL_PRODUCTS_PICTURES."/".$value['filename'].'" alt="'.$value['name'].'" />';
		}
		return $pictures;
	}

	*/	

?>


