<?php

class CMListsCreateAction extends UGViewAction
{

	public function prepareData()
	{
	    User::unsetSetting('LASTLIST', 'CM');
	}
}

?>