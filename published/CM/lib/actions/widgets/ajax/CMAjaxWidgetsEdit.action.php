<?php

class CMAjaxWidgetsEditAction extends UGAjaxAction
{
    protected $widget_id;
    
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = Env::Get('id');
        if ($this->widget_id && Env::Post('save')) {
            $this->save();
        }
    }
    
    public function save()
    {
        $info = Env::Post('info');
        $params = Env::Post('params');
        
        if (isset($params['REDIRECT']) && $params['REDIRECT']) {
			$url = parse_url($params['REDIRECT']);
			$scheme = isset($url['scheme']) ? $url['scheme'] : 'http';
			if (isset($url['host'])) {
				$host = $url['host'];
				$path = isset($url['path']) ? $url['path'] : '/';
			} elseif (isset($url['path'])) {
				$urls = explode("/", $url['path'], 2);
				$host = $urls[0];
				$path = isset($urls[1]) ? "/".$urls[1] : "/";
			} 
			if (preg_match("/^[a-z0-9\._-]{1,30}\.[a-z]{2,4}$/ui", $host, $matches)) {
				$query = isset($url['query']) ? "?" . $url['query'] : '';
			    $fragment = isset($url['fragment']) ? "#" . $url['fragment'] : ""; 
			    $params['REDIRECT'] = $scheme."://".$host.$path.$query.$fragment;
			} else {
			    unset($params['REDIRECT']);
			}
        }

		$widgets_model = new WidgetsModel();

        $widget = new ContactWidget($this->widget_id);        
        $delete = array();
        $old_params = $widget->getParam();
        foreach ($widget->param_fields as $name => $value) {
            if (isset($params[$name])) {
            	if ($params[$name] != $old_params[$name]) {
                	$widgets_model->setParam($this->widget_id, $name, $params[$name]);
            	}
            } elseif ($widget->issetParam($name)) {
                $delete[] = $name;
            }
        }
        if ($delete) {
            $widgets_model->deleteParams($this->widget_id, $delete);
        }
        
        if (isset($info['WG_LANG'])) {
            $widgets_model->set($this->widget_id, 'WG_LANG', $info['WG_LANG']);
        }
        
        if (isset($info['WG_DESC']) && $info['WG_DESC']) {
            $widgets_model->set($this->widget_id, 'WG_DESC', $info['WG_DESC']);
        }
        
        if (isset($params['CMFIELDS']) && $params['CMFIELDS']) {
            $widgets_model->set($this->widget_id, 'WST_ID', 'CUSTOM');
        }        
        
        $widget->load();
        $embed = $widget->getEmbedInfo();
        $this->response['code'] = $embed['html_code'];
    }
}
?>