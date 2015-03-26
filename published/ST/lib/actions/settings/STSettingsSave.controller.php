<?php

class STSettingsSaveController extends JsonController
{
	
	public function exec()
	{
		$name = Env::Post('name');
		if ($name) {
			User::setSetting($name, Env::Post('value'), 'ST', '');
		}
	}
}

?>