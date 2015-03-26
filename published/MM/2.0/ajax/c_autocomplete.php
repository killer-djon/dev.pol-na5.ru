<?php

	include '../../../../system/init.php';

	Wbs::authorizeUser('MM');

	$text = Env::Get('text', Env::TYPE_STRING, '');

	$result = array();
	if ($text) {
		$contacts_model = new ContactsModel();
		$data = $contacts_model->quickSearch($text, 10);

		$result = array();
		Contact::useStore(false);
		foreach($data as $contact) {
				$result[] = Contact::getName($contact['C_ID'], Contact::FORMAT_NAME_EMAIL, $contact);
		}
		Contact::useStore(true);
	}

	$json = new Services_JSON();
	echo $json->encode($result);

?>