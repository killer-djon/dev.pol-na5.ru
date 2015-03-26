<?php
define('C2CHECKOUT_TTL',								'2checkout');
define('C2CHECKOUT_DSCR',								'2checkout credit cards processing module');
define('C2CHECKOUT_CFG_ID_TTL',						'2checkout merchant ID');
define('C2CHECKOUT_CFG_ID_DSCR',					'Please input your 2checkout login ID');
define('C2CHECKOUT_CFG_SECRET_TTL',				'Secret word');
define('C2CHECKOUT_CFG_SECRET_DSCR',			'Secret word is a text string appended to the payment credentials, which are sent to merchant together with the payment notification.
<br />It is used to enhance the security of the notification identification and should not be disclosed to third parties.');
define('C2CHECKOUT_CFG_USD_CURRENCY_TTL',	'USD currency type');
define('C2CHECKOUT_CFG_USD_CURRENCY_DSCR',	'Order amount transferred to 2CO web site is denominated in USD. Specify currency type in your shopping cart which is assumed as USD (order amount will be calculated according to USD exchange rate; if not specified exchange rate will be assumed as 1)');
define('C2CHECKOUT_CFG_DEMO_TTL',				'Sandbox mode');
define('C2CHECKOUT_CFG_DEMO_DSCR',				'');
define('C2CHECKOUT_CUST_RESULT_URL_TTL',		'Approved URL');
define('C2CHECKOUT_CUST_RESULT_URL_DSCR',	'Destination URL for payment notifications. <strong>Copy and paste this address into the corresponding setting field in your 2CO account.</strong>');
define('C2CHECKOUT_TXT_1',								'Proceed to 2checkout payment gateway');
?>