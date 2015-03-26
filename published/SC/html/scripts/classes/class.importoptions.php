<?php
/*Плагин Ajax фильтрация продуктов (версия 2)
Разработано: © JOrange.ru*/

class ImportOptions extends ImportData{
	
	private $statistic = array(
		'category_added'=>0,
		'category_modify'=>0,
		'option_added'=>0,
		'option_modify'=>0,
		'variant_added'=>0,
		'variant_modify'=>0,
		'insert'=>false
	);
	protected $name_fields = null;
	protected $primary_category_col = null;
	private $currentCategoryID = 0;
	private $currentOptionID;
	
	function __construct($csv_file_name,$delimeter = ';'){
		$this->name_fields = LanguagesManager::ml_getLangFieldNames('name');
		$this->currentCategoryID = 0;
		parent::__construct($csv_file_name,$delimeter);
	}

	function readCsvLine(){
		static $columnCount = 0;
		static $counter = 0;
		
		
		if(!$this->csv_pointer||!$this->csv_size){
			return false;
		}
		do{//skip empty lines
			$line = fgetcsv($this->csv_pointer, $this->csv_size, $this->delimeter);
			
			$count_empty_cells = 0;
			$is_empty_line = true;
			if($line){
				foreach($line as $cell){
					if($cell!==''){
						$is_empty_line =false;
						break;
					}
				}
			}
		}while($line&&$is_empty_line);
		if($line === false){
			return false;
		}
		foreach($line as &$cell){
			$cell = utf8_bad_replace($cell);
		}
		$currentColumnCount = count($line);
		if($columnCount>$currentColumnCount){
			$line = array_merge($line,array_fill(0,$columnCount-$currentColumnCount,''));
		}else{
			$columnCount = count($line);
		}
		if(!$this->source_cols&&$line){
			$this->setSourceColumns($line);
		}
		$this->source_column_count = $columnCount;

		return $line;
	}	
	
	function ApplyDataMapping($line){
		$data = parent::applyDataMapping($line);
		return $data;
	}

	function parseDataMapping(){
		parent::parseDataMapping();
		$this->primary_category_col = $this->primary_col;
	}
	
	function import($data,$allow_insert = true){
		$this->statistic['insert'] = false;

		if($this->isOption($data['main'])){
			$this->importOptionData($data['main'],$allow_insert);
		}elseif($this->isCategory($data['main'])){
			$this->importCategoryData($data['main']);
		}elseif($this->isParam($data['main'])){
			$this->importParamData($data['main'],$allow_insert);
		}		
		return $this->statistic;
	}

	protected function setParentCategory($currentCategoryID){
		$this->currentCategoryID = $currentCategoryID;
	}
	
	protected function setParentOption($currentOptionID){
		$this->currentOptionID = $currentOptionID;
	}
	
	protected function importCategoryData($category_data){
		
		$this->fixCategoryName($category_data);
		foreach($category_data as $key=>$category){
			$category_data[preg_replace('/^id$/', 'categoryID', $key)]=$category;
			$category_data[preg_replace('/^sort_order$/', 'sort', $key)]=$category;
			$category_data[preg_replace('/^name/', 'category_name', $key)]=trim($category);
		}
		$primary_category_col = preg_replace('/^name/', 'category_name', $this->primary_category_col);
		$primary_category_col = preg_replace('/^id$/','categoryID', $primary_category_col); 	
		$sql = 'SELECT categoryID FROM ?#PRODUCT_OPTIONS_CATEGORYES_TABLE WHERE `'.$primary_category_col.'` = ?';
		$currentCategoryID = db_phquery_fetch(DBRFETCH_FIRST, $sql, $category_data[$primary_category_col]);
		
		if ($currentCategoryID&&$category_data['categoryID']!='0'){
			ProductFilter_UpdateOptionsCategory($category_data);
			$currentCategoryID = $currentCategoryID;
			$this->statistic['category_modify']++;	
		}elseif($category_data['categoryID']!='0'){
			$currentCategoryID = ProductFilter_AddOptionCategory($category_data);
			$this->statistic['category_added']++;
		}
		$this->setParentCategory($currentCategoryID);
	}
	
