<?php 

class CMFoldersAddController extends UGController 
{

    public function exec()
    {
        $this->layout = false;
        $this->actions[] = new CMAjaxFoldersAddAction();
    }
}

?>