<?php

class STRequestsAddAction extends Action
{
	public function prepare()
	{
        $user_rights_model = new UserRightsModel();
        $rights = $user_rights_model->getUsers('ST', 'SCREENS', 'RL');
        
        $users_model = new UsersModel();
        $names = $users_model->getNames('U_ID');
        $users = array();
        foreach ($rights as $user_id => $r) {
            if ($r[0] > 0) {
                $users[$names[$user_id]['C_ID']] = '"'.$names[$user_id]['C_FULLNAME'].'"'.($names[$user_id]['C_EMAILADDRESS'] ? ' <'.$names[$user_id]['C_EMAILADDRESS'].'>' : ''); 
            }
        }
        $this->view->assign('users', $users);   
        
        $class_type_model = new STClassTypeModel();
        $class_model = new STClassModel();
        $data = $class_type_model->getAll();
        $classes = array();
        $classTypes = array();
        foreach ($data as $class_type) {
            $classes = $class_model->getByClassType($class_type['id']);
            $class_type['classes'] = $classes;
            $class_type['classesLength'] = sizeof($classes);
            $classTypes[] = $class_type;
        }
        $this->view->assign('classTypes', $classTypes);
	}
}

?>