<?php

class CMWidgetsEditAction extends UGViewAction
{
    protected $widget_id;
    
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = Env::Get('id', Env::TYPE_INT, 0);
    }
    
    public function prepareData()
    {
        $widget = new ContactWidget($this->widget_id);
        User::setSetting('LASTFORM', $this->widget_id, 'CM');
        $this->smarty->assign('widget_id', $this->widget_id);
        $this->smarty->assign('widget', $widget->getInfo());
        $this->smarty->assign('params', $widget->getParam());
        
		$dsrte = new WidgetdsRTE('dsrte');
    	$this->smarty->assign('editor_scripts', $dsrte->getScripts());
    	$this->smarty->assign('editor_HTML', $dsrte->getHTML($widget->getParam('EMAILTEXT')));
        
        $contact_type = new ContactType($widget->getParam('CT_ID', 1));
        $info = $contact_type->getType(User::getLang());
        $this->smarty->assign('type_name', ContactType::getName($info['name'], User::getLang()));
        if ($widget->getParam('CT_ID', 1) == 1) {
            $k = array_keys($info['fields']);
            $info['fields'][$k[0]]['fields'] = array_merge(array(array('dbname' => 'C_FULLNAME', 'name' => _s('Full name'))), $info['fields'][$k[0]]['fields']);
        }
        $this->smarty->assign('fields', $info['fields']);
        
        $this->smarty->assign('form_fields', ContactType::getFieldsNames($widget->getInfo('WG_LANG'), false));
        
        $this->smarty->assign('langs', Wbs::getDbkeyObj()->getLanguages());
        
        // if widget in private folder other user
        $folder_id = $widget->getParam('FOLDER');
        $prefix = substr($folder_id, 0, 7);
        if ($prefix == 'PRIVATE') {
            $contact_id = substr($folder_id, 7);
            // private folder of the other user
            if ($contact_id != User::getContactId()) {
                $private_folder = Contact::getName($contact_id);
                $this->smarty->assign('private_folder', $private_folder);
            }
        }
        $lists_model = new ListsModel();
        $lists = $lists_model->getAll(User::getContactId(), true);
        $this->smarty->assign('lists', $lists);
        $this->smarty->assign('folders', Contact::getFolders());
        
        GetText::load($widget->getInfo('WG_LANG'), SYSTEM_PATH . "/locale", 'system', false);
        $this->smarty->assign('fullname', _s('Name'));
        $this->smarty->assign('embed', $widget->getEmbedInfo());
        GetText::load(User::getLang(), SYSTEM_PATH . "/locale", 'system', false);
    }
}
?>