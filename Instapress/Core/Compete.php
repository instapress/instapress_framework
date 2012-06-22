<?php


class Instapress_Core_Compete
{
	public $url;
	private $_objCompete = null;
	
	public function __construct(){
		require_once(LIB_PATH. 'Instapress/Core/Compete/competeApi.php');
		$this->_objCompete = new compete();
	}
	
	public function getDomainRank($url=null){
		$this->url = $url;
		
		$this->_objCompete -> query_compete($this->url);
		$results = $this->_objCompete->get_traffic();
	
		return $results['ranking'];
	}
}