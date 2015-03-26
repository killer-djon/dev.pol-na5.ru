<?php

class CMConstructAddSectionController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructAddSectionAction();
		} else {
		    $this->layout = 'Popup';
			$this->actions[] = new CMConstructAddSectionAction();
		}
	}
}
?>