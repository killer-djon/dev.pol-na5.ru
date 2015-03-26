<?php
/*Модуль дополнительные характеристики (расширенная) (версия 1)
Разработано: © JOrange.ru*/


function cptsettingview_ProductFilter_BlockGetCategoryes($params){
	$categoryes = ProductFilter_GetCategoryList(1,0);
	$params['options'] = array();
	$params['options'][0] = translate('prfilter_block_settings_allcategories');
	foreach ($categoryes as $category){
		if(!$category['hasTemplate']) continue;
		$params['options'][$category['categoryID']] = str_repeat("-", $category['level'])." ".$category['name'];
	}
	return cptsettingview_select($params);
}

function cptsettingserializer_ProductFilter_BlockGetCategoryes($params, $post){
	$Register = &Register::getInstance();
	if(!$Register->is_set('__AUXNAV_SERIALIZED') && is_array($post[$params['name']])){
		$post[$params['name']] = implode(':', $post[$params['name']]);
		$reg = 1;
		$Register->set('__AUXNAV_SERIALIZED', $reg);
	}
	return cptsettingserializer_select($params, $post);
}

function cptsettingview_ProductFilter_BlockGetTemplates($params){
	$templates = ProductFilter_GetOptionTemplates();
	$params['options'] = array();
	$params['options'][0] = translate('prfilter_block_settings_default');
	foreach ($templates as $template){
		$params['options'][$template['templateID']] = $template['templateName'];
	}
	return cptsettingview_select($params);
}

function cptsettingserializer_ProductFilter_BlockGetTemplates($params, $post){
	$Register = &Register::getInstance();
	if(!$Register->is_set('__AUXNAV_SERIALIZED') && is_array($post[$params['name']])){
		$post[$params['name']] = implode(':', $post[$params['name']]);
		$reg = 1;
		$Register->set('__AUXNAV_SERIALIZED', $reg);
	}
	return cptsettingserializer_select($params, $post);
}


//Delete all
function ProductFilter_DeleteAll(){
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_CATEGORYES_TABLE");
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_TABLE");
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_TEMPLATES_TABLE");
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE");
	db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE");
}

//Get all categories of options
function ProductFilter_GetOptionsCategoryes(){
	$category_name = LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_CATEGORYES_TABLE, 'category_name');
	$sort_name = LanguagesManager::sql_getSortField(PRODUCT_OPTIONS_CATEGORYES_TABLE, 'category_name');
	
	$sql = " SELECT `?#PRODUCT_OPTIONS_CATEGORYES_TABLE`.*, {$category_name} FROM `?#PRODUCT_OPTIONS_CATEGORYES_TABLE` ORDER BY `?#PRODUCT_OPTIONS_CATEGORYES_TABLE`.`sort`, {$sort_name}";
	$categoryes = db_phquery_fetch(DBRFETCH_ASSOC_ALL, $sql);
	if($categoryes){
		foreach($categoryes as $key => &$category){
			LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_CATEGORYES_TABLE, $category);
		}
	}
	return $categoryes;
}
		

//Add option category
function ProductFilter_AddOptionCategory($category_data){
	$category_name = LanguagesManager::sql_prepareFieldInsert('category_name', $category_data);
	$sort = preg_replace('!\D+!', "", $category_data['sort']);
	$sort_bd = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `sort` FROM ?#PRODUCT_OPTIONS_CATEGORYES_TABLE ORDER BY `sort` DESC LIMIT 0,1');
	if($sort=='') $sort = $sort_bd+1;
    $sql = "INSERT ".PRODUCT_OPTIONS_CATEGORYES_TABLE." ({$category_name['fields']}, `sort`) VALUES({$category_name['values']}, {$sort})";
	db_query($sql);	
	return db_insert_id();
}

//Update option category
function ProductFilter_UpdateOptionsCategory($category_data){
	if(!$category_data['categoryID'])return false;
	$category_name = LanguagesManager::sql_prepareFieldUpdate('category_name', $category_data);
	$sort = preg_replace('!\D+!', "", $category_data['sort']);
	$sort_bd = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `sort` FROM ?#PRODUCT_OPTIONS_CATEGORYES_TABLE ORDER BY `sort` DESC LIMIT 0,1');
	if($sort=='') $sort = $sort_bd+1;
	$sql = "UPDATE ".PRODUCT_OPTIONS_CATEGORYES_TABLE." SET {$category_name}, `sort`='{$sort}' where `categoryID`='{$category_data['categoryID']}';";
	db_query($sql);	
}


//Delete option category
function ProductFilter_DeleteOptionsCategory($categoryID){
	if(!$categoryID)return false;
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_CATEGORYES_TABLE WHERE `categoryID`=?", $categoryID);
	db_phquery("UPDATE ?#PRODUCT_OPTIONS_TABLE SET `optionCategory`='' WHERE `optionCategory`=?", $categoryID);
}

//Update option category sort
function ProductFilter_UpdateOptionsCategoryesSort($updateOptions){
	if(!empty($updateOptions)){
	foreach($updateOptions as $key => $val){
        $val["sort"] = xEscapeSQLstring($val["category_sort"] );
		db_phquery('UPDATE ?#PRODUCT_OPTIONS_CATEGORYES_TABLE SET `sort`=? WHERE `categoryID`=?', $val["sort"], $key);
    }
    }
}

//Update option category name
function ProductFilter_UpdateOptionsCategoryes($updateOptions){
	if(!empty($updateOptions)){
    foreach($updateOptions as $key => $val){
        $update_sql = LanguagesManager::sql_prepareTableUpdate(PRODUCT_OPTIONS_CATEGORYES_TABLE, $val, array('@category_name_(\w{2})@' => 'category_name_${1}'));
        $val["category_name"] = xEscapeSQLstring($val["category_name"] );
        $s = "UPDATE ".PRODUCT_OPTIONS_CATEGORYES_TABLE." SET {$update_sql} WHERE `categoryID`='{$key}';";
        db_query($s);    
    }
    }
}


//Add option
function ProductFilter_AddOption($option_data){
	$name_sql = LanguagesManager::sql_prepareFields('name', $option_data, true);
	$desctitle_sql = LanguagesManager::sql_prepareFields('description_title', $option_data, true);
	$desctext_sql = LanguagesManager::sql_prepareFields('description_text', $option_data, true);
	$prefix_sql = LanguagesManager::sql_prepareFields('slider_prefix', $option_data, true);
	$fields= $name_sql['fields_list'].','.$desctitle_sql['fields_list'].','.$desctext_sql['fields_list'].','.$prefix_sql['fields_list'];
	$values_place=str_repeat('?,',count($name_sql['values'])+count($desctitle_sql['values'])+count($desctext_sql['values'])+count($prefix_sql['values']));
							
	$sort = preg_replace('!\D+!', "", $option_data['sort_order']);
	$sort_bd = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `sort_order` FROM ?#PRODUCT_OPTIONS_TABLE WHERE `optionCategory`=? ORDER BY `sort_order` DESC LIMIT 0,1',$option_data['optionCategory']);
	if(empty($sort)) $sort = $sort_bd+1;
	$slider_step = (!empty($option_data["slider_step"]))?$option_data["slider_step"]:1;
		
	$sql = "INSERT ?#PRODUCT_OPTIONS_TABLE ( {$fields}, `sort_order`, `slider_step`, `optionType`, `optionCategory` ) ";
	$sql.="VALUES({$values_place}?,?,?,?)";

	db_phquery_array($sql,$name_sql['values'],$desctitle_sql['values'],$desctext_sql['values'],$prefix_sql['values'], $sort, $option_data['slider_step'],$option_data['optionType'],$option_data['optionCategory']);		
	return db_insert_id();
}

//Update optionS
function ProductFilter_UpdateOptions($options_data){
	if(!empty($options_data)){
		foreach($options_data as $optionID => $option){	
			if(!$optionID) continue;
			$update_sql = LanguagesManager::sql_prepareTableUpdate(PRODUCT_OPTIONS_TABLE, $option, array('@name_(\w{2})@' => 'extra_option_${1}', '@description_title_(\w{2})@' => 'extra_description_title_${1}', '@description_text_(\w{2})@' => 'extra_description_text_${1}', '@slider_prefix_(\w{2})@' => 'extra_slider_prefix_${1}'));		
			$sort = preg_replace('!\D+!', "", $option['extra_sort']);
			$sort_bd = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `sort_order` FROM ?#PRODUCT_OPTIONS_TABLE WHERE `optionCategory`=? ORDER BY `sort_order` DESC LIMIT 0,1',$option['extra_optionCategory']);
			if($sort=='') $sort = $sort_bd+1;
			$slider_step = floatval(preg_replace('/[^0-9|,|\.]/', '', str_replace(',','.',$option['extra_slider_step'])));
			$slider_step = (!empty($slider_step))?$slider_step:1;
			
			
			$sql = "UPDATE ".PRODUCT_OPTIONS_TABLE." SET {$update_sql}, `sort_order`='{$sort}', `slider_step`='{$slider_step}', `optionType`='{$option['extra_optionType']}', `optionCategory`='{$option['extra_optionCategory']}' where `optionID`='{$optionID}';";
			db_query($sql);	
		}
    }
}

