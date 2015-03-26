<?php

class STKnowledgeAction extends Action
{
	public function prepare()
	{ 
        $kb_model = new STKnowledgeModel();
        //print_r($kb_model->getBooks());die;
        $this->view->assign('books', $kb_model->getBooks());
	}
}

?>