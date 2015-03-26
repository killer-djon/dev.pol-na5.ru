<?php

	class PDSmarty extends WbsSmarty
	{
		const MODE_FRONTEND = 'MODE_FRONTEND';
		const MODE_BACKEND = 'MODE_BACKEND';
		
		private $viewMode = 'MODE_BACKEND';
		private $lang = null;
		
		public function __construct($mode = PDSmarty::MODE_BACKEND) 
		{
            $lang = Env::Get('lang', Env::TYPE_STRING, User::getLang());
            $lang = mb_substr($lang, 0, 2);
            $this->lang = $lang;		
			parent::__construct(AppPath::APP_PATH('PD')."/templates", 'PD', $lang);	

            $this->setViewMode($mode);
            			
			$this->assign ("p", $this);
			$this->assign ("self", $this);
		}

		public function display( $template, $cache_id = null, $compile_id = null )
		{
			parent::display( $template, $cache_id, $compile_id );			
		}
		
		public function setViewMode($mode)
		{
		    $this->viewMode = $mode;
		    if ($mode == self::MODE_BACKEND) {
		    	$this->template_dir .= "/backend";            	
		    }
		    else {
		    	$this->template_dir .= "/frontend";
		    }
		}
		
		public function getCommonUrl()
		{
			return Url::get('/common');		
		}
		
		public function getCssUrl()
		{			
			return Url::get('/PD/css');		
		}
		public function getJsUrl()
		{
		    if (defined("USE_LOCALIZATION") && USE_LOCALIZATION) {
		        return Url::get('/PD/js/'.$this->lang);
		    }
		    else {
		        return Url::get('/PD/js/source');
		    }
					
		}
		
		public function getJsFrontUrl() {
		    if (defined("USE_LOCALIZATION") && USE_LOCALIZATION) {
		        return Url::get("/PD/templates/".$this->lang."/frontend");
		    }
		    else {
		        return Url::get("/PD/templates/frontend");
		    }
		}
		
		public function getSwfUploadUrl()
		{
            return Url::get('/PD/css');
		}
		
		public function getImageUrl()
		{
			return Url::get('/PD/img');
		}
		
	}
	
	function renderURL($url, $request= '') 
	{
		if ( Wbs::getSystemObj()->isModeRewrite() ) {
				$url = str_replace('&', '/', $url);
				$url = str_replace('=', '/', $url);
				$url = str_replace('?', '', $url);
		}
		print $url;
	}
		
?>