//Update option
function ProductFilter_UpdateOption($option_data){
	if(!$option_data['optionID'])return false;
	$update_sql = LanguagesManager::sql_prepareTableUpdate(PRODUCT_OPTIONS_TABLE, $option_data, array('@name_(\w{2})@' => 'name_${1}', '@description_title_(\w{2})@' => 'description_title_${1}', '@description_text_(\w{2})@' => 'description_text_${1}', '@slider_prefix_(\w{2})@' => 'slider_prefix_${1}'));
	
	$sort = preg_replace('!\D+!', "", $option_data['sort_order']);
	$sort_bd = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `sort_order` FROM ?#PRODUCT_OPTIONS_TABLE WHERE `optionCategory`=? ORDER BY `sort_order` DESC LIMIT 0,1',$option_data['optionCategory']);
	if(empty($sort)) $sort = $sort_bd+1;
	$slider_step = (!empty($option_data["slider_step"]))?$option_data["slider_step"]:1;
	
	
	$sql = "UPDATE ".PRODUCT_OPTIONS_TABLE." SET {$update_sql}, `sort_order`='{$sort}', `slider_step`='{$slider_step}', `optionType`='{$option_data['optionType']}', `optionCategory`='{$option_data['optionCategory']}' where `optionID`='{$option_data["optionID"]}';";
	db_query($sql);	
	
}

//Delete option
function ProductFilter_DeleteOption($optionID){
	if(!$optionID) return false;
	ProductFilter_DeleteOptionFromTemplate($optionID);
	db_phquery("DELETE FROM ?#CATEGORY_PRODUCT_OPTION_VARIANTS WHERE `optionID`=?", $optionID);
	db_phquery("DELETE FROM ?#CATEGORY_PRODUCT_OPTIONS_TABLE WHERE `optionID`=?", $optionID);
	$pictures = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT `picture` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `optionID` = ?' ,$optionID);
	$uploadDir = DIR_PRODUCTS_PICTURES.'/filters/';
	if($pictures){
		foreach($pictures as $picture){
			if (file_exists($uploadDir.$picture)&& $picture) unlink($uploadDir.$picture);
		}
	}
	db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `optionID`=?", $optionID);
	db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE `optionID`=?", $optionID);
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE `optionID`=?", $optionID);
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_TABLE WHERE `optionID`=?", $optionID);
}


//Get param by ID
function ProductFilter_GetVariantById($variantID){	
	if(!$variantID) return false;
	$q = db_phquery('SELECT * FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `variantID`=?',$variantID);
	if ( $row=db_fetch_row($q) ){
		LanguagesManager::ml_fillFields(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, $row);
		return $row;
	}else return null;
}

//Add param
function ProductFilter_AddOptionValue($variant_data, $file = false){
	if(empty($variant_data['optionID'])) return false;
	
	$imageName = "";
	if($file["image"]["name"] !=''){
		$uploadDir = DIR_PRODUCTS_PICTURES.'/filters/';
		if(!is_dir($uploadDir)){
			mkdir($uploadDir, 0777);
			@chmod($uploadDir, 0777);
		}
		$imageinfo = getimagesize($file["image"]["tmp_name"]);
		if($imageinfo["mime"] == "image/jpeg" || $imageinfo["mime"] == "image/png") {
			$temp_file = $file["image"]["tmp_name"];
			$imageName = translit(xStripSlashesGPC($file["image"]["name"]));
			$imageName = str_replace('#','',urldecode($imageName));
			if(file_exists($uploadDir.$imageName))
			$imageName = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $imageName), $uploadDir);
			if(PEAR::isError($res = Functions::exec('file_copy', array($temp_file, $uploadDir.$imageName)))){
				$error = $res;
				if(file_exists($temp_file) && $temp_file) unlink($temp_file);		
				Functions::exec('file_remove', array($orig_file));
				Functions::exec('file_remove', array($uploadDir.$imageName));
				break;
			}
			if(file_exists($temp_file) && $temp_file) unlink($temp_file);	
			SetRightsToUploadedFile( $uploadDir.$imageName );
		}	
	}
	$name_sql = LanguagesManager::sql_prepareFields('option_value', $variant_data, true);
	$desctitle_sql = LanguagesManager::sql_prepareFields('description_title', $variant_data, true);
	$desctext_sql = LanguagesManager::sql_prepareFields('description_text', $variant_data, true);
	$fields= $name_sql['fields_list'].','.$desctitle_sql['fields_list'].','.$desctext_sql['fields_list'];
	$values_place=str_repeat('?,',count($name_sql['values'])+count($desctitle_sql['values'])+count($desctext_sql['values']));
	
	$sort = preg_replace('!\D+!', "", $variant_data['sort_order']);
	$sort_bd = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `sort_order` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `optionID`=? ORDER BY `sort_order` DESC LIMIT 0,1',$variant_data['optionID']);
	if($sort=='') $sort = $sort_bd+1;
	
	$sql = "INSERT ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE ( {$fields}, `optionID`, `sort_order`, `recomended`, `picture` ) ";
	$sql.="VALUES({$values_place}?,?,?,?)";
	
	db_phquery_array($sql,$name_sql['values'],$desctitle_sql['values'],$desctext_sql['values'], $variant_data['optionID'], $sort, $variant_data['recomended'], $imageName);		
	return db_insert_id();
}

//Update params
function ProductFilter_UpdateOptionValues($updateOptions, $files = false){ 	
	if(empty($updateOptions)) return false;
	foreach($updateOptions as $variantID => $variant_data){
		$variant_data = array_merge(array('variantID'=>$variantID), $variant_data);	
		ProductFilter_UpdateOptionValue($variant_data, $files[$variantID]);
    }	
}

//Update params
function ProductFilter_UpdateOptionValue($variant_data, $file = false){
   	
	if(empty($variant_data['variantID'])) return false;
	
	$imageName = "";
	if($file["image"]["name"] !=''){
		$uploadDir = DIR_PRODUCTS_PICTURES.'/filters/';
		if(!is_dir($uploadDir)){
			mkdir($uploadDir, 0777);
			@chmod($uploadDir, 0777);
		}
		$imageinfo = getimagesize($file["image"]["tmp_name"]);
		if($imageinfo["mime"] == "image/jpeg" || $imageinfo["mime"] == "image/png") {
			$temp_file = $file["image"]["tmp_name"];
			$imageName = translit(xStripSlashesGPC($file["image"]["name"]));
			$imageName = str_replace('#','',urldecode($imageName));
			if(file_exists($uploadDir.$imageName))
			$imageName = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $imageName), $uploadDir);
			if(PEAR::isError($res = Functions::exec('file_copy', array($temp_file, $uploadDir.$imageName)))){
				$error = $res;
				if(file_exists($temp_file) && $temp_file) unlink($temp_file);		
				Functions::exec('file_remove', array($orig_file));
				Functions::exec('file_remove', array($uploadDir.$imageName));
				break;
			}
			if(file_exists($temp_file)&& $temp_file) unlink($temp_file);	
			SetRightsToUploadedFile( $uploadDir.$imageName );
			
			$picture = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `picture` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE variantID=?',$variant_data["variantID"]);
			if (file_exists($uploadDir.$picture) && $picture) unlink($uploadDir.$picture);
			$sql = 'UPDATE ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE SET `picture`=?  WHERE `variantID`=?';
			db_phquery($sql, $imageName, $variant_data["variantID"]);
		}	
	}

	$sort = preg_replace('!\D+!', "", $variant_data['sort_order']);
	$sort_bd = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `sort_order` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `optionID`=? ORDER BY `sort_order` DESC LIMIT 0,1',$variant_data['optionID']);
	if($sort=='') $sort = $sort_bd+1;
	$update_sql = LanguagesManager::sql_prepareTableUpdate(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, $variant_data, array('@option_value_(\w{2})@' => 'option_value_${1}', '@description_title_(\w{2})@' => 'description_title_${1}', '@description_text_(\w{2})@' => 'description_text_${1}'));

	$sql = "UPDATE ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE." SET {$update_sql}, `sort_order`='{$sort}', `recomended`='{$variant_data['recomended']}' WHERE `variantID`='{$variant_data["variantID"]}';";
	db_query($sql);	
	
}


//Delete option param
function ProductFilter_DeleteOptionValue($variantID){
	if(!$variantID) return false;
	$picture = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `picture` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `variantID`=?',$variantID);
	$uploadDir = DIR_PRODUCTS_PICTURES.'/filters/';
	if (file_exists($uploadDir.$picture) && $picture) unlink($uploadDir.$picture);
	db_phquery('DELETE FROM ?#CATEGORY_PRODUCT_OPTION_VARIANTS WHERE `variantID`=?', $variantID);
	db_phquery('DELETE FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE `variantID`=?', $variantID);
	db_phquery('DELETE FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE `variantID`=?', $variantID);
	db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `variantID`=?", $variantID);
}

//Delete option param picture
function ProductFilter_DeleteOptionValuePicture($variantID){
	if(!$variantID) return false;
	$picture = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `picture` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `variantID`=?',$variantID);
	$uploadDir = DIR_PRODUCTS_PICTURES.'/filters/';
	if (file_exists($uploadDir.$picture) && $picture) unlink($uploadDir.$picture);
	db_phquery("UPDATE ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE set `picture` ='' WHERE `variantID`=?", $variantID);	
}

//Update / add exceptions for option param
function ProductFilter_UpdateOptionValuesBlock($variantID,$block){
	if(!$variantID) return false;
	$sql = 'UPDATE ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE SET `excluded`=? WHERE `variantID`=?';
	db_phquery($sql, $block, $variantID);
}


//Get all templates
function ProductFilter_GetOptionTemplates(){	
	$templates = db_phquery_fetch(DBRFETCH_ASSOC_ALL, 'SELECT * FROM ?#PRODUCT_OPTIONS_TEMPLATES_TABLE ORDER BY `templatePriority` ASC ');
	return $templates;
}

