<?php

//gives facebook like share comment or total count
class Instapress_Core_APIfacebook { 

private $_count;

private $_infoColumns = array('like'=>"like_count",'share'=>"share_count",'comment'=>"comment_count",'total'=>"total_count");

function __construct()
{
	
}

public function getFlikes($url,$getCount="total") {

	$this->_count = $this->_infoColumns[$getCount];
	
	$link ='http://api.facebook.com/method/fql.query?query='.urlencode('SELECT '.$this->_count.' FROM link_stat WHERE url=" '.$url.'"');
	$contents = file_get_contents($link);
	$xmlObj = simplexml_load_string( $contents );
	$result = json_decode( json_encode( $xmlObj->link_stat ), true );
	return $result[$this->_count];

}


}//end of getFlike
