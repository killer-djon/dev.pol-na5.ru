<?php

class Bodyaux extends ComponentModule{
	
	var $PageID = '1';
	
	function __construct(){
		
		if(!defined("AUX_PAGE_TABLE")){
			define("AUX_PAGE_TABLE", "SC_aux_pages");
		}
		
		parent::__construct();
		
	}
	
	function initSettings(){
		
		$this->Settings = array(
			'aux_page_id' => array(
				'name' => 'aux_page_id',
				'value' => '1',
				'type' => 'select',
				'title' => 'Статическая страница',
				'description' => '',
			)
		);
			
		$this->PageID = $this->getSettingValue('aux_page_id');
		
	}
	
	function initInterfaces(){
		
		$this->__registerComponent('bodyaux', 'Текстовое содержимое страницы', array('general_layout', 'home_page'), 
			'methodGetAuxPageId', 
			array(
				'aux_pageid' => array(
					'type' => 'select',
					'params' => array(
						'name' => 'aux_pageid',
						'title' => 'Список страниц',
						'options' => $this->getAuxPages()
					)
				)
			)
		);
				
	}
	
	function methodGetAuxPageId($call_settings = null){
		
		global $smarty;

		$local_settings = isset($call_settings['local_settings']) ? $call_settings['local_settings'] : array();

		if(isset($local_settings['aux_pageid']) && $local_settings['aux_pageid']){
			
			$this->PageID = $local_settings['aux_pageid'];
			
		}

		$moduleInstance = &ModulesFabric::getModuleObjByKey('aux_pages');
		/*@var $moduleInstance AuxPages*/

		$page = $moduleInstance->auxpgGetAuxPage($this->PageID);
		
		$smarty->assign("page_single", $page);
		$smarty->display('bodyaux.html');
		
	}
	
	static function getAuxPages(){
		
		$q = db_query("SELECT * FROM ".AUX_PAGE_TABLE." ORDER BY aux_page_ID");
		
		$aux_id = array();
		$aux_names = array();
		
		while($row = db_fetch_row($q)){
			
			$aux_id[] = $row["aux_page_ID"];
			$aux_names[] = $row["aux_page_name_ru"];
			
		}
	
		return array_combine($aux_id, $aux_names);
		
	}
	
}

?>