<?php

class STTemplate
{
	protected $template;
	protected $request_info;
	protected $contact_info;
	protected $my_contact_info;
	protected $source_info;
	protected $params;
	
	public function __construct($template = false)
	{
		$this->template = $template;
		$this->my_contact_info = User::getInfo();
	}
	
	
	public function setTemplate($template)
	{
		$this->template = $template;
	}
	
	public function setRequest($request_info, $source_info = false)
	{
		$this->request_info = $request_info;
		if ($source_info) {
			$this->source_info;
		}
	}
	
	public function setContact($contact_info)
	{
		$this->contact_info = $contact_info;
	}
	
	
	public function get($template = false)
	{
		if ($template) {
			$this->setTemplate($template);
		}
		$text = $this->compileRequest($this->template);
		$text = $this->compileContact($text);
		if ($this->params) {
			foreach ($this->params as $name => $value) {
				$text = str_replace('{'.$name.'}', $value, $text);
			}
		}
		
		return $text;
	}
	
	protected function getRequest($name) 
	{
		$result = isset($this->request_info[$name]) ? $this->request_info[$name] : "";
		if ($name == 'text') {
			$result = STRequest::formatHTML($result);
		}
		return $result; 
	}
	
	protected function getSource($name) 
	{
		if (!$this->source_info && $source_id = $this->getRequest('source_id')) {
			$source_model = new STSourceModel();
			$this->source_info = $source_model->get($source_id);
		}
		return isset($this->source_info[$name]) ? $this->source_info[$name] : ""; 
	}

	protected function getContact($name, $my = false) 
	{
		if ($my) {
			$result = isset($this->my_contact_info[$name]) ? $this->my_contact_info[$name] : "";	
		} else {
			$result = isset($this->contact_info[$name]) ? $this->contact_info[$name] : "";
		}
		if (is_array($result)) {
			$result = array_shift($result);
		}
		return $result; 
	}		
	
	protected function compileRequest($text)
	{
		$text = str_replace('{REQUEST_ID}', $this->getRequest('id'), $text);
		$text = str_replace('{REQUEST_SUBJECT}', $this->getRequest('subject'), $text);
		
		$text = str_replace('{REQUEST_TEXT}', $this->getRequest('text'), $text);
		$text = str_replace('{REQUEST_SOURCE}', $this->getSource('name'), $text);
		if (strpos($text, '{REQUEST_HISTORY_CLIENT}') !== false) {
			$text = str_replace('{REQUEST_HISTORY_CLIENT}', "<blockquote type='CITE' style='border-left:1px solid blue; margin-left:0; padding-left:20px;'>".$this->getRequestHistory()."</blockquote>", $text);
		}
		$request_url = Url::get('/ST/?m=requests&act=info&id='.$this->getRequest('id'), true);
		$text = preg_replace('/(href[\s]*=[\s]*["\']){REQUEST_URL}/uis', "$1".$request_url, $text);
		$text = preg_replace('/(<a[^>]*>){REQUEST_URL}/uis', "$1".$request_url, $text);
		$text = str_replace('{REQUEST_URL}', "<a href='".$request_url."'>".$request_url."</a>", $text);
		$request = new STRequest(false, $this->request_info);
		$text = preg_replace('/(href[\s]*=[\s]*["\']){REQUEST_CONFIRM_URL}/uis', "$1".$request->getConfirmURL(), $text);
		$text = preg_replace('/(<a[^>]*>){REQUEST_CONFIRM_URL}/uis', "$1".$request->getConfirmURL(), $text);
		$text = str_replace('{REQUEST_CONFIRM_URL}', '<a href="'.$request->getConfirmURL().'">'.$request->getConfirmURL()."</a>", $text);
		return $text;
	}
	
	public function getRequestHistory()
	{
        $action_model = new STActionModel();
        $all_actions = $action_model->getAll();
      
        $client_actions= array();
        foreach ($all_actions as $action){
            if ($action['type'] == "EMAIL-CLIENT") {
            	$client_actions[] = $action['id'];
            }
        }
        $request_log_model = new STRequestLogModel();
        $log = $request_log_model->getLastByAction($this->request_info['id'], $client_actions);
        if ($log) {
			return STRequest::formatHTML($log['text']);
        } else {
        	return $this->getRequest('text');
        }
	}
	
	public function setParams($params)
	{
		foreach ($params as $name => $value) {
			$this->params[$name] = $value;
		}
	}
	
	protected function compileContact($text)
	{
		$dbfields = ContactType::getDbFields();
		$fields = array('NAME' => 'C_FULLNAME');
		foreach ($dbfields as $field) {
			$fields[substr($field, 2)] = $field;
		}
		foreach ($fields as $field => $dbfield) {
			$text = str_replace('{'.$field.'}', $this->getContact($dbfield), $text);
			$text = str_replace('{MY_'.$field.'}', $this->getContact($dbfield, true), $text);
		}
		if (isset($this->contact_info['C_ID'])) {
			$url = Contact::getSubscribeLink($this->contact_info['C_ID'])."&t=requests";
			$text = preg_replace('/(href[\s]*=[\s]*["\']){REQUEST_LIST_URL}/uis', "$1".$url, $text);
			$text = preg_replace('/(<a[^>]*>){REQUEST_LIST_URL}/uis', "$1".$url, $text);			
			$text = str_replace('{REQUEST_LIST_URL}', '<a href="'.$url.'">'.$url.'</a>', $text);
		} else {
			$text = str_replace('{REQUEST_LIST_URL}', '', $text);
		}
		
		return $text;
	}
	
}

?>