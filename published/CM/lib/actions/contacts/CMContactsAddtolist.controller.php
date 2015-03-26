<?php

class CMContactsAddtolistController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		$this->actions[] = new CMAjaxContactsAddtolistAction();
	}
}
?>