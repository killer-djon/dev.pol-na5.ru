<?php

class STClassesTypesDeleteController extends JsonController
{
    public function exec()
    {
        $id = Env::Get('id');
        $class_type_model = new STClassTypeModel();
        $class_type_model->delete($id);
    }
}