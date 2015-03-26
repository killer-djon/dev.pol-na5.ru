<?php

class CMAjaxConstructMoveSectionAction extends UGAjaxAction
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

	    $after_id = Env::Post('after', Env::TYPE_INT, false);
		
		$contact_type_model = new ContactTypeModel();
		$old_after_id = $contact_type_model->getPreviousField($this->section_info['sorting']);
		if ($old_after_id != $after_id) {		
    		$section_info = ContactType::getField($after_id);
    		$sql = false;
    		if ($section_info['sorting'] > $this->section_info['sorting']) {
        		$sql = "UPDATE CONTACTFIELD 
        				SET CF_SORTING = CF_SORTING - 1 
        				WHERE CF_SECTION IS NULL AND CF_SORTING > i:from AND CF_SORTING <= i:to ";
	        } elseif ($section_info['sorting'] < $this->section_info['sorting'])  {
                $section_info['sorting']++;
        		$sql = "UPDATE CONTACTFIELD 
        				SET CF_SORTING = CF_SORTING + 1 
        				WHERE CF_SECTION IS NULL AND CF_SORTING >= i:to AND CF_SORTING < i:from ";
	        }
	        if ($sql) {
        		$contact_type_model->prepare($sql)
                   ->exec(array('from' => $this->section_info['sorting'], 'to' => $section_info['sorting']));
                $contact_type_model->setFieldSorting($this->section_id, $section_info['sorting']);            
	        }
	        
		}
		// Generate response in json
		$this->response = array(
			'id' => $this->section_id,
			'dbname' => null,
			'type' => 'SECTION',
			'options' => "",
			'standart' => 0,
			'section' => null,
			'after_id' => $after_id
		);
		$this->response += CMConstructIndexAction::getTypeFields();
	}
	
}

?>