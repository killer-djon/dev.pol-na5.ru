<?php

class STSourcesAddController extends ViewController
{
	public function exec()
	{
		if (Env::Get('type') == 'form') {
			$this->invokeAction(new STSourcesAddFormAction());
		} else {
			$this->invokeAction(new STSourcesAddAction());
		}
	}
}

?>