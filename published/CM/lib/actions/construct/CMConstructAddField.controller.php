<?php

class CMConstructAddFieldController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructAddFieldAction();
		} else {
		    $this->layout = 'Popup';
			$this->actions[] = new CMConstructAddFieldAction();
		}
	}
}

?>