//Get template by id
function ProductFilter_GetOptionTemplateById($templateID){	
	if(!$templateID) return false;
	$template = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#PRODUCT_OPTIONS_TEMPLATES_TABLE WHERE `templateID`=?',$templateID);
	$template['template'] = unserialize($template['template']);
	$template['template_json'] = json_encode($template['template']);
	$template['categoryes'] = ProductFilter_GetCategoryesTemplate($templateID);
	return $template;
}

//Get used categorys for current template
function ProductFilter_GetCategoryesTemplate($templateID){	
	if(!$templateID) return false;
	$categoryesAll = db_phquery_fetch(DBRFETCH_ASSOC_ALL, 'SELECT `categoryID` FROM ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE WHERE `templateID`=?',$templateID);
	if(empty($categoryesAll)) return false;
	foreach( $categoryesAll as $key=>$value){	
		$path = catCalculatePathToCategory($value['categoryID']);
		$categoryesAll[$key]['path']="";
		if(!empty($path)){
			foreach($path as $p => $v){
				if($v['categoryID']=='1')continue;
				$categoryesAll[$key]['path'].= (($categoryesAll[$key]['path'])?" > ":" ").$v['name'];
			}
		}
	}
	return $categoryesAll;
}


//Prepare the template to add / save
function ProductFilter_PrepareOptionTemplate($post){	
	$compliteArray = array();
	$template = array();
	$i = 0;
	if(!empty($post['template'])){
	foreach($post['template'] as $categoryID=>$category){	
		$template[$i] = $category;
		$template[$i]['categoryID'] = $categoryID;
		$template[$i]['sort'] = $i;
		$a = 0;
		$options = array();
		if(!empty($category['options'])){
		foreach($category['options'] as $optionID=>$option){
			$options[$a]['optionID'] = $optionID;
			$options[$a]['sort'] = $a;
			if(is_array($option)) $options[$a] = array_merge($options[$a],$option);
			$a++;
		}}
		unset($template[$i]['options']);
		$template[$i]['options'] = $options;
		$i++;
	}}
	$compliteArray['slidecategoryes'] = ($post['slidecategoryes'])?1:0;
	$compliteArray['slideoptions']	  = ($post['slideoptions'])?1:0;
	$compliteArray['groupbycategory'] = ($post['groupbycategory'])?1:0;
	$compliteArray['template'] 		  = $template;
	$compliteArraySerialize = serialize($compliteArray);
	return $compliteArraySerialize;
}

//Add template
function ProductFilter_AddOptionTemplate($post){	
	$categoryes = $post['categoryes'];
	$templateName = trim($post['templateName']);
	$templateEnable = ($post['templateEnable'])?1:0;	
	$compliteArraySerialize = ProductFilter_PrepareOptionTemplate($post);
 	$templatePriority = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `templatePriority` FROM ?#PRODUCT_OPTIONS_TEMPLATES_TABLE ORDER BY `templatePriority` DESC LIMIT 0,1');
	db_phquery_array("INSERT INTO ?#PRODUCT_OPTIONS_TEMPLATES_TABLE (`templateName`,`template`,`templatePriority`,`templateEnable`) VALUES (?,?,?,?) ", $templateName, $compliteArraySerialize,$templatePriority+1, $templateEnable);
	$templateID = db_insert_id();	
	if(!empty($categoryes)){
	foreach($categoryes as $category){
		db_phquery_array("INSERT ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE ( `templateID`, `categoryID`) VALUES(?,?)",$templateID,$category);	
	}}
	return false;
}

//Save template
function ProductFilter_UpdateOptionTemplate($post){	
	if(!$post['templateID']) return false;
	$categoryes = $post['categoryes'];
	$templateName = trim($post['templateName']);
	$templateEnable = ($post['templateEnable'])?1:0;
	$compliteArraySerialize = ProductFilter_PrepareOptionTemplate($post);
	db_phquery("UPDATE ?#PRODUCT_OPTIONS_TEMPLATES_TABLE set `templateName`=?, `template`=?, `templateEnable`=? WHERE `templateID`=? ", $templateName, $compliteArraySerialize, $templateEnable, $post['templateID']);
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE  WHERE `templateID`=?", $post['templateID']);
	if(!empty($categoryes)){
	foreach($categoryes as $category){
		db_phquery_array("INSERT ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE ( `templateID`, `categoryID`) VALUES(?,?)",$post['templateID'],$category);	
	}}
	return false;
}

//Delete template
function ProductFilter_DeleteOptionTemplate($templateID){	
	if(!$templateID) return false;
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_TEMPLATES_TABLE WHERE `templateID`=? ", $templateID);
	db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE  WHERE `templateID`=?", $templateID);
	return true;
}

//Removal option in template
function ProductFilter_DeleteOptionFromTemplate($optionID){	
	if(!$optionID) return false;
	$templates = ProductFilter_GetOptionTemplates();
	if(!empty($templates)){
	foreach($templates as $key=>$template){
		$templateInfo = unserialize($template['template']);
		
		if(!empty($templateInfo['template'])){
		foreach($templateInfo['template'] as $key => &$category){
			foreach($category['options'] as $key => &$option){
				if($option['optionID']==$optionID){
					unset($category['options'][$key]);
				}
			}
		}}	
		$return = serialize($templateInfo);
		db_phquery("UPDATE ?#PRODUCT_OPTIONS_TEMPLATES_TABLE set `template`=? WHERE `templateID`=? ", $return, $template['templateID']);
	}
	}
}

//Update template sort
function ProductFilter_UpdatePriorityOptionTemplate($post){	
	if(!$post) return false;
	$data = scanArrayKeysForID($post, array("priority"));
	if(empty($data)) return false;
	foreach($data as $templateID => $priority){
		db_phquery("UPDATE ?#PRODUCT_OPTIONS_TEMPLATES_TABLE set `templatePriority`=? WHERE `templateID`=? ", $priority['priority'], $templateID);
	}
	return true;
}

//An array of options and params for current template
function ProductFilter_GetOptionToTemplate($categoryID=false){
	$options = optGetOptions();
	$option_values = optGetOptionValues(null,true);
	if(!empty($options)){
	
	foreach($options as $key=>&$option){
		if($categoryID!==false && $option['optionCategory']!=$categoryID){
			unset($options[$key]);
			continue;
		}
		$optionID = $option['optionID'];
		$option['variants'] = array();
		if(isset($option_values[$optionID])){
			$option['variants'] = $option_values[$optionID];
		}
		unset($variant);
	}}
	unset($option);
	return $options;
}

//Get maximum price for current category
function ProductFilter_GetMaxPriceOfCategory( $categoryID ){
	if($categoryID){
		$where_clause = "";
		$where_clause = _getConditionWithCategoryConj( $where_clause, $categoryID, true);	
		$sql = "SELECT `Price` FROM `?#PRODUCTS_TABLE` p WHERE ".$where_clause." ORDER BY `Price` DESC limit 0,1";
		$q = db_phquery($sql);
		while( $row=db_fetch_row($q) ){
			$max_price = $row['Price'];
		}
	}
	$max_price =($max_price == '' OR $max_price == '0')?1:$max_price;
	$currencyEntry = Currency::getSelectedCurrencyInstance();
	$max_price =  ceil($currencyEntry->convertUnits($max_price));
	return $max_price;
}

