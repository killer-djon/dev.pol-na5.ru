<?php

class STContactsFolderlistController extends JsonController
{
	public function exec()
	{
		$rights = new Rights(User::getId());
        $folders = $rights->getFolders('CM', false, true, Rights::FLAG_ARRAY_OFFSET | Rights::FLAG_RIGHTS_INT | Rights::FLAG_NOT_EMPTY);
		$this->response = array();
		foreach ($folders as $row) {
			$this->response[$row['ID']] = $row['OFFSET'].'|'.mb_substr($row['NAME'], 0, 80);
		}
	}
}