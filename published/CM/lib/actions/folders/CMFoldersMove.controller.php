<?php 

class CMFoldersMoveController extends UGController 
{

    public function exec()
    {
        $this->layout = false;
        $this->actions[] = new CMAjaxFoldersMoveAction();
    }
}

?>