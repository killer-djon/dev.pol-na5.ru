<?php
class STRequestsInfoAction extends Action
{
	protected $request_id;
	/**
	 * @var STrequest
	 */
	protected $request;
	protected $request_info;
	protected $request_time;
	
	protected $states;
	
	public function __construct()
	{
		parent::__construct();
		$this->request_id = Env::Get('id', Env::TYPE_INT, 0);
		$this->request  = new STRequest($this->request_id);

        $states_model = new STStateModel();
        $this->states = $states_model->getAll();
		
	}
	
	public function prepareRequest()
	{
		$request_model = new STRequestModel();
		$this->request_info = $this->request->getInfo();
		// Set read
		if ($this->request_info['assigned_c_id'] == User::getContactId()) {
			$request_model->setRead($this->request_id, 1);
		}
		// Get client name
		if ($this->request_info['client_c_id']) {
	        $this->request_info['from_name'] = Contact::getName($this->request_info['client_c_id']);
	        // Count other requests
	        $client_requests_cnt = $request_model->countAll(array(), 'clientid|'.$this->request_info['client_c_id']); 
    	    $this->view->assign('client_requests_cnt', $client_requests_cnt);        
        }
        // Get assigned name
        if ($this->request_info['assigned_c_id']){
            $this->request_info['assigned_name'] = Contact::getName($this->request_info['assigned_c_id']);
        } else {
            $this->request_info['assigned_name'] = _s('none');
        }
		// Get time        
		$this->request_time = $this->request_info['datetime'];
        $this->request_info['datetime'] = WbsDateTime::getTime(strtotime($this->request_info['datetime']));
        $this->request_info['countdown'] = STRequest::getDatetimeBySeconds(time() - strtotime($this->request_info['datetime']));
        // Get source
        if ($this->request_info['source_type'] == 'form') {
            $widgets_model = new WidgetsModel();
            $form = $widgets_model->getById($this->request_info['source']);
            $this->request_info['form_name'] = $form['WG_DESC'];
        } elseif ($this->request_info['source_type'] == 'user'){
            $this->request_info['source'] = Contact::getName($this->request_info['source']);
        }

        // Escape subject
        $this->request_info['subject'] = htmlspecialchars($this->request_info['subject'], ENT_QUOTES);        

        // Get state
        if (isset($this->states[$this->request_info['state_id']])){
           $this->request_info['state'] =  $this->states[$this->request_info['state_id']]['name'];
        } else {
           $this->request_info['state'] = _s('Unknown');
        }
        
        // @todo: Replace 2 to new state
        $this->view->assign('request_color', $this->getColor(2, true, 'rgb(255,255,255)'));
        $this->view->assign('current_state_color', $this->getColor($this->request_info['state_id'], false, 'rgb(0,0,0)'));        
		
        $this->view->assign('request', $this->request_info);
	}
	
	public function getColor($state_id, $rgba = false, $color = '')
	{
        if (isset($this->states[$state_id]['properties']->css)){
	        $color = $this->states[$state_id]['properties']->css;
	        $color = substr($color, strpos($color,'color:') + 6);
	        if (strpos($color, ';') > 0) {
	        	$color = substr($color, 0, strpos($color, ';'));	
	        }
	        if ($rgba) {
		        $color = str_replace("rgb","rgba",$color);
		        $color = str_replace(")",",0.15)",$color);
	        }
        } 
		return $color;
	}
	
	public function prepareClasses()
	{
        $state_action_model = new STStateActionModel();
        $allowed_class_change = $state_action_model->getByStateType($this->request_info['state_id'],'CLASSIFY');

	    $request_class_model = new STRequestClassModel();
        $classes = $request_class_model->selectClassesByRequest($this->request_info['id']);
        $class_types = array();
        $class_type_id = 0;
        foreach ($classes as $class) {
            if ($class_type_id != $class['id']){
                $class_type_id = $class['id'];
                $class_types[$class_type_id]['id'] = $class['id'];
                $class_types[$class_type_id]['name'] = $class['name'];
                $class_types[$class_type_id]['classes'] = array();
            }
            $class_types[$class_type_id]['classes'][] = $class['class_name'];
        }
        foreach ($class_types as &$class_type) {
            $class_type['classes'] = implode(', ',$class_type['classes']);
        }
        $this->view->assign('class_types', $class_types);
        $this->view->assign('allowed_class_change', true);
	}
	
