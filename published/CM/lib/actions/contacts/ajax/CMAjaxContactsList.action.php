<?php

class CMAjaxContactsListAction extends UGAjaxAction 
{
	const BY_NAME = 0;
	const BY_FOLDER = 1;
	const LIMIT = 100;
	
	protected $type = false;
	protected $offset = 0;
	
	public function __construct()
	{
		parent::__construct();
		$this->type = Env::Post('type', Env::TYPE_INT, 0);	
		$this->offset = Env::Post('offset', Env::TYPE_INT, 0);
	}
	
	public function getContacts()
	{
		$contacts = array();
		switch ($this->type) {
			case self::BY_FOLDER: {
				$folder_id = Env::Post('folder_id');
				if (!$folder_id) $folder_id = 'ALL';
    			$contacts_model = new ContactsModel();
    			$contacts = $contacts_model->getByFolderId($folder_id, User::getContactId(), "C_FULLNAME", $this->offset.", ".self::LIMIT);
				break;
			}
			case self::BY_NAME: 
			default: {
				$name = Env::Post('name');
				if ($name) {
				    $contacts_model = new ContactsModel();
				    $contacts = $contacts_model->getByName($name, "C_FULLNAME", $this->offset.", ".self::LIMIT);
				}
			}
		}

		$result = array();
		Contact::useStore(false);
		foreach ($contacts as $contact_info) {
			$result[] = array(
				$contact_info['C_ID'],
				strip_tags(Contact::getName($contact_info['C_ID'], false, $contact_info))
			);
		}
		Contact::useStore(true);
		$this->response['contacts'] = $result;	
	}
	
	public function prepareData()
	{
		$this->getContacts();
	}
}

?> 