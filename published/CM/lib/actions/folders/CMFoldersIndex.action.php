<?php 

class CMFoldersIndexAction extends UGViewAction 
{
    public function prepareData()
    {
        User::setSetting('LASTFOLDER', 'ROOT', 'CM');
        $rights = new Rights(User::getId());
        $folders = $rights->getFolders('CM', false, true, Rights::FLAG_ARRAY_OFFSET | Rights::FLAG_RIGHTS_INT | Rights::FLAG_NOT_EMPTY);

        $contacts_model = new ContactsModel();
        $count_all = 0;
        /*
        $count_all = $contacts_model->countByContact(User::getContactId());
        
        // Advanced folders
        $add_folders = array();
        $add_folders[] = array(
        	'ID' => 'MY', 
            'NAME' => _s("My contacts"),
            'RIGHTS' => 7,
            'OFFSET' => 1,
            'COUNT' => $count_all
        );
        
        // For admin only
        if (User::isAdmin('CM')) {
            $count_other = $contacts_model->countByContact(User::getContactId(), true);
            $count_all += $count_other; 
            $add_folders[] = array(
            	'ID' => 'OTHER', 
                'NAME' => _s("Other users contacts"),
                'RIGHTS' => 7,
                'OFFSET' => 1,
                'COUNT' => $count_other
            );            
        }
        
        $folders = array_merge($add_folders, $folders);
        */
        $count = $contacts_model->countAllByFolders();
        foreach ($folders as &$folder) {
            if (isset($count[$folder['ID']])) {
        	    $folder['COUNT'] = $count[$folder['ID']];
        	    $count_all += $count[$folder['ID']];
            }
        }
        $this->smarty->assign('right_names', Rights::getNames());
        $this->smarty->assign('folders', $folders);
        $this->smarty->assign('contacts_count', $count_all);
    }
}

?>