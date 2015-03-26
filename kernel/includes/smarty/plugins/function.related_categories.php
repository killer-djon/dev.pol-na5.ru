<?php

function smarty_function_related_categories( $params, &$smarty ){
	
	if( is_array($params) && count($params) ){
		if( isset( $params['categories'] ) && is_string( $params['categories'] ) ){
			
			$params['categories'] = preg_replace('/ /is', '', $params['categories']);
			
			$categories = explode(',', $params['categories']);
			
			$category =  array();
			
			if( count( $categories ) ){
				foreach( $categories as $key => $_catId ){
					$_category = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#CATEGORIES_TABLE WHERE categoryID=?', $_catId);
					LanguagesManager::ml_fillFields(CATEGORIES_TABLE, $_category);
					
					$category[] = array(
						"category" => $_category,
						"pictures" => catGetSubCategories($_category['categoryID'])
					);
					
				}
			}
		}
		
	}
	$smarty->assign("related_categories_count", count($category));
	$smarty->assign("related_categories", $category);
	$smarty->assign("main_content_template", "related_categories.tpl.html");
}

?>