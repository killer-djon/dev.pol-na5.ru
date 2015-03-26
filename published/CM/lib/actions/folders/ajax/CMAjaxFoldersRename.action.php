<?php 

class CMAjaxFoldersRenameAction extends UGAjaxAction 
{

    public function prepareData()
    {
        
        $folders_model = new ContactFolderModel();
        $folder_id = Env::Post('id', Env::TYPE_STRING);
        $name = Env::Post('newName', Env::TYPE_STRING, "");
        if ($folder_id && $name) {
            $folder_id = $folders_model->rename($folder_id, $name);
        }

	    $this->response = $name;
    }
    
}

?>