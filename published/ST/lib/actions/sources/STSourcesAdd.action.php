<?php

class STSourcesAddAction extends Action
{
	public function __construct()
	{
		parent::__construct();
		$this->title = _("Add a new source");
		$this->template = "SourcesEdit";
        $this->view->assign('hostname', Env::Server("HTTP_HOST"));
        $this->view->assign('inner', Env::Get('inner',Env::TYPE_INT, 0));
	}
	
	public function prepare()
	{
		$this->view->assign('title', $this->title);
        $this->view->assign('user_lang', User::getLang());
               
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