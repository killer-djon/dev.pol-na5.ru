<?php

class STClassesListController extends JsonController
{
    public function exec()
    {
        $id = Env::Get('id');
        
        $request_class_model = new STRequestClassModel();
        $requestClasses = $request_class_model->selectClassesByRequest($id);
        
        $class_type_model = new STClassTypeModel();
        $class_model = new STClassModel();
        $data = $class_type_model->getAll();
        $classes = array();
        $classTypes = array();
        foreach ($data as $class_type) {
            $classes = $class_model->getByClassType($class_type['id']);
            foreach($classes as &$class){
                $class['selected'] = 0;
                foreach($requestClasses as $reqClass){
                    if ($reqClass['class_id'] == $class['id']) $class['selected'] = 1;
                }
            }
            $class_type['classes'] = $classes;
            $classTypes[] = $class_type;
        }
        $this->response = $classTypes;
    }
}