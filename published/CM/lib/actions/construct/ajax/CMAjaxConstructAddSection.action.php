<?php

class CMAjaxConstructAddSectionAction extends UGAjaxAction
{
	public function __construct()
	{
		parent::__construct();
		if (Env::Post('save')) {
			$this->add();
		}
	}
	
	public function add() 
	{
	    $type_id = Env::Post('type_id', Env::TYPE_INT, 1);
		$langs = Env::Post('lang', false, array());
		$name = array();
		foreach (Env::Post('name', false, array()) as $key => $v)  {
			if (!$v) {
				continue;
			}
			$name[$langs[$key]] = $v;
		}
		$after_id = Env::Post('after', Env::TYPE_INT, false);
		$contact_type_model = new ContactTypeModel();
		
		$field_info = ContactType::getField($after_id);
		
		$sql = "UPDATE CONTACTFIELD 
				SET CF_SORTING = CF_SORTING + 1 
				WHERE CF_SECTION IS NULL AND CF_SORTING > i:sort";
		$contact_type_model->prepare($sql)->exec(array('sort' => $field_info['sorting']));
		
		$section_id = $contact_type_model->addField(null, 'SECTION', $name, "", null, $field_info['sorting'] + 1);
        
		$this->response = array(
			'id' => $section_id,
			'dbname' => null,
			'type' => 'SECTION',
			'name' => ContactType::getName($name, User::getLang()),
			'options' => "",
			'standart' => 0,
			'section' => null,
			'sorting' => $field_info['sorting'] + 1,
			'after_id' => $after_id
		);
		$this->response += CMConstructIndexAction::getTypeFields();
	}
	
}

?>