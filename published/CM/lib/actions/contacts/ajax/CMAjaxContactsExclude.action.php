<?php

class CMAjaxContactsExcludeAction extends UGAjaxAction
{
    protected $list_id;
    protected $contacts = array();
    public function __construct()
    {
        $this->contacts = Env::Post('contacts', Env::TYPE_ARRAY_INT, array());
        $this->list_id = Env::Post('list_id', Env::TYPE_INT, 0);
        if ($this->list_id && $this->contacts) {
	        $contact_list_model = new ContactListModel();
	        $contact_list_model->delete($this->list_id, $this->contacts);
	        $this->response['contacts'] = count($this->contacts);
        }
    }
}