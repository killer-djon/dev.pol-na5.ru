<?php 

class CMAjaxWidgetsCreateAction extends UGAjaxAction
{

	const WIDGET_TYPE = "SBSC";
	
	public function __construct()
	{
		if (Env::Post('create')) {
			$this->createWidget();
		}			
	}
	
	public function createWidget()
	{
		$name = Env::Post('name', Env::TYPE_STRING, "");
		$folder_id = Env::Post('folder_id');
		$widgets_model = new WidgetsModel();
		$widget_id = $widgets_model->add(self::WIDGET_TYPE, "CUSTOM", $name, User::getLang());
		if ($folder_id) {
			$widgets_model->setParam($widget_id, 'FOLDER', $folder_id);
		}
		
		$type_id = Env::Post('type', Env::TYPE_INT, 1);
		$widgets_model->setParam($widget_id, 'CT_ID', $type_id);
		switch ($type_id) {
		    case 1:
		        $fields = array('C_FULLNAME', 'C_COMPANY', 'C_EMAILADDRESS');
		        break;
		    case 2: 
		        $fields = array('C_COMPANY', 'C_EMAILADDRESS');
		        break;		        
		}
		$widgets_model->setParam($widget_id, 'CMFIELDS', implode(",", $fields));
		$this->response = array(
			'id' => $widget_id,
			'enc_id' => base64_encode($widget_id),
			'name' => htmlspecialchars($name)
		);
	}
	
}

?>