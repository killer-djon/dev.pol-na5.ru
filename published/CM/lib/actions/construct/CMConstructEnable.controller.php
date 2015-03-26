<?php 

class CMConstructEnableController extends UGController
{
    public function exec()
    {
        $this->layout = false;
        $this->actions[] = new CMAjaxConstructEnableAction();
    }
}

?>