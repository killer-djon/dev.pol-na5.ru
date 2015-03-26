<?php
/*Модуль дополнительные характеристики (расширенная) (версия 1)
Разработано: © JOrange.ru*/

class prfilter extends ComponentModule  {
	
	function __construct(){
		$this->optionID = (int)$_GET['optionID'];
		$this->variantID = (int)$_GET['variantID'];
		$this->templateID = (int)$_GET['templateID'];
		$this->categoryID = (int)$_GET['categoryID'];
		$this->category_slug = $_GET['category_slug'];
		parent::__construct();
	}
	
	function initInterfaces(){
		$this->__registerInterface('prfilter_admin', 'prfilter_admin', INTCALLER, 'ShowPageAdmin');
		$this->__registerInterface('prfilter_import_excel', 'prfilter_import_excel', INTCALLER, 'ShowPageImportExecl');
		$this->__registerInterface('prfilter_export_excel', 'prfilter_export_excel', INTCALLER, 'ShowPageExcportExecl');
		$this->__registerInterface('prfilter_templates', 'prfilter_templates', INTCALLER, 'ShowPageTemplates');
		$this->__registerComponent('prfilter', 'prfilter', array('general_layout', 'home_page', 'product_info'), 'ShowPagePSearch');
		$this->__registerComponent('prfilter_block', 'prfilter_block', array('general_layout', 'home_page', 'product_info'), 'ShowBlock', 
			array(			
				'position' => array(
					'type' => 'select',
					'params' => array(
						'name' => 'position', 
						'title' => translate('prfilter_block_settings_position'), 
						'options' => array(
							'left' =>	translate('prfilter_block_settings_position_left'),
							'right' => 	translate('prfilter_block_settings_position_right'),
							'center' =>	translate('prfilter_block_settings_position_center') 
							)
					)
				),
				'colomns' => array(
					'type' => 'select',
					'params' => array(
						'name' => 'colomns', 
						'title' => translate('prfilter_block_settings_colomns'), 
						'options' => array(
							'1' =>	'1',
							'2' => 	'2',
							'3' =>	'3', 
							'4' =>	'4', 
							'5' =>	'5', 
							'6' =>	'6', 
							'7' =>	'7', 
							'8' =>	'8', 
							'9' =>	'9', 
							'10' =>	'10' 
							)
					)
				)
			)
		);
		$this->__registerComponent('prfilter_index_block', 'prfilter_index_block', array('general_layout', 'home_page', 'product_info'), 'ShowIndexBlock', 
			array(			
				'position' => array(
					'type' => 'select',
					'params' => array(
						'name' => 'position', 
						'title' => translate('prfilter_block_settings_position'), 
						'options' => array(
							'left' =>	translate('prfilter_block_settings_position_left'),
							'right' => 	translate('prfilter_block_settings_position_right'),
							'center' =>	translate('prfilter_block_settings_position_center') 
							)
					)
				),
				'colomns' => array(
					'type' => 'select',
					'params' => array(
						'name' => 'colomns', 
						'title' => translate('prfilter_block_settings_colomns'), 
						'options' => array(
							'1' =>	'1',
							'2' => 	'2',
							'3' =>	'3', 
							'4' =>	'4', 
							'5' =>	'5', 
							'6' =>	'6', 
							'7' =>	'7', 
							'8' =>	'8', 
							'9' =>	'9', 
							'10' =>	'10' 
							)
					)
				),
				'category' => array(
					'type' => 'ProductFilter_BlockGetCategoryes', 
					'params' => array(
						'name' => 'category', 
						'title'=> translate('prfilter_block_settings_category'),
						'options'=> array()
					)
				),
				'template' => array(
					'type' => 'ProductFilter_BlockGetTemplates', 
					'params' => array(
						'name' => 'template', 
						'title'=> translate('prfilter_block_settings_template'),
						'options'=> array()
					)
				),
				'showselected' => array(
					'type' => 'select',
					'params' => array(
						'name' => 'showselected', 
						'title' => translate('prfilter_block_settings_showselected'), 
						'options' => array(
							'false' =>	translate('prfilter_no'),
							'true' => 	translate('prfilter_yes'),
							)
					)
				),
			)
		);
	}	

