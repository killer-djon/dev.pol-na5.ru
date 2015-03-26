<?php

class STRequestsSidebarAction extends Action
{
   public function __construct()
    {
        parent::__construct();
        $this->request_id = Env::Get('id', Env::TYPE_INT, 0);
        $this->request  = new STRequest($this->request_id);     
    }
    
        
    public function prepare()
    {
        $request = $this->request->getInfo();
        
        $states_model = new STStateModel();
        $states = $states_model->getAll();
        
		//WbsDateTime::getTime(strtotime($request['datetime']));
		$request['datetime'] = WbsDateTime::getTime(strtotime($request['datetime'])).' ('.STRequest::getDatetimeBySeconds(time() - strtotime($request['datetime']));
		if (!empty($request['assigned_c_id'])){
	       $request['assigned_name'] = Contact::getName($request['assigned_c_id']);
		} else {
		    $request['assigned_name'] = _s('none');
		}
		
        if (!empty($request['state_id'])){
		   $request['state'] =  $states[$request['state_id']]['name'];
        } else {
           $request['state'] = _s('New');
        }
		
        $this->view->assign('request', $request);
        
        $state_action_model = new STStateActionModel();
        $actions = $state_action_model->getByState($request['state_id']);
        print_r($actions);die;
        
        $request_class_model = new STRequestClassModel();
        $classes = $request_class_model->selectClassesByRequest($request['id']);
        $class_types = array();
        $class_type_id = 0;
        foreach ($classes as $class) {
            if ($class_type_id != $class['id']){
                $class_type_id = $class['id'];
                $class_types[$class_type_id]['id'] = $class['id'];
                $class_types[$class_type_id]['multiple'] = $class['multiple'];
                $class_types[$class_type_id]['name'] = $class['name'];
                $class_types[$class_type_id]['classes'] = array();
            }
            $class_types[$class_type_id]['classes'][] = $class;
        }
        $this->view->assign('class_types', $class_types);

    }
}