<?php

class Chooser extends ComponentModule  {
	
	public function __construct(){
		parent::__construct();
	}
	
	function initInterfaces()
	{

		$this->Interfaces = array();
		$this->Interfaces['chooser'] = array(
			'name'	 => 'Chooser module',
			'method' => 'methodChooser',
		);
	}
	
	function methodChooser(){
		
		global $smarty;
		$Register = &Register::getInstance();
		$GetVars = &$Register->get(VAR_GET);
		$PostVars = &$Register->get(VAR_POST);
		
		$root_categories = catGetCategoryCompactCList(1);		
		$options_values = array();
		
		$this->__getOptions($GetVars['categoryID'], $options);
		foreach($options as $key => $item){
			if( ! $item['isSet'] ) continue;
			$options_values[] = $item;
		}
		
		//print_r($options_values);
		
		if( is_array($PostVars) && count($PostVars) ){
			if( isset($PostVars['action']) && $PostVars['action'] == 'filterProduct' ){
				$param = array();
				$i=0;
				foreach( $PostVars['params'] as $key=> $value ){
					$optionID = ( (intval(substr( $value['name'], strrpos($value['name'], "_")+1, strlen($value['name']) ))) ? substr( $value['name'], strrpos($value['name'], "_")+1, strlen($value['name']) ) : $value['name'] );
					
					$param[$i] = array(
						"option" => $optionID,
						"variant" => $value['value']
					);
					$i++;
					
				}
				
				$optionsIDs = array();
				$variantsIDs = array();
				
				$price = array();
				foreach( $param as $key => $item ){
					if( intval($item['option']) && intval($item['variant']) ) {
						$optionsIDs[] = $item['option'];
						$variantsIDs[] = $item['variant'];
					}else{
						$price[$item['option']] = ( (empty($item['variant']) || ! isset($item['variant'])) ? 0 : intval($item['variant']) );
					}
					
				}
				
				//$price_where = ( ( ($price['priceFrom'] < $price['priceTo']) || ( $price['priceFrom'] <= 0 && $price['priceTo'] <= 0 ) ) ? '' : ' and (pr.Price BETWEEN '.$price['priceFrom'].' and '.$price['priceTo'].') ' );
				
				$price_where = '';
				if( ($price['priceFrom'] == 0) && ($price['priceTo'] == 0) ){
					$price_where = '';	
				}else if( (intval($price['priceFrom']) <= intval($price['priceTo'])) && (intval($price['priceFrom']) > 0 && intval($price['priceFrom']) > 0) ){
					$price_where = ' and (pr.Price BETWEEN '.$price['priceFrom'].' and '.$price['priceTo'].') ';	
				}
				$sql = '
					SELECT pos.*, povt.name_ru, pr.*
					FROM ?#PRODUCTS_OPTIONS_SET_TABLE as pos
					JOIN ?#PRODUCT_OPTIONS_TABLE as povt ON (povt.optionID = pos.optionID)
					LEFT JOIN ?#PRODUCTS_TABLE as pr ON (pr.productID = pos.productID)
					WHERE (pos.optionID IN (?@)) and (pos.variantID IN (?@))'.$price_where.'
				';
				
				$Result = db_phquery($sql, $optionsIDs, $variantsIDs);
				$res = array();
				while( $_Row = db_fetch_assoc($Result) ){
					if( isset( $res[$_Row['productID']] ) ) continue;
					$res[$_Row['productID']] = $_Row;
				}
				
				if( is_array($res) && count($res) ){
					$form = str_replace("\"", "'", $this->parseForm( array_reverse($res) ));
					$form_result = preg_replace('/[\\t\\r\\n]/', '', $form);
					
					//print_r($res);
					echo '{"success" : true, "form" : "'.$form_result.'"}';	
				}
			}
			exit();
		}
		/*
		echo '<pre>';
		print_r( $options_values );
		echo '</pre>';
		*/
		
		$smarty->assign("options", $options_values);
		$smarty->assign("root_categories", $root_categories);
		$smarty->assign('main_content_template', 'chooser.tpl.html');
	}
	
	
	private function __getOptions($categoryID, &$options)
	{
		$categoryID = isset($categoryID)?intval($categoryID):false;
		$options = optGetOptions();
		$option_values = optGetOptionValues();

		foreach($options as &$option)
		{
			$optionID = $option['optionID'];
			if ( $categoryID ){
				$res = schOptionIsSetToSearch($categoryID, $optionID );
			}else{
				$res = array( 'isSet' => false, 'set_arbitrarily' => 1 );
			}
			if ( $res['isSet'] ){
				$option['isSet'] = true;
				$option['set_arbitrarily'] = $res['set_arbitrarily'];
			}else{
				$option['isSet'] = false;
				$option['set_arbitrarily'] = 1;
			}

			$option['variants'] = array();
			if(isset($option_values[$optionID])){
				$option['variants'] = $option_values[$optionID];
				foreach($option['variants'] as &$variant){
					$variant['isSet'] = $categoryID&&schVariantIsSetToSearch($categoryID, $optionID, $variant['variantID'] );
				}
			}
			unset($variant);

		}
		unset($option);
		return $options;
	}
	
