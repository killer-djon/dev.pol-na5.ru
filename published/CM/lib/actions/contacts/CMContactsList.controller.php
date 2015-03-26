<?php

class CMContactsListController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		$this->actions[] = new CMAjaxContactsListAction();
	}
}
?>