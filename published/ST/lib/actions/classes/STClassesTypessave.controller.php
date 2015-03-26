<?php

class STClassesTypesSaveController extends JsonController
{
    public function exec()
    {
        $id = Env::Post('id', Env::TYPE_INT);
        $name = Env::Post('name', Env::TYPE_STRING);
        $multiple = Env::Post('multiple', Env::TYPE_STRING);
        if ($multiple == "true") {
            $multiple = 1;
        } else {
            $multiple = 0;
        }
        $class_type_model = new STClassTypeModel();
        if (empty($name)){
            /*$sorting_new = Env::Post('sorting_new');
            $sorting_old = Env::Post('sorting_old');
            $this->response = $class_type_model->setSorting($id, $sorting_old, $sorting_new);*/
            $ids = Env::Post('ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$id){
                if (!empty($id)){
                    $class_type_model->setSorting($id, $key+1);
                }
            }
        } else {
            $this->response = $class_type_model->save($id, $name, $multiple);
        }
    }
}