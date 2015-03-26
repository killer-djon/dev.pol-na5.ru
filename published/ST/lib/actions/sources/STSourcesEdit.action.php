<?php

class STSourcesEditAction extends Action
{

	protected $source_id;
	
	public function __construct()
	{
		parent::__construct();
		$this->title = _("Edit source");
		$this->source_id = Env::Get('id', Env::TYPE_INT, '');	
	}
	
	public function prepare()
	{
		$source_model = new STSourceModel();
		$info = $source_model->get($this->source_id);
		$params = $source_model->getParams($this->source_id);
	    $hostname = Env::Server("HTTP_HOST");
        $this->view->assign('hostname', $hostname);
        /* If hosted - remove @hostname from all emails*/
	    if (isset($params['inner']) && $params['inner']) {
	    	$a = explode('@', $params['email'], 2);
            $params['email'] = $a[0];
            if (isset($params['receipt_email'])) {
		    	$a = explode('@', $params['receipt_email'], 2);
	            $params['receipt_email'] = $a[0];
            } else {
               $params['receipt_email'] = 'noreply';
            }
            if (isset($params['confirm_email'])){
		    	$a = explode('@', $params['confirm_email'], 2);
	            $params['confirm_email'] = $a[0];            	
            }  else {
                $params['confirm_email'] = 'noreply';
            }
        }
             
        if (isset($params['signature'])){
            $params['signature'] = str_replace("<br />","\n", $params['signature']);
        }
        if (isset($params['receipt_body'])){
            $params['receipt_body'] = str_replace("<br />","\n", $params['receipt_body']);
        }
        if (isset($params['confirm_body'])){
            $params['confirm_body'] = str_replace("<br />","\n", $params['confirm_body']);
        }
		
		$this->view->assign('info', $info);
		$this->view->assign('params', $params);
        $this->view->assign('inner', Env::Get('inner',Env::TYPE_INT, 0));
        $this->view->assign('user_lang', User::getLang());
        $this->view->assign('default_email', array('rus'=>User::getSetting('DEFAULT_EMAIL_rus',  'ST', ''),
                                                   'eng'=>User::getSetting('DEFAULT_EMAIL_eng',  'ST', '')));
        $dbfields = ContactType::getFieldsNames(false, false, true);
        $fields = array();
        $my_fields = array();
        $contacts_exists = Wbs::getDbkeyObj()->appExists('CM');
	    foreach ($dbfields as $field => $name) {
		    $my_fields[] = array("name" => "{MY_".substr($field, 2)."}", "descr" => $name);
		    if ($contacts_exists) {
		    	$fields[] = array("name" => "{".substr($field, 2)."}", "descr" => $name);
		    }
        }
        $templates = array(
            "default" => array(
		        array("name" => "{REQUEST_ID}", "descr" => _('Request number')),
                array("name" => "{REQUEST_SUBJECT}", "descr" => _('Request subject')),
                array("name" => "{REQUEST_TEXT}", "descr" => _('Request text')),
		        array("name" => "{REQUEST_SOURCE}", "descr" => _('Email box where request was received')),
		        array("name" => "{REQUEST_HISTORY_CLIENT}", "descr" => _('Request history available for client. Includes original request, reply, reopening and following replies.')),
		        array("name" => "{REQUEST_LIST_URL}", "descr" => _("Link to 'My requests' tab in client's personal cabinet."))
	        ),
            "subscr" => array(
                array("name" => "{REQUEST_CONFIRM_URL}", "descr" => _('Confirmation link')),
                array("name" => "{REQUEST_ID}", "descr" => _('Request number')),
                array("name" => "{REQUEST_SOURCE}", "descr" => _('Email box where request was received')),
                array("name" => "{REQUEST_SUBJECT}", "descr" => _('Request subject')),
                array("name" => "{REQUEST_TEXT}", "descr" => _('Request text'))
            ),
            "fields" => $fields,
            "my_fields" => $my_fields
        );
        $this->view->assign('templates', $templates);
        
        $this->view->assign('ssl_support', in_array('ssl', stream_get_transports()));
	}
}
