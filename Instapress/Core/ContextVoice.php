<?php
	/**
	 * 
	 * @author samir
	 *
	 */
	class Instapress_Core_ContextVoice{
		private $_url = null;
		private $_cvObject = null;
		private $_result = null;
		
		public function __construct( $url = null ){
			require_once(LIB_PATH. 'Instapress/Core/Contextvoice/ContextVoiceApi.php');
			$this->_cvObject = new contextvoice('eenzmdnfxpamv9m47rddh6yz');
			$this->_url = $url;
		}
		public function getReactions( $url = null ){
			$this->_url = $url !== null ? $url : $this->_url;
			if( $this->_url === null ) {
				throw new Exception( 'Please set Url first' );
			}
			$this->_result = $this->_cvObject->getUrlReactions( $this->_url );
			
			$reactions = $this->_result->reactions;
			$this->_result = array();
	
			foreach( $reactions as $value )
			{
				$this->_result[ $value -> generator ][] = $value;
			}
			
			return $this->_result;
		}
		
		public function getResult() {
			if( $this->_result !== null ) {
				throw new Exception( 'No data found' );
			}
			return $this->_result;
		}
	}
		try {
			$cvObj = new Instapress_Core_ContextVoice( "http://www.homeqn.com/entry/amazing-tree-trunk-garden-house/" );
			echo '<pre>';
			var_dump( $cvObj->getReactions() );
			echo '</pre>';
		 }
		catch (Exception $ex) {
			echo $ex->getMessage();
		}
	
	
	

?>