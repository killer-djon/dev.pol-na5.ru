<?php

class STFormsSaveController extends JsonController
{
	public function exec()
	{	
		if (Env::Post('add')) {
			$this->add();
		} elseif (Env::Post('id') && Env::Post('save')) {
			$this->save();
		} 
	}
	
	protected function add()
	{
		$name = Env::Post('name', Env::TYPE_STRING, '');
		if (!$name) {
			$name = _s('Noname');
		}
		
		$widgets_model = new WidgetsModel();
		$form_id = $widgets_model->add('ST', 'CUSTOM', $name, User::getLang());
		
		$this->response = array(
			'id' => $form_id,
			'name' => $name
		);
        $source_model = new STSourceModel();
        $all_sources = $source_model->getAll();
        if ($all_sources) {
	        $source = array_shift($all_sources);
            $widgets_model->setParam($form_id, 'WIDTH', 350);
            $widgets_model->setParam($form_id, 'HEIGHT', 350);
	        $widgets_model->setParam($form_id, 'SOURCEID', $source['id']);
        }

		$metric = metric::getInstance();
		$metric->addAction(Wbs::getDbKey(), User::getId(), 'ST', 'ADDFORM', 'ACCOUNT');

	}
	
	protected function save()
	{
		$widget_id = Env::Post('id', Env::TYPE_INT, 0);
        $info = Env::Post('info');
        $params = Env::Post('params');
        $classes = Env::Post('classes');
        $info['WG_DESC'] = Env::Post('form-name', Env::TYPE_STRING, '');
        
        $widget = new SupportForm($widget_id);        
        $old_params = $widget->getParam();
        $def_labels = $widget->getParam('LABELS');

        $labels = Env::Post('labels');
        
        $fields = array();
        foreach ($labels as $key=>$field) {
        	$fields[] = $key . ($labels[$key] == $def_labels[$key] ? "" : '='.$labels[$key]);
        }
        $params['FIELDS'] = implode(";", $fields);
        $params['CLASSES'] = $classes;
        
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
		$delete = array();
        foreach ($widget->param_fields as $name => $value) {
            if (isset($params[$name])) {
            	if ($params[$name] != $old_params[$name]) {
                	$widgets_model->setParam($widget_id, $name, $params[$name]);
            	}
            } elseif ($widget->issetParam($name)) {
                $delete[] = $name;
            }
        }
        if ($delete) {
            $widgets_model->deleteParams($widget_id, $delete);
        }
        
        if (isset($info['WG_LANG'])) {
            $widgets_model->set($widget_id, 'WG_LANG', $info['WG_LANG']);
        }
        
        if (isset($info['WG_DESC']) && $info['WG_DESC']) {
            $widgets_model->set($widget_id, 'WG_DESC', $info['WG_DESC']);
        }
	}
}