	public function prepare()
	{
        $this->view->assign('states', $this->states);
        $this->view->assign('user_info', User::getInfo());
        	
        $action_model = new STActionModel();
        $all_actions = $action_model->getAll();
        $this->view->assign('all_actions', $all_actions);

        $this->prepareRequest();
        $this->prepareClasses();
        
        if ($this->request_info['client_c_id']) {
	        $contact_info = $this->getContactInfo();
	        $this->view->assign('contact_info', $contact_info['html']);
	        $this->view->assign('contact_photo', $contact_info['photo']);
        }
		// Plugins
        $params = array(
        	'contact_id' => $this->request_info['client_c_id']
        );
        $contact_info_plugins  = STPlugins::getInstance()->getBlock('contact_info', $params);
        $this->view->assign('contact_info_plugins', $contact_info_plugins);
        $top_plugins = STPlugins::getInstance()->getBlock('top', $params);
        $this->view->assign('top_plugins', $top_plugins);   
        $sidebar_plugins = STPlugins::getInstance()->getBlock('sidebar', $params);
        $this->view->assign('sidebar_plugins', $sidebar_plugins);   
        
        $this->view->assign('user_is_admin', User::hasAccess('ST','FUNCTIONS','ADMIN'), View::TYPE_JSON);
        
        
		// Get log
        $order = User::getSetting('LOG_ORDER', 'ST');
        if (!$order) $order = 'ASC';
        $this->view->assign('log_order', $order);
        
		$request_log_model = new STRequestLogModel();
		$log = $request_log_model->getByRequest($this->request_id, false, false, false, '', $order);
		$this->request_info['datetime'] = $this->request_time;
		$this->view->assign('log', STRequest::prepareLogs($log, $this->request_info));

		if ($log) {
		    $last_id = $order == 'ASC' ? array_pop($log) : array_shift($log);
			$this->view->assign('last_req_log_id', $last_id['id']);		    
		}                    
    }
    
    public function getContactInfo()
    {
    	$result = array('html' => '', 'photo' => '');
        $contact_info = Contact::getInfo($this->request_info['client_c_id']);
        $contact_type = new ContactType($contact_info['CT_ID']);
        $photo_field = $contact_type->getPhotoField(true, true);
        $result['photo'] = $contact_info[$photo_field] ? $contact_info[$photo_field] . "&size=96" : Url::get("/UG/img/empty-contact" . $contact_info['CT_ID'] . ".gif");
    	
        if (User::hasAccess('CM', Rights::FOLDERS, $contact_info['CF_ID']) ||
            in_array($contact_info['APP_ID'], array('ST','MT'))) {   

	        $all_fields = ContactType::getAllFields(User::getLang(), false, false, true); 
	        $dbfields = array();
	        foreach ($all_fields as $id => $field) {
	            if ($field['type'] != 'SECTION') {
	                $dbfields[$field['dbname']] = $field['type'];
	            }
	        }
	        $main_fields = $contact_type->getMainFields();
	        $main_section = $contact_type->getMainSection();
	        $list_sections = array();
	        $type = $contact_type->getType(User::getLang());
	        foreach ($type['fields'] as $section) {
	            if ($section['id'] != $main_section) {
	                $list_sections[$section['id']] = array('name' => $section['name'], 'fields' => array());
	            }
	            foreach ($section['fields'] as $field) {
	                if ($field['type'] == 'IMAGE') {
	                    continue;
	                }
	                if ($section['id'] == $main_section && !in_array($field['id'], $main_fields)) {
	                    $list_sections[$section['id']."_".$field['id']] = array(
	                       'name' => $field['name'], 
	                       'fields' => array(
	                           array($field['dbname'], $field['name']) 
	                        ) 
	                    );
	                } else if ($section['id'] != $main_section) {
	                    $list_sections[$section['id']]['fields'][] = array($field['dbname'], $field['name']);       
	                }
	            }   
	        }
	        $type_sections = array_values($list_sections);
	                    	
	        $html = "<div class='small-gray'>";
	        foreach ($type_sections as $section) {    
                $section_content = "";
                foreach ($section['fields'] as $field) {    
                    if (!empty($contact_info[$field[0]])) {
                        if (!empty($section_content)) {
                            $section_content .= ', ';
                        }
                        
                        $v = $contact_info[$field[0]];
                        if ($field[0] == 'C_EMAILADDRESS') {
                            $es = $v;
                            $res = array();
                            foreach ($es as $e) {
                                if (!empty($e)) {
                                    $res[] = $e;
                                }
                            }
                            $v = implode(', ', $res);
                        } elseif ($dbfields[$field[0]] == 'URL') {
                            $v = '<a target="_blank" href="' . $v . '">' . $v . '</a>'; 
                        }
                            $section_content .= '<span class="field_title" title="' . $field[1] . '">' . $v . '</span>';
                    }
                }
                if (!empty($section_content)) {
                    $html .= '<span class="section_title">' . $section['name'] . ': </span>' . $section_content . '<br />';
                }
            }
            $html .= '<span class="section_title">'. _('Contact Id') .': </span><span class="field_title">' . $this->request_info['client_c_id'] . '</span><br />';
            $html .= "</div>";
	            
	        $result['html'] = $html;
    	}
    	return $result;
    }
}