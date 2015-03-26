<?php

class CMContactsPersonalAction extends UGViewAction
{
	protected $hash;
	protected $contact_id;
	protected $contact_info = array();

	public function __construct()
	{
		parent::__construct();
		$this->hash = Env::Get('key');

		if ((substr($this->hash, 6, 1) === 'r') && Wbs::getDbkeyObj()->appExists('ST')) {
			$request_id = substr($this->hash, 7, -6);
			$model = new DbModel();
			$sql = 'SELECT * FROM st_request WHERE id = '.(int)$request_id;
			$request_info = $model->query($sql)->fetch();
			if ($request_info['client_c_id']) {
				$url = Contact::getSubscribeLink($request_info['client_c_id']);
				Url::go($url."&t=requests", true);
			}
			$hash = substr(md5($request_info['datetime']), 0, 6);
			$hash .= "r".$request_id;
			$hash .= substr(md5($request_info['client_from']), -6);
			if ($this->hash === $hash) {
				$address = MailParser::address($request_info['client_from']);
				User::setApp('ST');
				if ($request_info['source_type'] == 'form') {
					$method = 'FORM';
				} else {
					$method = 'EMAIL';
				}
				$contact_id = Contact::addByNameEmail($address[0]['name'], $address[0]['email'], $method);
				if ($contact_id) {
					$source_id = false;
					if ($request_info['source_type'] == 'form') {
						$params_model = new WidgetParamsModel();
						$params = $params_model->getByWidget($request_info['source']);
						if (isset($params['SOURCEID']) && $params['SOURCEID']) {
							$source_id = $params['SOURCEID'];
						}
					} else {
						$source = $request_info['source'];
						$sql = "SELECT source_id FROM st_source_param WHERE name = 'email' AND value='".$model->escape($source)."' LIMIT 1";
						$source_id = $model->query($sql)->fetchField();
					}
					// Set language from settings of source email
					if ($source_id) {
						$sql = "SELECT value FROM st_source_param WHERE source_id = ".(int)$source_id." AND name = 'language'";
						$lang = $model->query($sql)->fetchField();
						if (!$lang) {
							$lang = 'eng';
						}
						$sql = "UPDATE CONTACT SET C_LANGUAGE = s:lang WHERE C_ID = i:id";
						$model->prepare($sql)->exec(array('lang' => $lang, 'id' => $contact_id));
					}
					if (!$request_info['state_id']) {
						$sql = "SELECT * FROM st_action WHERE type = 'CONFIRM-CLIENT' LIMIT 1";
						$action = $model->query($sql)->fetch();
						$sql = "UPDATE st_request SET client_c_id = ".(int)$contact_id.", state_id = ".(int)$action['state_id']." WHERE id = ".(int)$request_id;
						$model->exec($sql);
					}
					$url = Contact::getSubscribeLink($contact_id);
					Url::go($url."&t=requests", true);
				}
			}
			header("HTTP/1.0 404 Not Found");
			echo "<h1>Not Found</h1>";
			exit;
			//Url::go('/');
		} else {
			if (defined('STRONG_AUTH') && STRONG_AUTH) {
				$hash = explode('-', $this->hash);
				$this->contact_id = substr($hash[0], 6, -6);
			} else {
				$this->contact_id = substr($this->hash, 6, -6);
			}
		}
		if (!$this->check()) {
			header("HTTP/1.0 404 Not Found");
			echo "<h1>Not Found</h1>";
			exit;
			//Url::go('/');
		}

		$tab = Env::Session('tab');

		if (!Env::Get('confirm') && !Env::Session('confirm') && (Env::Get('i') === false)) {
			$tab = 'subscription';
		} elseif (Env::Session('confirm')) {
			$this->smarty->assign('show_message', 1);
			Env::unsetSession('confirm');
		} else {
			$this->smarty->assign('show_message', 0);
		}
		$this->smarty->assign('current_tab', $tab);

		// confirmation subscriber if it is necessary
		if ($this->contact_info['C_SUBSCRIBER'] == 0) {
			$this->confirm();
		}
			
		$this->do = Env::Get('do');
		switch ($this->do) {
			case "unsubscribe": {
				// Remove from lists
				/*
				$contact_lists_model = new ContactListModel();
				$contact_lists_model->deleteByContact($this->contact_id);
				*/
				// Unsubscribe
				Contact::unSubscribe($this->contact_id);
				$this->contact_info['C_SUBSCRIBER'] = -1;
				break;
			}
			case "subscribe":
				Contact::subscribe($this->contact_id);
				Url::go(Contact::getSubscribeLink($this->contact_id), true);
				break;
			case "remove": {
				try {
					$contact_lists_model = new ContactListModel();
					$contact_lists_model->deleteByContact($this->contact_id);
				} catch (Exception $e) {
						
				}
				Contact::unSubscribe($this->contact_id, true);
				Url::go(Contact::getSubscribeLink($this->contact_id), true);
				break;
			}
		}
	}

