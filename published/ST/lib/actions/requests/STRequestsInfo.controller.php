<?php

class STRequestsInfoController extends ViewController
{
	public function exec()
	{
        //$full = Env::Get('full', false);
        $full = false;
        $withoutHeader = Env::Get('h', false);
        
		if (!$this->isAjax()) {
			$this->setLayout(new STRequestSingleLayout());
		//	$full = true;
		} else {
			$this->setLayout(new STRequestLayout());
		}
		if ($full) {
			$this->invokeAction(new STRequestsTabsAction(), null, 'tabs');
		} else {
			$decorator = null;
		}
		if (!$withoutHeader){
		  $this->invokeAction(new STRequestsHeaderAction($full), null, 'header');
		}
		$this->invokeAction(new STRequestsInfoAction($full));
	}
}