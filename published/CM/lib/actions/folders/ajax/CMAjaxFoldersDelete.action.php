<?php

class CMAjaxFoldersDeleteAction extends UGAjaxAction
{
	protected $folder_id;
	
	public function __construct()
	{
		$this->folder_id = Env::Post('id');
		
        if (User::hasAccess('CM', 'FOLDERS', $this->folder_id) == 7) {
	        $rights = new Rights(User::getId());
	        $folders = $rights->getFolders('CM', false, true, Rights::FLAG_ARRAY_OFFSET|Rights::FLAG_RIGHTS_INT, $this->folder_id);
	
	        foreach ($folders as $f) {
	            if ($f['RIGHTS'] < 7) {
	                throw new Exception(_('You have no rights to delete current folder or subfolders included in current folder.'));
	            }
	        }
	    } elseif (User::isAdmin('CM') && $this->folder_id == "PUBLIC") {
	        
        } else {
           	throw new Exception('Access denied!');
        }		
        // Check that current user is not in deleted folder
        $contact_info = User::getInfo();
        if ($contact_info['CF_ID'] == $this->folder_id) {
            throw new Exception(_('Your own account entry is in this folder. You can not delete yourself!'));
        }
        
        // Check users and delete them if user has access to UG        
        $users_model = new UsersModel();     
        if ($users_model->countByFolderId($this->folder_id)) {
            if (!User::hasAccess('UG')) {
                throw new Exception(_("Folder could not be deleted as some contacts have user accounts."));
            } else {
                $users_model->deleteByFolderId($this->folder_id);                
            }
            
        }
        // Delete contacts
        $contacts_model = new ContactsModel();
        $contacts_model->deleteByFolderId($this->folder_id);
        // Delete contact folder with nested folders
        if ($this->folder_id == 'PUBLIC') {
            User::unSetSetting('PUBLIC', 'CM', '');
        } else {
	        $folders_model = new ContactFolderModel();
	        $folders_model->delete($this->folder_id);
	        // Delete rights to folder
	        $rights = new Rights();
	        $rights->set('CM', Rights::FOLDERS, $this->folder_id, 0);
        }  
    }
	
}
?>