<?php

class CMConstructFormatController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
			$this->actions[] = new CMAjaxConstructFormatAction();
		} else {
		    $this->layout = 'Popup';
			$this->actions[] = new CMConstructFormatAction();
		}
	}
}
?>