	function SetCategoryID($categoryID){
		return $this->categoryID = (int)$categoryID;
	}

	function SetCategoryslug($category_slug){
		return $this->category_slug = $category_slug;
	}
		
	function PrepareBlock($GET, $settings = null, &$template = false){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		$descriptions = array();
		$descriptions_params = array();
		$excluded = array();
		$options = array();
		if(isset($GET['psearch'])){
			ProductFilter_MinimizeGetUrl();
			$excluded = ProductFilter_GetExcludedParams($GET);	
		}		
		$template = ProductFilter_GetTemplateByCategory($this->categoryID, $GET, $Options, $settings['local_settings']['template']);	
		if(!empty($Options)){
		foreach($Options as $keyc=>$category){
			if(empty($category['options'])) continue;	
			foreach($category['options'] as $keyo=>$option){
				if(!empty($option['variants']) && is_array($option['variants'])){		
					foreach($option['variants'] as $keyv=>$variant){
						if(!$variant['description_text']) continue;
						$descriptions_params[] = $variant['variantID'];			
					}	
				}
				if(!$option['description_text']) continue;
				$descriptions[] = $option['optionID'];
			}
		}}	
		$template['excluded'] = ($excluded)?base64_encode(json_encode($excluded)):'';
		$template['descriptions'] = ($descriptions)?base64_encode(json_encode($descriptions)):'';
		$template['descriptions_params'] = ($descriptions_params)?base64_encode(json_encode($descriptions_params)):'';
		$template['categoryID'] = $this->categoryID;
		$template['category_slug'] = $this->category_slug;
	
		$smarty->assign("settings", $settings['local_settings'] );	
		$smarty->assign("prfilter", $template );
		return $template['templateID'];
	}
	
	function ShowPageAdmin(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);

