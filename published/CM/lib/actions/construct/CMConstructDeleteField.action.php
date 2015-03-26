<?php

class CMConstructDeleteFieldAction extends UGViewAction 
{
    protected $type_id;
    protected $type_name;
    protected $field_id;
    protected $field_info;
    protected $all = false;
     
    public function __construct()
    {
        parent::__construct();
        $this->all = Env::Get('all', Env::TYPE_INT, 0);
        $this->title = $this->all ? _("Delete field") : _("Disable field");
        $this->type_id = Env::Get('type_id', Env::TYPE_INT, 0);   
        $contact_type = new ContactType($this->type_id);
        $this->type_name = $contact_type->getTypeName();
		$this->field_id = Env::Get('field_id', Env::TYPE_INT, 0);
		$this->field_info = ContactType::getField($this->field_id);
		$this->title .= " &quot;".ContactType::getName($this->field_info['name'], User::getLang())."&quot;";
    }

    protected function getEmptyCondition()
    {
        $dbname = $this->field_info['dbname'];
        switch ($this->field_info['type']) {
            case 'DATE': 
                return $dbname." IS NOT NULL AND ".$dbname." != '0000-00-00'";
            default: 
                return $dbname." IS NOT NULL AND ".$dbname." != ''";
        }
    } 
    
    public function prepareData()
    {
        $contacts_model = new ContactsModel();
        $sql = ($this->all ? "" : "CT_ID = ".(int)$this->type_id." AND ") . $this->getEmptyCondition();

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
	        	$this->all = 2;
	        }
        }               
        $this->smarty->assign('n', $this->field_info['dbname'] == 'C_MIDDLENAME' ? -1 : $contacts_model->countBySQL($sql));
        $this->smarty->assign('all', $this->all);
        $this->smarty->assign('field_id', $this->field_id);
        $this->smarty->assign('section_id', $this->field_info['section']);
        $this->smarty->assign('type_id', $this->type_id);
        $this->smarty->assign('type', $this->type_name);
    }
}
?>