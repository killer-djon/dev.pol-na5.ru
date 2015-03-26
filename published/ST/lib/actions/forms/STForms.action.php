<?php

class STFormsAction extends Action
{
	public function prepare()
	{
		$widgets_model = new WidgetsModel();
		$delete = Env::Get('delete', Env::TYPE_INT, 0);  
        if ($delete>0){
            $widgets_model->delete($delete);
        }
		$forms = $widgets_model->getByType('ST');
		$this->view->assign('forms', $forms);
	}
}