//Load a template for the block
function ProductFilter_GetTemplateByCategory($categoryID, $current=false, &$Options, $templateID=false){	
	if(!$templateID){if(!$categoryID) return false;}
	if($templateID){
		$sql = 'SELECT t.* FROM ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE c LEFT JOIN ?#PRODUCT_OPTIONS_TEMPLATES_TABLE t on c.templateID = t.templateID  where t.templateID = ? AND t.templateEnable = 1 order by templatePriority ASC limit 0,1';
		$template = db_phquery_fetch(DBRFETCH_ASSOC, $sql, $templateID);
	}else{
		$sql = 'SELECT t.* FROM ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE c LEFT JOIN ?#PRODUCT_OPTIONS_TEMPLATES_TABLE t on c.templateID = t.templateID  where c.categoryID = ? AND t.templateEnable = 1 order by templatePriority ASC limit 0,1';
		$template = db_phquery_fetch(DBRFETCH_ASSOC, $sql, $categoryID);
	}
	$templateID = $template['templateID'];
	$template = unserialize($template['template']);
	$template['templateID'] = $templateID;
	$Options = $template['template'];
	if(!empty($Options)){
	foreach($Options as $keyc => &$category){
		
		$iso2 = &LanguagesManager::getCurrentLanguage()->iso2;
		$default_iso2 = &LanguagesManager::getDefaultLanguage()->iso2;
		$categoryName = '';
		if($category['name_'.$iso2]){
			$categoryName = $category['name_'.$iso2];
		}elseif($category['name_'.$default_iso2]){
			$categoryName = $category['name_'.$default_iso2];
		}else{
			if(!empty($category)){
			foreach($category as $key=>$value){
				if(preg_match('/^name/', $key) && $value){
					$categoryName = $value; break;
				}
			}}
		}
		$category['name'] = $categoryName;
		if(!empty($category['options'])){
		foreach($category['options'] as $keyo => &$option){
			
			//if option is static
			if(in_array($option['optionID'],array('productname','price','instock'))){
				unset($option['variants']);
				if($option['optionID']=='productname'){
					$option['current'] = $current['opname'];
				}
				if($option['optionID']=='instock'){
					$option['current'] = ($current['opinstock'])?true:false;
				}
				if($option['optionID']=='price'){
					$Currency = Currency::getSelectedCurrencyInstance();
					$option['slider_prefix'] = str_replace('{value}','',$Currency->display_template);
					$option['slider_from'] = 0;
					$option['slider_to'] = ProductFilter_GetMaxPriceOfCategory($categoryID);
					$option['current_from'] = $current['opprice_from'];
					$option['current_to'] = $current['opprice_to'];
					$option['current'] = ($option['current_from']||$option['current_to'])?true:false;
				}
			//other options	
			}else{
				//option info
				$optionInfo = optGetOptionById($option['optionID']);
				if($optionInfo['optionType']=='slider'&&count($option['variants'])<2){
					unset($option);
					continue;
				}
				$option = array_merge($optionInfo, $option);	
				if(empty($option['variants'])) continue;
				
				//variants info
				$value = LanguagesManager::sql_constractSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'option_value');
				$description_title = LanguagesManager::sql_constractSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'description_title');
				$description_text = LanguagesManager::sql_constractSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'description_text');   
				$value_sort = LanguagesManager::sql_getSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'option_value');
				$sql = "SELECT *, {$value}, {$description_title}, {$description_text} FROM `?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE` WHERE `variantID` IN (?@) ORDER BY `optionID`, `sort_order`, {$value_sort}";
				$variants = array();
				
				
				if(is_array($option['variants'])){ $variants = db_phquery_fetch(DBRFETCH_ASSOC_ALL, $sql, $option['variants']); }
				$i = 0;
				if(!empty($variants)){
				foreach($variants as $key3 => &$variant){	
					$variant['count_excluded'] = ($variant['excluded'])?count(unserialize($variant['excluded'])):'0';
					LanguagesManager::ml_fillFields(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, $variant);
					if($option['params']['type']=='select'||$option['params']['type']=='radio'){
						$variant['current'] = ($current["op{$option['optionID']}"]==$variant['variantID'])?true:false;
						if($current["op{$option['optionID']}"]==$variant['variantID']) $i++;
					}else{
						$variant['current'] = ($current["op{$option['optionID']}_{$variant['variantID']}"])?true:false;
						if($current["op{$option['optionID']}_{$variant['variantID']}"]) $i++;
					}
				}
				$option['variants'] = $variants;	
				}
				if($optionInfo['optionType']=='simple') $option['current'] = ($i>0)?true:false;	
				if($optionInfo['optionType']=='slider'){									
					$numbers = array_map(prfilter_array_map,$option['variants']);
					$option['slider_from'] = min($numbers);
					$option['slider_to'] = max($numbers);
					$option['current_from'] = $current["op{$option['optionID']}_from"];
					$option['current_to'] = $current["op{$option['optionID']}_to"];
					$option['current'] = ($option['current_from']||$option['current_to'])?true:false;
					
				}	
				if($optionInfo['optionType']=='single'&&$option['variants']){
					$option['picture'] = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `picture` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE variantID = ?',$option['variants']);
					$option['current'] = ($current["op{$option['optionID']}"])?true:false;
				}
			}
	
		}}
	}}
	$template['template'] = $Options;
	return $template;
}
function prfilter_array_map($details){ 
	return $details['option_value'];
}
//Prepare SQL
function ProductFilter_prepareVariants($template, $optionID){
	$varTemp = array();
	if(!empty($template)){
	foreach($template as $key => $val){
		if(is_array($template[$key])){
			$varTemp[$template[$key]['optionID']][] = $template[$key]['value'];
		}
	}}
	return  "'".implode("', '",$varTemp[$optionID])."'";
}

//Prepare SQL
function ProductFilter_prepareVariantsOfValue($template, $optionID, $cnt){
	$varTemp = array();
	$sqls_params = array();
	if(!empty($template)){
	foreach($template as $key => $val){		
		$qtmp = db_phquery("SELECT ".LanguagesManager::sql_prepareField('option_value')." FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE"." WHERE optionID=? AND variantID=?",intval($val['optionID']),intval($val['value']));
		$rowtmp = db_fetch_row($qtmp);
		$item_text_value = ($rowtmp)?$rowtmp[0]:"";
		$search_name = 'option_value_'.$val['value'];
		$sqls_params[$search_name] = '%'.$item_text_value.'%';
			
		if($val['optionID']!=$optionID)continue;
		if($val['from'] || $val['to']){
			$varTemp['from'] = $val['from'];
			$varTemp['to'] = $val['to'];
		}else{
			$varTemp[] = $val['value'];
		}		
	}}
	if($varTemp['from'] || $varTemp['to']){
		if($varTemp['from'] && $varTemp['to']){
			return 'PrdOptVal'.$cnt.'.'.LanguagesManager::ml_getLangFieldName('option_value').' BETWEEN '.$varTemp['from'].' AND '.$varTemp['to'];
		}elseif($varTemp['from'] && !$varTemp['to']){
			return 'PrdOptVal'.$cnt.'.'.LanguagesManager::ml_getLangFieldName('option_value').' >= '.$varTemp['from'];
		}elseif(!$varTemp['from'] && $varTemp['to']){
			return 'PrdOptVal'.$cnt.'.'.LanguagesManager::ml_getLangFieldName('option_value').' <= '.$varTemp['to'];
		}	
	}elseif(count($varTemp)==1){
		return 'PrdOptVal'.$cnt.'.'.LanguagesManager::ml_getLangFieldName('option_value').' LIKE ?option_value_'.$varTemp[0];
	}elseif(count($varTemp)>1){
		return 'PrdOptVal'.$cnt.'.'.LanguagesManager::ml_getLangFieldName('option_value').' LIKE ?option_value_'.implode(' OR PrdOptVal'.$cnt.'.'.LanguagesManager::ml_getLangFieldName('option_value').' LIKE ?option_value_', $varTemp);
	}
}

//Get founded products (AJAX)
function ProductFilter_GetFoundedProductsAjax($POST){

	$categoryID = $POST['categoryID'];
	$extraParametrsTemplate = array();
	if(!empty($POST)){
	foreach ($POST as $key => $optionValue){
		if (strstr($key, "op")){
			$optionID = str_replace("op","",$key);
			$paramentr = explode("_", $optionID);	
			$item = array();
			//name
			if($paramentr[0]=='name'){	
				$extraParametrsTemplate['name']['value'] = $optionValue;
				$extraParametrsTemplate['name']['type'] = "standart";
			//price
			}elseif($paramentr[0]=='price'){
				$extraParametrsTemplate['price']['from']	= $POST["op{$paramentr[0]}_from"];
				$extraParametrsTemplate['price']['to']		= $POST["op{$paramentr[0]}_to"];
				$extraParametrsTemplate['price']['type'] = "standart";				
			//in stock
			}elseif($paramentr[0]=='instock'){	
				$extraParametrsTemplate['instock']['value'] = "yep";
				$extraParametrsTemplate['instock']['type'] = "standart";
				
			//sliders
			}elseif(in_array($paramentr[1],array('from','to'))){	
				if(!$POST["op{$paramentr[0]}_from"] && !$POST["op{$paramentr[0]}_to"]) continue;
				$item['optionID']	= $paramentr[0];			
				$item['from']	= $POST["op{$paramentr[0]}_from"];
				$item['to']		= $POST["op{$paramentr[0]}_to"];
				$item['type'] = "slider";
				if(!in_array($item,$extraParametrsTemplate)) $extraParametrsTemplate[] = $item;	
			//singles
			}elseif ($optionValue=='yep' && !$paramentr[1]){
				$variants = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT `variantID` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID = ?',$paramentr[0]);
				if(!empty($variants)){
				foreach($variants as $variantID){
					$item['optionID']	= $paramentr[0];
					$item['value']		= $variantID;	
					$item['type'] = "single";
					$extraParametrsTemplate[] = $item;	
				}}
			//other	
			}else{
				if(!$paramentr[1] && !$optionValue) continue;
				$item['optionID']	= $paramentr[0];	
				$item['value']	= ($paramentr[1])?xStripSlashesGPC($paramentr[1]):$optionValue;
				$item['type'] = "simple";
				$extraParametrsTemplate[] = $item;	
			}
		}
	}}
    $sqls_joins = array();
    $sqls_options = array();
    $sqls_params = array();   
    $cnt = 0;
    $done_optionID = array();
	if(!empty($extraParametrsTemplate)){
    foreach( $extraParametrsTemplate as $key => $item ){  
        if(!isset($item['optionID']))continue;
	    if(in_array($key, array('name','price','instock'), true)) continue;
		
		//Translate names
		$qtmp = db_phquery( "SELECT ".LanguagesManager::sql_prepareField('option_value')." FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID=? and variantID=?",intval($item['optionID']),intval($item['value']));
        $rowtmp = db_fetch_row($qtmp);
        $item_text_value = ($rowtmp)?$rowtmp[0]:"";
		if($item['value']){
			$search_name = 'option_value_'.$item['value'];
			$sqls_params[$search_name] = '%'.$item_text_value.'%';
		}	
		if($item['value'] === '')continue;
		
		//Dublicates
        if(in_array($item['optionID'],$done_optionID)) continue;
        $done_optionID[] = $item['optionID'];
        
		$sqls_joins[] = '
			LEFT JOIN '.PRODUCT_OPTIONS_VALUES_TABLE.' PrdOptVal'.$cnt.' ON p.productID=PrdOptVal'.$cnt.'.`productID`
			LEFT JOIN '.PRODUCTS_OPTIONS_SET_TABLE.' PrdOptSet'.$cnt.' ON p.productID=PrdOptSet'.$cnt.'.`productID`
		';
		$sqls_options[] = '
			PrdOptVal'.$cnt.'.optionID='.intval($item['optionID']).' AND 
			(( PrdOptVal'.$cnt.'.option_type=1 AND PrdOptSet'.$cnt.'.variantID IN ('.ProductFilter_prepareVariants($extraParametrsTemplate, $item['optionID']).'))
				OR 
			(PrdOptVal'.$cnt.'.option_type=0 AND '.ProductFilter_prepareVariantsOfValue($extraParametrsTemplate, $item['optionID'], $cnt).'))';  
        
		$cnt++;
    }}
	$where_clause = '';
	$_sqlParams = array();
	$where_clause.=($where_clause?' AND':'').' enabled=1';
	if ($extraParametrsTemplate['name']['value']){
		$_count = 0;
		$where_clause_name = '';
		$search_name = 'search_'.($_count++);
		$_sqlParams[$search_name] = '%'._searchPatternReplace($extraParametrsTemplate['name']['value']).'%';
		$where_clause_name .= ($where_clause_name?' AND':'').' '.LanguagesManager::sql_prepareField('name').' LIKE ?'.$search_name.' OR product_code LIKE ?'.$search_name;
		if($where_clause_name) $where_clause .= ($where_clause?' AND':'').' ('.$where_clause_name.')';
	}
	if($extraParametrsTemplate['price']['from']) 	$where_clause .= ($where_clause?' AND':'').' '.ConvertPriceToUniversalUnit($extraParametrsTemplate['price']['from']).'<=Price ';
	if($extraParametrsTemplate['price']['to']) 		$where_clause .= ($where_clause?' AND':'').' Price <= '.ConvertPriceToUniversalUnit($extraParametrsTemplate['price']['to']).' ';
	if(isset($extraParametrsTemplate['instock']['value']))	$where_clause .= ($where_clause?' AND':'').' in_stock > 0 ';
	
	$where_clause = _getConditionWithCategoryConj( $where_clause, $categoryID, true);
	$where_clause = $where_clause?'WHERE '.$where_clause:'';
	
	if(count($sqls_options)){
		$left_join = implode(' ', $sqls_joins);
		$where_clause = trim(str_replace('WHERE', '', $where_clause));
		$where_clause = 'WHERE '.($where_clause?'('.$where_clause.') AND ':'').'('.implode(') AND (',$sqls_options).')';
		$group_by = ' GROUP BY p.productID';
	}
	$_sqlParams = array_merge($_sqlParams,$sqls_params);
	$dbq = 'SELECT COUNT(DISTINCT p.productID) as cnt FROM '.PRODUCTS_TABLE.' p '.$left_join.$where_clause;
	$foundProducts = db_phquery_fetch(DBRFETCH_FIRST,$dbq,$_sqlParams);
	
	//Excluded params
	$excluded = array();
	if(!empty($extraParametrsTemplate)){
	foreach($extraParametrsTemplate as $key => $Param){
		if(in_array($Param['type'],array('standart','slider'))) continue;
		$excluded_serialize = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `excluded` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `variantID`=?',$Param['value']);
		if(!$excluded_serialize) continue;
		$excluded = array_merge($excluded,unserialize($excluded_serialize));
	}}
	$excluded = array_unique ($excluded );
	if(!empty($extraParametrsTemplate)){
	foreach($extraParametrsTemplate as $key => $Param){
		if (false !== $removedKey = array_search($Param['value'], $excluded)) {
			unset($excluded[$removedKey]);
		}
	}}	
	$excluded_simple = array();
	if($excluded){
		$excluded_simple = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT `variantID` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE v LEFT JOIN ?#PRODUCT_OPTIONS_TABLE o ON v.optionID = o.optionID WHERE `variantID` IN (?@) AND optionType="simple"',$excluded);
		$excluded_single = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT o.`optionID` FROM ?#PRODUCT_OPTIONS_TABLE o LEFT JOIN ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE v ON v.optionID = o.optionID WHERE v.`variantID` IN (?@) AND o.`optionType`="single"',$excluded);
		if($excluded_single){
			$excluded_single = array_unique ($excluded_single );
			if(!empty($excluded_single)){
			foreach($excluded_single as $optionID){
				$excluded_simple[] = 's_'.$optionID;
			}}
		}
	}
	return array('count'=>$foundProducts,'excluded'=>$excluded_simple);
}

