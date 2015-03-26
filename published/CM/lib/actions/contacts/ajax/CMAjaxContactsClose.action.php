<?php

class CMAjaxContactsCloseAction extends UGAjaxAction
{
    protected $contact_id;
    public function __construct()
    {
        $this->contact_id = Env::Get('id', Env::TYPE_BASE64_INT, 0);
        if ($this->contact_id) {
            User::setSetting('READ', 1, 'CM', 'CONTACT:'.$this->contact_id);
        }
    }
}

?>