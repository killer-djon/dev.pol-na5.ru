<?php

class CMFoldersRightsAction extends UGViewAction
{
    protected $folder_id;
    protected $tab;
    public function __construct()
    {
        parent::__construct();
        $this->folder_id = Env::Get('folder_id');
        
        $this->tab = Env::Get('tab');
        if ($this->tab && $this->tab != 'groups') {
            $this->tab = 'users';
        }
        if ($this->tab) {
            $this->template = 'FoldersRights'.ucfirst($this->tab).".html";
        }
    }
    

    public function getUsersRights()
    {
        $user_rights_model = new UserRightsModel();
        $rights = $user_rights_model->getUsers('CM', Rights::FOLDERS, $this->folder_id);
       
        $admin_rights = $user_rights_model->getUsers('CM', Rights::FUNCTIONS, 'ADMIN');

        
        $users_model = new UsersModel();
        $names = $users_model->getNames('U_ID');
        $min = 7;
        $result = array();
        foreach ($names as $user_id => $u) {
            $r = isset($rights[$user_id]) ? $rights[$user_id] : array(0, 0, 0);
            if (isset($admin_rights[$user_id]) && $admin_rights[$user_id][0]) {
                $r = array(
                    7,
                    $admin_rights[$user_id][1] ? -1 : $r[1],
                    $admin_rights[$user_id][2] ? -1 : $r[2],  
                );
            }
            $min = min($min, $r[1] == -1 ? 7 : $r[1]);
            switch ($u['U_STATUS']) {
                case User::STATUS_INVITED: 
                    $name = "<i>".$u['C_FULLNAME']." ("._s('invited user, login not created yet').')</i>';
                    break;
                case User::STATUS_LOCKED:
                    $name = '<span style="color:#666">'.$u['C_FULLNAME']." (".$u['U_ID'].') - '._s('login disabled').'</span>';
                    break;
                default:
                    $name = $u['C_FULLNAME']." (".$u['U_ID'].')';
            }
            $result[] = array($user_id, $name, $r);
        }
        $this->smarty->assign('all', $min);
        $this->smarty->assign('users_rights', json_encode($result));
    }
    
    public function getGroupsRights()
    {
        $group_rights_model = new GroupsRightsModel();
        $rights = $group_rights_model->getGroups('CM', Rights::FOLDERS, $this->folder_id);
        
        $admin_rights = $group_rights_model->getGroups('CM', Rights::FUNCTIONS, 'ADMIN');
        
        $groups_model = new GroupsModel();
        $groups = $groups_model->getAll();
        $this->smarty->assign('groups_count', count($groups));
        $min = 7;
        $result = array();
        foreach ($groups as $g) {
            $r = isset($rights[$g['UG_ID']]) ? array($rights[$g['UG_ID']], $rights[$g['UG_ID']]) : array(0, 0);
            if (isset($admin_rights[$g['UG_ID']]) && $admin_rights[$g['UG_ID']]) {
                $r = array(7, -1);
            }
            $min = min($min, $r[1] == -1 ? 7 : $r[1]);
            $result[] = array($g['UG_ID'], $g['UG_NAME'], $r);
        }
        $this->smarty->assign('all', $min);
        $this->smarty->assign('groups_rights', json_encode($result));
    }
    
    public function prepareData()
    {
       $folders_model = new ContactFolderModel();
       $folder = $folders_model->get($this->folder_id);
       $this->smarty->assign('folder_id', $this->folder_id);
       $this->smarty->assign('folder_title', $folder['NAME']);        
       if ($this->tab == 'groups') {
           $this->getGroupsRights();
       } else {
            $this->getUsersRights();
       }
       
       $this->smarty->assign('folder_id', $this->folder_id);
       $this->smarty->assign('tab_id', $this->tab);
    }
}

?>