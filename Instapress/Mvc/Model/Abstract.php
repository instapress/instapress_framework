<?php
abstract class Instapress_Mvc_Model_Abstract
{
	public $result = array();
	public $count = 0;
	public $totalRecords = 0;
	public $countPages = 0;
	public $task = 'SHOW';

	public function isTrue($var){
		if($this->$var) return true;
	}
	public function isFalse($var){
		if(!$this->$var) return true;
	}
	abstract public function set();
	abstract public function get();
	abstract public function build();
	abstract public function add();
	abstract public function edit();
	abstract public function del();
	abstract public function show();

}