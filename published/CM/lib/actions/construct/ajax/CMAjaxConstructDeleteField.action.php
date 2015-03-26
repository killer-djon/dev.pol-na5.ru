<?php

class CMAjaxConstructDeleteFieldAction extends UGAjaxAction
{
	protected $field_id;
	protected $field_info = array();
	protected $all = false;
	public function __construct()
	{
		parent::__construct();
		$this->all = Env::Post('all');
		$this->field_id = Env::Post('field_id', Env::TYPE_INT, 0);
		$this->type_id = Env::Post('type_id', Env::TYPE_INT, 0);
		$this->field_info = ContactType::getField($this->field_id);
		if (Env::Post('delete')) {
			$this->delete();
		}
	}
	
	public function delete()
	{
        $contact_type_model = new ContactTypeModel();
        // get all available types
        $type_ids = $contact_type_model->getTypeIds();
        if (!$this->all && !$this->field_info['standart']) {
	        $delete_all = true;
	        foreach ($type_ids as $type_id) {
	        	if ($type_id != $this->type_id) {
	        		$contact_type = new ContactType($type_id);
	        		if ($contact_type->fieldExists($this->field_id)) {
	        			$delete_all = false;
	        			break;
	        		}
	        	}
	        }
	        if ($delete_all) {
	        	$this->all = true;
	        }
        }         
        // if all types
	    if ($this->all) {
	        foreach ($type_ids as $type_id) {
	            // disable field in the each type
	            $contact_type_model->setTypeField($type_id, $this->field_id, false);
	        }
	        $sql = "UPDATE CONTACTFIELD 
	        		SET CF_SORTING = CF_SORTING - 1 
	        		WHERE CF_SECTION = i:section AND CF_SORTING > i:sorting";
	        $contact_type_model->prepare($sql)->exec(array(
	        	'section' => $this->field_info['section'], 
	        	'sorting' => $this->field_info['sorting']
	        ));
   		    // drop column
    		$contact_type_model->deleteField($this->field_id);
    		// drop field description
    		ContactType::deleteField($this->field_info['dbname']);
    		User::addMetric('FIELDDELETE');
	    }
	    // if single type of the contacts 
	    else {
    	    $contact_type_model->setTypeField($this->type_id, $this->field_id, false);
    	    // clear data for current type
    	    $contact_model = new ContactsModel();
    	    if ($this->field_info['dbname'] == 'C_MIDDLENAME') {
                // Concat C_FIRSTNAME and C_MIDDLENAME
                $sql = "UPDATE CONTACT 
                		SET C_FIRSTNAME = CONCAT(IF(C_FIRSTNAME IS NULL, '', CONCAT(C_FIRSTNAME, ' ')), C_MIDDLENAME)
                		WHERE CT_ID = ".(int)$this->type_id." AND C_MIDDLENAME IS NOT NULL";
                $contact_model->exec($sql);
    	    }
            $contact_model->emptyField($this->type_id, $this->field_info['dbname']);
            User::addMetric('FIELDMODIFY');    	        
		} 
		$this->response = CMConstructIndexAction::getTypeFields();
	}
}
?>