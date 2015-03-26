<?php 

class CMContactsMultieditAction extends UGViewAction
{
	
	public function __construct()
	{
		parent::__construct();
	}
		
	
    public function prepareData()
    {	
	    $this->smarty->assign('contacts', Env::Get('contacts'));
	    $this->smarty->assign('f', Env::Get('f'));
	    
		$date_format = mb_strtolower(Wbs::getDbkeyObj()->getDateFormat());
		$this->smarty->assign("dateFormat", mb_substr($date_format, 0, -2));
    }
	 
}

?>