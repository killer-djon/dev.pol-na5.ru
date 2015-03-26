<?php

class STClassesRequestsCountController extends JsonController
{
    public function exec()
    {
        $id = Env::Post('id', Env::TYPE_INT);
        $type = Env::Post('type', Env::TYPE_INT, false);
        $requests_model = new STRequestClassModel();
        $this->response = $requests_model->countRequests($id, $type);
    }
}