//Get founded products 
function ProductFilter_GetFoundedProducts($POST, &$count_row, $navigatorParams = null){
	$categoryID = $POST['categoryID'];
	$extraParametrsTemplate = array();
	if(!empty($POST)){
	foreach ($POST as $key => $optionValue){
		if (strstr($key, "op")){
			$optionID = str_replace("op","",$key);
			$paramentr = explode("_", $optionID);	
			$item = array();
			//Name
			if($paramentr[0]=='name'){	
				$extraParametrsTemplate['name']['value'] = $optionValue;
				$extraParametrsTemplate['name']['type'] = "standart";
			//Price
			}elseif($paramentr[0]=='price'){
				$extraParametrsTemplate['price']['from']	= $POST["op{$paramentr[0]}_from"];
				$extraParametrsTemplate['price']['to']		= $POST["op{$paramentr[0]}_to"];
				$extraParametrsTemplate['price']['type'] = "standart";				
			//In stock
			}elseif($paramentr[0]=='instock'){	
				$extraParametrsTemplate['instock']['value'] = "yep";
				$extraParametrsTemplate['instock']['type'] = "standart";
				
			//sliders
			}elseif(in_array($paramentr[1],array('from','to'))){	
				if(!$POST["op{$paramentr[0]}_from"] && !$POST["op{$paramentr[0]}_to"]) continue;
				$item['optionID']	= $paramentr[0];			
				$item['from']	= $POST["op{$paramentr[0]}_from"];
				$item['to']		= $POST["op{$paramentr[0]}_to"];
				$item['type'] = "slider";
				if(!in_array($item,$extraParametrsTemplate)) $extraParametrsTemplate[] = $item;	
			//Singles
			}elseif ($optionValue=='yep' && !$paramentr[1]){
				$variants = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT `variantID` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID = ?',$paramentr[0]);
				if(!empty($variants)){
				foreach($variants as $variantID){
					$item['optionID']	= $paramentr[0];
					$item['value']		= $variantID;	
					$item['type'] = "single";
					$extraParametrsTemplate[] = $item;	
				}}
			//Other	
			}else{
				if(!$paramentr[1] && !$optionValue) continue;
				$item['optionID']	= $paramentr[0];	
				$item['value']	= ($paramentr[1])?xStripSlashesGPC($paramentr[1]):$optionValue;
				$item['type'] = "simple";
				$extraParametrsTemplate[] = $item;	
			}
		}
	}}
    $sqls_joins = array();
    $sqls_options = array();
    $sqls_params = array();   
    $cnt = 0;
    $done_optionID = array();
	if(!empty($extraParametrsTemplate)){
    foreach( $extraParametrsTemplate as $key => $item ){  
        if(!isset($item['optionID']))continue;
	    if(in_array($key, array('name','price','instock'), true)) continue;
		
		//Translate names
		$qtmp = db_phquery( "SELECT ".LanguagesManager::sql_prepareField('option_value')." FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID=? and variantID=?",intval($item['optionID']),intval($item['value']));
        $rowtmp = db_fetch_row($qtmp);
        $item_text_value = ($rowtmp)?$rowtmp[0]:"";
		if($item['value']){
			$search_name = 'option_value_'.$item['value'];
			$sqls_params[$search_name] = '%'.$item_text_value.'%';
		}	
		if($item['value'] === '')continue;
		
		//Dublicates
        if(in_array($item['optionID'],$done_optionID)) continue;
        $done_optionID[] = $item['optionID'];
        
		$sqls_joins[] = '
			LEFT JOIN '.PRODUCT_OPTIONS_VALUES_TABLE.' PrdOptVal'.$cnt.' ON p.productID=PrdOptVal'.$cnt.'.`productID`
			LEFT JOIN '.PRODUCTS_OPTIONS_SET_TABLE.' PrdOptSet'.$cnt.' ON p.productID=PrdOptSet'.$cnt.'.`productID`
		';
		$sqls_options[] = '
			PrdOptVal'.$cnt.'.optionID='.intval($item['optionID']).' AND 
			(( PrdOptVal'.$cnt.'.option_type=1 AND PrdOptSet'.$cnt.'.variantID IN ('.ProductFilter_prepareVariants($extraParametrsTemplate, $item['optionID']).'))
				OR 
			(PrdOptVal'.$cnt.'.option_type=0 AND '.ProductFilter_prepareVariantsOfValue($extraParametrsTemplate, $item['optionID'], $cnt).'))';  
        
		$cnt++;
    }}

	$limit = $navigatorParams != null?' LIMIT '.(int)$navigatorParams['offset'].','.(int)$navigatorParams['CountRowOnPage']:'';
	$where_clause = '';
	$_sqlParams = array();
	$where_clause.=($where_clause?' AND':'').' enabled=1';
	if ($extraParametrsTemplate['name']['value']){
		$_count = 0;
		$where_clause_name = '';
		$search_name = 'search_'.($_count++);
		$_sqlParams[$search_name] = '%'._searchPatternReplace($extraParametrsTemplate['name']['value']).'%';
		$where_clause_name .= ($where_clause_name?' AND':'').' '.LanguagesManager::sql_prepareField('name').' LIKE ?'.$search_name.' OR product_code LIKE ?'.$search_name;
		if($where_clause_name) $where_clause .= ($where_clause?' AND':'').' ('.$where_clause_name.')';
	}
	if($extraParametrsTemplate['price']['from']) 	$where_clause .= ($where_clause?' AND':'').' '.ConvertPriceToUniversalUnit($extraParametrsTemplate['price']['from']).'<=Price ';
	if($extraParametrsTemplate['price']['to']) 		$where_clause .= ($where_clause?' AND':'').' Price <= '.ConvertPriceToUniversalUnit($extraParametrsTemplate['price']['to']).' ';
	if(isset($extraParametrsTemplate['instock']['value']))	$where_clause .= ($where_clause?' AND':'').' in_stock > 0 ';
	
	$where_clause = _getConditionWithCategoryConj( $where_clause, $categoryID, true);
	$where_clause = $where_clause?'WHERE '.$where_clause:'';
	
	if(count($sqls_options)){
		$left_join = implode(' ', $sqls_joins);
		$where_clause = trim(str_replace('WHERE', '', $where_clause));
		$where_clause = 'WHERE '.($where_clause?'('.$where_clause.') AND ':'').'('.implode(') AND (',$sqls_options).')';
		$group_by = ' GROUP BY p.productID';
	}
	$_sqlParams = array_merge($_sqlParams,$sqls_params);
	
	$sort_field = 'name';
	$order_by_clause = ' ORDER BY sort_order, '.LanguagesManager::sql_getSortField(PRODUCTS_TABLE, $sort_field);
	if(isset($POST['callBackParam']['sort'])&&in_array($POST['callBackParam']['sort'],array('name','brief_description','in_stock','Price','customer_votes','customers_rating',
	'list_price','sort_order','items_sold','product_code','shipping_freight'))){
		$order_by_clause = ' ORDER BY '.LanguagesManager::sql_getSortField(PRODUCTS_TABLE, $POST['callBackParam']['sort']).' ASC ';
		if (isset($POST['callBackParam']['direction'])&&$POST['callBackParam']['direction'] == 'DESC')$order_by_clause = ' ORDER BY '.LanguagesManager::sql_getSortField(PRODUCTS_TABLE, $POST['callBackParam']['sort']).' DESC ';
	}
	if($count_row&&isset($navigatorParams['offset'])&&($count_row<$navigatorParams['offset'])){
		$navigatorParams['offset'] = $navigatorParams['CountRowOnPage']*intval($count_row/$navigatorParams['CountRowOnPage']);
	}
	$limit = $navigatorParams != null?' LIMIT '.(int)$navigatorParams['offset'].','.(int)$navigatorParams['CountRowOnPage']:'';
	$dbq = 'SELECT '.($limit?'SQL_CALC_FOUND_ROWS ':'').' p.*, '.LanguagesManager::sql_constractSortField(PRODUCTS_TABLE, $sort_field).' FROM '.PRODUCTS_TABLE.' p '.$left_join.$where_clause.$group_by.' '.$order_by_clause.$limit;
	$Result = db_phquery($dbq,$_sqlParams);
	if($limit){
		$dbq = 'SELECT FOUND_ROWS()';
		$count_row = db_phquery_fetch(DBRFETCH_FIRST,$dbq,$_sqlParams);
	}
	$Products = array();
	$ProductsIDs = array();
	$Counter = 0;
	while ($_Product = db_fetch_assoc($Result)) {
		LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $_Product);
		if (!$_Product["productID"] && ($_Product[0]>0)) $_Product["productID"] = $_Product[0]; 
		$_Product['PriceWithUnit'] = show_price($_Product['Price']);
		$_Product['list_priceWithUnit'] = show_price($_Product['list_price']);
		// you save (value)
		$_Product['SavePrice'] = show_price($_Product['list_price']-$_Product['Price']);
		// you save (%)
		if($_Product['list_price'])$_Product['SavePricePercent'] = ceil(((($_Product['list_price']-$_Product['Price'])/$_Product['list_price'])*100));
		$_Product['PriceWithOutUnit']	= show_priceWithOutUnit( $_Product['Price'] );
		if ( ((float)$_Product['shipping_freight']) > 0 )
		$_Product['shipping_freightUC'] = show_price( $_Product['shipping_freight'] );
		$ProductsIDs[$_Product['productID']] = $Counter;
		$_Product['vkontakte_update_timestamp']= ($_Product['vkontakte_update_timestamp']>0)?Time::standartTime( $_Product['vkontakte_update_timestamp'] ):'';
		
		$Products[] = $_Product;
		$Counter++;
	}
	if(!$limit){
		$count_row = $Counter;
	}
	$ProductsExtra = GetExtraParametrs(array_keys($ProductsIDs));
	if(!empty($ProductsExtra)){
	foreach ($ProductsExtra as $_ProductID=>$_Extra){
		$Products[$ProductsIDs[$_ProductID]]['product_extra'] = $_Extra;
	}}
	_setPictures($Products);
	if(!empty($ProductsExtra)){
	foreach ($Products as $key=>$Product){
		if($key==='') unset($Products[$key]);
	}}
	return $Products;
}

