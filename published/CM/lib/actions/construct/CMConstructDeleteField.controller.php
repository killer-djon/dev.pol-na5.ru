<?php

class CMConstructDeleteFieldController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructDeleteFieldAction();
		} else {
		    $this->layout = 'Popup';
		    $this->actions[] = new CMConstructDeleteFieldAction();
		}
	}
}
?>