	private function __getPicture($productID, $defaultID){
		
		$productID = intval($productID);
		
		$pictures = GetPictures($productID);
		
		$res = array();
		foreach( $pictures as $key => $value ){
			if( isset( $value['photoID'] ) && $value['photoID'] == $defaultID ){
				$res[$productID][$defaultID] = array(
					'filename' => $value['filename'],
					'enlarged' => $value['enlarged']
				);
			}
		}
		return $res;
		
	}
	
	private function parseForm($result){
		
		$form .= '<h3>Найденные товары по вашему запросу</h3>';
		$form .= '<div class="catalog">';
		foreach( $result as $key => $value ){
			$pictures = $this->__getPicture($value['productID'], $value['default_picture']);
			//{$smarty.const.URL_PRODUCTS_PICTURES}/			
			$form .= '<div class="block-catalog-brief">
						<form class="product_brief_block" rel="'.$value['productID'].'" method="post" action="/cart/">
						<input type="hidden" value="add_product" name="action">
						<input type="hidden" value="'.$value['productID'].'" name="productID">
						<input class="product_price" type="hidden" value="'.$value['Price'].'">
						
						<div class="prdbrief_thumbnail">
							<a class="lytebox" href="'.URL_PRODUCTS_PICTURES.'/'.$pictures[$value['productID']][$value['default_picture']]['filename'].'">
								<img src="'.URL_PRODUCTS_PICTURES.'/'.$pictures[$value['productID']][$value['default_picture']]['filename'].'" />
										</a>
						</div>
						
						<div class="prdbrief_name">
							<a href="/product/'.$value['slug'].'/">'.$value['name_ru'].'</a>
						</div>
						<div class="prdbrief_price">							
							Цена: &nbsp;
							<span class="totalPrice">'.show_price( $value['Price'] ).'</span>&nbsp;
							<span class="unit">Руб/м<sup>2</sup></span>
						</div>
						<div class="prdbrief_comparison">
							<input id="ctrl-prd-cmp-'.$value['productID'].'" class="checknomarging ctrl_products_cmp" type="checkbox" value="'.$value['productID'].'">
							<label for="ctrl-prd-cmp-'.$value['productID'].'">Добавить в сравнение</label>
						</div>
						
						<div class="prdbrief_add2cart">
							<div class="carttext">
								<input type="image" title="добавить в корзину" alt="добавить в корзину" src="/published/publicdata/LAMINAT/attachments/SC/images/addtocart.png">
								<span>В корзину</span>
							</div>
						</div>
						</form>
					 </div>
					  
			';
			
		}
		$form .= '</div>';
		
		return $form;
	}
}



