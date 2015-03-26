<?php

class CMConstructEditFieldController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructEditFieldAction();
		} else {
		    $this->layout = 'Popup';
			$this->actions[] = new CMConstructEditFieldAction();
		}
	}
}
?>