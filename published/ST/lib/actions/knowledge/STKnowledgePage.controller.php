<?php

class STKnowledgePageController extends JsonController
{
    public function exec()
    {
        $book_id = Env::Post('QPB_ID', Env::TYPE_STRING, '');    
        $page_id = Env::Post('QPF_TEXTID', Env::TYPE_STRING, '');    
        $kb_model = new STQPFolderModel();
        $this->response = $kb_model->getPage($page_id, $book_id);
	}
}

?>