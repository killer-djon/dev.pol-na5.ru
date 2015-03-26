<?php

class STClassesAddController extends JsonController
{
    public function exec()
    {
        $name = Env::Post('name');
        $type_id = Env::Post('type_id');
        $sorting = Env::Post('sorting');
        $class_model = new STClassModel();
        
        $this->response = $class_model->add($type_id, $name, $sorting);
        
    }
}