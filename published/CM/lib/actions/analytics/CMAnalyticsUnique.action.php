<?php

class CMAnalyticsUniqueAction extends UGViewAction
{
    
    protected $field ;
    
    public function __construct()
    {
        parent::__construct();
        $this->field = Env::Get('field');
        $this->offset = Env::Get('offset', Env::TYPE_INT, 0);
    }
    
    
    public function prepareData()
    {
        $contact_types_model = new ContactTypeModel();
        $field_info = $contact_types_model->getFieldInfo($this->field);
        if ($field_info) {
	        $this->smarty->assign('field_name', ContactType::getName($field_info['name'], User::getLang()));
	        
	        $contacts_model = new ContactsModel();
	        $sql = "SELECT ".$this->field." f, COUNT(*) c 
	        		FROM CONTACT 
	        		GROUP BY ".$this->field;
	        		
	        $values = $contacts_model->query($sql)->fetchAll();
	        $this->smarty->assign('values', $values);
	        $this->smarty->assign('field', $this->field);
        } else {
            throw new UserException(_s("Field not found"), "Field ".$this->field." not found");
        }
        
    }
    
    
}

?>