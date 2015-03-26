<?php

class CMAjaxConstructMoveFieldAction extends UGAjaxAction
{
	protected $field_id;
	protected $field_info = array();
	public function __construct()
	{
		parent::__construct();
        $this->type_id = Env::Post('type_id', Env::TYPE_INT, 0);
		$this->field_id = Env::Post('field_id', Env::TYPE_INT, 0);
		$this->field_info = ContactType::getField($this->field_id);
		if ($this->field_info && Env::Post('save')) {
			$this->edit();
		}
	}
	
	public function edit() 
	{	
	    $error_fields = array();
		// Save position
		$after_id = Env::Post('after', Env::TYPE_INT, false);
		$main_section = ContactType::getMainSection();
		$after_main = false;
	    if ($after_id == $main_section) {
	        $contact_type = new ContactType($this->type_id);
			$all = ContactType::getAllFields(User::getLang());
			foreach ($all as $f) {
				if ($f['section'] == $main_section && $f['standart']) {
					$after_id = $f['id'];
				}
			}
			$after_main = true;
			//$after_info = ContactType::getField($after_id);
		}		
		$contact_type_model = new ContactTypeModel();
		
		$old_after_id = $contact_type_model->getPreviousField($this->field_info['sorting'], $this->field_info['section']);

		if ($old_after_id != $after_id) {		
    		$field_info = ContactType::getField($after_id);
    		$change_section = false;
    		if (!$field_info['section']) {
    		    if ($this->field_info['section'] != $field_info['id']) {
    		        $change_section = true;
    		        $field_info['sorting'] = 1;
    		    } else {
        		    $field_info['sorting'] = 0;
    		    }
    		} else {
    		    if ($this->field_info['section'] != $field_info['section']) {
    		        $change_section = true;
    		    }
    		}
   		
    		$sql = false;
    		if ($change_section) {
		        $sql = "UPDATE CONTACTFIELD 
		        		SET CF_SORTING = CF_SORTING - 1 
		        		WHERE CF_SECTION = i:section AND CF_SORTING > i:sorting";
		        $contact_type_model->prepare($sql)->exec(array(
		        	'section' => $this->field_info['section'], 
		        	'sorting' => $this->field_info['sorting']
		        ));
       		    $this->field_info['section'] = $field_info['section'] ? $field_info['section'] : $field_info['id'];		            		    
		        $sql = "UPDATE CONTACTFIELD 
						SET CF_SORTING = CF_SORTING + 1 
						WHERE CF_SECTION = i:section AND CF_SORTING >= i:to";
    		} elseif ($field_info['sorting'] > $this->field_info['sorting']) {
        		$sql = "UPDATE CONTACTFIELD 
        				SET CF_SORTING = CF_SORTING - 1 
        				WHERE CF_SECTION = i:section AND CF_SORTING > i:from AND CF_SORTING <= i:to ";
	        } elseif ($field_info['sorting'] < $this->field_info['sorting'])  {
	            $field_info['sorting']++;
        		$sql = "UPDATE CONTACTFIELD 
        				SET CF_SORTING = CF_SORTING + 1 
        				WHERE CF_SECTION = i:section AND CF_SORTING >= i:to AND CF_SORTING <= i:from ";
	        }
	        if ($sql) {
        		$contact_type_model->prepare($sql)
                   ->exec(array('section' => $this->field_info['section'], 'from' => $this->field_info['sorting'], 'to' => $field_info['sorting']));
                $this->field_info['sorting'] = $field_info['sorting'];
	        }
		}
		
        // Save field description
		$contact_type_model->saveSorting($this->field_id, $this->field_info['section'], $this->field_info['sorting']);
	    User::addMetric('FIELDMODIFY');
		// Generate response 
		$this->response = array(
			'id' => $this->field_id,
			'section' => $this->field_info['section'],
			'sorting' => $this->field_info['sorting'],
			'after_id' => $after_main ? "aux-fields" : $after_id
		);
		
		$this->response += CMConstructIndexAction::getTypeFields();
	}
	
}

?>