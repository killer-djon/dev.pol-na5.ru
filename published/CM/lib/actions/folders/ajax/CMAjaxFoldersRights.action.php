<?php

/**
 * Getting, saving and deleting of the rights of users and groups by ajax
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id: UGAjaxUsersRights.action.php 4438 2009-04-21 09:17:15Z alexmuz $
 */
class CMAjaxFoldersRightsAction extends UGAjaxAction
{
	protected $id;
	protected $object_id;
	protected $app_id = 'CM';
	protected $section = 'FOLDERS';

	public function __construct()
	{
		$this->id = Env::Post('id');
		$this->object_id = Env::Post('object_id');
		$this->mode = Env::Post('mode');
		if ($this->mode != 'groups') {
		    $this->mode = 'users';
		}
        if (Env::Post('action') == 'save') {
			$this->save();
		}				
	}
	
	public function save()
	{
		$value = Env::Post('value', Env::TYPE_INT, 0);
		if ($this->mode == 'users') {
		    $model = new UserRightsModel();
		} else {
		    $model = new GroupsRightsModel();
		}		
		$path = '/ROOT/'.$this->app_id.'/'.$this->section;
		if ($this->id == 'ALL') {
		    if ($this->mode == 'users') {
		        $users_model = new UsersModel();
		        $ids = $users_model->getAllId();
		    } else {
		        $groups_model = new GroupsModel();
		        $ids = $groups_model->getAllId();
		    }
		    foreach ($ids as $id) {
		        $model->save($id, $path, $this->object_id, $value, Env::Post('max'));
		    }
		} else {
		    $model->save($this->id, $path, $this->object_id, $value);
		}
	}
}

?>