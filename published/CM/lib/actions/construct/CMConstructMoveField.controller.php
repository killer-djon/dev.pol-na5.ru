<?php

class CMConstructMoveFieldController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructMoveFieldAction();
		} else {
		    $this->layout = 'Popup';
			$this->actions[] = new CMConstructMoveFieldAction();
		}
	}
}
?>