	protected function confirm()
	{
		// find subscribers with the same email address
		$contacts_model = new ContactsModel();
		$contact_id = $contacts_model->getSubscribeByEmail($this->contact_info['C_EMAILADDRESS'][0], true);
		if (!$this->contact_info['U_ID'] && $contact_id) {
			$contact_info = $contacts_model->get($contact_id);
			$contact_type = new ContactType($this->contact_info['CT_ID']);
			$fields = $contact_type->getTypeDbFields();
			$dbfields = $contact_type->getDbFields();
			$new_contact_info = $contacts_model->get($this->contact_id);
			$data = array();
			if (isset($contact_info['SC_ID'])) {
				$dbfields[] = 'SC_ID';
			}
			foreach ($contact_info as $field => $value) {
				if (in_array($field, $dbfields)) {
					if (in_array($field, $fields)) {
						if ($new_contact_info[$field]) {
							$data[$field] = $new_contact_info[$field];
						} else {
							$data[$field] = $value;
						}
					} else {
						$data[$field] = null;
					}
				}
			}
			// Change type of the contact
			if ($contact_info['CT_ID'] != $new_contact_info['CT_ID']) {
				$contacts_model->save($contact_id, array('CT_ID' => $new_contact_info['CT_ID']));
			}
			$errors = array();
			Contact::save($contact_id, $data, $errors);
			// Add old contact to new lists
			$contact_lists_model = new ContactListModel();
			$lists = $contact_lists_model->getIds($this->contact_id);
			$contact_lists_model->addToLists($contact_id, $lists);
			// delete new contact
			Contact::delete($this->contact_id);
			// update links to contact
			if (Wbs::getDbkeyObj()->appExists('ST')) {
				$model = new DbModel();
				$sql = "UPDATE st_request SET client_c_id = ".(int)$contact_id."
            			WHERE client_c_id = ".(int)$this->contact_id;
				$model->exec($sql);
				$sql = "UPDATE st_request_log SET actor_c_id = ".(int)$contact_id."
            			WHERE actor_c_id = ".(int)$this->contact_id;
				$model->exec($sql);
			}
			$this->contact_id = $contact_id;

		} else {
			$errors = array();
			Contact::save($this->contact_id, array('C_SUBSCRIBER' => 1), $errors, false);
		}
		$sql = "DELETE FROM UNSUBSCRIBER WHERE ENS_EMAIL = s:email";
		$contacts_model->prepare($sql)->exec(array('email' => $this->contact_info['C_EMAILADDRESS'][0]));
		if (Env::Get('confirm')) {
			Env::setSession('confirm', 1);
		}
		$url = Contact::getSubscribeLink($this->contact_id);
		if (Env::Get('do')) {
			$url .= "&do=".Env::Get('do');
		}
		Url::go($url, true);
	}

	public function check()
	{
		$this->contact_info = Contact::getInfo($this->contact_id);
		if (!$this->contact_info) {
			return false;
		}
		$contacts_model = new ContactsModel();
		$contact_info = $contacts_model->get($this->contact_id);
		if (defined('STRONG_AUTH') && STRONG_AUTH) {
			$md5 = md5($this->contact_id.$contact_info['C_CREATEDATETIME']);
			$hash = explode('-', $this->hash, 2);
			if (!isset($hash[1]) || $hash[1] != (is_array($contact_info['C_EMAILADDRESS']) ? $contact_info['C_EMAILADDRESS'][0] : $contact_info['C_EMAILADDRESS'])) {
				return false;
			}
			return (substr($hash[0], 0, 6) == substr($md5, 0, 6) && substr($hash[0], -6) == substr($md5, -6));
		} else {
			$md5 = md5($contact_info['C_CREATEDATETIME']);
			return (substr($this->hash, 0, 6) == substr($md5, 0, 6) && substr($this->hash, -6) == substr($md5, -6));
		}
	}

