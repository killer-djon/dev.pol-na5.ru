<?php

class CMContactsMoveController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		$this->actions[] = new CMAjaxContactsMoveAction();
	}
}
?>