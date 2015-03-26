<?php 

class CMFoldersDeleteController extends UGController
{
	public function exec()
	{
		$this->actions[] = new CMAjaxFoldersDeleteAction();
	}
}

?>