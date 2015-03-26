<?php

class STRequestsCountController extends JsonController
{
	public function exec()
	{
		session_write_close();
		$request_model = new STRequestModel();
        $this->response['count'] = $request_model->countAll();
        $this->response['limit'] = Limits::get('ST');
	}
	
}