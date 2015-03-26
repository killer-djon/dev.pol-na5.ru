<?php 

class CMAjaxConstructEnableAction extends UGAjaxAction
{
    
    protected $type_id;
    protected $field_id;
    
    protected $right = array('CM', Rights::FUNCTIONS, 'CANTOOLS');
    public function __construct()
    {
        parent::__construct();
        $this->type_id = Env::Get('type_id', Env::TYPE_INT, 0);
        $this->field_id = Env::Post('field_id', Env::TYPE_INT, 0);
        
        if ($this->type_id && $this->field_id) {
            $this->enableField();
            $this->response = CMConstructIndexAction::getTypeFields();
        }
    }
    
    public function enableField()
    {
	    $contact_type_model = new ContactTypeModel();
	    $contact_type_model->setTypeField($this->type_id, $this->field_id, true);
	    User::addMetric('FIELDMODIFY');
    }
}

?> 