	protected function importOptionData($option_data,$allow_insert = true){
		
		$currentCategoryID = $this->currentCategoryID;
		$this->fixOptionName($option_data);
		
		$option_data['optionCategory']=$currentCategoryID;
		$primary_col = preg_replace('/^id$/','optionID',$this->primary_col);

		$sql = 'SELECT optionID FROM ?#PRODUCT_OPTIONS_TABLE WHERE optionCategory=? AND '.xEscapeSQLstring($primary_col).' = ?';
		$optionID = db_phquery_fetch(DBRFETCH_FIRST, $sql, $currentCategoryID,(string)$option_data[$primary_col]);
		$option_data['optionID'] = $optionID;
		switch(trim($option_data['optionType'])){
			case '2':
			case 'slider':
				$option_data['optionType'] = 'slider';
				break;
			case '3':
			case 'single':	
				$option_data['optionType'] = 'single';
			break;
			default:
				$option_data['optionType'] = 'simple';
			break;
		}
		$option_data['slider_step'] = floatval(preg_replace('/[^0-9|,|\.]/', '', str_replace(',','.',$option_data['slider_step'])));
		
		if($optionID){ 	
			ProductFilter_UpdateOption($option_data);		
			$currentOptionID = $optionID;
			$this->statistic['option_modify']++;
		}elseif($allow_insert){
			$currentOptionID = ProductFilter_AddOption($option_data);
			$this->statistic['option_added']++;
		}
		$this->setParentOption($currentOptionID);
	}

	protected function importParamData($variant_data,$allow_insert = true){
		
		$currentOptionID = $this->currentOptionID;
		foreach($variant_data as $key=>$variant){
			$variant_data[preg_replace('/^id$/', 'variantID', $key)]=$variant;
			$variant_data[preg_replace('/^name/', 'option_value', $key)]=trim($variant);
		}
		$variant_data['optionID']=$currentOptionID;
		$primary_col = preg_replace('/^id$/','variantID', $this->primary_col); 	
		$primary_col = preg_replace('/^name/', 'option_value', $primary_col);
			
		$sql = 'SELECT `variantID` FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE `optionID`=? AND '.xEscapeSQLstring($primary_col).' = ?';
		$variantID = db_phquery_fetch(DBRFETCH_FIRST, $sql, $currentOptionID,(string)$variant_data[$primary_col]);
		
		$option_data['variantID'] = $variantID;
		if(isset($variant_data['recomended'])) $variant_data['recomended'] = ($variant_data['recomended']=='')?1:floatval($variant_data['recomended']);	
		if($variantID){ 
			ProductFilter_UpdateOptionValue($variant_data);		
			$this->statistic['variant_modify']++;
		}elseif($allow_insert){
			ProductFilter_AddOptionValue($variant_data);
			$this->statistic['variant_added']++;
		}
	}

	
	protected function fixCategoryName(&$option_data){
		foreach($this->name_fields as $name_field){
			if(preg_match('/^([!]*)(.+)$/',$option_data[$name_field],$matches)){
				$option_data[$name_field] = ($matches[2]=='!')?'':$matches[2];
			}
		}
	}
	
	protected function fixOptionName(&$param_data){
		foreach($this->name_fields as $name_field){
			$param_data[$name_field] = preg_replace('/^!!/','',$param_data[$name_field]);
		}
	}

	
	protected function isCategory($data){
		$res = false;
		foreach($this->name_fields as $name_field){
			if(preg_match('/^(!)(.+)$/',$data[$name_field],$matches)){
				$res = true;
			}
		}
		return $res;
	}
	
	protected function isOption($data){
		$res = false;
		foreach($this->name_fields as $name_field){
			if(preg_match('/^(!!)(.+)$/',$data[$name_field],$matches)){
				$res = true;
			}
		}
		return $res;
	}
	
	protected function isParam(){
		$res = false;
		foreach($this->name_fields as $name_field){
			if(!preg_match('/^(!)(.+)$/',$data[$name_field],$matches)){
				$res = true;
			}
		}
		return $res;
	}
	
	function isValidData($data){
		if(!isset($data[$this->primary_col])||$data[$this->primary_col]===""){
			return false;
		}else{
			return true;
		}
	}

	
}
?>