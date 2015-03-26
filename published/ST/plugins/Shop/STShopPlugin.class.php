<?php

class STShopPlugin extends STPlugin
{
	protected $apps = array(
		'SC'
	);

	protected function register()
	{
		$this->registerMethod('sidebar', 'shop');
	}

	public function shopAction($params)
	{
		$view = View::getInstance();
		$contact_id = (int)$params['contact_id'];
		if (!$contact_id) {
			$view->assign('customer', false);
			return $this->display($view, 'Shop');
		}
		$view->assign('contact', Contact::getName($contact_id));
		$customer_info = Contact::getInfo($contact_id);

		$lang = User::getLang();
		$lang = strtolower(substr($lang,0,2));
		$model = new DbModel();
		
		$contact_model = new ContactsModel();
		$customer_emails = $contact_model->getEmail($contact_id);
		
		if (!$customer_emails) {
			$view->assign('customer', false);
			return $this->display($view, 'Shop');
		}
		
		$sql = 'SELECT `customerID` FROM `SC_customers` WHERE ';
		foreach ($customer_emails as $email_id => $email){
			$email = $contact_model->escape($email);
			$email = str_replace('_', '\_', $email);
			if ($email_id){
				$sql .= ' OR';
			}
			$sql .= " `Email` LIKE '".$email."'";
		}
		$customer_ids = array();
		if(isset($customer_info['SC_ID']) && $customer_info['SC_ID']){
			$customer_ids[] = (int)$customer_info['SC_ID'];
		}
		$data = $model->query($sql);
		foreach($data as $row){
			$customer_ids[] = (int)$row['customerID'];
		}
		$customer_ids = array_unique($customer_ids);
		$orders = array();
		if(count($customer_ids)){
			if(!isset($customer_info['SC_ID']) ||!$customer_info['SC_ID']){
				$customer_info['SC_ID'] = $customer_ids[0];
			}
			$_SESSION['WBS_ACCESS_SC'] = true;
			$_SESSION['__WBS_SC_DATA']['U_ID'] = User::getId();
			
			$sql = "SELECT `iso2` FROM `SC_language` WHERE `id` = (SELECT `settings_value` FROM `SC_settings` WHERE `settings_constant_name` = 'CONF_DEFAULT_LANG' LIMIT 1) LIMIT 1";
			$def_lang = $model->query($sql)->fetchField();
			if (!$def_lang) {
				$def_lang = 'en';
			}
			
			$statuses = array();
			$sql = 'SELECT * FROM  `SC_order_status`';
			$data = $model->query($sql);
			foreach ($data as $row) {
				$row['status_style'] = '';
				if($row['color']){
					$row['color'] = htmlspecialchars($row['color'], ENT_QUOTES);
					if(preg_match('/^([0-9A-F]{3}){1,2}$/i',$row['color'])){
						$row['color'] = '#'.$row['color'];
					}
					$row['status_style'] .= 'color: '.$row['color'].'!important;';
				}
				if($row['bold']){
					$row['status_style'] .= 'font-weight: bold!important;';
				}
				if($row['italic']){
					$row['status_style'] .= 'font-style: italic!important;';
				}
				if(isset($row["status_name_{$lang}"])){
					$row['status_name'] = $row["status_name_{$lang}"];
				}elseif(isset($row["status_name_{$def_lang}"])){
					$row['status_name'] = $row["status_name_{$def_lang}"];
				}else{
					$row['status_name'] = 'unknown';
				}
				$statuses[$row['statusID']] = $row;
			}

			$sql = "SELECT `settings_value` FROM `SC_settings` WHERE `settings_constant_name` = 'CONF_ORDERID_PREFIX' LIMIT 1";
			$prefix = $model->query($sql)->fetchField();
			
			$sql = 'SELECT `orders`.`orderID`,'.
					'`orders`.`order_time`,'.
					'`orders`.`currency_code`,'.
					'`orders`.`statusID` as `statusID`,'.
					'ROUND(`orders`.`currency_value`*`orders`.`order_amount`,2) AS order_amount,'.
					'(GROUP_CONCAT(CONCAT(`ord_cont`.`name`,\' — \',`ord_cont`.`Quantity`) SEPARATOR '."'<br/>\n'".')) as \'order_content\','.
					'`orders`.`shipping_type`,'.
					'CONCAT(`orders`.`shipping_firstname`,\' \',`orders`.`shipping_lastname`,'."'\n'".',`orders`.`shipping_address`,'."'\n'".','.
					'`orders`.`shipping_city`,\' \',`orders`.`shipping_zip`,'."'\n'".',`orders`.`shipping_state`,'."'\n'".',`orders`.`shipping_country`) as \'shipping_address\','.
					'`orders`.`shipping_cost`,'.
					'`orders`.`payment_type`'.
					'FROM `SC_orders` AS `orders` JOIN `SC_ordered_carts` AS `ord_cont` ON(`ord_cont`.`orderID`=`orders`.`orderID`)';
			if(count($customer_ids)>1){
				$sql .=' WHERE `orders`.`customerID` IN ('.implode(', ',$customer_ids).')';
			}else{
				$customer_id = array_shift($customer_ids);
				$sql .=" WHERE `orders`.`customerID` = {$customer_id}";
			}
			$sql .=	' GROUP BY `ord_cont`.`orderID`'.
					' ORDER BY `orders`.`orderID` DESC'.
					' LIMIT 5';
			
			$data = $model->query($sql);
			foreach ($data as $row) {
				$row['OrderIDView'] = $prefix.$row['orderID'];
				$row['order_time'] = WbsDateTime::getTime(strtotime($row['order_time']));
				$row['status_name'] = Env::getData($statuses,"{$row['statusID']}[status_name]",Env::TYPE_STRING,'');
				$row['status_style'] = Env::getData($statuses,"{$row['statusID']}[status_style]",Env::TYPE_STRING,'');
				$orders[] = $row;
			}
			$view->assign('customer', $customer_info);
			$view->assign('orders', $orders);
		}else{
			$view->assign('customer', false);
		}
			
		return $this->display($view, 'Shop');
	}
}