		if(isset($this->optionID) AND isset($this->variantID) AND isset($_GET["excluded"]) ){
			$options = array();
			$excluded = array();
			$variant = array();
            $variant = ProductFilter_GetVariantById($this->variantID);
            $excluded = unserialize($variant['excluded']);
            $options = optGetOptions();
			if(!empty($options)){
            foreach($options as $key => $val){
                if($options[$key]['optionID']==$this->optionID)continue;
                $options[$key]['values'] = optGetOptionValues($options[$key]['optionID'] );
				$exist_params = 0;
				foreach($options[$key]['values'] as $key2 => $val2){
					if(!$excluded){
						$options[$key]['values'][$key2]['excluded'] = '0';
					}else{
						$options[$key]['values'][$key2]['excluded'] = (in_array($options[$key]['values'][$key2]['variantID'],$excluded))?'1':'0';
						if(in_array($options[$key]['values'][$key2]['variantID'],$excluded)) $exist_params++;
					}
                }
				$options[$key]['exist_params'] = $exist_params;
            }
			}
            $smarty->assign("options", $options );
            $option = optGetOptionById($this->optionID );
            $smarty->assign("option_name",$option["name"]);
            $smarty->assign("excluded", 'yep' );
            $smarty->assign("optionID", $this->optionID );
            $smarty->assign("variantID", $this->variantID );
            $smarty->assign("value_count", count($values) );
            $smarty->assign("variant", $variant );
			
        }elseif (!$this->optionID){ 
			$categoryes = ProductFilter_GetOptionsCategoryes();
			$options = optGetOptions();   
			$smarty->assign("options", $options);
			$smarty->assign("categoryes", $categoryes);
		}else{
			$option = optGetOptionById( $this->optionID );		
			$values = optGetOptionValues( $this->optionID );
			$smarty->assign("values", $values);
			$smarty->assign("optionID", $this->optionID );
			$smarty->assign("option_name", $option["name"]);
			$smarty->assign("option_type", $option["optionType"]);
			$smarty->assign("value_count", count($values) );
		}			
		$smarty->assign( "admin_sub_dpt", 'prfilter/prfilter.html' );
	}

	function ShowPageTemplates(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
	
		$templateAdd = $_GET['add_template'];
		if ($templateAdd || $this->templateID){ 
			if($this->templateID){
				$template = ProductFilter_GetOptionTemplateById( $this->templateID );	
				$smarty->assign("template", $template );
			}
			
			$categoryes = ProductFilter_GetOptionsCategoryes();
			$options = ProductFilter_GetOptionToTemplate();
			$countCategoryes = db_phquery_fetch(DBRFETCH_FIRST, 'SELECT COUNT(*) FROM ?#CATEGORIES_TABLE WHERE `categoryID` <> 1');
			$smarty->assign("countCategoryes", $countCategoryes);
			$smarty->assign("categoryes", $categoryes);
			$smarty->assign("options", $options);
					
			//local for main category
			$languages = &LanguagesManager::getLanguages();
			$nonNamedCategory = array();
			$newNameCategory = array();
			if(!empty($languages)){
				foreach($languages as $language){
					$LanguageEntry = &LanguagesManager::getLanguageByISO2($language->iso2);
					$local = $LanguageEntry->getLocal('prfilter_noncategory');
					$local2 = $LanguageEntry->getLocal('prfilter_noncategory_name');
					$nonNamedCategory['category_name_'.$language->iso2] = $local['value'];	
					$newNameCategory['category_name_'.$language->iso2] = $local2['value'];	
				}
			}
			$smarty->assign("nonNamedCategory", $nonNamedCategory);
			$smarty->assign("newNameCategory", $newNameCategory);
		}else{ 
			$templates = ProductFilter_GetOptionTemplates();
			$smarty->assign("templates", $templates );
		}
		
		$smarty->assign("templateAdd", $templateAdd );
		$smarty->assign( "admin_sub_dpt", 'prfilter/prfilter_templates.html' );
	}

	function ShowBlock($settings = null){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		$this->PrepareBlock($_GET, $settings);
		$smarty->display("prfilter/lnrside.html" );
	}

	function ShowIndexBlock($settings = null){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		
		$settings['local_settings']['isindex'] = true;
		if(!$settings['local_settings']['category']){
			//Select category
			$this->SetCategoryID(0);
			$this->SetCategoryslug('');
			$settings['local_settings']['isselectcategory'] = true;
			$this->PrepareBlock(($settings['local_settings']['showselected']=='true')?$_GET:false, $settings);
			$categoryesAll = ProductFilter_GetCategoryList(1,0);
			$smarty->assign( 'categoryes', $categoryesAll);
		}else{
			//categoryID exist
			$this->SetCategoryID($settings['local_settings']['category']);
			$category = catGetCategoryById($this->categoryID);
			$this->SetCategoryslug($category['slug']);
			$this->PrepareBlock(($settings['local_settings']['showselected']=='true')?$_GET:false, $settings);
		}
		$smarty->display("prfilter/lnrside.html" );
	}


	function ShowPagePSearch(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		$categoryesAll = ProductFilter_GetCategoryList(1,0);
		$smarty->assign( 'categoryes', $categoryesAll);
		$smarty->assign( 'main_content_template', 'prfilter/psearch.html');
	}

	function ShowPageImportExecl(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		
		global $file_encoding_charsets;
		$smarty->assign('charsets', $file_encoding_charsets);
		$smarty->assign('default_charset', translate('prdine_default_charset'));
		$smarty->assign( "admin_sub_dpt", 'prfilter/prfilter_excel_import.html' );
	}
	
	function ShowPageExcportExecl(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		
		global $file_encoding_charsets;
		$smarty->assign('charsets', $file_encoding_charsets);
		$smarty->assign('default_charset', translate('prdine_default_charset'));
		$smarty->assign( "admin_sub_dpt", 'prfilter/prfilter_excel_export.html' );
	}
	
}
 
class PrFilterController extends ActionsController{
	
	function __construct(){
		parent::__construct();
	}
	
	//Delete all
	function delete_all(){
		safeMode(true);
		ProductFilter_DeleteAll();
		RedirectSQ('');
	}
	
	//Export to excel
	function excel_export(){
		safeMode(true);
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		ProductFilter_ExportToExcel($this->getData());
		$smarty->assign('MessageBlock',"<div class='success_block' ><span class='success_message'>".translate('prfilter_export_saved')."<br><br>".
		'<a href="get_file.php?getFileParam='.Crypt::FileParamCrypt( "GetCSVCatalog=".base64_encode('product_options.csv'), null ).'">'.translate('btn_download').'</a>'.sprintf(' (%3.2f Kb)',filesize(DIR_TEMP.'/product_options.csv')/1024).'</span></div>');
	}
	
