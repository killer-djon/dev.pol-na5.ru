<?php

class CMConstructDeleteSectionController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructDeleteSectionAction();
		} else {
		    $this->layout = 'Popup';
		    $this->actions[] = new CMConstructDeleteSectionAction();
		}
	}
}
?>