	public function prepareData()
	{
		$this->smarty->assign('company', Company::getName());
		$this->smarty->assign('contact_name', Contact::getName($this->contact_id));
		$this->smarty->assign('contact', $this->contact_info);


		$contact_type = new ContactType($this->contact_info['CT_ID']);
		$type = $contact_type->getType();
		$js = array();

		$main_fields = $contact_type->getMainFields(true);
		$main_section = ContactType::getMainSection();
		$type['fields'] = array_values($type['fields']);
		foreach($type['fields'] as $x => &$group) {
			$js_f = array();
			if ($group['fields']) {
				foreach ($group['fields'] as $f) {
					if ($f['type'] == 'IMAGE' || (!$this->contact_info[$f['dbname']] && !in_array($f['id'], $main_fields))) {
						continue;
					}
					$field_info = array(
					$f['id'],
					$f['name'],
					$f['type'],
					$f['type'] == 'EMAIL' ? $this->contact_info[$f['dbname']] : "{$this->contact_info[$f['dbname']]}",
					$f['required']
					);
						
					if ($f["options"]) {
						$field_info[] = $f["options"];
					}
					$js_f[] = $field_info;
				}
			}
			if ($group['id'] == $main_section) {
				$main = array();
				$other = array();
				foreach ($js_f as $field_info) {
					if (in_array($field_info[0], $main_fields)) {
						$main[] = $field_info;
					} else {
						$other[] = $field_info;
					}
				}
				$js_f = array_merge($main, $other);
			}
			$js[] = array($group['id'] != $main_section ? "group".$x : "CONTACT", $js_f);
		}

		//$show_message = User::getSetting('READ', 'CM', 'CONTACT:'.$this->contact_id);
		//$this->smarty->assign('show_message', !$show_message);
		$this->smarty->assign('contact_id', base64_encode($this->contact_id));
		$this->smarty->assign('contact_subscript', $this->contact_info['C_SUBSCRIBER']);
		$this->smarty->assign('name', Contact::getName($this->contact_id));
		$this->smarty->assign('type', $this->contact_info['CT_ID']);
		$this->smarty->assign('js', json_encode($js));
		$this->smarty->assign('fields', $type['fields']);

		// Check logo exists
		$logoFilename = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("AA", "logo.gif");
		$logoExists = file_exists($logoFilename);
		$logoTime = ($logoExists) ? filemtime($logoFilename) : null;
		 
		// Load viewsettings
		$dbkeyObj = Wbs::getDbkeyObj();
		$showLogo = ($dbkeyObj->getAdvancedParam("show_company_top") == "yes") && $logoExists;
		$showCompanyName = ($dbkeyObj->getAdvancedParam("show_company_name_top") != "no");

		$this->smarty->assign('logo_time', $logoTime);
		$this->smarty->assign('show_logo', $showLogo);

		if (Wbs::getDbkeyObj()->appExists('CM')) {
			$contact_lists_model = new ContactListModel();
			$this->smarty->assign('lists', $contact_lists_model->getLists($this->contact_id));
		}
		$this->smarty->assign('contact_url', Contact::getSubscribeLink($this->contact_id));

		$this->smarty->assign('subscribe_exists', Wbs::getDbkeyObj()->appExists('MM'));

		$date_format = mb_strtolower(Wbs::getDbkeyObj()->getDateFormat());
		$this->smarty->assign("dateFormat", mb_substr($date_format, 0, -2));

		$countries = Wbs::getCountries();
		$this->smarty->assign('countries', json_encode($countries));

		$this->smarty->assign('support_exists', Wbs::getDbkeyObj()->appExists('ST'));

		$wbs_d = (int)(strtotime('2010-07-16 00:00:00') - time()) / 86400;
		$this->smarty->assign('wbs_d', $wbs_d);
		$this->smarty->assign('wbs', Wbs::isHosted() && Wbs::getDbKey() == 'WEBASYST');

	}

}
?>