	//Import from excel (step 1)
	function excel_import_step1(){
		safeMode(true);
		ProductFilter_ImportFromExcel1($this->getData());
	}
	
	//Import from excel (step 2)
	function excel_import_step2(){
		safeMode(true);
		ProductFilter_ImportFromExcel2($this->getData());
	}
	
	//Save options categoryes
	function save_options_categoryes(){
		safeMode(true);
		$updateOptionsCategory = scanArrayKeysForID($_POST, array( "category_name_\w{2}") );
		ProductFilter_UpdateOptionsCategoryes($updateOptionsCategory);
		if ( !LanguagesManager::ml_isEmpty('category_name', $this->getData()) ){
			$categoryID = ProductFilter_AddOptionCategory($this->getData());
			$categoryes = ProductFilter_GetOptionsCategoryes();
			echo json_encode(array('categoryID'=>$categoryID, 'categoryes'=>$categoryes));
		}else{
			$categoryes = ProductFilter_GetOptionsCategoryes();
			echo json_encode(array('categoryes'=>$categoryes));
		}
		exit();
	}
	
	//Save options categoryes sort
	function save_options_categoryes_sort(){
		$updateOptionsCategory = scanArrayKeysForID($_POST, array( "category_sort") );
		ProductFilter_UpdateOptionsCategoryesSort($updateOptionsCategory);
		Message::raiseAjaxMessage(MSG_SUCCESS, '', 'order_saved');
		echo json_encode($GLOBALS['_RESULT']['_AJAXMESSAGE']);
		exit();
	}
	
	//Delete options category
	function delete_option_categoryes(){
		safeMode(true, 'categoryID=');
		ProductFilter_DeleteOptionsCategory($this->getData('categoryID'));
		RedirectSQ('optionID=&categoryID=');
	}
		
