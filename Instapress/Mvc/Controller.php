<?php


class Instapress_Mvc_Controller
{
	protected $_model = null;
	protected $_task = 'SHOW';
	protected $_args = array();
	protected $_total_args = 0;

	public $totalRecords = 0;
	public $count = 0;

	function __construct($entity, $task='SHOW') {
		$entity=ucwords($entity);
		//throw exception check class exists or not
		$this->_model = new $entity();
		$this->_task = $task;
	}

	public function set($arr){
		$this->_model;
		$this->_model->count;
		$this->_model->countPages;
		$this->_model->totalRecords;

		$this->_model->task = $this->_task;
			
		//$this->_args=func_get_args();
		//$this->_total_args=func_num_args();

		foreach ($arr as $key=>$value) {
			$this->_model->set($key, $value);
		}
			
		$this->_model->build();

		$this->count = $this->model->count;
		$this->totalRecords=$this->model->totalRecords;
	}

	public function get(){
		return $this->_model->result[$i][$val];
	}

}
