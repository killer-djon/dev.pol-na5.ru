<?php

class STActivityPlugin extends STPlugin 
{
	protected $apps = array(
		'MT'
	); 	
	
	protected function register()
	{
		$this->registerMethod('top', 'activity');
	}
	
	public function activityAction($params)
	{
		$C_ID = $params['contact_id'];

	    $model = new DbModel();

		$apps = $wahost = $arhost = $domains = array();

        $sql = "SELECT * FROM MT_CUSTOMER WHERE C_ID=s:C_ID";
		$customer = $model->prepare($sql)->query(array('C_ID'=>$C_ID))->fetchAssoc();
		
		if($customer) {
		
			$sql = "SELECT APP_ID FROM MT_WAOS_APPS a JOIN MT_LICENSE l ON l.MTL_ID=a.MTL_ID
				WHERE l.MTL_ISSUE_MTC_ID=i:MTC_ID AND l.MTL_LICENSE_STATUS='ISSUED'";
			$all_apps = $model->prepare($sql)->query($customer)->fetchAll(null, true);

			foreach($all_apps as $app) {
				if(!empty($apps[$app])) {
					$apps[$app]['count']++;
				} else {
					$apps[$app] = Rights::getApplicationInfo($app);
					$apps[$app]['count'] = 1;
				}
			}

			$aa_locale = @file_get_contents('../AA/localization/aa.'.User::getLang());

			$sql = "SELECT * FROM MT_WAHOST_ACCOUNT WHERE ACC_MTC_ID=i:MTC_ID";
			$wahost = $model->prepare($sql)->query($customer)->fetchAll();
			
			foreach($wahost as $key=>$wah) {
				$wah['expired'] = false;
				$wah['plan'] = $wah['ACC_PLAN'];
				if($wah['ACC_BILLING_DATE'] && $wah['ACC_BILLING_DATE'] < date('Y-m-d')) {
					$wah['expired'] = true;
				}
				
				if($aa_locale && preg_match('/tariff_'.$wah['ACC_PLAN'].'_label\t[^\t]+\t[^\t]+\t([^\n]+)/', $aa_locale, $match)) {
					$wah['plan'] = $match[1];
				}
				$wahost[$key] = $wah;
			}

			$sql = "SELECT * FROM MT_ARHOST_ACCOUNT WHERE MTAA_MTC_ID=i:MTC_ID AND MTAA_CANCEL_DATE=0";
			$arhost = $model->prepare($sql)->query($customer)->fetchAll();

			foreach($arhost as $key=>$arh) {
				$arh['expired'] = false;
				$arh['plan'] = $arh['MTAA_PLAN'];
				if($arh['MTAA_EXPIRE_DATE'] && $arh['MTAA_EXPIRE_DATE'] < date('Y-m-d')) {
					$arh['expired'] = true;
				}
				/*
				if(preg_match('/^([^;]+)/', $arh['MTAA_PARAMS'], $match)) {
					$arh['plan'] = $match[1];
				}
				*/
				$arhost[$key] = $arh;
			}

			//$sql = "SELECT * FROM MT_DOMAIN_NAMES WHERE MTDN_MTC_ID=i:MTC_ID";
			$sql="SELECT MTDR_DOMAIN_NAME, MTDR_EXPIRE_DATE, MTDR_REG_DATE FROM MT_DOMAIN_REG D, MT_DOMREG_CONTACT C 
						WHERE C.MTCC_ID = D.MTDR_MTCC_ID AND C.MTCC_CANCEL_DATE IS NULL AND D.MTDR_CANCEL_DATE = 0 AND C.MTCC_MTC_ID =i:MTC_ID";
			$domains = $model->prepare($sql)->query($customer)->fetchAll();
			foreach($domains as $key=>$dom) {
				$dom['expired'] = $dom['new'] = false;
				if(!$dom['MTDR_REG_DATE']) {
					$dom['new'] = true;
				} elseif(!$dom['MTDR_EXPIRE_DATE'] || $dom['MTDR_EXPIRE_DATE'] < date('Y-m-d')) {
					$dom['expired'] = true;
				}
				$domains[$key] = $dom;
			}

		}

        $view = View::getInstance();
        $view->assign('apps', $apps);
        $view->assign('wahost', $wahost);
        $view->assign('arhost', $arhost);
        $view->assign('domains', $domains);
        return $this->display($view, 'Activity');
	}
	
	
}
 