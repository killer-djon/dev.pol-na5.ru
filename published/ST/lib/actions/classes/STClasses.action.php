<?php

class STClassesAction extends Action
{
    public function prepare()
    {
        $class_type_model = new STClassTypeModel();
        $class_model = new STClassModel();
        $data = $class_type_model->getAll();
        $classes = array();
        $classTypes = array();
        foreach ($data as $class_type) {
            $classes = $class_model->getByClassType($class_type['id']);
            $class_type['classes'] = $classes;
            $classTypes[] = $class_type;
        }
        $this->view->assign('classTypes', $classTypes);
    }
}
