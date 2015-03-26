<?php

/**
 * Saving of the view settings of users and contacts by ajax
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id$
 */
class CMAjaxContactsMultieditfieldsAction extends UGAjaxAction 
{
	
	public function __construct() 
	{
		parent::__construct();
	}

	public function getFields()
	{
		$fields = ContactType::getAllFields(User::getLang(), ContactType::TYPE_FIELD, true, false);
		// Person
		$contact_type = new ContactType(1);
		$dbfields = $contact_type->getTypeDbFields();
		$middle_exists = in_array('C_MIDDLENAME', $dbfields);
		$visible_fields = array();
		if ($fs = Env::Get('f')) {
		    $fs = explode(',', $fs);
		    $visible_fields = array();
		    foreach ($fs as $f) {
		    	if ($f == 'C_NAME') {
			        $visible_fields[] = ContactType::getFieldId('C_FIRSTNAME');
			        if ($middle_exists) {
			        	$visible_fields[] = ContactType::getFieldId('C_MIDDLENAME');
			        }
			        $visible_fields[] = ContactType::getFieldId('C_LASTNAME');
		    	} else {
			        $id = ContactType::getFieldId($f, true);
			        if ($id > 0) {
			            $visible_fields[] = $id;
			        }
		    	}
		    }
		}
		if (!$visible_fields) {
			$visible_fields = array(
				ContactType::getFieldId('C_FIRSTNAME')
			);
			if ($middle_exists) {
			    $visible_fields[] = ContactType::getFieldId('C_MIDDLENAME');
			}
			$visible_fields[] =	ContactType::getFieldId('C_LASTNAME'); 
			$visible_fields[] = ContactType::getFieldId('C_EMAILADDRESS');
			$visible_fields[] = ContactType::getFieldId('C_COMPANY');
		}
		$r = array();
		foreach ($visible_fields as $fid) {
		    $r[$fid] = array();    
		}
		$visible_fields = $r;
		$hidden_fields = array();

		foreach ($fields as $field) {
			if ($field['type'] != 'IMAGE'){
				if (isset($visible_fields[$field['id']])) {
					if (empty($field['dbname'])) $field['dbname'] = "";
					$visible_fields[$field['id']] = $field;
				} else {
					$hidden_fields[$field['id']] = $field;
				}
			} elseif (isset($visible_fields[$field['id']])) {
				unset($visible_fields[$field['id']]);
			}
		}

		$this->response['visible_fields'] = $visible_fields;
		$this->response['hidden_fields'] = $hidden_fields;
	}

	public function prepareData()
	{
		$this->getFields();
	}
}

?>