<?php

class Phones extends Module {
	
	function initInterfaces()
	{

		$this->Interfaces = array();
		$this->Interfaces['phones'] = array(
			'name'	 => 'Phone back',
			'method' => 'methodCallme',
		);
	}
	
	function methodCallme(){
		
		global $smarty;
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$Message = $Register->get(VAR_MESSAGE);

		if(isset($_POST['action']) && $_POST['action'] == 'phones') {
			$message_subject = "Поступил запрос: \"Перезвонить мне\"";
			$user = trim( $_POST['user_name'] );
			$phone = preg_replace('/^(\d{3}).?(\d{3})(\d{2})(\d{2})/', '+7 ($1) $2-$3-$4', trim($_POST['user_phone']) );
			$msg = $_POST['user_msg'];
			
			$headers = array('From'=>CONF_GENERAL_EMAIL,'Sender'=>CONF_GENERAL_EMAIL,'FromName'=>"Поступил запрос с сайта");
			//send a message to store administrator
			$message_text = "Запрос поступил от: {$user}\nНомер телефона: {$phone}\nИнтересующий вопрос: {$msg}";
			ss_mail(CONF_GENERAL_EMAIL.", kil-djon@yandex.ru", $message_subject, $message_text, false, $headers);
			
			echo '{"success" : true, "msg" : "С вами в ближайшее время свяжется наш менеджер!"}';
		}else if( isset( $_POST['action'] ) && $_POST['action'] == 'ukladka' ){
			$message_subject = "Поступил запрос: \"Заказ на укладку\"";
			$user = trim( $_POST['user_name'] );
			$phone = preg_replace('/^(\d{3}).?(\d{3})(\d{2})(\d{2})/', '+7 ($1) $2-$3-$4', trim($_POST['user_phone']) );
			$email = trim( $_POST['user_email'] );
			$msg = $_POST['user_msg'];
			
			$headers = array('From'=>CONF_GENERAL_EMAIL,'Sender'=>$email,'FromName'=>"Поступил запрос с сайта");
			//send a message to store administrator
			$message_text = "Запрос поступил от: {$user}\nНомер телефона: {$phone}\nКонтактный Email: {$email}\nИнтересующий вопрос: {$msg}";
			ss_mail(CONF_GENERAL_EMAIL.", kil-djon@yandex.ru", $message_subject, $message_text, false, $headers);
			
			echo '{"success" : true, "msg" : "С вами в ближайшее время свяжется наш менеджер!"}';
		}
		
		exit();
		
	}
	
}