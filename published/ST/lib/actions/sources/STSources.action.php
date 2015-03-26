<?php

class STSourcesAction extends Action
{
	
	public function prepare()
	{
		$source_model = new STSourceModel();
        $delete = Env::Get('delete', Env::TYPE_INT, 0);  
        if ($delete>0){
            $source_model->delete($delete);
        } 
		$data = $source_model->getAllWithEmail();
		$sources = array();
		foreach ($data as $source) {
		    $source['language'] = $source_model->getParams($source['id'],'language');
			$sources[$source['type']][] = $source;
		}
		$this->view->assign('sources', $sources);
        $this->view->assign('wahost', Wbs::isHosted());
        $this->view->assign('hostname', Env::Server("HTTP_HOST"));
        $this->view->assign('default_email', array('rus'=>User::getSetting('DEFAULT_EMAIL_rus',  'ST', ''),
                                                   'eng'=>User::getSetting('DEFAULT_EMAIL_eng',  'ST', '')));
	}
}