//Items in variable smarty
function ProductFilter_ShowProducts($in, $callBackParam, $smarty){
	$products = array();
	$count = 0;
	if ( isset($in['sort']) )$callBackParam['sort'] = $in['sort'];
	if ( isset($in['direction']) )$callBackParam['direction'] = $in['direction'];	
	$in['callBackParam'] = $callBackParam;	
	$navigatorHtml = GetNavigatorHtml( 'categoryID='.$categoryID, CONF_PRODUCTS_PER_PAGE, 'ProductFilter_GetFoundedProducts', $in, $products, $offset, $count );	
	$smarty->assign( 'show_comparison', $count>1 );
	$smarty->assign( 'catalog_navigator', $navigatorHtml );
	$smarty->assign( 'products_to_show', $products);
	return true;
}

//Get excluded params 
function ProductFilter_GetExcludedParams($POST){
	$categoryID = $POST['categoryID'];
	$extraParametrsTemplate = array();
	if(!empty($POST)){
	foreach ($POST as $key => $optionValue){
		if (strstr($key, "op")){
			$optionID = str_replace("op","",$key);
			$paramentr = explode("_", $optionID);	
			$item = array();	
			if(in_array($paramentr[0],array('name','price','instock'))||in_array($paramentr[1],array('from','to'))) continue;
			//Singles
			if ($optionValue=='yep' && !$paramentr[1]){
				$variants = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT `variantID` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID = ?',$paramentr[0]);
				if(!empty($variants)){
				foreach($variants as $variantID){
					$item['optionID']	= $paramentr[0];
					$item['value']		= $variantID;	
					$extraParametrsTemplate[] = $item;	
				}}
			//Other	
			}else{
				if(!$paramentr[1] && !$optionValue) continue;
				$item['optionID']	= $paramentr[0];	
				$item['value']	= ($paramentr[1])?xStripSlashesGPC($paramentr[1]):$optionValue;
				$extraParametrsTemplate[] = $item;	
			}
		}
	} }	
	//Excleded params
	$excluded = array();
	if(!empty($extraParametrsTemplate)){
	foreach($extraParametrsTemplate as $key => $Param){
		$excluded_serialize = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT `excluded` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `variantID`=?',$Param['value']);
		if(!$excluded_serialize) continue;
		$excluded = array_merge($excluded,unserialize($excluded_serialize));
	}}
	$excluded = array_unique ($excluded );
	if(!empty($extraParametrsTemplate)){
	foreach($extraParametrsTemplate as $key => $Param){
		if (false !== $removedKey = array_search($Param['value'], $excluded)) {
			unset($excluded[$removedKey]);
		}
	}}	
	$excluded_simple = array();
	if($excluded){
		$excluded_simple = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT `variantID` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE v LEFT JOIN ?#PRODUCT_OPTIONS_TABLE o ON v.optionID = o.optionID WHERE `variantID` IN (?@) AND optionType="simple"',$excluded);
		$excluded_single = db_phquery_fetch(DBRFETCH_FIRST_ALL, 'SELECT o.`optionID` FROM ?#PRODUCT_OPTIONS_TABLE o LEFT JOIN ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE v ON v.optionID = o.optionID WHERE v.`variantID` IN (?@) AND o.`optionType` ="single"',$excluded);
		if($excluded_single){
			$excluded_single = array_unique ($excluded_single );
			if(!empty($excluded_single)){
			foreach($excluded_single as $optionID){
				$excluded_simple[] = 's_'.$optionID;
			}}
		}
	}
	return $excluded_simple;
}


//Loading categories with the presence of template
function ProductFilter_GetCategoryList( $parent, $level){
	$categoryes = ProductFilter_recursiveGetCategoryList( $parent, $level);
	if(!empty($categoryes)){
	foreach($categoryes as $key => $category){
		if($category['hasTemplate']>0){
			$visibleC = array();
			$visibleC = ProductFilter_recursiveGetCategoryParent($categoryes, $category['parent']);
			if(!empty($visibleC)){
				foreach($visibleC as $key => $item){
					$categoryes[$item]['visible'] = true;
				}
			}
		}
	}}
	return $categoryes;
}

function ProductFilter_recursiveGetCategoryParent( $array, $value){
	foreach ($array as $i => $v)$arrayx[$i] = $v['categoryID'];
	$key = array_search($value, $arrayx);
	$result = array();
	$result[] = $key;
	if($key||$key===0) $sub = ProductFilter_recursiveGetCategoryParent( $array, $array[$key]['parent']);
	for ($j=0; $j<count($sub); $j++)
	$result[] = $sub[$j];
	if($key||$key===0) return $result;
}

function ProductFilter_recursiveGetCategoryList( $parent, $level){
	$name = LanguagesManager::sql_prepareField('name', true);
	$sql = "SELECT `categoryID`, {$name}, `parent`, `slug` as `category_slug` FROM `?#CATEGORIES_TABLE` WHERE `parent`=? ORDER BY `sort_order`, `name`";
	$q = db_phquery($sql,$parent);
	$result = array();
	while ($row = db_fetch_row($q)){
		$row["hasTemplate"] = $categoryest = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT count(*) FROM ?#PRODUCT_OPTIONS_TEMPLATES_CATEGORYES_TABLE WHERE `categoryID`=?',$row["categoryID"]);
		$row["level"] = $level;
		$result[] = $row;
		$subcategories = ProductFilter_recursiveGetCategoryList( $row["categoryID"], $level+1);
		for ($j=0; $j<count($subcategories); $j++)
		$result[] = $subcategories[$j];
	}
	return $result;
}

