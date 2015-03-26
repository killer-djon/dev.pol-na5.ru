<?php

class STSettingsAction extends Action
{
	public function prepare()
	{
		$settings = array();
		$settings['manual'] = User::getSetting('manual', 'ST', '');
		$this->view->assign('settings', $settings);
	}
}