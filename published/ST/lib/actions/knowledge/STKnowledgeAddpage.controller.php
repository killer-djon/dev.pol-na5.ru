<?php

class STKnowledgeAddpageController extends JsonController
{
    
    public function exec()
    {
        $data = Env::Post('data');
        $qp_model = new STQPFolderModel();
        $this->response = $qp_model->setPage($data);
        
	}
	
}

?>