	//Save options
	function save_options(){
		safeMode(true);
		$updateOptions = scanArrayKeysForID($_POST, array( "extra_option_\w{2}", "extra_sort", "extra_slider_step" , "extra_colomns", "extra_optionType", "extra_optionCategory", "extra_description_title_\w{2}", "extra_description_text_\w{2}", "extra_slider_prefix_\w{2}" ) );
		ProductFilter_UpdateOptions($updateOptions);
		if ( !LanguagesManager::ml_isEmpty('name', $this->getData()) ){
			ProductFilter_AddOption($this->getData());
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}
	
	//Delete options
	function delete_option(){
		safeMode(true, 'optionID=');
		ProductFilter_DeleteOption($this->getData('optionID'));
		RedirectSQ('optionID=');
	}
	
	//Save option params
	function save_values(){
		safeMode(true);
		$updateOptions = scanArrayKeysForID($_POST, array( "sort_order", 'option_value_\w{2}', 'recomended', 'description_title_\w{2}', 'description_text_\w{2}' ) );
		$updateOptionsPictures = scanArrayKeysForID($_FILES, "image" );
		ProductFilter_UpdateOptionValues($updateOptions, $updateOptionsPictures);
		if ( !LanguagesManager::ml_isEmpty('option_value', $_POST)){	
			ProductFilter_AddOptionValue($_POST,$_FILES);
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}
	
	//Delete option param
	function delete_variant(){
		safeMode(true, 'variantID=');
		ProductFilter_DeleteOptionValue($this->getData('variantID'));
		RedirectSQ('variantID=');
	}
	
	//Delete param picture 
	function delete_picture(){
		safeMode(true, 'variantID=');
		ProductFilter_DeleteOptionValuePicture($this->getData('variantID'));	
		RedirectSQ('variantID=');
	}
					
	//Save excluded params
	function save_valuesblock(){
        safeMode(true);
        $excludedArray = array_keys(scanArrayKeysForID($_POST, 'param'));
        $excludedArray = array_map('intval',$excludedArray);
        $excludedArray = array_unique($excludedArray);
        $block = serialize($excludedArray);
        ProductFilter_UpdateOptionValuesBlock($_POST['variantID'],$block);
        Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
    }
	
	//Save template sort
	function save_template_priority(){
		ProductFilter_UpdatePriorityOptionTemplate($_POST);
		Message::raiseAjaxMessage(MSG_SUCCESS, '', 'order_saved');
		exit();
    }
	
	//Save template
	function save_template(){
		safeMode(true);
		if($_POST['templateID']){
			ProductFilter_UpdateOptionTemplate($_POST);
		}else{
			ProductFilter_AddOptionTemplate($_POST);
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=prfilter_templates', 'msg_information_save');
    }
	
	//Delete template
	function delete_template(){
		safeMode(true, 'templateID=');
		ProductFilter_DeleteOptionTemplate($this->getData('templateID'));
		RedirectSQ('templateID=');
	}
		
	//Load categoryes
	function load_categoryes(){
       	$categoryesAll = catGetCategoryCList();
		if(empty($categoryesAll))return false;
		foreach( $categoryesAll as $key=>$value){	
			$path = catCalculatePathToCategory($value['categoryID']);
			$categoryesAll[$key]['path']="";
			if(!empty($path)){
				foreach($path as $p => $v){
					if($v['categoryID']=='1')continue;
					$categoryesAll[$key]['path'].= (($categoryesAll[$key]['path'])?" > ":" ").$v['name'];
				}
			}else{
				$categoryesAll[$key]['path'] = $value['name'];
			}
		}
		echo json_encode(array('returnArray'=>$categoryesAll));
		exit();
    }
	
	//Get products count (AJAX)
	function get_products_ajax(){
       $return = ProductFilter_GetFoundedProductsAjax($this->getData());
	   echo json_encode($return);
	   exit();
    }
	
	//Get templete (AJAX)
	function get_template_ajax(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$GetVars = &$Register->get(VAR_GET);
		$settings = array('local_settings'=>array('position'=>($this->getData('position'))?$this->getData('position'):'center','colomns'=>($this->getData('colomns'))?$this->getData('colomns'):CONF_PRFILTER_COLOMNS_ON_PSEARCH));	
		$prfilter = new prfilter;
		$prfilter->SetCategoryID($this->getData('categoryID'));
		$prfilter->SetCategoryslug($this->getData('category_slug'));
		$templateID = $prfilter->PrepareBlock($this->getData(), $settings, $template);
		if($this->getData('isindex'))$smarty->assign("ajaxindex", true );
		$maxPrice = ProductFilter_GetMaxPriceOfCategory($this->getData('categoryID'));
		$smarty->assign("ajax", true );
		$return = $smarty->fetch("prfilter/lnrside.html" );
		
		$template = ($this->getData('isindex'))?$template:'';
		$descriptions = ($template['descriptions'])?base64_decode($template['descriptions']):'';
		$descriptions_params = ($template['descriptions_params'])?base64_decode($template['descriptions_params']):'';
		
		echo json_encode(array('html'=>$return,'templateID'=>$templateID,'maxPrice'=>$maxPrice,'descriptions'=>$descriptions,'descriptions_params'=>$descriptions_params));
		exit();
    }
	
	//Get category max price (AJAX)
	function get_categorymaxprice_ajax(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$maxPrice = ProductFilter_GetMaxPriceOfCategory($this->getData('categoryID'));
		echo json_encode(array('maxPrice'=>$maxPrice));
		exit();
    }
	
	//Get option description (AJAX)
	function get_option_description_ajax(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$option = optGetOptionById($this->getData('optionID'));
		echo json_encode(array('title'=>$option['description_title'],'text'=>$option['description_text']));
		exit();
    }
	
	//Get variant description (AJAX)
	function get_variant_description_ajax(){
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		$option = ProductFilter_GetVariantById($this->getData('variantID'));
		echo json_encode(array('title'=>$option['description_title'],'text'=>$option['description_text']));
		exit();
    }
		
}

if(isset($_POST) && $_POST['action']!='CPT_PREPARE_SMARTYCODE' && $_POST['action']!='save_settings' && $_POST['action']!='CPT_PREPARE_HTMLCODE' && $_GET['ukey']!='cpt_constructor'){
	ActionsController::exec('PrFilterController');
}	
		
?>