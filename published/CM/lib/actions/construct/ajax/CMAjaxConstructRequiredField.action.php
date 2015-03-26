<?php

class CMAjaxConstructRequiredFieldAction extends UGAjaxAction
{
	protected $field_id;
	protected $field_info = array();
	protected $req = 0;
	public function __construct()
	{
		parent::__construct();
		$this->field_id = Env::Post('field_id', Env::TYPE_INT, 0);
		$this->type_id = Env::Post('type_id', Env::TYPE_INT, 0);
		$this->field_info = ContactType::getField($this->field_id);
		if (Env::Post('req', false, false) !== false) {
		    $this->req  = Env::Post('req', Env::TYPE_INT, 0);
			$this->save();
		}
	}
	
	public function save()
	{
        $contact_type_model = new ContactTypeModel();
   	    $contact_type_model->setRequired($this->type_id, $this->field_id, $this->req);
   	    User::addMetric('FIELDMODIFY');
   	    $this->response = $this->req;
	}
}
?>