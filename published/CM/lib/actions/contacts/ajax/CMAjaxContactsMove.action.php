<?php

class CMAjaxContactsMoveAction extends UGAjaxAction 
{
	
	public function __construct()
	{
		parent::__construct();

		$folderId = Env::Post('folderId', Env::TYPE_STRING, null);
		$contact_ids = Env::Post('contacts', Env::TYPE_ARRAY_INT, array());
		
		$contact_folder_model = new ContactFolderModel();
		$folder = $contact_folder_model->get($folderId);
				
        $contacts_model = new ContactsModel();
        $contacts_info = $contacts_model->getByIds($contact_ids);
        
		$success = $errors = 0;
		$rights = new Rights(User::getId());
		foreach($contacts_info as $contact_info) {
            $folder_right = Contact::accessFolder($contact_info['CF_ID']);
			if($folder_right < 3) {
				$errors++;
			} else {
				$success++;
				$contacts_model->save($contact_info['C_ID'], array('CF_ID'=>$folderId));
			}
		}

		$this->response = array('success'=>$success, 'errors'=>$errors, 'folderName'=>$folder['NAME']);
	}
	
}

?> 