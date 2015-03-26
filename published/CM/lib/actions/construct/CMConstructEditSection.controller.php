<?php

class CMConstructEditSectionController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructEditSectionAction();
		} else {
		    $this->layout = 'Popup';
			$this->actions[] = new CMConstructEditSectionAction();
		}
	}
}
?>