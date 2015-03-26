<?php

class STContactsListController extends JsonController
{
	public function exec()
	{
		$text = Env::Get('text', Env::TYPE_STRING, '');
		$email = Env::Get('email');
        $limit = Env::Get('limit', Env::TYPE_STRING, '15');
		$byFolder = Env::Get('byfolder', Env::TYPE_INT, 0);
		$contacts_model = new ContactsModel();
		if ($byFolder == 0) {
		  $data = $contacts_model->quickSearch($text, $limit, "ASC");
		} else {
		  $data = $contacts_model->getWithEmailsByFolderId($text, $limit);
		}
		$this->response = array();
		foreach ($data as $row) {
		    if (!empty($row['C_EMAILADDRESS'])) {
        		$contact = $row['C_FULLNAME']." <".$row['C_EMAILADDRESS'].">";
                $this->response[] = htmlspecialchars($contact);
          	}
		}
	}
}