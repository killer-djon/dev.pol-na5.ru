<?php

class STClassesDeleteController extends JsonController
{
    public function exec()
    {
        $id = Env::Post('id', Env::TYPE_INT);
        $link = Env::Post('link', Env::TYPE_INT, false);
        $class_model = new STClassModel();
        if (!$link) {
            $this->response = $class_model->delete($id);
        } else {
            $requests_model = new STRequestClassModel();
            $requests_model->replaceClass($id, $link);
            $this->response = $class_model->delete($id);
        }
        
    }
}