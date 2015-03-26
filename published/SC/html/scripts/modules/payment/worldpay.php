<?php
/**
 * WorldPay payment module
 * @connect_module_class_name CWorldPay
 * @link http://www.worldpay.com
 * @see http://www.worldpay.com/support/kb/bg/pdf/rhtml.pdf
 * @package DynamicModules
 * @subpackage Payment
 */
class CWorldPay extends PaymentModule
{

	var $type = PAYMTD_TYPE_CC;
	var $default_logo = './images_common/payment-icons/worldpay.gif';

	#old url was https://select.worldpay.com/wcc/purchase
	#old url 2was https://secure.wp3.rbsworldpay.com/wcc/purchase
	private $url = 'https://secure.worldpay.com/wcc/purchase';

	function _initVars()
	{

		parent::_initVars();
		$this->title 		= CWORLDPAY_TTL;
		$this->description 	= CWORLDPAY_DSCR;
		$this->sort_order 	= 2;

		$this->Settings = array
		(
			"CONF_PAYMENTMODULE_WORLDPAY_INSTID",
			"CONF_PAYMENTMODULE_WORLDPAY_TEST",
		);
	}

	function _initSettingFields()
	{

		$this->SettingsFields['CONF_PAYMENTMODULE_WORLDPAY_INSTID'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> CWORLDPAY_CFG_INSTID_TTL, 
			'settings_description' 		=> CWORLDPAY_CFG_INSTID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 				=> 1,
		);

		$this->SettingsFields['CONF_PAYMENTMODULE_WORLDPAY_TEST'] = array(
			'settings_value' 			=> '', 
			'settings_title' 			=> CWORLDPAY_CFG_TEST_TTL, 
			'settings_description' 		=> CWORLDPAY_CFG_TEST_DSCR, 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 				=> 2,
		);
	}

	function after_processing_html( $orderID, $active = true )
	{
		$order = ordGetOrder( $orderID );
		$order_amount = round(100*$order["order_amount"] * $order["currency_value"])/100;

		$res = "";
		$country = "US";

		$fields = LanguagesManager::ml_getLangFieldNames('country_name');
		$where_clause = '';
		foreach ($fields as $field) {
			if ($field && isset($order["billing_country"]) && $order["billing_country"]) {
				$where_clause .= (strlen($where_clause)?' OR ':'')."{$field} = ?billing_country";
			}
		}
		if (strlen($where_clause)) {
			$sql  = 'SELECT country_iso_2 FROM ?#COUNTRIES_TABLE WHERE '.$where_clause;
			$q = db_phquery($sql,$order);
			if ($row = db_fetch_row($q)) {//country is not defined
				$country = $row[0];

			}
		}
		$order["billing_address"] = str_replace("\n","&#10;",$order["billing_address"]);
		$description = CONF_SHOP_NAME." - Order #{$orderID}";
		$submit = CWORLDPAY_TXT_AFTER_PROCESSING_HTML_1;
		if ($this->_getSettingValue('CONF_PAYMENTMODULE_WORLDPAY_TEST')) {
			$mode = 100;
		} else {
			$mode = 0;
		}

		$res .= <<<HTML
<table width='100%'>
	<tr>
		<td align="center">
			<form method="POST" action="{$this->url}" id="worldpay_form">
				<input type="hidden" name="instId" value="{$this->_getSettingValue('CONF_PAYMENTMODULE_WORLDPAY_INSTID')}">
				<input type="hidden" name="desc" value="{$description}">
				<input type="hidden" name="cartId" value="{$orderID}">
				<input type="hidden" name="amount" value="{$order_amount}">
				<input type="hidden" name="currency" value="{$order["currency_code"]}">
				<input type="hidden" name="testMode" value="{$mode}">
				<input type="hidden" name="country" value="{$country}">
				<input type="hidden" name="postcode" value="{$order["billing_zip"]}">
				<input type="hidden" name="address" value="{$order["billing_address"]}">
				<input type="hidden" name="email" value="{$order["customer_email"]}">
				<input type="submit" value="{$submit}">
			</form>		
		</td>
	</tr>
</table>
HTML;

		if($active){
			$res .= <<<HTML
<script type="text/javascript">
<!--
setTimeout(\'document.getElementById("worldpay_form").submit();\',2000);
//-->
</script>
HTML;
		}

		return $res;
	}
}

