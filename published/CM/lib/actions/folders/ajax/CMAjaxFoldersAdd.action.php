<?php 

class CMAjaxFoldersAddAction extends UGAjaxAction 
{
    
    public function prepareData()
    {
        $folders_model = new ContactFolderModel();
        
        $name = Env::Post('name', Env::TYPE_STRING, _('New Folder'));
        $parent = Env::Post('parentId', Env::TYPE_STRING, 'ROOT');
        if ($parent == 'FOLDER') {
        	$parent = Env::Post('subfolder');
        }
        if (User::hasAccess('CM', 'FOLDERS', $parent) < 3) {
        	$this->errors = _("Not sufficient rights");
        	return false;
        }
        $access = Env::Post('access', Env::TYPE_INT, 0);
        if ($access == 2 && User::isAdmin('CM')) {
            $folder_id = 'PUBLIC';
            $name = _s('Public');
            $parent = 'ROOT';
            User::setSetting('PUBLIC', 1, 'CM', '');
        } else {
	        $folder_id = $folders_model->add($name, $parent);
	        
	        if ($parent == 'ROOT') {
		        $rights = new Rights(User::getId());
		      	$rights->set('CM', Rights::FOLDERS, $folder_id, 7);
	        } else {
	            // Inherit from parent folder
	            $user_rights_model = new UserRightsModel();
	            $users = $user_rights_model->getUsers('CM', Rights::FOLDERS, $parent, false);
	            foreach ($users as $user_id => $r) {
	                $user_rights_model->save($user_id, '/ROOT/CM/FOLDERS', $folder_id, $r[1]);
	            }
	            $group_rights_model = new GroupsRightsModel();
	            $groups = $group_rights_model->getGroups('CM', Rights::FOLDERS, $parent);
	            foreach ($groups as $group_id => $r) {
	                $group_rights_model->save($group_id, '/ROOT/CM/FOLDERS', $folder_id, $r);
	            }
	        }
        }
        
        $this->response = array(
            'id'  => $folder_id, 
            'name' => $name,
            'parentId' => $parent,
            'access' => $access
        );
        if ($folder_id == 'PUBLIC') {
            $this->response['after'] = 'PRIVATE'.User::getContactId();
        }

    }
}

?>