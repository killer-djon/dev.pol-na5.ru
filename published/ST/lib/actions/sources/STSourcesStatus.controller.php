<?php

class STSourcesStatusController extends JsonController
{
	protected $source_id;
	public function exec()
	{
		$this->source_id = Env::Post('id', Env::TYPE_INT, 0);
		if ($this->source_id) {
			$source_model = new STSourceModel();
			$source_model->setStatus($this->source_id, Env::Post('status', Env::TYPE_INT, 1));
		} else {
			$this->errors = "Unknown source.";
		}
	}
}