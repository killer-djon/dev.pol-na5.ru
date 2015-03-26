<?php

class SupportForm extends Widget
{
	protected $type = 'ST';
	
	public $param_fields = array (
		"FIELDS" => array("default" => false),
		"CLASSES" => array("default" => false),
	
		"TITLE" => array("default" => "Support form", "gettext" => true),
		"SAVEBUTTON" => array ("default" => "Submit", "gettext" => true),
		    
		"WIDTH" => array("default" => 350, "min" => 100, "max" => 800),
		"TITLEBGCOLOR" => array ("default" => "#999999"),
		"TITLECOLOR" => array ("default" => "#FFFFFF"),
		"BGCOLOR" => array ("default" => "#F0F0F0"),	
	    
		"EMAILSEND" => array ("default" => false),	
		"AFTERTEXT" => array ("rows" => 3, "default" => "Thank you!", "gettext" => true),
		"REDIRECT" => array ("default" => ""),
	    "NEWWINDOW" => array("default" => false),		
		/*"EMAILFROMNAME" => array(),
		"EMAILFROM" => array(),*/
        "SOURCEID" => array(),
	    "EMAILSUBJECT" => array("default" => "Please confirm your request", "gettext" => true),
		"EMAILTEXT" => array ("rows" => 3, "default" => '<p>Verification link:<br />{REQUEST_CONFIRM_URL}</p>', "gettext" => true),  
	);
	
	protected function getLabels() 
	{
		GetText::load($this->getInfo('WG_LANG'), SYSTEM_PATH . "/locale", 'system', false);
		$labels = array(
			'name' => _s('Your name'),
			'email' => _s('Email'),
			'summary' => _s('Summary'),
			'text' => _s('Text'),
			'captcha' => _s('Type these numbers')
		);
		GetText::load(User::getLang(), SYSTEM_PATH . "/locale", 'system', false);
		return $labels;
	} 

	public function getParam($name = false, $default = false)
	{
		if ($name == 'LABELS') {
			return $this->getLabels();
		} elseif ($name == 'FIELDS') {
			if (!isset($this->params[$name])) {
				return array();
			}
			$param = $this->params[$name];
			$param = explode(";", $param);
			$fields = array();
			$labels = $this->getLabels();
			foreach ($param as $part) {
				$part = explode("=", $part, 2);
				if (!empty($part[0]))
				    $fields[$part[0]] = isset($part[1]) ? $part[1] : $labels[$part[0]];
			}
			return $fields;
		}		
		return parent::getParam($name, $default);
	}
	
    public function getSrc($params = "")
    {
        $q = base64_encode(Wbs::getDbKey()) . "-" . $this->data["WG_FPRINT"];
		$src= Url::get("/WG/widget.php?q=".$q, true) ;
		if ($params) {
			$src .= "&" . $params;
		}
		return $src;
	}	
	
	public function getEmbedInfo()
	{
		$info = parent::getEmbedInfo();
		$controller = new STWidgetController($this->data['WG_ID']);
		$info['html'] = $controller->display(true);
		return $info;
	}

}