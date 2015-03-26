<?php

class CMAjaxConstructAddFieldAction extends UGAjaxAction
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
		
		if ($after_id == ContactType::getMainSection()) {
			$all = ContactType::getAllFields(User::getLang());
			foreach ($all as $f) {
				if ($f['section'] == $field_info['id'] && $f['standart']) {
					$after_id = $f['id'];
				}
			}
			$field_info = ContactType::getField($after_id);
			$after_id = "aux-fields";
		}
				
		$section_id = $field_info['type'] == 'SECTION' ? $field_info['id'] : $field_info['section'];
		$field_type = strtoupper(Env::Post('type'));
		$options = Env::Post('options');
		$dbname = Env::Post('dbname');
	    $error_fields = array();
	    if (!$name) {
	        $error_fields[] = 'name-user-lang';
	    }
	    if ($field_type == 'VARCHAR' && !$options) {
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
		
		if ($field_type == 'VARCHAR' && !(is_numeric($options) && $options >= 1 && $options <= 255)) {
		    $this->errors[] = array(_s('Please enter the width between 1 and 255'), 'options');
		}

        if ($this->errors) {
            return false;
        }		
		
		$dbname = mb_strtoupper($dbname);
		if (Env::Get('dbname')) {
			try {
			    $dbname = ContactType::addField(array($field_type, $options), $dbname);
			    User::addMetric('FIELDADD');
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
				    $dbname = ContactType::addField(array($field_type, $options), $dbname);
				    User::addMetric('FIELDADD');
				    $try = 0;
		        } catch (Exception $e) {
		            $dbname = $dbname_orig . $try;
		            $try++;
		        }
		    }
		}
				
		$sorting = $field_info['type'] == 'SECTION' ? "0" : $field_info['sorting'];
		$sorting++;
		
		$sql = "UPDATE CONTACTFIELD 
				SET CF_SORTING = CF_SORTING + 1 
				WHERE CF_SECTION = i:section_id AND CF_SORTING >= i:sorting";
		$contact_type_model->prepare($sql)->exec(array('sorting' => $sorting, 'section_id' => $section_id));

		$field_id = $contact_type_model->addField($dbname, $field_type, $name, $options, $section_id, $sorting);
        $contact_type_model->setTypeField($type_id, $field_id, true);
        
        $types = ContactType::getDbTypeNames();
		$this->response = array(
			'id' => $field_id,
			'dbname' => $dbname,
			'type' => $types[$field_type] . ($field_type == 'VARCHAR' ? " (".$options.")" : ""),
			'name' => ContactType::getName($name, User::getLang()),
			'options' => $options,
			'standart' => 0,
			'section' => $section_id,
			'sorting' => $sorting,
			'after_id' => $after_id
		);
		
		ContactType::clearStore();
		$this->response += CMConstructIndexAction::getTypeFields();
	}
	
}

?>