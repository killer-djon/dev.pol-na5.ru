<?php

class STOrdersAmountController extends JsonController
{
	public function exec()
	{
		$C_ID = Env::Get('userid', Env::TYPE_INT, 0);

		$sql = "SELECT mto.MTO_ID, mto.MTP_ID, mto.MTO_AMOUNT, mto.MTO_CUR, mto.MTO_DATE, mto.MTO_ORDER_STATUS FROM MT_ORDER mto
			LEFT JOIN MT_CUSTOMER mtc ON mto.MTC_ID=mtc.MTC_ID
			WHERE mtc.C_ID=s:C_ID GROUP BY mto.MTO_ID ORDER BY mto.MTO_ORDER_STATUS";

		$model = new DbModel();

		$orders = $model->prepare($sql)->query(array('C_ID'=>$C_ID))->fetchAll('MTO_ID');

		$mt_status_names = array(
			'CHARGEBACK'       => 'Chargeback',
			'DELETED'          => 'Deleted',
			'NEW'              => 'New',
			'PAID'             => 'Shipped',
			'REFUND'           => 'Refund',
			'WTG_CONFIRMATION' => 'Waiting confirmation'
		);
		$mt_status_styles = array(
			'CHARGEBACK'       => 'color:#FF0000;',
			'DELETED'          => 'color:#AAAAAA;',
			'NEW'              => 'color:#2B831C;',
			'PAID'             => 'color:#E68B2C;',
			'REFUND'           => 'color:#D600B5;',
			'WTG_CONFIRMATION' => 'color:#D600B5;'
		);

		$products = array();
		foreach($mt_status_names as $st_id=>$st_name) {

			foreach($orders as $key=>$ord) {

				if($ord['MTO_ORDER_STATUS'] == $st_id) {
			
					if(!isset($products[$st_id])) {
						$products[$st_id] = array();
					}
					if(!isset($products[$st_id]['amount'])) {
						$products[$st_id]['amount'] = array();
					}
					if(!isset($products[$st_id]['amount'][$ord['MTO_CUR']])) {
						$products[$st_id]['amount'][$ord['MTO_CUR']] = 0;
					}
					if(!isset($products[$st_id]['count'])) {
						$products[$st_id]['count'] = 0;
					}
					if(!isset($products[$st_id]['style'])) {
						$products[$st_id]['style'] = $mt_status_styles[$st_id];
					}
					$products[$st_id]['amount'][$ord['MTO_CUR']] += $ord['MTO_AMOUNT'];
					$products[$st_id]['count'] ++;

/*
					if(!isset($products[$st_name][$ord['MTP_ID']])) {
						$products[$st_name][$ord['MTP_ID']] = array('amount'=>array(), 'count'=>0);
						if(!isset($products[$st_id][$ord['MTP_ID']]['amount'][$ord['MTO_CUR']])) {
							$products[$st_name][$ord['MTP_ID']]['amount'][$ord['MTO_CUR']] = 0;
						}
					}
					$products[$st_name][$ord['MTP_ID']]['amount'][$ord['MTO_CUR']] += $ord['MTO_AMOUNT'];
					$products[$st_name][$ord['MTP_ID']]['count'] ++;
*/
				}
			}
		}

		$this->response = $products;
	}
	
}