//Minimizing the links (remove unnecessary parameters)
function ProductFilter_MinimizeGetUrl(){
	$i = 0;
	$query = $_GET;
	foreach($query as $key => $val) if(!$val){ unset($query[$key]); $i++; }
	if (sizeof($query) > 0) $pageURL .= '?' . http_build_query($query);
	if($i) Redirect( set_query($pageURL) );
}


//Import/Export excel functions BadSymbolsToExcel
function ProductFilter_getImportFields(){
	$options_fields = array(
		'id' => translate('prfilter_excel_id'),
		'name' => translate('prfilter_excel_name'),
		'sort_order' => translate('prfilter_excel_sort_order'),
		'description_title' => translate('prfilter_excel_description_title'),
		'description_text' => translate('prfilter_excel_description_text'),
		'optionType' => translate('prfilter_excel_optiontype'),
		'slider_step' => translate('prfilter_excel_slider_step'),
		'slider_prefix' => translate('prfilter_excel_slider_prefix'),
		'recomended' => translate('prfilter_excel_recomended'),
	);
	$r_languageEntry = LanguagesManager::getLanguages();
	$fields = array();
	foreach ($options_fields as $field=>$title){
		if(!LanguagesManager::ml_isMLField(PRODUCT_OPTIONS_TABLE, $field)){
			$fields[$field] = $title;
			continue;
       	}
		for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){
			$fields[LanguagesManager::ml_getLangFieldName($field, $r_languageEntry[$_j])] = $title.($_j_max>1?' ('.$r_languageEntry[$_j]->getName().')':'');
		}
	}
	return $fields;
}

function ProductFilter_getUniqueColumns(){
	$product_fields = array(
		'id' => translate('prfilter_excel_id'),
		'name' => translate('prfilter_excel_name'),
	);	
	$r_languageEntry = LanguagesManager::getLanguages();
	$defaultLanguage = &LanguagesManager::getDefaultLanguage();
	$fields = array();
	foreach ($product_fields as $field=>$title){
		if(!LanguagesManager::ml_isMLField(PRODUCT_OPTIONS_TABLE, $field)){
			$fields[$field] = $title;
			continue;
       	}
       	$_j_max = count($r_languageEntry);
		$fields[LanguagesManager::ml_getLangFieldName($field, $defaultLanguage)] = $title.($_j_max>1?' ('.$defaultLanguage->getName().')':'');
       	for($_j = 0; $_j<$_j_max; $_j++){
			$fields[LanguagesManager::ml_getLangFieldName($field, $r_languageEntry[$_j])] = $title.($_j_max>1?' ('.$r_languageEntry[$_j]->getName().')':'');
		}
	}
	return $fields;
}


//Export options to excel
function ProductFilter_ExportToExcel($data){
	$charset=($data['charset'])?$data['charset']:'cp1251';	
	$delimiter=($data['delimiter'])?(($data['delimiter']=='\t')?chr(9):$data['delimiter']):';';	
	$f = fopen( DIR_TEMP."/product_options.csv", "w" );
	$r_languageEntry = LanguagesManager::getLanguages();
	$defaultLanguage = &LanguagesManager::getDefaultLanguage();
	$fields = ProductFilter_getImportFields();
	fputcsv( $f, $fields, $delimiter );
	
	$categoryes = ProductFilter_GetOptionsCategoryes();
	$categoryes = array_merge(array(array('categoryID'=>0)),$categoryes);

	if(!empty($categoryes)){
		foreach($categoryes as $key=>$category){
		//Category
			if(!empty($category['categoryID'])){
				$line = array();
				$line['categoryID'] = $category['categoryID'];	
				for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){
					$line[LanguagesManager::ml_getLangFieldName('category_name', $r_languageEntry[$_j])] = '!'.$category[LanguagesManager::ml_getLangFieldName('category_name',$r_languageEntry[$_j])];
				}
				$line['sort'] = $category['sort'];	
				fputcsv( $f, $line, $delimiter );
				unset($line);
			}
				
		//Options	
			$params = ProductFilter_GetOptionToTemplate($category['categoryID']);
			if(!empty($params)){
				$lines = array();
				foreach($params as $key => $param){		
					$line = array();
					$line['optionID'] = $param['optionID'];
					for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){
						$line[LanguagesManager::ml_getLangFieldName('name', $r_languageEntry[$_j])] = '!!'.$param[LanguagesManager::ml_getLangFieldName('name',$r_languageEntry[$_j])];
					} 
					$line['sort_order'] = $param['sort_order'];
					for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){	
						$line[LanguagesManager::ml_getLangFieldName('description_title', $r_languageEntry[$_j])] = $param[LanguagesManager::ml_getLangFieldName('description_title',$r_languageEntry[$_j])];
					} 
					for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){
						$line[LanguagesManager::ml_getLangFieldName('description_text', $r_languageEntry[$_j])] = $param[LanguagesManager::ml_getLangFieldName('description_text',$r_languageEntry[$_j])];
					} 
					$line['optionType'] = (empty($param['optionType']))?'simple':$param['optionType'];
					$line['slider_step'] = ($param['optionType']=='slider')?$param['slider_step']:''; 	
					for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){	
						$line[LanguagesManager::ml_getLangFieldName('slider_prefix', $r_languageEntry[$_j])] = ($param['optionType']=='slider')?$param[LanguagesManager::ml_getLangFieldName('slider_prefix',$r_languageEntry[$_j])]:'';
					} 
					fputcsv( $f, $line, $delimiter );
					unset($line);
					
					//Params
					if(!empty($param['variants'])){
					foreach($param['variants'] as $key => $variant){	
						$line = array();
						$line['variantID'] = $variant['variantID'];
						for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){
							$line[LanguagesManager::ml_getLangFieldName('option_value', $r_languageEntry[$_j])] = $variant[LanguagesManager::ml_getLangFieldName('option_value',$r_languageEntry[$_j])];
						} 
						$line['sort_order'] = $variant['sort_order'];
						for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){	
							$line[LanguagesManager::ml_getLangFieldName('description_title', $r_languageEntry[$_j])] = $variant[LanguagesManager::ml_getLangFieldName('description_title',$r_languageEntry[$_j])];
						} 
						for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++){
							$line[LanguagesManager::ml_getLangFieldName('description_text', $r_languageEntry[$_j])] = $variant[LanguagesManager::ml_getLangFieldName('description_text',$r_languageEntry[$_j])];
						} 
						$line['empty1'] = '';
						$line['empty2'] = ''; 
						for($_j = 0, $_j_max = count($r_languageEntry); $_j<$_j_max; $_j++) $line[LanguagesManager::ml_getLangFieldName('empty3', $r_languageEntry[$_j])] = '';
						$line['recomended'] = $variant['recomended'];
						fputcsv( $f, $line, $delimiter );
						unset($line);	
					}}
				}
			}
		}
	}
	fclose($f);
	if($charset && $charset != DEFAULT_CHARSET){ File::convert(DIR_TEMP."/product_options.csv",DEFAULT_CHARSET, $charset); }	
}

//Import options from excel (step 1)
function ProductFilter_ImportFromExcel1($data){
	$res = 0;
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	$FilesVar = &$Register->get(VAR_FILES);
	
	$file_excel_name = DIR_TEMP."/file.csv";
	if (isset($FilesVar["csv"]) && $FilesVar["csv"]["name"]){
		do{	
			$res = File::checkUpload($FilesVar["csv"]);
			if(PEAR::isError($res)){
				$error = $res;
				break;
			}	
			if(
			PEAR::isError($res = File::checkUpload($FilesVar['csv']))||
			PEAR::isError($res = File::move_uploaded($FilesVar['csv']['tmp_name'],$file_excel_name))
			){
				$error = $res;
				break;
			}	
			if($data['charset'] && strtoupper($data['charset']) != strtoupper(DEFAULT_CHARSET)){
				if(!File::convert($file_excel_name,$data['charset'],DEFAULT_CHARSET,true)){
					$error = PEAR::raiseError('error_convert_file_encoding');
					break;
				}
			}	
			File::chmod($file_excel_name);
			$smarty->assign("file_excel_name", $file_excel_name);	
			$optionsImport = new ImportOptions($file_excel_name,$data["delimeter"]);	
			$option_fields = ProductFilter_getImportFields();
			$option_unique = ProductFilter_getUniqueColumns();
			if(!$optionsImport->readCsvLine()){
				$error = PEAR::raiseError('error_read_csv_file');
				break;
			}
			$optionsImport->setTargetColumns(array('main'=>$option_fields));
			$optionsImport->setPrimaryCols($option_unique);
			$optionsImport->setConfiguratorHeader(array('prdimport_source_column','&nbsp;','prdimport_target_column','prdimport_primary_column'));
			$excel_configurator = $optionsImport->getDataMappingHtmlConfigurator(true,false,'name');
			
			$smarty->assign("excel_import_configurator", $excel_configurator);
			$smarty->assign("source_column_count", sprintf(translate('prdimport_found_n_columns'),$optionsImport->getSourceColumnCount()));
			$smarty->assign("source_columns", $optionsImport->getSourceColumns());
			$smarty->assign("delimeter", $data["delimeter"]);	
			
		}while(false);
	}
	if (isset($error)){
		Message::raiseMessageRedirectSQ(MSG_ERROR,'',$error->getMessage());
	}
}

