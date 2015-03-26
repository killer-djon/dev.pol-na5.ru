<?php 
class CMConstructIndexAction extends UGViewAction
{
	
    protected $type_id;
    /**
     * @var ContactType
     */
    protected $type;
    
	public function __construct()
	{
		parent::__construct();
		$this->type_id = Env::Get('type_id', Env::TYPE_INT, 0);
		$this->type = new ContactType($this->type_id);
	}
	
	public function sortFields($a, $b) 
	{
		if ($a['sorting'] == $b['sorting']) {
			return 0;
		}
		return ($a['sorting'] < $b['sorting']) ? -1 : 1;
	}
	
	
	public static function getTypeFields()
	{
        $all_fields = ContactType::getAllFields(User::getLang(), false, false, true);
	    // dbfields
	    $dbfields = array();
		foreach ($all_fields as $id => $field) {
			if ($field['type'] != 'SECTION') {
				$dbfields[$field['dbname']] = $field['type'];
			}
		}
		$type_sections = array();
		$photo_field = array();
		$types = ContactType::getTypes(User::getLang());
		foreach ($types as $type_id => $type_info) {
			$contact_type = new ContactType($type_id);
			$main_fields = $contact_type->getMainFields();
			$main_section = $contact_type->getMainSection();
			$list_sections = array();
			$type = $contact_type->getType(User::getLang());
			foreach ($type['fields'] as $section) {
				if ($section['id'] != $main_section) {
					$list_sections[$section['id']] = array('name' => $section['name'], 'fields' => array());
				}
				foreach ($section['fields'] as $field) {
					if ($field['type'] == 'IMAGE') {
						continue;
					}
					if ($section['id'] == $main_section && !in_array($field['id'], $main_fields)) {
						$list_sections[$section['id']."_".$field['id']] = array(
							'name' => $field['name'], 
							'fields' => array(
								array($field['dbname'], $field['name']) 
							) 
						);
					} else if ($section['id'] != $main_section) {
						$list_sections[$section['id']]['fields'][] = array($field['dbname'], $field['name']);		
					}
				}	
			}
			$pf = ContactType::getField($contact_type->getPhotoField());
			$photo_field[$type_id] = $pf ? $pf['dbname'] : '';
			$type_sections[$type_id] = array_values($list_sections);
		}
		return array(
			'listfields' => $type_sections,
			'photoField' => $photo_field,
		    'dbfields' => $dbfields
		);		
    }
	    
	
	public function prepareData()
	{
	    
	    $sections = ContactType::getAllFields(User::getLang(), ContactType::TYPE_SECTION);
	    $result = array();
	    foreach ($sections as $section) {
	        $section['fields'] = array();
	        $result[$section['id']] = $section;
	    }
	    $type_info = $this->type->getType(User::getLang());	    
	    $fields = ContactType::getAllFields(User::getLang(), ContactType::TYPE_FIELD, false, false, false);

	    foreach ($fields as $f) {
	        if (!isset($type_info['fields'][$f['section']]['fields'][$f['id']])) {
	            $f['disabled'] = true;
	            $result[$f['section']]['fields'][$f['id']] = $f;
	        } else {
	            $result[$f['section']]['fields'][$f['id']] = $type_info['fields'][$f['section']]['fields'][$f['id']];
	        }
	    }
	    
	    foreach ($result as &$section) {
	        uasort($section['fields'], array($this, 'sortFields'));
	    }
	        
	    $main_fields = $this->type->getMainFields(false);
	    $a = array();
	    foreach ($main_fields as $field) {
	        $a[$field] = 1;
	    }
	    
	    // For person
	    if ($this->type_id == 1) {
	    	$format = $type_info['fname_format'][0];
	    	$fs = $this->type->getMainFields(true);
	    	foreach ($fs as $f) {
	    		$format = str_replace("!".$f."!", ContactType::getFieldName($f), $format);
	    	}
	    	$this->smarty->assign("fullname", str_replace(array(" name", " Name"), "", $format));
	    }
	    
	    $this->smarty->assign('type_id', $this->type_id);
	    $this->smarty->assign('type_name', ContactType::getName($type_info['name'], User::getLang()));
	    $this->smarty->assign('main', $a);
	    $this->smarty->assign('photo_id', $this->type->getPhotoField(false));
	    $this->smarty->assign('photo_exists', $this->type->getPhotoField());
	    //print_r($type_info);
	    //var_dump($type_info['fields']); exit;
	    
	    $this->smarty->assign('types', ContactType::getDbTypeNames());
	    $this->smarty->assign('fields', array_values($result));
	}
}