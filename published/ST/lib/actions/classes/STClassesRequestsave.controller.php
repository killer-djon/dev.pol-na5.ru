<?php

class STClassesRequestsaveController extends JsonController
{
    public function exec()
    {
        $id = Env::Get('id', Env::TYPE_INT);
        $classes = Env::Get('classes');
        $requests_class_model = new STRequestClassModel();
        $requests_class_model->add($id, $classes, true);
        
        $classes = $requests_class_model->selectClassesByRequest($id);
        $class_types = array();
        $class_type_id = 0;
        $all_classes = array();
        foreach ($classes as $class) {
            $all_classes[] = $class['name'].": ".$class['class_name'];
            if ($class_type_id != $class['id']){
                $class_type_id = $class['id'];
                $class_types[$class_type_id]['id'] = $class['id'];
                $class_types[$class_type_id]['multiple'] = $class['multiple'];
                $class_types[$class_type_id]['name'] = $class['name'];
                $class_types[$class_type_id]['classes'] = array();
            }
            $class_types[$class_type_id]['classes'][] = $class;
        }
        $all_classes = implode('<br/> ', $all_classes);
        $this->response['classes'] = $class_types;
        
        
        $request_model = new STRequestModel();
        $this->request_info = $request_model->get($id);
        
        $action_model = new STActionModel();
        $action = $action_model->getByType('CLASSIFY');
        $this->action_id = $action['id'];
        
        if (!$action['state_id']) {
            // State of the request does not change.
            $action['state_id'] = $this->request_info['state_id'];
        }
        if ($action['assigned_c_id'] == -1) {
            $assigned = Env::Post('assigned', Env::TYPE_INT, 0);    
        } elseif ($action['assigned_c_id']) {
            $assigned = $action['assigned_c_id'];
        } else {
            $assigned = $this->request_info['assigned_c_id'];
        }
        
        $this->response['type'] = $action['type'];
        
        $info = array();

        $info['actor_c_id'] = User::getContactId();
        $info['state_id'] = $action['state_id'];
        $info['action_id'] = $this->action_id;
        $info['assigned_c_id'] = $assigned;
        $info['text'] = $all_classes;
        
        $request_log_model = new STRequestLogModel();
        $request_log_model->add($id, $info);
        
        $info['log_name'] = $action['log_name'];
        $info['datetime'] = WbsDateTime::getTime(time());
        $info['contact'] = Contact::getName($info['actor_c_id']);
        $this->response['info'] = $info;
    }
}