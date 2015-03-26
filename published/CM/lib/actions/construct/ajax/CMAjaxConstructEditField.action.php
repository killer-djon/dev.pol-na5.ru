<?php

class CMAjaxConstructEditFieldAction extends UGAjaxAction
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
		$langs = Env::Post('lang', false, array());
		$names = array();
		foreach (Env::Post('name', false, array()) as $key => $name)  {
			if (!$name) {
			    if (isset($this->field_info['name'][$langs[$key]])) {
			        unset($this->field_info['name'][$langs[$key]]);
			    }
				continue;
			}
			$names[] = $name;
			if (is_array($this->field_info['name'])) {
				$this->field_info['name'][$langs[$key]] = $name;
			} else {
				$this->field_info['name'] = array(
					'all' => $this->field_info['name'],
					$langs[$key] => $name
				);
			}
		}
		
		$contact_type_model = new ContactTypeModel();
		
		$type = strtoupper(Env::Post('type'));
		if (!$type) {
			$type = $this->field_info['type'];
		}
		
		$options = Env::Post('options', Env::TYPE_STRING, "");
		
		// Change type of the field
		// @todo: Need to change the length in cases where the type of the field remain the same, but options are changed
		$dbname = Env::Post('dbname');

	    if (!$names) {
	        $error_fields[] = 'name-user-lang';
	    }
	    if ($type == 'VARCHAR' && !$options) {
	        $error_fields[] = 'options';
	    }
	    if (!$dbname) {
	        $error_fields[] = 'dbname';
	    }
		if ($error_fields) {
		    $this->errors[] = array(_s('Please fill required fields'), $error_fields);
		}
		
		if (preg_match_all("/([^a-z0-9_])/usi", $dbname, $matches)) {
            $this->errors[] = array(_s("Please use only Latin characters and numbers"), 'dbname');
		}

		if ($type == 'VARCHAR' && !(is_numeric($options) && $options >= 1 && $options <= 255)) {
		    $this->errors[] = array(_s('Please enter the width between 1 and 255'), 'options');
		}
		
        if ($this->errors) {
            return false;
        }		
		
		$dbname = mb_strtoupper($dbname);
	    if (substr($dbname, 0, 2) != 'C_') {
	        $dbname = 'C_'.$dbname;
	    }

	    $edit = $this->field_info['type'] != $type || $this->field_info['dbname'] != $dbname;
	    if ($this->field_info['type'] == 'VARCHAR' && $this->field_info['options'] != $options) {
	        $edit = true;
	    }
		if ($edit) {
		    if (Env::Get('dbname')) {
			    try {
				    ContactType::editField($this->field_info['dbname'], $dbname, array($this->field_info['type'], $this->field_info['options']), array($type, $options));
	    		} catch (Exception $e) {
	    		    if (!$this->errors) {
	    		        $this->errors = array();
	    		    }    
	    		    $this->errors[] = array($e->getMessage(), 'dbname');
	    		    return false;
	    		}
		    } else {
		        $try = 1;
		        $dbname_orig = $dbname;
		        while ($try) {
		            try {
		                ContactType::editField($this->field_info['dbname'], $dbname, array($this->field_info['type'], $this->field_info['options']), array($type, $options));
		                $try = 0;
		            } catch (Exception $e) {
		                $dbname = $dbname_orig . $try;
		                $try++;
		            }
		        }
		    }  
		}
		
		if ($this->field_info['dbname'] != $dbname && $dbname) {
		    $contact_type_model->setDbName($this->field_id, $dbname);
		}
        // Save field description
		$contact_type_model->saveField($this->field_id, $type, $this->field_info['name'], $options, $this->field_info['section'], $this->field_info['sorting']);
	    User::addMetric('FIELDMODIFY');
		$types = ContactType::getDbTypeNames();
		// Generate response 
		$this->response = array(
			'id' => $this->field_id,
			'type' => $types[$type] . ($type == 'VARCHAR' ? " (".$options.")" : ""),
			'name' => ContactType::getName($this->field_info['name'], User::getLang()),
			'options' => $options,
			'standart' => $this->field_info['standart'],
			'section' => $this->field_info['section'],
			'sorting' => $this->field_info['sorting']
		);
		$this->response += CMConstructIndexAction::getTypeFields();
	}
	
}

?>