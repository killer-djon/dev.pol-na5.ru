<?php

class CMConstructMoveSectionController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructMoveSectionAction();
		} else {
		    $this->layout = 'Popup';
			$this->actions[] = new CMConstructMoveSectionAction();
		}
	}
}
?>