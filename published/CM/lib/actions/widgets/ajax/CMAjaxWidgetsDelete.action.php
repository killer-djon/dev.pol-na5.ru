<?php

class CMAjaxWidgetsDeleteAction extends UGAjaxAction
{
    protected $widget_id;
    public function __construct()
    {
        $this->widget_id = Env::Post('id', Env::TYPE_INT);
        if ($this->widget_id) {
            $this->delete();
        }
    }
    
    public function delete()
    {
        $widgets_model = new WidgetsModel();
        $widgets_model->delete($this->widget_id);
    }
}

?>