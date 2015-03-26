<?php

class STPartnersPlugin extends STPlugin 
{
	protected $apps = array(
		'MT'
	); 	
	
	protected function register()
	{
		$this->registerMethod('top', 'partners');
	}
	
	public function partnersAction($params)
	{
		$C_ID = $params['contact_id'];

	    $model = new DbModel();

		$referal = $reseller = array();

        $sql = "SELECT * FROM MT_CUSTOMER WHERE C_ID=s:C_ID";
		$customer = $model->prepare($sql)->query(array('C_ID'=>$C_ID))->fetchAssoc();
		
		if($customer) {

			$sql = "SELECT * FROM MT_REFERAL WHERE MTC_ID=i:MTC_ID";
			if($res = $model->prepare($sql)->query($customer)->fetchAssoc()) {
		
				//$sql = "SELECT SUM(MTT_OP_SUMM) `summ`, COUNT(*) `count` FROM MT_TRANSACTION WHERE MTC_ID=i:MTC_ID AND MTT_OP_SUMM > 0 AND MTO_ID IS NOT NULL AND MTT_CANCEL_ID IS NULL";
				$sql = "SELECT MTT_OP_SUMM FROM MT_TRANSACTION WHERE MTC_ID=i:MTC_ID AND MTT_OP_SUMM > 0 AND MTO_ID IS NOT NULL AND MTT_CANCEL_ID IS NULL";
				$res = $model->prepare($sql)->query($customer)->fetchAll(null, true);
				$sum = $count = 0;
				if($res) {
					foreach($res as $bonus) {
						$sum += $bonus;
						$count++;
					}
				}
				$referal['bonus']['summ'] = number_format($sum, 0, '', ' ');
				$referal['bonus']['count'] = $count;
				/*
				$referal['bonus'] = $model->prepare($sql)->query($customer)->fetchAssoc();
				$referal['bonus']['summ'] = number_format($referal['bonus']['summ'], 0, '', ' ');
			
				$sql = "SELECT SUM(MTT_OP_SUMM) FROM MT_TRANSACTION WHERE MTC_ID=i:MTC_ID AND MTT_OP_SUMM < 0 AND MTO_ID IS NULL AND MTT_CANCEL_ID IS NULL";
				$referal['outcome'] = $model->prepare($sql)->query($customer)->fetchField();
				*/
				$sql = "SELECT MTT_ACC_SUMM `summ`, MTT_CUR `currency` FROM MT_TRANSACTION WHERE MTC_ID=i:MTC_ID ORDER BY MTT_ID DESC LIMIT 1";
				$referal['balance'] = $model->prepare($sql)->query($customer)->fetchAssoc();
				$referal['balance']['summ'] = number_format($referal['balance']['summ'], 0, '', ' ');
			}
			
			$sql = "SELECT * FROM MT_RESELLER WHERE MTC_ID=i:MTC_ID";
			if($res = $model->prepare($sql)->query($customer)->fetchAssoc()) {
				/*
				$sql = "SELECT COUNT(*) `count`, SUM(O.MTO_AMOUNT) `summ`, O.MTO_CUR `currency`
					FROM MT_LICENSE L
					INNER JOIN MT_ORDER O ON O.MTO_ID=L.MTO_ID
					WHERE L.MTL_LICENSE_STATUS='ISSUED' AND O.MTO_ORDER_STATUS='PAID' AND
					O.MTC_ID=i:MTC_ID AND L.MTL_ISSUE_MTC_ID<>i:MTC_ID
					GROUP BY O.MTO_CUR";
				*/
				$sql = "SELECT COUNT(*)	FROM MT_LICENSE L INNER JOIN MT_ORDER O ON O.MTO_ID=L.MTO_ID
					WHERE L.MTL_LICENSE_STATUS='ISSUED' AND O.MTO_ORDER_STATUS='PAID' AND
					O.MTC_ID=i:MTC_ID AND L.MTL_ISSUE_MTC_ID<>i:MTC_ID";
				$reseller['count'] = $model->prepare($sql)->query($customer)->fetchField();
				/*
				$res =  $model->prepare($sql)->query($customer)->fetchAll();
				$reseller['count'] = 0;
				$reseller['summ'] = '';
				foreach($res as $row) {
					$reseller['count'] += $row['count'];
					$reseller['summ'] .= number_format($row['summ'], 0, '', ' ').' '.$row['currency'].', ';
				}
				$reseller['summ'] = substr($reseller['summ'], 0, -2);
				*/
			}

		}

        $view = View::getInstance();
        $view->assign('referal', $referal);
        $view->assign('reseller', $reseller);
        return $this->display($view, 'Partners');
	}

}
 