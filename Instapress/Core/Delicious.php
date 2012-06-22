<?php

class Instapress_Core_Delicious{
	private $_url='';
	public $deliciousUrl = 'http://feeds.delicious.com/v2/json/urlinfo/data?url=';
	public function __construct( $url = null ){
		$this->_url = $url;
	}
	function LoadCURLPage($url = null, $agent='', $cookie='', $referer='', $post_fields='', $ssl='') 
	{
		$this->_url = $url !== null ? $url : $this->_url;
		if( $this->_url === null ) {
			throw new Exception('Please set Url!');
		}
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL,$this->deliciousUrl . $this->_url);
		if($ssl) curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		if($agent) curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		if($post_fields) 
		{
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if($referer) curl_setopt($ch, CURLOPT_REFERER, $referer);
		if($cookie) 
		{
		   curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		   curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		}
		$result = curl_exec ($ch);
		$result = json_decode( $result );
		if( !$result )
		{
			throw new Exception('No data found for this url');
		}
		curl_close ($ch);
	
		return $result;
	}

}
	try {
		$obj = new Instapress_Core_Delicious();
		$result = $obj->LoadCURLPage('http://ecofriend.org');
		echo '<pre>';
		var_dump( $result );
		echo '</pre>';
		echo "Total Bookmarks : ".$result[0] -> total_posts;
	}
	catch ( Exception $ex )
	{
		echo $ex->getMessage();
	}
?>
