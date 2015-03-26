<?php

class STKnowledgeBookController extends JsonController
{
    public function exec()
    {
        $book_id = Env::Post('id', Env::TYPE_STRING, '');    
        $kb_model = new STQPFolderModel();
        $this->response = $kb_model->getBookPages($book_id);
	}
}

?>