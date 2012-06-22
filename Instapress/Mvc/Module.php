<?php
class Instapress_Mvc_Module {

	protected $_entities = array();
	protected $_ipmlParser = null;
	protected $_entityObj = array();
	public $moduleHtml = null;
	protected $_entity = 0;

	function __construct($id){
		$this->ipmlParser = new Instapress_Core_HTML_Dom(MODULES_PATH.$id.".ipml");
		//var_dump($this->ipmlParser);		

		foreach($this->ipmlParser->find('ipml:entity') as $key=>$e) {
			$this->_entities[$key]['type']=$e->type;
			$e->task = $e->task ? $e->task : "show";
			$this->_entities[$key]['task']=strtolower($e->task);
			
			/*echo $e->innertext;
			exit;*/
			
			$this->_entityObj[$key]= new Instapress_Mvc_Ipml($this->_entities[$key]['type'], $this->_entities[$key]['task'], new Instapress_Core_HTML_Dom($e->innertext));

			/*echo '<pre>';
			var_dump( $this->_entityObj[$key]->html() );
			echo '</pre>';
			exit;*/
			
			$this->moduleHtml.=$this->_entityObj[$key]->html();
				
		}
		//print_r($this->_entities);
	}
}