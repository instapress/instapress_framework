<?php 

	class Instapress_Core_Reddit{
		
		public $reddit_url = 'http://www.reddit.com/api/info.json?url=';
		public $score = 0;
		public $ups = 0;
		public $downs = 0;
		public $comments = 0;
		public $author = '';
		public $url = '';
		
		private $_infoColumns = array(  'author', 'ups', 'downs', 'score', 'totalcomments' );
		
		private $_urlInfo = array();
		
		public function __construct( $url = null )
		{
			$this->url = $url;
		}
		
		public function get_url( $url = null ) { 
			$this->url = $url !== null ? $url : $this->url;
			if( $this->url === null ) {
				throw new Exception('Set Url first');
			}
	  		
		       $ch = curl_init();
	
		       curl_setopt($ch,CURLOPT_URL, $this->reddit_url . $this->url );
		    
		       curl_setopt($ch, CURLOPT_HEADER, 0);
		    
		       curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	
		       curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
	
		       $content = curl_exec($ch);
		       curl_close($ch);
		   
		       $json = json_decode( $content, true );
		       
		       $result = array();
		       
		       foreach( $json['data']['children'] as $child ) { 
			       $result[] = $child['data'];
			}
	  		
	  		foreach( $result as $key => $value ) {
	  			$this->_urlInfo[ 'author' ] = $value['author'];
	  			$this->_urlInfo[ 'ups' ] = $value['ups'];
	  			$this->_urlInfo[ 'downs' ] = $value['downs'];
	  			$this->_urlInfo[ 'score' ] = $value['score'];
	  			$this->_urlInfo[ 'totalcomments' ] = $value['num_comments'];
			}
	  		
	  		return $this->_urlInfo; 
		}
		
		public function getUrlInfo( $infoColumn ) {
			if( !in_array( $infoColumn, $this->_infoColumns ) ) {
				throw new Exception('Argument is not valid');
			}
			return $this->_urlInfo[ $infoColumn ];
		}
	}
		try {
		$obj = new Instapress_Core_Reddit();
		echo '<pre>';
		var_dump( $obj->get_url('http://imgur.com/hi6Lf') );
		echo '</pre>';
		
		echo '<p>Author : ' . $obj->getUrlInfo( 'author' ) . '</p>';
		}
		catch ( Exception $ex )
		{
			echo $ex->getMessage();
		}

?>
 

