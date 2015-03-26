<?php
class STRequestsEmptybinController extends JsonController
{
	public function exec()
	{
		$state = Env::Post('state');
        $request_model = new STRequestModel();
		if ($state == '-1'){
		    $success = $request_model->deleteByStateId(-1);
		} elseif ($state == '0') {
            $success = $request_model->deleteByStateId(0);
		}
		$this->response['success'] = $success;
	}
}

?>