//Import options from excel (step 2)
function ProductFilter_ImportFromExcel2($POST){
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	$FilesVar = &$Register->get(VAR_FILES);
	if (CONF_BACKEND_SAFEMODE) Redirect(set_query('safemode=yes'));

	@set_time_limit(0);

	$importOptions = new ImportOptions($POST["filename"],$POST["delimeter"]);
	$line = $importOptions->readCsvLine();
	$importOptions->parseDataMapping();
	$data = $importOptions->applyDataMapping($line);
	if (!$importOptions->primary_col||!$importOptions->isValidData($data['main'])){ //not set update column
		$smarty->assign("excel_import_result", "update_column_error");
		//go to the previous step
		$proceed = 1;
		$file_excel = "";
		$file_excel_name = $POST["filename"];
		$res = 1;
	}else{
		$session_id = session_id();
		session_write_close();
		$maxCount=0;
		$msg='';
		$limitExceed=false;
		if(SystemSettings::is_hosted()){
			$messageClient = new WbsHttpMessageClient($db_key, 'wbs_msgserver.php');
			$messageClient->putData('action', 'ALLOW_ADD_PRODUCT');
			$messageClient->putData('language',LanguagesManager::getCurrentLanguage()->iso2);
			$messageClient->putData('session_id',$session_id);
			$res=$messageClient->send();
		}else{
			$res = false;
		}
		if($res&&($messageClient->getResult('max')>0)){
			$maxCount=$messageClient->getResult('max')-$messageClient->getResult('current');
			$msg=$messageClient->getResult('msg');
			if($messageClient->getResult('success')!==true){
				$limitExceed=true;
			}	
			while($line = $importOptions->readCsvLine()){
				$data = $importOptions->applyDataMapping($line);
				$statistic = $importOptions->import($data,($maxCount>0));
				if($statistic['insert']){
					$maxCount--;
				}
			}
			$messageClient = new WbsHttpMessageClient($db_key, 'wbs_msgserver.php');
			$messageClient->putData('action', 'ALLOW_ADD_PRODUCT');
			$messageClient->putData('language',LanguagesManager::getCurrentLanguage()->iso2);
			$messageClient->putData('session_id',$session_id);
			$messageClient->send();
			$msg=$messageClient->getResult('msg');
			$limitExceed=!$messageClient->getResult('success');
			session_id($session_id);
			session_start();	
			if(strlen($msg)&&$limitExceed){
				$msg='<div class="error_block" ><span class="error_message">'.$msg.'</span></div>';
			}elseif(strlen($msg)){
				$msg='<div class="comment_block" ><span class="success_message">'.$msg.'</span></div>';
			}
			$smarty->assign('limit_msg',$msg);
		}else{
			$line_counter = 0;	
			while($line = $importOptions->readCsvLine()){
				$line_counter++;
				$data = $importOptions->applyDataMapping($line);
				$statistic = $importOptions->import($data);
			}
		}		
		$smarty->assign("excel_import_result", "ok");
		$smarty->assign('category_added',$statistic['category_added']);
		$smarty->assign('category_modify',$statistic['category_modify']);
		$smarty->assign('option_added',$statistic['option_added']);
		$smarty->assign('option_modify',$statistic['option_modify']);
		$smarty->assign('variant_added',$statistic['variant_added']);
		$smarty->assign('variant_modify',$statistic['variant_modify']);
		
	}
}


//The old functions changed
function optGetOptions(){
	$name = LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'name',true);
	$description_title = LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'description_title',true);
    $description_text = LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'description_text',true);    
    $slider_prefix = LanguagesManager::sql_constractSortField(PRODUCT_OPTIONS_TABLE, 'slider_prefix',true);    
	$sort_name = LanguagesManager::sql_getSortField(PRODUCT_OPTIONS_TABLE, 'name');
	$sql = "
    SELECT 
        (
            SELECT COUNT(`?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE`.`variantID`) 
            FROM `?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE` 
            WHERE `?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE`.`optionID` = `?#PRODUCT_OPTIONS_TABLE`.`optionID`
        ) as `count_variants`,
        `?#PRODUCT_OPTIONS_TABLE`.*,
        {$name},{$description_title},{$description_text},{$slider_prefix}
        
    FROM `?#PRODUCT_OPTIONS_TABLE`
    ORDER BY `?#PRODUCT_OPTIONS_TABLE`.`sort_order`, {$sort_name}";

	$q = db_phquery($sql);	
	$result=array();
	while( $row=db_fetch_assoc($q) ){
		LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_TABLE, $row);
		$row['optionID'] = intval($row['optionID']);
		$result[] = $row;
	}
	return $result;
}

function optGetOptionValues($optionID = null, $modifi = false){
	$value = LanguagesManager::sql_constractSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'option_value');
	$description_title = LanguagesManager::sql_constractSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'description_title');
    $description_text = LanguagesManager::sql_constractSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'description_text');   
	$value_sort = LanguagesManager::sql_getSortField(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, 'option_value');
	$dbq = "SELECT *, {$value}, {$description_title}, {$description_text} FROM `?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE`";
	if(!is_null($optionID)){ $dbq .= 'WHERE `optionID` IN (?@)';	}
	$dbq .= "ORDER BY `optionID`, `sort_order`, {$value_sort}";
	$q = db_phquery($dbq, (array)$optionID);
	$result=array();
	while($row=db_fetch_assoc($q)){
		LanguagesManager::ml_fillFields(PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE, $row);
		$row['count_excluded'] = ($row['excluded'])?count(unserialize($row['excluded'])):'0';
		$current = $row['optionID'];
		if(!isset($result[$current])){
			$result[$current] = array();
		}
		$result[$current][] = $row;	
	}
	
	$return = (is_array($optionID)||is_null($optionID))?$result:$result[$optionID];
	if($modifi) $return = ProductFilter_PrepareSliderVariantsArray($return);
	return $return;
}


//Number format and sort slider variants
function ProductFilter_PrepareSliderVariantsArray($options){
	if(empty($options)) return false;
	foreach($options as $optionID=>&$variants){
		$isSlider = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT count(*) FROM ?#PRODUCT_OPTIONS_TABLE WHERE `optionID`=? AND `optionType`="slider"',$optionID);
		if(!$isSlider||empty($variants)) continue;
		foreach($variants as &$variant)	$variant['option_value'] = floatval(preg_replace('/[^0-9|,|\.]/', '', str_replace(',','.',$variant['option_value'])));
		$sorter=array();
		$ret=array();
		reset($variants);
		foreach ($variants as $ii => $va) $sorter[$ii]=$va["option_value"];
		asort($sorter);
		foreach ($sorter as $ii => $va)	$ret[$ii]=$variants[$ii];
		$variants=$ret;
	}
	return $options;
}

function optGetOptionById($optionID){	
	$q = db_phquery('SELECT *, '.LanguagesManager::sql_prepareField('category_name','category_name').', '.LanguagesManager::sql_prepareField('name','optionName').' FROM ?#PRODUCT_OPTIONS_TABLE o LEFT JOIN ?#PRODUCT_OPTIONS_CATEGORYES_TABLE c ON o.`optionCategory`=c.`categoryID` WHERE o.`optionID`=?',$optionID);
	if ( $row=db_fetch_row($q) ){
		LanguagesManager::ml_fillFields(PRODUCT_OPTIONS_TABLE, $row);
		return $row;
	}else return null;
}


//Old functions
function optUpdateOptions($updateOptions){
	if(!empty($updateOptions)){
	foreach($updateOptions as $key => $val){
		$update_sql = LanguagesManager::sql_prepareTableUpdate(PRODUCT_OPTIONS_TABLE, $val, array('@name_(\w{2})@' => 'extra_option_${1}'));
		$val["extra_option"] = xEscapeSQLstring($val["extra_option"] );
		$val["extra_sort"] = (int)$val["extra_sort"];
		$s = "update ".PRODUCT_OPTIONS_TABLE." set {$update_sql}, sort_order='".$val["extra_sort"]."' where optionID='$key';";
		db_query($s);
	}}
}

function optAddOption($params){
	
	$ml_dbqs = LanguagesManager::sql_prepareFieldInsert('name', $params);
	db_phquery('INSERT ?#PRODUCT_OPTIONS_TABLE ('.$ml_dbqs['fields'].', sort_order) VALUES('.$ml_dbqs['values'].',?)', $params['sort_order']);
}

function optOptionValueExists($optionID, $value_name){
	
	$langfield_names = LanguagesManager::ml_getLangFieldNames('option_value');
	$q = db_phquery('SELECT variantID FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE optionID=? AND ('.implode('="'.xEscapeSQLstring($value_name).'" OR ', $langfield_names).'="'.xEscapeSQLstring($value_name).'")', $optionID);
	$row = db_fetch_row($q);
	if ($row)
		return $row[0]; //return variant ID
	else
		return false;
}

function optUpdateOptionValues($updateOptions){
	if(!empty($updateOptions)){
	foreach($updateOptions as $key => $value){		
		$sql = '
			UPDATE ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE SET '.LanguagesManager::sql_prepareFieldUpdate('option_value', $value).', sort_order=? 
			WHERE variantID=?
		';
		db_phquery($sql, $value["sort_order"], $key);
	}}
}

function optAddOptionValue($params){
	
	if(LanguagesManager::ml_isEmpty('option_value', $params))return false;
	$ml_dbqs = LanguagesManager::sql_prepareFieldInsert('option_value', $params);
	$sql = '
		INSERT ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE (optionID, '.$ml_dbqs['fields'].', sort_order) values(?, '.$ml_dbqs['values'].',?) 
	';
	db_phquery($sql,$params['optionID'], $params['sort_order']);
	return db_insert_id();
}



?>