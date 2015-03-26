<?php

class STClassesSaveController extends JsonController
{
    public function exec()
    {
        $id = Env::Post('id');
        $name = Env::Post('name');
        $class_model = new STClassModel();
        if (empty($name)){
	        //$sorting_new = Env::Post('sorting_new');
	        //$sorting_old = Env::Post('sorting_old');
            $ids = Env::Post('ids');
	        $ids = explode(',', $ids);
	        foreach ($ids as $key=>$id){
	            if (!empty($id)){
                    $class_model->setSorting($id, $key+1);
	            }
	        }
            //$this->response = $class_model->setSorting($id, $sorting_old, $sorting_new);
        } else {
            $this->response = $class_model->setName($id, $name);
        }
    }
}