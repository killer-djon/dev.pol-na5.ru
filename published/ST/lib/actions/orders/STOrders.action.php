<?php

class STOrdersAction extends Action
{
	
	public function prepare()
	{
	    
        if (User::hasAccess("MT") && Wbs::getDbkeyObj()->appExists("MT")){
			$C_ID = Env::Get('userid', Env::TYPE_INT, 0);
	
			$sql = "SELECT mto.MTO_ID, mto.MTP_ID, mto.MTO_DESC, mto.MTO_AMOUNT, mto.MTO_CUR, mto.MTO_DATE,
				mto.MTO_ORDER_STATUS, mto.MTO_PAYMENT_OPTION, mto.MTO_SHIP_DATE, mto.MTO_PAID_INSTALL, mto.MTO_BULK_COUNT
				FROM MT_ORDER mto LEFT JOIN MT_CUSTOMER mtc ON mto.MTC_ID=mtc.MTC_ID
				WHERE mtc.C_ID=s:C_ID GROUP BY mto.MTO_ID ORDER BY mto.MTO_DATE DESC, mto.MTO_ID DESC";
	
			$model = new DbModel();
	
			$orders = $model->prepare($sql)->query(array('C_ID'=>$C_ID))->fetchAll('MTO_ID');
	
			$orders_total = array('amount'=>array(), 'count'=>count($orders));
			$products = array();
			foreach($orders as $key=>$ord) {
				if(!isset($orders_total['amount'][$ord['MTO_CUR']])) {
					$orders_total['amount'][$ord['MTO_CUR']] = 0;
				}
				if($ord['MTO_ORDER_STATUS'] == 'PAID') {
					$orders_total['amount'][$ord['MTO_CUR']] += $ord['MTO_AMOUNT'];
				}
				$orders[$key]['MTO_DATE'] = WbsDateTime::getTime(strtotime($ord['MTO_DATE']));
				if($ord['MTO_SHIP_DATE']) {
					$orders[$key]['MTO_SHIP_DATE'] = WbsDateTime::getTime(strtotime($ord['MTO_SHIP_DATE']));
				} else {
					$orders[$key]['MTO_SHIP_DATE'] = '-';
				}
				$orders[$key]['description'] = '';
				if(stripos($ord['MTP_ID'], 'WAOS') !== false) {
					$separator = '';
					if(preg_match('/APP:(.*)/i', $ord['MTO_DESC'], $match)) {
						$orders[$key]['description'] = $match[1];
						$separator = ', ';
					}
					if($ord['MTO_BULK_COUNT'] > 1) {
						$orders[$key]['description'] .= $separator.$ord['MTO_BULK_COUNT'].' lic';
					}
				}
				if($ord['MTO_PAID_INSTALL']) {
					$orders[$key]['MTP_ID'] .= '+INST';
				}


				if(!isset($products[$ord['MTP_ID']])) {
					$products[$ord['MTP_ID']] = array('amount'=>array(), 'count'=>0);
					if(!isset($products[$ord['MTP_ID']]['amount'][$ord['MTO_CUR']])) {
						$products[$ord['MTP_ID']]['amount'][$ord['MTO_CUR']] = 0;
					}
				}
				if($ord['MTO_ORDER_STATUS'] == 'PAID') {
					$products[$ord['MTP_ID']]['amount'][$ord['MTO_CUR']] += $ord['MTO_AMOUNT'];
				}
				$products[$ord['MTP_ID']]['count'] ++;
			}
			foreach($orders_total['amount'] as $key=>$num) {
				$orders_total['amount'][$key] = number_format($num, 2, ',', ' ');
			}
            $this->view->assign('has_access', true);
            $this->view->assign('orders', $orders);
            $this->view->assign('orders_total', $orders_total);
        } else {
            $this->view->assign('has_access', false);
        }

        $this->view->assign('contact', Contact::getName($C_ID));
        $this->view->assign('request_id', Env::Get('request', Env::TYPE_INT, 0));
	}
}

?>