<?php

class STUsersListController extends JsonController
{
        
    private function asorti($arr) { 
       $arr2 = $arr; 
       foreach($arr2 as $key => $val) { 
          $arr2[$key] = strtolower($val); 
       } 
      
       asort($arr2); 
       foreach($arr2 as $key => $val) { 
          $arr2[$key] = $arr[$key]; 
       } 
    
       return $arr2; 
    } 
    
	public function exec()
	{
		$text = Env::Get('text', Env::TYPE_STRING, '');
        $with_email = Env::Get('email', Env::TYPE_INT, 1);
        $without_id = Env::Get('noid', Env::TYPE_INT, 0);
		$contacts_model = new ContactsModel();
		$this->response = array();
		// Get All Users which have access to the application
        $user_rights_model = new UserRightsModel();
        $rights = $user_rights_model->getUsers('ST', 'SCREENS', 'RL');
        
        $users_model = new UsersModel();
        $names = $users_model->getNames('U_ID');
        $users = array();
        
        foreach ($rights as $user_id => $r) {
            if (
                $r[0] > 0 
                && 
	            (
	              empty($text) 
	              || 
	              mb_stripos($names[$user_id]['C_FULLNAME'], $text)>-1 
	              || 
	              mb_stripos($names[$user_id]['C_EMAILADDRESS'], $text)>-1
	            )
             ) {
				if (!empty($names[$user_id]['C_EMAILADDRESS'])) {
				    $users[$names[$user_id]['C_ID']] = $names[$user_id]['C_FULLNAME'];
				    $users[$names[$user_id]['C_ID']] .= " <".$names[$user_id]['C_EMAILADDRESS'].">";
				} else {
				    if ($with_email != 1) $users[$names[$user_id]['C_ID']] = $names[$user_id]['C_FULLNAME'];
				}
				if (!empty($users[$names[$user_id]['C_ID']])){
				    $users[$names[$user_id]['C_ID']] = htmlspecialchars($users[$names[$user_id]['C_ID']]);
				}
				
            }
        }
        $users = $this->asorti($users);
        if (!$without_id){
            $this->response = $users;
        } else {
            $new_users = array();
            foreach($users as $user){
                $new_users[] = $user;
            }
            $this->response = $new_users;
        }
	}
	
}