<?php


class Instapress_Core_Plagiarism
{
	private $_object = null;
	
	public function __construct($text_to_search, $type = 'copyscape')
	{
		if($type=='copyscape')
			$this->_object = new Instapress_Core_CopyScape($text_to_search);
		else
			$this->_object = new Instapress_Core_InstamediaPlagiarism($text_to_search);	
	}
	
	public function getResultCount()
	{
		return $this->_object->getResultCount();
	}
	
	public function getResults()
	{
		return $this->_object->getResults();
	}
	
	
}