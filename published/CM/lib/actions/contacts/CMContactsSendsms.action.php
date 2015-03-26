<?php
 
class CMContactsSendsmsAction extends UGViewAction 
{
    public function __construct()
    {
        parent::__construct();

        if (Env::Get('users', Env::TYPE_STRING, false) === false) {
        	$this->sendsms();
        }
    }

    public function sendsms()
    {
		$errorStr = false;

		// Parse TO value
		//
		if($to = trim(Env::Post('To'))) {
			$phones = $contacts = preg_split('/\s*[;,]\s*/', $to);
		} else {
			$phones = $contacts = array();
			$errorStr = 'Invalid contact data';
		}
		if(!($message = trim(Env::Post('message')))) {
			$errorStr = 'Invalid message data';
		}

		if(!$errorStr) {

			$message = str_replace("\r\n", "\n", $message);

			// Send message to each recipient
			//
			$sentCount = 0;
			foreach ($phones as $recipient) {
			
				if (strlen($recipient)) {

					$history = SMS::send(User::getId(), $recipient, $message, 'CM');
					
					if ($history['SMSH_MSGID']) {
						$sentCount++;
					}
				}
			}
			if ($sentCount) {
				exit(json_encode(array('Error'=> false, 'Count'=>$sentCount)));
			} else {
				$errorStr = $history['SMSH_STATUS_TEXT'];
			}
		}
		exit(json_encode(array('Error'=> $errorStr, 'Count'=>false)));
	}
    
    public function prepareData()
    {
		$phones = array();
		if ($contacts = Env::Get('users')) {
		    $contact_type_model = new ContactTypeModel();
		    $types = $contact_type_model->getTypeIds();
		    $fields = array();
		    foreach ($types as $type_id) {
		        $type = new ContactType($type_id);
		        $fields[$type_id] = $type->getMobileFields();
		    }
		        
			$contacts = explode(',', $contacts);
			if(is_array($contacts)) {
				$contacts_model = new ContactsModel();
				foreach($contacts as $id) {
				    $contact = $contacts_model->get((int)$id);
				    if (isset($fields[$contact['CT_ID']]) && $fields[$contact['CT_ID']]) {
				    	foreach ($fields[$contact['CT_ID']] as $f) {
					        $phone = $contact[$f];
					        if ($phone) {
					            $phones[] = $phone;
					        }
				    	} 
				    }
				}
			}
		} else {
			$contacts = array();
		}

		$this->smarty->assign('phones', join('; ', $phones));
		$this->smarty->assign('phones_count', count($phones));
		$this->smarty->assign('contacts_count', count($contacts));
    	$this->smarty->assign('mode', Env::Get('mode', Env::TYPE_STRING));
	}
    
}

?>
