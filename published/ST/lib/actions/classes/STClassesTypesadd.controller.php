<?php

class STClassesTypesAddController extends JsonController
{
    public function exec()
    {
        $name = Env::Post('name');
        $sorting = Env::Post('sorting', Env::TYPE_STRING);
        $class_type_model = new STClassTypeModel();
        $this->response = $class_type_model->add($name, $sorting);
    }
}