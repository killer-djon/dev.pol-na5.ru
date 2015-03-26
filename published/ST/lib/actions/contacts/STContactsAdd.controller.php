<?php

class STContactsAddController extends JsonController
{
	
	public function exec()
	{
		$name = Env::Post('name');
		$email = Env::Post('email');

		if ($name || $email) {
			$this->add($name, $email);
		}
	}
	
	protected function add($name, $email)
	{
		$errors = array();
		$contact_id = Contact::addByNameEmail($name, $email, 'ADD', $errors);
		if ($contact_id) {
			$this->response['id'] = $contact_id;
			$this->response['name'] = Contact::getName($contact_id, Contact::FORMAT_NAME_EMAIL);
		} elseif ($errors) {
			foreach ($errors as $e) {
				$this->errors[] = $e['text'];
			}
		} else {
			$this->errors[] = _s('Unknown error');
		}
	}
}