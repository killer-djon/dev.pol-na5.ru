<?php

class CMAnalyticsIndexAction extends UGViewAction
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getTypes()
    {
        $types = ContactType::getTypeNames();
        $contacts_model = new ContactsModel();
        $sql = "SELECT CT_ID, COUNT(*) FROM CONTACT GROUP BY CT_ID";
        $types_count = $contacts_model->query($sql)->fetchAll('CT_ID', true);
        $this->smarty->assign('types', $types);
        $this->smarty->assign('types_count', $types_count);
    }
    
    public function getFields()
    {
        $fields = ContactType::getAllFields(User::getLang(), ContactType::TYPE_FIELD, true);
        $select = array();
        foreach ($fields as $field) {
            $select[] = "SUM(IF(".$field['dbname']." IS NULL OR ".$field['dbname']." = '', 0, 1)) ".$field['dbname'];
        }
        $sql = "SELECT ".implode(", ", $select)." FROM CONTACT";
        $contacts_model = new ContactsModel();
        $fields_count = $contacts_model->query($sql)->fetch();
        $this->smarty->assign('fields', $fields);
        $this->smarty->assign('fields_count', $fields_count);  
    }
    
    public function getUsers()
    {
        $users_model = new UsersModel();
        $users = $users_model->getNames();
        $contacts_model = new ContactsModel();
        $sql = "SELECT C_CREATECID, COUNT(*) FROM CONTACT GROUP BY C_CREATECID";
        $users_count = $contacts_model->query($sql)->fetchAll('C_CREATECID', true);
        if (!isset($users_count[0])) {
            $users_count[0] = 0;  
        } 
        foreach ($users_count as $contact_id => $n) {
            if ($contact_id !== 0 && !isset($users[$contact_id])) {
                $users_count[0] += $n;
                unset($users_count[$contact_id]);
            }
        }
        if (!$users_count[0]) {
        	unset($users_count[0]);
        }
        $this->smarty->assign('users', $users);
        $this->smarty->assign('users_count', $users_count);
    }
    
    
    public function getSubscribers()
    {
        $contacts_model = new ContactsModel();
        $sql = "SELECT C_SUBSCRIBER, COUNT(*) FROM CONTACT WHERE C_SUBSCRIBER IS NOT NULL GROUP BY C_SUBSCRIBER";
        $count = $contacts_model->query($sql)->fetchAll('C_SUBSCRIBER', true);
        $count = array(
            'confirm' => isset($count[1]) ? $count[1] : 0,
        	'noconfirm' => isset($count[0]) ? $count[0] : 0,
            'unsubscribed' => isset($count[-1]) ? $count[-1] : 0,
        	'canceled' => isset($count[-2]) ? $count[-2] : 0,
        );
        $this->smarty->assign('subscribers_count', $count);
    }
    
    public function prepareData()
    {
        User::setSetting('LASTFOLDER', 'ANALYTICS', 'CM');
        $contacts_model = new ContactsModel();
        $this->smarty->assign('count', $contacts_model->countAll());
        
        $this->getTypes();
        $this->getFields();
        $this->getUsers();
        $this->getSubscribers();
    }
}