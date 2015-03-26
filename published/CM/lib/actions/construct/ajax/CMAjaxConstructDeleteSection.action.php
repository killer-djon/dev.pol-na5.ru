<?php

class CMAjaxConstructDeleteSectionAction extends UGAjaxAction
{
	protected $section_id;
	protected $type_id;
	public function __construct()
	{
		parent::__construct();
		$this->section_id = Env::Post('section_id', Env::TYPE_INT, 0);
		$this->type_id = Env::Post('type_id', Env::TYPE_INT, 0);
		if (Env::Post('delete')) {
			$this->delete();
		}
	}
	
	
	protected function delete()
	{
	    $contact_type_model = new ContactTypeModel();
	    if ($contact_type_model->getFields($this->section_id)) {
            throw new Exception("['This section is not empty`]");	        
	    }

	    // Check if this field not use
		$contact_type_model = new ContactTypeModel();
		$type_ids = $contact_type_model->getTypeIds();
		foreach ($type_ids as $type_id) {
            $contact_type_model->setTypeField($this->type_id, $this->section_id, false);
		} 
	    $contact_type_model->deleteField($this->section_id);
	    $this->response = CMConstructIndexAction::getTypeFields();
	}
}

?>