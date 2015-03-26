<?php

class CMAjaxConstructEditSectionAction extends UGAjaxAction
{
    protected $section_id;
    protected $section_info = array();
	public function __construct()
	{
		parent::__construct();
		$this->section_id = Env::Post('section_id', Env::TYPE_INT, 0);
		
		if ($this->section_id && Env::Post('save')) {
    	    $this->section_info = ContactType::getField($this->section_id);
			$this->edit();
		}
	}
	
	public function edit() 
	{
	    $type_id = Env::Post('type_id', Env::TYPE_INT, 1);
		$langs = Env::Post('lang', false, array());
		foreach (Env::Post('name', false, array()) as $key => $name)  {
			if (!$name) {
			    if (isset($this->section_info['name'][$langs[$key]])) {
			        unset($this->section_info['name'][$langs[$key]]);
			    }
				continue;
			}
			if (is_array($this->section_info['name'])) {
				$this->section_info['name'][$langs[$key]] = $name;
			} else {
				$this->section_info['name'] = array(
					'all' => $this->section_info['name'],
					$langs[$key] => $name
				);
			}
		}
		
		$contact_type_model = new ContactTypeModel();
		// Save name
		$contact_type_model->setFieldName($this->section_id, $this->section_info['name']);
		// Generate response in json
		$this->response = array(
			'id' => $this->section_id,
			'dbname' => null,
			'type' => 'SECTION',
			'name' => ContactType::getName($this->section_info['name'], User::getLang()),
			'options' => "",
			'standart' => 0,
			'section' => null,
		);
        $this->response += CMConstructIndexAction::getTypeFields();		
	}
	
}

?>