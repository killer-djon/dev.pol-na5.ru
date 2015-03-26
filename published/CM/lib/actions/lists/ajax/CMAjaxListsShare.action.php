<?php 

class CMAjaxListsShareAction extends UGAjaxAction
{
	protected $list_id;
	protected $list_info;
	
	protected $share = 1;
	
	public function __construct()
	{
		parent::__construct();
		$this->list_id = Env::Post('id');
		$this->share = Env::Post('share', Env::TYPE_INT, 1);
		$lists_model = new ListsModel();
		$this->list_info = $lists_model->get($this->list_id);
		if ($this->list_info) {
			$this->share();
		}
	} 
	
	public function share()
	{
		$lists_model = new ListsModel();
		$lists_model->share($this->list_id, $this->share);
	}
}

?>