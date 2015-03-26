<?php

class STClassesDeleteclasstypeController extends JsonController
{
    public function exec()
    {
        $id = Env::Post('id', Env::TYPE_INT);
        $link = Env::Post('link', Env::TYPE_INT, false);
        $class_model_type = new STClassTypeModel();
        $class_model = new STClassModel();
        if (!$link) {
            $this->response = $class_model_type->delete($id);
        } else {
            $class_model_type->relinkClasses($id, $link);
            $this->response = $class_model_type->delete($id);
        }
        
    }
}