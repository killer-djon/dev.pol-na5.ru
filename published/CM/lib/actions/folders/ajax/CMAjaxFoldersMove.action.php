<?php

class CMAjaxFoldersMoveAction extends UGAjaxAction
{
	public function __construct()
	{
		$from = Env::Post('from', Env::TYPE_STRING, 0);
		$to = Env::Post('to', Env::TYPE_STRING, 0);
		$min_rights = 7;

		if(User::hasAccess('CM', 'FOLDERS', $to) < $min_rights || !User::hasAccess('CM', 'FOLDERS', $from)) {
			throw new Exception('Access denied.');
		}

		$rights = new Rights(User::getId());
		$folders = $rights->getFolders('CM', false, true, Rights::FLAG_ARRAY_OFFSET|Rights::FLAG_RIGHTS_INT, $from);

		foreach($folders as $f) {
			if($f['RIGHTS'] < $min_rights) {
				throw new Exception(_('You have not enough rights to move this folder.'));
			}
		}

		$contact_folder_model = new ContactFolderModel();
		$new = $contact_folder_model->move($from, $to);
		
		$contacts_model = new ContactsModel();
		$contacts_model->moveFolder($from, $new);
		
		$rights_model = new UserRightsModel();
		$rights_model->updateObject('CM', 'FOLDERS', $from, $new, true);

		$this->response = array($from, $to, $new);
	}
}
?>