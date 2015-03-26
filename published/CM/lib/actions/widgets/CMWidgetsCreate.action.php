<?php

class CMWidgetsCreateAction extends UGViewAction
{
	
	public function prepareData()
	{
        $this->smarty->assign('folders', Contact::getFolders());
        $this->smarty->assign('types', ContactType::getTypeNames());
	}
}
?>