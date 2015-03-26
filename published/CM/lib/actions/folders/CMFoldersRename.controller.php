<?php 

class CMFoldersRenameController extends UGController 
{

    public function exec()
    {
        $this->layout = false;
        $this->actions[] = new CMAjaxFoldersRenameAction();
    }
}

?>